import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';


import { DesignerStatsCards } from '@/components/designer/DesignerStatsCards';
import LazyImage from '@/components/ui/lazy-image';
import { Designer, Order, Rachma, Rating } from '@/types';
import {
  Package,
  TrendingUp,
  ShoppingCart,
  AlertTriangle,
  Plus,
  Eye,
  Calendar,
  User,
  Crown,
  ClipboardList
} from 'lucide-react';

interface Stats {
  totalRachmat: number;
  activeRachmat: number;
  totalSales: number;
  totalEarnings: number;
  unpaidEarnings: number;
  averageRating: number;
}

interface MonthlySales {
  month: string;
  sales: number;
}

interface Props {
  designer: Designer;
  stats: Stats;
  recentOrders: Order[];
  monthlySales: MonthlySales[];
  topRachmat: Rachma[];
  recentRatings: Rating[];
}

export default function Dashboard({
  designer,
  stats,
  recentOrders,
  topRachmat,
}: Omit<Props, 'monthlySales' | 'recentRatings'>) {
  const getSubscriptionBadge = (status: string) => {
    switch (status) {
      case 'active':
        return (
          <Badge className="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 text-lg font-bold shadow-lg border-0">
            <span className="text-right">نشط</span>
          </Badge>
        );
      case 'pending':
        return (
          <Badge className="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-6 py-3 text-lg font-bold shadow-lg border-0">
            <span className="text-right">معلق</span>
          </Badge>
        );
      case 'expired':
        return (
          <Badge className="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-3 text-lg font-bold shadow-lg border-0">
            <span className="text-right">منتهي</span>
          </Badge>
        );
      default:
        return (
          <Badge variant="outline" className="px-6 py-3 text-lg font-bold border-2">
            <span className="text-right">{status}</span>
          </Badge>
        );
    }
  };

  const getOrderStatusBadge = (status: string) => {
    switch (status) {
      case 'completed':
        return (
          <Badge className="bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-1 text-sm font-bold border-0">
            مكتمل
          </Badge>
        );
      case 'pending':
        return (
          <Badge className="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-3 py-1 text-sm font-bold border-0">
            معلق
          </Badge>
        );
      case 'processing':
        return (
          <Badge className="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-1 text-sm font-bold border-0">
            قيد المعالجة
          </Badge>
        );
      default:
        return (
          <Badge variant="secondary" className="px-3 py-1 text-sm font-bold">
            {status}
          </Badge>
        );
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ', {
      style: 'currency',
      currency: 'DZD',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  return (
    <AppLayout 
      breadcrumbs={[
        { title: 'لوحة المصمم', href: '/designer/dashboard' }
      ]}
    >
      <Head title="لوحة المصمم - Designer Dashboard" />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Modern Header */}
          <div className="relative">
            <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5 rounded-3xl"></div>
            <div className="relative p-8 space-y-4">
              <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-6 md:gap-8">
                {/* Title Section */}
                <div className="flex items-start gap-4 flex-1 order-2 md:order-1">
                  <div className="w-16 h-16 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                    <Crown className="w-8 h-8 text-primary-foreground" />
                  </div>
                  <div className="flex-1">
                    <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent text-right leading-tight">
                      مرحباً، {designer.store_name}
                    </h1>
                    <p className="text-base md:text-lg lg:text-xl text-muted-foreground mt-2 text-right">
                      مرحباً بك في لوحة تحكم المصمم
                    </p>
                  </div>
                </div>
                
                {/* Subscription Info */}
                <div className="flex flex-col items-end gap-3 text-right flex-shrink-0 order-1 md:order-2">
                  <div className="flex justify-end">
                    {getSubscriptionBadge(designer.subscription_status)}
                  </div>
                  {designer.subscription_end_date && (
                    <div className="flex items-center gap-2 text-sm md:text-base text-muted-foreground">
                      <span>ينتهي في: {new Date(designer.subscription_end_date).toLocaleDateString('en-US', {year: 'numeric', month: '2-digit', day: '2-digit'})}</span>
                      <Calendar className="w-4 h-4 md:w-5 md:h-5" />
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Subscription Status Notification */}
          {designer.subscription_status !== 'active' && (
            <div className="relative overflow-hidden">
              <div className={`absolute inset-0 bg-gradient-to-r ${
                designer.subscription_status === 'pending' 
                  ? 'from-yellow-500/20 via-yellow-500/10 to-orange-500/20' 
                  : 'from-red-500/20 via-red-500/10 to-red-600/20'
              } rounded-2xl`}></div>
              
              <Card className="relative border-0 shadow-xl rounded-2xl backdrop-blur-sm">
                <CardContent className="p-8">
                  <div className="flex items-start gap-6 flex-row-reverse">
                    {/* Icon */}
                    <div className={`w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0 ${
                      designer.subscription_status === 'pending'
                        ? 'bg-gradient-to-br from-yellow-500 to-orange-500'
                        : 'bg-gradient-to-br from-red-500 to-red-600'
                    }`}>
                      <AlertTriangle className="w-8 h-8 text-white" />
                    </div>
                    
                    {/* Content */}
                    <div className="flex-1 space-y-3">
                      <h3 className={`text-2xl font-bold text-right leading-tight ${
                        designer.subscription_status === 'pending' ? 'text-yellow-700' : 'text-red-700'
                      }`}>
                        {designer.subscription_status === 'pending' ? 'اشتراكك قيد المراجعة' : 'اشتراكك منتهي الصلاحية'}
                      </h3>
                      
                      <p className="text-lg text-right leading-relaxed text-muted-foreground">
                        {designer.subscription_status === 'pending' 
                          ? 'سيتم مراجعة اشتراكك قريباً من قبل الإدارة وستتمكن من رفع الرشمات فور الموافقة عليه'
                          : 'يرجى تجديد اشتراكك لمواصلة رفع الرشمات والاستفادة من جميع المزايا المتاحة'
                        }
                      </p>
                      
                      {/* Action Button */}
                      {designer.subscription_status === 'expired' && (
                        <div className="pt-2">
                          <Link href="/designer/subscription-requests/create">
                            <Button className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-white shadow-lg hover:shadow-xl transition-all duration-300 px-6 py-3 text-lg font-bold rounded-xl">
                              تجديد الاشتراك الآن
                            </Button>
                          </Link>
                        </div>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          )}

          {/* Stats Grid */}
          <DesignerStatsCards stats={stats} />

          {/* Quick Actions */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
            <CardHeader className="relative pb-6 text-right">
              <CardTitle className="text-2xl font-bold text-foreground text-right">الإجراءات السريعة</CardTitle>
              <CardDescription className="text-lg text-muted-foreground text-right">
                وصول سريع لأهم الوظائف
              </CardDescription>
            </CardHeader>
            <CardContent className="relative">
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <Link href={route('designer.rachmat.create')}>
                  <Button className="w-full bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300 text-lg px-6 py-8 h-auto font-bold rounded-xl">
                    <Plus className="ml-3 h-6 w-6" />
                    إضافة رشمة جديدة
                  </Button>
                </Link>
                <Link href={route('designer.rachmat.index')}>
                  <Button variant="outline" className="w-full border-primary/20 text-primary hover:bg-primary/10 text-lg px-6 py-8 h-auto font-bold rounded-xl">
                    <Eye className="ml-3 h-6 w-6" />
                    عرض جميع الرشمات
                  </Button>
                </Link>
                <Link href={route('designer.orders.index')}>
                  <Button variant="outline" className="w-full border-primary/20 text-primary hover:bg-primary/10 text-lg px-6 py-8 h-auto font-bold rounded-xl">
                    <ClipboardList className="ml-3 h-6 w-6" />
                    طلباتي
                  </Button>
                </Link>
                <Link href="/designer/subscription-requests">
                  <Button variant="outline" className="w-full border-primary/20 text-primary hover:bg-primary/10 text-lg px-6 py-8 h-auto font-bold rounded-xl">
                    <ShoppingCart className="ml-3 h-6 w-6" />
                    طلبات الاشتراك
                  </Button>
                </Link>
              </div>
            </CardContent>
          </Card>

          {/* Recent Orders & Top Rachmat */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
            {/* Recent Orders */}
            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-blue-500/5"></div>
              <CardHeader className="relative pb-6 text-right">
                <CardTitle className="text-2xl font-bold text-foreground flex items-center gap-4 justify-end text-right">
                  <span>الطلبات الأخيرة</span>
                  <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <ShoppingCart className="w-6 h-6 text-white" />
                  </div>
                </CardTitle>
              </CardHeader>
              <CardContent className="relative">
                <div className="space-y-4">
                  {recentOrders.length > 0 ? (
                    recentOrders.slice(0, 5).map((order) => (
                      <div key={order.id} className="flex items-center justify-between p-4 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                        <div className="flex items-center gap-4 flex-1 min-w-0">
                          <div className="w-12 h-12 bg-gradient-to-br from-primary to-primary/70 rounded-xl flex items-center justify-center flex-shrink-0">
                            <User className="w-6 h-6 text-primary-foreground" />
                          </div>
                          <div className="flex-1 min-w-0">
                            <p className="font-bold text-right truncate">{order.client?.name || 'عميل'}</p>
                            <p className="text-sm text-muted-foreground text-right">
                              {new Date(order.created_at).toLocaleDateString('en-US', {year: 'numeric', month: '2-digit', day: '2-digit'})}
                            </p>
                          </div>
                        </div>
                        <div className="text-right flex-shrink-0 ml-4">
                          {getOrderStatusBadge(order.status)}
                          <p className="text-lg font-bold mt-1">{formatCurrency(order.amount)}</p>
                        </div>
                      </div>
                    ))
                  ) : (
                    <div className="text-center py-12">
                      <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                        <ShoppingCart className="w-8 h-8 text-muted-foreground" />
                      </div>
                      <p className="text-muted-foreground">لا توجد طلبات بعد</p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Top Rachmat */}
            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
              <div className="absolute inset-0 bg-gradient-to-br from-green-500/10 via-transparent to-green-500/5"></div>
              <CardHeader className="relative pb-6 text-right">
                <CardTitle className="text-2xl font-bold text-foreground flex items-center gap-4 justify-end text-right">
                  <span>أفضل الرشمات</span>
                  <div className="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <TrendingUp className="w-6 h-6 text-white" />
                  </div>
                </CardTitle>
              </CardHeader>
              <CardContent className="relative">
                <div className="space-y-4">
                  {topRachmat.length > 0 ? (
                    topRachmat.slice(0, 5).map((rachma, index) => (
                      <div key={rachma.id} className="flex items-center justify-between p-4 bg-gradient-to-r from-muted/30 to-transparent rounded-xl">
                        <div className="flex items-center gap-4 flex-1 min-w-0">
                          {/* Rachma Image */}
                          <div className="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0">
                            {rachma.preview_image_urls && rachma.preview_image_urls.length > 0 ? (
                              <LazyImage
                                src={rachma.preview_image_urls[0]}
                                alt={rachma.title}
                                className="w-full h-full object-cover"
                                aspectRatio="1:1"
                                priority={false}
                                showSkeleton={true}
                              />
                            ) : (
                              <div className="w-full h-full bg-gradient-to-br from-muted to-muted/70 flex items-center justify-center">
                                <Package className="w-6 h-6 text-muted-foreground" />
                              </div>
                            )}
                          </div>
                          <div className="w-8 h-8 bg-gradient-to-br from-primary to-primary/70 rounded-lg flex items-center justify-center font-bold text-primary-foreground flex-shrink-0">
                            {index + 1}
                          </div>
                          <div className="flex-1 min-w-0">
                            <p className="font-bold text-right truncate">{rachma.title}</p>
                            <p className="text-sm text-muted-foreground text-right truncate">{rachma.categories?.map(cat => cat.name).join(', ') || 'غير محدد'}</p>
                          </div>
                        </div>
                        <div className="text-right flex-shrink-0 ml-4">
                          <p className="text-lg font-bold text-green-600">{rachma.total_orders_count || 0} مبيعة</p>
                          <p className="text-sm text-muted-foreground">{formatCurrency(rachma.price)}</p>
                        </div>
                      </div>
                    ))
                  ) : (
                    <div className="text-center py-12">
                      <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                        <Package className="w-8 h-8 text-muted-foreground" />
                      </div>
                      <p className="text-muted-foreground">لا توجد رشمات بعد</p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
} 