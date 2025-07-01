import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import { ArrowLeft, Save, FileText, Globe } from 'lucide-react';
import { PageProps, PartsSuggestion } from '@/types';

interface Props extends PageProps {
  partsSuggestion: PartsSuggestion;
}

export default function Edit({ partsSuggestion }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    name_ar: partsSuggestion.name_ar,
    name_fr: partsSuggestion.name_fr,
    is_active: partsSuggestion.is_active,
  });

  function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
    setData(e.target.id as keyof typeof data, e.target.value);
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    put(route('admin.parts-suggestions.update', partsSuggestion.id));
  }

  return (
    <AppLayout>
      <Head title={`تعديل: ${partsSuggestion.name_ar}`} />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="container mx-auto px-4 py-8">
          {/* Header */}
          <div className="flex items-center justify-center space-x-4 space-x-reverse mb-8">
            <Link href={route('admin.parts-suggestions.index')}>
              <Button variant="outline" size="sm">
                <ArrowLeft className="w-4 h-4 ml-2" />
                العودة
              </Button>
            </Link>
            <div className="flex items-center space-x-4 space-x-reverse">
              <FileText className="w-8 h-8 text-primary" />
              <div>
                <h1 className="text-3xl font-bold">تعديل الاقتراح</h1>
                <p className="text-gray-600">تعديل اسم الجزء: {partsSuggestion.name_ar}</p>
              </div>
            </div>
          </div>

          <div className="max-w-2xl mx-auto">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Globe className="w-5 h-5" />
                معلومات الاقتراح
              </CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="name_ar">الاسم بالعربية *</Label>
                    <Input
                      id="name_ar"
                      value={data.name_ar}
                      onChange={handleChange}
                      placeholder="مثال: الوسط، الحافة، الزاوية"
                      className="mt-1"
                    />
                    {errors.name_ar && (
                      <p className="text-sm text-red-600 mt-1">{errors.name_ar}</p>
                    )}
                  </div>

                  <div>
                    <Label htmlFor="name_fr">الاسم بالفرنسية *</Label>
                    <Input
                      id="name_fr"
                      value={data.name_fr}
                      onChange={handleChange}
                      placeholder="Ex: Centre, Bordure, Coin"
                      className="mt-1"
                    />
                    {errors.name_fr && (
                      <p className="text-sm text-red-600 mt-1">{errors.name_fr}</p>
                    )}
                  </div>
                </div>

                <div className="flex items-center space-x-3 space-x-reverse">
                  
                </div>
                {errors.is_active && (
                  <p className="text-sm text-red-600">{errors.is_active}</p>
                )}

                <div className="flex gap-4 pt-4">
                  <Button type="submit" disabled={processing}>
                    {processing ? (
                      <>
                        <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin ml-2"></div>
                        جاري الحفظ...
                      </>
                    ) : (
                      <>
                        <Save className="w-4 h-4 ml-2" />
                        حفظ التغييرات
                      </>
                    )}
                  </Button>
                  
                  <Link href={route('admin.parts-suggestions.index')}>
                    <Button type="button" variant="outline">
                      إلغاء
                    </Button>
                  </Link>
                </div>
              </form>
            </CardContent>
          </Card>

          {/* Info Card */}
          <Card className="mt-6">
            <CardHeader>
              <CardTitle className="text-lg">معلومات إضافية</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span className="font-medium text-gray-600">تاريخ الإنشاء:</span>
                  <p>{new Date(partsSuggestion.created_at).toLocaleDateString('en-US')}</p>
                </div>
                <div>
                  <span className="font-medium text-gray-600">آخر تحديث:</span>
                  <p>{new Date(partsSuggestion.updated_at).toLocaleDateString('en-US')}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Help Card */}
          <Card className="mt-6">
            <CardHeader>
              <CardTitle className="text-lg">نصائح</CardTitle>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-sm text-gray-600">
                <li>• استخدم أسماء واضحة ومفهومة للأجزاء</li>
                <li>• تأكد من صحة الترجمة بين العربية والفرنسية</li>
                <li>• الاقتراحات النشطة فقط ستظهر للمصممين</li>
                <li>• إلغاء التفعيل لن يحذف الاقتراح، بل يخفيه فقط</li>
              </ul>
            </CardContent>
          </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
} 