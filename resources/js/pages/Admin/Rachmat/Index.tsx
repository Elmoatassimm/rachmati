import React, { useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable, DataTableColumnHeader } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { ModernStatsCard } from '@/components/ui/modern-stats-card';
import ErrorBoundary from '@/components/error-boundary';
import { Rachma, Designer, Category, Paginated, PaginationLink, PageProps } from '@/types';
import {
  Package,
  Eye,
  Edit,
  Download,
  Trash2,
  Filter,
  Search,
  DollarSign,
  Users,
  ShoppingCart,
  MoreHorizontal,
  CheckCircle,
  AlertCircle
} from 'lucide-react';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Stats {
  total_rachmat: number;
  total_designers: number;
  total_orders: number;
  total_revenue: number;
}

interface Props extends PageProps {
  rachmat: Paginated<Rachma>;
  designers: Designer[];
  categories: Category[];
  stats: Stats;
  filters: {
    designer_id?: string;
    category_id?: string;
    date_from?: string;
    date_to?: string;
    min_price?: string;
    max_price?: string;
    search?: string;
  };
}

export default function Index({ rachmat, designers, categories, stats, filters }: Props) {
  const { flash } = usePage<PageProps>().props;
  const [showFilters, setShowFilters] = useState(false);
  
  const { data, setData, get, processing } = useForm({
    designer_id: filters.designer_id || '',
    category_id: filters.category_id || '',
    date_from: filters.date_from || '',
    date_to: filters.date_to || '',
    min_price: filters.min_price || '',
    max_price: filters.max_price || '',
    search: filters.search || '',
  });

  const handleFilter = () => {
    get(route('admin.rachmat.index'), {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const clearFilters = () => {
    setData({
      designer_id: '',
      category_id: '',
      date_from: '',
      date_to: '',
      min_price: '',
      max_price: '',
      search: '',
    });
    router.get(route('admin.rachmat.index'));
  };

  const handleDelete = (rachma: Rachma) => {
    const confirmMessage = `هل أنت متأكد من حذف الرشمة "${rachma.title_ar}"؟\n\nسيتم حذف جميع الملفات والبيانات المرتبطة بها نهائياً.\nهذا الإجراء لا يمكن التراجع عنه.`;
    
    if (confirm(confirmMessage)) {
      router.delete(route('admin.rachmat.destroy', rachma.id), {
        preserveScroll: true,
        onSuccess: () => {
          // Success will be handled by flash message
          console.log('Rachma deleted successfully');
        },
        onError: (errors) => {
          console.error('Delete failed:', errors);
          // Display specific error message
          const errorMessage = errors.message || 'حدث خطأ أثناء حذف الرشمة. تأكد من عدم وجود طلبات مرتبطة بها.';
          alert(errorMessage);
        },
        onFinish: () => {
          // Optional: Add any cleanup logic here
        }
      });
    }
  };

  // Define columns for the rachmat data table
  const columns: ColumnDef<Rachma>[] = [
    {
      accessorKey: "title",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الرشمة" />
      ),
      cell: ({ row }) => {
        const rachma = row.original;
        return (
          <div className="text-right">
            <div className="font-medium text-sm lg:text-base">{rachma.title_ar}</div>
            <div className="text-xs lg:text-sm text-muted-foreground">
              {rachma.categories?.map(cat => cat.name_ar).join(', ') || 'بدون تصنيف'}
            </div>
          </div>
        );
      },
    },
    {
      accessorKey: "designer",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="المصمم" />
      ),
      cell: ({ row }) => {
        const designer = row.original.designer;
        return (
          <div className="text-right">
            <div className="font-medium text-sm">{designer?.store_name}</div>
            <div className="text-xs text-muted-foreground">{designer?.user?.name}</div>
          </div>
        );
      },
    },
    {
      accessorKey: "price",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="السعر" />
      ),
      cell: ({ row }) => (
        <span className="font-semibold text-green-600 text-sm text-right block">
          {row.getValue("price")} دج
        </span>
      ),
    },
    {
      accessorKey: "orders_count",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="المبيعات" />
      ),
      cell: ({ row }) => (
        <span className="font-semibold text-blue-600 text-sm text-right block">
          {row.getValue("orders_count") || 0}
        </span>
      ),
    },
    {
      accessorKey: "created_at",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="تاريخ الرفع" />
      ),
      cell: ({ row }) => {
        const date = new Date(row.getValue("created_at"));
        return (
          <span className="text-sm text-muted-foreground text-right block">
            {date.toLocaleDateString('ar-DZ')}
          </span>
        );
      },
    },
    {
      id: "actions",
      header: "الإجراءات",
      cell: ({ row }) => {
        const rachma = row.original;
        return (
          <div className="flex items-center gap-2 justify-end">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="h-8 w-8 p-0">
                  <MoreHorizontal className="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="text-right">
                <DropdownMenuLabel>الإجراءات</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                  <Link href={route('admin.rachmat.show', rachma.id)}>
                    <Eye className="mr-2 h-4 w-4" />
                    عرض التفاصيل
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                  <Link href={route('admin.rachmat.edit', rachma.id)}>
                    <Edit className="mr-2 h-4 w-4" />
                    تعديل
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                  <a href={route('admin.rachmat.download-file', rachma.id)}>
                    <Download className="mr-2 h-4 w-4" />
                    تحميل الملف
                  </a>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem 
                  onClick={() => handleDelete(rachma)}
                  className="text-red-600"
                >
                  <Trash2 className="mr-2 h-4 w-4" />
                  حذف
                </DropdownMenuItem>

              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        );
      },
    },
  ];

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'إدارة الرشمات', href: '/admin/rachmat' }
      ]}
    >
      <Head title="إدارة الرشمات" />

      <ErrorBoundary>
        <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
          <div className="p-8 space-y-10">
            {/* Modern Header */}
            <AdminPageHeader
              title="إدارة الرشمات"
              subtitle="إدارة جميع الرشمات والملفات في النظام"
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

            {/* Statistics Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
              <ModernStatsCard
                title="إجمالي الرشمات"
                value={stats.total_rachmat}
                subtitle="Total Rachmat"
                icon={Package}
                colorScheme="blue"
              />
              <ModernStatsCard
                title="المصممين النشطين"
                value={stats.total_designers}
                subtitle="Active Designers"
                icon={Users}
                colorScheme="green"
              />
              <ModernStatsCard
                title="إجمالي المبيعات"
                value={stats.total_orders}
                subtitle="Total Orders"
                icon={ShoppingCart}
                colorScheme="purple"
              />
              <ModernStatsCard
                title="إجمالي الإيرادات"
                value={`${stats.total_revenue.toLocaleString()} دج`}
                subtitle="Total Revenue"
                icon={DollarSign}
                colorScheme="yellow"
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
                <div className="flex flex-wrap gap-4 mb-4">
                  <div className="flex-1 min-w-64 relative">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                      placeholder="البحث في الرشمات..."
                      value={data.search}
                      onChange={(e) => setData('search', e.target.value)}
                      className="pl-10 text-base py-3"
                    />
                  </div>
                  <Button onClick={handleFilter} disabled={processing} className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300 text-base px-6 py-6 h-auto font-semibold">
                    تطبيق الفلاتر
                  </Button>
                  <Button onClick={clearFilters} variant="outline" className="border-primary/20 text-primary hover:bg-primary/10 text-base px-6 py-6 h-auto font-semibold">
                    مسح الفلاتر
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => setShowFilters(!showFilters)}
                    className="border-primary/20 text-primary hover:bg-primary/10 text-base px-6 py-6 h-auto font-semibold"
                  >
                    {showFilters ? 'إخفاء الفلاتر المتقدمة' : 'إظهار الفلاتر المتقدمة'}
                  </Button>
                </div>

              {showFilters && (
                <div className="space-y-4 pt-4 border-t">
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                      <Label htmlFor="designer">المصمم</Label>
                      <Select value={data.designer_id} onValueChange={(value) => setData('designer_id', value)}>
                        <SelectTrigger>
                          <SelectValue placeholder="اختر المصمم" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="all">جميع المصممين</SelectItem>
                          {designers.map((designer) => (
                            <SelectItem key={designer.id} value={designer.id.toString()}>
                              {designer.store_name} - {designer.user?.name}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>

                    <div>
                      <Label htmlFor="category">التصنيف</Label>
                      <Select value={data.category_id} onValueChange={(value) => setData('category_id', value)}>
                        <SelectTrigger>
                          <SelectValue placeholder="اختر التصنيف" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="all">جميع التصنيفات</SelectItem>
                          {categories.map((category) => (
                            <SelectItem key={category.id} value={category.id.toString()}>
                              {category.name_ar}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>

                    <div>
                      <Label htmlFor="date_from">من تاريخ</Label>
                      <Input
                        id="date_from"
                        type="date"
                        value={data.date_from}
                        onChange={(e) => setData('date_from', e.target.value)}
                      />
                    </div>

                    <div>
                      <Label htmlFor="date_to">إلى تاريخ</Label>
                      <Input
                        id="date_to"
                        type="date"
                        value={data.date_to}
                        onChange={(e) => setData('date_to', e.target.value)}
                      />
                    </div>

                    <div>
                      <Label htmlFor="min_price">أقل سعر</Label>
                      <Input
                        id="min_price"
                        type="number"
                        placeholder="0"
                        value={data.min_price}
                        onChange={(e) => setData('min_price', e.target.value)}
                      />
                    </div>

                    <div>
                      <Label htmlFor="max_price">أعلى سعر</Label>
                      <Input
                        id="max_price"
                        type="number"
                        placeholder="10000"
                        value={data.max_price}
                        onChange={(e) => setData('max_price', e.target.value)}
                      />
                    </div>
                  </div>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Rachmat Data Table */}
            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl">
              <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
              <CardHeader className="relative pb-6">
                <CardTitle className="text-2xl font-bold text-foreground flex items-center gap-4">
                  <div className="w-12 h-12 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center shadow-lg">
                    <Package className="w-6 h-6 text-primary-foreground" />
                  </div>
                  الرشمات ({rachmat.meta?.total || 0})
                </CardTitle>
              </CardHeader>
              <CardContent className="relative">
                {rachmat.data && rachmat.data.length > 0 ? (
                  <div className="rounded-md border">
                    <DataTable
                      columns={columns}
                      data={rachmat.data}
                    />
                  </div>
                ) : (
                  <div className="text-center py-12">
                    <div className="w-20 h-20 bg-gradient-to-br from-muted to-muted/70 rounded-full flex items-center justify-center mx-auto mb-6">
                      <Package className="w-10 h-10 text-muted-foreground" />
                    </div>
                    <h3 className="text-2xl font-bold text-foreground mb-4">لا توجد رشمات</h3>
                    <p className="text-muted-foreground max-w-md mx-auto">لم يتم العثور على أي رشمات تطابق معايير البحث</p>
                  </div>
                )}
              </CardContent>
            </Card>

          {/* Pagination */}
          {rachmat.meta && rachmat.meta.last_page > 1 && (
            <div className="flex justify-center">
              <div className="flex items-center space-x-2">
                {rachmat.links?.map((link: PaginationLink, index: number) => (
                  <Button
                    key={index}
                    variant={link.active ? 'default' : 'outline'}
                    size="sm"
                    disabled={!link.url}
                    asChild={!!link.url}
                  >
                    {link.url ? (
                      <Link href={link.url} dangerouslySetInnerHTML={{ __html: link.label }} />
                    ) : (
                      <span dangerouslySetInnerHTML={{ __html: link.label }} />
                    )}
                  </Button>
                ))}
              </div>
            </div>
          )}
          </div>
        </div>
      </ErrorBoundary>
    </AppLayout>
  );
}
