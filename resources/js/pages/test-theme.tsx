import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import AppearanceToggleDropdown from '@/components/appearance-dropdown';
import AppearanceToggleTab from '@/components/appearance-tabs';

export default function TestTheme() {
    return (
        <>
            <Head title="اختبار الخطوط والمظهر - Font & Theme Test" />
            
            <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
                {/* Header with Theme Toggle */}
                <header className="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center py-6">
                            <div className="flex items-center space-x-4">
                                <h1 className="text-3xl font-bold text-primary font-arabic">اختبار الخطوط</h1>
                                <Badge variant="secondary">Font Test</Badge>
                            </div>
                            <div className="flex items-center gap-4">
                                <AppearanceToggleTab />
                                <AppearanceToggleDropdown />
                            </div>
                        </div>
                    </div>
                </header>

                {/* Arabic Typography Showcase */}
                <section className="py-12">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            
                            {/* Sans-serif Font Test */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="font-arabic">خط IBM Plex Sans Arabic & Cairo</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="font-arabic leading-arabic tracking-arabic">
                                        <h2 className="text-2xl font-bold mb-3">العنوان الرئيسي</h2>
                                        <h3 className="text-xl font-semibold mb-3">العنوان الثانوي</h3>
                                        <p className="text-base text-muted-foreground mb-4">
                                            هذا نص تجريبي لاختبار جودة خط IBM Plex Sans Arabic مع Cairo كخط احتياطي. 
                                            يتميز هذا الخط بوضوحه وجماليته في عرض النصوص العربية على الشاشات الرقمية.
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            نص صغير: الجودة والوضوح في الأحجام المختلفة ١٢٣٤٥٦٧٨٩٠
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Serif Font Test */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="font-arabic-serif">خط Amiri (التراثي)</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="font-arabic-serif leading-arabic tracking-arabic">
                                        <h2 className="text-2xl font-bold mb-3">النص التراثي الجميل</h2>
                                        <h3 className="text-xl font-semibold mb-3">الخط العربي الأصيل</h3>
                                        <p className="text-base text-muted-foreground mb-4">
                                            خط أميري هو خط عربي تراثي يحاكي خط النسخ التقليدي. 
                                            يتميز بجماليته وأناقته ووضوحه، ويناسب النصوص الطويلة والعناوين الرئيسية.
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            مثال على النص الصغير بالخط التراثي ١٢٣٤٥٦٧٨٩٠
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <Separator className="my-8" />

                        {/* Theme Colors Demonstration */}
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="font-arabic">الألوان الأساسية</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div className="p-3 bg-primary text-primary-foreground rounded font-arabic">
                                        اللون الأساسي
                                    </div>
                                    <div className="p-3 bg-secondary text-secondary-foreground rounded font-arabic">
                                        اللون الثانوي
                                    </div>
                                    <div className="p-3 bg-muted text-muted-foreground rounded font-arabic">
                                        اللون المكتوم
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle className="font-arabic">الألوان التفاعلية</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div className="p-3 bg-accent text-accent-foreground rounded font-arabic">
                                        لون التمييز
                                    </div>
                                    <div className="p-3 bg-destructive text-destructive-foreground rounded font-arabic">
                                        لون التحذير
                                    </div>
                                    <div className="p-3 border bg-card text-card-foreground rounded font-arabic">
                                        بطاقة
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle className="font-arabic">الأزرار</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <Button className="w-full font-arabic">زر أساسي</Button>
                                    <Button variant="secondary" className="w-full font-arabic">زر ثانوي</Button>
                                    <Button variant="outline" className="w-full font-arabic">زر محدد</Button>
                                    <Button variant="ghost" className="w-full font-arabic">زر شفاف</Button>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle className="font-arabic">الشارات</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div className="flex flex-wrap gap-2">
                                        <Badge className="font-arabic">افتراضي</Badge>
                                        <Badge variant="secondary" className="font-arabic">ثانوي</Badge>
                                        <Badge variant="destructive" className="font-arabic">تحذير</Badge>
                                        <Badge variant="outline" className="font-arabic">محدد</Badge>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <Separator className="my-8" />

                        {/* Typography Scale */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="font-arabic">مقياس الخطوط</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6 font-arabic leading-arabic tracking-arabic">
                                <div>
                                    <h1 className="text-4xl font-bold text-foreground">عنوان من المستوى الأول</h1>
                                    <p className="text-xs text-muted-foreground">text-4xl font-bold</p>
                                </div>
                                <div>
                                    <h2 className="text-3xl font-bold text-foreground">عنوان من المستوى الثاني</h2>
                                    <p className="text-xs text-muted-foreground">text-3xl font-bold</p>
                                </div>
                                <div>
                                    <h3 className="text-2xl font-semibold text-foreground">عنوان من المستوى الثالث</h3>
                                    <p className="text-xs text-muted-foreground">text-2xl font-semibold</p>
                                </div>
                                <div>
                                    <h4 className="text-xl font-medium text-foreground">عنوان من المستوى الرابع</h4>
                                    <p className="text-xs text-muted-foreground">text-xl font-medium</p>
                                </div>
                                <div>
                                    <p className="text-base text-foreground">نص عادي - هذا مثال على النص العادي الذي يظهر في الفقرات الأساسية</p>
                                    <p className="text-xs text-muted-foreground">text-base</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">نص صغير - يستخدم للملاحظات والتفاصيل الثانوية</p>
                                    <p className="text-xs text-muted-foreground">text-sm text-muted-foreground</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </section>
            </div>
        </>
    );
} 