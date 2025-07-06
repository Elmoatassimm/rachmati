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
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { Package, Search, Loader2, Plus } from 'lucide-react';

interface PaymentInfo {
  id: number;
  ccp_number: string;
  ccp_key: string;
  nom: string;
  adress: string;
  baridimob: string;
  created_at: string;
  updated_at: string;
  formatted_ccp_number?: string;
  masked_ccp_key?: string;
}

interface Props {
  paymentInfos: {
    data: PaymentInfo[];
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
    search?: string;
    is_active?: boolean;
  };
}

export default function Index({ paymentInfos, filters = {} }: Props) {
  const [isLoading, setIsLoading] = useState(false);
  const [searchQuery, setSearchQuery] = useState(filters.search || '');
  const [searchDebounceTimer, setSearchDebounceTimer] = useState<NodeJS.Timeout | null>(null);

  const { isLoading: isPaginationLoading, handlePageChange } = usePagination('/admin/payment-info', {
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

  const columns: ColumnDef<PaymentInfo>[] = [
    {
      accessorKey: "id",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الرقم" />
      ),
      cell: ({ row }) => (
        <span className="font-medium">#{row.getValue("id")}</span>
      ),
    },
    {
      accessorKey: "ccp_number",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="رقم CCP" />
      ),
      cell: ({ row }) => (
        <span className="font-medium">{row.getValue("formatted_ccp_number") || row.getValue("ccp_number")}</span>
      ),
    },
    {
      accessorKey: "nom",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="صاحب الحساب" />
      ),
      cell: ({ row }) => (
        <span className="font-medium">{row.getValue("nom")}</span>
      ),
    },
    {
      accessorKey: "baridimob",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="رقم BaridiMob" />
      ),
      cell: ({ row }) => (
        <span className="font-medium">{row.getValue("baridimob")}</span>
      ),
    },
    {
      accessorKey: "created_at",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="تاريخ الإنشاء" />
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
        const paymentInfo = row.original;
        return (
          <Button
            variant="ghost"
            size="icon"
            asChild
          >
            <Link href={`/admin/payment-info/${paymentInfo.id}/edit`}>
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
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'معلومات الدفع', href: '/admin/payment-info' }
      ]}
    >
      <Head title="إدارة معلومات الدفع" />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-8">
          <AdminPageHeader
            title="إدارة معلومات الدفع"
            subtitle="إدارة معلومات الحسابات البنكية للدفع"
            action={
              <Button asChild>
                <Link href="/admin/payment-info/create">
                  <Plus className="w-4 h-4 ml-2" />
                  إضافة حساب جديد
                </Link>
              </Button>
            }
          />

          <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm">
            <CardHeader className="pb-6">
              <div className="flex items-center justify-between">
                <CardTitle className="text-2xl font-semibold text-foreground flex items-center gap-3">
                  <Package className="w-6 h-6" />
                  قائمة الحسابات البنكية
                </CardTitle>
                <div className="flex items-center gap-4">
                  {isLoading && (
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                      <Loader2 className="w-4 h-4 animate-spin" />
                      جاري التحديث...
                    </div>
                  )}
                  <Badge variant="outline" className="text-sm">
                    المجموع: {paymentInfos.total}
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
                    placeholder="البحث في الحسابات..."
                    className="w-full h-10 pl-4 pr-10 text-sm border border-input bg-background rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors"
                  />
                </div>
              </div>

              {/* Data Table with Pagination */}
              <DataTablePagination
                columns={columns}
                paginatedData={paymentInfos}
                searchPlaceholder="البحث في الحسابات..."
                searchColumn="ccp_number"
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
