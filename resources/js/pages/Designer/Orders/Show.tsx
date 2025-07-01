import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/app-layout';
import { ModernPageHeader } from '@/components/ui/modern-page-header';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import LazyImage from '@/components/ui/lazy-image';
import { Order } from '@/types';
import {
  ArrowLeft,
  Calendar,
  User,
  Package,
  CreditCard,
  MapPin,
  Phone,
  Mail,
  Eye,
  Download,
  ShoppingCart,
  CheckCircle,
  Clock,
  AlertCircle,
  MessageSquare
} from 'lucide-react';

interface Props {
  order: Order;
}

export default function Show({ order }: Props) {
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
          <Badge className="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 text-base font-bold border-0">
            <CheckCircle className="ml-2 h-4 w-4" />
            مكتمل
          </Badge>
        );
      case 'pending':
        return (
          <Badge className="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-4 py-2 text-base font-bold border-0">
            <Clock className="ml-2 h-4 w-4" />
            معلق
          </Badge>
        );
      case 'processing':
        return (
          <Badge className="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 text-base font-bold border-0">
            <AlertCircle className="ml-2 h-4 w-4" />
            قيد المعالجة
          </Badge>
        );
      default:
        return (
          <Badge variant="secondary" className="px-4 py-2 text-base font-bold">
            {status}
          </Badge>
        );
    }
  };

  return (
    <AppLayout
      breadcrumbs={[
        { title: 'لوحة المصمم', href: '/designer/dashboard' },
        { title: 'طلباتي', href: '/designer/orders' },
        { title: `طلب #${order.id}`, href: `/designer/orders/${order.id}` }
      ]}
    >
      <Head title={`طلب #${order.id} - Order Details`} />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Header */}
            <ModernPageHeader
              title={`طلب #${order.id}`}
              subtitle={`تفاصيل الطلب من ${order.client?.name || 'عميل'}`}
              icon={ShoppingCart}
            >
              <div className="flex items-center gap-4">
              {getOrderStatusBadge(order.status)}
              <Link href={route('designer.orders.index')}>
                <Button variant="outline">
                  <ArrowLeft className="ml-2 h-4 w-4" />
                  العودة للطلبات
                </Button>
              </Link>
            </div>
            </ModernPageHeader>
            
            
          

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Main Content */}
            <div className="lg:col-span-2 space-y-8">
              {/* Rachma Details */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
                <CardHeader className="text-right">
                  <CardTitle className="text-2xl font-bold text-foreground text-right">تفاصيل الرشمة</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="flex items-start gap-6">
                    {/* Rachma Images */}
                    <div className="flex-shrink-0">
                      <div className="grid grid-cols-2 gap-2 w-32">
                        {order.rachma?.preview_image_urls && order.rachma.preview_image_urls.length > 0 ? (
                          order.rachma.preview_image_urls.slice(0, 4).map((imageUrl, index) => (
                            <div key={index} className="w-14 h-14 rounded-lg overflow-hidden">
                              <LazyImage
                                src={imageUrl}
                                alt={`${order.rachma.title} - Preview ${index + 1}`}
                                className="w-full h-full object-cover"
                                aspectRatio="1:1"
                                priority={false}
                                showSkeleton={true}
                              />
                            </div>
                          ))
                        ) : (
                          <div className="col-span-2 w-full h-28 bg-gradient-to-br from-muted to-muted/70 rounded-lg flex items-center justify-center">
                            <Package className="w-8 h-8 text-muted-foreground" />
                          </div>
                        )}
                      </div>
                    </div>

                    {/* Rachma Info */}
                    <div className="flex-1 text-right">
                      <h3 className="text-2xl font-bold text-foreground mb-3">{order.rachma?.title}</h3>
                      <p className="text-muted-foreground mb-4 leading-relaxed">{order.rachma?.description}</p>
                      
                      {order.rachma?.categories && order.rachma.categories.length > 0 && (
                        <div className="mb-4">
                          <p className="text-sm font-medium text-muted-foreground mb-2">التصنيفات:</p>
                          <div className="flex flex-wrap gap-2 justify-end">
                            {order.rachma.categories.map((category) => (
                              <Badge key={category.id} variant="secondary" className="text-sm">
                                {category.name}
                              </Badge>
                            ))}
                          </div>
                        </div>
                      )}

                      {order.rachma?.parts && order.rachma.parts.length > 0 && (
                        <div>
                          <p className="text-sm font-medium text-muted-foreground mb-2">القطع المطلوبة:</p>
                          <div className="flex flex-wrap gap-2 justify-end">
                            {order.rachma.parts.map((part) => (
                              <Badge key={part.id} variant="outline" className="text-sm">
                                {part.name}
                              </Badge>
                            ))}
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Order Timeline */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
                <CardHeader className="text-right">
                  <CardTitle className="text-2xl font-bold text-foreground text-right">سجل الطلب</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-blue-500/10 to-transparent rounded-lg">
                      <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <ShoppingCart className="w-5 h-5 text-white" />
                      </div>
                      <div className="flex-1 text-right">
                        <p className="font-bold">تم إنشاء الطلب</p>
                        <p className="text-sm text-muted-foreground">
                          {new Date(order.created_at).toLocaleString('ar-DZ', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                          })}
                        </p>
                      </div>
                    </div>

                    {order.status === 'processing' && (
                      <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-yellow-500/10 to-transparent rounded-lg">
                        <div className="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full flex items-center justify-center flex-shrink-0">
                          <Clock className="w-5 h-5 text-white" />
                        </div>
                        <div className="flex-1 text-right">
                          <p className="font-bold">قيد المعالجة</p>
                          <p className="text-sm text-muted-foreground">الطلب قيد المراجعة والمعالجة</p>
                        </div>
                      </div>
                    )}

                    {order.status === 'completed' && (
                      <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-green-500/10 to-transparent rounded-lg">
                        <div className="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                          <CheckCircle className="w-5 h-5 text-white" />
                        </div>
                        <div className="flex-1 text-right">
                          <p className="font-bold">تم إكمال الطلب</p>
                          <p className="text-sm text-muted-foreground">تم تسليم الملفات للعميل بنجاح</p>
                        </div>
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Sidebar */}
            <div className="space-y-8">
              {/* Order Summary */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-green-500/5 shadow-xl rounded-2xl">
                <CardHeader className="text-right">
                  <CardTitle className="text-xl font-bold text-foreground text-right">ملخص الطلب</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex justify-between items-center">
                    <span className="text-3xl font-bold text-green-600">{formatCurrency(order.amount)}</span>
                    <span className="text-muted-foreground">المبلغ الإجمالي</span>
                  </div>
                  
                  <Separator />
                  
                  <div className="space-y-3 text-right">
                    <div className="flex justify-between items-center">
                      <span className="text-muted-foreground">رقم الطلب</span>
                      <span className="font-bold">#{order.id}</span>
                    </div>
                    
                    <div className="flex justify-between items-center">
                      <span className="text-muted-foreground">تاريخ الطلب</span>
                      <span className="font-medium">
                        {new Date(order.created_at).toLocaleDateString('en-US', {
                          year: 'numeric',
                          month: '2-digit',
                          day: '2-digit'
                        })}
                      </span>
                    </div>
                    
                    <div className="flex justify-between items-center">
                      <span className="text-muted-foreground">الحالة</span>
                      {getOrderStatusBadge(order.status)}
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Client Information */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-blue-500/5 shadow-xl rounded-2xl">
                <CardHeader className="text-right">
                  <CardTitle className="text-xl font-bold text-foreground text-right">معلومات العميل</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex items-center gap-3 text-right">
                    <div className="flex-1 text-right">
                      <p className="font-bold text-lg">{order.client?.name || 'عميل'}</p>
                      <p className="text-sm text-muted-foreground">العميل</p>
                    </div>
                    <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                      <User className="w-6 h-6 text-white" />
                    </div>
                  </div>

                  <Separator />

                  <div className="space-y-3">
                    {order.client?.email && (
                      <div className="flex items-center gap-3 text-right">
                        <span className="text-sm text-muted-foreground flex-1">{order.client.email}</span>
                        <Mail className="w-4 h-4 text-muted-foreground flex-shrink-0" />
                      </div>
                    )}

                    {order.client?.phone && (
                      <div className="flex items-center gap-3 text-right">
                        <span className="text-sm text-muted-foreground flex-1">{order.client.phone}</span>
                        <Phone className="w-4 h-4 text-muted-foreground flex-shrink-0" />
                      </div>
                    )}

                    {order.client?.city && (
                      <div className="flex items-center gap-3 text-right">
                        <span className="text-sm text-muted-foreground flex-1">{order.client.city}</span>
                        <MapPin className="w-4 h-4 text-muted-foreground flex-shrink-0" />
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>

              {/* Actions */}
              <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl rounded-2xl">
                <CardHeader className="text-right">
                  <CardTitle className="text-xl font-bold text-foreground text-right">الإجراءات</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <Link href={route('designer.rachmat.show', order.rachma?.id)}>
                    <Button className="w-full" variant="outline">
                      <Eye className="ml-2 h-4 w-4" />
                      عرض الرشمة
                    </Button>
                  </Link>
                  
                  {order.status === 'completed' && (
                    <Button className="w-full" variant="outline">
                      <Download className="ml-2 h-4 w-4" />
                      تحميل الملفات
                    </Button>
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