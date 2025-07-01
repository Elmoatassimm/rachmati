import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Alert, AlertDescription } from '@/components/ui/alert';
import InputError from '@/components/input-error';
import { PricingPlan } from '@/types';
import {
  ArrowLeft,
  Save,
  DollarSign,
  Calendar,
  FileText,
  Eye,
  Package,
  AlertTriangle
} from 'lucide-react';

interface Props {
  pricingPlan: PricingPlan;
}

export default function Edit({ pricingPlan }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    name: pricingPlan.name,
    duration_months: pricingPlan.duration_months.toString(),
    price: pricingPlan.price.toString(),
    description: pricingPlan.description || '',
    is_active: pricingPlan.is_active,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/admin/pricing-plans/${pricingPlan.id}`);
  };

  const getDurationText = (months: number) => {
    if (!months) return '';
    if (months === 1) return 'شهر واحد';
    if (months === 2) return 'شهران';
    if (months <= 10) return months + ' أشهر';
    return months + ' شهر';
  };

  const formatCurrency = (amount: number) => {
    if (!amount) return '';
    return new Intl.NumberFormat('ar-DZ').format(amount) + ' دج';
  };

  return (
    <AppLayout>
      <Head title={`تحرير خطة التسعير: ${pricingPlan.name}`} />
      
      <div className="space-y-8 p-8">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-4xl font-bold text-foreground">تحرير خطة التسعير</h1>
            <p className="text-xl text-muted-foreground mt-2">
              تحديث تفاصيل خطة: {pricingPlan.name}
            </p>
          </div>
          <div className="flex gap-3">
            <Link href={`/admin/pricing-plans/${pricingPlan.id}`}>
              <Button variant="outline" className="text-lg px-6 py-3 h-auto">
                <Eye className="mr-2 h-5 w-5" />
                عرض التفاصيل
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

       

        <div className="grid gap-8 lg:grid-cols-2">
          {/* Form Card */}
          <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm">
            <CardHeader className="pb-6">
              <CardTitle className="text-2xl font-semibold text-foreground flex items-center gap-3">
                <Package className="h-6 w-6" />
                تفاصيل الخطة
              </CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-6">
                <div className="space-y-2">
                  <Label htmlFor="name" className="text-base font-medium">
                    اسم الخطة *
                  </Label>
                  <Input
                    id="name"
                    type="text"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="مثال: الخطة الشهرية"
                    className="h-12 text-base"
                    required
                  />
                  <InputError message={errors.name} />
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                  <div className="space-y-2">
                    <Label htmlFor="duration_months" className="text-base font-medium">
                      المدة (بالأشهر) *
                    </Label>
                    <div className="relative">
                      <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                      <Input
                        id="duration_months"
                        type="number"
                        min="1"
                        max="24"
                        value={data.duration_months}
                        onChange={(e) => setData('duration_months', e.target.value)}
                        placeholder="1"
                        className="h-12 text-base pl-10"
                        required
                      />
                    </div>
                    <InputError message={errors.duration_months} />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="price" className="text-base font-medium">
                      السعر (دينار جزائري) *
                    </Label>
                    <div className="relative">
                      <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                      <Input
                        id="price"
                        type="number"
                        min="0"
                        step="0.01"
                        value={data.price}
                        onChange={(e) => setData('price', e.target.value)}
                        placeholder="2000.00"
                        className="h-12 text-base pl-10"
                        required
                      />
                    </div>
                    <InputError message={errors.price} />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description" className="text-base font-medium">
                    الوصف
                  </Label>
                  <div className="relative">
                    <FileText className="absolute left-3 top-3 h-5 w-5 text-muted-foreground" />
                    <Textarea
                      id="description"
                      value={data.description}
                      onChange={(e) => setData('description', e.target.value)}
                      placeholder="وصف مختصر للخطة وما تتضمنه..."
                      className="min-h-[100px] text-base pl-10 resize-none"
                      rows={4}
                    />
                  </div>
                  <InputError message={errors.description} />
                </div>

                

                <div className="flex gap-4 pt-6">
                  <Button
                    type="submit"
                    disabled={processing}
                    className="flex-1 bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-200 text-lg px-8 py-6 h-auto"
                  >
                    <Save className="mr-2 h-5 w-5" />
                    {processing ? 'جاري الحفظ...' : 'حفظ التغييرات'}
                  </Button>
                  <Link href="/admin/pricing-plans">
                    <Button
                      type="button"
                      variant="outline"
                      className="text-lg px-8 py-6 h-auto"
                    >
                      إلغاء
                    </Button>
                  </Link>
                </div>
              </form>
            </CardContent>
          </Card>

          {/* Preview Card */}
          <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm">
            <CardHeader className="pb-6">
              <CardTitle className="text-2xl font-semibold text-foreground flex items-center gap-3">
                <Eye className="h-6 w-6" />
                معاينة الخطة المحدثة
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-6">
                <div className="p-6 border-2 border-dashed border-muted-foreground/20 rounded-lg bg-gradient-to-br from-primary/5 to-primary/10">
                  <div className="text-center space-y-4">
                    <h3 className="text-2xl font-bold text-foreground">
                      {data.name || 'اسم الخطة'}
                    </h3>
                    
                    <div className="text-4xl font-bold text-primary">
                      {data.price ? formatCurrency(Number(data.price)) : '0 دج'}
                    </div>
                    
                    <div className="text-lg text-muted-foreground">
                      {data.duration_months ? getDurationText(Number(data.duration_months)) : 'المدة'}
                    </div>
                    
                    {data.description && (
                      <p className="text-base text-muted-foreground mt-4 leading-relaxed">
                        {data.description}
                      </p>
                    )}
                    
                    <div className="pt-4">
                      <div className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                        data.is_active 
                          ? 'bg-green-100 text-green-800' 
                          : 'bg-red-100 text-red-800'
                      }`}>
                        {data.is_active ? 'نشط' : 'غير نشط'}
                      </div>
                    </div>
                  </div>
                </div>

                <div className="space-y-3 text-sm text-muted-foreground">
                  <p className="flex items-center gap-2">
                    <span className="w-2 h-2 bg-primary rounded-full"></span>
                    التغييرات ستطبق فوراً بعد الحفظ
                  </p>
                  <p className="flex items-center gap-2">
                    <span className="w-2 h-2 bg-primary rounded-full"></span>
                    الاشتراكات الحالية لن تتأثر بالتغييرات
                  </p>
                  <p className="flex items-center gap-2">
                    <span className="w-2 h-2 bg-primary rounded-full"></span>
                    الخطط غير النشطة لن تظهر للمصممين الجدد
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
