import { useState, useEffect, useCallback } from 'react';
import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable, DataTableColumnHeader, DataTableRowActions } from '@/components/ui/data-table';
import { AdminPaymentInfo } from '@/types';
import {
  Wallet,
  CreditCard,
  BarChart3,
  Plus,
  RefreshCw
} from 'lucide-react';

interface Stats {
  total: number;
}

interface FilterState {
  search: string;
}

interface FilterValidation {
  isValid: boolean;
  errors: {
    search?: string;
  };
}

interface FilterUIState {
  isLoading: boolean;
  isValidating: boolean;
  hasActiveFilters: boolean;
  resultCount: number;
  lastAppliedFilters: FilterState | null;
}

interface Props {
  paymentInfos: {
    data: AdminPaymentInfo[];
    links: Record<string, unknown>;
    meta: Record<string, unknown>;
  };
  filters: FilterState;
  stats: Stats;
}

export default function Index({ paymentInfos, filters, stats }: Props) {
  const [localFilters, setLocalFilters] = useState<FilterState>(filters);
  const [filterUI, setFilterUI] = useState<FilterUIState>({
    isLoading: false,
    isValidating: false,
    hasActiveFilters: Object.values(filters).some(value => value !== '' && value !== null),
    resultCount: paymentInfos.data.length,
    lastAppliedFilters: filters,
  });

  // Validation logic
  const validateFilters = useCallback((filterState: FilterState): FilterValidation => {
    const errors: FilterValidation['errors'] = {};

    // Search validation
    if (filterState.search && filterState.search.length > 100) {
      errors.search = 'البحث لا يجب أن يتجاوز 100 حرف';
    }



    return {
      isValid: Object.keys(errors).length === 0,
      errors
    };
  }, []);

  const [validation, setValidation] = useState<FilterValidation>(validateFilters(localFilters));

  // Update validation when filters change
  useEffect(() => {
    setFilterUI(prev => ({ ...prev, isValidating: true }));
    
    const timer = setTimeout(() => {
      const newValidation = validateFilters(localFilters);
      setValidation(newValidation);
      setFilterUI(prev => ({ 
        ...prev, 
        isValidating: false,
        hasActiveFilters: Object.values(localFilters).some(value => value !== '' && value !== null)
      }));
    }, 300);

    return () => clearTimeout(timer);
  }, [localFilters, validateFilters]);

  const applyFilters = useCallback(() => {
    if (!validation.isValid) return;

    setFilterUI(prev => ({ ...prev, isLoading: true }));

    router.get('/admin/payment-info', localFilters, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        setFilterUI(prev => ({
          ...prev,
          isLoading: false,
          lastAppliedFilters: { ...localFilters },
          resultCount: paymentInfos.data.length
        }));
      },
      onError: () => {
        setFilterUI(prev => ({ ...prev, isLoading: false }));
      }
    });
  }, [localFilters, validation.isValid, paymentInfos.data.length]);

  const clearFilters = useCallback(() => {
    const emptyFilters = { search: '' };
    setLocalFilters(emptyFilters);
    
    setFilterUI(prev => ({ ...prev, isLoading: true }));

    router.get('/admin/payment-info', emptyFilters, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        setFilterUI(prev => ({
          ...prev,
          isLoading: false,
          hasActiveFilters: false,
          lastAppliedFilters: emptyFilters,
          resultCount: paymentInfos.data.length
        }));
      },
      onError: () => {
        setFilterUI(prev => ({ ...prev, isLoading: false }));
      }
    });
  }, [paymentInfos.data.length]);

  const refreshData = useCallback(() => {
    setFilterUI(prev => ({ ...prev, isLoading: true }));
    
    router.reload({
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        setFilterUI(prev => ({ ...prev, isLoading: false }));
      },
      onError: () => {
        setFilterUI(prev => ({ ...prev, isLoading: false }));
      }
    });
  }, []);

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ar-DZ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const columns: ColumnDef<AdminPaymentInfo>[] = [
    {
      accessorKey: "ccp_number",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="رقم CCP" />
      ),
      cell: ({ row }) => (
        <span className="text-sm font-mono">
          {row.getValue("ccp_number") || '-'}
        </span>
      ),
    },
    {
      accessorKey: "nom",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="اسم صاحب الحساب" />
      ),
      cell: ({ row }) => (
        <span className="text-sm">
          {row.getValue("nom") || '-'}
        </span>
      ),
    },
    {
      accessorKey: "baridimob",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="رقم BaridiMob" />
      ),
      cell: ({ row }) => (
        <span className="text-sm font-mono">
          {row.getValue("baridimob") || '-'}
        </span>
      ),
    },
    {
      accessorKey: "created_at",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="التاريخ" />
      ),
      cell: ({ row }) => (
        <span className="text-sm text-gray-600">
          {formatDate(row.getValue("created_at"))}
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
                onClick: (paymentInfo: AdminPaymentInfo) => {
                  router.visit(`/admin/payment-info/${paymentInfo.id}`);
                },
              },
              {
                label: "تعديل",
                onClick: (paymentInfo: AdminPaymentInfo) => {
                  router.visit(`/admin/payment-info/${paymentInfo.id}/edit`);
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
        { title: 'معلومات الدفع', href: '/admin/payment-info' }
      ]}
    >
      <Head title="إدارة معلومات الدفع - Payment Info Management" />

      <div className="space-y-8 p-6">
        {/* Header Section */}
        <div className="relative">
          <div className="absolute inset-0 bg-gradient-to-r from-purple-600/20 via-blue-600/20 to-indigo-600/20 rounded-3xl"></div>
          <div className="relative bg-card/80 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl">
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
              <div className="space-y-2">
                <div className="flex items-center gap-3">
                  <div className="p-3 bg-gradient-to-br from-purple-500 to-blue-600 rounded-2xl shadow-lg">
                    <Wallet className="h-8 w-8 text-white" />
                  </div>
                  <div>
                    <h1 className="text-4xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent">
                      معلومات الدفع
                    </h1>
                    <p className="text-2xl text-muted-foreground mt-3 leading-relaxed font-medium">
                      إدارة معلومات الدفع
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-1 gap-6">
          <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
            <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-blue-500/5"></div>
            <CardHeader className="relative pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-base font-bold text-muted-foreground uppercase tracking-wider">المجموع</CardTitle>
                <div className="p-2 bg-blue-500/10 rounded-xl group-hover:bg-blue-500/20 transition-colors">
                  <BarChart3 className="h-5 w-5 text-blue-600" />
                </div>
              </div>
            </CardHeader>
            <CardContent className="relative">
              <div className="text-4xl font-bold text-blue-600 mb-2">{stats.total}</div>
              <p className="text-sm text-muted-foreground font-medium">إجمالي معلومات الدفع</p>
            </CardContent>
          </Card>
        </div>

        {/* Main Content */}
        <Card className="border-0 shadow-xl bg-card/50 backdrop-blur-sm">
          <CardHeader className="border-b border-border/50 bg-muted/30">
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-primary/10 rounded-lg">
                  <CreditCard className="h-5 w-5 text-primary" />
                </div>
                <div>
                  <CardTitle className="text-xl font-bold">قائمة معلومات الدفع</CardTitle>
                  <p className="text-sm text-muted-foreground mt-1">
                    إدارة ومتابعة معلومات الدفع للمديرين
                  </p>
                </div>
              </div>
              
              <div className="flex items-center gap-3">
                <Button
                  onClick={refreshData}
                  variant="outline"
                  size="sm"
                  disabled={filterUI.isLoading}
                  className="gap-2"
                >
                  <RefreshCw className={`h-4 w-4 ${filterUI.isLoading ? 'animate-spin' : ''}`} />
                  تحديث
                </Button>
                
                <Button
                  onClick={() => router.visit('/admin/payment-info/create')}
                  className="gap-2 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700"
                >
                  <Plus className="h-4 w-4" />
                  إضافة معلومات دفع
                </Button>
              </div>
            </div>
          </CardHeader>

          <CardContent className="p-6">
            <DataTable
              columns={columns}
              data={paymentInfos.data}
              searchPlaceholder="البحث في معلومات الدفع..."
            />
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
