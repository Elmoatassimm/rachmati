import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PricingPlan } from '@/types';
import {
  ArrowLeft,
  Edit,
  DollarSign,
  Calendar,
  FileText,
  Users,
  TrendingUp,
  Package,
  ToggleLeft,
  ToggleRight,
  Trash2
} from 'lucide-react';

interface Stats {
  totalSubscriptions: number;
  activeSubscriptions: number;
  totalRevenue: number;
}

interface Props {
  pricingPlan: PricingPlan;
  stats: Stats;
}

export default function Show({ pricingPlan, stats }: Props) {
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ').format(amount) + ' دج';
  };

  const getDurationText = (months: number) => {
    if (months === 1) return 'شهر واحد';
    if (months === 2) return 'شهران';
    if (months <= 10) return months + ' أشهر';
    return months + ' شهر';
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ar-DZ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const handleToggleStatus = () => {
    const action = pricingPlan.is_active ? 'إلغاء تفعيل' : 'تفعيل';
    const confirmMessage = `هل أنت متأكد من ${action} خطة "${pricingPlan.name}"؟`;

    if (confirm(confirmMessage)) {
      router.post(`/admin/pricing-plans/${pricingPlan.id}/toggle-status`, {}, {
        preserveScroll: true, // Keep the current scroll position
        onSuccess: () => {
          // The page will automatically refresh with updated data while preserving scroll position
        },
        onError: (errors) => {
          console.error('Toggle status failed:', errors);
          alert('حدث خطأ أثناء تغيير حالة الخطة');
        }
      });
    }
  };

  const handleDelete = () => {
    if (confirm(`هل أنت متأكد من حذف خطة التسعير "${pricingPlan.name}"؟`)) {
      router.delete(`/admin/pricing-plans/${pricingPlan.id}`);
    }
  };

  return (
    <AppLayout>
      <Head title={`خطة التسعير: ${pricingPlan.name}`} />
      
      <div className="space-y-8 p-8">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-4xl font-bold text-foreground">{pricingPlan.name}</h1>
            <p className="text-xl text-muted-foreground mt-2">
              تفاصيل خطة التسعير والإحصائيات
            </p>
          </div>
          <div className="flex gap-3">
            <Link href={`/admin/pricing-plans/${pricingPlan.id}/edit`}>
              <Button className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-200 text-lg px-6 py-3 h-auto">
                <Edit className="mr-2 h-5 w-5" />
                تحرير الخطة
              </Button>
            </Link>
            <Link href="/admin/pricing-plans">
              <Button variant="outline" className="text-lg px-6 py-3 h-auto">
                <ArrowLeft className="mr-2 h-5 w-5" />
                العودة للقائمة
              </Button>
            </Link>
          </div>
        </div>

        {/* Statistics Cards */}
        <div className="grid gap-6 md:grid-cols-2">
          <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
              <CardTitle className="text-lg font-medium text-blue-700 dark:text-blue-300">
                إجمالي الاشتراكات
              </CardTitle>
              <Users className="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold text-blue-800 dark:text-blue-200">
                {stats.totalSubscriptions}
              </div>
            </CardContent>
          </Card>

          <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm bg-gradient-to-br from-green-50 to-green-100 dark:from-green-950 dark:to-green-900">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
              <CardTitle className="text-lg font-medium text-green-700 dark:text-green-300">
                الاشتراكات النشطة
              </CardTitle>
              <TrendingUp className="h-6 w-6 text-green-600 dark:text-green-400" />
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold text-green-800 dark:text-green-200">
                {stats.activeSubscriptions}
              </div>
            </CardContent>
          </Card>


        </div>

        <div className="grid gap-8 lg:grid-cols-2">
          {/* Plan Details Card */}
          <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm">
            <CardHeader className="pb-6">
              <CardTitle className="text-2xl font-semibold text-foreground flex items-center gap-3">
                <Package className="h-6 w-6" />
                تفاصيل الخطة
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="grid gap-6">
                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-lg">
                  <div className="flex items-center gap-3">
                    <Package className="h-5 w-5 text-muted-foreground" />
                    <span className="text-base font-medium">اسم الخطة</span>
                  </div>
                  <span className="text-lg font-semibold">{pricingPlan.name}</span>
                </div>

                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-lg">
                  <div className="flex items-center gap-3">
                    <Calendar className="h-5 w-5 text-muted-foreground" />
                    <span className="text-base font-medium">المدة</span>
                  </div>
                  <span className="text-lg font-semibold">{getDurationText(pricingPlan.duration_months)}</span>
                </div>

                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-lg">
                  <div className="flex items-center gap-3">
                    <DollarSign className="h-5 w-5 text-muted-foreground" />
                    <span className="text-base font-medium">السعر</span>
                  </div>
                  <span className="text-lg font-semibold text-primary">{formatCurrency(pricingPlan.price)}</span>
                </div>

                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-lg">
                  <div className="flex items-center gap-3">
                    <span className="text-base font-medium">الحالة</span>
                  </div>
                  <Badge className={pricingPlan.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}>
                    {pricingPlan.is_active ? 'نشط' : 'غير نشط'}
                  </Badge>
                </div>

                {pricingPlan.description && (
                  <div className="p-4 bg-muted/50 rounded-lg">
                    <div className="flex items-center gap-3 mb-3">
                      <FileText className="h-5 w-5 text-muted-foreground" />
                      <span className="text-base font-medium">الوصف</span>
                    </div>
                    <p className="text-base text-muted-foreground leading-relaxed">
                      {pricingPlan.description}
                    </p>
                  </div>
                )}

                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-lg">
                  <div className="flex items-center gap-3">
                    <span className="text-base font-medium">تاريخ الإنشاء</span>
                  </div>
                  <span className="text-base text-muted-foreground">{formatDate(pricingPlan.created_at)}</span>
                </div>

                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-lg">
                  <div className="flex items-center gap-3">
                    <span className="text-base font-medium">آخر تحديث</span>
                  </div>
                  <span className="text-base text-muted-foreground">{formatDate(pricingPlan.updated_at)}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Actions Card */}
          <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm">
            <CardHeader className="pb-6">
              <CardTitle className="text-2xl font-semibold text-foreground">الإجراءات</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <Button
                onClick={handleToggleStatus}
                className={`w-full text-lg px-6 py-4 h-auto ${
                  pricingPlan.is_active 
                    ? 'bg-red-600 hover:bg-red-700 text-white' 
                    : 'bg-green-600 hover:bg-green-700 text-white'
                }`}
              >
                {pricingPlan.is_active ? (
                  <>
                    <ToggleLeft className="mr-2 h-5 w-5" />
                    إلغاء تفعيل الخطة
                  </>
                ) : (
                  <>
                    <ToggleRight className="mr-2 h-5 w-5" />
                    تفعيل الخطة
                  </>
                )}
              </Button>

              <Link href={`/admin/pricing-plans/${pricingPlan.id}/edit`}>
                <Button variant="outline" className="w-full text-lg px-6 py-4 h-auto">
                  <Edit className="mr-2 h-5 w-5" />
                  تحرير تفاصيل الخطة
                </Button>
              </Link>

              <Button
                onClick={handleDelete}
                variant="destructive"
                className="w-full text-lg px-6 py-4 h-auto"
              >
                <Trash2 className="mr-2 h-5 w-5" />
                حذف الخطة
              </Button>

              <div className="mt-6 p-4 bg-muted/50 rounded-lg">
                <h4 className="font-medium text-base mb-2">ملاحظات مهمة:</h4>
                <ul className="text-sm text-muted-foreground space-y-1">
                  <li>• إلغاء تفعيل الخطة سيمنع المصممين الجدد من اختيارها</li>
                  <li>• الاشتراكات الحالية لن تتأثر بإلغاء التفعيل</li>
                  <li>• لا يمكن حذف الخطة إذا كانت مرتبطة باشتراكات نشطة</li>
                </ul>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
