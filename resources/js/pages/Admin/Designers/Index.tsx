import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DataTable, DataTableColumnHeader, DataTableRowActions } from '@/components/ui/data-table';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { ModernStatsCard } from '@/components/ui/modern-stats-card';
import { Designer, PageProps } from '@/types';
import {
  Users,
  UserCheck,
  Clock,
  ShoppingCart,
  Crown,
  CheckCircle,
  AlertCircle,
  Search,
  Filter
} from 'lucide-react';

interface Stats {
  total: number;
  active: number;
  pending: number;
  totalRachmat: number;
  totalOrders: number;
}

interface Props extends PageProps {
  designers?: {
    data: Designer[];
    links: Record<string, unknown>[];
    meta: Record<string, unknown>;
  };
  filters?: {
    search?: string;
    status?: string;
  };
  stats?: Stats;
}

export default function Index({ designers, filters = {}, stats }: Props) {
  const { flash } = usePage<PageProps>().props;
  const [searchTerm, setSearchTerm] = useState(filters?.search || '');
  const [statusFilter, setStatusFilter] = useState(filters?.status || 'all');

  // Provide default values if undefined
  const safeStats = stats || { total: 0, active: 0, pending: 0, totalRachmat: 0, totalOrders: 0 };
  const safeDesigners = designers?.data || [];

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/designers', {
      search: searchTerm,
      status: statusFilter,
    }, {
      preserveState: true,
      replace: true,
    });
  };

  const resetFilters = () => {
    setSearchTerm('');
    setStatusFilter('all');
    router.get('/admin/designers', {}, {
      preserveState: true,
      replace: true,
    });
  };

  const getStatusBadge = (status: string) => {
    const variants = {
      active: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
      pending: 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20',
      expired: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
    };
    return variants[status as keyof typeof variants] || 'bg-muted text-muted-foreground border-border';
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ar-DZ', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ').format(amount) + ' دج';
  };

  const handleDelete = (designerId: number, designerName: string) => {
    if (confirm(`هل أنت متأكد من حذف المصمم "${designerName}"؟ سيتم حذف جميع الرشمات المرتبطة به.`)) {
      router.delete(`/admin/designers/${designerId}`);
    }
  };

  const handleToggleStatus = (designerId: number, currentStatus: string, designerName: string) => {
    const newStatus = currentStatus === 'active' ? 'expired' : 'active';
    const action = newStatus === 'active' ? 'تفعيل' : 'إلغاء تفعيل';

    if (confirm(`هل أنت متأكد من ${action} حساب المصمم "${designerName}"؟`)) {
      router.post(`/admin/designers/${designerId}/toggle-status`);
    }
  };

  const handleApproveSubscription = (designerId: number, designerName: string) => {
    if (confirm(`هل أنت متأكد من الموافقة على اشتراك المصمم "${designerName}"؟`)) {
      router.post(`/admin/designers/${designerId}/approve-subscription`);
    }
  };

  const handleRejectSubscription = (designerId: number, designerName: string) => {
    const reason = prompt(`سبب رفض اشتراك المصمم "${designerName}" (اختياري):`);
    if (reason !== null) { // User didn't cancel
      router.post(`/admin/designers/${designerId}/reject-subscription`, {
        reason: reason.trim()
      });
    }
  };

  const handleActivateSubscription = (designerId: number, designerName: string) => {
    if (confirm(`هل أنت متأكد من تفعيل اشتراك المصمم "${designerName}"؟`)) {
      router.post(`/admin/designers/${designerId}/activate-subscription`);
    }
  };

  const handleDeactivateSubscription = (designerId: number, designerName: string) => {
    if (confirm(`هل أنت متأكد من إلغاء تفعيل اشتراك المصمم "${designerName}"؟`)) {
      router.post(`/admin/designers/${designerId}/deactivate-subscription`);
    }
  };

  const handleExtendSubscription = (designerId: number, designerName: string) => {
    const months = prompt(`كم شهر تريد إضافة لاشتراك المصمم "${designerName}"؟ (1-12):`);
    if (months !== null && months.trim() !== '') {
      const monthsNum = parseInt(months.trim());
      if (monthsNum >= 1 && monthsNum <= 12) {
        router.post(`/admin/designers/${designerId}/extend-subscription`, {
          months: monthsNum
        });
      } else {
        alert('يرجى إدخال رقم صحيح بين 1 و 12');
      }
    }
  };

  // Define columns for the data table
  const columns: ColumnDef<Designer>[] = [
    {
      accessorKey: "store_name",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="اسم المتجر" />
      ),
      cell: ({ row }) => {
        const designer = row.original;
        return (
          <div>
            <div className="font-medium text-foreground">{designer.store_name}</div>
            <div className="text-sm text-muted-foreground">{designer.user?.name}</div>
            <div className="text-xs text-muted-foreground">{designer.user?.email}</div>
          </div>
        );
      },
    },
    {
      accessorKey: "subscription_status",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="حالة الاشتراك" />
      ),
      cell: ({ row }) => {
        const status = row.getValue("subscription_status") as string;
        const statusLabels = {
          active: 'نشط',
          pending: 'معلق',
          expired: 'منتهي'
        };
        return (
          <Badge className={getStatusBadge(status)}>
            {statusLabels[status as keyof typeof statusLabels] || status}
          </Badge>
        );
      },
    },
    {
      accessorKey: "rachmat_count",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الرشمات" />
      ),
      cell: ({ row }) => (
        <div className="text-center">
          <span className="font-semibold">{row.getValue("rachmat_count")}</span>
        </div>
      ),
    },
    {
      accessorKey: "total_sales",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="المبيعات" />
      ),
      cell: ({ row }) => (
        <div className="text-center">
          <span className="font-semibold">{row.getValue("total_sales")}</span>
        </div>
      ),
    },
    {
      accessorKey: "unpaid_earnings",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الأرباح المعلقة" />
      ),
      cell: ({ row }) => {
        const earnings = row.getValue("unpaid_earnings");
        const amount = typeof earnings === 'number' ? earnings : 0;
        return (
          <span className="font-semibold text-orange-600 dark:text-orange-400">
            {formatCurrency(amount)}
          </span>
        );
      },
    },
    {
      accessorKey: "created_at",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="تاريخ التسجيل" />
      ),
      cell: ({ row }) => (
        <span className="text-sm text-muted-foreground">
          {formatDate(row.getValue("created_at"))}
        </span>
      ),
    },
    {
      id: "actions",
      enableHiding: false,
      cell: ({ row }) => {
        const designer = row.original;
        const actions: Array<{
          label: string;
          onClick: (designer: Designer) => void;
          variant?: "default" | "destructive";
        }> = [];

        // Always available actions
        actions.push({
          label: "عرض التفاصيل",
          onClick: (designer: Designer) => {
            router.visit(`/admin/designers/${designer.id}`);
          },
        });

        actions.push({
          label: "تعديل الأرباح المدفوعة",
          onClick: (designer: Designer) => {
            router.visit(`/admin/designers/${designer.id}/edit-paid-earnings`);
          },
        });

        
        

        // Delete action (always last)
        actions.push({
          label: "حذف",
          onClick: (designer: Designer) => {
            handleDelete(designer.id, designer.store_name);
          },
          variant: "destructive",
        });

        return (
          <DataTableRowActions
            row={row}
            actions={actions.map(action => ({
              ...action,
              onClick: () => action.onClick(designer)
            }))}
          />
        );
      },
    },
  ];

  return (
    <AppLayout>
      <Head title="إدارة المصممين" />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Modern Header */}
          <AdminPageHeader
            title="إدارة المصممين"
            subtitle="مراجعة وإدارة المصممين المسجلين في المنصة"
          />

        {/* Flash Messages */}
        {flash?.success && (
          <div className="p-4 rounded-lg bg-green-500/10 border border-green-500/20">
            <div className="flex">
              <CheckCircle className="w-5 h-5 text-green-600 dark:text-green-400" />
              <div className="mr-3">
                <p className="text-sm font-medium text-green-800 dark:text-green-200">
                  {flash.success}
                </p>
              </div>
            </div>
          </div>
        )}

        {flash?.error && (
          <div className="p-4 rounded-lg bg-red-500/10 border border-red-500/20">
            <div className="flex">
              <AlertCircle className="w-5 h-5 text-red-600 dark:text-red-400" />
              <div className="mr-3">
                <p className="text-sm font-medium text-red-800 dark:text-red-200">
                  {flash.error}
                </p>
              </div>
            </div>
          </div>
        )}

          {/* Statistics Cards */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            <ModernStatsCard
              title="إجمالي المصممين"
              value={safeStats.total}
              subtitle="Total Designers"
              icon={Users}
              colorScheme="blue"
            />
            <ModernStatsCard
              title="مصممين نشطين"
              value={safeStats.active}
              subtitle="Active Designers"
              icon={UserCheck}
              colorScheme="green"
            />
          
            <ModernStatsCard
              title="إجمالي الرشمات"
              value={safeStats.totalRachmat}
              subtitle="Total Rachmat"
              icon={Crown}
              colorScheme="purple"
            />
            <ModernStatsCard
              title="إجمالي الطلبات"
              value={safeStats.totalOrders}
              subtitle="Total Orders"
              icon={ShoppingCart}
              colorScheme="orange"
            />
          </div>

          {/* Filters */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
            <CardHeader className="relative pb-6">
              <CardTitle className="text-2xl font-bold text-foreground flex items-center gap-4">
                <div className="w-12 h-12 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center shadow-lg">
                  <Filter className="w-6 h-6 text-primary-foreground" />
                </div>
                تصفية النتائج
              </CardTitle>
            </CardHeader>
            <CardContent className="relative">
              <form onSubmit={handleSearch} className="flex flex-wrap gap-4">
                <div className="flex-1 min-w-64 relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                  <Input
                    placeholder="بحث بالاسم أو اسم المتجر..."
                    value={searchTerm}
                    onChange={e => setSearchTerm(e.target.value)}
                    className="pl-10 text-base py-3"
                  />
                </div>

                <Select value={statusFilter} onValueChange={setStatusFilter}>
                  <SelectTrigger className="w-48 text-base py-6">
                    <SelectValue placeholder="فلترة بالحالة" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">جميع الحالات</SelectItem>
                    <SelectItem value="active">نشط</SelectItem>
                    <SelectItem value="pending">معلق</SelectItem>
                    <SelectItem value="expired">منتهي</SelectItem>
                  </SelectContent>
                </Select>

                <Button type="submit" className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300 text-base px-6 py-6 h-auto font-semibold">
                  <Search className="w-5 h-5 mr-2" />
                  بحث
                </Button>

                <Button type="button" variant="outline" onClick={resetFilters} className="border-primary/20 text-primary hover:bg-primary/10 text-base px-6 py-6 h-auto font-semibold">
                  إعادة تعيين
                </Button>
              </form>
            </CardContent>
          </Card>

          {/* Designers Table */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
            <CardHeader className="relative pb-6">
              <CardTitle className="text-2xl font-bold text-foreground">قائمة المصممين</CardTitle>
            </CardHeader>
            <CardContent className="relative">
              {safeDesigners.length === 0 ? (
                <div className="text-center py-12">
                  <div className="w-20 h-20 bg-gradient-to-br from-muted to-muted/70 rounded-full flex items-center justify-center mx-auto mb-6">
                    <Users className="w-10 h-10 text-muted-foreground" />
                  </div>
                  <h3 className="text-2xl font-bold text-foreground mb-4">لا توجد مصممين</h3>
                  <p className="text-muted-foreground max-w-md mx-auto">
                    لم يتم العثور على مصممين يطابقون معايير البحث
                  </p>
                </div>
              ) : (
                <DataTable columns={columns} data={safeDesigners} />
              )}
            </CardContent>
          </Card>

          {/* Pagination */}
          {designers && designers.meta && typeof designers.meta === 'object' && 'last_page' in designers.meta && (designers.meta.last_page as number) > 1 && (
            <div className="flex items-center justify-between">
              <div className="text-sm text-muted-foreground">
                عرض {designers.data.length} من {'total' in designers.meta ? (designers.meta.total as number) : 0} نتيجة
              </div>
            </div>
          )}
        </div>
      </div>
    </AppLayout>
  );
}