import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import InputError from '@/components/input-error';
import { AdminPaymentInfo } from '@/types';
import {
  ArrowLeft,
  Save,
  X,
  Wallet,
  AlertTriangle,
  CreditCard,
  Phone,
  MapPin,
  Key,
  Edit3
} from 'lucide-react';

interface Props {
  paymentInfo: AdminPaymentInfo;
}

export default function Edit({ paymentInfo }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    ccp_number: paymentInfo.ccp_number || '',
    ccp_key: paymentInfo.ccp_key || '',
    nom: paymentInfo.nom || '',
    adress: paymentInfo.adress || '',
    baridimob: paymentInfo.baridimob || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/admin/payment-info/${paymentInfo.id}`);
  };

  const handleCancel = () => {
    window.history.back();
  };

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'معلومات الدفع', href: '/admin/payment-info' },
        { title: 'تعديل معلومات الدفع', href: `/admin/payment-info/${paymentInfo.id}/edit` }
      ]}
    >
      <Head title="تعديل معلومات الدفع" />

      <div className="space-y-8 p-6">
        {/* Header Section */}
        <div className="relative">
          <div className="absolute inset-0 bg-gradient-to-r from-purple-600/20 via-blue-600/20 to-indigo-600/20 rounded-3xl"></div>
          <div className="relative bg-card/80 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl">
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
              <div className="space-y-2">
                <div className="flex items-center gap-3">
                  <div className="p-3 bg-gradient-to-br from-purple-500 to-blue-600 rounded-2xl shadow-lg">
                    <Edit3 className="h-8 w-8 text-white" />
                  </div>
                  <div>
                    <h1 className="text-4xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent">
                      تعديل معلومات الدفع
                    </h1>
                    <p className="text-2xl text-muted-foreground mt-3 leading-relaxed font-medium">
                      تعديل معلومات الدفع في النظام
                    </p>
                  </div>
                </div>
              </div>
              
              <div className="flex items-center gap-3">
                <Button
                  onClick={handleCancel}
                  variant="outline"
                  className="gap-2 px-6 py-3 text-base"
                >
                  <ArrowLeft className="h-5 w-5" />
                  العودة
                </Button>
              </div>
            </div>
          </div>
        </div>

        {/* Form Alert */}
        <Alert className="bg-amber-50 border-amber-200">
          <AlertTriangle className="h-4 w-4 text-amber-600" />
          <AlertDescription className="text-amber-800">
            تأكد من صحة المعلومات المدخلة قبل الحفظ.
          </AlertDescription>
        </Alert>

        {/* Form Card */}
        <Card className="border-0 shadow-xl bg-card/50 backdrop-blur-sm">
          <CardHeader className="border-b border-border/50 bg-muted/30">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-primary/10 rounded-lg">
                <CreditCard className="h-5 w-5 text-primary" />
              </div>
              <div>
                <CardTitle className="text-xl font-bold">معلومات الدفع</CardTitle>
                <p className="text-sm text-muted-foreground mt-1">
                  تعديل معلومات الدفع المطلوبة
                </p>
              </div>
            </div>
          </CardHeader>

          <CardContent className="p-8">
            <form onSubmit={handleSubmit} className="space-y-8">
              {/* CCP Information */}
              <div className="space-y-4">
                <div className="flex items-center gap-2 mb-4">
                  <CreditCard className="h-5 w-5 text-primary" />
                  <h3 className="text-lg font-semibold">معلومات CCP</h3>
                </div>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="ccp_number" className="text-base font-medium">
                      رقم CCP
                    </Label>
                    <Input
                      id="ccp_number"
                      type="text"
                      value={data.ccp_number}
                      onChange={(e) => setData('ccp_number', e.target.value)}
                      placeholder="أدخل رقم CCP"
                      className="mt-2"
                    />
                    <InputError message={errors.ccp_number} className="mt-2" />
                  </div>

                  <div>
                    <Label htmlFor="ccp_key" className="text-base font-medium">
                      مفتاح CCP
                    </Label>
                    <Input
                      id="ccp_key"
                      type="text"
                      value={data.ccp_key}
                      onChange={(e) => setData('ccp_key', e.target.value)}
                      placeholder="أدخل مفتاح CCP"
                      className="mt-2"
                    />
                    <InputError message={errors.ccp_key} className="mt-2" />
                  </div>
                </div>
              </div>

              {/* Account Holder Information */}
              <div className="space-y-4">
                <div className="flex items-center gap-2 mb-4">
                  <Key className="h-5 w-5 text-primary" />
                  <h3 className="text-lg font-semibold">معلومات صاحب الحساب</h3>
                </div>
                
                <div className="grid gap-6">
                  <div>
                    <Label htmlFor="nom" className="text-base font-medium">
                      اسم صاحب الحساب
                    </Label>
                    <Input
                      id="nom"
                      type="text"
                      value={data.nom}
                      onChange={(e) => setData('nom', e.target.value)}
                      placeholder="أدخل اسم صاحب الحساب"
                      className="mt-2"
                    />
                    <InputError message={errors.nom} className="mt-2" />
                  </div>

                  <div>
                    <Label htmlFor="adress" className="text-base font-medium">
                      العنوان
                    </Label>
                    <Textarea
                      id="adress"
                      value={data.adress}
                      onChange={(e) => setData('adress', e.target.value)}
                      placeholder="أدخل العنوان"
                      className="mt-2"
                      rows={3}
                    />
                    <InputError message={errors.adress} className="mt-2" />
                  </div>
                </div>
              </div>

              {/* BaridiMob Information */}
              <div className="space-y-4">
                <div className="flex items-center gap-2 mb-4">
                  <Phone className="h-5 w-5 text-primary" />
                  <h3 className="text-lg font-semibold">معلومات BaridiMob</h3>
                </div>
                
                <div className="grid gap-6">
                  <div>
                    <Label htmlFor="baridimob" className="text-base font-medium">
                      رقم BaridiMob
                    </Label>
                    <Input
                      id="baridimob"
                      type="text"
                      value={data.baridimob}
                      onChange={(e) => setData('baridimob', e.target.value)}
                      placeholder="أدخل رقم BaridiMob"
                      className="mt-2"
                    />
                    <InputError message={errors.baridimob} className="mt-2" />
                  </div>
                </div>
              </div>

              {/* Form Actions */}
              <div className="flex flex-col sm:flex-row gap-4 pt-6 border-t border-border/50">
                <Button
                  type="submit"
                  disabled={processing}
                  className="flex-1 gap-2 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white py-3 text-base font-medium"
                >
                  <Save className="h-5 w-5" />
                  {processing ? 'جاري الحفظ...' : 'حفظ التعديلات'}
                </Button>
                
                <Button
                  type="button"
                  variant="outline"
                  onClick={handleCancel}
                  className="flex-1 gap-2 py-3 text-base"
                >
                  <X className="h-5 w-5" />
                  إلغاء
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
