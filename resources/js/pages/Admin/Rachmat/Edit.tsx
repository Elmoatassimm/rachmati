import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { ModernPageHeader } from '@/components/ui/modern-page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import ErrorBoundary from '@/components/error-boundary';
import { Rachma, Category } from '@/types';
import {
  ArrowLeft,
  Package,
  Save,
  X,
  FileText,
  Globe,
  Star,
  Palette,
  AlertCircle,
  Loader2,
  Eye
} from 'lucide-react';

interface Props {
  rachma: Rachma;
  categories: Category[];
}

export default function Edit({ rachma, categories }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    title_ar: rachma.title_ar || '',
    title_fr: rachma.title_fr || '',
    description_ar: rachma.description_ar || '',
    description_fr: rachma.description_fr || '',
    categories: rachma.categories?.map(cat => cat.id) || [],
    width: rachma.width || '',
    height: rachma.height || '',
    gharazat: rachma.gharazat || '',
    color_numbers: rachma.color_numbers || '',
    price: rachma.price || '',
  });

  const [uploadProgress, setUploadProgress] = useState(0);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setUploadProgress(0);
    put(route('admin.rachmat.update', rachma.id), {
      preserveScroll: true,
      onProgress: () => {
        setUploadProgress(prev => Math.min(prev + 10, 90));
      },
      onSuccess: () => {
        setUploadProgress(100);
      },
      onError: () => {
        setUploadProgress(0);
      },
    });
  };

  const handleCategoryChange = (categoryId: number, checked: boolean) => {
    if (checked) {
      setData('categories', [...data.categories, categoryId]);
    } else {
      setData('categories', data.categories.filter(id => id !== categoryId));
    }
  };

  const getFieldError = (field: string) => {
    return errors[field as keyof typeof errors];
  };

  const isFormValid = () => {
    return data.title_ar && data.width && data.height && data.gharazat && data.color_numbers && data.price && data.categories.length > 0;
  };

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'إدارة الرشمات', href: '/admin/rachmat' },
        { title: rachma.title_ar || rachma.title, href: `/admin/rachmat/${rachma.id}` },
        { title: 'تعديل', href: `/admin/rachmat/${rachma.id}/edit` }
      ]}
    >
      <Head title={`تعديل رشمة: ${rachma.title_ar || rachma.title}`} />
      
      <ErrorBoundary>
        <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/10">
          <div className="p-4 md:p-8 space-y-8">
            {/* Enhanced Header */}
            <div className="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
              <div className="space-y-2">
                <ModernPageHeader
                  title={`تعديل ${rachma.title_ar || rachma.title}`}
                  subtitle="تحديث معلومات الرشمة بسهولة وأمان - الإدارة العامة"
                  icon={Package}
                />
                <div className="flex items-center gap-4 text-sm text-muted-foreground">
                  <div className="flex items-center gap-1">
                    <Package className="h-4 w-4" />
                    معرف الرشمة: {rachma.id}
                  </div>
                  <div className="flex items-center gap-1">
                    <Eye className="h-4 w-4" />
                    آخر تحديث: {new Date(rachma.updated_at).toLocaleDateString('ar-DZ')}
                  </div>
                </div>
              </div>
              
              <div className="flex items-center gap-3">
                <Link href={route('admin.rachmat.show', rachma.id)}>
                  <Button variant="outline">
                    <Eye className="ml-2 h-4 w-4" />
                    معاينة الرشمة
                  </Button>
                </Link>
                <Link href={route('admin.rachmat.index')}>
                  <Button variant="outline">
                    <ArrowLeft className="ml-2 h-4 w-4" />
                    العودة للقائمة
                  </Button>
                </Link>
              </div>
            </div>

            {/* Progress Indicator */}
            {processing && (
              <Card className="border-primary/20 bg-primary/5">
                <CardContent className="pt-6">
                  <div className="flex items-center gap-4">
                    <Loader2 className="h-5 w-5 animate-spin text-primary" />
                    <div className="flex-1">
                      <p className="text-sm font-medium text-foreground mb-2">جاري حفظ التغييرات...</p>
                      <Progress value={uploadProgress} className="h-2" />
                    </div>
                    <span className="text-sm text-muted-foreground">{uploadProgress}%</span>
                  </div>
                </CardContent>
              </Card>
            )}

            <form onSubmit={handleSubmit} className="space-y-8">
              <div className="grid grid-cols-1 xl:grid-cols-3 gap-8">
                {/* Main Form */}
                <div className="xl:col-span-2 space-y-8">
                  {/* Enhanced Basic Information */}
                  <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                    <CardHeader className="text-right">
                      <CardTitle className="text-2xl font-bold text-foreground text-right flex items-center gap-3">
                        <FileText className="h-6 w-6" />
                        المعلومات الأساسية
                      </CardTitle>
                      <CardDescription className="text-right">
                        قم بتحديث المعلومات الأساسية للرشمة مع التحقق من صحة البيانات
                      </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-8">
                      {/* Titles Section */}
                      <div className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                          <div className="space-y-3">
                            <Label htmlFor="title_ar" className="flex items-center gap-2">
                              <Globe className="h-4 w-4" />
                              عنوان الرشمة (عربي) *
                            </Label>
                            <Input
                              id="title_ar"
                              value={data.title_ar}
                              onChange={(e) => setData('title_ar', e.target.value)}
                              className={`text-right h-12 ${getFieldError('title_ar') ? 'border-red-500 focus:border-red-500' : 'border-border/50 focus:border-primary/50'}`}
                              placeholder="أدخل عنوان الرشمة بالعربي"
                              required
                            />
                            {getFieldError('title_ar') && (
                              <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {getFieldError('title_ar')}
                              </p>
                            )}
                          </div>

                          <div className="space-y-3">
                            <Label htmlFor="title_fr" className="flex items-center gap-2">
                              <Globe className="h-4 w-4" />
                              عنوان الرشمة (فرنسي)
                            </Label>
                            <Input
                              id="title_fr"
                              value={data.title_fr}
                              onChange={(e) => setData('title_fr', e.target.value)}
                              className="h-12 border-border/50 focus:border-primary/50"
                              placeholder="Titre en français"
                            />
                            {getFieldError('title_fr') && (
                              <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {getFieldError('title_fr')}
                              </p>
                            )}
                          </div>
                        </div>
                      </div>

                      <Separator />

                      {/* Technical Specifications */}
                      <div className="space-y-6">
                        <h3 className="text-lg font-semibold text-foreground flex items-center gap-2">
                          <Star className="h-5 w-5" />
                          المواصفات التقنية
                        </h3>
                        
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                          <div className="space-y-3">
                            <Label htmlFor="width">العرض (سم) *</Label>
                            <Input
                              id="width"
                              type="number"
                              value={data.width}
                              onChange={(e) => setData('width', e.target.value)}
                              className={`h-12 ${getFieldError('width') ? 'border-red-500' : 'border-border/50'}`}
                              placeholder="العرض"
                              required
                            />
                            {getFieldError('width') && <p className="text-sm text-red-600">{getFieldError('width')}</p>}
                          </div>

                          <div className="space-y-3">
                            <Label htmlFor="height">الارتفاع (سم) *</Label>
                            <Input
                              id="height"
                              type="number"
                              value={data.height}
                              onChange={(e) => setData('height', e.target.value)}
                              className={`h-12 ${getFieldError('height') ? 'border-red-500' : 'border-border/50'}`}
                              placeholder="الارتفاع"
                              required
                            />
                            {getFieldError('height') && <p className="text-sm text-red-600">{getFieldError('height')}</p>}
                          </div>

                          <div className="space-y-3">
                            <Label htmlFor="price">السعر (دج) *</Label>
                            <Input
                              id="price"
                              type="number"
                              value={data.price}
                              onChange={(e) => setData('price', e.target.value)}
                              className={`h-12 ${getFieldError('price') ? 'border-red-500' : 'border-border/50'}`}
                              placeholder="السعر"
                              required
                            />
                            {getFieldError('price') && <p className="text-sm text-red-600">{getFieldError('price')}</p>}
                          </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div className="space-y-3">
                            <Label htmlFor="gharazat">عدد الغرزات *</Label>
                            <Input
                              id="gharazat"
                              type="number"
                              value={data.gharazat}
                              onChange={(e) => setData('gharazat', e.target.value)}
                              className={`h-12 ${getFieldError('gharazat') ? 'border-red-500' : 'border-border/50'}`}
                              placeholder="عدد الغرزات"
                              required
                            />
                            {getFieldError('gharazat') && <p className="text-sm text-red-600">{getFieldError('gharazat')}</p>}
                          </div>

                          <div className="space-y-3">
                            <Label htmlFor="color_numbers" className="flex items-center gap-2">
                              <Palette className="h-4 w-4" />
                              عدد الألوان *
                            </Label>
                            <Input
                              id="color_numbers"
                              type="number"
                              value={data.color_numbers}
                              onChange={(e) => setData('color_numbers', e.target.value)}
                              className={`h-12 ${getFieldError('color_numbers') ? 'border-red-500' : 'border-border/50'}`}
                              placeholder="عدد الألوان"
                              required
                            />
                            {getFieldError('color_numbers') && <p className="text-sm text-red-600">{getFieldError('color_numbers')}</p>}
                          </div>
                        </div>
                      </div>

                      <Separator />

                      {/* Descriptions */}
                      <div className="space-y-6">
                        <h3 className="text-lg font-semibold text-foreground">الأوصاف التفصيلية</h3>
                        
                        <div className="space-y-6">
                          <div className="space-y-3">
                            <Label htmlFor="description_ar">وصف الرشمة (عربي)</Label>
                            <Textarea
                              id="description_ar"
                              value={data.description_ar}
                              onChange={(e) => setData('description_ar', e.target.value)}
                              className="text-right min-h-[120px] border-border/50 focus:border-primary/50"
                              placeholder="وصف تفصيلي للرشمة بالعربي يساعد العملاء على فهم التصميم..."
                              rows={5}
                            />
                            {getFieldError('description_ar') && <p className="text-sm text-red-600">{getFieldError('description_ar')}</p>}
                          </div>

                          <div className="space-y-3">
                            <Label htmlFor="description_fr">وصف الرشمة (فرنسي)</Label>
                            <Textarea
                              id="description_fr"
                              value={data.description_fr}
                              onChange={(e) => setData('description_fr', e.target.value)}
                              className="min-h-[120px] border-border/50 focus:border-primary/50"
                              placeholder="Description détaillée en français..."
                              rows={5}
                            />
                            {getFieldError('description_fr') && <p className="text-sm text-red-600">{getFieldError('description_fr')}</p>}
                          </div>
                        </div>
                      </div>

                      <Separator />

                      {/* Enhanced Categories */}
                      <div className="space-y-6">
                        <div className="flex items-center justify-between">
                          <Label className="text-lg font-semibold text-foreground">الفئات *</Label>
                          <span className="text-sm text-muted-foreground">
                            {data.categories.length} من {categories.length} محددة
                          </span>
                        </div>
                        <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                          {categories.map((category) => (
                            <div key={category.id} className="flex items-center space-x-2 space-x-reverse p-3 bg-muted/20 rounded-lg border border-border/50 hover:border-primary/30 transition-colors">
                              <Checkbox
                                id={`category-${category.id}`}
                                checked={data.categories.includes(category.id)}
                                onCheckedChange={(checked) => handleCategoryChange(category.id, checked as boolean)}
                              />
                              <Label htmlFor={`category-${category.id}`} className="text-sm font-medium cursor-pointer">
                                {category.name_ar || category.name}
                              </Label>
                            </div>
                          ))}
                        </div>
                        {getFieldError('categories') && (
                          <p className="text-sm text-red-600 flex items-center gap-1">
                            <AlertCircle className="h-4 w-4" />
                            {getFieldError('categories')}
                          </p>
                        )}
                      </div>
                    </CardContent>
                  </Card>
                </div>

                {/* Enhanced Sidebar */}
                <div className="space-y-8">
                  {/* Actions Card */}
                  <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                    <CardHeader className="text-right">
                      <CardTitle className="text-xl font-bold text-foreground text-right">الإجراءات</CardTitle>
                      <CardDescription className="text-right">احفظ التغييرات أو ألغِ العملية</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <Button 
                        type="submit" 
                        className="w-full h-12 bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70"
                        disabled={processing || !isFormValid()}
                      >
                        <Save className="h-4 w-4 mr-2" />
                        {processing ? 'جاري الحفظ...' : 'حفظ التغييرات'}
                      </Button>
                      
                      <Button 
                        type="button" 
                        variant="outline" 
                        className="w-full h-12"
                        asChild
                      >
                        <Link href={route('admin.rachmat.show', rachma.id)}>
                          <X className="h-4 w-4 mr-2" />
                          إلغاء
                        </Link>
                      </Button>
                    </CardContent>
                  </Card>

                  {/* Enhanced Additional Info */}
                  <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                    <CardHeader className="text-right">
                      <CardTitle className="text-lg font-bold text-foreground text-right">معلومات إضافية</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <div className="space-y-3">
                        <div className="flex items-center justify-between p-3 bg-gradient-to-r from-blue-500/5 to-blue-500/10 rounded-lg border border-blue-500/20">
                          <span className="text-muted-foreground">المصمم</span>
                          <span className="font-semibold text-foreground">{rachma.designer?.store_name}</span>
                        </div>
                        <div className="flex items-center justify-between p-3 bg-gradient-to-r from-green-500/5 to-green-500/10 rounded-lg border border-green-500/20">
                          <span className="text-muted-foreground">تاريخ الرفع</span>
                          <span className="font-semibold text-foreground">{new Date(rachma.created_at).toLocaleDateString('ar-DZ')}</span>
                        </div>
                        <div className="flex items-center justify-between p-3 bg-gradient-to-r from-purple-500/5 to-purple-500/10 rounded-lg border border-purple-500/20">
                          <span className="text-muted-foreground">عدد المبيعات</span>
                          <span className="font-semibold text-foreground">{rachma.orders_count || 0}</span>
                        </div>
                        <div className="flex items-center justify-between p-3 bg-gradient-to-r from-yellow-500/5 to-yellow-500/10 rounded-lg border border-yellow-500/20">
                          <span className="text-muted-foreground">التقييم</span>
                          <span className="font-semibold text-foreground flex items-center gap-1">
                            <Star className="h-4 w-4 text-yellow-500" />
                            {rachma.average_rating || 0} ({rachma.ratings_count || 0})
                          </span>
                        </div>
                      </div>
                    </CardContent>
                  </Card>

                  {/* Form Validation Status */}
                  <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                    <CardHeader className="text-right">
                      <CardTitle className="text-lg font-bold text-foreground text-right">حالة النموذج</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-3">
                        <div className="flex items-center justify-between">
                          <span className="text-sm text-muted-foreground">العنوان العربي</span>
                          <Badge variant={data.title_ar ? "secondary" : "destructive"}>
                            {data.title_ar ? "مكتمل" : "مطلوب"}
                          </Badge>
                        </div>
                        <div className="flex items-center justify-between">
                          <span className="text-sm text-muted-foreground">المواصفات</span>
                          <Badge variant={data.width && data.height && data.gharazat && data.color_numbers ? "secondary" : "destructive"}>
                            {data.width && data.height && data.gharazat && data.color_numbers ? "مكتمل" : "مطلوب"}
                          </Badge>
                        </div>
                        <div className="flex items-center justify-between">
                          <span className="text-sm text-muted-foreground">السعر</span>
                          <Badge variant={data.price ? "secondary" : "destructive"}>
                            {data.price ? "مكتمل" : "مطلوب"}
                          </Badge>
                        </div>
                        <div className="flex items-center justify-between">
                          <span className="text-sm text-muted-foreground">الفئات</span>
                          <Badge variant={data.categories.length > 0 ? "secondary" : "destructive"}>
                            {data.categories.length > 0 ? "مكتمل" : "مطلوب"}
                          </Badge>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </div>
              </div>
            </form>
          </div>
        </div>
      </ErrorBoundary>
    </AppLayout>
  );
}
