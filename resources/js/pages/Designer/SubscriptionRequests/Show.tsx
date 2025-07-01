import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    CalendarDays, 
    DollarSign, 
    Clock, 
    FileText, 
    Eye, 
    User,
    MessageSquare,
    CheckCircle,
    XCircle,
    AlertCircle,
    FileSpreadsheet
} from 'lucide-react';
import { PageProps, SubscriptionRequest } from '@/types';
import { format, parseISO } from 'date-fns';
import ModernPageHeader from '@/components/ui/modern-page-header';

interface Props extends PageProps {
    subscriptionRequest: SubscriptionRequest;
}

export default function Show({ subscriptionRequest }: Props) {
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'pending':
                return 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800/30';
            case 'approved':
                return 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/30';
            case 'rejected':
                return 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/30';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800/50 dark:text-gray-400 dark:border-gray-700';
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

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'pending':
                return <AlertCircle className="w-5 h-5" />;
            case 'approved':
                return <CheckCircle className="w-5 h-5" />;
            case 'rejected':
                return <XCircle className="w-5 h-5" />;
            default:
                return <AlertCircle className="w-5 h-5" />;
        }
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'طلبات الاشتراك', href: '/designer/subscription-requests' },
                { title: `طلب #${subscriptionRequest.id}`, href: `/designer/subscription-requests/${subscriptionRequest.id}` }
            ]}
        >
            <Head title={`طلب اشتراك #${subscriptionRequest.id}`} />

            <div className="space-y-6 p-8">
                {/* Page Header */}
                <ModernPageHeader
                    title={`طلب اشتراك #${subscriptionRequest.id}`}
                    subtitle="تفاصيل طلب الاشتراك المرسل"
                    icon={FileSpreadsheet}
                >
                    <Link href={route('designer.subscription-requests.index')}>
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="w-4 h-4 ml-2" />
                            العودة إلى القائمة
                        </Button>
                    </Link>
                </ModernPageHeader>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Request Details */}
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle>تفاصيل الطلب</CardTitle>
                                    <Badge className={getStatusColor(subscriptionRequest.status)}>
                                        <div className="flex items-center">
                                            {getStatusIcon(subscriptionRequest.status)}
                                            <span className="mr-2">{getStatusText(subscriptionRequest.status)}</span>
                                        </div>
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">خطة الاشتراك</label>
                                        <p className="text-base font-semibold">
                                            {subscriptionRequest.pricing_plan?.name}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-500">السعر</label>
                                        <div className="flex items-center">
                                            <DollarSign className="w-4 h-4 ml-1 text-green-600" />
                                            <p className="text-base font-semibold">
                                                {new Intl.NumberFormat('ar-DZ', {
                                                    style: 'currency',
                                                    currency: 'DZD'
                                                }).format(subscriptionRequest.subscription_price)}
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-500">تاريخ البداية المطلوب</label>
                                        <div className="flex items-center">
                                            <CalendarDays className="w-4 h-4 ml-1 text-blue-600" />
                                            <p className="text-base font-semibold">
                                                {format(parseISO(subscriptionRequest.requested_start_date), 'MM/dd/yyyy')}
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-500">مدة الاشتراك</label>
                                        <div className="flex items-center">
                                            <Clock className="w-4 h-4 ml-1 text-purple-600" />
                                            <p className="text-base font-semibold">
                                                {subscriptionRequest.pricing_plan?.duration_months} شهر
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-500">تاريخ الإرسال</label>
                                        <p className="text-base font-semibold">
                                            {format(parseISO(subscriptionRequest.created_at), 'MM/dd/yyyy HH:mm')}
                                        </p>
                                    </div>

                                    {subscriptionRequest.reviewed_at && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-500">تاريخ المراجعة</label>
                                            <p className="text-base font-semibold">
                                                {format(parseISO(subscriptionRequest.reviewed_at), 'MM/dd/yyyy HH:mm')}
                                            </p>
                                        </div>
                                    )}
                                </div>

                                {subscriptionRequest.notes && (
                                    <div>
                                        <label className="text-sm font-medium text-gray-500 dark:text-gray-400">ملاحظاتك</label>
                                        <div className="mt-1 p-3 bg-muted/30 dark:bg-muted/20 border border-border/50 rounded-lg">
                                            <p className="text-sm text-foreground">{subscriptionRequest.notes}</p>
                                        </div>
                                    </div>
                                )}

                                {subscriptionRequest.pricing_plan?.description && (
                                    <div>
                                        <label className="text-sm font-medium text-gray-500 dark:text-gray-400">وصف الخطة</label>
                                        <div className="mt-1 p-3 bg-primary/5 dark:bg-primary/10 border border-primary/20 rounded-lg">
                                            <p className="text-sm text-foreground">{subscriptionRequest.pricing_plan.description}</p>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Payment Proof */}
                        {subscriptionRequest.payment_proof_path && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>إثبات الدفع</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <p className="font-medium text-foreground">{subscriptionRequest.payment_proof_original_name}</p>
                                                <p className="text-sm text-muted-foreground">
                                                    {subscriptionRequest.formatted_file_size} • {subscriptionRequest.payment_proof_mime_type}
                                                </p>
                                            </div>
                                            {subscriptionRequest.payment_proof_url && (
                                                <a
                                                    href={subscriptionRequest.payment_proof_url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <Button variant="outline" size="sm">
                                                        <Eye className="w-4 h-4 ml-1" />
                                                        عرض الصورة
                                                    </Button>
                                                </a>
                                            )}
                                        </div>
                                        {subscriptionRequest.payment_proof_url && (
                                            <div className="border border-border rounded-lg overflow-hidden">
                                                <img
                                                    src={subscriptionRequest.payment_proof_url}
                                                    alt="إثبات الدفع"
                                                    className="w-full h-64 object-contain bg-muted/30 dark:bg-muted/20"
                                                />
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Admin Response */}
                        {(subscriptionRequest.admin_notes || subscriptionRequest.reviewed_by_user) && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>رد الإدارة</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {subscriptionRequest.reviewed_by_user && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-500 dark:text-gray-400">تمت المراجعة بواسطة</label>
                                            <div className="flex items-center mt-1">
                                                <User className="w-4 h-4 ml-1 text-muted-foreground" />
                                                <p className="text-base font-semibold text-foreground">
                                                    {subscriptionRequest.reviewed_by_user.name}
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                    {subscriptionRequest.admin_notes && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-500 dark:text-gray-400">ملاحظات الإدارة</label>
                                            <div className="mt-1 p-3 bg-primary/5 dark:bg-primary/10 border border-primary/20 rounded-lg">
                                                <div className="flex items-start">
                                                    <MessageSquare className="w-4 h-4 ml-2 mt-0.5 text-primary" />
                                                    <p className="text-sm text-foreground">{subscriptionRequest.admin_notes}</p>
                                                </div>
                                            </div>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Status Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle>حالة الطلب</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-center">
                                    <div className="mb-4">
                                        <div className={`inline-flex p-3 rounded-full ${getStatusColor(subscriptionRequest.status)}`}>
                                            {getStatusIcon(subscriptionRequest.status)}
                                        </div>
                                    </div>
                                    <h3 className="text-lg font-semibold mb-2 text-foreground">
                                        {getStatusText(subscriptionRequest.status)}
                                    </h3>
                                    <p className="text-sm text-muted-foreground">
                                        {subscriptionRequest.status === 'pending' && 
                                            'طلبك قيد المراجعة من قبل فريق الإدارة'}
                                        {subscriptionRequest.status === 'approved' && 
                                            'تم قبول طلبك وتفعيل اشتراكك'}
                                        {subscriptionRequest.status === 'rejected' && 
                                            'تم رفض طلبك. يرجى مراجعة ملاحظات الإدارة'}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Quick Actions */}
                        {subscriptionRequest.status === 'pending' && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>إجراءات سريعة</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <p className="text-sm text-muted-foreground">
                                        يمكنك إرسال طلب اشتراك جديد إذا كنت تريد تغيير تفاصيل هذا الطلب
                                    </p>
                                    <Link href={route('designer.subscription-requests.create')}>
                                        <Button className="w-full" variant="outline">
                                            <FileText className="w-4 h-4 ml-2" />
                                            طلب جديد
                                        </Button>
                                    </Link>
                                </CardContent>
                            </Card>
                        )}

                        {subscriptionRequest.status === 'rejected' && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>الخطوات التالية</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <p className="text-sm text-muted-foreground">
                                        يمكنك إرسال طلب جديد بعد مراجعة ملاحظات الإدارة
                                    </p>
                                    <Link href={route('designer.subscription-requests.create')}>
                                        <Button className="w-full">
                                            <FileText className="w-4 h-4 ml-2" />
                                            إرسال طلب جديد
                                        </Button>
                                    </Link>
                                </CardContent>
                            </Card>
                        )}

                        {/* Timeline */}
                        <Card>
                            <CardHeader>
                                <CardTitle>الجدول الزمني</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div className="flex items-start">
                                        <div className="w-2 h-2 bg-blue-500 rounded-full mt-2 ml-3"></div>
                                        <div>
                                            <p className="text-sm font-medium text-foreground">تم إرسال الطلب</p>
                                            <p className="text-xs text-muted-foreground">
                                                {format(parseISO(subscriptionRequest.created_at), 'MM/dd/yyyy HH:mm')}
                                            </p>
                                        </div>
                                    </div>

                                    {subscriptionRequest.reviewed_at && (
                                        <div className="flex items-start">
                                            <div className={`w-2 h-2 rounded-full mt-2 ml-3 ${
                                                subscriptionRequest.status === 'approved' ? 'bg-green-500' : 'bg-red-500'
                                            }`}></div>
                                            <div>
                                                <p className="text-sm font-medium text-foreground">
                                                    {subscriptionRequest.status === 'approved' ? 'تم القبول' : 'تم الرفض'}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {format(parseISO(subscriptionRequest.reviewed_at), 'MM/dd/yyyy HH:mm')}
                                                </p>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
} 