import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, Plus, FileText, Globe } from 'lucide-react';

export default function Create() {
  const { data, setData, post, processing, errors } = useForm({
    name_ar: '',
    name_fr: '',
  });

  function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
    setData(e.target.id as keyof typeof data, e.target.value);
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    post(route('admin.parts-suggestions.store'));
  }

  return (
    <AppLayout>
      <Head title="إضافة اقتراح جديد" />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-8">
          {/* Modern Header */}
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4 space-x-reverse">
              <div className="w-12 h-12 bg-gradient-to-br from-primary to-primary/80 rounded-xl flex items-center justify-center shadow-lg hover:shadow-xl transition-shadow duration-300">
                <Plus className="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 className="text-3xl font-bold bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text text-transparent">
                  إضافة اقتراح جديد
                </h1>
                <p className="text-muted-foreground">إضافة اسم جزء جديد لقائمة الاقتراحات</p>
              </div>
            </div>
            <Link href={route('admin.parts-suggestions.index')}>
              <Button variant="outline" size="sm" className="gap-2 hover:bg-muted/50 transition-colors">
                <ArrowLeft className="w-4 h-4" />
                العودة
              </Button>
            </Link>
          </div>

          <div className="max-w-2xl mx-auto">
            {/* Main Form Card */}
            <Card className="border-0 shadow-xl bg-gradient-to-br from-card via-card to-muted/30 hover:shadow-2xl transition-shadow duration-300">
              <CardHeader className="pb-6">
                <CardTitle className="text-xl font-semibold flex items-center gap-3">
                  <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
                    <Globe className="w-4 h-4 text-white" />
                  </div>
                  معلومات الاقتراح
                </CardTitle>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-2">
                      <Label htmlFor="name_ar" className="text-sm font-semibold text-foreground">الاسم بالعربية *</Label>
                      <Input
                        id="name_ar"
                        value={data.name_ar}
                        onChange={handleChange}
                        placeholder="مثال: الوسط، الحافة، الزاوية"
                        className="h-12 border-border/50 focus:border-primary transition-colors"
                      />
                      {errors.name_ar && (
                        <p className="text-sm text-destructive mt-1">{errors.name_ar}</p>
                      )}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="name_fr" className="text-sm font-semibold text-foreground">الاسم بالفرنسية *</Label>
                      <Input
                        id="name_fr"
                        value={data.name_fr}
                        onChange={handleChange}
                        placeholder="Ex: Centre, Bordure, Coin"
                        className="h-12 border-border/50 focus:border-primary transition-colors"
                      />
                      {errors.name_fr && (
                        <p className="text-sm text-destructive mt-1">{errors.name_fr}</p>
                      )}
                    </div>
                  </div>

                  <div className="flex gap-4 pt-6 border-t border-border/50">
                    <Button
                      type="submit"
                      disabled={processing}
                      className="h-12 px-8 text-base font-semibold shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-r from-primary to-primary/90"
                    >
                      {processing ? (
                        <>
                          <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin ml-2"></div>
                          جاري الحفظ...
                        </>
                      ) : (
                        <>
                          <Plus className="w-5 h-5 ml-2" />
                          إضافة الاقتراح
                        </>
                      )}
                    </Button>

                    <Link href={route('admin.parts-suggestions.index')}>
                      <Button
                        type="button"
                        variant="outline"
                        className="h-12 px-8 text-base hover:bg-muted/50 transition-colors"
                      >
                        إلغاء
                      </Button>
                    </Link>
                  </div>
                </form>
              </CardContent>
            </Card>

            {/* Help Card */}
            <Card className="mt-8 border-0 shadow-xl bg-gradient-to-br from-card via-card to-muted/30 hover:shadow-2xl transition-shadow duration-300">
              <CardHeader className="pb-6">
                <CardTitle className="text-xl font-semibold flex items-center gap-3">
                  <div className="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-md">
                    <FileText className="w-4 h-4 text-white" />
                  </div>
                  نصائح وإرشادات
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="bg-gradient-to-br from-muted/20 to-muted/40 rounded-lg p-6 border border-border/50">
                  <ul className="space-y-4 text-sm">
                    <li className="flex items-start gap-3">
                      <div className="w-2 h-2 rounded-full bg-gradient-to-r from-primary to-primary/80 mt-1.5 flex-shrink-0"></div>
                      <span className="text-foreground">استخدم أسماء واضحة ومفهومة للأجزاء</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <div className="w-2 h-2 rounded-full bg-gradient-to-r from-primary to-primary/80 mt-1.5 flex-shrink-0"></div>
                      <span className="text-foreground">تأكد من صحة الترجمة بين العربية والفرنسية</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <div className="w-2 h-2 rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600 mt-1.5 flex-shrink-0"></div>
                      <span className="text-foreground font-medium">الاقتراحات ستكون نشطة افتراضياً وتظهر للمصممين</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <div className="w-2 h-2 rounded-full bg-gradient-to-r from-primary to-primary/80 mt-1.5 flex-shrink-0"></div>
                      <span className="text-foreground">يمكن تعديل أو حذف الاقتراحات لاحقاً من قائمة الإدارة</span>
                    </li>
                  </ul>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
} 