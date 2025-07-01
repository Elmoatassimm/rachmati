import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/app-layout';
import { ModernPageHeader } from '@/components/ui/modern-page-header';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Designer, DesignerSocialMedia } from '@/types';
import {
  Store,
  Edit,
  ArrowLeft,
  User,
  Calendar,
  MapPin,
  Phone,
  Mail,
  Globe,
  Facebook,
  Instagram,
  Twitter,
  Youtube,
  ExternalLink,
  Crown,
  TrendingUp,
  Package,
  ShoppingCart,
  DollarSign,
  Star
} from 'lucide-react';

interface Props {
  designer: Designer;
  socialMedia: DesignerSocialMedia[];
}

export default function Show({ designer, socialMedia }: Props) {
  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('ar-DZ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ', {
      style: 'currency',
      currency: 'DZD',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const getSubscriptionBadge = (status: string) => {
    switch (status) {
      case 'active':
        return (
          <Badge className="bg-gradient-to-r from-green-500 to-green-600 text-white border-0">
            نشط
          </Badge>
        );
      case 'pending':
        return (
          <Badge className="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white border-0">
            معلق
          </Badge>
        );
      case 'expired':
        return (
          <Badge className="bg-gradient-to-r from-red-500 to-red-600 text-white border-0">
            منتهي الصلاحية
          </Badge>
        );
      default:
        return (
          <Badge variant="secondary">
            غير محدد
          </Badge>
        );
    }
  };

  const getSocialIcon = (platform: string) => {
    switch (platform.toLowerCase()) {
      case 'facebook':
        return <Facebook className="h-4 w-4" />;
      case 'instagram':
        return <Instagram className="h-4 w-4" />;
      case 'twitter':
        return <Twitter className="h-4 w-4" />;
      case 'youtube':
        return <Youtube className="h-4 w-4" />;
      default:
        return <Globe className="h-4 w-4" />;
    }
  };

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة المصمم', href: route('designer.dashboard') },
        { title: 'إدارة المتجر', href: route('designer.store.index') },
        { title: 'معاينة المتجر', href: route('designer.store.show') }
      ]}
    >
      <Head title={`${designer.store_name} - معاينة المتجر`} />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Header */}
          <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <ModernPageHeader
              title="معاينة المتجر"
              subtitle="كيف يبدو متجرك للعملاء"
              icon={Store}
            />
            
            <div className="flex items-center gap-4">
              <Link href={route('designer.store.index')}>
                <Button className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70">
                  <Edit className="ml-2 h-4 w-4" />
                  تعديل المتجر
                </Button>
              </Link>
              <Link href={route('designer.dashboard')}>
                <Button variant="outline">
                  <ArrowLeft className="ml-2 h-4 w-4" />
                  العودة للوحة
                </Button>
              </Link>
            </div>
          </div>

          <div className="grid grid-cols-1 xl:grid-cols-3 gap-8">
            {/* Main Content */}
            <div className="xl:col-span-2 space-y-8">
              {/* Store Header */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
                <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
                <CardContent className="relative p-8">
                  <div className="flex items-start gap-6">
                    <Avatar className="h-20 w-20 border-4 border-primary/20">
                      <AvatarFallback className="text-2xl font-bold bg-gradient-to-br from-primary to-primary/80 text-primary-foreground">
                        {designer.store_name?.charAt(0) || designer.user?.name?.charAt(0)}
                      </AvatarFallback>
                    </Avatar>
                    
                    <div className="flex-1 space-y-4">
                      <div>
                        <div className="flex items-center gap-3 mb-2">
                          <h1 className="text-3xl font-bold text-foreground">{designer.store_name}</h1>
                          {getSubscriptionBadge(designer.subscription_status)}
                        </div>
                        <p className="text-lg text-muted-foreground">{designer.user?.name}</p>
                      </div>
                      
                      {designer.store_description && (
                        <p className="text-foreground leading-relaxed">
                          {designer.store_description}
                        </p>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Store Statistics */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
                <CardHeader className="text-right">
                  <CardTitle className="text-2xl font-bold text-foreground text-right flex items-center gap-3">
                    <TrendingUp className="h-6 w-6" />
                    إحصائيات المتجر
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div className="text-center p-4 bg-gradient-to-br from-blue-500/10 to-blue-600/5 rounded-xl border border-blue-500/20">
                      <Package className="h-8 w-8 text-blue-600 mx-auto mb-2" />
                      <p className="text-2xl font-bold text-foreground">{designer.rachmat_count || 0}</p>
                      <p className="text-sm text-muted-foreground">رشمة</p>
                    </div>
                    
                    <div className="text-center p-4 bg-gradient-to-br from-green-500/10 to-green-600/5 rounded-xl border border-green-500/20">
                      <ShoppingCart className="h-8 w-8 text-green-600 mx-auto mb-2" />
                      <p className="text-2xl font-bold text-foreground">{designer.orders_count || 0}</p>
                      <p className="text-sm text-muted-foreground">طلب</p>
                    </div>
                    
                    <div className="text-center p-4 bg-gradient-to-br from-purple-500/10 to-purple-600/5 rounded-xl border border-purple-500/20">
                      <DollarSign className="h-8 w-8 text-purple-600 mx-auto mb-2" />
                      <p className="text-2xl font-bold text-foreground">{formatCurrency(designer.earnings || 0)}</p>
                      <p className="text-sm text-muted-foreground">الأرباح</p>
                    </div>
                    
                    <div className="text-center p-4 bg-gradient-to-br from-yellow-500/10 to-yellow-600/5 rounded-xl border border-yellow-500/20">
                      <Star className="h-8 w-8 text-yellow-600 mx-auto mb-2" />
                      <p className="text-2xl font-bold text-foreground">4.8</p>
                      <p className="text-sm text-muted-foreground">التقييم</p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Social Media Links */}
              {socialMedia && socialMedia.length > 0 && (
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
                  <CardHeader className="text-right">
                    <CardTitle className="text-2xl font-bold text-foreground text-right flex items-center gap-3">
                      <Globe className="h-6 w-6" />
                      وسائل التواصل الاجتماعي
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      {socialMedia.filter(social => social.is_active).map((social) => (
                        <a
                          key={social.id}
                          href={social.url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center gap-3 p-4 bg-muted/30 rounded-lg hover:bg-muted/50 transition-colors duration-200 group"
                        >
                          <div className="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center group-hover:bg-primary/20 transition-colors duration-200">
                            {getSocialIcon(social.platform)}
                          </div>
                          <div className="flex-1">
                            <p className="font-medium text-foreground capitalize">{social.platform}</p>
                            <p className="text-sm text-muted-foreground truncate">{social.url}</p>
                          </div>
                          <ExternalLink className="h-4 w-4 text-muted-foreground group-hover:text-foreground transition-colors duration-200" />
                        </a>
                      ))}
                    </div>
                  </CardContent>
                </Card>
              )}
            </div>

            {/* Sidebar */}
            <div className="space-y-8">
              {/* Store Information */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
                <CardHeader className="text-right">
                  <CardTitle className="text-xl font-bold text-foreground text-right flex items-center gap-3">
                    <User className="h-5 w-5" />
                    معلومات المتجر
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">اسم المالك</span>
                    <span className="font-semibold text-foreground">{designer.user?.name}</span>
                  </div>
                  
                  <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">البريد الإلكتروني</span>
                    <span className="font-semibold text-foreground text-sm">{designer.user?.email}</span>
                  </div>
                  
                  {designer.user?.phone && (
                    <div className="flex items-center justify-between">
                      <span className="text-muted-foreground">رقم الهاتف</span>
                      <span className="font-semibold text-foreground">{designer.user.phone}</span>
                    </div>
                  )}
                  
                  <Separator />
                  
                  <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">تاريخ الانضمام</span>
                    <span className="font-semibold text-foreground flex items-center gap-2">
                      <Calendar className="h-4 w-4" />
                      {formatDate(designer.created_at)}
                    </span>
                  </div>
                  
                  <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">حالة الاشتراك</span>
                    {getSubscriptionBadge(designer.subscription_status)}
                  </div>
                  
                  {designer.subscription_end_date && (
                    <div className="flex items-center justify-between">
                      <span className="text-muted-foreground">انتهاء الاشتراك</span>
                      <span className="font-semibold text-foreground text-sm">
                        {formatDate(designer.subscription_end_date)}
                      </span>
                    </div>
                  )}
                </CardContent>
              </Card>

              {/* Subscription Info */}
              {designer.pricing_plan && (
                <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-primary/10 to-primary/5 border-primary/20 shadow-xl rounded-2xl">
                  <CardHeader className="text-right">
                    <CardTitle className="text-xl font-bold text-foreground text-right flex items-center gap-3">
                      <Crown className="h-5 w-5 text-primary" />
                      خطة الاشتراك
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="text-center">
                      <h3 className="text-lg font-bold text-primary">{designer.pricing_plan.name_ar}</h3>
                      <p className="text-2xl font-bold text-foreground mt-2">
                        {formatCurrency(designer.pricing_plan.price)}
                        <span className="text-sm text-muted-foreground font-normal">/{designer.pricing_plan.duration_months} شهر</span>
                      </p>
                    </div>
                    
                    {designer.pricing_plan.description_ar && (
                      <p className="text-sm text-muted-foreground text-center">
                        {designer.pricing_plan.description_ar}
                      </p>
                    )}
                  </CardContent>
                </Card>
              )}

              {/* Quick Actions */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
                <CardHeader className="text-right">
                  <CardTitle className="text-xl font-bold text-foreground text-right">إجراءات سريعة</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <Link href={route('designer.store.index')}>
                    <Button variant="outline" className="w-full">
                      <Edit className="ml-2 h-4 w-4" />
                      تعديل معلومات المتجر
                    </Button>
                  </Link>
                  
                  <Link href={route('designer.rachmat.index')}>
                    <Button variant="outline" className="w-full">
                      <Package className="ml-2 h-4 w-4" />
                      إدارة الرشمات
                    </Button>
                  </Link>
                  
                  <Link href={route('designer.orders.index')}>
                    <Button variant="outline" className="w-full">
                      <ShoppingCart className="ml-2 h-4 w-4" />
                      عرض الطلبات
                    </Button>
                  </Link>
                  
                  <Link href={route('designers.show', designer.id)}>
                    <Button variant="outline" className="w-full">
                      <ExternalLink className="ml-2 h-4 w-4" />
                      عرض المتجر العام
                    </Button>
                  </Link>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
