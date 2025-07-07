import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTableColumnHeader } from '@/components/ui/data-table';
import { DataTablePagination } from '@/components/ui/data-table-pagination';
import { usePagination } from '@/hooks/use-pagination';
import { DesignerPageHeader } from '@/components/designer/DesignerPageHeader';
import { DesignerStatsCards } from '@/components/designer/DesignerStatsCards';
import { Package, Search, Loader2 } from 'lucide-react';

interface OrderItem {
  id: number;
  rachma_id: number;
  price: number;
  rachma: {
    title: string;
    title_ar: string;
    title_fr: string;
  };
}

interface Order {
  id: number;
  client: {
    name: string;
    email: string;
  };
  rachma?: {
    title: string;
    title_ar: string;
    title_fr: string;
  };
  order_items?: OrderItem[];
  amount: number;
  status: string;
  created_at: string;
}

interface Props {
  orders: {
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
  };
  stats?: {
    total: number;
    completed: number;
    pending: number;
    revenue: number;
  };
}

export default function Index({ orders, filters = {}, stats }: Props) {
  const [isLoading, setIsLoading] = useState(false);
  const [searchQuery, setSearchQuery] = useState(filters.search || '');
  const [searchDebounceTimer, setSearchDebounceTimer] = useState<NodeJS.Timeout | null>(null);

  const { isLoading: isPaginationLoading, handlePageChange } = usePagination('/designer/orders', {
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
      handlePageChange(1, { search: value });
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

  const columns: ColumnDef<Order>[] = [
    {
      accessorKey: "id",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="رقم الطلب" />
      ),
      cell: ({ row }) => (
        <span className="font-medium">#{row.getValue("id")}</span>
      ),
    },
    {
      accessorKey: "client",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="العميل" />
      ),
      cell: ({ row }) => {
        const client = row.original.client;
        return (
          <div>
            <div className="font-medium">{client.name}</div>
            <div className="text-sm text-muted-foreground">{client.email}</div>
          </div>
        );
      },
    },
    {
      accessorKey: "rachma",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الرشمات" />
      ),
      cell: ({ row }) => {
        const order = row.original;

        // Handle multi-item orders
        if (order.order_items && order.order_items.length > 0) {
          // Filter items that belong to this designer (in case of mixed orders)
          const designerItems = order.order_items.filter((item: OrderItem) =>
            // This would need designer filtering logic - for now show all items
            true
          );

          if (designerItems.length === 1) {
            const item = designerItems[0];
            return (
              <div>
                <div className="font-medium">{item.rachma.title_ar || item.rachma.title}</div>
                <div className="text-sm text-muted-foreground">رشمة واحدة</div>
              </div>
            );
          } else {
            return (
              <div>
                <div className="font-medium">{designerItems.length} رشمات</div>
                <div className="text-sm text-muted-foreground">طلب متعدد الرشمات</div>
              </div>
            );
          }
        }

        // Handle single-item orders (backward compatibility)
        if (order.rachma) {
          return (
            <div>
              <div className="font-medium">{order.rachma.title_ar || order.rachma.title}</div>
              <div className="text-sm text-muted-foreground">رشمة واحدة</div>
            </div>
          );
        }

        return <span className="text-muted-foreground">غير محدد</span>;
      },
    },
    {
      accessorKey: "amount",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="المبلغ" />
      ),
      cell: ({ row }) => (
        <span className="font-semibold text-green-600">
          {row.getValue("amount")} دج
        </span>
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
          <Badge
            variant={
              status === 'completed' ? 'default' :
              status === 'pending' ? 'secondary' :
              'outline'
            }
          >
            {status === 'completed' ? 'مكتمل' :
             status === 'pending' ? 'قيد الانتظار' :
             status}
          </Badge>
        );
      },
    },
    {
      accessorKey: "created_at",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="تاريخ الطلب" />
      ),
      cell: ({ row }) => {
        const date = new Date(row.getValue("created_at"));
        return (
          <span className="text-muted-foreground">
            {date.toLocaleDateString('ar-DZ')}
          </span>
        );
      },
    },
    {
      id: "actions",
      cell: ({ row }) => {
        const order = row.original;
        return (
          <Button
            variant="ghost"
            size="icon"
            asChild
          >
            <Link href={`/designer/orders/${order.id}`}>
              <Package className="w-4 h-4 text-muted-foreground hover:text-foreground transition-colors" />
            </Link>
          </Button>
        );
      },
    },
  ];

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة المصمم', href: '/designer/dashboard' },
        { title: 'الطلبات', href: '/designer/orders' }
      ]}
    >
      <Head title="إدارة الطلبات" />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-8">
          <DesignerPageHeader
            title="إدارة الطلبات"
            subtitle="إدارة طلبات الرشمات الخاصة بك"
          />

          {stats && <DesignerStatsCards stats={stats} />}

          <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm">
            <CardHeader className="pb-6">
              <div className="flex items-center justify-between">
                <CardTitle className="text-2xl font-semibold text-foreground flex items-center gap-3">
                  <Package className="w-6 h-6" />
                  قائمة الطلبات
                </CardTitle>
                <div className="flex items-center gap-4">
                  {isLoading && (
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                      <Loader2 className="w-4 h-4 animate-spin" />
                      جاري التحديث...
                    </div>
                  )}
                  <Badge variant="outline" className="text-sm">
                    المجموع: {orders.total}
                  </Badge>
                </div>
              </div>
            </CardHeader>
            <CardContent>
              {/* Search Bar */}
              <div className="mb-6">
                <div className="relative">
                  <Search className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => handleSearch(e.target.value)}
                    placeholder="البحث في الطلبات..."
                    className="w-full h-10 pl-4 pr-10 text-sm border border-input bg-background rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors"
                  />
                </div>
              </div>

              {/* Data Table with Pagination */}
              <DataTablePagination
                columns={columns}
                paginatedData={orders}
                searchPlaceholder="البحث في الطلبات..."
                searchColumn="id"
                isLoading={isLoading}
                onPageChange={(page) => handlePageChange(page, { search: searchQuery || undefined })}

              />
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
} 