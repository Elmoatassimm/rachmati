import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import {
  ArrowLeft,
  CalendarDays,
  DollarSign,
  Clock,
  CheckCircle,
  XCircle,
  User,
  Eye,
  FileText,
} from 'lucide-react';
import { PageProps, SubscriptionRequest } from '@/types';
import { format, parseISO } from 'date-fns';
import { ar } from 'date-fns/locale';

interface Props extends PageProps {
  subscriptionRequest: SubscriptionRequest;
}

export default function Show({ subscriptionRequest }: Props) {
  const { data, setData, put, processing } = useForm({
    status: '',
    admin_notes: '',
  });

  const [submitting, setSubmitting] = useState(false);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending':
        return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case 'approved':
        return 'bg-green-100 text-green-800 border-green-200';
      case 'rejected':
        return 'bg-red-100 text-red-800 border-red-200';
      default:
        return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'pending':
        return 'معلق';
      case 'approved':
        return 'موافق عليه';
      case 'rejected':
        return 'مرفوض';
      default:
        return status;
    }
  };

  const review = (status: 'approved' | 'rejected') => {
    if (submitting) return;
    setSubmitting(true);
    setData('status', status);
    put(route('admin.subscription-requests.update', subscriptionRequest.id), {
      preserveScroll: true,
      onFinish: () => setSubmitting(false),
    });
  };

  return (
    <AppLayout>
      <Head title={`مراجعة طلب اشتراك #${subscriptionRequest.id}`} />

      <div className="container mx-auto px-4 py-6 max-w-7xl min-h-screen bg-background">
        <div className="space-y-8">
          {/* Header */}
          <div className="flex items-center space-x-4 space-x-reverse bg-card p-6 rounded-lg shadow-sm border border-border">
            <Link href={route('admin.subscription-requests.index')}>
              <Button variant="outline" size="sm">
                <ArrowLeft className="w-4 h-4 ml-2" />
                العودة إلى القائمة
              </Button>
            </Link>
            <div className="flex-1">
              <h1 className="text-3xl font-bold text-foreground mb-2">طلب اشتراك #{subscriptionRequest.id}</h1>
              <p className="text-muted-foreground">مراجعة تفاصيل الطلب واتخاذ الإجراء المناسب</p>
            </div>
          </div>

          {/* Main Content Grid */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Details */}
            <div className="lg:col-span-2 space-y-6">
            <Card className="shadow-sm border-border">
              <CardHeader className="pb-4">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-xl text-foreground">تفاصيل الطلب</CardTitle>
                  <Badge className={getStatusColor(subscriptionRequest.status)}>
                    {getStatusText(subscriptionRequest.status)}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-6 pt-2">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-muted-foreground">المصمم</label>
                    <div className="flex items-center mt-2">
                      <User className="w-4 h-4 ml-2 text-primary" />
                      <span className="font-semibold text-foreground">
                        {subscriptionRequest.designer?.store_name || '---'}
                      </span>
                    </div>
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-muted-foreground">خطة الاشتراك</label>
                    <p className="font-semibold text-foreground mt-2">{subscriptionRequest.pricing_plan?.name}</p>
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-muted-foreground">السعر</label>
                    <div className="flex items-center mt-2">
                      <DollarSign className="w-4 h-4 ml-2 text-green-500" />
                      <span className="font-semibold text-foreground">
                        {new Intl.NumberFormat('ar-DZ', {
                          style: 'currency',
                          currency: 'DZD',
                        }).format(subscriptionRequest.subscription_price)}
                      </span>
                    </div>
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-muted-foreground">تاريخ البداية المطلوب</label>
                    <div className="flex items-center mt-2">
                      <CalendarDays className="w-4 h-4 ml-2 text-blue-500" />
                      <span className="font-semibold text-foreground">
                        {format(parseISO(subscriptionRequest.requested_start_date), 'dd/MM/yyyy', { locale: ar })}
                      </span>
                    </div>
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-muted-foreground">مدة الاشتراك</label>
                    <div className="flex items-center mt-2">
                      <Clock className="w-4 h-4 ml-2 text-purple-500" />
                      <span className="font-semibold text-foreground">
                        {subscriptionRequest.pricing_plan?.duration_months} شهر
                      </span>
                    </div>
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-muted-foreground">تاريخ الإرسال</label>
                    <p className="font-semibold text-foreground mt-2">
                      {format(parseISO(subscriptionRequest.created_at), 'dd/MM/yyyy HH:mm', { locale: ar })}
                    </p>
                  </div>
                  {subscriptionRequest.reviewed_at && (
                    <div className="space-y-2">
                      <label className="text-sm font-medium text-muted-foreground">تاريخ المراجعة</label>
                      <p className="font-semibold text-foreground mt-2">
                        {format(parseISO(subscriptionRequest.reviewed_at), 'dd/MM/yyyy HH:mm', { locale: ar })}
                      </p>
                    </div>
                  )}
                </div>
                {subscriptionRequest.notes && (
                  <div className="space-y-3 pt-4 border-t border-border">
                    <label className="text-sm font-medium text-muted-foreground">ملاحظات المصمم</label>
                    <div className="mt-2 p-4 bg-muted/50 rounded-lg text-sm leading-relaxed text-foreground">
                      {subscriptionRequest.notes}
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Payment Proof Section */}
            <Card className="shadow-sm border-border">
              <CardHeader className="pb-4">
                <CardTitle className="text-xl text-foreground">إثبات الدفع</CardTitle>
              </CardHeader>
              <CardContent className="pt-2">
                {subscriptionRequest.payment_proof_url ? (
                  <div className="space-y-6">
                    {/* File Information */}
                    <div className="flex items-center justify-between p-4 bg-muted/30 rounded-lg border border-border">
                      <div className="flex items-center space-x-4 space-x-reverse">
                        <div className="p-3 bg-primary/10 rounded-lg">
                          {subscriptionRequest.payment_proof_mime_type?.startsWith('image/') ? (
                            <Eye className="w-6 h-6 text-primary" />
                          ) : (
                            <FileText className="w-6 h-6 text-primary" />
                          )}
                        </div>
                        <div className="flex-1">
                          <p className="font-medium text-base text-foreground mb-1">
                            {subscriptionRequest.payment_proof_original_name || 'إثبات الدفع'}
                          </p>
                          <p className="text-sm text-muted-foreground">
                            {subscriptionRequest.formatted_file_size && subscriptionRequest.payment_proof_mime_type
                              ? `${subscriptionRequest.formatted_file_size} • ${subscriptionRequest.payment_proof_mime_type}`
                              : subscriptionRequest.formatted_file_size || subscriptionRequest.payment_proof_mime_type || 'ملف مرفق'
                            }
                          </p>
                        </div>
                      </div>
                      <a
                        href={subscriptionRequest.payment_proof_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors border border-primary/20"
                      >
                        <Eye className="w-4 h-4 ml-2" />
                        عرض الملف
                      </a>
                    </div>

                    {/* Image Preview (only for images) */}
                    {subscriptionRequest.payment_proof_mime_type?.startsWith('image/') && (
                      <div className="border border-border rounded-lg overflow-hidden shadow-sm">
                        <img
                          src={subscriptionRequest.payment_proof_url}
                          alt="إثبات الدفع"
                          className="w-full h-80 object-contain bg-muted/20"
                          onError={(e) => {
                            // Hide image if it fails to load
                            e.currentTarget.style.display = 'none';
                          }}
                        />
                      </div>
                    )}
                  </div>
                ) : (
                  <div className="text-center py-12">
                    <div className="p-4 bg-muted/30 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                      <FileText className="w-10 h-10 text-muted-foreground" />
                    </div>
                    <p className="text-muted-foreground text-base font-medium">لم يتم إرفاق إثبات دفع</p>
                    <p className="text-muted-foreground/70 text-sm mt-2">لا يوجد ملف مرفق مع هذا الطلب</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

            {/* Review box */}
            <div className="space-y-6">
              {subscriptionRequest.status === 'pending' ? (
                <Card className="shadow-sm border-orange-500/20">
                  <CardHeader className="pb-4 bg-orange-500/10">
                    <CardTitle className="text-xl text-orange-600 dark:text-orange-400">اتخاذ إجراء</CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-6 pt-6">
                    <div className="space-y-3">
                      <label className="text-sm font-medium text-foreground" htmlFor="admin_notes">
                        ملاحظات المدير (اختياري)
                      </label>
                      <Textarea
                        id="admin_notes"
                        value={data.admin_notes}
                        onChange={(e) => setData('admin_notes', e.target.value)}
                        rows={4}
                        className="resize-none"
                        placeholder="أضف ملاحظاتك هنا..."
                      />
                    </div>
                    <div className="flex flex-col sm:flex-row gap-4 pt-4 border-t border-border">
                      <Button
                        disabled={processing || submitting}
                        onClick={() => review('approved')}
                        className="bg-green-600 hover:bg-green-700 text-white flex-1 py-3"
                      >
                        <CheckCircle className="w-5 h-5 ml-2" />
                        قبول الطلب
                      </Button>
                      <Button
                        variant="destructive"
                        disabled={processing || submitting}
                        onClick={() => review('rejected')}
                        className="flex-1 py-3"
                      >
                        <XCircle className="w-5 h-5 ml-2" />
                        رفض الطلب
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              ) : (
                <Card className="shadow-sm border-border">
                  <CardHeader className="pb-4">
                    <CardTitle className="text-xl text-foreground">ملاحظات المدير</CardTitle>
                  </CardHeader>
                  <CardContent className="pt-2">
                    {subscriptionRequest.admin_notes ? (
                      <div className="p-4 bg-muted/30 rounded-lg border border-border">
                        <p className="text-sm leading-relaxed text-foreground">{subscriptionRequest.admin_notes}</p>
                      </div>
                    ) : (
                      <p className="text-sm text-muted-foreground italic">لا توجد ملاحظات</p>
                    )}
                  </CardContent>
                </Card>
              )}
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
} 