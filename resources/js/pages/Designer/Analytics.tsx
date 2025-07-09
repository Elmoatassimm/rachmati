import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  BarChart3,
  DollarSign,
  Package,
  ShoppingCart,
  Star,
  ArrowUpRight,
  ArrowDownRight,
  Eye,
  Layers
} from 'lucide-react';
import { Order, Rachma, OrderItem } from '@/types';

interface MonthlySales {
  month: string;
  sales: number;
  revenue: number;
}

interface Stats {
  totalRachmat: number;
  activeRachmat: number;
  totalSales: number;
  totalEarnings: number;
  unpaidEarnings: number;
  averageRating: number;
  currentMonthSales: number;
  lastMonthSales: number;
  currentMonthRevenue: number;
  lastMonthRevenue: number;
  salesGrowth: number;
  revenueGrowth: number;
}

interface Props {
  stats: Stats;
  monthlySales: MonthlySales[];
  recentOrders: Order[];
  topRachmat: (Rachma & { total_orders_count: number })[];
}

export default function Analytics({
  stats,
  monthlySales = [],
  recentOrders = [],
  topRachmat = []
}: Props) {

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ', {
      style: 'currency',
      currency: 'DZD',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  // Helper function to get rachmat names for both single and multi-item orders
  const getRachmatDisplay = (order: Order) => {
    // Single-item order (legacy)
    if (order.rachma) {
      return {
        title: order.rachma.title_ar || order.rachma.title_fr || 'رشمة غير محددة',
        subtitle: 'رشمة واحدة',
        isMultiItem: false
      };
    }

    // Multi-item order
    if (order.order_items && order.order_items.length > 0) {
      if (order.order_items.length === 1) {
        const item = order.order_items[0];
        return {
          title: item.rachma?.title_ar || item.rachma?.title_fr || 'رشمة غير محددة',
          subtitle: 'رشمة واحدة',
          isMultiItem: false
        };
      } else {
        // Multiple items - show first rachma name + count
        const firstItem = order.order_items[0];
        const firstName = firstItem.rachma?.title_ar || firstItem.rachma?.title_fr || 'رشمة';

        // Truncate long names for better display
        const truncatedName = firstName.length > 20 ? firstName.substring(0, 20) + '...' : firstName;

        return {
          title: `${truncatedName} + ${order.order_items.length - 1} أخرى`,
          subtitle: `${order.order_items.length} رشمات متعددة`,
          isMultiItem: true,
          itemsCount: order.order_items.length
        };
      }
    }

    // Fallback
    return {
      title: 'طلب غير محدد',
      subtitle: 'غير محدد',
      isMultiItem: false
    };
  };

  const getOrderStatusBadge = (status: string) => {
    switch (status) {
      case 'completed':
        return <Badge className="bg-green-100 text-green-800">مكتمل</Badge>;
      case 'pending':
        return <Badge className="bg-yellow-100 text-yellow-800">معلق</Badge>;
      case 'processing':
        return <Badge className="bg-blue-100 text-blue-800">قيد المعالجة</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => (
      <Star 
        key={i} 
        className={`w-3 h-3 ${i < rating ? 'text-yellow-400 fill-current' : 'text-gray-300'}`} 
      />
    ));
  };

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة المصمم', href: '/designer/dashboard' },
        { title: 'التحليلات والتقارير', href: '/designer/analytics' }
      ]}
    >
      <Head title="التحليلات والتقارير - Designer Analytics" />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/10">
        <div className="p-2 md:p-4 space-y-4">
          {/* Compact Header */}
          <div className="relative">
            <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5 rounded-2xl"></div>
            <div className="relative p-4">
              <div>
                <h1 className="text-3xl font-bold bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                  التحليلات والتقارير
                </h1>
                <p className="text-sm text-muted-foreground mt-2">
                  تحليل شامل لأداء متجرك ومبيعاتك
                </p>
              </div>
            </div>
          </div>

          {/* Compact Stats Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            {/* Total Sales Performance */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-lg hover:shadow-xl transition-all duration-300">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-blue-500/5"></div>
              <CardHeader className="relative pb-2">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-xs font-bold text-muted-foreground uppercase tracking-wider">أداء المبيعات</CardTitle>
                  <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <ShoppingCart className="w-4 h-4 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0 space-y-2">
                <div className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-blue-500 bg-clip-text text-transparent">
                  {stats.totalSales}
                </div>
                <div className="space-y-1">
                  <div className="flex justify-between text-xs">
                    <span className="text-muted-foreground">هذا الشهر:</span>
                    <span className="font-bold">{stats.currentMonthSales}</span>
                  </div>
                  <div className="flex items-center gap-1">
                    <div className={`w-4 h-4 rounded-md flex items-center justify-center ${stats.salesGrowth >= 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'}`}>
                      {stats.salesGrowth >= 0 ? (
                        <ArrowUpRight className="w-2 h-2" />
                      ) : (
                        <ArrowDownRight className="w-2 h-2" />
                      )}
                    </div>
                    <span className={`text-xs font-bold ${stats.salesGrowth >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                      {stats.salesGrowth >= 0 ? '+' : ''}{stats.salesGrowth}%
                    </span>
                  </div>
                </div>
                <div className="mt-2 h-0.5 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"></div>
              </CardContent>
            </Card>

            {/* Revenue Performance */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-lg hover:shadow-xl transition-all duration-300">
              <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-emerald-500/5"></div>
              <CardHeader className="relative pb-2">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-xs font-bold text-muted-foreground uppercase tracking-wider">الأرباح الشهرية</CardTitle>
                  <div className="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                    <DollarSign className="w-4 h-4 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0 space-y-2">
                <div className="text-xl font-bold bg-gradient-to-r from-emerald-600 to-emerald-500 bg-clip-text text-transparent">
                  {formatCurrency(stats.currentMonthRevenue)}
                </div>
                <div className="space-y-1">
                  <div className="flex justify-between text-xs">
                    <span className="text-muted-foreground">الشهر الماضي:</span>
                    <span className="font-bold text-xs">{formatCurrency(stats.lastMonthRevenue)}</span>
                  </div>
                  <div className="flex items-center gap-1">
                    <div className={`w-4 h-4 rounded-md flex items-center justify-center ${stats.revenueGrowth >= 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'}`}>
                      {stats.revenueGrowth >= 0 ? (
                        <ArrowUpRight className="w-2 h-2" />
                      ) : (
                        <ArrowDownRight className="w-2 h-2" />
                      )}
                    </div>
                    <span className={`text-xs font-bold ${stats.revenueGrowth >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                      {stats.revenueGrowth >= 0 ? '+' : ''}{stats.revenueGrowth}%
                    </span>
                  </div>
                </div>
                <div className="mt-2 h-0.5 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full"></div>
              </CardContent>
            </Card>

            {/* Average Rating */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-lg hover:shadow-xl transition-all duration-300">
              <div className="absolute inset-0 bg-gradient-to-br from-yellow-500/10 via-transparent to-yellow-500/5"></div>
              <CardHeader className="relative pb-2">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-xs font-bold text-muted-foreground uppercase tracking-wider">متوسط التقييم</CardTitle>
                  <div className="w-8 h-8 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Star className="w-4 h-4 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0 space-y-2">
                <div className="text-2xl font-bold bg-gradient-to-r from-yellow-600 to-yellow-500 bg-clip-text text-transparent">
                  {stats.averageRating && typeof stats.averageRating === 'number' ? stats.averageRating.toFixed(1) : '0.0'}
                </div>
                <div className="flex items-center gap-0.5">
                  {renderStars(Math.floor(stats.averageRating || 0))}
                </div>
                <div className="mt-2 h-0.5 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-full"></div>
              </CardContent>
            </Card>

            {/* Product Portfolio */}
            <Card className="group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-lg hover:shadow-xl transition-all duration-300">
              <div className="absolute inset-0 bg-gradient-to-br from-purple-500/10 via-transparent to-purple-500/5"></div>
              <CardHeader className="relative pb-2">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-xs font-bold text-muted-foreground uppercase tracking-wider">محفظة المنتجات</CardTitle>
                  <div className="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Package className="w-4 h-4 text-white" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="relative pt-0 space-y-2">
                <div className="text-2xl font-bold bg-gradient-to-r from-purple-600 to-purple-500 bg-clip-text text-transparent">
                  {stats.activeRachmat}
                </div>
                <div className="space-y-1">
                  <div className="flex justify-between text-xs">
                    <span className="text-muted-foreground">إجمالي الرشمات:</span>
                    <span className="font-bold">{stats.totalRachmat}</span>
                  </div>
                  <div className="text-xs text-muted-foreground">منتجات نشطة</div>
                </div>
                <div className="mt-2 h-0.5 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full"></div>
              </CardContent>
            </Card>
          </div>

          {/* Monthly Sales Chart */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-lg">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
            <CardHeader className="relative pb-3">
              <CardTitle className="text-lg font-bold text-foreground flex items-center gap-3">
                <div className="w-8 h-8 bg-gradient-to-br from-primary to-primary/70 rounded-xl flex items-center justify-center shadow-lg">
                  <BarChart3 className="w-4 h-4 text-primary-foreground" />
                </div>
                المبيعات الشهرية
              </CardTitle>
            </CardHeader>
            <CardContent className="relative">
              <div className="space-y-2">
                {monthlySales.map((month, index) => (
                  <div key={index} className="flex items-center justify-between p-3 bg-gradient-to-r from-muted/50 to-muted/20 rounded-lg">
                    <div>
                      <span className="text-sm font-semibold text-foreground">{month.month}</span>
                      <p className="text-xs text-muted-foreground">{formatCurrency(month.revenue)}</p>
                    </div>
                    <div className="text-lg font-bold text-primary">{month.sales}</div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Recent Orders Table */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/20 shadow-lg">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
            <CardHeader className="relative pb-3">
              <CardTitle className="text-lg font-bold text-foreground flex items-center gap-3">
                <div className="w-8 h-8 bg-gradient-to-br from-primary to-primary/70 rounded-xl flex items-center justify-center shadow-lg">
                  <Eye className="w-4 h-4 text-primary-foreground" />
                </div>
                الطلبات الأخيرة
              </CardTitle>
            </CardHeader>
            <CardContent className="relative">
              <div className="space-y-2">
                {recentOrders.map((order) => {
                  const rachmatDisplay = getRachmatDisplay(order);
                  return (
                    <div key={order.id} className="flex items-center justify-between p-3 bg-gradient-to-r from-muted/50 to-muted/20 rounded-lg">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 bg-gradient-to-br from-primary to-primary/70 rounded-full flex items-center justify-center text-white text-xs font-bold">
                          #{order.id}
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center gap-2">
                            <span className="text-sm font-semibold text-foreground truncate">{rachmatDisplay.title}</span>
                            {rachmatDisplay.isMultiItem && (
                              <div className="flex items-center gap-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                <Layers className="w-3 h-3" />
                                {rachmatDisplay.itemsCount}
                              </div>
                            )}
                          </div>
                          <p className="text-xs text-muted-foreground">{order.client?.name} • {rachmatDisplay.subtitle}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-3">
                        <div className="text-right">
                          <div className="text-sm font-bold text-foreground">{formatCurrency(order.amount)}</div>
                          <p className="text-xs text-muted-foreground">
                            {new Date(order.created_at).toLocaleDateString('ar-SA')}
                          </p>
                        </div>
                        {getOrderStatusBadge(order.status)}
                      </div>
                    </div>
                  );
                })}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
} 