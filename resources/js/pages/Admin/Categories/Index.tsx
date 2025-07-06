import React, { useState, useCallback } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable, DataTableColumnHeader } from '@/components/ui/data-table';
import CustomPagination from '@/components/ui/custom-pagination';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { Plus, Package, Search, Pencil, Eye } from 'lucide-react';
import { PageProps } from '@/types';

interface Category {
  id: number;
  name: string;
  name_ar: string;
  name_fr: string;
  description: string;
  description_ar: string;
  description_fr: string;
  is_active: boolean;
  created_at: string;
}

interface CustomPageProps extends PageProps {
  categories: Category[];
  stats: {
    total: number;
    active: number;
  };
}

export default function Index() {
  const { categories, stats } = usePage<CustomPageProps>().props;
  const [searchQuery, setSearchQuery] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 10;

  // Filter categories based on search
  const filteredCategories = categories.filter(category => {
    const searchLower = searchQuery.toLowerCase();
    return (
      (category.name?.toLowerCase() || '').includes(searchLower) ||
      (category.name_ar || '').includes(searchQuery) ||
      (category.description?.toLowerCase() || '').includes(searchLower) ||
      (category.description_ar || '').includes(searchQuery)
    );
  });

  // Calculate pagination
  const totalPages = Math.ceil(filteredCategories.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentCategories = filteredCategories.slice(startIndex, endIndex);

  // Handle search with debounce
  const handleSearch = useCallback((value: string) => {
    setSearchQuery(value);
    setCurrentPage(1); // Reset to first page when searching
  }, []);

  const columns: ColumnDef<Category>[] = [
    {
      accessorKey: "name",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الاسم" />
      ),
      cell: ({ row }) => {
        const category = row.original;
        return (
          <div>
            <div className="font-medium">{category.name_ar}</div>
            <div className="text-sm text-muted-foreground">{category.name}</div>
          </div>
        );
      },
    },
    {
      accessorKey: "description",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الوصف" />
      ),
      cell: ({ row }) => {
        const category = row.original;
        return (
          <div>
            <div className="font-medium">{category.description_ar}</div>
            <div className="text-sm text-muted-foreground">{category.description}</div>
          </div>
        );
      },
    },
    {
      accessorKey: "is_active",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="الحالة" />
      ),
      cell: ({ row }) => {
        const category = row.original;
        return (
          <Badge
            variant={category.is_active ? "default" : "secondary"}
            className="font-medium"
          >
            {category.is_active ? 'نشط' : 'غير نشط'}
          </Badge>
        );
      },
    },
    {
      accessorKey: "created_at",
      header: ({ column }) => (
        <DataTableColumnHeader column={column} title="تاريخ الإنشاء" />
      ),
      cell: ({ row }) => {
        const category = row.original;
        return (
          <span className="text-sm text-muted-foreground">
            {new Date(category.created_at).toLocaleDateString('ar-DZ')}
          </span>
        );
      },
    },
    {
      id: "actions",
      cell: ({ row }) => {
        const category = row.original;
        return (
          <div className="flex items-center gap-2">
            <Button
              variant="ghost"
              size="icon"
              asChild
            >
              <Link href={`/admin/categories/${category.id}/edit`}>
                <Pencil className="w-4 h-4 text-muted-foreground hover:text-foreground transition-colors" />
              </Link>
            </Button>
            <Button
              variant="ghost"
              size="icon"
              asChild
            >
              <Link href={`/admin/categories/${category.id}`}>
                <Eye className="w-4 h-4 text-muted-foreground hover:text-foreground transition-colors" />
              </Link>
            </Button>
          </div>
        );
      },
    },
  ];

  return (
    <AppLayout 
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'التصنيفات', href: '/admin/categories' }
      ]}
    >
      <Head title="إدارة التصنيفات - Categories Management" />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-8">
          <AdminPageHeader
            title="إدارة التصنيفات"
            subtitle="إدارة تصنيفات الرشمات"
          >
            <Button asChild>
              <Link href="/admin/categories/create">
                <Plus className="w-4 h-4 mr-2" />
                إضافة تصنيف
              </Link>
            </Button>
          </AdminPageHeader>

          <Card className="transition-all duration-200 hover:shadow-md border-0 shadow-sm">
            <CardHeader className="pb-6">
              <div className="flex items-center justify-between">
                <CardTitle className="text-2xl font-semibold text-foreground flex items-center gap-3">
                  <Package className="w-6 h-6" />
                  قائمة التصنيفات
                </CardTitle>
                <div className="flex items-center gap-4">
                  <Badge variant="outline" className="text-sm">
                    المجموع: {stats.total}
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
                    placeholder="البحث في التصنيفات..."
                    className="w-full h-10 pl-4 pr-10 text-sm border border-input bg-background rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors"
                  />
                </div>
              </div>

              {/* Data Table */}
              {currentCategories.length === 0 ? (
                <div className="text-center py-12">
                  <Package className="w-16 h-16 text-muted-foreground mx-auto mb-4" />
                  <h3 className="text-lg font-medium text-foreground mb-2">لا توجد تصنيفات</h3>
                  <p className="text-muted-foreground mb-4">
                    {searchQuery ? 'لم يتم العثور على نتائج للبحث المحدد' : 'لم يتم إضافة أي تصنيفات بعد'}
                  </p>
                  {!searchQuery && (
                    <Button asChild>
                      <Link href="/admin/categories/create">
                        <Plus className="w-4 h-4 mr-2" />
                        إضافة تصنيف جديد
                      </Link>
                    </Button>
                  )}
                </div>
              ) : (
                <div className="space-y-6">
                  <DataTable columns={columns} data={currentCategories} />

                  {totalPages > 1 && (
                    <CustomPagination
                      currentPage={currentPage}
                      totalPages={totalPages}
                      totalItems={filteredCategories.length}
                      itemsPerPage={itemsPerPage}
                      onPageChange={setCurrentPage}

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