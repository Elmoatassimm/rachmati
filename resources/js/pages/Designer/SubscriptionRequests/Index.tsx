import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { AlertCircle, Plus, Eye, CalendarDays, DollarSign, FileText, ClipboardList } from 'lucide-react';
import { PageProps, SubscriptionRequest } from '@/types';
import { format, parseISO } from 'date-fns';
import ModernPageHeader from '@/components/ui/modern-page-header';

interface Props extends PageProps {
    subscriptionRequests: {
        data: SubscriptionRequest[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export default function Index({ subscriptionRequests }: Props) {
    const { flash } = usePage<PageProps>().props;

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'pending':
                return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'approved':
                return 'bg-green-100 text-green-800 border-green-200';
            case 'rejected':
                return 'bg-red-100 text-red-800 border-red-200';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200';
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

    return (
        <AppLayout 
            breadcrumbs={[
                { title: 'طلبات الاشتراك', href: '/designer/subscription-requests' }
            ]}
        >
            <Head title="طلبات الاشتراك" />

            <div className="space-y-6 p-8">
                {/* Page Header */}
                <ModernPageHeader
                    title="طلبات الاشتراك"
                    subtitle="إدارة طلبات الاشتراك الخاصة بك"
                    icon={ClipboardList}
                >
                    <Link href={route('designer.subscription-requests.create')}>
                        <Button>
                            <Plus className="w-4 h-4 ml-2" />
                            طلب اشتراك جديد
                        </Button>
                    </Link>
                </ModernPageHeader>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="p-4 rounded-lg bg-green-50 border border-green-200">
                        <div className="flex">
                            <AlertCircle className="w-5 h-5 text-green-400" />
                            <div className="mr-3">
                                <p className="text-sm font-medium text-green-800">
                                    {flash.success}
                                </p>
                            </div>
                        </div>
                    </div>
                )}

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

                {/* Subscription Requests Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>قائمة طلبات الاشتراك</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {subscriptionRequests.data.length === 0 ? (
                            <div className="text-center py-8">
                                <FileText className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                                <h3 className="text-lg font-medium text-gray-900 mb-2">
                                    لا توجد طلبات اشتراك
                                </h3>
                                <p className="text-gray-500 mb-4">
                                    لم تقم بإرسال أي طلبات اشتراك بعد
                                </p>
                                <Link href={route('designer.subscription-requests.create')}>
                                    <Button>
                                        <Plus className="w-4 h-4 ml-2" />
                                        إرسال طلب اشتراك
                                    </Button>
                                </Link>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="text-right">خطة الاشتراك</TableHead>
                                            <TableHead className="text-right">السعر</TableHead>
                                            <TableHead className="text-right">تاريخ البداية المطلوب</TableHead>
                                            <TableHead className="text-right">الحالة</TableHead>
                                            <TableHead className="text-right">تاريخ الإرسال</TableHead>
                                            <TableHead className="text-right">الإجراءات</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {subscriptionRequests.data.map((request) => (
                                            <TableRow key={request.id}>
                                                <TableCell className="font-medium">
                                                    {request.pricing_plan?.name}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center">
                                                        <DollarSign className="w-4 h-4 ml-1" />
                                                        {new Intl.NumberFormat('ar-DZ', {
                                                            style: 'currency',
                                                            currency: 'DZD'
                                                        }).format(request.subscription_price)}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center">
                                                        <CalendarDays className="w-4 h-4 ml-1" />
                                                        {format(parseISO(request.requested_start_date), 'MM/dd/yyyy')}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={getStatusColor(request.status)}>
                                                        {getStatusText(request.status)}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {format(parseISO(request.created_at), 'MM/dd/yyyy HH:mm')}
                                                </TableCell>
                                                <TableCell>
                                                    <Link
                                                        href={route('designer.subscription-requests.show', request.id)}
                                                    >
                                                        <Button variant="outline" size="sm">
                                                            <Eye className="w-4 h-4 ml-1" />
                                                            عرض
                                                        </Button>
                                                    </Link>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Pagination */}
                {subscriptionRequests.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <div className="text-sm text-gray-700">
                            عرض {subscriptionRequests.data.length} من {subscriptionRequests.total} نتيجة
                        </div>
                        <div className="flex items-center space-x-2 space-x-reverse">
                            {subscriptionRequests.current_page > 1 && (
                                <Link
                                    href={route('designer.subscription-requests.index', {
                                        page: subscriptionRequests.current_page - 1
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        السابق
                                    </Button>
                                </Link>
                            )}
                            <span className="text-sm text-gray-700">
                                صفحة {subscriptionRequests.current_page} من {subscriptionRequests.last_page}
                            </span>
                            {subscriptionRequests.current_page < subscriptionRequests.last_page && (
                                <Link
                                    href={route('designer.subscription-requests.index', {
                                        page: subscriptionRequests.current_page + 1
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        التالي
                                    </Button>
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
} 