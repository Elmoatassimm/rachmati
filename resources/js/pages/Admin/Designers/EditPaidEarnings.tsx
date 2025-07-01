import React from 'react';
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
import { Designer } from '@/types';
import {
  ArrowLeft,
  Save,
  X,
  DollarSign,
  TrendingUp,
  AlertTriangle,
  User,
  Store,
  Calculator,
  FileText,
  CheckCircle
} from 'lucide-react';

interface Stats {
  totalEarnings: number;
  paidEarnings: number;
  unpaidEarnings: number;
}

interface Props {
  designer: Designer;
  stats: Stats;
}

export default function EditPaidEarnings({ designer, stats }: Props) {
  const { data, setData, put, processing, errors, isDirty, reset } = useForm({
    paid_earnings: stats.paidEarnings.toString(),
    admin_notes: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.designers.update-paid-earnings', designer.id), {
      preserveScroll: false,
      onSuccess: () => {
        // Success handled by redirect
      },
      onError: (errors) => {
        console.error('Validation errors:', errors);
      },
    });
  };

  const handleCancel = () => {
    router.visit(route('admin.designers.show', designer.id));
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ').format(amount) + ' دج';
  };

  const getStatusBadge = (status: string) => {
    const variants = {
      active: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
      pending: 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20',
      expired: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
    };
    return variants[status as keyof typeof variants] || 'bg-muted text-muted-foreground border-border';
  };

  const statusLabels = {
    active: 'نشط',
    pending: 'معلق',
    expired: 'منتهي'
  };

  const currentPaidEarnings = parseFloat(data.paid_earnings) || 0;
  const remainingEarnings = stats.totalEarnings - currentPaidEarnings;
  const isValidAmount = currentPaidEarnings >= 0 && currentPaidEarnings <= stats.totalEarnings;

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'إدارة المصممين', href: '/admin/designers' },
        { title: designer.store_name, href: `/admin/designers/${designer.id}` },
        { title: 'تعديل الأرباح المدفوعة', href: `/admin/designers/${designer.id}/edit-paid-earnings` }
      ]}
    >
      <Head title={`تعديل الأرباح المدفوعة - ${designer.store_name}`} />

      <div className="space-y-6 p-6">
        {/* Page Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight text-foreground">تعديل الأرباح المدفوعة</h1>
            <p className="text-muted-foreground">
              إدارة الأرباح المدفوعة للمصمم {designer.store_name}
            </p>
          </div>
          <Link
            href={route('admin.designers.show', designer.id)}
            className="inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
          >
            <ArrowLeft className="w-4 h-4 ml-2" />
            العودة إلى تفاصيل المصمم
          </Link>
        </div>

        {/* Designer Info Card */}
        <Card className="border border-border shadow-sm bg-card">
          <CardHeader className="border-b border-border">
            <CardTitle className="text-lg font-semibold text-right flex items-center gap-2 text-foreground">
              <Store className="h-5 w-5" />
              معلومات المصمم
            </CardTitle>
          </CardHeader>
          <CardContent className="pt-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-4">
                <div>
                  <Label className="text-sm font-medium text-muted-foreground">اسم المتجر</Label>
                  <p className="text-lg font-semibold text-foreground">{designer.store_name}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium text-muted-foreground">اسم المالك</Label>
                  <p className="text-foreground">{designer.user?.name}</p>
                </div>
              </div>
              <div className="space-y-4">
                <div>
                  <Label className="text-sm font-medium text-muted-foreground">البريد الإلكتروني</Label>
                  <p className="text-foreground">{designer.user?.email}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium text-muted-foreground">حالة الاشتراك</Label>
                  <Badge className={getStatusBadge(designer.subscription_status)}>
                    {statusLabels[designer.subscription_status as keyof typeof statusLabels] || designer.subscription_status}
                  </Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Earnings Overview */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Card className="border border-border shadow-sm bg-card">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">إجمالي الأرباح</CardTitle>
                <div className="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center">
                  <TrendingUp className="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
              </div>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="text-2xl font-bold text-foreground">
                {formatCurrency(stats.totalEarnings)}
              </div>
              <p className="text-xs text-muted-foreground mt-1">من جميع المبيعات</p>
            </CardContent>
          </Card>

          <Card className="border border-border shadow-sm bg-card">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">الأرباح المدفوعة</CardTitle>
                <div className="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                  <CheckCircle className="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
              </div>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="text-2xl font-bold text-foreground">
                {formatCurrency(stats.paidEarnings)}
              </div>
              <p className="text-xs text-muted-foreground mt-1">تم دفعها للمصمم</p>
            </CardContent>
          </Card>

          <Card className="border border-border shadow-sm bg-card">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">الأرباح المعلقة</CardTitle>
                <div className="w-10 h-10 bg-orange-500/10 rounded-lg flex items-center justify-center">
                  <DollarSign className="w-5 h-5 text-orange-600 dark:text-orange-400" />
                </div>
              </div>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="text-2xl font-bold text-foreground">
                {formatCurrency(stats.unpaidEarnings)}
              </div>
              <p className="text-xs text-muted-foreground mt-1">في انتظار الدفع</p>
            </CardContent>
          </Card>
        </div>

        {/* Edit Form */}
        <Card className="border border-border shadow-sm bg-card">
          <CardHeader className="border-b border-border">
            <CardTitle className="text-lg font-semibold text-right flex items-center gap-2 text-foreground">
              <Calculator className="h-5 w-5" />
              تحديث الأرباح المدفوعة
            </CardTitle>
          </CardHeader>
          <CardContent className="pt-6">
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="space-y-2">
                <Label htmlFor="paid_earnings" className="text-sm font-medium text-foreground">
                  مبلغ الأرباح المدفوعة (دج)
                </Label>
                <Input
                  id="paid_earnings"
                  type="number"
                  step="0.01"
                  min="0"
                  max={stats.totalEarnings}
                  value={data.paid_earnings}
                  onChange={e => setData('paid_earnings', e.target.value)}
                  className={`text-right ${errors.paid_earnings ? 'border-red-500' : ''}`}
                  placeholder="0.00"
                />
                <InputError message={errors.paid_earnings} />
                
                {/* Real-time calculation display */}
                {isValidAmount && (
                  <div className="mt-2 p-3 bg-muted/50 rounded-lg">
                    <div className="flex justify-between items-center text-sm">
                      <span className="text-muted-foreground">الأرباح المتبقية:</span>
                      <span className="font-semibold text-foreground">
                        {formatCurrency(remainingEarnings)}
                      </span>
                    </div>
                  </div>
                )}
                
                {!isValidAmount && currentPaidEarnings > stats.totalEarnings && (
                  <Alert className="border-red-500/20 bg-red-500/10">
                    <AlertTriangle className="h-4 w-4 text-red-600 dark:text-red-400" />
                    <AlertDescription className="text-red-800 dark:text-red-200">
                      المبلغ المدخل يتجاوز إجمالي الأرباح ({formatCurrency(stats.totalEarnings)})
                    </AlertDescription>
                  </Alert>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="admin_notes" className="text-sm font-medium text-foreground">
                  ملاحظات الإدارة (اختياري)
                </Label>
                <Textarea
                  id="admin_notes"
                  value={data.admin_notes}
                  onChange={e => setData('admin_notes', e.target.value)}
                  className="text-right resize-none"
                  rows={3}
                  placeholder="أضف ملاحظات حول هذا التحديث..."
                />
                <InputError message={errors.admin_notes} />
              </div>

              {/* Action Buttons */}
              <div className="flex items-center justify-end gap-4 pt-4 border-t border-border">
                <Button
                  type="button"
                  variant="outline"
                  onClick={handleCancel}
                  disabled={processing}
                  className="flex items-center gap-2"
                >
                  <X className="w-4 h-4" />
                  إلغاء
                </Button>
                <Button
                  type="submit"
                  disabled={processing || !isDirty || !isValidAmount}
                  className="flex items-center gap-2"
                >
                  <Save className="w-4 h-4" />
                  {processing ? 'جاري الحفظ...' : 'حفظ التغييرات'}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
