import React, { useState } from 'react';
import { Head, router, Link, usePage } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { ColumnDef } from '@tanstack/react-table';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable, DataTableColumnHeader, DataTableRowActions } from '@/components/ui/data-table';
import CustomPagination from '@/components/ui/custom-pagination';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { ModernStatsCard } from '@/components/ui/modern-stats-card';
import { PricingPlan, PageProps } from '@/types';
import { cn } from '@/lib/utils';
import {
  DollarSign,
  Plus,
  Search,
  Calendar,
  TrendingUp,
  Package,
  XCircle,
  CheckCircle,
  AlertCircle,
  Filter
} from 'lucide-react';

interface Stats {
  total: number;
  active: number;
  inactive: number;
}

interface Props extends PageProps {
  pricingPlans?: {
    data: PricingPlan[];
    links: Record<string, unknown>[];
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
  filters?: {
    search?: string;
    status?: string;
  };
  stats?: Stats;
}

export default function Index({ pricingPlans, filters = {}, stats }: Props) {
  const { flash } = usePage<PageProps>().props;
  const [searchTerm, setSearchTerm] = useState(filters?.search || '');
  const [statusFilter, setStatusFilter] = useState(filters?.status || 'all');
  const [togglingPlan, setTogglingPlan] = useState<number | null>(null);

  // Provide default values if undefined
  const safeStats = stats || { total: 0, active: 0, inactive: 0 };
  const safePricingPlans = pricingPlans?.data || [];

  // Use the correct pagination data from pricingPlans object, not meta
  const paginationData = {
    current_page: pricingPlans?.current_page || 1,
    last_page: pricingPlans?.last_page || 1,
    total: pricingPlans?.total || 0,
    per_page: pricingPlans?.per_page || 15,
    from: pricingPlans?.from || 0,
    to: pricingPlans?.to || 0
  };



  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/pricing-plans', {
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
    router.get('/admin/pricing-plans', {}, {
      preserveState: true,
      replace: true,
    });
  };

  const getStatusBadge = (isActive: boolean) => {
    return isActive 
      ? <Badge className="bg-green-100 text-green-800">نشط</Badge>
      : <Badge className="bg-red-100 text-red-800">غير نشط</Badge>;
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ').format(amount) + ' دج';
  };

  const getDurationText = (months: number) => {
    if (months === 1) return 'شهر واحد';
    if (months === 2) return 'شهران';
    if (months <= 10) return months + ' أشهر';
    return months + ' شهر';
  };

  const handleToggleStatus = (planId: number) => {
    const plan = safePricingPlans.find(p => p.id === planId);
    if (!plan || togglingPlan === planId) return;

    const action = plan.is_active ? 'إلغاء تفعيل' : 'تفعيل';
    const confirmMessage = `هل أنت متأكد من ${action} خطة "${plan.name}"؟`;

    if (confirm(confirmMessage)) {
      setTogglingPlan(planId);
      router.post(`/admin/pricing-plans/${planId}/toggle-status`, {}, {
        preserveScroll: true, // Keep the current scroll position
        onSuccess: () => {
          setTogglingPlan(null);
          // The page will automatically refresh with updated data while preserving scroll position
        },
        onError: (errors) => {
          setTogglingPlan(null);
          console.error('Toggle status failed:', errors);
          alert('حدث خطأ أثناء تغيير حالة الخطة');
        }
      });
    }
  };

  const handleDelete = (planId: number, planName: string) => {
    const confirmMessage = `هل أنت متأكد من حذف خطة التسعير "${planName}"؟\n\nتحذير: هذا الإجراء لا يمكن التراجع عنه.`;
    
    if (confirm(confirmMessage)) {
      router.delete(`/admin/pricing-plans/${planId}`, {
        onSuccess: () => {
          // Success message will be handled by the backend flash message
        },
        onError: (errors) => {
          console.error('Delete failed:', errors);
          // Error will be displayed via backend error handling
        }
      });
    }
  };

  const columns: ColumnDef<PricingPlan>[] = [
    {
      accessorKey: 'name',
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="اسم الخطة" />
      ),
      cell: ({ row }) => (
        <div className="font-medium text-lg">{row.getValue('name')}</div>
      ),
    },
    {
      accessorKey: 'duration_months',
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="المدة" />
      ),
      cell: ({ row }) => (
        <div className="flex items-center gap-2">
          <Calendar className="h-4 w-4 text-muted-foreground" />
          <span className="text-base">{getDurationText(row.getValue('duration_months'))}</span>
        </div>
      ),
    },
    {
      accessorKey: 'price',
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="السعر" />
      ),
      cell: ({ row }) => (
        <div className="flex items-center gap-2">
          <DollarSign className="h-4 w-4 text-muted-foreground" />
          <span className="font-semibold text-lg text-primary">
            {formatCurrency(row.getValue('price'))}
          </span>
        </div>
      ),
    },
    {
      accessorKey: 'is_active',
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الحالة" />
      ),
      cell: ({ row }) => {
        const plan = row.original;
        const isToggling = togglingPlan === plan.id;
        return (
          <div className="flex items-center gap-4 py-2">
            {/* RTL-Aware Toggle Switch */}
            <div className="relative">
              <button
                onClick={() => handleToggleStatus(plan.id)}
                disabled={isToggling}
                className={cn(
                  "toggle-switch",
                  plan.is_active ? "active" : "inactive",
                  isToggling && "disabled"
                )}
                aria-pressed={plan.is_active}
                aria-label={`${plan.is_active ? 'إلغاء تفعيل' : 'تفعيل'} ${plan.name}`}
              >
                {/* Toggle Circle */}
                <span className="toggle-circle" />

                {/* Optional: Add icons inside the toggle */}
                <div className="absolute inset-0 flex items-center justify-between px-1.5">
                  {plan.is_active && (
                    <div className="w-3 h-3 text-white opacity-90">
                      <svg viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                    </div>
                  )}
                  {!plan.is_active && (
                    <div className="w-3 h-3 text-gray-400 opacity-90 mr-auto">
                      <svg viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3">
                        <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </div>
                  )}
                </div>
              </button>
            </div>

            {/* Status Badge */}
            {getStatusBadge(plan.is_active)}
          </div>
        );
      },
    },
    {
      id: "actions",
      enableHiding: false,
      cell: ({ row }) => {
        const plan = row.original;
        const actions: Array<{
          label: string;
          onClick: (plan: PricingPlan) => void;
          variant?: "default" | "destructive";
        }> = [
          
          {
            label: "تعديل",
            onClick: (plan: PricingPlan) => {
              router.visit(route('admin.pricing-plans.edit', plan.id));
            },
          },
          {
            label: "حذف",
            onClick: (plan: PricingPlan) => {
              handleDelete(plan.id, plan.name);
            },
            variant: "destructive",
          },
        ];

        return (
          <DataTableRowActions
            row={row}
            actions={actions.map(action => ({
              ...action,
              onClick: () => action.onClick(plan)
            }))}
          />
        );
      },
    },
  ];

  return (
    <AppLayout>
      <Head title="إدارة خطط الأسعار" />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Modern Header */}
          <AdminPageHeader
            title="إدارة خطط الأسعار"
            subtitle="إدارة خطط الاشتراك والأسعار للمصممين"
          >
            <Link href={route('admin.pricing-plans.create')}>
              <Button className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-xl hover:shadow-2xl transition-all duration-300 text-lg px-8 py-6 h-auto font-bold">
                <Plus className="w-6 h-6 mr-3" />
                إضافة خطة جديدة
              </Button>
            </Link>
          </AdminPageHeader>

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

          {/* Statistics Cards */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <ModernStatsCard
              title="إجمالي الخطط"
              value={safeStats.total}
              subtitle="Total Plans"
              icon={Package}
              colorScheme="blue"
            />
            <ModernStatsCard
              title="خطط نشطة"
              value={safeStats.active}
              subtitle="Active Plans"
              icon={TrendingUp}
              colorScheme="green"
            />
            <ModernStatsCard
              title="خطط غير نشطة"
              value={safeStats.inactive}
              subtitle="Inactive Plans"
              icon={XCircle}
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
                    placeholder="بحث بالاسم..."
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
                    <SelectItem value="all">جميع الخطط</SelectItem>
                    <SelectItem value="active">نشط</SelectItem>
                    <SelectItem value="inactive">غير نشط</SelectItem>
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

          {/* Pricing Plans Table */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
            <CardHeader className="relative pb-6">
              <CardTitle className="text-2xl font-bold text-foreground">قائمة خطط الأسعار</CardTitle>
            </CardHeader>
            <CardContent className="relative">
              {safePricingPlans.length === 0 ? (
                <div className="text-center py-12">
                  <div className="w-20 h-20 bg-gradient-to-br from-muted to-muted/70 rounded-full flex items-center justify-center mx-auto mb-6">
                    <DollarSign className="w-10 h-10 text-muted-foreground" />
                  </div>
                  <h3 className="text-2xl font-bold text-foreground mb-4">لا توجد خطط أسعار</h3>
                  <p className="text-muted-foreground max-w-md mx-auto">
                    لم يتم العثور على خطط تطابق معايير البحث
                  </p>
                </div>
              ) : (
                <div className="space-y-6">
                  <DataTable columns={columns} data={safePricingPlans} />

                  {paginationData.last_page > 1 && (
                    <CustomPagination
                      currentPage={paginationData.current_page}
                      totalPages={paginationData.last_page}
                      totalItems={paginationData.total}
                      itemsPerPage={paginationData.per_page}
                      onPageChange={(page) => {
                        router.get('/admin/pricing-plans', {
                          page,
                          search: searchTerm || undefined,
                          status: statusFilter !== 'all' ? statusFilter : undefined,
                        }, {
                          preserveState: true,
                          preserveScroll: true,
                        });
                      }}
                    />
                  )}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
