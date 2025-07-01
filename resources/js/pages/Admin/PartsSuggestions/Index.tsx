import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
  DropdownMenu, 
  DropdownMenuContent, 
  DropdownMenuItem, 
  DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu';
import { 
  Plus, 
  Search, 
  MoreVertical, 
  Edit, 
  Trash2, 
  Eye, 
  EyeOff,
  FileText,
  Globe 
} from 'lucide-react';
import { PartsSuggestion, PageProps } from '@/types';

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Props extends PageProps {
  partsSuggestions?: {
    data: PartsSuggestion[];
    links: PaginationLink[];
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
}

export default function Index({ partsSuggestions, filters = {} }: Props) {
  const [search, setSearch] = useState(filters.search || '');
  
  const { delete: destroy } = useForm();

  // Safe access to partsSuggestions properties with defaults
  const suggestions = partsSuggestions?.data || [];
  const meta = partsSuggestions?.meta || { total: 0, last_page: 1, current_page: 1, per_page: 15 };
  const links = partsSuggestions?.links || [];

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(route('admin.parts-suggestions.index'), { 
      search: search || undefined 
    }, { preserveState: true });
  };

  const handleDelete = (id: number) => {
    if (confirm('هل أنت متأكد من حذف هذا الاقتراح؟')) {
      destroy(route('admin.parts-suggestions.destroy', id));
    }
  };

  const toggleStatus = (id: number) => {
    router.post(route('admin.parts-suggestions.toggle-status', id), {}, {
      preserveState: true
    });
  };

  return (
    <AppLayout>
      <Head title="إدارة اقتراحات أجزاء الرشمات" />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Header */}
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4 space-x-reverse">
              <FileText className="w-8 h-8 text-primary" />
              <div>
                <h1 className="text-3xl font-bold">اقتراحات أجزاء الرشمات</h1>
                <p className="text-gray-600">إدارة أسماء أجزاء الرشمات المقترحة للمصممين</p>
              </div>
            </div>
            <Link href={route('admin.parts-suggestions.create')}>
              <Button>
                <Plus className="w-4 h-4 ml-2" />
                إضافة اقتراح جديد
              </Button>
            </Link>
          </div>

          {/* Search */}
          <Card>
            <CardContent className="p-4">
              <form onSubmit={handleSearch} className="flex gap-4">
                <div className="flex-1">
                  <div className="relative">
                    <Search className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                    <Input
                      type="text"
                      placeholder="البحث في الاقتراحات..."
                      value={search}
                      onChange={(e) => setSearch(e.target.value)}
                      className="pr-10"
                    />
                  </div>
                </div>
                <Button type="submit">بحث</Button>
                {(filters?.search) && (
                  <Button 
                    type="button" 
                    variant="outline"
                    onClick={() => {
                      setSearch('');
                      router.get(route('admin.parts-suggestions.index'));
                    }}
                  >
                    إزالة الفلترة
                  </Button>
                )}
              </form>
            </CardContent>
          </Card>

          {/* Revolutionary Stats Grid */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {/* Total Suggestions */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-blue-500/5"></div>
              <CardHeader className="relative pb-3">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-base font-bold text-muted-foreground uppercase tracking-wider">إجمالي الاقتراحات</CardTitle>
                  <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <FileText className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0">
                <div className="text-4xl font-black bg-gradient-to-r from-blue-600 to-blue-500 bg-clip-text text-transparent">
                  {meta.total.toLocaleString()}
                </div>
                <p className="text-sm text-muted-foreground mt-2 font-medium">Total Suggestions</p>
                <div className="mt-3 h-1 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"></div>
              </CardContent>
            </Card>
            
            {/* Active Suggestions */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
              <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-emerald-500/5"></div>
              <CardHeader className="relative pb-3">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-base font-bold text-muted-foreground uppercase tracking-wider">الاقتراحات النشطة</CardTitle>
                  <div className="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Eye className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0">
                <div className="text-4xl font-black bg-gradient-to-r from-emerald-600 to-emerald-500 bg-clip-text text-transparent">
                  {suggestions.filter(s => s.is_active).length.toLocaleString()}
                </div>
                <p className="text-sm text-muted-foreground mt-2 font-medium">Active Suggestions</p>
                <div className="mt-3 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full"></div>
              </CardContent>
            </Card>
            
            {/* Inactive Suggestions */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
              <div className="absolute inset-0 bg-gradient-to-br from-red-500/10 via-transparent to-red-500/5"></div>
              <CardHeader className="relative pb-3">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-base font-bold text-muted-foreground uppercase tracking-wider">الاقتراحات غير النشطة</CardTitle>
                  <div className="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <EyeOff className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0">
                <div className="text-4xl font-black bg-gradient-to-r from-red-600 to-red-500 bg-clip-text text-transparent">
                  {suggestions.filter(s => !s.is_active).length.toLocaleString()}
                </div>
                <p className="text-sm text-muted-foreground mt-2 font-medium">Inactive Suggestions</p>
                <div className="mt-3 h-1 bg-gradient-to-r from-red-500 to-red-600 rounded-full"></div>
              </CardContent>
            </Card>
          </div>

          {/* Parts Suggestions Table */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Globe className="w-5 h-5" />
                قائمة الاقتراحات
              </CardTitle>
            </CardHeader>
            <CardContent>
              {suggestions.length > 0 ? (
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead>
                      <tr className="border-b">
                        <th className="text-right p-3 font-semibold">الاسم بالعربية</th>
                        <th className="text-right p-3 font-semibold">الاسم بالفرنسية</th>
                        <th className="text-center p-3 font-semibold">الحالة</th>
                        <th className="text-center p-3 font-semibold">تاريخ الإنشاء</th>
                        <th className="text-center p-3 font-semibold">الإجراءات</th>
                      </tr>
                    </thead>
                    <tbody>
                      {suggestions.map((suggestion) => (
                        <tr key={suggestion.id} className="border-b">
                          <td className="p-3">
                            <div className="font-medium">{suggestion.name_ar}</div>
                          </td>
                          <td className="p-3">
                            <div className="text-gray-600">{suggestion.name_fr}</div>
                          </td>
                          <td className="p-3 text-center">
                            <Badge 
                              variant={suggestion.is_active ? "default" : "secondary"}
                              className={suggestion.is_active ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800"}
                            >
                              {suggestion.is_active ? 'نشط' : 'غير نشط'}
                            </Badge>
                          </td>
                          <td className="p-3 text-center text-gray-600">
                            {new Date(suggestion.created_at).toLocaleDateString('ar-SA')}
                          </td>
                          <td className="p-3 text-center">
                            <DropdownMenu>
                              <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="sm">
                                  <MoreVertical className="w-4 h-4" />
                                </Button>
                              </DropdownMenuTrigger>
                              <DropdownMenuContent align="end">
                                <DropdownMenuItem asChild>
                                  <Link href={route('admin.parts-suggestions.edit', suggestion.id)}>
                                    <Edit className="w-4 h-4 ml-2" />
                                    تعديل
                                  </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem 
                                  onClick={() => toggleStatus(suggestion.id)}
                                >
                                  {suggestion.is_active ? (
                                    <>
                                      <EyeOff className="w-4 h-4 ml-2" />
                                      إلغاء التفعيل
                                    </>
                                  ) : (
                                    <>
                                      <Eye className="w-4 h-4 ml-2" />
                                      تفعيل
                                    </>
                                  )}
                                </DropdownMenuItem>
                                <DropdownMenuItem 
                                  onClick={() => handleDelete(suggestion.id)}
                                  className="text-red-600"
                                >
                                  <Trash2 className="w-4 h-4 ml-2" />
                                  حذف
                                </DropdownMenuItem>
                              </DropdownMenuContent>
                            </DropdownMenu>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="text-center py-8">
                  <FileText className="w-16 h-16 text-gray-300 mx-auto mb-4" />
                  <h3 className="text-lg font-medium text-gray-900 mb-2">لا توجد اقتراحات</h3>
                  <p className="text-gray-500 mb-4">
                    {filters?.search ? 'لم يتم العثور على نتائج للبحث المحدد' : 'لم يتم إضافة أي اقتراحات بعد'}
                  </p>
                  {!filters?.search && (
                    <Link href={route('admin.parts-suggestions.create')}>
                      <Button>
                        <Plus className="w-4 h-4 ml-2" />
                        إضافة اقتراح جديد
                      </Button>
                    </Link>
                  )}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Pagination */}
          {meta.last_page > 1 && (
            <div className="flex justify-center">
              <div className="flex space-x-2 space-x-reverse">
                {links.map((link, index) => (
                  <Button
                    key={index}
                    variant={link.active ? "default" : "outline"}
                    size="sm"
                    disabled={!link.url}
                    onClick={() => link.url && router.get(link.url)}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                  />
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </AppLayout>
  );
} 