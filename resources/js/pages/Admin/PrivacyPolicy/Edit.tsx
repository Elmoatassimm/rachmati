import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import InputError from '@/components/input-error';
import { PrivacyPolicy } from '@/types';
import { Shield, Save, ArrowRight } from 'lucide-react';

interface Props {
    privacyPolicy: PrivacyPolicy;
}

export default function Edit({ privacyPolicy }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        title: privacyPolicy.title,
        content: privacyPolicy.content,
        is_active: privacyPolicy.is_active,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.privacy-policy.update', privacyPolicy.id));
    };

    return (
        <AppLayout>
            <Head title={`تعديل سياسة الخصوصية: ${privacyPolicy.title}`} />

            <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
                <div className="p-8 space-y-10">
                    {/* Header */}
                    <AdminPageHeader
                        title="تعديل سياسة الخصوصية"
                        subtitle={`تعديل: ${privacyPolicy.title}`}
                    >
                        <Link href={route('admin.privacy-policy.index')}>
                            <Button variant="outline">
                                <ArrowRight className="w-4 h-4 mr-2" />
                                العودة للقائمة
                            </Button>
                        </Link>
                    </AdminPageHeader>

                    {/* Form */}
                    <Card className="max-w-4xl mx-auto">
                        <CardHeader>
                            <div className="flex items-center gap-3">
                                <Shield className="w-6 h-6 text-primary" />
                                <div>
                                    <CardTitle>تعديل معلومات سياسة الخصوصية</CardTitle>
                                    <CardDescription>
                                        قم بتحديث تفاصيل سياسة الخصوصية
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                {/* Title */}
                                <div className="space-y-2">
                                    <Label htmlFor="title">عنوان سياسة الخصوصية</Label>
                                    <Input
                                        id="title"
                                        type="text"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        placeholder="مثال: سياسة الخصوصية - رشماتي"
                                        className="text-right"
                                        required
                                    />
                                    <InputError message={errors.title} />
                                </div>

                                {/* Content */}
                                <div className="space-y-2">
                                    <Label htmlFor="content">محتوى سياسة الخصوصية</Label>
                                    <Textarea
                                        id="content"
                                        value={data.content}
                                        onChange={(e) => setData('content', e.target.value)}
                                        placeholder="اكتب محتوى سياسة الخصوصية هنا..."
                                        className="min-h-[400px] text-right leading-relaxed"
                                        required
                                    />
                                    <InputError message={errors.content} />
                                    <p className="text-sm text-muted-foreground">
                                        يمكنك استخدام HTML للتنسيق (مثل &lt;p&gt;، &lt;h2&gt;، &lt;ul&gt;، إلخ)
                                    </p>
                                </div>

                                {/* Active Status */}
                                <div className="flex items-center space-x-2 space-x-reverse">
                                    <Checkbox
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                                    />
                                    <Label htmlFor="is_active" className="text-sm font-medium">
                                        تفعيل سياسة الخصوصية
                                    </Label>
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    إذا تم تفعيل هذه السياسة، سيتم إلغاء تفعيل جميع السياسات الأخرى تلقائياً
                                </p>

                                {/* Submit Button */}
                                <div className="flex justify-end pt-6 border-t">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70"
                                    >
                                        <Save className="w-4 h-4 mr-2" />
                                        {processing ? 'جاري الحفظ...' : 'حفظ التغييرات'}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
