import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PrivacyPolicy } from '@/types';
import { ArrowRight, Shield, Home } from 'lucide-react';

interface Props {
    privacyPolicy: PrivacyPolicy;
}

export default function PrivacyPolicyPage({ privacyPolicy }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    return (
        <>
            <Head title={`سياسة الخصوصية - ${privacyPolicy.title}`}>
                <meta name="description" content="سياسة الخصوصية لمنصة رشماتي - منصة الرشمات الرقمية الأولى في الجزائر" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            </Head>
            
            <div className="min-h-screen bg-background text-foreground" dir="rtl">
                {/* Header */}
                <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="container mx-auto flex h-16 items-center justify-between px-4">
                        <Link href="/" className="flex items-center gap-2">
                            <span className="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-purple-400 to-purple-700 shadow-md">
                                <img
                                    src="/logo.png"
                                    alt="رشماتي"
                                    className="h-6 w-6 object-contain"
                                    loading="lazy"
                                />
                            </span>
                            <span className="bg-gradient-to-br from-rose-200 to-rose-500 bg-clip-text text-xl font-semibold tracking-tight text-transparent">
                                رشماتي
                            </span>
                        </Link>
                        
                        <Link href="/">
                            <Button variant="outline">
                                <Home className="w-4 h-4 mr-2" />
                                العودة للرئيسية
                            </Button>
                        </Link>
                    </div>
                </header>

                {/* Main Content */}
                <main className="container mx-auto px-4 py-8">
                    <div className="max-w-4xl mx-auto space-y-8">
                        {/* Page Header */}
                        <div className="text-center space-y-4">
                            <div className="flex justify-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-primary/10 border border-primary/20">
                                    <Shield className="h-8 w-8 text-primary" />
                                </div>
                            </div>
                            <h1 className="text-4xl font-bold tracking-tight">
                                {privacyPolicy.title}
                            </h1>
                            <p className="text-muted-foreground">
                                آخر تحديث: {formatDate(privacyPolicy.updated_at)}
                            </p>
                        </div>

                        {/* Privacy Policy Content */}
                        <Card className="border-0 shadow-lg">
                            <CardHeader className="text-center pb-6">
                                <CardTitle className="text-2xl">محتوى سياسة الخصوصية</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div 
                                    className="prose prose-lg max-w-none text-right leading-relaxed space-y-6"
                                    style={{ direction: 'rtl' }}
                                    dangerouslySetInnerHTML={{ __html: privacyPolicy.content }}
                                />
                            </CardContent>
                        </Card>

                        {/* Contact Information */}
                        <Card className="bg-muted/50">
                            <CardContent className="pt-6">
                                <div className="text-center space-y-4">
                                    <h3 className="text-xl font-semibold">هل لديك أسئلة؟</h3>
                                    <p className="text-muted-foreground">
                                        إذا كان لديك أي أسئلة حول سياسة الخصوصية هذه، يرجى التواصل معنا
                                    </p>
                                    <div className="flex justify-center gap-4">
                                        <Link href="/">
                                            <Button variant="outline">
                                                <Home className="w-4 h-4 mr-2" />
                                                العودة للرئيسية
                                            </Button>
                                        </Link>
                                        <Link href="/register">
                                            <Button>
                                                انضم كمصمم
                                                <ArrowRight className="w-4 h-4 ml-2" />
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </main>

                {/* Footer */}
                <footer className="border-t bg-muted/30 mt-16">
                    <div className="container mx-auto px-4 py-8">
                        <div className="text-center space-y-4">
                            <Link href="/" className="inline-flex items-center gap-2">
                                <span className="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-purple-400 to-purple-700 shadow-md">
                                    <img
                                        src="/logo.png"
                                        alt="رشماتي"
                                        className="h-5 w-5 object-contain"
                                        loading="lazy"
                                    />
                                </span>
                                <span className="bg-gradient-to-br from-rose-200 to-rose-500 bg-clip-text text-lg font-semibold tracking-tight text-transparent">
                                    رشماتي
                                </span>
                            </Link>
                            <p className="text-sm text-muted-foreground">
                                منصة الرشمات الرقمية الأولى في الجزائر
                            </p>
                            <p className="text-xs text-muted-foreground">
                                &copy; 2025 منصة رشماتي. جميع الحقوق محفوظة.
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
