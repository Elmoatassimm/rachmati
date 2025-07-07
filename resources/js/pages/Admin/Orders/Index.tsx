import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Skeleton } from '@/components/ui/skeleton';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { DataTableColumnHeader } from '@/components/ui/data-table';
import { DataTablePagination } from '@/components/ui/data-table-pagination';
import { usePagination } from '@/hooks/use-pagination';
import {
  ShoppingCart,
  Clock,
  CheckCircle,
  XCircle,
  Package,
  Search,
  Filter,
  AlertCircle,
  Loader2,
  RefreshCw,
  Eye
} from 'lucide-react';

interface User {
  name: string;
  email: string;
}

interface Designer {
  user: User;
}

interface Category {
  name: string;
}

interface Rachma {
  title: string;
  title_ar: string;
  title_fr: string;
  designer: Designer;
  categories: Category[];
}

interface OrderItem {
  id: number;
  rachma_id: number;
  price: number;
  rachma: Rachma;
}

interface Order {
  id: number;
  client: User;
  rachma?: Rachma; // For backward compatibility
  order_items?: OrderItem[]; // For multi-item orders
  amount: number;
  status: string;
  created_at: string;
}

interface Stats {
  total: number;
  pending: number;
  completed: number;
  rejected: number;
  totalRevenue: number;
}

interface FilterState {
  search: string;
  status: string;
  dateFrom: string;
  dateTo: string;
}

interface FilterValidation {
  isValid: boolean;
  errors: {
    search?: string;
    status?: string;
    dateFrom?: string;
    dateTo?: string;
    dateRange?: string;
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
  orders?: {
    data: Order[];
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{
      url: string | null;
      label: string;
      active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
  };
  filters?: {
    status?: string;
    search?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
  };
  stats?: Stats;
}

export default function Index({ orders, filters = {}, stats }: Props) {
  // Enhanced filter state management
  const [filterState, setFilterState] = useState<FilterState>({
    search: filters?.search || '',
    status: filters?.status || 'all',
    dateFrom: filters?.date_from || '',
    dateTo: filters?.date_to || '',
  });

  const [uiState, setUiState] = useState<FilterUIState>({
    isLoading: false,
    isValidating: false,
    hasActiveFilters: false,
    resultCount: 0,
    lastAppliedFilters: null,
  });

  const [searchDebounceTimer, setSearchDebounceTimer] = useState<NodeJS.Timeout | null>(null);

  // Smart filter validation function
  const validateFilters = useCallback((filters: FilterState): FilterValidation => {
    const errors: FilterValidation['errors'] = {};
    let isValid = true;

    // Validate search term length - allow single digit for order ID search
    if (filters.search && filters.search.length > 0) {
      const isNumeric = /^\d+$/.test(filters.search);
      // Allow single digit if it's numeric (order ID), otherwise require at least 2 characters
      if (!isNumeric && filters.search.length < 2) {
        errors.search = 'يجب أن يكون البحث أكثر من حرف واحد أو رقم طلب صحيح';
        isValid = false;
      }
    }

    // Validate date range
    if (filters.dateFrom && filters.dateTo) {
      const fromDate = new Date(filters.dateFrom);
      const toDate = new Date(filters.dateTo);

      if (fromDate > toDate) {
        errors.dateRange = 'تاريخ البداية يجب أن يكون قبل تاريخ النهاية';
        isValid = false;
      }

      // Check if date range is too far in the future
      const today = new Date();
      if (fromDate > today) {
        errors.dateFrom = 'لا يمكن البحث في تواريخ مستقبلية';
        isValid = false;
      }
    }

    // Validate individual dates
    if (filters.dateFrom) {
      const fromDate = new Date(filters.dateFrom);
      if (isNaN(fromDate.getTime())) {
        errors.dateFrom = 'تاريخ البداية غير صحيح';
        isValid = false;
      }
    }

    if (filters.dateTo) {
      const toDate = new Date(filters.dateTo);
      if (isNaN(toDate.getTime())) {
        errors.dateTo = 'تاريخ النهاية غير صحيح';
        isValid = false;
      }
    }

    return { isValid, errors };
  }, []);

  // Check if filters have meaningful values
  const hasActiveFilters = useMemo(() => {
    return !!(
      (filterState.search && filterState.search.trim().length >= 2) ||
      (filterState.status && filterState.status !== 'all') ||
      filterState.dateFrom ||
      filterState.dateTo
    );
  }, [filterState]);

  // Provide default values for stats if undefined
  const safeStats = stats || {
    total: 0,
    pending: 0,
    completed: 0,
    rejected: 0,
    totalRevenue: 0
  };

  // Provide default values for orders if undefined
  const safeOrders = orders || {
    data: [],
    current_page: 1,
    first_page_url: '',
    from: 0,
    last_page: 1,
    last_page_url: '',
    links: [],
    next_page_url: null,
    path: '',
    per_page: 15,
    prev_page_url: null,
    to: 0,
    total: 0
  };

  // Update UI state when filters change
  useEffect(() => {
    setUiState(prev => ({
      ...prev,
      hasActiveFilters,
      resultCount: safeOrders.data.length,
    }));
  }, [hasActiveFilters, safeOrders.data.length]);

  const { isLoading: isPaginationLoading, handlePageChange } = usePagination('/admin/orders', {
    onSuccess: () => {
      setUiState(prev => ({
        ...prev,
        lastAppliedFilters: { ...filterState },
      }));
    }
  });

  // Update applyFilters to use handlePageChange
  const applyFilters = useCallback((filters: FilterState) => {
    const validation = validateFilters(filters);

    if (!validation.isValid) {
      return;
    }

    const queryParams: Record<string, string> = {};

    if (filters.search && filters.search.trim().length >= 2) {
      queryParams.search = filters.search.trim();
    }

    if (filters.status && filters.status !== 'all') {
      queryParams.status = filters.status;
    }

    if (filters.dateFrom) {
      queryParams.date_from = filters.dateFrom;
    }

    if (filters.dateTo) {
      queryParams.date_to = filters.dateTo;
    }

    handlePageChange(1, queryParams);
  }, [handlePageChange, validateFilters]);

  // Debounced search handler
  const handleSearchChange = useCallback((value: string) => {
    setFilterState(prev => ({ ...prev, search: value }));

    // Clear existing timer
    if (searchDebounceTimer) {
      clearTimeout(searchDebounceTimer);
    }

    // Set new timer for debounced search
    const timer = setTimeout(() => {
      const isNumeric = /^\d+$/.test(value);
      
      if (value.length === 0 || value.length >= 2 || (isNumeric && value.length >= 1)) {
        // Auto-apply search if it's empty, has enough characters, or is numeric (order ID)
        const newFilters = { ...filterState, search: value };
        const validation = validateFilters(newFilters);

        if (validation.isValid) {
          applyFilters(newFilters);
        }
      }
    }, 300);

    setSearchDebounceTimer(timer);
  }, [filterState, searchDebounceTimer, validateFilters, applyFilters]);

  // Handle other filter changes
  const handleStatusChange = useCallback((value: string) => {
    setFilterState(prev => ({ ...prev, status: value }));
  }, []);

  const handleDateFromChange = useCallback((value: string) => {
    setFilterState(prev => ({ ...prev, dateFrom: value }));
  }, []);

  const handleDateToChange = useCallback((value: string) => {
    setFilterState(prev => ({ ...prev, dateTo: value }));
  }, []);

  // Handle manual search button click
  const handleSearch = useCallback(() => {
    applyFilters(filterState);
  }, [filterState, applyFilters]);

  // Reset all filters
  const resetFilters = useCallback(() => {
    const emptyFilters: FilterState = {
      search: '',
      status: 'all',
      dateFrom: '',
      dateTo: '',
    };

    setFilterState(emptyFilters);
    setUiState(prev => ({ ...prev, isLoading: true }));

    router.visit('/admin/orders', {
      method: 'get',
      data: {},
      preserveState: true,
      replace: true,
      preserveScroll: false, // Ensure proper scrolling behavior during reset
      onSuccess: () => {
        setUiState(prev => ({
          ...prev,
          isLoading: false,
          lastAppliedFilters: null,
        }));
      },
      onError: () => {
        setUiState(prev => ({ ...prev, isLoading: false }));
      },
    });
  }, []);

  // Get current filter validation
  const currentValidation = useMemo(() => {
    return validateFilters(filterState);
  }, [filterState, validateFilters]);

  // Smart filter suggestions based on current data
  const filterSuggestions = useMemo(() => {
    if (!uiState.hasActiveFilters || safeOrders.data.length > 0) return [];

    const suggestions = [];

    // If no results with status filter, suggest removing it
    if (filterState.status !== 'all') {
      suggestions.push({
        text: 'جرب إزالة فلتر الحالة',
        action: () => handleStatusChange('all'),
      });
    }

    // If no results with date range, suggest expanding it
    if (filterState.dateFrom || filterState.dateTo) {
      suggestions.push({
        text: 'جرب توسيع نطاق التاريخ',
        action: () => {
          handleDateFromChange('');
          handleDateToChange('');
        },
      });
    }

    // If search term is too specific, suggest shortening it
    if (filterState.search && filterState.search.length > 5) {
      const isNumeric = /^\d+$/.test(filterState.search);
      if (!isNumeric) {
        suggestions.push({
          text: 'جرب تقصير مصطلح البحث',
          action: () => handleSearchChange(filterState.search.substring(0, 3)),
        });
      }
    }

    return suggestions;
  }, [uiState.hasActiveFilters, safeOrders.data.length, filterState, handleStatusChange, handleDateFromChange, handleDateToChange, handleSearchChange]);

  // Cleanup timer on unmount
  useEffect(() => {
    return () => {
      if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
      }
    };
  }, [searchDebounceTimer]);

  // Update UI state when filters or pagination loading changes
  useEffect(() => {
    setUiState(prev => ({
      ...prev,
      isLoading: isPaginationLoading,
      hasActiveFilters,
      resultCount: safeOrders.data.length,
    }));
  }, [isPaginationLoading, hasActiveFilters, safeOrders.data.length]);

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

  // Helper function to get status badge styling
  const getStatusBadge = (status: string) => {
    const variants = {
      pending: 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
      completed: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
      rejected: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
    };
    return variants[status as keyof typeof variants] || 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600';
  };

  // Helper function to get status icon
  const getStatusIcon = (status: string) => {
    const icons = {
      pending: <Clock className="w-3 h-3" />,
      completed: <CheckCircle className="w-3 h-3" />,
      rejected: <XCircle className="w-3 h-3" />,
    };
    return icons[status as keyof typeof icons] || <Clock className="w-3 h-3" />;
  };

  // Helper function to get status label
  const getStatusLabel = (status: string) => {
    const labels = {
      pending: 'معلق',
      completed: 'مكتمل',
      rejected: 'مرفوض',
    };
    return labels[status as keyof typeof labels] || status;
  };

  // Define columns for the data table
  const columns: ColumnDef<Order>[] = [
    {
      accessorKey: "id",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="رقم الطلب" />
      ),
      cell: ({ row }) => (
        <span className="font-mono text-sm">#{row.getValue("id")}</span>
      ),
    },
    {
      accessorKey: "client.name",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="العميل" />
      ),
      cell: ({ row }) => {
        const order = row.original;
        return (
          <div>
            <div className="font-medium">{order.client?.name || 'غير محدد'}</div>
            <div className="text-sm text-gray-500">{order.client?.email || 'غير محدد'}</div>
          </div>
        );
      },
    },
    {
      accessorKey: "rachma.title",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الرشمات" />
      ),
      cell: ({ row }) => {
        const order = row.original;

        // Handle multi-item orders
        if (order.order_items && order.order_items.length > 0) {
          if (order.order_items.length === 1) {
            const item = order.order_items[0];
            const rachmaTitle = item.rachma?.title || item.rachma?.title_ar || item.rachma?.title_fr || 'غير محدد';
            return (
              <div>
                <div className="font-medium">{rachmaTitle}</div>
                <div className="text-sm text-gray-500">{item.rachma?.categories?.[0]?.name || 'غير محدد'}</div>
              </div>
            );
          } else {
            return (
              <div>
                <div className="font-medium">{order.order_items.length} رشمات</div>
                <div className="text-sm text-gray-500">طلب متعدد الرشمات</div>
              </div>
            );
          }
        }

        // Handle single-item orders (backward compatibility)
        if (order.rachma) {
          const rachmaTitle = order.rachma?.title || order.rachma?.title_ar || order.rachma?.title_fr || 'غير محدد';
          return (
            <div>
              <div className="font-medium">{rachmaTitle}</div>
              <div className="text-sm text-gray-500">{order.rachma?.categories?.[0]?.name || 'غير محدد'}</div>
            </div>
          );
        }

        return <span className="text-muted-foreground">غير محدد</span>;
      },
    },
    {
      accessorKey: "rachma.designer.user.name",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="المصمم" />
      ),
      cell: ({ row }) => {
        const order = row.original;

        // Handle multi-item orders
        if (order.order_items && order.order_items.length > 0) {
          const uniqueDesigners = [...new Set(order.order_items.map((item: OrderItem) => item.rachma?.designer?.user?.name).filter(Boolean))];

          if (uniqueDesigners.length === 1) {
            return <span className="text-sm">{uniqueDesigners[0]}</span>;
          } else if (uniqueDesigners.length > 1) {
            return <span className="text-sm">{uniqueDesigners.length} مصممين</span>;
          }
        }

        // Handle single-item orders (backward compatibility)
        if (order.rachma?.designer?.user?.name) {
          return <span className="text-sm">{order.rachma.designer.user.name}</span>;
        }

        return <span className="text-sm text-muted-foreground">غير محدد</span>;
      },
    },
    {
      accessorKey: "amount",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="المبلغ" />
      ),
      cell: ({ row }) => (
        <span className="font-semibold">{formatCurrency(row.getValue("amount"))}</span>
      ),
    },
    {
      accessorKey: "status",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الحالة" />
      ),
      cell: ({ row }) => {
        const order = row.original;
        return (
          <Badge
            className={`${getStatusBadge(order.status)} flex items-center gap-1.5 px-3 py-1.5 border font-medium`}
          >
            {getStatusIcon(order.status)}
            <span>{getStatusLabel(order.status)}</span>
          </Badge>
        );
      },
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
        const order = row.original;
        return (
          <Button
            variant="ghost"
            size="icon"
            onClick={() => router.visit(`/admin/orders/${order.id}`)}
            className="hover:bg-muted"
          >
            <Eye className="w-4 h-4 text-muted-foreground hover:text-foreground transition-colors" />
          </Button>
        );
      },
    },
  ];

  return (
    <AppLayout 
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'الطلبات', href: '/admin/orders' }
      ]}
    >
      <Head title="إدارة الطلبات - Orders Management" />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Modern Header */}
          <AdminPageHeader
            title="إدارة الطلبات"
            subtitle="إدارة ومتابعة جميع الطلبات والمعاملات"
          />

          {/* Revolutionary Stats Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {/* Total Orders */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-blue-500/5"></div>
              <CardHeader className="relative pb-3">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-base font-bold text-muted-foreground uppercase tracking-wider">المجموع</CardTitle>
                  <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <ShoppingCart className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0">
                <div className="text-4xl font-black bg-gradient-to-r from-blue-600 to-blue-500 bg-clip-text text-transparent">
                  {safeStats.total.toLocaleString()}
                </div>
                <p className="text-sm text-muted-foreground mt-2 font-medium">Total Orders</p>
                <div className="mt-3 h-1 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"></div>
              </CardContent>
            </Card>

            {/* Pending Orders */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
              <div className="absolute inset-0 bg-gradient-to-br from-amber-500/10 via-transparent to-amber-500/5"></div>
              <CardHeader className="relative pb-3">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-sm font-bold text-muted-foreground uppercase tracking-wider">معلقة</CardTitle>
                  <div className="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Clock className="w-5 h-5 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0">
                <div className="text-3xl font-black bg-gradient-to-r from-amber-600 to-amber-500 bg-clip-text text-transparent">
                  {safeStats.pending.toLocaleString()}
                </div>
                <p className="text-xs text-muted-foreground mt-1">Pending</p>
                <div className="mt-3 h-1 bg-gradient-to-r from-amber-500 to-amber-600 rounded-full"></div>
              </CardContent>
            </Card>

            {/* Completed Orders */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
              <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-emerald-500/5"></div>
              <CardHeader className="relative pb-3">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-sm font-bold text-muted-foreground uppercase tracking-wider">مكتملة</CardTitle>
                  <div className="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <CheckCircle className="w-5 h-5 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0">
                <div className="text-3xl font-black bg-gradient-to-r from-emerald-600 to-emerald-500 bg-clip-text text-transparent">
                  {safeStats.completed.toLocaleString()}
                </div>
                <p className="text-xs text-muted-foreground mt-1">Completed</p>
                <div className="mt-3 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full"></div>
              </CardContent>
            </Card>

            {/* Rejected Orders */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
              <div className="absolute inset-0 bg-gradient-to-br from-red-500/10 via-transparent to-red-500/5"></div>
              <CardHeader className="relative pb-3">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-sm font-bold text-muted-foreground uppercase tracking-wider">مرفوضة</CardTitle>
                  <div className="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <XCircle className="w-5 h-5 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0">
                <div className="text-3xl font-black bg-gradient-to-r from-red-600 to-red-500 bg-clip-text text-transparent">
                  {safeStats.rejected.toLocaleString()}
                </div>
                <p className="text-xs text-muted-foreground mt-1">Rejected</p>
                <div className="mt-3 h-1 bg-gradient-to-r from-red-500 to-red-600 rounded-full"></div>
              </CardContent>
            </Card>
          </div>

        {/* Enhanced Smart Filters */}
        <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm">
          <CardHeader className="pb-6">
            <div className="flex items-center justify-between">
              <CardTitle className="text-2xl font-semibold text-foreground flex items-center gap-3">
                <Filter className="w-6 h-6" />
                البحث والتصفية الذكية
              </CardTitle>
              {uiState.hasActiveFilters && (
                <Badge variant="secondary" className="flex items-center gap-2">
                  <Search className="w-4 h-4" />
                  فلاتر نشطة
                </Badge>
              )}
            </div>
          </CardHeader>
          <CardContent>
            {/* Filter validation errors */}
            {!currentValidation.isValid && (
              <Alert className="mb-6 border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive">
                <AlertCircle className="h-4 w-4" />
                <AlertDescription>
                  <div className="space-y-1">
                    {Object.entries(currentValidation.errors).map(([field, error]) => (
                      <div key={field} className="text-sm">{error}</div>
                    ))}
                  </div>
                </AlertDescription>
              </Alert>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
              <div className="space-y-3">
                <label className="block text-base font-semibold text-foreground">
                  البحث
                </label>
                <div className="relative">
                  <input
                    type="text"
                    value={filterState.search}
                    onChange={(e) => handleSearchChange(e.target.value)}
                    placeholder="رقم الطلب، اسم العميل، عنوان الرشمة..."
                    className={`w-full h-12 px-4 py-3 text-base border rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors ${
                      currentValidation.errors.search
                        ? 'border-destructive bg-destructive/5'
                        : 'border-input bg-background'
                    }`}
                  />
                  {filterState.search && filterState.search.length > 0 && (() => {
                    const isNumeric = /^\d+$/.test(filterState.search);
                    const needsMoreChars = !isNumeric && filterState.search.length < 2;
                    return needsMoreChars ? (
                      <div className="absolute top-full left-0 mt-1 text-xs text-muted-foreground">
                        أدخل حرفين على الأقل للبحث أو رقم طلب صحيح
                      </div>
                    ) : null;
                  })()}
                </div>
              </div>

              <div className="space-y-3">
                <label className="block text-base font-semibold text-foreground">
                  الحالة
                </label>
                <select
                  value={filterState.status}
                  onChange={(e) => handleStatusChange(e.target.value)}
                  className="w-full h-12 px-4 py-3 text-base border border-input bg-background rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors"
                >
                  <option value="all">جميع الحالات</option>
                  <option value="pending">معلقة</option>
                  <option value="completed">مكتملة</option>
                  <option value="rejected">مرفوضة</option>
                </select>
              </div>

              <div className="space-y-3">
                <label className="block text-base font-semibold text-foreground">
                  من تاريخ
                </label>
                <div className="relative">
                  <input
                    type="date"
                    value={filterState.dateFrom}
                    onChange={(e) => handleDateFromChange(e.target.value)}
                    className={`w-full h-12 px-4 py-3 text-base border rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors ${
                      currentValidation.errors.dateFrom
                        ? 'border-destructive bg-destructive/5'
                        : 'border-input bg-background'
                    }`}
                  />
                  
                </div>
              </div>

              <div className="space-y-3">
                <label className="block text-base font-semibold text-foreground">
                  إلى تاريخ
                </label>
                <div className="relative">
                  <input
                    type="date"
                    value={filterState.dateTo}
                    onChange={(e) => handleDateToChange(e.target.value)}
                    className={`w-full h-12 px-4 py-3 text-base border rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors ${
                      currentValidation.errors.dateTo || currentValidation.errors.dateRange
                        ? 'border-destructive bg-destructive/5'
                        : 'border-input bg-background'
                    }`}
                  />
                  
                </div>
              </div>

              <div className="flex items-end space-x-2 space-x-reverse">
                <Button
                  onClick={handleSearch}
                  disabled={uiState.isLoading || !currentValidation.isValid}
                  className="flex-1 h-12 text-base px-6"
                >
                  {uiState.isLoading ? (
                    <>
                      <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                      جاري البحث...
                    </>
                  ) : (
                    <>
                      <Search className="w-4 h-4 mr-2" />
                      بحث
                    </>
                  )}
                </Button>
                <Button
                  onClick={resetFilters}
                  variant="outline"
                  disabled={uiState.isLoading}
                  className="h-12 text-base px-6"
                >
                  {uiState.isLoading ? (
                    <Loader2 className="w-4 h-4 animate-spin" />
                  ) : (
                    <>
                      <RefreshCw className="w-4 h-4 mr-2" />
                      إعادة تعيين
                    </>
                  )}
                </Button>
              </div>
            </div>

            {/* Filter Summary */}
            {uiState.hasActiveFilters && (
              <div className="mt-6 p-4 bg-muted/50 rounded-lg">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <Filter className="w-4 h-4" />
                    <span>الفلاتر المطبقة:</span>
                    {filterState.search && (
                      <Badge variant="outline" className="text-xs">
                        البحث: {filterState.search}
                      </Badge>
                    )}
                    {filterState.status !== 'all' && (
                      <Badge variant="outline" className="text-xs">
                        الحالة: {filterState.status}
                      </Badge>
                    )}
                    {filterState.dateFrom && (
                      <Badge variant="outline" className="text-xs">
                        من: {filterState.dateFrom}
                      </Badge>
                    )}
                    {filterState.dateTo && (
                      <Badge variant="outline" className="text-xs">
                        إلى: {filterState.dateTo}
                      </Badge>
                    )}
                  </div>
                  <div className="text-sm text-muted-foreground">
                    النتائج: {safeOrders.data.length}
                  </div>
                </div>
              </div>
            )}

            {/* Smart Filter Suggestions */}
            {filterSuggestions.length > 0 && (
              <div className="mt-4 p-4 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div className="flex items-start gap-3">
                  <div className="w-6 h-6 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <Search className="w-3 h-3 text-blue-600 dark:text-blue-400" />
                  </div>
                  <div className="flex-1">
                    <h4 className="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
                      اقتراحات للحصول على نتائج أفضل:
                    </h4>
                    <div className="space-y-2">
                      {filterSuggestions.map((suggestion, index) => (
                        <button
                          key={index}
                          onClick={suggestion.action}
                          className="block text-sm text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100 hover:underline transition-colors"
                        >
                          • {suggestion.text}
                        </button>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Enhanced Orders Data Table */}
        <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm">
          <CardHeader className="pb-6">
            <div className="flex items-center justify-between">
              <CardTitle className="text-2xl font-semibold text-foreground flex items-center gap-3">
                <Package className="w-6 h-6" />
                قائمة الطلبات
              </CardTitle>
              <div className="flex items-center gap-4">
                {uiState.isLoading && (
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <Loader2 className="w-4 h-4 animate-spin" />
                    جاري التحديث...
                  </div>
                )}
                <Badge variant="outline" className="text-sm">
                  المجموع: {safeOrders.total}
                </Badge>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            {/* Loading State */}
            {uiState.isLoading ? (
              <div className="space-y-4">
                {[...Array(5)].map((_, i) => (
                  <div key={i} className="flex items-center space-x-4 space-x-reverse">
                    <Skeleton className="h-12 w-12 rounded-full" />
                    <div className="space-y-2 flex-1">
                      <Skeleton className="h-4 w-[250px]" />
                      <Skeleton className="h-4 w-[200px]" />
                    </div>
                  </div>
                ))}
              </div>
            ) : safeOrders.data.length === 0 ? (
              /* Empty State */
              <div className="text-center py-16">
                <div className="mx-auto w-24 h-24 bg-muted rounded-full flex items-center justify-center mb-6">
                  <Package className="w-12 h-12 text-muted-foreground" />
                </div>
                <h3 className="text-xl font-semibold text-foreground mb-2">
                  {uiState.hasActiveFilters ? 'لا توجد نتائج مطابقة' : 'لا توجد طلبات'}
                </h3>
                <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                  {uiState.hasActiveFilters
                    ? 'لم يتم العثور على طلبات تطابق معايير البحث المحددة. جرب تعديل الفلاتر أو إعادة تعيينها.'
                    : 'لم يتم إنشاء أي طلبات بعد. ستظهر الطلبات هنا عند إنشائها.'
                  }
                </p>
                {uiState.hasActiveFilters && (
                  <Button onClick={resetFilters} variant="outline" className="gap-2">
                    <RefreshCw className="w-4 h-4" />
                    إعادة تعيين الفلاتر
                  </Button>
                )}
              </div>
            ) : (
              /* Data Table with Pagination */
              <DataTablePagination
                columns={columns}
                paginatedData={safeOrders}
                searchPlaceholder="البحث في الطلبات..."
                searchColumn="id"
                isLoading={uiState.isLoading}
                onPageChange={(page) => handlePageChange(page, {
                  search: filterState.search || undefined,
                  status: filterState.status !== 'all' ? filterState.status : undefined,
                  date_from: filterState.dateFrom || undefined,
                  date_to: filterState.dateTo || undefined,
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