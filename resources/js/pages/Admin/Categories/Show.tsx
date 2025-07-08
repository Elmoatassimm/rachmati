import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Category } from '@/types';
import {
  ArrowLeft,
  FolderOpen,
  Edit,
  Trash2,
  Plus,
  Calendar,
  BarChart3,
  Layers,
  Eye,
  Settings
} from 'lucide-react';

interface Props {
  category: Category & {
    rachmat_count?: number;
  };
}

export default function Show({ category }: Props) {
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ar-DZ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const handleDelete = () => {
    if (confirm(`هل أنت متأكد من حذف التصنيف "${category.name}"؟ سيتم إلغاء ربط جميع الرشمات المرتبطة به.`)) {
      router.delete(`/admin/categories/${category.id}`);
    }
  };

  return (
    <AppLayout 
      breadcrumbs={[
        { title: 'لوحة الإدارة', href: '/admin/dashboard' },
        { title: 'التصنيفات', href: '/admin/categories' },
        { title: category.name, href: `/admin/categories/${category.id}` }
      ]}
    >
      <Head title={`${category.name} - Category Details`} />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-10">
          {/* Revolutionary Header */}
          <div className="relative">
            <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5 rounded-3xl"></div>
            <div className="relative p-8">
              <div className="flex justify-between items-start">
                <div className="flex items-center gap-6">
                  <div className="w-16 h-16 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center shadow-xl">
                    <FolderOpen className="w-8 h-8 text-primary-foreground" />
                  </div>
                  <div>
                    <h1 className="text-5xl font-black bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                      {category.name}
                    </h1>
                    <p className="text-xl text-muted-foreground mt-2">
                      تفاصيل التصنيف
                    </p>
                  </div>
                </div>
                <div className="flex gap-3">
                  <Link href={`/admin/categories/${category.id}/edit`}>
                    <Button className="h-12 px-6 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white shadow-lg hover:shadow-xl transition-all duration-300">
                      <Edit className="w-5 h-5 ml-2" />
                      تعديل التصنيف
                    </Button>
                  </Link>
                  <Button
                    onClick={handleDelete}
                    variant="outline"
                    className="h-12 px-6 border-2 border-red-500 text-red-600 hover:bg-red-50 hover:border-red-600 transition-all duration-300"
                  >
                    <Trash2 className="w-5 h-5 ml-2" />
                    حذف التصنيف
                  </Button>
                  <Link
                    href="/admin/categories"
                    className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-muted to-muted/80 hover:from-muted/80 hover:to-muted/60 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl"
                  >
                    <ArrowLeft className="w-5 h-5" />
                    <span className="font-semibold">العودة للتصنيفات</span>
                  </Link>
                </div>
              </div>
            </div>
          </div>

        {/* Category Information */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>معلومات التصنيف</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <label className="text-sm font-medium text-gray-500">اسم التصنيف</label>
                <p className="text-lg font-semibold">{category.name}</p>
              </div>
              
              {category.description && (
                <div>
                  <label className="text-sm font-medium text-gray-500">الوصف</label>
                  <p className="text-sm bg-gray-50 p-3 rounded">{category.description}</p>
                </div>
              )}
              
              <div>
                <label className="text-sm font-medium text-gray-500">تاريخ الإنشاء</label>
                <p>{formatDate(category.created_at)}</p>
              </div>
              
              <div>
                <label className="text-sm font-medium text-gray-500">آخر تحديث</label>
                <p>{formatDate(category.updated_at)}</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>إحصائيات التصنيف</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex justify-center">
                <div className="bg-blue-50 p-6 rounded-lg text-center min-w-[200px]">
                  <div className="text-3xl font-bold text-blue-600">{category.rachmat_count || 0}</div>
                  <p className="text-sm text-blue-700 mt-2">الرشمات المرتبطة</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>الإجراءات السريعة</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Link href={`/admin/categories/${category.id}/edit`}>
                <div className="p-4 border rounded-lg hover:bg-purple-50 transition-colors cursor-pointer">
                  <div className="flex items-center space-x-3 space-x-reverse">
                    <div className="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                      <svg className="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </div>
                    <div>
                      <h3 className="font-medium">تعديل التصنيف</h3>
                      <p className="text-sm text-gray-600">تحديث معلومات التصنيف</p>
                    </div>
                  </div>
                </div>
              </Link>

              <Link href="/admin/rachmat">
                <div className="p-4 border rounded-lg hover:bg-blue-50 transition-colors cursor-pointer">
                  <div className="flex items-center space-x-3 space-x-reverse">
                    <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                      <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                      </svg>
                    </div>
                    <div>
                      <h3 className="font-medium">إدارة الرشمات</h3>
                      <p className="text-sm text-gray-600">عرض وإدارة جميع الرشمات</p>
                    </div>
                  </div>
                </div>
              </Link>
            </div>
          </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}