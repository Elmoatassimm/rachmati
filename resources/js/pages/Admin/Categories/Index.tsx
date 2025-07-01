import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

import { Label } from '@/components/ui/label';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { ModernStatsCard } from '@/components/ui/modern-stats-card';
import InputError from '@/components/input-error';
import { Category } from '@/types';
import {
  Edit,
  Plus,
  Trash2,
  Save,
  X,
  FolderOpen,
  Package,
  Search,
  Grid3X3,
  Sparkles,
  MoreVertical
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
  total: number;
  active: number;
}

interface Props {
  categories?: Category[];
  stats?: Stats;
}

type EditingState = {
  type: 'category' | null;
  id: number | 'new' | null;
};

export default function Index({ categories = [] }: Props) {
  // Ensure categories is always an array
  const safeCategories = Array.isArray(categories) ? categories : [];
  const [editing, setEditing] = useState<EditingState>({ type: null, id: null });
  const [searchTerm, setSearchTerm] = useState('');

  // Category form
  const categoryForm = useForm({
    name_ar: '',
    name_fr: '',
  });



  const filteredCategories = safeCategories.filter(category => {
    if (!searchTerm.trim()) return true;
    
    const searchLower = searchTerm.toLowerCase();
    return (
      (category.name_ar && category.name_ar.toLowerCase().includes(searchLower)) ||
      (category.name_fr && category.name_fr.toLowerCase().includes(searchLower))
    );
  });

  const totalRachmat = safeCategories.reduce((sum, cat) => sum + (cat.rachmat_count || 0), 0);



  const startEditCategory = (category?: Category) => {
    if (category) {
      categoryForm.setData({
        name_ar: category.name_ar || '',
        name_fr: category.name_fr || '',
      });
      setEditing({ type: 'category', id: category.id });
    } else {
      categoryForm.setData({ name_ar: '', name_fr: '' });
      setEditing({ type: 'category', id: 'new' });
    }
  };

  const cancelEditing = () => {
    setEditing({ type: null, id: null });
    categoryForm.reset();
  };

  const saveCategory = (categoryId?: number) => {
    if (categoryId) {
      categoryForm.put(`/admin/categories/${categoryId}`, {
        onSuccess: () => {
          cancelEditing();
        },
      });
    } else {
      categoryForm.post('/admin/categories', {
        onSuccess: () => {
          cancelEditing();
        },
      });
    }
  };



  const deleteCategory = (categoryId: number, categoryName: string, force: boolean = false) => {
    let confirmMessage = `هل أنت متأكد من حذف التصنيف "${categoryName}"؟`;

    if (force) {
      confirmMessage = `⚠️ حذف نهائي للتصنيف "${categoryName}" ⚠️`;
      confirmMessage += '\n\nتحذير: سيتم حذف التصنيف نهائياً مع جميع الرشمات المرتبطة به';
      confirmMessage += '\n\n⚠️ هذا الإجراء لا يمكن التراجع عنه! ⚠️';
    } else {
      confirmMessage += '\n\nملاحظة: لن يتم الحذف إذا كانت هناك رشمات مرتبطة.';
    }

    if (confirm(confirmMessage)) {
      const url = force
        ? `/admin/categories/${categoryId}/force`
        : `/admin/categories/${categoryId}`;

      router.delete(url, {
        onError: (errors) => {
          console.error('Delete failed:', errors);
        }
      });
    }
  };

  return (
    <AppLayout 
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'التصنيفات', href: '/admin/categories' }
      ]}
    >
      <Head title="إدارة التصنيفات" />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Modern Header */}
          <AdminPageHeader
            title="إدارة التصنيفات"
            subtitle="إدارة تصنيفات الرشمات"
            icon={Grid3X3}
          >
            <div className="flex flex-col sm:flex-row gap-4">
              <div className="relative">
                <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-muted-foreground" />
                <Input
                  type="text"
                  placeholder="بحث في التصنيفات..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full sm:w-80 h-14 pl-12 pr-6 text-lg bg-gradient-to-r from-background to-muted/20 border-2 border-border/50 focus:border-primary/50 rounded-2xl shadow-lg"
                />
              </div>
              <Button
                onClick={() => startEditCategory()}
                className="h-14 px-8 bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-xl hover:shadow-2xl transition-all duration-300 rounded-2xl"
              >
                <Plus size={20} className="ml-2" />
                <span className="text-lg font-semibold">إضافة تصنيف</span>
              </Button>
            </div>
          </AdminPageHeader>

          {/* Stats Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <ModernStatsCard
              title="إجمالي التصنيفات"
              value={safeCategories.length}
              subtitle="Total Categories"
              icon={FolderOpen}
              colorScheme="blue"
            />
            <ModernStatsCard
              title="إجمالي الرشمات"
              value={totalRachmat}
              subtitle="Total Rachmat"
              icon={Package}
              colorScheme="purple"
            />
          </div>

          {/* Revolutionary New Category Form */}
          {editing.type === 'category' && editing.id === 'new' && (
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-2xl">
              <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
              <div className="absolute -top-20 -right-20 w-40 h-40 bg-gradient-to-br from-primary/20 to-transparent rounded-full"></div>
              <div className="absolute -bottom-20 -left-20 w-40 h-40 bg-gradient-to-br from-primary/20 to-transparent rounded-full"></div>

              <CardHeader className="relative pb-8">
                <div className="flex items-center gap-4">
                  <div className="w-14 h-14 bg-gradient-to-br from-primary to-primary/80 rounded-2xl flex items-center justify-center shadow-xl">
                    <Sparkles className="w-7 h-7 text-primary-foreground" />
                  </div>
                  <CardTitle className="text-3xl font-bold text-foreground">إضافة تصنيف جديد</CardTitle>
                </div>
              </CardHeader>

              <CardContent className="relative space-y-8 p-8">
                <div className="space-y-4">
                  <Label htmlFor="category-name" className="text-lg font-semibold text-foreground">اسم التصنيف بالعربية *</Label>
                  <Input
                    id="category-name"
                    value={categoryForm.data.name_ar}
                    onChange={(e) => categoryForm.setData('name_ar', e.target.value)}
                    placeholder="أدخل اسم التصنيف بالعربية"
                    className="h-14 text-lg bg-gradient-to-r from-background to-muted/20 border-2 border-border/50 focus:border-primary/50 rounded-2xl shadow-lg"
                  />
                  <InputError message={categoryForm.errors.name_ar} />
                </div>

                <div className="space-y-4">
                  <Label htmlFor="category-name-fr" className="text-lg font-semibold text-foreground">اسم التصنيف بالفرنسية *</Label>
                  <Input
                    id="category-name-fr"
                    value={categoryForm.data.name_fr}
                    onChange={(e) => categoryForm.setData('name_fr', e.target.value)}
                    placeholder="Entrez le nom de la catégorie en français"
                    className="h-14 text-lg bg-gradient-to-r from-background to-muted/20 border-2 border-border/50 focus:border-primary/50 rounded-2xl shadow-lg"
                  />
                  <InputError message={categoryForm.errors.name_fr} />
                </div>

                <div className="flex gap-4 pt-4">
                  <Button
                    onClick={() => saveCategory()}
                    disabled={categoryForm.processing}
                    className="h-14 px-8 bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-xl hover:shadow-2xl transition-all duration-300 rounded-2xl"
                  >
                    <Save size={20} className="ml-2" />
                    <span className="text-lg font-semibold">حفظ</span>
                  </Button>
                  <Button
                    variant="outline"
                    onClick={cancelEditing}
                    className="h-14 px-8 bg-gradient-to-r from-background to-muted/20 border-2 border-border/50 hover:border-destructive/50 hover:bg-destructive/5 transition-all duration-300 rounded-2xl"
                  >
                    <X size={20} className="ml-2" />
                    <span className="text-lg font-semibold">إلغاء</span>
                  </Button>
                </div>
              </CardContent>
            </Card>
          )}

        {/* Categories List */}
        <div className="space-y-6">
          {filteredCategories.map(category => (
            <Card key={category.id} className="border-l-4 border-l-blue-500 transition-all duration-200 hover:shadow-md border-0 shadow-sm">
              <div className="p-6">
                {/* Category Row */}
                {editing.type === 'category' && editing.id === category.id ? (
                  // Edit Category Form
                  <div className="space-y-6 bg-gradient-to-r from-blue-50/30 to-indigo-50/30 p-6 rounded-lg">
                    <div className="space-y-2">
                      <Label className="text-sm font-medium text-foreground">اسم التصنيف بالعربية *</Label>
                      <Input
                        value={categoryForm.data.name_ar}
                        onChange={(e) => categoryForm.setData('name_ar', e.target.value)}
                        placeholder="أدخل اسم التصنيف بالعربية"
                        className="h-11"
                      />
                      <InputError message={categoryForm.errors.name_ar} />
                    </div>
                    <div className="space-y-2">
                      <Label className="text-sm font-medium text-foreground">اسم التصنيف بالفرنسية *</Label>
                      <Input
                        value={categoryForm.data.name_fr}
                        onChange={(e) => categoryForm.setData('name_fr', e.target.value)}
                        placeholder="Entrez le nom de la catégorie en français"
                        className="h-11"
                      />
                      <InputError message={categoryForm.errors.name_fr} />
                    </div>
                    <div className="flex gap-3">
                      <Button
                        onClick={() => saveCategory(category.id)}
                        disabled={categoryForm.processing}
                        className="px-6 h-11"
                      >
                        <Save size={16} className="ml-2" /> حفظ
                      </Button>
                      <Button variant="outline" onClick={cancelEditing} className="px-6 h-11">
                        <X size={16} className="ml-2" /> إلغاء
                      </Button>
                    </div>
                  </div>
                ) : (
                  // Display Category
                  <div className="flex items-center justify-between">
                                      <div className="space-y-1">
                    <h3 className="font-bold text-xl text-foreground">{category.name_ar || 'غير محدد'}</h3>
                    {category.name_fr && (
                      <p className="text-sm text-muted-foreground font-medium">{category.name_fr}</p>
                    )}

                  </div>
                    <div className="flex items-center gap-3">
                      <div className="bg-muted/50 text-muted-foreground px-4 py-2 rounded-xl text-sm font-medium">
                        {category.rachmat_count || 0} رشمة
                      </div>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => startEditCategory(category)}
                        className="hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200"
                      >
                        <Edit size={16} />
                      </Button>

                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button
                            variant="ghost"
                            size="sm"
                            className="text-destructive hover:bg-destructive/10 transition-colors duration-200"
                          >
                            <MoreVertical size={16} />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-48">
                          <DropdownMenuLabel>خيارات الحذف</DropdownMenuLabel>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem
                            onClick={() => deleteCategory(category.id, category.name_ar || 'غير محدد', false)}
                            className="text-destructive focus:text-destructive"
                          >
                            <Trash2 size={14} className="mr-2" />
                            حذف التصنيف
                          </DropdownMenuItem>

                        </DropdownMenuContent>
                      </DropdownMenu>
                    </div>
                  </div>
                )}
              </div>
            </Card>
          ))}
        </div>

        {filteredCategories.length === 0 && (
          <Card>
            <CardContent className="text-center py-8">
              <p className="text-muted-foreground">
                {searchTerm ? 'لا توجد نتائج للبحث' : 'لا توجد تصنيفات. ابدأ بإضافة تصنيف جديد.'}
              </p>
            </CardContent>
          </Card>
        )}
        </div>
      </div>
    </AppLayout>
  );
}