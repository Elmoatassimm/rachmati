import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { PrivacyPolicy } from '@/types';
import { Shield, Plus, Edit, Trash2, Eye, ToggleLeft, ToggleRight } from 'lucide-react';

interface Props {
    privacyPolicies: {
        data: PrivacyPolicy[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}

export default function Index({ privacyPolicies }: Props) {
    const { delete: destroy, post } = useForm();

    // Provide safe defaults to prevent undefined errors
    const safePolicies = privacyPolicies?.data || [];
    const safeMeta = privacyPolicies?.meta || { current_page: 1, last_page: 1, per_page: 10, total: 0 };
    const safeLinks = privacyPolicies?.links || [];

    const handleDelete = (id: number) => {
        if (confirm('هل أنت متأكد من حذف سياسة الخصوصية هذه؟')) {
            destroy(route('admin.privacy-policy.destroy', id));
        }
    };

    const handleToggleStatus = (id: number) => {
        post(route('admin.privacy-policy.toggle-status', id));
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const truncateContent = (content: string, maxLength: number = 100) => {
        if (content.length <= maxLength) return content;
        return content.substring(0, maxLength) + '...';
    };

    return (
        <AppLayout>
            <Head title="إدارة سياسة الخصوصية" />

            <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
                <div className="p-8 space-y-10">
                    {/* Header */}
                    <AdminPageHeader
                        title="إدارة سياسة الخصوصية"
                        subtitle="إدارة وتحديث سياسات الخصوصية للمنصة"
                    >
                        <Link href={route('admin.privacy-policy.create')}>
                            <Button className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-xl hover:shadow-2xl transition-all duration-300 text-lg px-8 py-6 h-auto font-bold">
                                <Plus className="w-6 h-6 mr-3" />
                                إضافة سياسة جديدة
                            </Button>
                        </Link>
                    </AdminPageHeader>

                    {/* Privacy Policies Grid */}
                    <div className="grid gap-6">
                        {safePolicies.length === 0 ? (
                            <Card className="border-dashed border-2 border-muted-foreground/25">
                                <CardContent className="flex flex-col items-center justify-center py-16">
                                    <Shield className="w-16 h-16 text-muted-foreground/50 mb-4" />
                                    <h3 className="text-xl font-semibold text-muted-foreground mb-2">
                                        لا توجد سياسات خصوصية
                                    </h3>
                                    <p className="text-muted-foreground text-center mb-6">
                                        لم يتم إنشاء أي سياسة خصوصية بعد. ابدأ بإضافة سياسة جديدة.
                                    </p>
                                    <Link href={route('admin.privacy-policy.create')}>
                                        <Button>
                                            <Plus className="w-4 h-4 mr-2" />
                                            إضافة سياسة جديدة
                                        </Button>
                                    </Link>
                                </CardContent>
                            </Card>
                        ) : (
                            safePolicies.map((policy) => (
                                <Card key={policy.id} className="hover:shadow-lg transition-shadow duration-300">
                                    <CardHeader className="pb-4">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <div className="flex items-center gap-3 mb-2">
                                                    <CardTitle className="text-xl">{policy.title}</CardTitle>
                                                    <Badge 
                                                        variant={policy.is_active ? "default" : "secondary"}
                                                        className={policy.is_active ? "bg-green-500 hover:bg-green-600" : ""}
                                                    >
                                                        {policy.is_active ? 'نشط' : 'غير نشط'}
                                                    </Badge>
                                                </div>
                                                <CardDescription className="text-sm text-muted-foreground">
                                                    تم الإنشاء: {formatDate(policy.created_at)}
                                                    {policy.updated_at !== policy.created_at && (
                                                        <span className="mr-4">
                                                            آخر تحديث: {formatDate(policy.updated_at)}
                                                        </span>
                                                    )}
                                                </CardDescription>
                                            </div>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="mb-4">
                                            <p className="text-sm text-muted-foreground leading-relaxed">
                                                {truncateContent(policy.content.replace(/<[^>]*>/g, ''))}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-2 pt-4 border-t">
                                            <Link href={route('admin.privacy-policy.show', policy.id)}>
                                                <Button variant="outline" size="sm">
                                                    <Eye className="w-4 h-4 mr-2" />
                                                    عرض
                                                </Button>
                                            </Link>
                                            <Link href={route('admin.privacy-policy.edit', policy.id)}>
                                                <Button variant="outline" size="sm">
                                                    <Edit className="w-4 h-4 mr-2" />
                                                    تعديل
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => handleToggleStatus(policy.id)}
                                            >
                                                {policy.is_active ? (
                                                    <>
                                                        <ToggleRight className="w-4 h-4 mr-2" />
                                                        إلغاء التفعيل
                                                    </>
                                                ) : (
                                                    <>
                                                        <ToggleLeft className="w-4 h-4 mr-2" />
                                                        تفعيل
                                                    </>
                                                )}
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                onClick={() => handleDelete(policy.id)}
                                            >
                                                <Trash2 className="w-4 h-4 mr-2" />
                                                حذف
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))
                        )}
                    </div>

                    {/* Pagination */}
                    {safeMeta.last_page > 1 && (
                        <div className="flex justify-center">
                            <div className="flex items-center gap-2">
                                {safeLinks.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`px-3 py-2 text-sm rounded-md transition-colors ${
                                            link.active
                                                ? 'bg-primary text-primary-foreground'
                                                : 'bg-background border hover:bg-muted'
                                        } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                        preserveState
                                        preserveScroll
                                    >
                                        <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                    </Link>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
