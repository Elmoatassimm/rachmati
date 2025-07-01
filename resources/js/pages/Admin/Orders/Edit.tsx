import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import InputError from '@/components/input-error';
import { Order } from '@/types';
import {
  ArrowLeft,
  Save,
  X,
  Edit3,
  AlertTriangle,
  User,
  Package,
  CreditCard,
  FileText,
  Clock,
  DollarSign,
  Calendar,
  CheckCircle,
  XCircle
} from 'lucide-react';

interface Status {
  value: string;
  label: string;
  description: string;
}

interface Props {
  order: Order;
  statuses: Status[];
}

export default function Edit({ order, statuses }: Props) {
  const [showRejectionReason, setShowRejectionReason] = useState(order.status === 'rejected');

  const { data, setData, put, processing, errors, isDirty, reset } = useForm({
    status: order.status,
    admin_notes: order.admin_notes || '',
    rejection_reason: order.rejection_reason || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.orders.update', order.id), {
      preserveScroll: false,
      onSuccess: () => {
        // Success handled by redirect
      },
      onError: (errors) => {
        console.error('Validation errors:', errors);
      },
    });
  };

  const handleStatusChange = (newStatus: string) => {
    setData('status', newStatus);
    setShowRejectionReason(newStatus === 'rejected');
    
    // Clear rejection reason if not rejecting
    if (newStatus !== 'rejected') {
      setData('rejection_reason', '');
    }
  };

  const handleCancel = () => {
    if (isDirty) {
      if (confirm('هل أنت متأكد من إلغاء التغييرات؟ ستفقد جميع التعديلات غير المحفوظة.')) {
        router.visit(route('admin.orders.show', order.id));
      }
    } else {
      router.visit(route('admin.orders.show', order.id));
    }
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

  const getStatusBadge = (status: string) => {
    const variants = {
      pending: 'bg-yellow-100 text-yellow-800',
      confirmed: 'bg-blue-100 text-blue-800',
      file_available: 'bg-purple-100 text-purple-800',
      completed: 'bg-green-100 text-green-800',
      rejected: 'bg-red-100 text-red-800',
    };
    return variants[status as keyof typeof variants] || 'bg-gray-100 text-gray-800';
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'pending': return <Clock className="w-4 h-4" />;
      case 'confirmed': return <CheckCircle className="w-4 h-4" />;
      case 'file_available': return <Package className="w-4 h-4" />;
      case 'completed': return <CheckCircle className="w-4 h-4" />;
      case 'rejected': return <XCircle className="w-4 h-4" />;
      default: return <Clock className="w-4 h-4" />;
    }
  };

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'الطلبات', href: '/admin/orders' },
        { title: `طلب #${order.id}`, href: `/admin/orders/${order.id}` },
        { title: 'تعديل', href: `/admin/orders/${order.id}/edit` }
      ]}
    >
      <Head title={`تعديل طلب #${order.id} - Edit Order`} />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Revolutionary Header */}
          <div className="relative">
            <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5 rounded-3xl"></div>
            <div className="relative p-8">
              <div className="flex justify-between items-start">
                <div className="flex items-center gap-6">
                  <div className="w-16 h-16 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center shadow-xl">
                    <Edit3 className="w-8 h-8 text-primary-foreground" />
                  </div>
                  <div>
                    <h1 className="text-5xl font-black bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                      تحديث حالة الطلب #{order.id}
                    </h1>
                    <p className="text-xl text-muted-foreground mt-2">
                      Update Order Status
                    </p>
                  </div>
                </div>
                <div className="flex gap-3">
                  <Button
                    onClick={handleCancel}
                    variant="outline"
                    className="flex items-center gap-2 px-6 py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl"
                  >
                    <X className="w-5 h-5" />
                    <span className="font-semibold">إلغاء</span>
                  </Button>
                  <Link
                    href={route('admin.orders.show', order.id)}
                    className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-muted to-muted/80 hover:from-muted/80 hover:to-muted/60 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl"
                  >
                    <ArrowLeft className="w-5 h-5" />
                    <span className="font-semibold">عرض التفاصيل</span>
                  </Link>
                </div>
              </div>
            </div>
          </div>

          {/* Status Warning */}
          {order.status === 'completed' && (
            <Alert className="bg-gradient-to-r from-amber-50 to-amber-100 border-amber-200">
              <AlertTriangle className="w-5 h-5 text-amber-600" />
              <AlertDescription className="text-amber-800 font-semibold">
                تحذير: هذا الطلب مكتمل. قد تؤثر التغييرات على حسابات المصمم والعميل.
              </AlertDescription>
            </Alert>
          )}

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Edit Form */}
            <div className="lg:col-span-2">
              <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-2xl">
                <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
                <CardHeader className="relative pb-6">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-gradient-to-br from-primary to-primary/80 rounded-xl flex items-center justify-center shadow-lg">
                      <Edit3 className="w-6 h-6 text-primary-foreground" />
                    </div>
                    <CardTitle className="text-2xl font-bold text-foreground">تحديث حالة الطلب</CardTitle>
                  </div>
                </CardHeader>
                <CardContent className="relative">
                  {/* Current Order Details (Read-only) */}
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 p-6 bg-gradient-to-r from-muted/30 to-muted/10 rounded-xl border border-border/50">
                    <div className="space-y-3">
                      <Label className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">مبلغ الطلب</Label>
                      <div className="flex items-center gap-2">
                        <DollarSign className="w-5 h-5 text-emerald-600" />
                        <p className="text-xl font-black text-emerald-600">{formatCurrency(order.amount)}</p>
                      </div>
                    </div>

                    <div className="space-y-3">
                      <Label className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">طريقة الدفع</Label>
                      <div className="flex items-center gap-2">
                        <CreditCard className="w-5 h-5 text-blue-600" />
                        <p className="text-lg font-semibold text-foreground">
                          {order.payment_method === 'ccp' ? 'CCP' :
                           order.payment_method === 'baridi_mob' ? 'Baridi Mob' :
                           order.payment_method === 'dahabiya' ? 'Dahabiya' : order.payment_method}
                        </p>
                      </div>
                    </div>
                  </div>

                  {/* Information Note */}
                  <div className="p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl border border-blue-200 mb-6">
                    <div className="flex items-start gap-3">
                      <FileText className="w-5 h-5 text-blue-600 mt-0.5" />
                      <div>
                        <h4 className="font-semibold text-blue-800 mb-1">ملاحظة مهمة</h4>
                        <p className="text-sm text-blue-700">
                          يمكن تحديث حالة الطلب وإضافة ملاحظات إدارية فقط. مبلغ الطلب وطريقة الدفع لا يمكن تعديلهما لضمان سلامة البيانات.
                        </p>
                      </div>
                    </div>
                  </div>

                  <form onSubmit={handleSubmit} className="space-y-8">

                    {/* Status Field */}
                    <div className="space-y-3">
                      <Label className="text-base font-semibold text-foreground flex items-center gap-2">
                        <Clock className="w-4 h-4 text-primary" />
                        حالة الطلب *
                      </Label>
                      <div className="space-y-3">
                        {statuses.map((status) => (
                          <div
                            key={status.value}
                            className={`p-4 rounded-xl border-2 cursor-pointer transition-all duration-300 ${
                              data.status === status.value
                                ? 'border-primary bg-primary/5 shadow-lg'
                                : 'border-border hover:border-primary/50 hover:bg-muted/30'
                            }`}
                            onClick={() => handleStatusChange(status.value)}
                          >
                            <div className="flex items-center justify-between">
                              <div className="flex items-center gap-3">
                                <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center ${
                                  data.status === status.value
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-muted-foreground'
                                }`}>
                                  {data.status === status.value && <CheckCircle className="w-4 h-4" />}
                                </div>
                                <div>
                                  <div className="flex items-center gap-2">
                                    {getStatusIcon(status.value)}
                                    <span className="font-semibold text-foreground">{status.label}</span>
                                  </div>
                                  <p className="text-sm text-muted-foreground mt-1">{status.description}</p>
                                </div>
                              </div>
                              <Badge className={getStatusBadge(status.value)}>
                                {status.label}
                              </Badge>
                            </div>
                          </div>
                        ))}
                      </div>
                      <InputError message={errors.status} />
                    </div>

                    {/* Rejection Reason Field */}
                    {showRejectionReason && (
                      <div className="space-y-3 p-6 bg-gradient-to-r from-red-50 to-red-100 rounded-xl border border-red-200">
                        <Label className="text-base font-semibold text-red-800 flex items-center gap-2">
                          <XCircle className="w-4 h-4" />
                          سبب الرفض *
                        </Label>
                        <Textarea
                          value={data.rejection_reason}
                          onChange={(e) => setData('rejection_reason', e.target.value)}
                          placeholder="أدخل سبب رفض الطلب..."
                          className="min-h-[100px] text-base border-red-300 focus:border-red-500 focus:ring-red-500"
                          disabled={processing}
                        />
                        <InputError message={errors.rejection_reason} />
                      </div>
                    )}

                    {/* Admin Notes Field */}
                    <div className="space-y-3">
                      <Label className="text-base font-semibold text-foreground flex items-center gap-2">
                        <FileText className="w-4 h-4 text-primary" />
                        ملاحظات الإدارة
                      </Label>
                      <Textarea
                        value={data.admin_notes}
                        onChange={(e) => setData('admin_notes', e.target.value)}
                        placeholder="أضف ملاحظات إدارية (اختياري)..."
                        className="min-h-[120px] text-base"
                        disabled={processing}
                      />
                      <InputError message={errors.admin_notes} />
                      <p className="text-sm text-muted-foreground">
                        هذه الملاحظات للاستخدام الداخلي ولن تظهر للعميل
                      </p>
                    </div>

                    {/* Submit Button */}
                    <div className="flex gap-4 pt-6">
                      <Button
                        type="submit"
                        disabled={processing || !isDirty}
                        className="flex-1 h-12 text-base bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 shadow-lg hover:shadow-xl transition-all duration-300"
                      >
                        {processing ? (
                          <div className="flex items-center gap-2">
                            <div className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" />
                            جاري الحفظ...
                          </div>
                        ) : (
                          <div className="flex items-center gap-2">
                            <Save className="w-5 h-5" />
                            تحديث حالة الطلب
                          </div>
                        )}
                      </Button>
                      <Button
                        type="button"
                        onClick={() => reset()}
                        variant="outline"
                        disabled={processing || !isDirty}
                        className="h-12 px-6 text-base"
                      >
                        إعادة تعيين
                      </Button>
                    </div>
                  </form>
                </CardContent>
              </Card>
            </div>

            {/* Order Information Sidebar */}
            <div className="space-y-6">
              {/* Current Order Info */}
              <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-xl hover:shadow-2xl transition-all duration-500">
                <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-blue-500/10"></div>
                <CardHeader className="relative pb-4">
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                      <FileText className="w-5 h-5 text-white" />
                    </div>
                    <CardTitle className="text-lg font-bold text-foreground">معلومات الطلب</CardTitle>
                  </div>
                </CardHeader>
                <CardContent className="relative space-y-4">
                  <div className="p-3 bg-gradient-to-r from-background to-muted/20 rounded-lg">
                    <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">رقم الطلب</label>
                    <p className="text-lg font-black font-mono text-primary mt-1">#{order.id}</p>
                  </div>

                  <div className="p-3 bg-gradient-to-r from-background to-muted/20 rounded-lg">
                    <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">الحالة الحالية</label>
                    <div className="flex items-center gap-2 mt-1">
                      {getStatusIcon(order.status)}
                      <Badge className={getStatusBadge(order.status)}>
                        {statuses.find(s => s.value === order.status)?.label || order.status}
                      </Badge>
                    </div>
                  </div>

                  <div className="p-3 bg-gradient-to-r from-background to-muted/20 rounded-lg">
                    <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">تاريخ الإنشاء</label>
                    <div className="flex items-center gap-2 mt-1">
                      <Calendar className="w-4 h-4 text-muted-foreground" />
                      <p className="text-sm font-semibold text-foreground">{formatDate(order.created_at)}</p>
                    </div>
                  </div>

                  {order.confirmed_at && (
                    <div className="p-3 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border border-green-200">
                      <label className="text-xs font-semibold text-green-700 uppercase tracking-wider">تاريخ التأكيد</label>
                      <p className="text-sm font-semibold text-green-800 mt-1">{formatDate(order.confirmed_at)}</p>
                    </div>
                  )}

                  {order.file_sent_at && (
                    <div className="p-3 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                      <label className="text-xs font-semibold text-blue-700 uppercase tracking-wider">تاريخ إرسال الملف</label>
                      <p className="text-sm font-semibold text-blue-800 mt-1">{formatDate(order.file_sent_at)}</p>
                    </div>
                  )}
                </CardContent>
              </Card>

              {/* Client Information */}
              <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-xl hover:shadow-2xl transition-all duration-500">
                <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-emerald-500/10"></div>
                <CardHeader className="relative pb-4">
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                      <User className="w-5 h-5 text-white" />
                    </div>
                    <CardTitle className="text-lg font-bold text-foreground">العميل</CardTitle>
                  </div>
                </CardHeader>
                <CardContent className="relative space-y-3">
                  <div className="p-3 bg-gradient-to-r from-background to-muted/20 rounded-lg">
                    <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">الاسم</label>
                    <p className="text-sm font-bold text-foreground mt-1">{order.client?.name || 'غير محدد'}</p>
                  </div>

                  <div className="p-3 bg-gradient-to-r from-background to-muted/20 rounded-lg">
                    <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">البريد الإلكتروني</label>
                    <p className="text-sm font-semibold text-foreground mt-1">{order.client?.email || 'غير محدد'}</p>
                  </div>
                </CardContent>
              </Card>

              {/* Rachma Information */}
              <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-xl hover:shadow-2xl transition-all duration-500">
                <div className="absolute inset-0 bg-gradient-to-br from-purple-500/5 via-transparent to-purple-500/10"></div>
                <CardHeader className="relative pb-4">
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg">
                      <Package className="w-5 h-5 text-white" />
                    </div>
                    <CardTitle className="text-lg font-bold text-foreground">الرشمة</CardTitle>
                  </div>
                </CardHeader>
                <CardContent className="relative space-y-3">
                  <div className="p-3 bg-gradient-to-r from-background to-muted/20 rounded-lg">
                    <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">اسم الرشمة</label>
                    <p className="text-sm font-bold text-foreground mt-1">{order.rachma?.title || 'غير محدد'}</p>
                  </div>

                  <div className="p-3 bg-gradient-to-r from-background to-muted/20 rounded-lg">
                    <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">السعر الأصلي</label>
                    <p className="text-sm font-semibold text-foreground mt-1">{formatCurrency(order.rachma?.price || 0)}</p>
                  </div>

                  <div className="p-3 bg-gradient-to-r from-background to-muted/20 rounded-lg">
                    <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">المصمم</label>
                    <p className="text-sm font-semibold text-foreground mt-1">{order.rachma?.designer?.store_name || 'غير محدد'}</p>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
