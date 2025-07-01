import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { AdminStatsCards } from '@/components/admin/AdminStatsCards';
import { Order, Designer } from '@/types';
import {
  Users,
  UserCheck,
  Package,
  Clock,
  TrendingUp,
  TrendingDown,
  BarChart3,
  DollarSign,
  ShoppingBag,
  Star,
  ArrowUpRight,
  ArrowDownRight
} from 'lucide-react';

interface Stats {
  totalUsers: number;
  totalDesigners: number;
  activeDesigners: number;
  totalRachmat: number;
  activeRachmat: number;
  totalOrders: number;
  pendingOrders: number;
  currentMonthOrders: number;
  lastMonthOrders: number;
  currentMonthRevenue: number;
  lastMonthRevenue: number;
}

interface RevenueData {
  date: string;
  total: number;
}

interface TopDesigner {
  id: number;
  store_name: string;
  orders_count: number;
  user: {
    name: string;
  };
}

interface Props {
  stats: Stats;
  recentOrders: Order[];
  pendingSubscriptions: Designer[];
  revenueData: RevenueData[];
  topDesigners: TopDesigner[];
}

export default function Dashboard({
  stats,
  recentOrders,
  pendingSubscriptions,
  topDesigners,
}: Omit<Props, 'revenueData'>) {
  const orderGrowth = stats.lastMonthOrders > 0 
    ? ((stats.currentMonthOrders - stats.lastMonthOrders) / stats.lastMonthOrders * 100).toFixed(1)
    : '0';
  
  const revenueGrowth = stats.lastMonthRevenue > 0 
    ? ((stats.currentMonthRevenue - stats.lastMonthRevenue) / stats.lastMonthRevenue * 100).toFixed(1)
    : '0';

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'completed':
        return <Badge variant="default">مكتمل</Badge>;
      case 'pending':
        return <Badge variant="secondary">معلق</Badge>;
      case 'processing':
        return <Badge variant="outline">قيد المعالجة</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  const getSubscriptionBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge variant="default">نشط</Badge>;
      case 'pending':
        return <Badge variant="secondary">معلق</Badge>;
      case 'expired':
        return <Badge variant="destructive">منتهي</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  return (
    <AppLayout 
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' }
      ]}
    >
      <Head title="لوحة الإدارة - Dashboard" />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Modern Header */}
          <AdminPageHeader />

          {/* Revolutionary Stats Grid */}
          <AdminStatsCards stats={stats} />

          {/* Revolutionary Performance Cards */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {/* Orders Performance */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-2xl hover:shadow-3xl transition-all duration-700">
              <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
              <div className="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary/20 to-transparent rounded-full -translate-y-16 translate-x-16"></div>

              <CardHeader className="relative pb-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-gradient-to-br from-primary to-primary/80 rounded-xl flex items-center justify-center shadow-lg">
                    <ShoppingBag className="w-6 h-6 text-primary-foreground" />
                  </div>
                  <div>
                    <CardTitle className="text-2xl font-bold text-foreground">
                      أداء الطلبات الشهري
                    </CardTitle>
                    <CardDescription className="text-muted-foreground text-base">Monthly Orders Performance</CardDescription>
                  </div>
                </div>
              </CardHeader>

              <CardContent className="relative space-y-6">
                <div className="relative p-6 bg-gradient-to-r from-primary/10 to-primary/5 rounded-2xl border border-primary/20">
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">هذا الشهر</span>
                    <div className="text-right">
                      <div className="text-3xl font-black text-primary">{stats.currentMonthOrders.toLocaleString()}</div>
                      <div className="text-xs text-muted-foreground">This Month</div>
                    </div>
                  </div>
                </div>

                <div className="relative p-6 bg-muted/30 rounded-2xl">
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">الشهر الماضي</span>
                    <div className="text-right">
                      <div className="text-2xl font-bold text-foreground">{stats.lastMonthOrders.toLocaleString()}</div>
                      <div className="text-xs text-muted-foreground">Last Month</div>
                    </div>
                  </div>
                </div>

                <div className="relative p-6 bg-gradient-to-r from-background to-muted/20 rounded-2xl border-2 border-dashed border-primary/30">
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">النمو</span>
                    <div className="flex items-center gap-3">
                      <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${Number(orderGrowth) >= 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'}`}>
                        {Number(orderGrowth) >= 0 ? (
                          <ArrowUpRight className="w-5 h-5" />
                        ) : (
                          <ArrowDownRight className="w-5 h-5" />
                        )}
                      </div>
                      <div className="text-right">
                        <div className={`text-2xl font-black ${Number(orderGrowth) >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                          {Number(orderGrowth) >= 0 ? '+' : ''}{orderGrowth}%
                        </div>
                        <div className="text-xs text-muted-foreground">Growth</div>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Revenue Performance */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-2xl hover:shadow-3xl transition-all duration-700">
              <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-emerald-500/10"></div>
              <div className="absolute top-0 left-0 w-32 h-32 bg-gradient-to-br from-emerald-500/20 to-transparent rounded-full -translate-y-16 -translate-x-16"></div>

              <CardHeader className="relative pb-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                    <DollarSign className="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <CardTitle className="text-2xl font-bold text-foreground">
                      أداء الإيرادات الشهري
                    </CardTitle>
                    <CardDescription className="text-muted-foreground text-base">Monthly Revenue Performance</CardDescription>
                  </div>
                </div>
              </CardHeader>

              <CardContent className="relative space-y-6">
                <div className="relative p-6 bg-gradient-to-r from-emerald-500/10 to-emerald-500/5 rounded-2xl border border-emerald-500/20">
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">هذا الشهر</span>
                    <div className="text-right">
                      <div className="text-3xl font-black text-emerald-600">{stats.currentMonthRevenue.toLocaleString()} دج</div>
                      <div className="text-xs text-muted-foreground">This Month</div>
                    </div>
                  </div>
                </div>

                <div className="relative p-6 bg-muted/30 rounded-2xl">
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">الشهر الماضي</span>
                    <div className="text-right">
                      <div className="text-2xl font-bold text-foreground">{stats.lastMonthRevenue.toLocaleString()} دج</div>
                      <div className="text-xs text-muted-foreground">Last Month</div>
                    </div>
                  </div>
                </div>

                <div className="relative p-6 bg-gradient-to-r from-background to-muted/20 rounded-2xl border-2 border-dashed border-emerald-500/30">
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">النمو</span>
                    <div className="flex items-center gap-3">
                      <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${Number(revenueGrowth) >= 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'}`}>
                        {Number(revenueGrowth) >= 0 ? (
                          <ArrowUpRight className="w-5 h-5" />
                        ) : (
                          <ArrowDownRight className="w-5 h-5" />
                        )}
                      </div>
                      <div className="text-right">
                        <div className={`text-2xl font-black ${Number(revenueGrowth) >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                          {Number(revenueGrowth) >= 0 ? '+' : ''}{revenueGrowth}%
                        </div>
                        <div className="text-xs text-muted-foreground">Growth</div>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Modern Activity Sections */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {/* Recent Orders */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-xl hover:shadow-2xl transition-all duration-500">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-blue-500/10"></div>

              <CardHeader className="relative pb-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <ShoppingBag className="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <CardTitle className="text-2xl font-bold text-foreground">الطلبات الأخيرة</CardTitle>
                    <CardDescription className="text-muted-foreground text-base">Recent Orders</CardDescription>
                  </div>
                </div>
              </CardHeader>

              <CardContent className="relative">
                <div className="space-y-4">
                  {recentOrders.slice(0, 5).map((order, index) => (
                    <div key={order.id} className="group/item relative p-5 bg-gradient-to-r from-background to-muted/20 rounded-2xl border border-border/50 hover:border-primary/30 hover:shadow-lg transition-all duration-300">
                      <div className="absolute inset-0 bg-gradient-to-r from-primary/5 to-transparent rounded-2xl opacity-0 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                      <div className="relative flex items-center justify-between">
                        <div className="flex items-center gap-4">
                          <div className="w-10 h-10 bg-gradient-to-br from-primary/20 to-primary/10 rounded-xl flex items-center justify-center text-primary font-bold text-sm">
                            #{index + 1}
                          </div>
                          <div className="space-y-1">
                            <p className="font-bold text-foreground">#{order.id}</p>
                            <p className="text-sm text-muted-foreground">{order.client?.name}</p>
                            <p className="text-xs text-muted-foreground">{order.rachma?.title}</p>
                          </div>
                        </div>
                        <div className="text-right space-y-2">
                          <p className="font-black text-lg text-foreground">{order.amount.toLocaleString()} دج</p>
                          {getStatusBadge(order.status)}
                        </div>
                      </div>
                    </div>
                  ))}
                  <Link href="/admin/orders" preserveState={true}>
                    <Button variant="outline" className="w-full mt-6 h-12 bg-gradient-to-r from-background to-muted/20 border-2 border-dashed border-primary/30 hover:border-primary/50 hover:bg-primary/5 transition-all duration-300">
                      <span className="font-semibold">عرض جميع الطلبات</span>
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>

            {/* Pending Subscriptions */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-xl hover:shadow-2xl transition-all duration-500">
              <div className="absolute inset-0 bg-gradient-to-br from-amber-500/5 via-transparent to-amber-500/10"></div>

              <CardHeader className="relative pb-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Clock className="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <CardTitle className="text-2xl font-bold text-foreground">طلبات الاشتراك المعلقة</CardTitle>
                    <CardDescription className="text-muted-foreground text-base">Pending Subscriptions</CardDescription>
                  </div>
                </div>
              </CardHeader>

              <CardContent className="relative">
                <div className="space-y-4">
                  {pendingSubscriptions.slice(0, 5).map((designer, index) => (
                    <div key={designer.id} className="group/item relative p-5 bg-gradient-to-r from-background to-muted/20 rounded-2xl border border-border/50 hover:border-amber-500/30 hover:shadow-lg transition-all duration-300">
                      <div className="absolute inset-0 bg-gradient-to-r from-amber-500/5 to-transparent rounded-2xl opacity-0 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                      <div className="relative flex items-center justify-between">
                        <div className="flex items-center gap-4">
                          <div className="w-10 h-10 bg-gradient-to-br from-amber-500/20 to-amber-500/10 rounded-xl flex items-center justify-center text-amber-600 font-bold text-sm">
                            #{index + 1}
                          </div>
                          <div className="space-y-1">
                            <p className="font-bold text-foreground">{designer.store_name}</p>
                            <p className="text-sm text-muted-foreground">{designer.user?.name}</p>
                            <p className="text-xs text-muted-foreground">{designer.user?.email}</p>
                          </div>
                        </div>
                        <div className="text-right space-y-3">
                          {getSubscriptionBadge(designer.subscription_status)}
                          {/* <Button size="sm" className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300">
                            مراجعة
                          </Button> */}
                        </div>
                      </div>
                    </div>
                  ))}
                  <Link href="/admin/subscription-requests" preserveState={true}>
                    <Button variant="outline" className="w-full mt-6 h-12 bg-gradient-to-r from-background to-muted/20 border-2 border-dashed border-amber-500/30 hover:border-amber-500/50 hover:bg-amber-500/5 transition-all duration-300">
                      <span className="font-semibold">عرض جميع الطلبات</span>
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Top Designers Showcase */}
          <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-2xl hover:shadow-3xl transition-all duration-700">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
            <div className="absolute -top-20 -right-20 w-40 h-40 bg-gradient-to-br from-primary/20 to-transparent rounded-full"></div>
            <div className="absolute -bottom-20 -left-20 w-40 h-40 bg-gradient-to-br from-primary/20 to-transparent rounded-full"></div>

            <CardHeader className="relative pb-8">
              <div className="flex items-center gap-4">
                <div className="w-14 h-14 bg-gradient-to-br from-primary to-primary/80 rounded-2xl flex items-center justify-center shadow-xl">
                  <Star className="w-7 h-7 text-primary-foreground" />
                </div>
                <div>
                  <CardTitle className="text-3xl font-bold text-foreground">أفضل المصممين</CardTitle>
                  <CardDescription className="text-muted-foreground text-lg">Top Performing Designers</CardDescription>
                </div>
              </div>
            </CardHeader>

            <CardContent className="relative">
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {topDesigners.map((designer, index) => (
                  <div key={designer.id} className="group/designer relative p-6 bg-gradient-to-br from-background via-background to-muted/30 rounded-2xl border border-border/50 hover:border-primary/30 hover:shadow-xl transition-all duration-500 hover:-translate-y-1">
                    <div className="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent rounded-2xl opacity-0 group-hover/designer:opacity-100 transition-opacity duration-300"></div>

                    <div className="relative flex items-center gap-4">
                      <div className="relative">
                        <div className={`w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg ${
                          index === 0 ? 'bg-gradient-to-br from-yellow-400 to-yellow-500' :
                          index === 1 ? 'bg-gradient-to-br from-gray-400 to-gray-500' :
                          index === 2 ? 'bg-gradient-to-br from-amber-600 to-amber-700' :
                          'bg-gradient-to-br from-primary to-primary/80'
                        }`}>
                          <span className="text-lg font-black text-white">#{index + 1}</span>
                        </div>
                        {index < 3 && (
                          <div className="absolute -top-2 -right-2 w-6 h-6 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center">
                            <Star className="w-3 h-3 text-primary-foreground" />
                          </div>
                        )}
                      </div>

                      <div className="flex-1 space-y-2">
                        <p className="font-bold text-lg text-foreground group-hover/designer:text-primary transition-colors duration-300">
                          {designer.store_name}
                        </p>
                        <p className="text-sm text-muted-foreground">{designer.user.name}</p>
                        <div className="flex items-center gap-2">
                          <div className="px-3 py-1 bg-gradient-to-r from-primary/10 to-primary/5 rounded-full border border-primary/20">
                            <span className="text-sm font-bold text-primary">{designer.orders_count} مبيعة</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
} 