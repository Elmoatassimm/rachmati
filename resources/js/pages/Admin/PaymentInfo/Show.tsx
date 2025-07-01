import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AdminPaymentInfo } from '@/types';
import {
  ArrowLeft,
  Edit,
  Trash2,
  Wallet,
  CreditCard,
  Phone,
  MapPin,
  Key,
  Eye,
  Calendar,
  User
} from 'lucide-react';

interface Props {
  paymentInfo: AdminPaymentInfo;
}

export default function Show({ paymentInfo }: Props) {
  const handleEdit = () => {
    router.visit(`/admin/payment-info/${paymentInfo.id}/edit`);
  };

  const handleDelete = () => {
    if (confirm('هل أنت متأكد من حذف معلومات الدفع هذه؟ لا يمكن التراجع عن هذا الإجراء.')) {
      router.delete(`/admin/payment-info/${paymentInfo.id}`, {
        preserveScroll: false,
        onSuccess: () => {
          router.visit('/admin/payment-info');
        },
      });
    }
  };

  const handleBack = () => {
    router.visit('/admin/payment-info');
  };

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'معلومات الدفع', href: '/admin/payment-info' },
        { title: 'تفاصيل معلومات الدفع', href: `/admin/payment-info/${paymentInfo.id}` }
      ]}
    >
      <Head title="تفاصيل معلومات الدفع" />

      <div className="space-y-8 p-6">
        {/* Header Section */}
        <div className="relative">
          <div className="absolute inset-0 bg-gradient-to-r from-purple-600/20 via-blue-600/20 to-indigo-600/20 rounded-3xl"></div>
          <div className="relative bg-card/80 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl">
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
              <div className="space-y-2">
                <div className="flex items-center gap-3">
                  <div className="p-3 bg-gradient-to-br from-purple-500 to-blue-600 rounded-2xl shadow-lg">
                    <Eye className="h-8 w-8 text-white" />
                  </div>
                  <div>
                    <h1 className="text-4xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent">
                      تفاصيل معلومات الدفع
                    </h1>
                    <p className="text-2xl text-muted-foreground mt-3 leading-relaxed font-medium">
                      عرض تفاصيل معلومات الدفع
                    </p>
                  </div>
                </div>
              </div>
              
              <div className="flex items-center gap-3">
                <Button
                  onClick={handleBack}
                  variant="outline"
                  className="gap-2 px-6 py-3 text-base"
                >
                  <ArrowLeft className="h-5 w-5" />
                  العودة
                </Button>

                <Button
                  onClick={handleEdit}
                  className="gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 px-6 py-3 text-base"
                >
                  <Edit className="h-5 w-5" />
                  تعديل
                </Button>

                <Button
                  onClick={handleDelete}
                  variant="destructive"
                  className="gap-2 px-6 py-3 text-base"
                >
                  <Trash2 className="h-5 w-5" />
                  حذف
                </Button>
              </div>
            </div>
          </div>
        </div>



        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Information */}
          <div className="lg:col-span-2 space-y-6">


            {/* CCP Information */}
            <Card className="border-0 shadow-xl bg-card/50 backdrop-blur-sm">
              <CardHeader className="border-b border-border/50 bg-muted/30">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-primary/10 rounded-lg">
                    <CreditCard className="h-5 w-5 text-primary" />
                  </div>
                  <CardTitle className="text-xl font-bold">معلومات CCP</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="p-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label className="text-sm font-medium text-muted-foreground">رقم CCP</Label>
                    <p className="text-lg font-mono bg-muted/50 p-3 rounded-lg">
                      {paymentInfo.ccp_number || 'غير محدد'}
                    </p>
                  </div>
                  <div className="space-y-2">
                    <Label className="text-sm font-medium text-muted-foreground">مفتاح CCP</Label>
                    <p className="text-lg font-mono bg-muted/50 p-3 rounded-lg">
                      {paymentInfo.masked_ccp_key || 'غير محدد'}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Account Holder Information */}
            <Card className="border-0 shadow-xl bg-card/50 backdrop-blur-sm">
              <CardHeader className="border-b border-border/50 bg-muted/30">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-primary/10 rounded-lg">
                    <User className="h-5 w-5 text-primary" />
                  </div>
                  <CardTitle className="text-xl font-bold">معلومات صاحب الحساب</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="p-6">
                <div className="space-y-6">
                  <div className="space-y-2">
                    <Label className="text-sm font-medium text-muted-foreground">اسم صاحب الحساب</Label>
                    <p className="text-lg font-semibold">{paymentInfo.nom || 'غير محدد'}</p>
                  </div>
                  <div className="space-y-2">
                    <Label className="text-sm font-medium text-muted-foreground">العنوان</Label>
                    <p className="text-lg bg-muted/50 p-3 rounded-lg whitespace-pre-wrap">
                      {paymentInfo.adress || 'غير محدد'}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* BaridiMob Information */}
            <Card className="border-0 shadow-xl bg-card/50 backdrop-blur-sm">
              <CardHeader className="border-b border-border/50 bg-muted/30">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-primary/10 rounded-lg">
                    <Phone className="h-5 w-5 text-primary" />
                  </div>
                  <CardTitle className="text-xl font-bold">معلومات BaridiMob</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="p-6">
                <div className="space-y-2">
                  <Label className="text-sm font-medium text-muted-foreground">رقم BaridiMob</Label>
                  <p className="text-lg font-mono bg-muted/50 p-3 rounded-lg">
                    {paymentInfo.baridimob || 'غير محدد'}
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Sidebar Information */}
          <div className="space-y-6">
            {/* Timestamps */}
            <Card className="border-0 shadow-xl bg-card/50 backdrop-blur-sm">
              <CardHeader className="border-b border-border/50 bg-muted/30">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-primary/10 rounded-lg">
                    <Calendar className="h-5 w-5 text-primary" />
                  </div>
                  <CardTitle className="text-lg font-bold">التواريخ</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="p-6 space-y-4">
                <div className="space-y-2">
                  <Label className="text-sm font-medium text-muted-foreground">تاريخ الإنشاء</Label>
                  <p className="text-sm font-semibold">
                    {new Date(paymentInfo.created_at).toLocaleDateString('ar-DZ', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </p>
                </div>
                <div className="space-y-2">
                  <Label className="text-sm font-medium text-muted-foreground">آخر تحديث</Label>
                  <p className="text-sm font-semibold">
                    {new Date(paymentInfo.updated_at).toLocaleDateString('ar-DZ', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </p>
                </div>
              </CardContent>
            </Card>

            {/* Quick Actions */}
            <Card className="border-0 shadow-xl bg-card/50 backdrop-blur-sm">
              <CardHeader className="border-b border-border/50 bg-muted/30">
                <CardTitle className="text-lg font-bold">إجراءات سريعة</CardTitle>
              </CardHeader>
              <CardContent className="p-6 space-y-3">
                <Button
                  onClick={handleEdit}
                  className="w-full gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
                >
                  <Edit className="h-4 w-4" />
                  تعديل المعلومات
                </Button>
                
                <Button
                  onClick={handleDelete}
                  variant="destructive"
                  className="w-full gap-2"
                >
                  <Trash2 className="h-4 w-4" />
                  حذف المعلومات
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
