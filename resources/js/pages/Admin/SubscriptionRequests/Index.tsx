import React, { useState, useEffect } from 'react';
import { Head, usePage, useForm, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { ModernStatsCard } from '@/components/ui/modern-stats-card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DataTable, DataTableColumnHeader, DataTableRowActions } from '@/components/ui/data-table';
import { DataTablePagination } from '@/components/ui/data-table-pagination';
import { usePagination } from '@/hooks/use-pagination';
import {
    AlertCircle,
    Filter,
    CheckCircle,
    XCircle,
    Clock,
    Users,
    DollarSign,
    Calendar,
    FileText,
    ArrowUpRight,
    ArrowDownRight,
    TrendingUp,
    Loader2,
    Search
} from 'lucide-react';
import { PageProps, SubscriptionRequest } from '@/types';
import { format, parseISO } from 'date-fns';
import { ar } from 'date-fns/locale';

interface Props extends PageProps {
    subscriptionRequests: {
        data: SubscriptionRequest[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    statistics: {
        total: number;
        pending: number;
        approved: number;
        rejected: number;
    };
    filters: {
        status?: string;
        search?: string;
    };
}

export default function Index({ subscriptionRequests, statistics, filters }: Props) {
    const { flash } = usePage<PageProps>().props;
    const [selectedRequests, setSelectedRequests] = useState<number[]>([]);
    const [bulkAction, setBulkAction] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [searchDebounceTimer, setSearchDebounceTimer] = useState<NodeJS.Timeout | null>(null);

    const { data, setData, get } = useForm({
        status: filters.status || 'all',
    });

    const { isLoading: isPaginationLoading, handlePageChange } = usePagination('/admin/subscription-requests', {
        onSuccess: () => setIsLoading(false),
        onError: () => setIsLoading(false)
    });

    // Update loading state when pagination is loading
    useEffect(() => {
        setIsLoading(isPaginationLoading);
    }, [isPaginationLoading]);

    // Handle search with debounce
    const handleSearch = (value: string) => {
        setSearchQuery(value);

        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
        }

        const timer = setTimeout(() => {
            handlePageChange(1, { search: value, status: data.status });
        }, 300);

        setSearchDebounceTimer(timer);
    };

    // Cleanup timer on unmount
    useEffect(() => {
        return () => {
            if (searchDebounceTimer) {
                clearTimeout(searchDebounceTimer);
            }
        };
    }, [searchDebounceTimer]);

    // Provide safe defaults
    const safeSubscriptionRequests = subscriptionRequests?.data || [];
    const safeStatistics = statistics || { total: 0, pending: 0, approved: 0, rejected: 0 };

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

    const handleStatusFilter = (status: string) => {
        setData('status', status);
        get(route('admin.subscription-requests.index'), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            setSelectedRequests(subscriptionRequests.data.map(req => req.id));
        } else {
            setSelectedRequests([]);
        }
    };

    const handleSelectRequest = (requestId: number, checked: boolean) => {
        if (checked) {
            setSelectedRequests([...selectedRequests, requestId]);
        } else {
            setSelectedRequests(selectedRequests.filter(id => id !== requestId));
        }
    };

    const handleBulkAction = () => {
        if (!bulkAction || selectedRequests.length === 0) return;

        router.post(route('admin.subscription-requests.bulk-update'), {
            ids: selectedRequests,
            status: bulkAction,
        }, {
            onSuccess: () => {
                setSelectedRequests([]);
                setBulkAction('');
            },
        });
    };

    const resetFilters = () => {
        setData({ status: 'all' });
        get(route('admin.subscription-requests.index'));
    };

    // Define columns for the DataTable
    const columns: ColumnDef<SubscriptionRequest>[] = [
        
        {
            accessorKey: "designer.user.name",
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="المصمم" />
            ),
            cell: ({ row }) => {
                const request = row.original;
                return (
                    <div className="flex items-center gap-2">
                        <Users className="w-4 h-4 text-muted-foreground" />
                        <div>
                            <div className="font-medium">{request.designer?.user?.name}</div>
                            <div className="text-sm text-muted-foreground">{request.designer?.user?.email}</div>
                        </div>
                    </div>
                );
            },
        },
        {
            accessorKey: "designer.store_name",
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="المتجر" />
            ),
            cell: ({ row }) => (
                <div className="font-medium">{row.original.designer?.store_name}</div>
            ),
        },
        {
            accessorKey: "pricing_plan.name",
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="خطة الاشتراك" />
            ),
            cell: ({ row }) => (
                <div className="font-medium">{row.original.pricing_plan?.name}</div>
            ),
        },
        {
            accessorKey: "subscription_price",
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="السعر" />
            ),
            cell: ({ row }) => (
                <div className="flex items-center gap-1">
                    <DollarSign className="w-4 h-4 text-muted-foreground" />
                    <span className="font-medium">
                        {new Intl.NumberFormat('ar-DZ', {
                            style: 'currency',
                            currency: 'DZD'
                        }).format(row.getValue("subscription_price"))}
                    </span>
                </div>
            ),
        },
        {
            accessorKey: "requested_start_date",
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="تاريخ البداية" />
            ),
            cell: ({ row }) => (
                <div className="flex items-center gap-1">
                    <Calendar className="w-4 h-4 text-muted-foreground" />
                    <span>{format(parseISO(row.getValue("requested_start_date")), 'dd/MM/yyyy', { locale: ar })}</span>
                </div>
            ),
        },
        {
            accessorKey: "status",
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="الحالة" />
            ),
            cell: ({ row }) => {
                const status = row.getValue("status") as string;
                return (
                    <Badge className={getStatusColor(status)}>
                        {getStatusText(status)}
                    </Badge>
                );
            },
        },
        {
            accessorKey: "created_at",
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="تاريخ الإرسال" />
            ),
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {format(parseISO(row.getValue("created_at")), 'dd/MM/yyyy HH:mm', { locale: ar })}
                </span>
            ),
        },
        {
            id: "actions",
            enableHiding: false,
            cell: ({ row }) => {
                return (
                    <DataTableRowActions
                        row={row}
                        actions={[
                            {
                                label: "عرض التفاصيل",
                                onClick: (request: SubscriptionRequest) => {
                                    router.visit(route('admin.subscription-requests.show', request.id));
                                },
                            },
                        ]}
                    />
                );
            },
        },
    ];

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'لوحة الإدارة', href: '/admin/dashboard' },
                { title: 'طلبات الاشتراك', href: '/admin/subscription-requests' }
            ]}
        >
            <Head title="إدارة طلبات الاشتراك - Subscription Requests Management" />

            <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
                <div className="p-8 space-y-10">
                    {/* Modern Header */}
                    <AdminPageHeader
                        title="إدارة طلبات الاشتراك"
                        subtitle="مراجعة والموافقة على طلبات اشتراك المصممين"
                        icon={FileText}
                    />

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="p-4 rounded-lg bg-green-50 border border-green-200">
                        <div className="flex">
                            <CheckCircle className="w-5 h-5 text-green-400" />
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

                {/* Revolutionary Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {/* Total Requests */}
                    <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
                        <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-blue-500/5"></div>
                        <CardHeader className="relative pb-3">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-base font-bold text-muted-foreground uppercase tracking-wider">إجمالي الطلبات</CardTitle>
                                <div className="w-12 h-12 bg-gradient-to-br from-blue-500/20 to-blue-500/10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <FileText className="w-6 h-6 text-blue-600" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent className="relative pt-0">
                            <div className="space-y-3">
                                <div className="text-4xl font-black text-foreground group-hover:text-blue-600 transition-colors duration-300">
                                    {safeStatistics.total}
                                </div>
                                <div className="flex items-center gap-2 text-sm">
                                    <TrendingUp className="w-4 h-4 text-green-500" />
                                    <span className="text-muted-foreground font-medium">جميع الطلبات</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Pending Requests */}
                    <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
                        <div className="absolute inset-0 bg-gradient-to-br from-yellow-500/10 via-transparent to-yellow-500/5"></div>
                        <CardHeader className="relative pb-3">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-base font-bold text-muted-foreground uppercase tracking-wider">طلبات معلقة</CardTitle>
                                <div className="w-12 h-12 bg-gradient-to-br from-yellow-500/20 to-yellow-500/10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <Clock className="w-6 h-6 text-yellow-600" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent className="relative pt-0">
                            <div className="space-y-3">
                                <div className="text-4xl font-black text-foreground group-hover:text-yellow-600 transition-colors duration-300">
                                    {safeStatistics.pending}
                                </div>
                                <div className="flex items-center gap-2 text-sm">
                                    <Clock className="w-4 h-4 text-yellow-500" />
                                    <span className="text-muted-foreground font-medium">في انتظار المراجعة</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Approved Requests */}
                    <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
                        <div className="absolute inset-0 bg-gradient-to-br from-green-500/10 via-transparent to-green-500/5"></div>
                        <CardHeader className="relative pb-3">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-base font-bold text-muted-foreground uppercase tracking-wider">طلبات موافق عليها</CardTitle>
                                <div className="w-12 h-12 bg-gradient-to-br from-green-500/20 to-green-500/10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <CheckCircle className="w-6 h-6 text-green-600" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent className="relative pt-0">
                            <div className="space-y-3">
                                <div className="text-4xl font-black text-foreground group-hover:text-green-600 transition-colors duration-300">
                                    {safeStatistics.approved}
                                </div>
                                <div className="flex items-center gap-2 text-sm">
                                    <ArrowUpRight className="w-4 h-4 text-green-500" />
                                    <span className="text-muted-foreground font-medium">تم قبولها</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Rejected Requests */}
                    <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
                        <div className="absolute inset-0 bg-gradient-to-br from-red-500/10 via-transparent to-red-500/5"></div>
                        <CardHeader className="relative pb-3">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-base font-bold text-muted-foreground uppercase tracking-wider">طلبات مرفوضة</CardTitle>
                                <div className="w-12 h-12 bg-gradient-to-br from-red-500/20 to-red-500/10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <XCircle className="w-6 h-6 text-red-600" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent className="relative pt-0">
                            <div className="space-y-3">
                                <div className="text-4xl font-black text-foreground group-hover:text-red-600 transition-colors duration-300">
                                    {safeStatistics.rejected}
                                </div>
                                <div className="flex items-center gap-2 text-sm">
                                    <ArrowDownRight className="w-4 h-4 text-red-500" />
                                    <span className="text-muted-foreground font-medium">تم رفضها</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Bulk Actions */}
                {selectedRequests.length > 0 && (
                    <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-lg hover:shadow-xl transition-all duration-300">
                        <div className="absolute inset-0 bg-gradient-to-r from-primary/5 to-transparent"></div>
                        <CardContent className="relative p-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 bg-gradient-to-br from-primary/20 to-primary/10 rounded-xl flex items-center justify-center">
                                        <CheckCircle className="w-5 h-5 text-primary" />
                                    </div>
                                    <p className="text-base font-bold text-foreground">
                                        تم تحديد {selectedRequests.length} طلب
                                    </p>
                                </div>
                                <div className="flex items-center gap-3">
                                    <Select value={bulkAction} onValueChange={setBulkAction}>
                                        <SelectTrigger className="w-48 h-12 text-base">
                                            <SelectValue placeholder="اختر إجراء" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="approved">موافقة</SelectItem>
                                            <SelectItem value="rejected">رفض</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button
                                        onClick={handleBulkAction}
                                        disabled={!bulkAction}
                                        className="h-12 px-6 bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300"
                                    >
                                        تطبيق
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Status Filter */}
                <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-lg hover:shadow-xl transition-all duration-300">
                    <div className="absolute inset-0 bg-gradient-to-r from-primary/5 to-transparent"></div>
                    <CardContent className="relative p-6">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 bg-gradient-to-br from-primary/20 to-primary/10 rounded-xl flex items-center justify-center">
                                    <Filter className="w-5 h-5 text-primary" />
                                </div>
                                <div>
                                    <h3 className="text-lg font-bold text-foreground">تصفية حسب الحالة</h3>
                                    <p className="text-sm text-muted-foreground">اختر حالة الطلبات المراد عرضها</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <Select
                                    value={data.status}
                                    onValueChange={handleStatusFilter}
                                >
                                    <SelectTrigger className="w-48 h-12">
                                        <SelectValue placeholder="اختر الحالة" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">جميع الحالات</SelectItem>
                                        <SelectItem value="pending">معلق</SelectItem>
                                        <SelectItem value="approved">موافق عليه</SelectItem>
                                        <SelectItem value="rejected">مرفوض</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button
                                    variant="outline"
                                    onClick={resetFilters}
                                    className="h-12 px-4"
                                >
                                    <Filter className="w-4 h-4 ml-2" />
                                    إعادة تعيين
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Subscription Requests DataTable */}
                <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-xl hover:shadow-2xl transition-all duration-500">
                    <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
                    <CardHeader className="relative">
                        <CardTitle className="text-2xl font-bold text-foreground flex items-center gap-3">
                            <div className="w-10 h-10 bg-gradient-to-br from-primary/20 to-primary/10 rounded-xl flex items-center justify-center">
                                <FileText className="w-5 h-5 text-primary" />
                            </div>
                            قائمة طلبات الاشتراك
                            {data.status !== 'all' && (
                                <Badge variant="secondary" className="mr-2">
                                    مفلتر
                                </Badge>
                            )}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="relative">
                        {safeSubscriptionRequests.length === 0 ? (
                            <div className="text-center py-16">
                                <div className="w-24 h-24 bg-gradient-to-br from-muted/50 to-muted/30 rounded-3xl flex items-center justify-center mx-auto mb-6">
                                    <FileText className="w-12 h-12 text-muted-foreground" />
                                </div>
                                <h3 className="text-2xl font-bold text-foreground mb-3">
                                    لا توجد طلبات اشتراك
                                </h3>
                                <p className="text-lg text-muted-foreground max-w-md mx-auto">
                                    لم يتم العثور على طلبات تطابق معايير البحث
                                </p>
                            </div>
                        ) : (
                            <DataTablePagination
                                columns={columns}
                                paginatedData={subscriptionRequests}
                                searchPlaceholder="البحث في الطلبات..."
                                searchColumn="designer.user.name"
                                isLoading={isLoading}
                                onPageChange={(page) => handlePageChange(page, {
                                  search: searchQuery || undefined,
                                  status: data.status !== 'all' ? data.status : undefined
                                })}

                            />
                        )}
                    </CardContent>
                </Card>
                </div>
            </div>
        </AppLayout>
    );
}
