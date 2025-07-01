import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/app-layout';
import { ModernPageHeader } from '@/components/ui/modern-page-header';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';

import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { Rachma, Category, PartsSuggestion } from '@/types';
import {
  ArrowLeft,
  Save,
  Eye,
  Package,
  FileText,
  File,
  AlertCircle,
  X,
  Loader2,

  Star,
  Globe,
  Palette
} from 'lucide-react';

interface Props {
  rachma: Rachma;
  categories: Category[];
  partsSuggestions?: PartsSuggestion[];
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
    color_numbers: Array.isArray(rachma.color_numbers) ? rachma.color_numbers[0] : rachma.color_numbers || '',
    price: rachma.price || '',
    preview_images: [] as File[],
    files: [] as File[],
    remove_preview_images: [] as string[],
    remove_files: [] as number[],
  });

  const [previewImages, setPreviewImages] = useState<string[]>([]);
  const [selectedFiles, setSelectedFiles] = useState<string[]>([]);
  const [uploadProgress, setUploadProgress] = useState(0);
  
  // Track existing files/images that should be kept
  const [currentPreviewImages, setCurrentPreviewImages] = useState(rachma.preview_image_urls || []);
  const [currentFiles, setCurrentFiles] = useState(rachma.files || []);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setUploadProgress(0);
    put(route('designer.rachmat.update', rachma.id), {
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

  const handlePreviewImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    setData('preview_images', files);
    
    // Create preview URLs
    const urls = files.map(file => URL.createObjectURL(file));
    setPreviewImages(urls);
  };

  const handleFilesChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    setData('files', files);
    
    // Create file names list
    const names = files.map(file => file.name);
    setSelectedFiles(names);
  };

  const removePreviewImage = (index: number) => {
    const newFiles = data.preview_images.filter((_, i) => i !== index);
    const newUrls = previewImages.filter((_, i) => i !== index);
    setData('preview_images', newFiles);
    setPreviewImages(newUrls);
  };

  const removeSelectedFile = (index: number) => {
    const newFiles = data.files.filter((_, i) => i !== index);
    const newNames = selectedFiles.filter((_, i) => i !== index);
    setData('files', newFiles);
    setSelectedFiles(newNames);
  };

  // Remove existing preview image
  const removeCurrentPreviewImage = (imageUrl: string) => {
    setCurrentPreviewImages(prev => prev.filter(url => url !== imageUrl));
    setData('remove_preview_images', [...data.remove_preview_images, imageUrl]);
  };

  // Remove existing file
  const removeCurrentFile = (fileId: number) => {
    setCurrentFiles(prev => prev.filter(file => file.id !== fileId));
    setData('remove_files', [...data.remove_files, fileId]);
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
        { title: 'لوحة المصمم', href: route('designer.dashboard') },
        { title: 'رشماتي', href: route('designer.rachmat.index') },
        { title: rachma.title_ar || 'رشمة', href: route('designer.rachmat.show', rachma.id) },
        { title: 'تعديل', href: route('designer.rachmat.edit', rachma.id) }
      ]}
    >
      <Head title={`تعديل ${rachma.title_ar || 'رشمة'} - Edit Rachma`} />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/10">
        <div className="p-4 md:p-8 space-y-8">
          {/* Enhanced Header */}
          <div className="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div className="space-y-2">
              <ModernPageHeader
                title={`تعديل ${rachma.title_ar || 'رشمة'}`}
                subtitle="تحديث معلومات وملفات الرشمة بسهولة وأمان"
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
              <Link href={route('designer.rachmat.show', rachma.id)}>
                <Button variant="outline">
                  <Eye className="ml-2 h-4 w-4" />
                  معاينة الرشمة
                </Button>
              </Link>
              <Link href={route('designer.rachmat.index')}>
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
                              {category.name_ar}
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
                {/* Interactive Files Management */}
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                  <CardHeader className="text-right">
                    <CardTitle className="text-xl font-bold text-foreground text-right">إدارة الملفات والصور</CardTitle>
                    <CardDescription className="text-right">يمكنك إضافة أو حذف الملفات والصور الموجودة</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    {/* Current Preview Images */}
                    {currentPreviewImages.length > 0 && (
                      <div>
                        <div className="flex items-center justify-between mb-3">
                          <Label className="text-sm font-medium text-muted-foreground">صور المعاينة الحالية</Label>
                          <span className="text-xs text-muted-foreground">{currentPreviewImages.length} صورة</span>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                          {currentPreviewImages.map((url, index) => (
                            <div key={index} className="relative group aspect-square overflow-hidden rounded-lg border border-border/50">
                              <img
                                src={url}
                                alt={`معاينة حالية ${index + 1}`}
                                className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                              />
                              <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <Button
                                  type="button"
                                  size="sm"
                                  variant="destructive"
                                  className="h-8 w-8 p-0 rounded-full"
                                  onClick={() => removeCurrentPreviewImage(url)}
                                >
                                  <X className="h-4 w-4" />
                                </Button>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}

                    {/* Current Files */}
                    {currentFiles.length > 0 && (
                      <div>
                        <div className="flex items-center justify-between mb-3">
                          <Label className="text-sm font-medium text-muted-foreground">الملفات الحالية</Label>
                          <span className="text-xs text-muted-foreground">{currentFiles.length} ملف</span>
                        </div>
                        <div className="space-y-2">
                          {currentFiles.map((file) => (
                            <div key={file.id} className="flex items-center gap-3 p-3 bg-muted/30 rounded-lg border border-border/50 group">
                              <div className="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                <File className="h-4 w-4 text-primary" />
                              </div>
                              <div className="flex-1 min-w-0">
                                <p className="text-sm font-medium truncate">{file.original_name}</p>
                                <p className="text-xs text-muted-foreground">{file.format}</p>
                              </div>
                              <div className="flex items-center gap-2">
                                {file.is_primary && (
                                  <Badge variant="default" className="text-xs">أساسي</Badge>
                                )}
                                <Button
                                  type="button"
                                  size="sm"
                                  variant="ghost"
                                  className="h-8 w-8 p-0 text-muted-foreground hover:text-destructive opacity-0 group-hover:opacity-100 transition-opacity"
                                  onClick={() => removeCurrentFile(file.id)}
                                >
                                  <X className="h-4 w-4" />
                                </Button>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}

                    {/* Empty State */}
                    {currentPreviewImages.length === 0 && currentFiles.length === 0 && (
                      <div className="text-center py-8 text-muted-foreground">
                        <Package className="h-12 w-12 mx-auto mb-3 opacity-50" />
                        <p className="text-sm">لا توجد ملفات أو صور حالياً</p>
                        <p className="text-xs">يمكنك إضافة ملفات جديدة أدناه</p>
                      </div>
                    )}
                  </CardContent>
                </Card>

               

                {/* Action Buttons */}
                <div className="space-y-3">
                  <Button 
                    type="submit" 
                    disabled={processing || !isFormValid()} 
                    className="w-full h-12 bg-gradient-to-r from-primary via-primary/90 to-primary/80 hover:from-primary/90 hover:via-primary/80 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300"
                  >
                    {processing ? (
                      <>
                        <Loader2 className="ml-2 h-5 w-5 animate-spin" />
                        جاري الحفظ...
                      </>
                    ) : (
                      <>
                        <Save className="ml-2 h-5 w-5" />
                        حفظ التغييرات
                      </>
                    )}
                  </Button>
                  
                  <Link href={route('designer.rachmat.show', rachma.id)} className="block">
                    <Button variant="outline" className="w-full h-12">
                      <Eye className="ml-2 h-4 w-4" />
                      معاينة الرشمة
                    </Button>
                  </Link>
                  
                  <Link href={route('designer.rachmat.index')} className="block">
                    <Button variant="outline" className="w-full h-12">
                      <ArrowLeft className="ml-2 h-4 w-4" />
                      إلغاء والعودة
                    </Button>
                  </Link>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </AppLayout>
  );
}
