import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { ModernPageHeader } from '@/components/ui/modern-page-header';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import LazyImage from '@/components/ui/lazy-image';
import ErrorBoundary from '@/components/error-boundary';
import { Rachma } from '@/types';
import {
  ArrowLeft,
  Package,
  User,
  DollarSign,
  Calendar,
  Download,
  FileImage,
  Trash2,
  AlertTriangle,
  Eye,
  Star,
  ShoppingCart,
  Layers,
  Ruler,
  Palette,
  FileText,
  Image as ImageIcon,
  ExternalLink,
  File,
  BarChart3
} from 'lucide-react';

interface FileInfo {
  size: number;
  last_modified: number;
  exists: boolean;
}

interface PreviewImageInfo {
  path: string;
  url?: string;
  size: number;
  last_modified: number;
  exists: boolean;
}

interface FileInfoNew {
  id: number;
  path: string;
  original_name: string;
  format: string;
  description: string;
  is_primary: boolean;
  exists: boolean;
  size: number;
  formatted_size: string;
  uploaded_at: string;
  download_url: string;
}

interface Props {
  rachma: Rachma;
  fileInfo: FileInfo | null;
  filesInfo: FileInfoNew[];
  previewImagesInfo: PreviewImageInfo[];
}

export default function Show({ rachma, fileInfo, filesInfo, previewImagesInfo }: Props) {
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);

  const handleDelete = () => {
    if (confirm('هل أنت متأكد من حذف هذه الرشمة؟ سيتم حذف جميع الملفات المرتبطة بها.')) {
      router.delete(route('admin.rachmat.destroy', rachma.id));
    }
  };

  const handleForceDelete = () => {
    if (confirm('هل أنت متأكد من الحذف النهائي؟ سيتم حذف الرشمة وجميع الطلبات المرتبطة بها نهائياً.')) {
      router.delete(route('admin.rachmat.force-destroy', rachma.id));
    }
  };

  const handlePreviewImage = (rachmaId: number, index: number) => {
    router.visit(route('admin.rachmat.preview-image', [rachmaId, index]), {
      method: 'get',
      preserveScroll: true,
      preserveState: true,
    });
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };



  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ar-DZ', {
      style: 'currency',
      currency: 'DZD',
      minimumFractionDigits: 0,
    }).format(price);
  };

  const formatRegularDate = (date: string) => {
    return new Date(date).toLocaleDateString('ar-DZ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'إدارة الرشمات', href: '/admin/rachmat' },
        { title: rachma.title_ar || rachma.title, href: `/admin/rachmat/${rachma.id}` }
      ]}
    >
      <Head title={`رشمة: ${rachma.title_ar || rachma.title}`} />
      
      <ErrorBoundary>
        <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/10">
          <div className="p-2 md:p-4 space-y-4 relative">
            {/* Enhanced Header */}
            <div className="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 relative">
              <div className="space-y-3">
                <ModernPageHeader
                  title={rachma.title_ar || rachma.title}
                  subtitle="تفاصيل شاملة للرشمة مع الإحصائيات - الإدارة العامة"
                  icon={Package}
                />
                <div className="flex items-center gap-4 text-sm text-muted-foreground">
                  <div className="flex items-center gap-1">
                    <ShoppingCart className="h-4 w-4" />
                    {rachma.orders_count || 0} طلب
                  </div>
                  <div className="flex items-center gap-1">
                    <Star className="h-4 w-4 text-yellow-500" />
                    {rachma.average_rating && typeof rachma.average_rating === 'number' ? rachma.average_rating.toFixed(1) : '0.0'}
                  </div>
                  <div className="flex items-center gap-1">
                    <DollarSign className="h-4 w-4 text-green-600" />
                    {formatPrice(rachma.orders_sum_amount || 0)}
                  </div>
                </div>
              </div>
              
              <div className="flex items-center gap-3">
                {((filesInfo.length > 0 && filesInfo.some(file => file.exists)) || fileInfo?.exists) && (
                  <a href={route('admin.rachmat.download-file', rachma.id)}>
                    <Button variant="outline">
                      <Download className="h-4 w-4 mr-2" />
                      تحميل الملف
                    </Button>
                  </a>
                )}
                <Link href={route('admin.rachmat.index')}>
                  <Button variant="outline">
                    العودة للقائمة
                    <ArrowLeft className="ml-2 h-4 w-4" />
                  </Button>
                </Link>
              </div>
            </div>

            <div className="grid grid-cols-1 xl:grid-cols-3 gap-4 relative">
              {/* Main Content */}
              <div className="xl:col-span-2 space-y-4 relative">
                {/* Enhanced Preview Images */}
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                  <CardHeader className="text-right">
                    <CardTitle className="text-2xl font-bold text-foreground text-right flex items-center gap-3">
                      <ImageIcon className="h-6 w-6" />
                      معرض الصور
                    </CardTitle>
                    <CardDescription className="text-right">
                      اضغط على الصورة للعرض بالحجم الكامل
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    {previewImagesInfo.length > 0 ? (
                      <div className="space-y-3">
                        {/* Main Image */}
                        <div className="relative group">
                          <div 
                            className="h-128 overflow-hidden rounded-2xl bg-muted border border-border/50 shadow-lg cursor-pointer"
                            onClick={() => previewImagesInfo[selectedImageIndex]?.url && window.open(previewImagesInfo[selectedImageIndex]?.url, '_blank')}
                          >
                            <LazyImage
                              src={previewImagesInfo[selectedImageIndex]?.url || ''}
                              alt={rachma.title_ar || rachma.title}
                              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                              aspectRatio="4:3"
                              priority={true}
                              showSkeleton={true}
                            />
                          </div>
                        
                          <div className="absolute bottom-4 right-4 bg-background/80 backdrop-blur-sm rounded-lg px-3 py-1 text-sm text-foreground">
                            {selectedImageIndex + 1} / {previewImagesInfo.length}
                          </div>
                        </div>
                        
                        {/* Thumbnail Gallery */}
                        {previewImagesInfo.length > 1 && (
                          <div className="grid grid-cols-6 gap-2">
                            {previewImagesInfo.map((imageInfo, index) => (
                              <button
                                key={index}
                                onClick={() => setSelectedImageIndex(index)}
                                className={`h-12 w-full overflow-hidden rounded-lg border-2 transition-all duration-300 hover:shadow-lg ${
                                  selectedImageIndex === index 
                                    ? 'border-primary shadow-lg ring-2 ring-primary/20' 
                                    : 'border-border hover:border-primary/50'
                                }`}
                              >
                                <LazyImage
                                  src={imageInfo.url || ''}
                                  alt={`${rachma.title_ar} - صورة ${index + 1}`}
                                  className="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                                  aspectRatio="4:3"
                                  priority={false}
                                  showSkeleton={true}
                                />
                              </button>
                            ))}
                          </div>
                        )}
                      </div>
                    ) : (
                      <div className="h-48 bg-gradient-to-br from-muted via-muted/80 to-muted/60 rounded-xl flex items-center justify-center border border-border/50">
                        <div className="text-center">
                          <Package className="mx-auto h-12 w-12 text-muted-foreground mb-3" />
                          <p className="text-sm text-muted-foreground">لا توجد صور معاينة</p>
                        </div>
                      </div>
                    )}
                  </CardContent>
                </Card>

                {/* Enhanced Basic Information */}
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                  <CardHeader className="text-right">
                    <CardTitle className="text-2xl font-bold text-foreground text-right flex items-center gap-3">
                      <FileText className="h-6 w-6" />
                      المعلومات التفصيلية
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div className="space-y-4">
                        <div>
                          <h4 className="text-sm font-medium text-muted-foreground">عدد الألوان</h4>
                          <p className="text-lg font-semibold">{rachma.color_numbers}</p>
                        </div>
                        <div>
                          <h4 className="text-sm font-medium text-muted-foreground">السعر</h4>
                          <p className="text-lg font-semibold">{rachma.price} دج</p>
                        </div>
                      </div>
                    </div>

                    {/* Categories */}
                    {rachma.categories && rachma.categories.length > 0 && (
                      <div>
                        <label className="text-sm font-medium text-muted-foreground mb-3 block">الفئات</label>
                        <div className="flex flex-wrap gap-2">
                          {rachma.categories.map((category) => (
                            <Badge key={category.id} variant="secondary" className="text-sm px-3 py-1 bg-primary/10 text-primary border-primary/20">
                              {category.name_ar || category.name}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    )}

                    {/* Descriptions */}
                    {(rachma.description_ar || rachma.description_fr || rachma.description) && (
                      <div className="space-y-3">
                        <Separator />
                        {rachma.description_ar && (
                          <div>
                            <label className="text-base font-semibold text-foreground mb-2 block">الوصف بالعربية</label>
                            <div className="p-3 bg-muted/30 rounded-lg border border-border/50">
                              <p className="text-sm text-foreground leading-relaxed">{rachma.description_ar}</p>
                            </div>
                          </div>
                        )}
                        {rachma.description_fr && (
                          <div>
                            <label className="text-base font-semibold text-foreground mb-2 block">الوصف بالفرنسية</label>
                            <div className="p-3 bg-muted/30 rounded-lg border border-border/50">
                              <p className="text-sm text-foreground leading-relaxed">{rachma.description_fr}</p>
                            </div>
                          </div>
                        )}
                        {rachma.description && !rachma.description_ar && !rachma.description_fr && (
                          <div>
                            <label className="text-base font-semibold text-foreground mb-2 block">الوصف</label>
                            <div className="p-3 bg-muted/30 rounded-lg border border-border/50">
                              <p className="text-sm text-foreground leading-relaxed">{rachma.description}</p>
                            </div>
                          </div>
                        )}
                      </div>
                    )}

                    <Separator />

                    <div className="flex items-center justify-between p-3 bg-muted/20 rounded-lg">
                      <span className="text-muted-foreground">تاريخ الإنشاء</span>
                      <span className="font-semibold text-foreground flex items-center gap-2">
                        <Calendar className="h-4 w-4" />
                        {formatRegularDate(rachma.created_at)}
                      </span>
                    </div>
                  </CardContent>
                </Card>

                {/* Designer Info */}
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                  <CardHeader className="text-right">
                    <CardTitle className="text-xl font-bold text-foreground text-right flex items-center gap-3">
                      <User className="h-5 w-5" />
                      معلومات المصمم
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="flex items-center gap-4">
                      <div className="text-right flex-1">
                        <p className="font-semibold text-lg">{rachma.designer?.store_name}</p>
                        <p className="text-muted-foreground">{rachma.designer?.user?.name}</p>
                        <p className="text-sm text-muted-foreground">{rachma.designer?.user?.email}</p>
                      </div>
                      <Button variant="outline" size="sm" asChild>
                        <Link href={route('admin.designers.show', rachma.designer?.id)}>
                          <ExternalLink className="h-4 w-4 mr-2" />
                          عرض المصمم
                        </Link>
                      </Button>
                    </div>
                  </CardContent>
                </Card>

                {/* Parts Information */}
                {rachma.parts && rachma.parts.length > 0 && (
                  <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                    <CardHeader className="text-right">
                      <CardTitle className="text-xl font-bold text-foreground text-right flex items-center gap-3">
                        <Layers className="h-5 w-5" />
                        أجزاء الرشمة ({rachma.parts.length})
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-3">
                        {rachma.parts.map((part, index) => (
                          <div key={part.id} className="border rounded-lg p-4">
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 text-right">
                              <div>
                                <label className="text-sm font-medium text-muted-foreground">اسم الجزء</label>
                                <p className="font-medium">{part.name}</p>
                              </div>
                              <div>
                                <label className="text-sm font-medium text-muted-foreground">الطول</label>
                                <p>{part.length || 'غير محدد'}</p>
                              </div>
                              <div>
                                <label className="text-sm font-medium text-muted-foreground">العرض</label>
                                <p>{part.height || 'غير محدد'}</p>
                              </div>
                              <div>
                                <label className="text-sm font-medium text-muted-foreground">عدد الغرز</label>
                                <p className="font-semibold text-blue-600">{part.stitches}</p>
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    </CardContent>
                  </Card>
                )}
              </div>

              {/* Enhanced Sidebar */}
              <div className="space-y-4">
                {/* Enhanced Statistics */}
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                  <CardHeader className="text-right">
                    <CardTitle className="text-xl font-bold text-foreground text-right flex items-center gap-3">
                      <BarChart3 className="h-5 w-5" />
                      إحصائيات المبيعات
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    <div className="flex items-center justify-between p-3 bg-gradient-to-r from-blue-500/5 to-blue-500/10 rounded-lg border border-blue-500/20">
                      <span className="text-muted-foreground">عدد الطلبات</span>
                      <span className="font-bold text-foreground text-lg flex items-center gap-2">
                        <ShoppingCart className="h-4 w-4 text-blue-600" />
                        {rachma.orders_count || 0}
                      </span>
                    </div>
                    <div className="flex items-center justify-between p-3 bg-gradient-to-r from-green-500/5 to-green-500/10 rounded-lg border border-green-500/20">
                      <span className="text-muted-foreground">إجمالي الإيرادات</span>
                      <span className="font-bold text-green-600 text-lg flex items-center gap-2">
                        <DollarSign className="h-4 w-4" />
                        {formatPrice(rachma.orders_sum_amount || 0)}
                      </span>
                    </div>
                                         <div className="flex items-center justify-between p-3 bg-gradient-to-r from-yellow-500/5 to-yellow-500/10 rounded-lg border border-yellow-500/20">
                       <span className="text-muted-foreground">متوسط التقييم</span>
                       <span className="font-bold text-yellow-600 text-lg flex items-center gap-2">
                         <Star className="h-4 w-4" />
                         {rachma.average_rating && typeof rachma.average_rating === 'number' ? rachma.average_rating.toFixed(1) : '0.0'}
                       </span>
                     </div>
                    <div className="flex items-center justify-between p-3 bg-gradient-to-r from-purple-500/5 to-purple-500/10 rounded-lg border border-purple-500/20">
                      <span className="text-muted-foreground">عدد التقييمات</span>
                      <span className="font-bold text-purple-600 text-lg">{rachma.ratings_count || 0}</span>
                    </div>
                  </CardContent>
                </Card>

                {/* Files Information */}
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                  <CardHeader className="text-right">
                    <CardTitle className="text-lg font-bold text-foreground text-right flex items-center gap-3">
                      <File className="h-5 w-5" />
                      ملفات الرشمة ({filesInfo.length})
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    {filesInfo.length > 0 ? (
                      <div className="space-y-3">
                        {filesInfo.map((file) => (
                          <div key={file.id} className="border rounded-lg p-3">
                            <div className="flex items-center justify-between mb-2">
                              <div className="flex items-center gap-2">
                                <Badge variant={file.is_primary ? "default" : "outline"}>
                                  {file.format}
                                </Badge>
                                {file.is_primary && (
                                  <Badge variant="secondary" className="text-xs">
                                    أساسي
                                  </Badge>
                                )}
                              </div>
                              <div className="flex items-center gap-1">
                                {file.exists ? (
                                  <Badge variant="outline" className="text-green-600 border-green-600 text-xs">
                                    متوفر
                                  </Badge>
                                ) : (
                                  <Badge variant="outline" className="text-red-600 border-red-600 text-xs">
                                    غير موجود
                                  </Badge>
                                )}
                              </div>
                            </div>

                            <div className="text-right space-y-1">
                              <p className="text-sm font-medium">{file.original_name}</p>
                              <p className="text-xs text-muted-foreground">{file.description}</p>
                              <p className="text-xs text-muted-foreground">الحجم: {file.formatted_size}</p>
                            </div>

                            {file.exists && (
                              <div className="mt-2">
                                <a href={file.download_url} className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-8 px-3 w-full">
                                  <Download className="h-3 w-3 mr-2" />
                                  تحميل {file.format}
                                </a>
                              </div>
                            )}
                          </div>
                        ))}
                      </div>
                    ) : (
                      <div className="text-center py-4">
                        <AlertTriangle className="h-8 w-8 text-red-500 mx-auto mb-2" />
                        <p className="text-sm text-red-600">لا توجد ملفات</p>
                      </div>
                    )}
                  </CardContent>
                </Card>

                {/* Preview Images */}
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                  <CardHeader className="text-right">
                    <CardTitle className="text-lg font-bold text-foreground text-right flex items-center gap-3">
                      <ImageIcon className="h-5 w-5" />
                      صور المعاينة ({previewImagesInfo.length})
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <ErrorBoundary
                      fallback={
                        <div className="p-4 text-center text-muted-foreground">
                          <FileImage className="h-12 w-12 mx-auto mb-2" />
                          <p className="text-sm">خطأ في تحميل صور المعاينة</p>
                        </div>
                      }
                    >
                    {previewImagesInfo.length > 0 ? (
                      <div className="space-y-4">
                        <div className="grid grid-cols-2 gap-2">
                                                     {previewImagesInfo.map((imageInfo, imageIndex) => (
                             <div key={imageIndex} className="relative group">
                               {imageInfo.exists ? (
                                 <div className="space-y-2">
                                   <div
                                     className="aspect-square bg-muted rounded-lg overflow-hidden cursor-pointer hover:opacity-80 transition-opacity"
                                     onClick={() => imageInfo.url && window.open(imageInfo.url, '_blank')}
                                   >
                                     <LazyImage
                                       src={imageInfo.url || ''}
                                       alt={`معاينة ${imageIndex + 1}`}
                                       className="w-full h-full object-cover aspect-square"
                                       aspectRatio="1:1"
                                       priority={false}
                                       showSkeleton={true}
                                     />
                                   </div>
                                   <div className="flex gap-1">
                                     <Button
                                       size="sm"
                                       variant="outline"
                                       className="flex-1"
                                       onClick={() => handlePreviewImage(rachma.id, imageIndex)}
                                     >
                                       <Eye className="h-3 w-3" />
                                     </Button>
                                     <a href={route('admin.rachmat.download-preview', [rachma.id, imageIndex])} className="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-2">
                                       <Download className="h-3 w-3" />
                                     </a>
                                   </div>
                                   <div className="text-xs text-muted-foreground text-center">
                                     {formatFileSize(imageInfo.size)}
                                   </div>
                                 </div>
                               ) : (
                                 <div className="aspect-square bg-muted rounded-lg flex items-center justify-center">
                                   <div className="text-center">
                                     <AlertTriangle className="h-6 w-6 text-red-500 mx-auto mb-1" />
                                     <p className="text-xs text-red-600">غير موجود</p>
                                   </div>
                                 </div>
                               )}
                             </div>
                           ))}
                        </div>
                      </div>
                    ) : (
                      <div className="text-center py-8">
                        <FileImage className="h-12 w-12 text-muted-foreground mx-auto mb-2" />
                        <p className="text-sm text-muted-foreground">لا توجد صور معاينة</p>
                      </div>
                    )}
                    </ErrorBoundary>
                  </CardContent>
                </Card>

                {/* Danger Zone */}
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-red-500/5 via-red-500/5 to-red-500/10 shadow-xl rounded-2xl backdrop-blur-sm border-red-200">
                  <CardHeader className="text-right">
                    <CardTitle className="text-lg font-bold text-right flex items-center gap-2 text-red-600">
                      <AlertTriangle className="h-5 w-5" />
                      منطقة الخطر
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    <Button
                      variant="destructive"
                      className="w-full"
                      onClick={handleDelete}
                    >
                      <Trash2 className="h-4 w-4 mr-2" />
                      حذف الرشمة
                    </Button>
                   
                    <p className="text-xs text-muted-foreground text-center">
                      الحذف النهائي سيحذف الرشمة وجميع الطلبات المرتبطة بها نهائياً
                    </p>
                  </CardContent>
                </Card>
              </div>
            </div>
          </div>
        </div>
      </ErrorBoundary>
    </AppLayout>
  );
}
