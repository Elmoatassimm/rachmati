import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/input-error';
import { Trash2 } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Category } from '@/types';

type CategoryForm = {
  name_ar: string;
  name_fr: string;
  description?: string;
  _method: string;
};

interface Props {
  category: Category;
}

export default function Edit({ category }: Props) {
  const {
    data,
    setData,
    put,
    processing,
    errors,
    clearErrors,
    recentlySuccessful
      } = useForm<CategoryForm>({
      name_ar: category.name_ar || '',
      name_fr: category.name_fr || '',
      description: category.description || '',
      _method: 'PUT',
    });

  // Clear errors when user starts typing
  const handleFieldChange = (field: keyof CategoryForm, value: string) => {
    setData(field, value);
    if (errors[field]) {
      clearErrors(field);
    }
  };

  const handleSubmit: FormEventHandler = (e) => {
    e.preventDefault();

    put(route('admin.categories.update', category.id), {
      onSuccess: () => {
        console.log('Category updated successfully');
      },
      onError: (errors) => {
        // Focus on first error field
        const errorFields = ['name_ar', 'name_fr', 'description'];
        for (const field of errorFields) {
          if (errors[field]) {
            document.getElementById(field)?.focus();
            break;
          }
        }
      },
    });
  };

  return (
    <AppLayout 
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'التصنيفات', href: '/admin/categories' },
        { title: `تعديل: ${category.name}`, href: `/admin/categories/${category.id}/edit` }
      ]}
    >
      <Head title={`تعديل التصنيف: ${category.name} - Edit Category`} />
      
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-3xl font-bold text-foreground">
              تعديل التصنيف
              <span className="block text-lg font-normal text-muted-foreground mt-1">
                {category.name}
              </span>
            </h1>
          </div>
          <div className="flex space-x-2 space-x-reverse">
            <Link href="/admin/categories" className="text-primary hover:text-primary/90">
              ← العودة للتصنيفات
            </Link>
          </div>
        </div>

        {/* Success Message */}
        {recentlySuccessful && (
          <div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-md p-4">
            <div className="text-sm text-green-600 dark:text-green-400">
              تم تحديث التصنيف بنجاح!
            </div>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Main Category Card */}
          <Card>
            <CardHeader>
              <CardTitle>معلومات التصنيف</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Category Name */}
              <div>
                <Label htmlFor="name_ar">
                  اسم التصنيف *
                </Label>
                <Input
                  id="name_ar"
                  type="text"
                  value={data.name_ar}
                  onChange={(e) => handleFieldChange('name_ar', e.target.value)}
                  placeholder="أدخل اسم التصنيف"
                  required
                  disabled={processing}
                  className={cn(errors.name_ar && "border-destructive")}
                />
                <InputError message={errors.name_ar} />
              </div>

              {/* French Category Name */}
              <div>
                <Label htmlFor="name_fr">
                  اسم التصنيف بالفرنسية *
                </Label>
                <Input
                  id="name_fr"
                  type="text"
                  value={data.name_fr}
                  onChange={(e) => handleFieldChange('name_fr', e.target.value)}
                  placeholder="Entrez le nom de la catégorie en français"
                  required
                  disabled={processing}
                  className={cn(errors.name_fr && "border-destructive")}
                />
                <InputError message={errors.name_fr} />
              </div>

              {/* Category Description */}
              <div>
                <Label htmlFor="description">
                  وصف التصنيف
                </Label>
                <Textarea
                  id="description"
                  value={data.description || ''}
                  onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => handleFieldChange('description', e.target.value)}
                  rows={4}
                  placeholder="وصف تفصيلي للتصنيف (اختياري)"
                  disabled={processing}
                  className={cn(errors.description && "border-destructive")}
                />
                <InputError message={errors.description} />
              </div>
            </CardContent>
          </Card>

          {/* Form Actions */}
          <div className="flex space-x-4 space-x-reverse pt-6">
            <Button
              type="submit"
              disabled={processing}
            >
              {processing ? 'جاري الحفظ...' : 'حفظ التغييرات'}
            </Button>
            <Link href="/admin/categories">
              <Button type="button" variant="outline" disabled={processing}>
                إلغاء
              </Button>
            </Link>
          </div>
        </form>

        {/* Danger Zone */}
        <Card className="border-destructive/20">
          <CardHeader>
            <CardTitle className="text-destructive">منطقة الخطر</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <p className="text-sm text-muted-foreground">
                حذف هذا التصنيف سيؤدي إلى إلغاء ربطه بجميع الرشمات المرتبطة به. هذا الإجراء لا يمكن التراجع عنه.
              </p>
              <Link
                href={route('admin.categories.destroy', category.id)}
                method="delete"
                as="button"
                type="button"
                className="inline-flex items-center gap-2 text-white bg-destructive hover:bg-destructive/90 px-4 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-destructive/20 focus:ring-offset-2 disabled:opacity-50"
              >
                <Trash2 size={16} /> حذف التصنيف
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
} 