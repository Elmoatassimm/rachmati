import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { PrivacyPolicy } from '@/types';
import { Shield, Edit, ArrowRight, Calendar, Clock } from 'lucide-react';

interface Props {
    privacyPolicy: PrivacyPolicy;
}

export default function Show({ privacyPolicy }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <AppLayout>
            <Head title={`عرض سياسة الخصوصية: ${privacyPolicy.title}`} />

            <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
                <div className="p-8 space-y-10">
                    {/* Header */}
                    <AdminPageHeader
                        title="عرض سياسة الخصوصية"
                        subtitle={privacyPolicy.title}
                    >
                        <div className="flex gap-3">
                            <Link href={route('admin.privacy-policy.edit', privacyPolicy.id)}>
                                <Button>
                                    <Edit className="w-4 h-4 mr-2" />
                                    تعديل
                                </Button>
                            </Link>
                            <Link href={route('admin.privacy-policy.index')}>
                                <Button variant="outline">
                                    <ArrowRight className="w-4 h-4 mr-2" />
                                    العودة للقائمة
                                </Button>
                            </Link>
                        </div>
                    </AdminPageHeader>

                    {/* Privacy Policy Details */}
                    <div className="max-w-4xl mx-auto space-y-6">
                        {/* Meta Information */}
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <Shield className="w-6 h-6 text-primary" />
                                        <div>
                                            <CardTitle>{privacyPolicy.title}</CardTitle>
                                            <CardDescription>معلومات سياسة الخصوصية</CardDescription>
                                        </div>
                                    </div>
                                    <Badge 
                                        variant={privacyPolicy.is_active ? "default" : "secondary"}
                                        className={privacyPolicy.is_active ? "bg-green-500 hover:bg-green-600" : ""}
                                    >
                                        {privacyPolicy.is_active ? 'نشط' : 'غير نشط'}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Calendar className="w-4 h-4" />
                                        <span>تاريخ الإنشاء: {formatDate(privacyPolicy.created_at)}</span>
                                    </div>
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Clock className="w-4 h-4" />
                                        <span>آخر تحديث: {formatDate(privacyPolicy.updated_at)}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Content */}
                        <Card>
                            <CardHeader>
                                <CardTitle>محتوى سياسة الخصوصية</CardTitle>
                                <CardDescription>
                                    المحتوى الكامل لسياسة الخصوصية كما سيظهر للمستخدمين
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div 
                                    className="prose prose-sm max-w-none text-right leading-relaxed"
                                    style={{ direction: 'rtl' }}
                                    dangerouslySetInnerHTML={{ __html: privacyPolicy.content }}
                                />
                            </CardContent>
                        </Card>

                        {/* Preview Link */}
                        {privacyPolicy.is_active && (
                            <Card className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950">
                                <CardContent className="pt-6">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h3 className="font-semibold text-green-800 dark:text-green-200">
                                                سياسة الخصوصية النشطة
                                            </h3>
                                            <p className="text-sm text-green-600 dark:text-green-300">
                                                هذه السياسة نشطة حالياً ويمكن للمستخدمين الوصول إليها
                                            </p>
                                        </div>
                                        <Link href={route('privacy-policy.show')} target="_blank">
                                            <Button variant="outline" size="sm">
                                                عرض في الموقع
                                            </Button>
                                        </Link>
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
