import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, Upload, CreditCard, DollarSign, Store, FileImage } from 'lucide-react';
import { Designer, PricingPlan } from '@/types';

interface Props {
    designer?: Designer;
    pricingPlans: PricingPlan[];
}

export default function SubscriptionRequest({ designer, pricingPlans }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        months: '1',
        pricing_plan_id: '',
        payment_proof: null as File | null,
        store_name: designer?.store_name || '',
        store_description: designer?.store_description || '',
    });

    const selectedPlan = pricingPlans.find(plan => plan.id.toString() === data.pricing_plan_id);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('months', data.months);
        if (data.pricing_plan_id) {
            formData.append('pricing_plan_id', data.pricing_plan_id);
        }
        if (data.payment_proof) {
            formData.append('payment_proof', data.payment_proof);
        }
        if (data.store_name) {
            formData.append('store_name', data.store_name);
        }
        if (data.store_description) {
            formData.append('store_description', data.store_description);
        }

        post('/designer/subscription/request', {
            forceFormData: true,
            onSuccess: () => reset(),
        });
    };

    const calculateTotal = () => {
        const months = parseInt(data.months);
        if (selectedPlan) {
            return selectedPlan.price * months;
        }
        return 2000 * months;
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'لوحة المصمم', href: '/designer/dashboard' },
                { title: designer ? 'تجديد الاشتراك' : 'طلب اشتراك جديد', href: '/designer/subscription/request' }
            ]}
        >
            <Head title={designer ? 'تجديد الاشتراك' : 'طلب اشتراك جديد'} />

            <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
                <div className="p-8 space-y-10">
                    {/* Revolutionary Header */}
                    <div className="relative">
                        <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5 rounded-3xl"></div>
                        <div className="relative p-8">
                            <div className="text-center mb-8">
                                <div className="mx-auto w-20 h-20 bg-gradient-to-br from-primary to-primary/70 rounded-full flex items-center justify-center mb-6 shadow-xl">
                                    <CreditCard className="w-10 h-10 text-primary-foreground" />
                                </div>
                                <h1 className="text-5xl font-black bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                                    {designer ? 'تجديد الاشتراك' : 'طلب اشتراك جديد'}
                                </h1>
                                <p className="text-xl text-muted-foreground mt-4 max-w-3xl mx-auto leading-relaxed">
                                    اختر مدة الاشتراك وارفع إثبات الدفع لتفعيل حسابك كمصمم
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-10">
                        {/* Subscription Form */}
                        <div className="lg:col-span-2">
                            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl">
                                <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
                                <CardHeader className="relative pb-6">
                                    <CardTitle className="text-2xl font-bold text-foreground flex items-center gap-4">
                                        <div className="w-12 h-12 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center shadow-lg">
                                            <CreditCard className="w-6 h-6 text-primary-foreground" />
                                        </div>
                                        تفاصيل الاشتراك
                                    </CardTitle>
                                    <CardDescription className="text-lg text-muted-foreground">
                                        املأ البيانات المطلوبة لإرسال طلب الاشتراك
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="relative">
                                    <form onSubmit={handleSubmit} className="space-y-8">
                                        {!designer && (
                                            <div className="space-y-6 p-6 bg-gradient-to-r from-blue-500/10 to-blue-500/5 rounded-2xl border border-blue-200/50">
                                                <div className="flex items-center gap-4 mb-4">
                                                    <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                                        <Store className="w-5 h-5 text-white" />
                                                    </div>
                                                    <h3 className="font-bold text-xl text-blue-900">معلومات المتجر</h3>
                                                </div>
                                                
                                                <div className="space-y-4">
                                                    <div>
                                                        <Label htmlFor="store_name" className="text-base font-semibold">اسم المتجر *</Label>
                                                        <Input
                                                            id="store_name"
                                                            value={data.store_name}
                                                            onChange={(e) => setData('store_name', e.target.value)}
                                                            placeholder="أدخل اسم متجرك"
                                                            className="mt-2 text-base py-3"
                                                        />
                                                        {errors.store_name && (
                                                            <p className="mt-2 text-sm text-red-600">{errors.store_name}</p>
                                                        )}
                                                    </div>

                                                    <div>
                                                        <Label htmlFor="store_description" className="text-base font-semibold">وصف المتجر</Label>
                                                        <Textarea
                                                            id="store_description"
                                                            value={data.store_description}
                                                            onChange={(e) => setData('store_description', e.target.value)}
                                                            placeholder="وصف مختصر عن متجرك ونوع التصاميم"
                                                            className="mt-2 text-base"
                                                            rows={4}
                                                        />
                                                        {errors.store_description && (
                                                            <p className="mt-2 text-sm text-red-600">{errors.store_description}</p>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        )}

                                        {/* Pricing Plans */}
                                        {pricingPlans.length > 0 && (
                                            <div className="space-y-4">
                                                <Label className="text-lg font-bold">اختر الخطة</Label>
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                                    {pricingPlans.map((plan) => (
                                                        <div
                                                            key={plan.id}
                                                            className={`group relative p-6 border-2 rounded-2xl cursor-pointer transition-all duration-300 ${
                                                                data.pricing_plan_id === plan.id.toString()
                                                                    ? 'border-primary bg-gradient-to-br from-primary/10 to-primary/5 shadow-lg'
                                                                    : 'border-border hover:border-primary/50 hover:shadow-md'
                                                            }`}
                                                            onClick={() => setData('pricing_plan_id', plan.id.toString())}
                                                        >
                                                            <div className="flex items-center justify-between mb-4">
                                                                <h4 className="font-bold text-lg">{plan.name}</h4>
                                                                <Badge className="bg-gradient-to-r from-primary to-primary/80 text-primary-foreground px-3 py-1">
                                                                    {plan.duration_months} شهر
                                                                </Badge>
                                                            </div>
                                                            <p className="text-3xl font-black bg-gradient-to-r from-primary to-primary/80 bg-clip-text text-transparent mb-2">
                                                                {plan.formatted_price}
                                                            </p>
                                                            <p className="text-base text-muted-foreground leading-relaxed">
                                                                {plan.description}
                                                            </p>
                                                            {data.pricing_plan_id === plan.id.toString() && (
                                                                <div className="absolute top-4 right-4">
                                                                    <CheckCircle className="w-6 h-6 text-primary" />
                                                                </div>
                                                            )}
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {/* Custom Duration */}
                                        <div className="space-y-4">
                                            <Label htmlFor="months" className="text-lg font-bold">عدد الأشهر *</Label>
                                            <Select value={data.months} onValueChange={(value) => setData('months', value)}>
                                                <SelectTrigger className="text-base py-6">
                                                    <SelectValue placeholder="اختر عدد الأشهر" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {Array.from({ length: 24 }, (_, i) => i + 1).map((month) => (
                                                        <SelectItem key={month} value={month.toString()} className="text-base">
                                                            {month} {month === 1 ? 'شهر' : 'أشهر'}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.months && (
                                                <p className="mt-2 text-sm text-red-600">{errors.months}</p>
                                            )}
                                        </div>

                                        {/* Payment Proof Upload */}
                                        <div className="space-y-4">
                                            <Label htmlFor="payment_proof" className="text-lg font-bold">إثبات الدفع *</Label>
                                            <div className="mt-4 flex justify-center rounded-2xl border-2 border-dashed border-primary/30 bg-gradient-to-br from-primary/5 to-primary/10 px-8 py-12 transition-all duration-300 hover:border-primary/50 hover:bg-primary/10">
                                                <div className="text-center">
                                                    <div className="mx-auto w-16 h-16 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                                                        <Upload className="w-8 h-8 text-primary-foreground" />
                                                    </div>
                                                    <div className="mt-4 flex text-lg leading-6 text-muted-foreground">
                                                        <label
                                                            htmlFor="payment_proof"
                                                            className="relative cursor-pointer rounded-xl bg-gradient-to-r from-primary to-primary/80 px-6 py-3 font-bold text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                                                        >
                                                            <span>ارفع صورة إثبات الدفع</span>
                                                            <input
                                                                id="payment_proof"
                                                                name="payment_proof"
                                                                type="file"
                                                                className="sr-only"
                                                                accept="image/*"
                                                                onChange={(e) => setData('payment_proof', e.target.files?.[0] || null)}
                                                            />
                                                        </label>
                                                    </div>
                                                    <p className="text-sm text-muted-foreground mt-2">PNG, JPG, GIF حتى 10 ميجابايت</p>
                                                    {data.payment_proof && (
                                                        <div className="mt-4 p-4 bg-gradient-to-r from-green-500/10 to-green-500/5 rounded-xl">
                                                            <p className="text-sm text-green-700 font-medium flex items-center gap-2">
                                                                <FileImage className="w-4 h-4" />
                                                                تم اختيار: {data.payment_proof.name}
                                                            </p>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                            {errors.payment_proof && (
                                                <p className="mt-2 text-sm text-red-600">{errors.payment_proof}</p>
                                            )}
                                        </div>

                                        <Button 
                                            type="submit" 
                                            disabled={processing}
                                            className="w-full bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-xl hover:shadow-2xl transition-all duration-300 text-lg px-8 py-6 h-auto font-bold"
                                        >
                                            {processing ? 'جاري الإرسال...' : 'إرسال طلب الاشتراك'}
                                        </Button>
                                    </form>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Summary Sidebar */}
                        <div className="space-y-6">
                            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl">
                                <div className="absolute inset-0 bg-gradient-to-br from-green-500/10 via-transparent to-green-500/5"></div>
                                <CardHeader className="relative pb-4">
                                    <CardTitle className="text-xl font-bold text-foreground flex items-center gap-3">
                                        <div className="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <DollarSign className="w-5 h-5 text-white" />
                                        </div>
                                        ملخص الطلب
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="relative space-y-4">
                                    <div className="flex justify-between items-center p-3 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                                        <span className="text-muted-foreground">المدة:</span>
                                        <span className="font-bold">{data.months} {parseInt(data.months) === 1 ? 'شهر' : 'أشهر'}</span>
                                    </div>
                                    
                                    {selectedPlan && (
                                        <div className="flex justify-between items-center p-3 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                                            <span className="text-muted-foreground">الخطة:</span>
                                            <span className="font-bold">{selectedPlan.name}</span>
                                        </div>
                                    )}
                                    
                                    <div className="border-t pt-4">
                                        <div className="flex justify-between items-center p-4 bg-gradient-to-r from-primary/10 to-primary/5 rounded-xl">
                                            <span className="text-lg font-bold">المجموع:</span>
                                            <span className="text-2xl font-black bg-gradient-to-r from-primary to-primary/80 bg-clip-text text-transparent">
                                                {calculateTotal().toLocaleString()} دج
                                            </span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Payment Instructions */}
                            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl">
                                <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-blue-500/5"></div>
                                <CardHeader className="relative pb-4">
                                    <CardTitle className="text-xl font-bold text-foreground">تعليمات الدفع</CardTitle>
                                </CardHeader>
                                <CardContent className="relative space-y-4">
                                    <Alert className="border-0 bg-gradient-to-r from-blue-500/10 to-blue-500/5 p-4">
                                        <AlertDescription className="text-base leading-relaxed">
                                            يرجى إجراء الدفع وإرفاق صورة إثبات الدفع قبل إرسال الطلب
                                        </AlertDescription>
                                    </Alert>
                                    <div className="space-y-3">
                                        <div className="p-3 bg-gradient-to-r from-muted/20 to-transparent rounded-xl">
                                            <p className="text-sm font-medium text-muted-foreground">رقم الحساب:</p>
                                            <p className="font-bold">1234567890123456</p>
                                        </div>
                                        <div className="p-3 bg-gradient-to-r from-muted/20 to-transparent rounded-xl">
                                            <p className="text-sm font-medium text-muted-foreground">اسم البنك:</p>
                                            <p className="font-bold">البنك الوطني الجزائري</p>
                                        </div>
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