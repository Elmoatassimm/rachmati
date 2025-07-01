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
import { cn } from '@/lib/utils';

type CategoryForm = {
  name_ar: string;
  name_fr: string;
  description?: string;
};

export default function Create() {
  const {
    data,
    setData,
    post,
    processing,
    errors,
    clearErrors,
    recentlySuccessful
  } = useForm<CategoryForm>({
    name_ar: '',
    name_fr: '',
    description: '',
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
    post(route('admin.categories.store'), {
      onSuccess: () => {
        console.log('Category created successfully');
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
        { title: 'إضافة تصنيف جديد', href: '/admin/categories/create' }
      ]}
    >
      <Head title="إضافة تصنيف جديد - Create Category" />
      
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-3xl font-bold text-foreground">
              إضافة تصنيف جديد
              <span className="block text-lg font-normal text-muted-foreground mt-1">
                إنشاء تصنيف جديد للرشمات
              </span>
            </h1>
          </div>
          <div>
            <Link href="/admin/categories" className="text-primary hover:text-primary/90">
              ← العودة للتصنيفات
            </Link>
          </div>
        </div>

        {/* Success Message */}
        {recentlySuccessful && (
          <div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-md p-4">
            <div className="text-sm text-green-600 dark:text-green-400">
              تم إنشاء التصنيف بنجاح!
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
              {processing ? 'جاري الحفظ...' : 'حفظ التصنيف'}
            </Button>
            <Link href="/admin/categories">
              <Button type="button" variant="outline" disabled={processing}>
                إلغاء
              </Button>
            </Link>
          </div>
        </form>

        {/* Help Section */}
        <Card>
          <CardHeader>
            <CardTitle>نصائح لإضافة التصنيفات</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3 text-sm text-muted-foreground">
              <div className="flex items-start space-x-2 space-x-reverse">
                <div className="w-2 h-2 bg-primary rounded-full mt-2 flex-shrink-0"></div>
                <p>استخدم أسماء واضحة ومفهومة للتصنيفات</p>
              </div>
              <div className="flex items-start space-x-2 space-x-reverse">
                <div className="w-2 h-2 bg-primary rounded-full mt-2 flex-shrink-0"></div>
                <p>يمكن ربط الرشمات بعدة تصنيفات في نفس الوقت</p>
              </div>
              <div className="flex items-start space-x-2 space-x-reverse">
                <div className="w-2 h-2 bg-primary rounded-full mt-2 flex-shrink-0"></div>
                <p>يمكنك تعديل التصنيف أو حذفه لاحقًا من صفحة إدارة التصنيفات</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
} 