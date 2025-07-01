import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Clock, FileCheck, Phone, Mail, Calendar, DollarSign, Store, ArrowLeft } from 'lucide-react';
import { Designer } from '@/types';

interface Props {
    designer: Designer & {
        pricing_plan?: {
            id: number;
            name: string;
            price: number;
            duration_months: number;
            formatted_price: string;
        };
    };
}

export default function SubscriptionPending({ designer }: Props) {
    return (
        <AppLayout
            breadcrumbs={[
                { title: 'لوحة المصمم', href: '/designer/dashboard' },
                { title: 'حالة الاشتراك', href: '/designer/subscription-pending' }
            ]}
        >
            <Head title="اشتراكك قيد المراجعة" />

            <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
                <div className="p-8 space-y-10">
                    {/* Revolutionary Header */}
                    <div className="relative">
                        <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5 rounded-3xl"></div>
                        <div className="relative p-8">
                            <div className="text-center mb-8">
                                <div className="mx-auto w-20 h-20 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full flex items-center justify-center mb-6 shadow-xl">
                                    <Clock className="w-10 h-10 text-white" />
                                </div>
                                <h1 className="text-5xl font-black bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                                    اشتراكك قيد المراجعة
                                </h1>
                                <p className="text-xl text-muted-foreground mt-4 max-w-3xl mx-auto leading-relaxed">
                                    تم استلام طلب الاشتراك الخاص بك بنجاح. سيقوم فريق الإدارة بمراجعته وإشعارك بالنتيجة قريباً.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        {/* Subscription Details Card */}
                        <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
                            <div className="absolute inset-0 bg-gradient-to-br from-primary/10 via-transparent to-primary/5"></div>
                            <CardHeader className="relative pb-6">
                                <CardTitle className="text-2xl font-bold text-foreground flex items-center gap-4">
                                    <div className="w-12 h-12 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center shadow-lg">
                                        <FileCheck className="w-6 h-6 text-primary-foreground" />
                                    </div>
                                    تفاصيل الطلب
                                </CardTitle>
                                <CardDescription className="text-lg text-muted-foreground">
                                    معلومات طلب الاشتراك المرسل
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="relative space-y-6">
                                <div className="flex justify-between items-center p-4 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                                    <span className="text-muted-foreground flex items-center gap-3 text-lg">
                                        <Store className="w-5 h-5" />
                                        اسم المتجر:
                                    </span>
                                    <span className="font-bold text-xl">{designer.store_name}</span>
                                </div>

                                {designer.store_description && (
                                    <div className="p-4 bg-gradient-to-r from-muted/20 to-transparent rounded-xl">
                                        <span className="text-muted-foreground block mb-3 font-medium text-lg">وصف المتجر:</span>
                                        <p className="text-base bg-gradient-to-r from-muted/40 to-muted/20 p-4 rounded-lg leading-relaxed">
                                            {designer.store_description}
                                        </p>
                                    </div>
                                )}

                                <div className="flex justify-between items-center p-4 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                                    <span className="text-muted-foreground text-lg">حالة الطلب:</span>
                                    <Badge className="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-4 py-2 text-base font-bold">
                                        قيد المراجعة
                                    </Badge>
                                </div>

                                {designer.pricing_plan && (
                                    <>
                                        <div className="flex justify-between items-center p-4 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                                            <span className="text-muted-foreground text-lg">الخطة المختارة:</span>
                                            <span className="font-bold text-xl">{designer.pricing_plan.name}</span>
                                        </div>

                                        <div className="flex justify-between items-center p-4 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                                            <span className="text-muted-foreground flex items-center gap-3 text-lg">
                                                <DollarSign className="w-5 h-5" />
                                                السعر:
                                            </span>
                                            <span className="font-black text-2xl bg-gradient-to-r from-primary to-primary/80 bg-clip-text text-transparent">
                                                {designer.pricing_plan.formatted_price}
                                            </span>
                                        </div>

                                        <div className="flex justify-between items-center p-4 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                                            <span className="text-muted-foreground flex items-center gap-3 text-lg">
                                                <Calendar className="w-5 h-5" />
                                                المدة:
                                            </span>
                                            <span className="font-bold text-xl">
                                                {designer.pricing_plan.duration_months} شهر
                                            </span>
                                        </div>
                                    </>
                                )}

                                <div className="flex justify-between items-center p-4 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                                    <span className="text-muted-foreground text-lg">تاريخ الإرسال:</span>
                                    <span className="font-bold text-xl">
                                        {new Date(designer.created_at).toLocaleDateString('ar-SA')}
                                    </span>
                                </div>

                                {designer.payment_proof_path && (
                                    <div className="flex justify-between items-center p-4 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                                        <span className="text-muted-foreground text-lg">إثبات الدفع:</span>
                                        <Badge className="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 text-base font-bold">
                                            تم الرفع
                                        </Badge>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Next Steps Card */}
                        <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
                            <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-blue-500/5"></div>
                            <CardHeader className="relative pb-6">
                                <CardTitle className="text-2xl font-bold text-foreground flex items-center gap-4">
                                    <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <Clock className="w-6 h-6 text-white" />
                                    </div>
                                    الخطوات التالية
                                </CardTitle>
                                <CardDescription className="text-lg text-muted-foreground">
                                    ما يمكنك توقعه خلال الفترة القادمة
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="relative space-y-6">
                                <div className="space-y-6">
                                    <div className="flex items-start gap-4 p-4 bg-gradient-to-r from-blue-500/10 to-transparent rounded-xl">
                                        <div className="w-4 h-4 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full mt-2"></div>
                                        <div>
                                            <p className="font-bold text-lg">مراجعة الطلب</p>
                                            <p className="text-base text-muted-foreground leading-relaxed">
                                                سيتم مراجعة طلبك وإثبات الدفع خلال 24-48 ساعة
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-4 p-4 bg-gradient-to-r from-gray-200/50 to-transparent rounded-xl">
                                        <div className="w-4 h-4 bg-gray-300 rounded-full mt-2"></div>
                                        <div>
                                            <p className="font-bold text-lg text-muted-foreground">الإشعار بالنتيجة</p>
                                            <p className="text-base text-muted-foreground leading-relaxed">
                                                ستحصل على إشعار بالموافقة أو الرفض
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-4 p-4 bg-gradient-to-r from-gray-200/50 to-transparent rounded-xl">
                                        <div className="w-4 h-4 bg-gray-300 rounded-full mt-2"></div>
                                        <div>
                                            <p className="font-bold text-lg text-muted-foreground">تفعيل الحساب</p>
                                            <p className="text-base text-muted-foreground leading-relaxed">
                                                في حالة الموافقة، سيتم تفعيل حسابك فوراً
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <Alert className="border-0 bg-gradient-to-r from-green-500/10 to-green-500/5 p-4">
                                        <AlertDescription className="flex items-center gap-3 text-base">
                                            <div className="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                                                <Phone className="w-5 h-5 text-white" />
                                            </div>
                                            سيتم إشعارك عبر البريد الإلكتروني والموقع
                                        </AlertDescription>
                                    </Alert>

                                    <Alert className="border-0 bg-gradient-to-r from-purple-500/10 to-purple-500/5 p-4">
                                        <AlertDescription className="flex items-center gap-3 text-base">
                                            <div className="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                                                <Mail className="w-5 h-5 text-white" />
                                            </div>
                                            تأكد من صحة معلومات الاتصال في ملفك الشخصي
                                        </AlertDescription>
                                    </Alert>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Help Section */}
                    <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl">
                        <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
                        <CardHeader className="relative pb-6">
                            <CardTitle className="text-2xl font-bold text-foreground">هل تحتاج مساعدة؟</CardTitle>
                            <CardDescription className="text-lg text-muted-foreground">
                                إذا كان لديك أي استفسار حول طلب الاشتراك
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="relative">
                            <div className="flex flex-col sm:flex-row gap-6">
                                <Link href="/designer/dashboard">
                                    <Button 
                                        variant="outline" 
                                        className="bg-gradient-to-r from-muted to-muted/80 hover:from-muted/80 hover:to-muted/60 border-0 shadow-lg hover:shadow-xl transition-all duration-300 text-lg px-8 py-6 h-auto"
                                    >
                                        <ArrowLeft className="mr-3 h-5 w-5" />
                                        العودة للوحة التحكم
                                    </Button>
                                </Link>
                                <Button 
                                    variant="outline"
                                    className="bg-gradient-to-r from-primary/10 to-primary/5 hover:from-primary/20 hover:to-primary/10 border-primary/20 text-primary hover:text-primary shadow-lg hover:shadow-xl transition-all duration-300 text-lg px-8 py-6 h-auto"
                                >
                                    اتصل بالدعم
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
} 