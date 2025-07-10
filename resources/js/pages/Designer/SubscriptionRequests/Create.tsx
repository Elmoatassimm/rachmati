import React, { useState, useRef } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertCircle, ArrowLeft, Upload, X, FileImage, Calendar, DollarSign, Clock, FileEdit } from 'lucide-react';
import { PageProps, PricingPlan } from '@/types';
import ModernPageHeader from '@/components/ui/modern-page-header';

interface Props extends PageProps {
    pricingPlans: PricingPlan[];
}

export default function Create({ pricingPlans }: Props) {
    const { flash } = usePage<PageProps>().props;
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(null);
    const [fileName, setFileName] = useState<string | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        pricing_plan_id: '',
        notes: '',
        payment_proof: null as File | null,
    });

    const selectedPlan = pricingPlans.find(plan => plan.id.toString() === data.pricing_plan_id);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('يرجى اختيار ملف صورة صحيح');
                return;
            }

            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('حجم الملف كبير جداً. الحد الأقصى هو 5 ميجابايت');
                return;
            }

            setData('payment_proof', file);
            setFileName(file.name);

            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                setPreview(e.target?.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const removeFile = () => {
        setData('payment_proof', null);
        setFileName(null);
        setPreview(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('designer.subscription-requests.store'), {
            onSuccess: () => {
                reset();
                setPreview(null);
                setFileName(null);
            },
        });
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'طلبات الاشتراك', href: '/designer/subscription-requests' },
                { title: 'طلب جديد', href: '/designer/subscription-requests/create' }
            ]}
        >
            <Head title="طلب اشتراك جديد" />

            <div className="space-y-6 p-8">
                {/* Page Header */}
                <ModernPageHeader
                    title="طلب اشتراك جديد"
                    subtitle="قم بإرسال طلب اشتراك جديد للحصول على خدمات المنصة"
                    icon={FileEdit}
                >
                    <Link href={route('designer.subscription-requests.index')}>
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="w-4 h-4 ml-2" />
                            العودة إلى القائمة
                        </Button>
                    </Link>
                </ModernPageHeader>

                {/* Flash Messages */}
                {flash?.error && (
                    <div className="p-4 rounded-lg bg-red-50 border border-red-200">
                        <div className="flex">
                            <AlertCircle className="w-5 h-5 text-red-400" />
                            <div className="mr-3">
                                <p className="text-sm font-medium text-red-800">
                                    {flash.error}
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Form */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Pricing Plan Selection */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>اختيار خطة الاشتراك</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <Label htmlFor="pricing_plan_id">خطة الاشتراك *</Label>
                                        <Select
                                            value={data.pricing_plan_id}
                                            onValueChange={(value) => setData('pricing_plan_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="اختر خطة الاشتراك" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {pricingPlans.map((plan) => (
                                                    <SelectItem key={plan.id} value={plan.id.toString()}>
                                                        <div className="flex items-center justify-between w-full">
                                                            <span>{plan.name}</span>
                                                            <span className="text-sm text-gray-500">
                                                                {new Intl.NumberFormat('ar-DZ', {
                                                                    style: 'currency',
                                                                    currency: 'DZD'
                                                                }).format(plan.price)}
                                                            </span>
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.pricing_plan_id && (
                                            <p className="text-sm text-red-600 mt-1">{errors.pricing_plan_id}</p>
                                        )}
                                    </div>

                                

                                    <div>
                                        <Label htmlFor="notes">ملاحظات (اختياري)</Label>
                                        <Textarea
                                            id="notes"
                                            placeholder="أضف أي ملاحظات أو تفاصيل إضافية..."
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            rows={4}
                                        />
                                        {errors.notes && (
                                            <p className="text-sm text-red-600 mt-1">{errors.notes}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Payment Proof Upload */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>إثبات الدفع (اختياري)</CardTitle>
                                    <p className="text-sm text-gray-600">
                                        قم برفع صورة إيصال الدفع إذا كنت قد دفعت المبلغ مسبقاً
                                    </p>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {!fileName ? (
                                            <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                                <input
                                                    ref={fileInputRef}
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={handleFileChange}
                                                    className="hidden"
                                                />
                                                <FileImage className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                                                <p className="text-sm text-gray-600 mb-2">
                                                    اضغط لرفع صورة إثبات الدفع
                                                </p>
                                                <p className="text-xs text-gray-500">
                                                    PNG, JPG, GIF حتى 5MB
                                                </p>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    className="mt-4"
                                                    onClick={() => fileInputRef.current?.click()}
                                                >
                                                    <Upload className="w-4 h-4 ml-2" />
                                                    اختيار ملف
                                                </Button>
                                            </div>
                                        ) : (
                                            <div className="border rounded-lg p-4">
                                                <div className="flex items-center justify-between mb-3">
                                                    <div className="flex items-center space-x-3 space-x-reverse">
                                                        <FileImage className="w-8 h-8 text-blue-500" />
                                                        <div>
                                                            <p className="text-sm font-medium">{fileName}</p>
                                                            <p className="text-xs text-gray-500">
                                                                {data.payment_proof && 
                                                                    `${(data.payment_proof.size / 1024 / 1024).toFixed(2)} MB`
                                                                }
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={removeFile}
                                                    >
                                                        <X className="w-4 h-4" />
                                                    </Button>
                                                </div>
                                                {preview && (
                                                    <div className="mt-3">
                                                        <img
                                                            src={preview}
                                                            alt="معاينة إثبات الدفع"
                                                            className="max-w-full h-48 object-contain rounded border"
                                                        />
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                        {errors.payment_proof && (
                                            <p className="text-sm text-red-600">{errors.payment_proof}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar - Selected Plan Details */}
                        <div className="space-y-6">
                            {selectedPlan && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>تفاصيل الخطة المختارة</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div>
                                            <h3 className="font-semibold text-lg">{selectedPlan.name}</h3>
                                            <p className="text-sm text-gray-600 mt-1">
                                                {selectedPlan.description}
                                            </p>
                                        </div>

                                        <div className="space-y-3">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center">
                                                    <DollarSign className="w-4 h-4 ml-2 text-green-600" />
                                                    <span className="text-sm">السعر:</span>
                                                </div>
                                                <span className="font-semibold">
                                                    {new Intl.NumberFormat('ar-DZ', {
                                                        style: 'currency',
                                                        currency: 'DZD'
                                                    }).format(selectedPlan.price)}
                                                </span>
                                            </div>

                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center">
                                                    <Clock className="w-4 h-4 ml-2 text-blue-600" />
                                                    <span className="text-sm">المدة:</span>
                                                </div>
                                                <span className="font-semibold">
                                                    {selectedPlan.duration_months} شهر
                                                </span>
                                            </div>

                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center">
                                                    <Calendar className="w-4 h-4 ml-2 text-purple-600" />
                                                    <span className="text-sm">تاريخ البداية:</span>
                                                </div>
                                                <span className="font-semibold">
                                                    {new Date(data.requested_start_date).toLocaleDateString('ar')}
                                                </span>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Submit Button */}
                            <Card>
                                <CardContent className="pt-6">
                                    <Button
                                        type="submit"
                                        className="w-full"
                                        disabled={processing || !data.pricing_plan_id}
                                    >
                                        {processing ? 'جاري الإرسال...' : 'إرسال طلب الاشتراك'}
                                    </Button>
                                    <p className="text-xs text-gray-500 mt-2 text-center">
                                        سيتم مراجعة طلبك من قبل فريق الإدارة
                                    </p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
} 