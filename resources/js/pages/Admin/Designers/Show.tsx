import React from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable, DataTableColumnHeader } from '@/components/ui/data-table';
import ErrorBoundary from '@/components/error-boundary';
import { Designer, Rachma, PricingPlan } from '@/types';
import {
  ArrowLeft,
  Crown,
  CheckCircle,
  XCircle,
  Store,
  User,
  DollarSign,
  TrendingUp,
  Calendar,
  Star,
  Package,
  Eye,
  Clock,
  FileImage,
  Plus,
  Minus,
  Settings,
  Download
} from 'lucide-react';

interface Stats {
  totalEarnings: number;
  unpaidEarnings: number;
  totalSales: number;
}

interface Props {
  designer: Designer;
  stats: Stats;
  rachmat: Rachma[];
  pricingPlans: PricingPlan[];
}

export default function Show({ designer, stats, rachmat, pricingPlans }: Props) {
  const { data, setData, post, processing } = useForm({
    pricing_plan_id: '',
  });

  const getStatusBadge = (status: string) => {
    const variants = {
      active: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
      pending: 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20',
      expired: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
    };
    return variants[status as keyof typeof variants] || 'bg-muted text-muted-foreground border-border';
  };

  const getSubscriptionBadge = (status: string) => {
    const variants = {
      active: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
      pending: 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20',
      expired: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
      rejected: 'bg-muted text-muted-foreground border-border',
    };
    return variants[status as keyof typeof variants] || 'bg-muted text-muted-foreground border-border';
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ar-DZ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ').format(amount) + ' دج';
  };

  const handleToggleStatus = () => {
    if (confirm('هل أنت متأكد من تغيير حالة المصمم؟')) {
      post(route('admin.designers.toggle-status', designer.id), {
        onSuccess: () => {
          console.log('Status toggled successfully');
        },
      });
    }
  };

  const handleApproveSubscription = () => {
    const months = prompt('كم شهراً تريد تفعيل الاشتراك؟ (1-24)', '6');
    if (months && !isNaN(Number(months)) && Number(months) >= 1 && Number(months) <= 24) {
      if (confirm(`هل أنت متأكد من الموافقة على اشتراك هذا المصمم لمدة ${months} أشهر؟`)) {
        router.post(`/admin/designers/${designer.id}/approve-subscription`, {
          duration_months: Number(months),
          pricing_plan_id: null // No specific pricing plan for approval
        });
      }
    } else if (months !== null) {
      alert('يرجى إدخال رقم صحيح بين 1 و 24');
    }
  };

  const handleRejectSubscription = () => {
    const reason = prompt('سبب الرفض:');
    if (reason) {
      router.post(`/admin/designers/${designer.id}/reject-subscription`, { reason });
    }
  };

  const handleActivateSubscription = () => {
    const months = prompt('كم شهراً تريد تفعيل الاشتراك؟ (1-24)', '6');
    if (months && !isNaN(Number(months)) && Number(months) >= 1 && Number(months) <= 24) {
      // Calculate and show the end date
      const startDate = new Date();
      const endDate = new Date(startDate);
      endDate.setMonth(endDate.getMonth() + Number(months));

      const endDateFormatted = endDate.toLocaleDateString('ar-DZ', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });

      if (confirm(`هل أنت متأكد من تفعيل اشتراك هذا المصمم لمدة ${months} أشهر؟\n\nسيبدأ الاشتراك اليوم وينتهي في: ${endDateFormatted}`)) {
        console.log('Activating subscription for', months, 'months');
        router.post(`/admin/designers/${designer.id}/activate-subscription`, {
          months: Number(months),
          pricing_plan_id: null // Can be enhanced later to select pricing plan
        }, {
          onSuccess: () => {
            console.log('Subscription activated successfully for', months, 'months');
          },
          onError: (errors) => {
            console.error('Subscription activation failed:', errors);
          }
        });
      }
    } else if (months !== null) {
      alert('يرجى إدخال رقم صحيح بين 1 و 24');
    }
  };

  const handleDeactivateSubscription = () => {
    if (confirm('هل أنت متأكد من إلغاء تفعيل اشتراك هذا المصمم؟')) {
      router.post(`/admin/designers/${designer.id}/deactivate-subscription`);
    }
  };

  const handleExtendSubscription = () => {
    const months = prompt('كم شهراً تريد تمديد الاشتراك؟ (1-12)', '3');
    if (months && !isNaN(Number(months)) && Number(months) >= 1 && Number(months) <= 12) {
      if (confirm(`هل أنت متأكد من تمديد اشتراك هذا المصمم لمدة ${months} أشهر إضافية؟`)) {
        router.post(`/admin/designers/${designer.id}/extend-subscription`, {
          months: Number(months)
        });
      }
    } else if (months !== null) {
      alert('يرجى إدخال رقم صحيح بين 1 و 12');
    }
  };

  // Define columns for the rachmat data table
  const columns: ColumnDef<Rachma>[] = [
    {
      accessorKey: "title",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="اسم الرشمة" />
      ),
      cell: ({ row }) => {
        const rachma = row.original;
        return (
          <div className="text-right">
            <div className="font-medium text-sm lg:text-base">{rachma.title}</div>
            <div className="text-xs lg:text-sm text-muted-foreground">{rachma.category?.name}</div>
          </div>
        );
      },
    },
    {
      accessorKey: "orders_count",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="المبيعات" />
      ),
      cell: ({ row }) => (
        <span className="font-semibold text-blue-600 text-sm lg:text-base text-right block">{row.getValue("orders_count") || 0}</span>
      ),
    },
    {
      accessorKey: "price",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="السعر" />
      ),
      cell: ({ row }) => (
        <span className="font-semibold text-sm lg:text-base text-right block">{formatCurrency(row.getValue("price"))}</span>
      ),
    },
    {
      accessorKey: "average_rating",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="التقييم" />
      ),
      cell: ({ row }) => {
        const rating = row.getValue("average_rating");
        const numRating = typeof rating === 'number' ? rating : 0;
        
        if (!rating || isNaN(numRating) || numRating === 0) {
          return (
            <div className="flex items-center justify-end">
              <span className="text-xs lg:text-sm font-medium text-muted-foreground">لا توجد تقييمات</span>
            </div>
          );
        }

        return (
          <div className="flex items-center justify-end gap-1">
            <span className="text-xs lg:text-sm font-medium">{numRating.toFixed(1)}</span>
            <span className="text-yellow-400">★</span>
          </div>
        );
      },
    },
    {
      accessorKey: "created_at",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="تاريخ الإنشاء" />
      ),
      cell: ({ row }) => (
        <span className="text-xs lg:text-sm text-muted-foreground text-right block">
          {formatDate(row.getValue("created_at"))}
        </span>
      ),
    },
  ];

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'المصممين', href: '/admin/designers' },
        { title: designer.store_name, href: `/admin/designers/${designer.id}` }
      ]}
    >
      <Head title={`${designer.store_name} - Designer Details`} />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20" dir="rtl">
        <div className="p-6 space-y-8">
          <ErrorBoundary>
            {/* Enhanced Header with Better RTL Support */}
            <div className="relative">
              <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5 rounded-3xl"></div>
              <div className="relative p-6">
                <div className="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-6">
                  <div className="flex items-center gap-6">
                    <div className="w-16 h-16 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center shadow-xl">
                      <Crown className="w-8 h-8 text-primary-foreground" />
                    </div>
                    <div className="text-right">
                      <h1 className="text-5xl font-black bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                        {designer.store_name}
                      </h1>
                      <p className="text-xl text-muted-foreground mt-2">
                        تفاصيل متجر المصمم
                      </p>
                    </div>
                  </div>
                  <Link
                    href="/admin/designers"
                    className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-muted to-muted/80 hover:from-muted/80 hover:to-muted/60 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl"
                  >
                    <ArrowLeft className="w-5 h-5" />
                    <span className="font-semibold">العودة للمصممين</span>
                  </Link>
                </div>
              </div>
            </div>

            {/* Enhanced Subscription Management */}
            <Card className="transition-all duration-200 hover:shadow-md border border-border/50 shadow-sm bg-card/50 backdrop-blur-sm">
              <CardHeader className="pb-6 border-b border-border/50">
                <CardTitle className="text-xl lg:text-2xl font-semibold text-foreground flex items-center gap-3 text-right">
                  <Settings className="h-5 w-5 lg:h-6 lg:w-6" />
                  إدارة الاشتراك
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6 pt-6">
                {/* Current Status */}
                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-lg border border-border">
                  <div className="flex items-center gap-3">
                    <div className={`w-3 h-3 rounded-full ${
                      designer.subscription_status === 'active' ? 'bg-green-500' :
                      designer.subscription_status === 'pending' ? 'bg-yellow-500' : 'bg-red-500'
                    }`}></div>
                    <span className="font-medium text-sm lg:text-base text-foreground">الحالة الحالية:</span>
                  </div>
                  <Badge className={`${getStatusBadge(designer.subscription_status)} text-xs lg:text-sm border`}>
                    {designer.subscription_status === 'active' ? 'نشط' :
                     designer.subscription_status === 'pending' ? 'معلق' : 'منتهي'}
                  </Badge>
                </div>

                {/* Subscription Details */}
                {(designer.subscription_start_date || designer.subscription_end_date) && (
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {designer.subscription_start_date && (
                      <div className="flex items-center gap-2 text-sm lg:text-base p-3 bg-muted/30 rounded-lg border border-border/50">
                        <Calendar className="h-4 w-4 text-muted-foreground flex-shrink-0" />
                        <span className="text-muted-foreground">تاريخ البداية:</span>
                        <span className="font-medium text-foreground">{formatDate(designer.subscription_start_date)}</span>
                      </div>
                    )}
                    {designer.subscription_end_date && (
                      <div className="flex items-center gap-2 text-sm lg:text-base p-3 bg-muted/30 rounded-lg border border-border/50">
                        <Clock className="h-4 w-4 text-muted-foreground flex-shrink-0" />
                        <span className="text-muted-foreground">تاريخ الانتهاء:</span>
                        <span className="font-medium text-foreground">{formatDate(designer.subscription_end_date)}</span>
                      </div>
                    )}
                  </div>
                )}

                {/* Action Buttons */}
                <div className="flex flex-wrap gap-3">
                  {designer.subscription_status === 'pending' && (
                    <>
                      <Button
                        onClick={handleApproveSubscription}
                        className="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white text-sm lg:text-base"
                        disabled={processing}
                      >
                        <CheckCircle className="ml-2 h-4 w-4" />
                        الموافقة على الاشتراك
                      </Button>
                      <Button
                        onClick={handleRejectSubscription}
                        variant="outline"
                        className="text-red-600 dark:text-red-400 border-red-600 dark:border-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 text-sm lg:text-base"
                        disabled={processing}
                      >
                        <XCircle className="ml-2 h-4 w-4" />
                        رفض الاشتراك
                      </Button>
                    </>
                  )}

                  <Button
                    onClick={handleActivateSubscription}
                    className="bg-primary hover:bg-primary/90 text-primary-foreground text-sm lg:text-base"
                    disabled={processing}
                  >
                    <Plus className="ml-2 h-4 w-4" />
                    تفعيل اشتراك جديد
                  </Button>

                  {designer.subscription_status === 'active' && (
                    <>
                      <Button
                        onClick={handleExtendSubscription}
                        variant="outline"
                        className="text-blue-600 dark:text-blue-400 border-blue-600 dark:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/20 text-sm lg:text-base"
                      >
                        <Clock className="ml-2 h-4 w-4" />
                        تمديد الاشتراك
                      </Button>
                      <Button
                        onClick={handleDeactivateSubscription}
                        variant="outline"
                        className="text-red-600 dark:text-red-400 border-red-600 dark:border-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 text-sm lg:text-base"
                      >
                        <Minus className="ml-2 h-4 w-4" />
                        إلغاء التفعيل
                      </Button>
                    </>
                  )}
                </div>
              </CardContent>
            </Card>

           

            {/* Enhanced Statistics */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
              <Card className="group relative overflow-hidden border border-border bg-card shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div className="absolute inset-0 bg-gradient-to-br from-green-500/5 via-transparent to-green-500/5 dark:from-green-400/10 dark:to-green-400/5"></div>
                <CardHeader className="relative pb-3">
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-xs lg:text-sm font-bold text-muted-foreground uppercase tracking-wider text-right">إجمالي الأرباح</CardTitle>
                    <div className="w-8 h-8 lg:w-10 lg:h-10 bg-green-500 dark:bg-green-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                      <DollarSign className="w-4 h-4 lg:w-5 lg:h-5 text-white" />
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="relative pt-0">
                  <div className="text-xl lg:text-2xl xl:text-3xl font-black text-green-600 dark:text-green-400 text-right">
                    {formatCurrency(stats.totalEarnings)}
                  </div>
                  <p className="text-xs text-muted-foreground mt-1 text-right">إجمالي الأرباح</p>
                  <div className="mt-3 h-1 bg-green-500 dark:bg-green-600 rounded-full"></div>
                </CardContent>
              </Card>

              <Card className="group relative overflow-hidden border border-border bg-card shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div className="absolute inset-0 bg-gradient-to-br from-yellow-500/5 via-transparent to-yellow-500/5 dark:from-yellow-400/10 dark:to-yellow-400/5"></div>
                <CardHeader className="relative pb-3">
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-xs lg:text-sm font-bold text-muted-foreground uppercase tracking-wider text-right">الأرباح المعلقة</CardTitle>
                    <div className="w-8 h-8 lg:w-10 lg:h-10 bg-yellow-500 dark:bg-yellow-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                      <TrendingUp className="w-4 h-4 lg:w-5 lg:h-5 text-white" />
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="relative pt-0">
                  <div className="text-xl lg:text-2xl xl:text-3xl font-black text-yellow-600 dark:text-yellow-400 text-right">
                    {formatCurrency(stats.unpaidEarnings)}
                  </div>
                  <p className="text-xs text-muted-foreground mt-1 text-right">الأرباح غير المدفوعة</p>
                  <div className="mt-3 h-1 bg-yellow-500 dark:bg-yellow-600 rounded-full"></div>
                </CardContent>
              </Card>

              <Card className="group relative overflow-hidden border border-border bg-card shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 md:col-span-2 lg:col-span-1">
                <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-blue-500/5 dark:from-blue-400/10 dark:to-blue-400/5"></div>
                <CardHeader className="relative pb-3">
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-xs lg:text-sm font-bold text-muted-foreground uppercase tracking-wider text-right">إجمالي المبيعات</CardTitle>
                    <div className="w-8 h-8 lg:w-10 lg:h-10 bg-blue-500 dark:bg-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                      <Package className="w-4 h-4 lg:w-5 lg:h-5 text-white" />
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="relative pt-0">
                  <div className="text-xl lg:text-2xl xl:text-3xl font-black text-blue-600 dark:text-blue-400 text-right">
                    {stats.totalSales.toLocaleString('ar-DZ')}
                  </div>
                  <p className="text-xs text-muted-foreground mt-1 text-right">إجمالي المبيعات</p>
                  <div className="mt-3 h-1 bg-blue-500 dark:bg-blue-600 rounded-full"></div>
                </CardContent>
              </Card>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {/* Store Information */}
              <Card className="border border-border shadow-sm bg-card">
                <CardHeader className="border-b border-border">
                  <CardTitle className="text-lg lg:text-xl font-semibold text-right flex items-center gap-2 text-foreground">
                    <Store className="h-5 w-5" />
                    معلومات المتجر
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4 pt-6">
                  <div className="space-y-2">
                    <label className="text-xs lg:text-sm font-medium text-muted-foreground">اسم المتجر</label>
                    <p className="text-base lg:text-lg font-semibold text-right text-foreground">{designer.store_name}</p>
                  </div>

                  {designer.store_description && (
                    <div className="space-y-2">
                      <label className="text-xs lg:text-sm font-medium text-muted-foreground">وصف المتجر</label>
                      <p className="text-sm lg:text-base bg-muted/50 p-3 rounded-lg text-right leading-relaxed text-foreground">{designer.store_description}</p>
                    </div>
                  )}

                  <div className="space-y-2">
                    <label className="text-xs lg:text-sm font-medium text-muted-foreground">حالة الاشتراك</label>
                    <div className="text-right">
                      <Badge className={`${getSubscriptionBadge(designer.subscription_status)} text-xs lg:text-sm border`}>
                        {designer.subscription_status === 'active' ? 'نشط' :
                         designer.subscription_status === 'pending' ? 'معلق' :
                         designer.subscription_status === 'expired' ? 'منتهي' :
                         designer.subscription_status === 'rejected' ? 'مرفوض' : designer.subscription_status}
                      </Badge>
                    </div>
                  </div>

                  {designer.subscription_end_date && (
                    <div className="space-y-2">
                      <label className="text-xs lg:text-sm font-medium text-muted-foreground">تاريخ انتهاء الاشتراك</label>
                      <p className="text-sm lg:text-base text-right text-foreground">{formatDate(designer.subscription_end_date)}</p>
                    </div>
                  )}

                  

                  {designer.subscription_price && (
                    <div className="space-y-2">
                      <label className="text-xs lg:text-sm font-medium text-muted-foreground">سعر الاشتراك المدفوع</label>
                      <p className="text-base lg:text-lg font-semibold text-green-600 dark:text-green-400 text-right">{formatCurrency(designer.subscription_price)}</p>
                    </div>
                  )}

                  <div className="space-y-2">
                    <label className="text-xs lg:text-sm font-medium text-muted-foreground">تاريخ إنشاء المتجر</label>
                    <p className="text-sm lg:text-base text-right text-foreground">{formatDate(designer.created_at)}</p>
                  </div>
                </CardContent>
              </Card>

              {/* User Information */}
              <Card className="border border-border shadow-sm bg-card">
                <CardHeader className="border-b border-border">
                  <CardTitle className="text-lg lg:text-xl font-semibold text-right flex items-center gap-2 text-foreground">
                    <User className="h-5 w-5" />
                    معلومات المصمم
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4 pt-6">
                  <div className="space-y-2">
                    <label className="text-xs lg:text-sm font-medium text-muted-foreground">الاسم</label>
                    <p className="text-base lg:text-lg text-right text-foreground">{designer.user?.name || 'غير محدد'}</p>
                  </div>

                  <div className="space-y-2">
                    <label className="text-xs lg:text-sm font-medium text-muted-foreground">البريد الإلكتروني</label>
                    <p className="text-sm lg:text-base text-right break-all text-foreground">{designer.user?.email || 'غير محدد'}</p>
                  </div>

                  {designer.user?.phone && typeof designer.user.phone === 'string' && (
                    <div className="space-y-2">
                      <label className="text-xs lg:text-sm font-medium text-muted-foreground">رقم الهاتف</label>
                      <p className="text-sm lg:text-base text-right text-foreground">{designer.user.phone}</p>
                    </div>
                  )}

                  <div className="space-y-2">
                    <label className="text-xs lg:text-sm font-medium text-muted-foreground">تاريخ التسجيل</label>
                    <p className="text-sm lg:text-base text-right text-foreground">{designer.user?.created_at ? formatDate(designer.user.created_at) : 'غير محدد'}</p>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Earnings Information */}
            <Card className="border border-border shadow-sm bg-card">
              <CardHeader className="border-b border-border">
                <CardTitle className="text-lg lg:text-xl font-semibold text-right flex items-center gap-2 text-foreground">
                  <DollarSign className="h-5 w-5" />
                  معلومات الأرباح
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4 pt-6">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-2 p-4 bg-green-500/10 dark:bg-green-500/20 rounded-lg border border-green-500/20">
                    <label className="text-xs lg:text-sm font-medium text-green-700 dark:text-green-400">الأرباح المدفوعة</label>
                    <p className="text-base lg:text-lg font-semibold text-green-600 dark:text-green-400 text-right">{formatCurrency(designer.paid_earnings)}</p>
                  </div>
                  <div className="space-y-2 p-4 bg-blue-500/10 dark:bg-blue-500/20 rounded-lg border border-blue-500/20">
                    <label className="text-xs lg:text-sm font-medium text-blue-700 dark:text-blue-400">إجمالي الأرباح</label>
                    <p className="text-base lg:text-lg font-semibold text-blue-600 dark:text-blue-400 text-right">{formatCurrency(designer.earnings)}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Rachmat Data Table */}
            <Card className="border border-border shadow-sm bg-card">
              <CardHeader className="border-b border-border">
                <CardTitle className="text-lg lg:text-xl font-semibold text-right flex items-center gap-2 text-foreground">
                  <Package className="h-5 w-5" />
                  رشمات المصمم ({Array.isArray(rachmat) ? rachmat.length : 0})
                </CardTitle>
              </CardHeader>
              <CardContent className="pt-6">
                {Array.isArray(rachmat) && rachmat.length > 0 ? (
                  <div className="overflow-hidden rounded-lg border border-border">
                    <DataTable
                      columns={columns}
                      data={rachmat}
                      searchPlaceholder="البحث في الرشمات..."
                      searchColumn="title"
                    />
                  </div>
                ) : (
                  <div className="text-center py-12">
                    <div className="w-16 h-16 mx-auto mb-4 bg-muted/50 rounded-full flex items-center justify-center">
                      <Package className="w-8 h-8 text-muted-foreground" />
                    </div>
                    <h3 className="text-base lg:text-lg font-medium text-foreground mb-2">لا توجد رشمات</h3>
                    <p className="text-sm lg:text-base text-muted-foreground">لم يقم المصمم برفع أي رشمات بعد</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </ErrorBoundary>
        </div>
      </div>
    </AppLayout>
  );
}