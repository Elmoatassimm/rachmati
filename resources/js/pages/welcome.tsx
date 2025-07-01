import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="الرئيسية">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            </Head>
            <div className="min-h-screen bg-background text-foreground" dir="rtl">
                {/* Custom Arabic Header */}
                <header className="fixed left-0 right-0 top-0 z-50 border-b border-border/50 bg-background/80 backdrop-blur-md">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <div className="flex h-16 items-center justify-between">
                            <div className="flex items-center space-x-3 space-x-reverse">
                                <Link href="/" className="flex items-center space-x-3 space-x-reverse">
                                    <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 via-purple-600 to-purple-700 shadow-lg">
                                        <span className="text-white font-bold">ر</span>
                                    </div>
                                    <div className="flex flex-col">
                                        <span className="text-lg font-bold text-foreground">رشماتي</span>
                                        <span className="-mt-1 text-xs text-muted-foreground">منصة التصميم</span>
                                    </div>
                                </Link>
                            </div>

                            <nav className="hidden items-center space-x-1 space-x-reverse lg:flex">
                                <Link href="/" className="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground">الرئيسية</Link>
                                <Link href="#features" className="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground">المميزات</Link>
                                <Link href="#pricing" className="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground">الأسعار</Link>
                                <Link href="#about" className="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground">عن المشروع</Link>
                            </nav>

                            <div className="hidden items-center space-x-3 space-x-reverse lg:flex">
                                {auth.user ? (
                                    <Button variant="outline" asChild>
                                        <Link href={route('dashboard')}>لوحة التحكم</Link>
                                    </Button>
                                ) : (
                                    <>
                                        <Link href={route('login')} className="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground">
                                            تسجيل الدخول
                                        </Link>
                                        <Button asChild>
                                            <Link href={route('register')}>إنشاء حساب</Link>
                                        </Button>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </header>

                {/* Designer Dashboard Hero Section */}
                <section className="min-h-screen py-6 sm:py-14 mt-16" id="hero1">
                    <div className="pointer-events-none absolute inset-0 top-0 z-0 overflow-hidden">
                        <div className="absolute -left-20 -top-20 h-[600px] w-[600px] rounded-full bg-gradient-to-br from-rose-500/30 via-rose-500/20 to-transparent opacity-50 blur-[100px]" />
                        <div className="absolute -right-20 -top-40 h-[500px] w-[500px] rounded-full bg-gradient-to-bl from-red-500/30 via-red-500/20 to-transparent opacity-50 blur-[100px]" />
                    </div>

                    <main className="container relative mt-4 max-w-[1100px] px-2 py-4 lg:py-8">
                        <div className="relative sm:overflow-hidden">
                            <div className="relative flex flex-col items-start justify-start rounded-xl border border-primary/20 bg-background/70 px-4 pt-12 shadow-xl shadow-primary/10 backdrop-blur-md max-md:text-center md:px-12 md:pt-16">
                                <h1 className="mb-4 flex flex-wrap gap-2 text-3xl font-medium leading-tight md:text-5xl">
                                    منصة <span className="text-primary">رشماتي</span> للتصميم
                                </h1>
                                <p className="mb-8 text-right text-muted-foreground md:max-w-[80%] md:text-xl">
                                    منصة شاملة تجمع بين المصممين والعملاء لتقديم خدمات تصميم احترافية. من التصاميم البسيطة إلى المشاريع المعقدة، رشماتي تساعدك في إنجاز مشاريعك بأعلى جودة.
                                </p>
                                <div className="mb-6 flex flex-wrap gap-4 md:flex-row">
                                    <div className="flex items-center gap-2">
                                        <svg className="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>مصممون محترفون</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <svg className="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>تسليم سريع</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <svg className="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>أسعار تنافسية</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <svg className="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>ضمان الجودة</span>
                                    </div>
                                </div>

                                <div className="z-10 mt-2 inline-flex items-center justify-start gap-3">
                                    <Button size="lg" className="rounded-full bg-gradient-to-b from-primary to-primary/80" asChild>
                                        <Link href={route('register')}>ابدأ الآن</Link>
                                    </Button>
                                    <Button variant="outline" size="lg" className="rounded-full" asChild>
                                        <Link href="#about">اعرف المزيد</Link>
                                    </Button>
                                </div>

                                <div className="relative z-10 mt-16 w-full">
                                    <img
                                        src="https://blocks.mvp-subha.me/assets/bg.png"
                                        alt="منصة رشماتي للتصميم"
                                        width={1000}
                                        height={600}
                                        className="border-6 z-10 mx-auto -mb-60 w-full select-none rounded-lg border-neutral-100 object-cover shadow-2xl duration-1000 animate-in fade-in slide-in-from-bottom-12 dark:border-neutral-600 lg:-mb-40"
                                    />
                                </div>
                            </div>
                        </div>
                    </main>
                </section>

                {/* Mobile App Presentation */}
                <section className="relative min-h-screen w-full overflow-hidden bg-background py-16" id="mobile">
                    <div className="absolute inset-0 z-0">
                        <div className="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(229,62,62,0.2),rgba(255,255,255,0))] dark:bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(229,62,62,0.15),rgba(30,30,40,0))]"></div>
                    </div>

                    <div className="container relative z-10 mx-auto max-w-7xl">
                        <div className="grid items-center gap-16 md:grid-cols-2">
                            <div className="flex flex-col text-center md:text-right">
                                <h2 className="mb-6 text-4xl font-bold leading-tight tracking-tight text-foreground md:text-5xl lg:text-6xl">
                                    تطبيق <span className="bg-gradient-to-r from-primary via-rose-400 to-rose-300 bg-clip-text text-transparent">رشماتي</span> الذكي
                                </h2>
                                <p className="mb-8 text-lg leading-relaxed text-muted-foreground">
                                    استمتع بتجربة سلسة على جهازك المحمول. تطبيق رشماتي يجمع بين الذكاء الاصطناعي وسهولة الاستخدام لتقديم أفضل خدمات التصميم.
                                </p>
                                <div className="flex flex-wrap justify-center gap-4 md:justify-start">
                                    <Button className="rounded-full">
                                        استكشف التطبيق
                                    </Button>
                                    <Button variant="outline" className="rounded-full">
                                        تحميل التطبيق
                                    </Button>
                                </div>
                            </div>
                            <div className="relative mx-auto flex justify-center">
                                <div className="relative">
                                    <img
                                        src="https://blocks.mvp-subha.me/assets/phone.png"
                                        alt="تطبيق رشماتي المحمول"
                                        className="h-auto w-64 md:w-80"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* About Us Section */}
                <section className="relative w-full overflow-hidden pt-20" id="about">
                    <div className="container relative z-10 mx-auto px-4 md:px-6">
                        <div className="mx-auto mb-16 max-w-2xl text-center">
                            <h1 className="bg-gradient-to-r from-foreground/80 via-foreground to-foreground/80 bg-clip-text text-4xl font-bold tracking-tight text-transparent sm:text-5xl md:text-6xl">
                                عن مشروع رشماتي
                            </h1>
                            <p className="mt-6 text-xl text-muted-foreground">
                                بناء مستقبل التصميم الرقمي من خلال منصة متطورة تجمع بين المصممين والعملاء
                            </p>
                        </div>

                        <div className="relative mx-auto mb-24 max-w-7xl">
                            <div className="relative z-10 grid gap-12 md:grid-cols-2">
                                <div className="group relative block overflow-hidden rounded-2xl border border-border/40 bg-gradient-to-br p-10 backdrop-blur-3xl">
                                    <div className="mb-6 inline-flex aspect-square h-16 w-16 flex-1 items-center justify-center rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 backdrop-blur-sm">
                                        <svg className="h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <h2 className="mb-4 bg-gradient-to-r from-primary/90 to-primary/70 bg-clip-text text-3xl font-bold text-transparent">
                                        رسالتنا
                                    </h2>
                                    <p className="text-lg leading-relaxed text-muted-foreground">
                                        مهمتنا هي تسهيل عملية التصميم وجعلها في متناول الجميع من خلال توفير منصة تجمع أفضل المصممين مع العملاء الذين يبحثون عن جودة عالية وخدمة احترافية.
                                    </p>
                                </div>

                                <div className="group relative block overflow-hidden rounded-2xl border border-border/40 bg-gradient-to-br p-10 backdrop-blur-3xl">
                                    <div className="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500/20 to-blue-500/5 backdrop-blur-sm">
                                        <svg className="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                    <h2 className="mb-4 bg-gradient-to-r from-blue-500/90 to-blue-500/70 bg-clip-text text-3xl font-bold text-transparent">
                                        رؤيتنا
                                    </h2>
                                    <p className="text-lg leading-relaxed text-muted-foreground">
                                        نتطلع إلى عالم يكون فيه الحصول على تصاميم جميلة ومهنية أمراً سهلاً ومتاحاً للجميع، مما يساهم في رفع مستوى الإبداع والابتكار في المنطقة العربية.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Pricing Section */}
                <section className="relative w-full bg-gradient-to-br from-zinc-50 via-white to-zinc-100 py-16 dark:from-zinc-900 dark:via-zinc-950 dark:to-black md:py-32" id="pricing">
                    <div className="mx-auto max-w-5xl px-6">
                        <div className="mx-auto max-w-2xl text-center">
                            <h2 className="text-balance text-3xl font-extrabold md:text-4xl lg:text-5xl">
                                خطط أسعار تناسب احتياجاتك
                            </h2>
                            <p className="mt-4 text-lg text-muted-foreground">
                                اختر الخطة المناسبة لك وابدأ رحلتك مع أفضل المصممين في المنطقة
                            </p>
                        </div>
                        <div className="mt-10 md:mt-20">
                            <div className="relative rounded-3xl border border-zinc-200/60 bg-card shadow-xl shadow-zinc-950/5 backdrop-blur-sm dark:border-zinc-700/50 dark:bg-zinc-900/70">
                                <div className="grid items-center gap-12 divide-y divide-zinc-200 p-12 dark:divide-zinc-700 md:grid-cols-2 md:gap-x-2 md:divide-x-0 md:divide-y-0">
                                    <div className="pb-12 text-center md:pb-0 md:pr-12">
                                        <h3 className="text-2xl font-semibold">الخطة الشاملة</h3>
                                        <p className="mt-2 text-lg">للشركات والمؤسسات</p>
                                        <span className="mb-6 mt-12 inline-block text-6xl font-extrabold text-primary">
                                            <span className="align-super text-4xl">$</span>300
                                        </span>
                                        <div className="flex justify-center">
                                            <Button size="lg" className="shadow-md" asChild>
                                                <Link href={route('register')}>ابدأ الآن</Link>
                                            </Button>
                                        </div>
                                        <p className="mt-12 text-sm text-muted-foreground">
                                            يشمل: الأمان، تخزين غير محدود، المدفوعات، محرك البحث، وجميع المميزات
                                        </p>
                                    </div>

                                    <div className="relative m-3">
                                        <div className="text-right">
                                            <h4 className="mb-4 text-lg font-medium">ما يشمله:</h4>
                                            <ul role="list" className="space-y-4">
                                                <li className="flex items-start gap-3 text-sm">
                                                    <svg className="mt-1 size-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span>وصول إلى جميع المصممين المحترفين</span>
                                                </li>
                                                <li className="flex items-start gap-3 text-sm">
                                                    <svg className="mt-1 size-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span>دعم فني على مدار الساعة</span>
                                                </li>
                                                <li className="flex items-start gap-3 text-sm">
                                                    <svg className="mt-1 size-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span>مراجعات غير محدودة</span>
                                                </li>
                                                <li className="flex items-start gap-3 text-sm">
                                                    <svg className="mt-1 size-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span>أولوية في التسليم</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Custom Arabic Footer */}
                <footer className="relative z-10 mt-8 w-full overflow-hidden pb-8 pt-16">
                    <div className="pointer-events-none absolute left-1/2 top-0 z-0 h-full w-full -translate-x-1/2 select-none">
                        <div className="absolute -top-32 left-1/4 h-72 w-72 rounded-full bg-rose-600/20 blur-3xl"></div>
                        <div className="absolute -bottom-24 right-1/4 h-80 w-80 rounded-full bg-rose-600/20 blur-3xl"></div>
                    </div>
                    <div className="relative mx-auto flex max-w-6xl flex-col items-center gap-8 rounded-2xl border border-border/40 bg-background/70 backdrop-blur-md px-6 py-10 md:flex-row md:items-start md:justify-between md:gap-12">
                        <div className="flex flex-col items-center md:items-start">
                            <Link href="/" className="mb-4 flex items-center gap-2">
                                <span className="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-purple-400 to-purple-700 text-2xl font-extrabold text-white shadow-md">
                                    ر
                                </span>
                                <span className="bg-gradient-to-br from-rose-200 to-rose-500 bg-clip-text text-xl font-semibold tracking-tight text-transparent">
                                    رشماتي
                                </span>
                            </Link>
                            <p className="mb-6 max-w-xs text-center text-sm text-foreground md:text-right">
                                منصة رشماتي توفر مجموعة من المصممين المحترفين والأدوات المتطورة لمساعدتك في إنشاء تصاميم جميلة ومتجاوبة بسرعة وكفاءة.
                            </p>
                        </div>
                        <nav className="flex w-full flex-col gap-9 text-center md:w-auto md:flex-row md:justify-end md:text-right">
                            <div>
                                <div className="mb-3 text-xs font-semibold uppercase tracking-widest text-rose-400">
                                    المنتج
                                </div>
                                <ul className="space-y-2">
                                    <li><a href="#features" className="text-foreground/70 hover:text-foreground">المميزات</a></li>
                                    <li><a href="#pricing" className="text-foreground/70 hover:text-foreground">الأسعار</a></li>
                                    <li><a href="#" className="text-foreground/70 hover:text-foreground">التكامل</a></li>
                                </ul>
                            </div>
                            <div>
                                <div className="mb-3 text-xs font-semibold uppercase tracking-widest text-rose-400">
                                    الشركة
                                </div>
                                <ul className="space-y-2">
                                    <li><a href="#about" className="text-foreground/70 hover:text-foreground">عن المشروع</a></li>
                                    <li><a href="#" className="text-foreground/70 hover:text-foreground">الوظائف</a></li>
                                    <li><a href="#" className="text-foreground/70 hover:text-foreground">التواصل</a></li>
                                </ul>
                            </div>
                            <div>
                                <div className="mb-3 text-xs font-semibold uppercase tracking-widest text-rose-400">
                                    الموارد
                                </div>
                                <ul className="space-y-2">
                                    <li><a href="#" className="text-foreground/70 hover:text-foreground">التوثيق</a></li>
                                    <li><a href="#" className="text-foreground/70 hover:text-foreground">المجتمع</a></li>
                                    <li><a href="#" className="text-foreground/70 hover:text-foreground">الدعم</a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                    <div className="relative z-10 mt-10 text-center text-xs text-foreground">
                        <span>&copy; 2025 منصة رشماتي. جميع الحقوق محفوظة.</span>
                    </div>
                </footer>
            </div>
        </>
    );
}
