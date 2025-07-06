import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/app-layout';
import { ModernPageHeader } from '@/components/ui/modern-page-header';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Progress } from '@/components/ui/progress';
import LazyImage from '@/components/ui/lazy-image';
import { Rachma, Order, Category } from '@/types';
import {
  ArrowLeft,
  Package,
  DollarSign,
  Calendar,
  Download,
  Trash2,
  Star,
  ShoppingCart,
  File,
  FileText,
  Image as ImageIcon,
  BarChart3
} from 'lucide-react';

interface Stats {
  total_orders: number;
  completed_orders: number;
  total_earnings: number;
  average_rating: number;
}

interface ExtendedRachma extends Rachma {
  categories?: Category[];
  title?: string;
  orders?: Order[];
  preview_image_urls?: string[];
}

interface Props {
  rachma: ExtendedRachma;
  stats: Stats;
}

export default function Show({ rachma, stats }: Props) {
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ar-DZ', {
      style: 'currency',
      currency: 'DZD',
      minimumFractionDigits: 0,
    }).format(price);
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('ar-DZ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const completionRate = stats.total_orders > 0 ? (stats.completed_orders / stats.total_orders) * 100 : 0;

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة المصمم', href: route('designer.dashboard') },
        { title: 'رشماتي', href: route('designer.rachmat.index') },
        { title: rachma.title_ar || 'رشمة', href: route('designer.rachmat.show', rachma.id) }
      ]}
    >
      <Head title={`${rachma.title_ar || 'رشمة'} - تفاصيل الرشمة`} />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/10">
        <div className="p-2 md:p-4 space-y-4">
          {/* Enhanced Header */}
                      <div className="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3">
            <div className="space-y-3">
              <ModernPageHeader
                title={rachma.title_ar || 'رشمة'}
                subtitle={rachma.title_fr || 'تفاصيل شاملة للرشمة مع الإحصائيات'}
                icon={Package}
              />
              <div className="flex items-center gap-4 text-sm text-muted-foreground">
                <div className="flex items-center gap-1">
                  <ShoppingCart className="h-4 w-4" />
                  {stats.total_orders} طلب
                </div>
                <div className="flex items-center gap-1">
                  <Star className="h-4 w-4 text-yellow-500" />
                  {stats.average_rating && typeof stats.average_rating === 'number' ? stats.average_rating.toFixed(1) : '0.0'}
                </div>
                <div className="flex items-center gap-1">
                  <DollarSign className="h-4 w-4 text-green-600" />
                  {formatPrice(stats.total_earnings)}
                </div>
              </div>
            </div>
            
            <div className="flex items-center gap-3">
              <Button 
                variant="destructive"
                onClick={() => {
                  if (confirm('هل أنت متأكد من حذف هذه الرشمة؟')) {
                    router.delete(route('designer.rachmat.destroy', rachma.id));
                  }
                }}
                className="bg-destructive hover:bg-destructive/90"
              >
                <Trash2 className="ml-2 h-4 w-4" />
                حذف الرشمة
              </Button>
              <Link href={route('designer.rachmat.index')}>
                <Button variant="outline">
                  العودة للقائمة
                  <ArrowLeft className="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>
          </div>

          <div className="grid grid-cols-1 xl:grid-cols-3 gap-4">
            {/* Main Content */}
            <div className="xl:col-span-2 space-y-4">
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
                  {rachma.preview_image_urls && rachma.preview_image_urls.length > 0 ? (
                    <div className="space-y-3">
                      {/* Main Image */}
                      <div className="relative group">
                        <div 
                          className="h-128 overflow-hidden rounded-2xl bg-muted border border-border/50 shadow-lg cursor-pointer"
                          onClick={() => rachma.preview_image_urls?.[selectedImageIndex] && window.open(rachma.preview_image_urls[selectedImageIndex], '_blank')}
                        >
                          <LazyImage
                            src={rachma.preview_image_urls?.[selectedImageIndex] || ''}
                            alt={rachma.title_ar}
                            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            aspectRatio="4:3"
                            priority={true}
                            showSkeleton={true}
                          />
                        </div>
                      
                        <div className="absolute bottom-4 right-4 bg-background/80 backdrop-blur-sm rounded-lg px-3 py-1 text-sm text-foreground">
                          {selectedImageIndex + 1} / {rachma.preview_image_urls?.length || 0}
                        </div>
                      </div>
                      
                      {/* Thumbnail Gallery */}
                      <div className="grid grid-cols-4 gap-3">
                        {(rachma.preview_image_urls || []).map((imageUrl: string, index: number) => (
                          <button
                            key={index}
                            onClick={() => setSelectedImageIndex(index)}
                            className={`relative aspect-square rounded-lg overflow-hidden border-2 transition-all duration-300 ${
                              selectedImageIndex === index ? 'border-primary ring-2 ring-primary/20' : 'border-border/50 hover:border-border'
                            }`}
                          >
                            <LazyImage
                              src={imageUrl}
                              alt={`Preview ${index + 1}`}
                              className="w-full h-full object-cover"
                              aspectRatio="1:1"
                              priority={false}
                              showSkeleton={true}
                            />
                          </button>
                        ))}
                      </div>
                    </div>
                  ) : (
                    <div className="text-center py-12">
                      <div className="w-20 h-20 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                        <ImageIcon className="w-10 h-10 text-muted-foreground" />
                      </div>
                      <h3 className="text-lg font-semibold mb-2">لا توجد صور معاينة</h3>
                      <p className="text-muted-foreground">لم يتم رفع أي صور معاينة لهذه الرشمة</p>
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
                  <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Basic Info */}
                    <div className="space-y-3">
                      <div className="space-y-4">
                        <div>
                          <label className="text-sm font-medium text-muted-foreground mb-1 block">العنوان بالعربية</label>
                          <h2 className="text-2xl font-bold text-foreground">{rachma.title_ar}</h2>
                        </div>
                        {rachma.title_fr && (
                          <div>
                            <label className="text-sm font-medium text-muted-foreground mb-1 block">العنوان بالفرنسية</label>
                            <h3 className="text-xl font-semibold text-foreground">{rachma.title_fr}</h3>
                          </div>
                        )}
                      </div>
                      
                      <div className="p-6 bg-gradient-to-br from-primary/5 to-primary/10 rounded-2xl border border-primary/20">
                        <label className="text-sm font-medium text-muted-foreground mb-2 block">السعر</label>
                        <p className="text-3xl font-bold text-primary">{formatPrice(rachma.price)}</p>
                      </div>
                    </div>
                    
                    {/* Technical Specs */}
                    <div className="space-y-3">
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
                    </div>
                  </div>

                  {/* Categories */}
                  {rachma.categories && rachma.categories.length > 0 && (
                    <div>
                      <label className="text-sm font-medium text-muted-foreground mb-3 block">الفئات</label>
                      <div className="flex flex-wrap gap-2">
                        {rachma.categories.map((category) => (
                          <Badge key={category.id} variant="secondary" className="text-sm px-3 py-1 bg-primary/10 text-primary border-primary/20">
                            {category.name_ar}
                          </Badge>
                        ))}
                      </div>
                    </div>
                  )}

                  {/* Descriptions */}
                  {(rachma.description_ar || rachma.description_fr) && (
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
                    </div>
                  )}
                </CardContent>
              </Card>

              
            </div>

            {/* Enhanced Sidebar */}
            <div className="space-y-4">
              {/* Enhanced Statistics */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                <CardHeader className="text-right">
                  <CardTitle className="text-xl font-bold text-foreground text-right flex items-center gap-3">
                    <BarChart3 className="h-5 w-5" />
                    إحصائيات الأداء
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <div className="space-y-2">
                    <div className="flex items-center justify-between p-3 bg-gradient-to-r from-blue-500/5 to-blue-500/10 rounded-lg border border-blue-500/20">
                      <span className="text-muted-foreground">إجمالي الطلبات</span>
                      <span className="font-bold text-foreground text-lg flex items-center gap-2">
                        <ShoppingCart className="h-4 w-4 text-blue-600" />
                        {stats.total_orders}
                      </span>
                    </div>
                    <div className="flex items-center justify-between p-3 bg-gradient-to-r from-green-500/5 to-green-500/10 rounded-lg border border-green-500/20">
                      <span className="text-muted-foreground">الطلبات المكتملة</span>
                      <span className="font-bold text-green-600 text-lg flex items-center gap-2">
                        <ShoppingCart className="h-4 w-4" />
                        {stats.completed_orders}
                      </span>
                    </div>
                    <div className="flex items-center justify-between p-3 bg-gradient-to-r from-yellow-500/5 to-yellow-500/10 rounded-lg border border-yellow-500/20">
                      <span className="text-muted-foreground">متوسط التقييم</span>
                      <span className="font-bold text-yellow-600 text-lg flex items-center gap-2">
                        <Star className="h-4 w-4" />
                        {stats.average_rating && typeof stats.average_rating === 'number' ? stats.average_rating.toFixed(1) : '0.0'}
                      </span>
                    </div>
                    <div className="flex items-center justify-between p-3 bg-gradient-to-r from-purple-500/5 to-purple-500/10 rounded-lg border border-purple-500/20">
                      <span className="text-muted-foreground">إجمالي الأرباح</span>
                      <span className="font-bold text-primary text-lg flex items-center gap-2">
                        <DollarSign className="h-4 w-4" />
                        {formatPrice(stats.total_earnings)}
                      </span>
                    </div>
                  </div>

                  {/* Completion Rate */}
                  <div className="space-y-3">
                    <div className="flex justify-between text-sm">
                      <span className="text-muted-foreground">معدل إتمام الطلبات</span>
                      <span className="font-medium text-foreground">{completionRate.toFixed(1)}%</span>
                    </div>
                    <Progress value={completionRate} className="h-2" />
                  </div>

                  <Separator />
                  
                  <div className="flex items-center justify-between p-3 bg-muted/20 rounded-lg">
                    <span className="text-muted-foreground">تاريخ الإنشاء</span>
                    <span className="font-semibold text-foreground flex items-center gap-2">
                      <Calendar className="h-4 w-4" />
                      {formatDate(rachma.created_at)}
                    </span>
                  </div>
                                </CardContent>
              </Card>

              {/* Files Section */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card/95 to-muted/20 shadow-xl rounded-2xl backdrop-blur-sm">
                <CardHeader className="text-right">
                  <CardTitle className="text-lg font-bold text-foreground text-right flex items-center gap-3">
                    <File className="h-5 w-5" />
                    ملفات الرشمة والتحميلات
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  {rachma.files && rachma.files.length > 0 ? (
                    <div className="space-y-3">
                      <div className="space-y-2">
                        {rachma.files.map((file, index) => (
                          <div key={index} className="flex items-center justify-between p-2 bg-gradient-to-r from-muted/30 to-muted/20 rounded-lg border border-border/50 hover:border-primary/30 transition-colors">
                            <div className="flex items-center gap-2">
                              <div className="w-8 h-8 bg-gradient-to-br from-primary/10 to-primary/20 rounded-lg flex items-center justify-center">
                                <File className="h-4 w-4 text-primary" />
                              </div>
                              <div>
                                <p className="text-sm font-medium text-foreground">{file.original_name}</p>
                                <p className="text-xs text-muted-foreground">{file.format}</p>
                              </div>
                            </div>
                            <a
                              href={route('designer.rachmat.download-file', [rachma.id, file.id])}
                              download={file.original_name}
                              className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-2"
                            >
                              <Download className="h-3 w-3" />
                            </a>
                          </div>
                        ))}
                      </div>
                      
                      <div className="pt-2">
                        <a
                          href={route('designer.rachmat.download', rachma.id)}
                          download
                          className="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white h-9 px-4"
                        >
                          <Download className="ml-2 h-4 w-4" />
                          تحميل جميع الملفات
                        </a>
                      </div>
                    </div>
                  ) : (
                    <div className="text-center py-4">
                      <div className="w-10 h-10 mx-auto mb-2 bg-gradient-to-br from-muted to-muted/70 rounded-full flex items-center justify-center">
                        <File className="h-5 w-5 text-muted-foreground" />
                      </div>
                      <h3 className="text-sm font-semibold text-foreground mb-1">لا توجد ملفات مرفقة</h3>
                      <p className="text-xs text-muted-foreground">لم يتم رفع أي ملفات لهذه الرشمة بعد</p>
                    </div>
                  )}
                </CardContent>
              </Card>

              
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
