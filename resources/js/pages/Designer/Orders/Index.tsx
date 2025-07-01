import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { ModernPageHeader } from '@/components/ui/modern-page-header';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Pagination } from '@/components/ui/pagination';
import LazyImage from '@/components/ui/lazy-image';
import { Order } from '@/types';
import {
  ClipboardList,
  Search,
  Filter,
  Eye,
  User,
  Calendar,
  Package,
  TrendingUp,
  ShoppingCart,
  CheckCircle,
  Clock,
  AlertCircle
} from 'lucide-react';

interface Stats {
  total: number;
  completed: number;
  pending: number;
  processing: number;
}

interface Props {
  orders: {
    data: Order[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
  stats: Stats;
  filters: {
    status?: string;
    search?: string;
  };
}

export default function Index({ orders, stats, filters }: Props) {
  const [searchValue, setSearchValue] = useState(filters.search || '');
  const [statusFilter, setStatusFilter] = useState(filters.status || 'all');

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ', {
      style: 'currency',
      currency: 'DZD',
      minimumFractionDigits: 0,
    }).format(amount);
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

  const handleFilter = () => {
    router.get('/designer/orders', {
      search: searchValue,
      status: statusFilter === 'all' ? '' : statusFilter,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleReset = () => {
    setSearchValue('');
    setStatusFilter('all');
    router.get('/designer/orders', {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة المصمم', href: '/designer/dashboard' },
        { title: 'طلباتي', href: '/designer/orders' }
      ]}
    >
      <Head title="طلباتي - My Orders" />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Header */}
          <ModernPageHeader
            title="طلباتي"
            subtitle="عرض وإدارة جميع الطلبات على رشماتي"
            icon={ClipboardList}
          />

          {/* Stats Cards */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-blue-500/5 shadow-xl rounded-2xl">
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div className="text-right">
                    <p className="text-sm font-medium text-muted-foreground">إجمالي الطلبات</p>
                    <p className="text-3xl font-bold text-foreground">{stats.total}</p>
                  </div>
                  <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <ShoppingCart className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-green-500/5 shadow-xl rounded-2xl">
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div className="text-right">
                    <p className="text-sm font-medium text-muted-foreground">طلبات مكتملة</p>
                    <p className="text-3xl font-bold text-green-600">{stats.completed}</p>
                  </div>
                  <div className="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                    <CheckCircle className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-yellow-500/5 shadow-xl rounded-2xl">
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div className="text-right">
                    <p className="text-sm font-medium text-muted-foreground">طلبات معلقة</p>
                    <p className="text-3xl font-bold text-yellow-600">{stats.pending}</p>
                  </div>
                  <div className="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center">
                    <Clock className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-purple-500/5 shadow-xl rounded-2xl">
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div className="text-right">
                    <p className="text-sm font-medium text-muted-foreground">قيد المعالجة</p>
                    <p className="text-3xl font-bold text-purple-600">{stats.processing}</p>
                  </div>
                  <div className="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <AlertCircle className="w-6 h-6 text-white" />
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Filters */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
            <CardHeader className="text-right">
              <CardTitle className="text-xl font-bold text-foreground text-right">البحث والتصفية</CardTitle>
              <CardDescription className="text-muted-foreground text-right">
                ابحث وصفي الطلبات حسب معايير مختلفة
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                
                <Select value={statusFilter} onValueChange={setStatusFilter}>
                  <SelectTrigger className="text-center">
                    <SelectValue placeholder="حالة الطلب" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem className="text-center" value="completed">مكتمل</SelectItem>
                    <SelectItem className="text-center" value="pending">معلق</SelectItem>
                    
                  </SelectContent>
                </Select>

                <div className="flex gap-2">
                  <Button onClick={handleFilter} className="flex-1">
                    <Filter className="ml-2 h-4 w-4" />
                    تطبيق الفلتر
                  </Button>
                  <Button variant="outline" onClick={handleReset}>
                    إعادة تعيين
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Orders List */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
            <CardHeader className="text-right">
              <CardTitle className="text-2xl font-bold text-foreground text-right">قائمة الطلبات</CardTitle>
              <CardDescription className="text-muted-foreground text-right">
                عرض {orders.from} - {orders.to} من أصل {orders.total} طلب
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {orders.data.length > 0 ? (
                  orders.data.map((order) => (
                    <div key={order.id} className="flex items-center justify-between p-6 bg-gradient-to-r from-muted/30 to-transparent rounded-xl border border-border/50 hover:border-border transition-colors">
                      <div className="flex items-center gap-4 flex-1 min-w-0">
                        {/* Rachma Image */}
                        <div className="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0">
                          {order.rachma?.preview_image_urls && order.rachma.preview_image_urls.length > 0 ? (
                            <LazyImage
                              src={order.rachma.preview_image_urls[0]}
                              alt={order.rachma.title}
                              className="w-full h-full object-cover"
                              aspectRatio="1:1"
                              priority={false}
                              showSkeleton={true}
                            />
                          ) : (
                            <div className="w-full h-full bg-gradient-to-br from-muted to-muted/70 flex items-center justify-center">
                              <Package className="w-8 h-8 text-muted-foreground" />
                            </div>
                          )}
                        </div>

                        {/* Order Info */}
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center gap-2 mb-2">
                            <span className="text-sm text-muted-foreground">#{order.id}</span>
                            {getOrderStatusBadge(order.status)}
                          </div>
                          <h3 className="font-bold text-lg text-right truncate">{order.rachma?.title}</h3>
                          <div className="flex items-center gap-4 text-sm text-muted-foreground text-right mt-1">
                            <span className="flex items-center gap-1">
                              <User className="w-4 h-4" />
                              {order.client?.name || 'عميل'}
                            </span>
                            <span className="flex items-center gap-1">
                              <Calendar className="w-4 h-4" />
                              {new Date(order.created_at).toLocaleDateString('en-US', {year: 'numeric', month: '2-digit', day: '2-digit'})}
                            </span>
                          </div>
                        </div>
                      </div>

                      {/* Order Actions */}
                      <div className="text-right flex-shrink-0 ml-6">
                        <p className="text-2xl font-bold text-green-600 mb-2">{formatCurrency(order.amount)}</p>
                        <Link href={`/designer/orders/${order.id}`}>
                          <Button variant="outline" size="sm">
                            <Eye className="ml-2 h-4 w-4" />
                            عرض التفاصيل
                          </Button>
                        </Link>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-16">
                    <div className="w-24 h-24 bg-muted rounded-full flex items-center justify-center mx-auto mb-6">
                      <ClipboardList className="w-12 h-12 text-muted-foreground" />
                    </div>
                    <h3 className="text-xl font-bold text-foreground mb-2">لا توجد طلبات</h3>
                    <p className="text-muted-foreground mb-6">لم يتم العثور على أي طلبات مطابقة للمعايير المحددة</p>
                    {filters.search || filters.status ? (
                      <Button variant="outline" onClick={handleReset}>
                        إعادة تعيين الفلتر
                      </Button>
                    ) : null}
                  </div>
                )}
              </div>

              {/* Pagination */}
              {orders.last_page > 1 && (
                <div className="mt-8 flex justify-center">
                  <Pagination
                    currentPage={orders.current_page}
                    totalPages={orders.last_page}
                    onPageChange={(page) => {
                      router.get('/designer/orders', {
                        ...filters,
                        page
                      }, {
                        preserveState: true,
                        preserveScroll: true,
                      });
                    }}
                  />
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
} 