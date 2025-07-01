import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';

import ErrorBoundary from '@/components/error-boundary';
import { Order } from '@/types';
import {
  ArrowLeft,
  CheckCircle,
  XCircle,
  Clock,
  User,
  Package,
  CreditCard,
  FileText,
  Eye,
  Calendar,
  DollarSign
} from 'lucide-react';

interface Props {
  order: Order;
}

export default function Show({ order }: Props) {
  // Form for updating order status
  const updateForm = useForm({
    status: order.status,
    admin_notes: '',
    rejection_reason: ''
  });

  // State for file delivery validation
  const [deliveryStatus, setDeliveryStatus] = React.useState<{
    canComplete: boolean;
    message: string;
    issues: string[];
    totalSize?: number;
    filesCount: number;
    clientHasTelegram: boolean;
    hasFiles: boolean;
    files: Array<{
      id: number;
      name: string;
      format: string;
      size?: number;
      exists: boolean;
      is_primary: boolean;
    }>;
    recommendations: string[];
  } | null>(null);

  const [showDeliveryCheck, setShowDeliveryCheck] = React.useState(false);

  const getStatusBadge = (status: string) => {
    const variants = {
      pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300',
      confirmed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
      completed: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300',
      rejected: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300',
    };
    return variants[status as keyof typeof variants] || 'bg-muted text-muted-foreground';
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('ar-DZ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ').format(amount) + ' دج';
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const checkFileDelivery = async () => {
    try {
      const response = await fetch(route('admin.orders.check-file-delivery', order.id));
      const data = await response.json();
      setDeliveryStatus(data);
      setShowDeliveryCheck(true);
      return data;
    } catch (error) {
      console.error('Error checking file delivery:', error);
      return null;
    }
  };

  const handleConfirmOrder = async () => {
    // First check file delivery status
    const deliveryCheck = await checkFileDelivery();

    if (!deliveryCheck) {
      alert('فشل في التحقق من حالة التسليم. يرجى المحاولة مرة أخرى.');
      return;
    }

    if (!deliveryCheck.canComplete) {
      // Show detailed error message
      alert(`لا يمكن إكمال الطلب:\n${deliveryCheck.message}\n\nالتوصيات:\n${deliveryCheck.recommendations.join('\n')}`);
      return;
    }

    // Show confirmation with delivery details
    const filesInfo = deliveryCheck.filesCount > 1
      ? `${deliveryCheck.filesCount} ملفات`
      : 'ملف واحد';

    const confirmMessage = `هل أنت متأكد من تأكيد هذا الطلب؟\n\n` +
      `✅ ${filesInfo} موجود (${deliveryCheck.totalSize ? formatFileSize(deliveryCheck.totalSize) : 'غير محدد'})\n` +
      `${deliveryCheck.clientHasTelegram ? '✅' : '❌'} العميل مربوط بتيليجرام\n\n` +
      `${deliveryCheck.filesCount > 1 ? 'سيتم ضغط الملفات في ملف ZIP و' : 'سيتم '}إرسال ${deliveryCheck.filesCount > 1 ? 'الملفات' : 'الملف'} فوراً للعميل عند التأكيد.`;

    if (confirm(confirmMessage)) {
      updateForm.setData({
        status: 'completed',
        admin_notes: 'تم تأكيد الطلب وإرسال الملف',
        rejection_reason: ''
      });

      updateForm.put(route('admin.orders.update', order.id), {
        onSuccess: () => {
          console.log('Order confirmed successfully');
          setShowDeliveryCheck(false);
        },
        onError: (errors) => {
          console.error('Error confirming order:', errors);
        },
      });
    }
  };

  const handleRejectOrder = () => {
    const reason = prompt('سبب الرفض:');
    if (reason) {
      updateForm.setData({
        status: 'rejected',
        admin_notes: 'تم رفض الطلب',
        rejection_reason: reason
      });

      updateForm.put(route('admin.orders.update', order.id), {
        onSuccess: () => {
          console.log('Order rejected successfully');
        },
        onError: (errors) => {
          console.error('Error rejecting order:', errors);
        },
      });
    }
  };



  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'الطلبات', href: '/admin/orders' },
        { title: `طلب #${order.id}`, href: `/admin/orders/${order.id}` }
      ]}
    >
      <Head title={`طلب #${order.id} - Order Details`} />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Revolutionary Header */}
          <div className="relative">
            <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5 rounded-3xl"></div>
            <div className="relative p-8">
              <div className="flex justify-between items-start">
                <div className="flex items-center gap-6">
                  <div className="w-16 h-16 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center shadow-xl">
                    <Eye className="w-8 h-8 text-primary-foreground" />
                  </div>
                  <div>
                    <h1 className="text-5xl font-black bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                      طلب #{order.id}
                    </h1>
                    <p className="text-xl text-muted-foreground mt-2">
                      Order Details
                    </p>
                  </div>
                </div>
                <Link
                  href="/admin/orders"
                  className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-muted to-muted/80 hover:from-muted/80 hover:to-muted/60 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl"
                >
                  <ArrowLeft className="w-5 h-5" />
                  <span className="font-semibold">العودة للطلبات</span>
                </Link>
              </div>
            </div>
          </div>

          {/* Success/Error Messages */}
          {updateForm.recentlySuccessful && (
            <Alert className="bg-gradient-to-r from-emerald-50 to-emerald-100 border-emerald-200">
              <CheckCircle className="w-5 h-5 text-emerald-600" />
              <AlertDescription className="text-emerald-800 font-semibold">تم تحديث حالة الطلب بنجاح!</AlertDescription>
            </Alert>
          )}

          {/* Error Messages */}
          {Object.keys(updateForm.errors).length > 0 && (
            <Alert className="bg-gradient-to-r from-destructive/10 to-destructive/20 border-destructive/30">
              <XCircle className="w-5 h-5 text-destructive" />
              <AlertDescription className="text-destructive font-semibold">
                {Object.values(updateForm.errors)[0]}
              </AlertDescription>
            </Alert>
          )}

          {/* Order Status */}
          <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-2xl">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
            <CardHeader className="relative pb-6">
              <CardTitle className="flex items-center justify-between text-2xl font-bold">
                <div className="flex items-center gap-4">
                  <Clock className="w-8 h-8 text-primary" />
                  <span>حالة الطلب</span>
                </div>
                <Badge className={`px-4 py-2 text-lg font-semibold ${getStatusBadge(order.status)}`}>
                  {order.status}
                </Badge>
              </CardTitle>
            </CardHeader>
            <CardContent className="relative">
              <div className="space-y-4">
                {/* File Delivery Status Check */}
                {order.status === 'pending' && (
                  <div className="p-4 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-950/20 dark:to-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <div className="flex items-center justify-between">
                      <div>
                        <h4 className="font-semibold text-blue-800 dark:text-blue-300">التحقق من إمكانية التسليم</h4>
                        <p className="text-sm text-blue-600 dark:text-blue-400">تحقق من حالة الملف والاتصال قبل إكمال الطلب</p>
                      </div>
                      <Button
                        onClick={checkFileDelivery}
                        variant="outline"
                        size="sm"
                        className="border-blue-300 text-blue-700 hover:bg-blue-50 dark:border-blue-700 dark:text-blue-300 dark:hover:bg-blue-950/20"
                      >
                        <Eye className="w-4 h-4 ml-2" />
                        فحص التسليم
                      </Button>
                    </div>
                  </div>
                )}

                {/* Delivery Status Display */}
                {showDeliveryCheck && deliveryStatus && (
                  <div className={`p-4 rounded-xl border ${
                    deliveryStatus.canComplete
                      ? 'bg-gradient-to-r from-emerald-50 to-emerald-100 dark:from-emerald-950/20 dark:to-emerald-900/20 border-emerald-200 dark:border-emerald-800'
                      : 'bg-gradient-to-r from-destructive/10 to-destructive/20 border-destructive/30'
                  }`}>
                    <div className="space-y-3">
                      <div className="flex items-center gap-2">
                        {deliveryStatus.canComplete ? (
                          <CheckCircle className="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        ) : (
                          <XCircle className="w-5 h-5 text-destructive" />
                        )}
                        <span className={`font-semibold ${
                          deliveryStatus.canComplete ? 'text-emerald-800 dark:text-emerald-300' : 'text-destructive'
                        }`}>
                          {deliveryStatus.message}
                        </span>
                      </div>

                      <div className="grid grid-cols-2 gap-4 text-sm">
                        <div className="flex items-center gap-2">
                          {deliveryStatus.hasFiles ? (
                            <CheckCircle className="w-4 h-4 text-emerald-500 dark:text-emerald-400" />
                          ) : (
                            <XCircle className="w-4 h-4 text-destructive" />
                          )}
                          <span>الملفات موجودة ({deliveryStatus.filesCount})</span>
                          {deliveryStatus.totalSize && (
                            <span className="text-muted-foreground">({formatFileSize(deliveryStatus.totalSize)})</span>
                          )}
                        </div>
                        <div className="flex items-center gap-2">
                          {deliveryStatus.clientHasTelegram ? (
                            <CheckCircle className="w-4 h-4 text-emerald-500 dark:text-emerald-400" />
                          ) : (
                            <XCircle className="w-4 h-4 text-destructive" />
                          )}
                          <span>مربوط بتيليجرام</span>
                        </div>
                      </div>

                      {/* Files Details */}
                      {deliveryStatus.files.length > 0 && (
                        <div className="mt-3 p-3 bg-background/50 rounded-lg">
                          <h5 className="font-semibold text-sm mb-2">تفاصيل الملفات:</h5>
                          <div className="space-y-2">
                            {deliveryStatus.files.map((file) => (
                              <div key={file.id} className="flex items-center justify-between text-sm">
                                <div className="flex items-center gap-2">
                                  {file.exists ? (
                                    <CheckCircle className="w-3 h-3 text-emerald-500 dark:text-emerald-400" />
                                  ) : (
                                    <XCircle className="w-3 h-3 text-destructive" />
                                  )}
                                  <span className={file.is_primary ? 'font-semibold' : ''}>
                                    {file.name}
                                  </span>
                                  {file.is_primary && (
                                    <span className="text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300 px-1 rounded">أساسي</span>
                                  )}
                                </div>
                                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                  <span>{file.format}</span>
                                  {file.size && <span>({formatFileSize(file.size)})</span>}
                                </div>
                              </div>
                            ))}
                          </div>
                        </div>
                      )}

                      {deliveryStatus.recommendations.length > 0 && (
                        <div className="mt-3 p-3 bg-background/50 rounded-lg">
                          <h5 className="font-semibold text-sm mb-2">التوصيات:</h5>
                          <ul className="text-sm space-y-1">
                            {deliveryStatus.recommendations.map((rec, index) => (
                              <li key={index} className="flex items-start gap-2">
                                <span className="text-blue-600 dark:text-blue-400">•</span>
                                <span>{rec}</span>
                              </li>
                            ))}
                          </ul>
                        </div>
                      )}
                    </div>
                  </div>
                )}

                {/* Action Buttons */}
                <div className="flex gap-4">
                  {order.status === 'pending' && (
                    <>
                      <Button
                        onClick={handleConfirmOrder}
                        className="h-12 px-8 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white shadow-lg hover:shadow-xl transition-all duration-300"
                        disabled={updateForm.processing}
                      >
                        <CheckCircle className="w-5 h-5 ml-2" />
                        {updateForm.processing ? 'جاري التأكيد...' : 'تأكيد الطلب وإرسال الملف'}
                      </Button>
                      <Button
                        onClick={handleRejectOrder}
                        variant="outline"
                        className="h-12 px-8 border-2 border-destructive text-destructive hover:bg-destructive/10 hover:border-destructive/80 transition-all duration-300"
                        disabled={updateForm.processing}
                      >
                        <XCircle className="w-5 h-5 ml-2" />
                        {updateForm.processing ? 'جاري الرفض...' : 'رفض الطلب'}
                      </Button>
                    </>
                  )}
                  {order.status === 'completed' && (
                    <div className="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                      <CheckCircle className="w-5 h-5" />
                      <span className="font-semibold">تم إكمال الطلب وإرسال الملف</span>
                    </div>
                  )}
                  {order.status === 'rejected' && (
                    <div className="flex items-center gap-2 text-destructive">
                      <XCircle className="w-5 h-5" />
                      <span className="font-semibold">تم رفض الطلب</span>
                      {order.rejection_reason && (
                        <span className="text-sm text-muted-foreground">({order.rejection_reason})</span>
                      )}
                    </div>
                  )}
                </div>
              </div>
            </CardContent>
          </Card>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {/* Order Information */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-xl hover:shadow-2xl transition-all duration-500">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-blue-500/10"></div>
              <CardHeader className="relative pb-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <FileText className="w-6 h-6 text-white" />
                  </div>
                  <CardTitle className="text-2xl font-bold text-foreground">معلومات الطلب</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="relative space-y-6">
                <div className="p-4 bg-gradient-to-r from-background to-muted/20 rounded-xl">
                  <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">رقم الطلب</label>
                  <p className="text-2xl font-black font-mono text-primary mt-1">#{order.id}</p>
                </div>

                <div className="p-4 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-xl">
                  <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">المبلغ</label>
                  <div className="flex items-center gap-2 mt-1">
                    <DollarSign className="w-6 h-6 text-emerald-600" />
                    <p className="text-2xl font-black text-emerald-600">{formatCurrency(order.amount)}</p>
                  </div>
                </div>

                <div className="p-4 bg-gradient-to-r from-background to-muted/20 rounded-xl">
                  <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">تاريخ الطلب</label>
                  <div className="flex items-center gap-2 mt-1">
                    <Calendar className="w-5 h-5 text-muted-foreground" />
                    <p className="text-lg font-semibold text-foreground">{formatDate(order.created_at)}</p>
                  </div>
                </div>

                <div className="p-4 bg-gradient-to-r from-background to-muted/20 rounded-xl">
                  <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">آخر تحديث</label>
                  <div className="flex items-center gap-2 mt-1">
                    <Calendar className="w-5 h-5 text-muted-foreground" />
                    <p className="text-lg font-semibold text-foreground">{formatDate(order.updated_at)}</p>
                  </div>
                </div>

                {order.admin_notes && (
                  <div className="p-4 bg-gradient-to-r from-amber-50 to-amber-100 rounded-xl border border-amber-200">
                    <label className="text-sm font-semibold text-amber-700 uppercase tracking-wider">ملاحظات الإدارة</label>
                    <p className="text-sm text-amber-800 mt-2 leading-relaxed">{order.admin_notes}</p>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Client Information */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-xl hover:shadow-2xl transition-all duration-500">
              <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-emerald-500/10"></div>
              <CardHeader className="relative pb-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                    <User className="w-6 h-6 text-white" />
                  </div>
                  <CardTitle className="text-2xl font-bold text-foreground">معلومات العميل</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="relative space-y-6">
                <div className="p-4 bg-gradient-to-r from-background to-muted/20 rounded-xl">
                  <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">الاسم</label>
                  <p className="text-xl font-bold text-foreground mt-1">{order.client?.name || 'غير محدد'}</p>
                </div>

                <div className="p-4 bg-gradient-to-r from-background to-muted/20 rounded-xl">
                  <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">البريد الإلكتروني</label>
                  <p className="text-lg font-semibold text-foreground mt-1">{order.client?.email || 'غير محدد'}</p>
                </div>

                {order.client?.id && (
                  <div className="p-4 bg-gradient-to-r from-primary/10 to-primary/5 rounded-xl border border-primary/20">
                    <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">رقم العميل</label>
                    <p className="text-xl font-black font-mono text-primary mt-1">#{order.client.id}</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Rachma Information */}
          <ErrorBoundary>
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-2xl">
              <div className="absolute inset-0 bg-gradient-to-br from-purple-500/5 via-transparent to-purple-500/10"></div>
              <CardHeader className="relative pb-6">
                <div className="flex items-center gap-4">
                  <div className="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl">
                    <Package className="w-7 h-7 text-white" />
                  </div>
                  <CardTitle className="text-3xl font-bold text-foreground">معلومات الرشمة</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="relative">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div className="space-y-4">
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">اسم الرشمة</label>
                    <p className="text-lg text-foreground">{order.rachma?.title || 'غير محدد'}</p>
                  </div>

                  {order.rachma?.description && (
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">الوصف</label>
                      <p className="text-sm text-foreground">{order.rachma.description}</p>
                    </div>
                  )}

                  <div>
                    <label className="text-sm font-medium text-muted-foreground">السعر</label>
                    <p className="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{formatCurrency(order.rachma?.price || 0)}</p>
                  </div>

                  <div>
                    <label className="text-sm font-medium text-muted-foreground">التصنيف</label>
                    <p className="text-foreground">{order.rachma?.categories?.[0]?.name || 'غير محدد'}</p>
                  </div>


                </div>

                <div className="space-y-4">
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">المصمم</label>
                    <p className="text-lg text-foreground">{order.rachma?.designer?.store_name || 'غير محدد'}</p>
                    <p className="text-sm text-muted-foreground">{order.rachma?.designer?.user?.name || 'غير محدد'}</p>
                    <p className="text-sm text-muted-foreground">{order.rachma?.designer?.user?.email || 'غير محدد'}</p>
                  </div>

                  <ErrorBoundary>
                    {(() => {
                      try {
                        // Use preview_image_urls from the rachma (properly formatted URLs)
                        const images = order.rachma?.preview_image_urls;
                        if (images && Array.isArray(images) && images.length > 0) {
                          return (
                            <div>
                              <label className="text-sm font-medium text-muted-foreground">صور المعاينة</label>
                              <div className="grid grid-cols-2 gap-2 mt-2">
                                {images.slice(0, 4).map((image, index) => {
                                  if (typeof image !== 'string') return null;
                                  return (
                                    <div key={index} className="relative">
                                      <img
                                        src={image}
                                        alt={`Preview ${index + 1}`}
                                        className="w-full h-20 object-cover rounded border"
                                        loading="lazy"
                                        decoding="async"
                                      />
                                    </div>
                                  );
                                })}
                              </div>
                            </div>
                          );
                        }
                        return null;
                      } catch (error) {
                        console.error('Error rendering preview images:', error);
                        return (
                          <div className="p-2 text-sm text-destructive bg-destructive/10 rounded">
                            خطأ في عرض صور المعاينة
                          </div>
                        );
                      }
                    })()}
                  </ErrorBoundary>
                </div>
              </div>
            </CardContent>
          </Card>
        </ErrorBoundary>

          {/* Payment Information */}
          <ErrorBoundary>
            {order.payment_proof_path && (
              <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-xl hover:shadow-2xl transition-all duration-500">
                <div className="absolute inset-0 bg-gradient-to-br from-green-500/5 via-transparent to-green-500/10"></div>
                <CardHeader className="relative pb-6">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                      <CreditCard className="w-6 h-6 text-white" />
                    </div>
                    <CardTitle className="text-2xl font-bold text-foreground">إثبات الدفع</CardTitle>
                  </div>
                </CardHeader>
                <CardContent className="relative">
                  <div className="max-w-md p-4 bg-gradient-to-r from-background to-muted/20 rounded-2xl">
                    <img
                      src={order.payment_proof_url}
                      alt="Payment Proof"
                      className="w-full h-auto rounded-xl border-2 border-border/50 shadow-lg"
                      loading="lazy"
                      decoding="async"
                    />
                  </div>
                </CardContent>
              </Card>
            )}
          </ErrorBoundary>
        </div>
      </div>
    </AppLayout>
  );
}