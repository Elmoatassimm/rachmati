import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/app-layout';
import { DesignerPageHeader } from '@/components/designer/DesignerPageHeader';
import { ModernStatsCard } from '@/components/ui/modern-stats-card';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import LazyImage from '@/components/ui/lazy-image';
import { Rachma, Category } from '@/types';
import {
  Package,
  Plus,
  Eye,
  Edit,
  Filter,
  TrendingUp,
  DollarSign,
  ShoppingCart,
  Star,
  Calendar,
  MoreHorizontal,
  Search,
  Grid,
  List,
  ChevronLeft,
  ChevronRight,
  Users
} from 'lucide-react';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Stats {
  total: number;
  active: number;
  totalSales: number;
  totalEarnings: number;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Props {
  rachmat: {
    data: Rachma[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links?: PaginationLink[];
  };
  categories: Category[];
  filters: {
    search?: string;
    category?: string;
  };
  stats: Stats;
}

export default function Index({ rachmat, categories, filters, stats }: Props) {
  const [searchValue, setSearchValue] = useState(filters.search || '');
  const [categoryFilter, setCategoryFilter] = useState(filters.category || 'all');
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
console.log(rachmat);
  const handleFilter = () => {
    const params: Record<string, string> = {};

    // Only include search if it has a value
    if (searchValue && searchValue.trim()) {
      params.search = searchValue.trim();
    }

    // Only include category if it's not 'all'
    if (categoryFilter && categoryFilter !== 'all') {
      params.category = categoryFilter;
    }

    router.get(route('designer.rachmat.index'), params, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleReset = () => {
    setSearchValue('');
    setCategoryFilter('all');
    router.get(route('designer.rachmat.index'), {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handlePageChange = (page: number) => {
    const params: Record<string, string | number> = { page };

    // Only include search if it has a value
    if (searchValue && searchValue.trim()) {
      params.search = searchValue.trim();
    }

    // Only include category if it's not 'all'
    if (categoryFilter && categoryFilter !== 'all') {
      params.category = categoryFilter;
    }

    router.get(route('designer.rachmat.index'), params, {
      preserveState: true,
      preserveScroll: true,
    });
  };

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
      month: 'short',
      day: 'numeric'
    });
  };



  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة المصمم', href: route('designer.dashboard') },
        { title: 'رشماتي', href: route('designer.rachmat.index') }
      ]}
    >
      <Head title="رشماتي - My Rachmat" />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/10">
        <div className="p-4 md:p-8 space-y-8">
          {/* Enhanced Header */}
          <DesignerPageHeader
            title="رشماتي"
            subtitle="إدارة وعرض جميع رشماتك المرفوعة مع إحصائيات مفصلة"
            icon={Package}
          >
            <Link href={route('designer.rachmat.create')}>
              <Button size="lg" className="bg-gradient-to-r from-primary via-primary/90 to-primary/80 hover:from-primary/90 hover:via-primary/80 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300 group">
                <Plus className="ml-2 h-5 w-5 group-hover:scale-110 transition-transform" />
                إضافة رشمة جديدة
              </Button>
            </Link>
          </DesignerPageHeader>

          {/* Enhanced Stats Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            {/* Total Rachmat Card */}
            <ModernStatsCard
              title="إجمالي الرشمات"
              value={stats.total}
              subtitle="Total Rachmat"
              icon={Package}
              colorScheme="blue"
            />

            {/* Total Sales Card */}
            <ModernStatsCard
              title="إجمالي المبيعات"
              value={stats.totalSales}
              subtitle="Total Sales"
              icon={ShoppingCart}
              colorScheme="purple"
            />

            {/* Active Designs Card */}
            <ModernStatsCard
              title="الرشمات النشطة"
              value={stats.active}
              subtitle="Active Designs"
              icon={TrendingUp}
              colorScheme="green"
            />

            {/* Total Earnings Card */}
            <ModernStatsCard
              title="إجمالي الأرباح"
              value={formatPrice(stats.totalEarnings)}
              subtitle="Total Earnings"
              icon={DollarSign}
              colorScheme="orange"
            />
          </div>

          {/* Enhanced Filters & Actions */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/5"></div>
            <CardHeader className="relative text-right pb-4">
              <div className="flex items-center justify-between">
                <CardTitle className="text-xl font-bold text-foreground text-right flex items-center gap-3">
                  <Search className="h-5 w-5" />
                  البحث والفلترة
                </CardTitle>
                <div className="flex items-center gap-2">
                  <Button
                    variant={viewMode === 'grid' ? 'default' : 'outline'}
                    size="sm"
                    onClick={() => setViewMode('grid')}
                    className="h-8 w-8 p-0"
                  >
                    <Grid className="h-4 w-4" />
                  </Button>
                  <Button
                    variant={viewMode === 'list' ? 'default' : 'outline'}
                    size="sm"
                    onClick={() => setViewMode('list')}
                    className="h-8 w-8 p-0"
                  >
                    <List className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </CardHeader>
            <CardContent className="relative">
              <div className="flex flex-col lg:flex-row gap-4">
                <div className="flex-1 relative">
                  <Search className="absolute right-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="البحث في الرشمات..."
                    value={searchValue}
                    onChange={(e) => setSearchValue(e.target.value)}
                    className="text-right pr-10 h-12 border-border/50 focus:border-primary/50 bg-background/50"
                    onKeyDown={(e) => e.key === 'Enter' && handleFilter()}
                  />
                </div>

                <Select value={categoryFilter} onValueChange={setCategoryFilter}>
                  <SelectTrigger className="text-right lg:w-56 h-12 border-border/50 focus:border-primary/50 bg-background/50">
                    <SelectValue placeholder="اختر الفئة" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">جميع الفئات</SelectItem>
                    {categories.map((category) => (
                      <SelectItem key={category.id} value={category.id.toString()}>
                        {category.name_ar}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>

                <div className="flex gap-2">
                  <Button onClick={handleFilter} className="flex-1 lg:flex-none h-12 px-6">
                    <Filter className="ml-2 h-4 w-4" />
                    تطبيق الفلتر
                  </Button>
                  <Button variant="outline" onClick={handleReset} className="h-12">
                    إعادة تعيين
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Enhanced Rachmat List */}
          <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/5"></div>
            <CardHeader className="relative text-right">
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle className="text-2xl font-bold text-foreground text-right">قائمة الرشمات</CardTitle>
                  <CardDescription className="text-muted-foreground text-right mt-1">
                    عرض {rachmat.from} - {rachmat.to} من أصل {rachmat.total} رشمة
                  </CardDescription>
                </div>
                <Badge variant="secondary" className="text-sm">
                  صفحة {rachmat.current_page} من {rachmat.last_page}
                </Badge>
              </div>
            </CardHeader>
            <CardContent className="relative">
              {rachmat.data.length === 0 ? (
                <div className="text-center py-16">
                  <div className="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-muted to-muted/70 rounded-full flex items-center justify-center">
                    <Package className="h-12 w-12 text-muted-foreground" />
                  </div>
                  <h3 className="text-xl font-semibold text-foreground mb-3">لا توجد رشمات</h3>
                  <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                    لم تقم برفع أي رشمات بعد. ابدأ رحلتك في عالم التطريز برفع أول رشمة لك
                  </p>
                  <Link href={route('designer.rachmat.create')}>
                    <Button size="lg" className="bg-gradient-to-r from-primary to-primary/80">
                      <Plus className="ml-2 h-5 w-5" />
                      إضافة رشمة جديدة
                    </Button>
                  </Link>
                </div>
              ) : (
                <>
                  <div className={viewMode === 'grid' 
                    ? "grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4" 
                    : "space-y-3"
                  }>
                    {rachmat.data.map((rachma) => (
                      <Card key={rachma.id} className={`group hover:shadow-xl transition-all duration-500 border-0 bg-gradient-to-br from-card via-card to-muted/30 hover:from-card/95 hover:to-muted/20 hover:-translate-y-2 ${viewMode === 'list' ? 'flex overflow-hidden' : ''}`}>
                        <div className={`relative ${viewMode === 'list' ? 'w-32 flex-shrink-0' : ''}`}>
                          {/* Rachma Image */}
                          <div className={`overflow-hidden ${viewMode === 'list' ? 'h-full' : 'aspect-video rounded-t-lg'}`}>
                            {rachma.preview_image_urls && rachma.preview_image_urls.length > 0 ? (
                              <LazyImage
                                src={rachma.preview_image_urls[0]}
                                alt={rachma.title_ar || 'رشمة'}
                                className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                aspectRatio={viewMode === 'list' ? undefined : "16:9"}
                                priority={false}
                                showSkeleton={true}
                              />
                            ) : (
                              <div className="w-full h-full bg-gradient-to-br from-muted via-muted/80 to-muted/60 flex items-center justify-center">
                                <Package className="w-8 h-8 text-muted-foreground group-hover:scale-110 transition-transform duration-300" />
                              </div>
                            )}
                          </div>

                          {/* Actions Dropdown */}
                          <div className="absolute top-2 left-2">
                            <DropdownMenu>
                              <DropdownMenuTrigger asChild>
                                <Button variant="secondary" size="sm" className="h-7 w-7 p-0 backdrop-blur-sm bg-background/80 hover:bg-background/90">
                                  <MoreHorizontal className="h-3 w-3" />
                                </Button>
                              </DropdownMenuTrigger>
                              <DropdownMenuContent align="end">
                                <DropdownMenuItem asChild>
                                  <Link href={route('designer.rachmat.show', rachma.id)}>
                                    <Eye className="ml-2 h-4 w-4" />
                                    عرض التفاصيل
                                  </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem asChild>
                                  <Link href={route('designer.rachmat.edit', rachma.id)}>
                                    <Edit className="ml-2 h-4 w-4" />
                                    تعديل
                                  </Link>
                                </DropdownMenuItem>
                              </DropdownMenuContent>
                            </DropdownMenu>
                          </div>

                          {/* Status Badge */}
                          <div className="absolute top-2 right-2">
                            <Badge className="bg-green-500/20 text-green-700 dark:text-green-400 border-green-500/30 backdrop-blur-sm text-xs">
                              نشطة
                            </Badge>
                          </div>
                        </div>

                        <CardContent className={`${viewMode === 'list' ? 'flex-1 flex flex-col justify-between' : ''} p-3`}>
                          <div className="space-y-2">
                            {/* Title and Price */}
                            <div className="flex items-start justify-between gap-2">
                              <div className="flex-1 min-w-0">
                                <h3 className="font-semibold text-foreground text-right truncate text-sm">
                                  {rachma.title_ar}
                                </h3>
                                {rachma.title_fr && (
                                  <p className="text-xs text-muted-foreground text-right truncate">
                                    {rachma.title_fr}
                                  </p>
                                )}
                              </div>
                              <div className="text-right">
                                <div className="text-sm font-bold text-primary">
                                  {formatPrice(rachma.price)}
                                </div>
                              </div>
                            </div>

                            {/* Categories */}
                            {rachma.categories && rachma.categories.length > 0 && (
                              <div className="flex flex-wrap gap-1 justify-end">
                                {rachma.categories.slice(0, 2).map((category) => (
                                  <Badge key={category.id} variant="secondary" className="text-xs bg-primary/10 text-primary dark:text-primary border-primary/20 px-1 py-0">
                                    {category.name_ar}
                                  </Badge>
                                ))}
                                {rachma.categories.length > 2 && (
                                  <Badge variant="secondary" className="text-xs bg-muted/50 text-muted-foreground px-1 py-0">
                                    +{rachma.categories.length - 2}
                                  </Badge>
                                )}
                              </div>
                            )}

                            {/* Stats */}
                            <div className="grid grid-cols-2 gap-2 text-xs">
                              <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">المبيعات</span>
                                <div className="flex items-center gap-1 font-medium">
                                  <ShoppingCart className="h-3 w-3" />
                                  {rachma.orders_count || 0}
                                </div>
                              </div>
                              <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">التقييم</span>
                                <div className="flex items-center gap-1 font-medium text-yellow-600 dark:text-yellow-500">
                                  <Star className="h-3 w-3" />
                                  {rachma.ratings_avg_rating ? Number(rachma.ratings_avg_rating).toFixed(1) : '0.0'}
                                </div>
                              </div>
                            </div>

                            <div className="flex items-center justify-between text-xs text-muted-foreground pt-1">
                              <div className="flex items-center gap-1">
                                <Calendar className="h-3 w-3" />
                                {formatDate(rachma.created_at)}
                              </div>
                              <div className="flex items-center gap-1">
                                <Users className="h-3 w-3" />
                                المشاهدات
                              </div>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex gap-1 pt-1">
                              <Link href={route('designer.rachmat.show', rachma.id)} className="flex-1">
                                <Button variant="outline" size="sm" className="w-full h-7 text-xs group">
                                  <Eye className="ml-1 h-3 w-3 group-hover:scale-110 transition-transform" />
                                  عرض
                                </Button>
                              </Link>
                              <Link href={route('designer.rachmat.edit', rachma.id)} className="flex-1">
                                <Button size="sm" className="w-full h-7 text-xs group">
                                  <Edit className="ml-1 h-3 w-3 group-hover:scale-110 transition-transform" />
                                  تعديل
                                </Button>
                              </Link>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>

                  {/* Enhanced Pagination */}
                  {rachmat.last_page > 1 && (
                    <div className="flex items-center justify-between mt-8 pt-6 border-t border-border/50">
                      <div className="text-sm text-muted-foreground">
                        عرض {rachmat.from} - {rachmat.to} من أصل {rachmat.total} نتيجة
                      </div>

                      <div className="flex items-center gap-2">
                        {/* Use Laravel's pagination links */}
                        {rachmat.links && rachmat.links.map((link: PaginationLink, index: number) => {
                          if (link.label === '&laquo; Previous') {
                            return link.url ? (
                              <Button
                                key={`prev-${index}`}
                                variant="outline"
                                size="sm"
                                onClick={() => {
                                  if (link.url) {
                                    const url = new URL(link.url);
                                    const page = url.searchParams.get('page');
                                    if (page) handlePageChange(Number(page));
                                  }
                                }}
                              >
                                <ChevronRight className="h-4 w-4" />
                              </Button>
                            ) : null;
                          }

                          if (link.label === 'Next &raquo;') {
                            return link.url ? (
                              <Button
                                key={`next-${index}`}
                                variant="outline"
                                size="sm"
                                onClick={() => {
                                  if (link.url) {
                                    const url = new URL(link.url);
                                    const page = url.searchParams.get('page');
                                    if (page) handlePageChange(Number(page));
                                  }
                                }}
                              >
                                <ChevronLeft className="h-4 w-4" />
                              </Button>
                            ) : null;
                          }

                          if (link.label === '...') {
                            return (
                              <span key={`dots-${index}`} className="px-2 py-1 text-muted-foreground">
                                ...
                              </span>
                            );
                          }

                          // Page number buttons
                          const pageNum = Number(link.label);
                          if (!isNaN(pageNum)) {
                            return (
                              <Button
                                key={`page-${index}`}
                                variant={link.active ? "default" : "outline"}
                                size="sm"
                                className="w-8 h-8 p-0"
                                onClick={() => handlePageChange(pageNum)}
                                disabled={link.active}
                              >
                                {pageNum}
                              </Button>
                            );
                          }

                          return null;
                        })}
                      </div>
                    </div>
                  )}
                </>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
