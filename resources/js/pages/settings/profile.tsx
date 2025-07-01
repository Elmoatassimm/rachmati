import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { CheckCircle, AlertTriangle } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'إعدادات الملف الشخصي',
        href: '/settings/profile',
    },
];

type ProfileForm = {
    name: string;
    email: string;
};

export default function Profile({ mustVerifyEmail, status }: { mustVerifyEmail: boolean; status?: string }) {
    const { auth } = usePage<SharedData>().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm<Required<ProfileForm>>({
        name: auth.user.name,
        email: auth.user.email,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="إعدادات الملف الشخصي" />

            <SettingsLayout>
                <div className="space-y-6">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">معلومات الملف الشخصي</h2>
                        <p className="text-muted-foreground">تحديث اسمك وعنوان بريدك الإلكتروني</p>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>معلومات الحساب</CardTitle>
                            <CardDescription>
                                قم بتحديث معلومات حسابك الأساسية
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="name">الاسم</Label>
                                        <Input
                                            id="name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            required
                                            autoComplete="name"
                                            placeholder="الاسم الكامل"
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="email">عنوان البريد الإلكتروني</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            required
                                            autoComplete="username"
                                            placeholder="عنوان البريد الإلكتروني"
                                        />
                                        <InputError message={errors.email} />
                                    </div>
                                </div>

                                {mustVerifyEmail && auth.user.email_verified_at === null && (
                                    <Alert>
                                        <AlertTriangle className="h-4 w-4" />
                                        <AlertDescription>
                                            عنوان بريدك الإلكتروني غير مؤكد.{' '}
                                            <Link
                                                href={route('verification.send')}
                                                method="post"
                                                as="button"
                                                className="text-primary underline hover:no-underline"
                                            >
                                                انقر هنا لإعادة إرسال رسالة التأكيد.
                                            </Link>
                                        </AlertDescription>
                                    </Alert>
                                )}

                                {status === 'verification-link-sent' && (
                                    <Alert>
                                        <CheckCircle className="h-4 w-4" />
                                        <AlertDescription>
                                            تم إرسال رابط تأكيد جديد إلى عنوان بريدك الإلكتروني.
                                        </AlertDescription>
                                    </Alert>
                                )}

                                <div className="flex items-center justify-between">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'جاري الحفظ...' : 'حفظ التغييرات'}
                                    </Button>
                                    
                                    {recentlySuccessful && (
                                        <div className="flex items-center gap-2 text-sm text-green-600">
                                            <CheckCircle className="h-4 w-4" />
                                            تم الحفظ بنجاح
                                        </div>
                                    )}
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    <Separator />

                    <DeleteUser />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
