import { type SharedData, type PricingPlan } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import AppearanceToggleDropdown from '@/components/appearance-dropdown';
import { useEffect, useState, useRef } from 'react';
import { Menu, X, Check, ArrowUp } from 'lucide-react';

// Import MVP Blocks components
import ClientMobileAppHero from '@/components/mvpblocks/mockup-hero';
import DesignerDashboardHero from '@/components/mvpblocks/notebook';
import AboutUs1 from '@/components/mvpblocks/about-us-1';

interface HomeProps extends SharedData {
    pricingPlans: PricingPlan[];
}

export default function Welcome() {
    const { auth, pricingPlans } = usePage<HomeProps>().props;
    const [activeSection, setActiveSection] = useState('hero');
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [showScrollTop, setShowScrollTop] = useState(false);
    
    const sectionRefs = {
        hero: useRef<HTMLElement>(null),
        features: useRef<HTMLElement>(null),
        about: useRef<HTMLElement>(null),
        pricing: useRef<HTMLElement>(null),
    };

    // Smart scrolling behavior
    useEffect(() => {
        const handleScroll = () => {
            const scrollPosition = window.scrollY + window.innerHeight / 2;
            setShowScrollTop(window.scrollY > 500);

            // Determine active section
            Object.entries(sectionRefs).forEach(([key, ref]) => {
                if (ref.current) {
                    const rect = ref.current.getBoundingClientRect();
                    const elementTop = rect.top + window.scrollY;
                    const elementBottom = elementTop + rect.height;
                    
                    if (scrollPosition >= elementTop && scrollPosition <= elementBottom) {
                        setActiveSection(key);
                    }
                }
            });
        };

        window.addEventListener('scroll', handleScroll);
        handleScroll(); // Initial check
        
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    // Smooth scroll to section
    const scrollToSection = (sectionKey: keyof typeof sectionRefs) => {
        const element = sectionRefs[sectionKey].current;
        if (element) {
            const offsetTop = element.offsetTop - 80; // Account for fixed header
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
            setIsMenuOpen(false);
        }
    };

    // Scroll to top
    const scrollToTop = () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    // Enhanced Pricing Section Component
    const PricingSection = () => {
        if (!pricingPlans || pricingPlans.length === 0) {
            return (
                <div className="text-center py-12">
                    <p className="text-muted-foreground">لا توجد خطط أسعار متاحة حالياً</p>
                </div>
            );
        }

        return (
            <>
            <Head title="الرئيسية">
            <link rel="preconnect" href="https://fonts.bunny.net" />
            <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        </Head>
            <div className="grid gap-6 md:gap-8 lg:grid-cols-3 max-w-5xl mx-auto">
                {pricingPlans.map((plan, index) => (
                    <div 
                        key={plan.id}
                        className={`relative rounded-2xl border p-8 transition-all duration-300 hover:shadow-xl hover:scale-105 ${
                            index === 1 // Middle plan highlighted
                                ? 'border-primary bg-primary/5 shadow-lg scale-105' 
                                : 'border-border bg-card'
                        }`}
                    >
                        {index === 1 && (
                            <div className="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                <span className="bg-primary text-primary-foreground px-4 py-1 text-sm font-medium rounded-full">
                                    الأكثر شعبية
                                </span>
                            </div>
                        )}
                        
                        <div className="text-center">
                            <h3 className="text-xl font-bold font-arabic mb-2">{plan.name}</h3>
                            <p className="text-muted-foreground text-sm mb-6">{plan.description}</p>
                            
                            <div className="mb-6">
                                <span className="text-4xl font-bold text-primary">
                                    {plan.formatted_price}
                                </span>
                                <span className="text-muted-foreground text-sm block mt-1">
                                    لمدة {plan.duration_text}
                                </span>
                            </div>

                            <Button 
                                asChild 
                                className={`w-full mb-6 ${index === 1 ? 'bg-primary hover:bg-primary/90' : ''}`}
                                variant={index === 1 ? 'default' : 'outline'}
                            >
                                <Link href={route('register')}>
                                    {index === 1 ? 'ابدأ الآن' : 'اختر هذه الخطة'}
                                </Link>
                            </Button>

                            <div className="space-y-3 text-right">
                                <div className="flex items-center gap-2">
                                    <Check className="h-4 w-4 text-primary flex-shrink-0" />
                                    <span className="text-sm">رفع وبيع تصاميم التطريز</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Check className="h-4 w-4 text-primary flex-shrink-0" />
                                    <span className="text-sm">عرض المتجر الشخصي</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Check className="h-4 w-4 text-primary flex-shrink-0" />
                                    <span className="text-sm">إدارة الطلبات والمبيعات</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Check className="h-4 w-4 text-primary flex-shrink-0" />
                                    <span className="text-sm">دعم فني متواصل</span>
                                </div>
                                {plan.duration_months >= 6 && (
                                    <div className="flex items-center gap-2">
                                        <Check className="h-4 w-4 text-primary flex-shrink-0" />
                                        <span className="text-sm text-primary font-medium">خصم خاص للاشتراك الطويل</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                ))}
            </div>
            </>
        );
    };

    // Navigation items
    const navItems = [
        { key: 'hero' as const, label: 'الرئيسية', href: '#hero' },
        { key: 'features' as const, label: 'المميزات', href: '#features' },
        { key: 'about' as const, label: 'عن المشروع', href: '#about' },
        { key: 'pricing' as const, label: 'الأسعار', href: '#pricing' },
    ];

    return (
        <>
            <Head title="الرئيسية">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            </Head>
            
            <div className="min-h-screen bg-background text-foreground relative overflow-hidden" dir="rtl">
                {/* Enhanced Background Effects */}
                <div className="fixed inset-0 -z-10 overflow-hidden">
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(229,62,62,0.08),rgba(255,255,255,0))] dark:bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(229,62,62,0.06),rgba(30,30,40,0))]"></div>
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_10%_90%,rgba(229,62,62,0.05),transparent_50%)] dark:bg-[radial-gradient(circle_at_10%_90%,rgba(120,119,198,0.08),transparent_50%)]"></div>
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_90%_20%,rgba(255,100,150,0.03),transparent_50%)] dark:bg-[radial-gradient(circle_at_90%_20%,rgba(100,150,255,0.03),transparent_50%)]"></div>
                    <div className="absolute inset-0 opacity-[0.02] [background-image:linear-gradient(rgba(229,62,62,0.03)_1px,transparent_1px),linear-gradient(to_right,rgba(229,62,62,0.03)_1px,transparent_1px)] [background-size:40px_40px] dark:opacity-[0.015] dark:[background-image:linear-gradient(rgba(200,200,255,0.03)_1px,transparent_1px),linear-gradient(to_right,rgba(200,200,255,0.03)_1px,transparent_1px)]"></div>
                </div>

                {/* Enhanced Header */}
                <header className="fixed left-0 right-0 top-0 z-50 border-b border-border/40 bg-background/70 backdrop-blur-xl transition-all duration-300">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <div className="flex h-16 items-center justify-between">
                            {/* Logo */}
                            <div className="flex items-center space-x-3 space-x-reverse">
                                <Link href={route('home')} className="flex items-center space-x-3 space-x-reverse">
                                    <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 via-purple-600 to-purple-700 shadow-lg">
                                        <img
                                            src="/logo.png"
                                            alt="Rachmat Logo"
                                            className="h-6 w-6 object-contain"
                                            loading="lazy"
                                        />
                                    </div>
                                    <div className="flex flex-col">
                                        <span className="text-lg font-bold text-foreground">رشماتي</span>
                                        <span className="-mt-1 text-xs text-muted-foreground">منصة التطريز</span>
                                    </div>
                                </Link>
                            </div>

                            {/* Desktop Navigation */}
                            <nav className="hidden items-center space-x-1 space-x-reverse lg:flex">
                                {navItems.map((item) => (
                                    <button
                                        key={item.key}
                                        onClick={() => scrollToSection(item.key)}
                                        className={`px-4 py-2 text-sm font-medium transition-colors ${
                                            activeSection === item.key
                                                ? 'text-primary'
                                                : 'text-foreground/80 hover:text-foreground'
                                        }`}
                                    >
                                        {item.label}
                                    </button>
                                ))}
                            </nav>

                            {/* Desktop Auth Actions */}
                            <div className="hidden items-center space-x-3 space-x-reverse lg:flex">
                                <AppearanceToggleDropdown />
                                {auth.user ? (
                                    <Button variant="outline" asChild>
                                        <Link href={route('dashboard')}>لوحة التحكم</Link>
                                    </Button>
                                ) : (
                                    <>
                                        <Link 
                                            href={route('login')} 
                                            className="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground transition-colors"
                                        >
                                            تسجيل الدخول
                                        </Link>
                                        <Button asChild>
                                            <Link href={route('register')}>إنشاء حساب</Link>
                                        </Button>
                                    </>
                                )}
                            </div>

                            {/* Mobile Menu Button */}
                            <div className="lg:hidden">
                                <button
                                    onClick={() => setIsMenuOpen(!isMenuOpen)}
                                    className="p-2 text-foreground/80 hover:text-foreground"
                                >
                                    {isMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                                </button>
                            </div>
                        </div>

                        {/* Mobile Menu */}
                        {isMenuOpen && (
                            <div className="lg:hidden border-t border-border/40 bg-background/95 backdrop-blur-xl">
                                <div className="px-4 py-6 space-y-4">
                                    {navItems.map((item) => (
                                        <button
                                            key={item.key}
                                            onClick={() => scrollToSection(item.key)}
                                            className={`block w-full text-right px-4 py-2 text-sm font-medium transition-colors ${
                                                activeSection === item.key
                                                    ? 'text-primary'
                                                    : 'text-foreground/80 hover:text-foreground'
                                            }`}
                                        >
                                            {item.label}
                                        </button>
                                    ))}
                                    <div className="pt-4 border-t border-border/40 space-y-2">
                                        {auth.user ? (
                                            <Button variant="outline" asChild className="w-full">
                                                <Link href={route('dashboard')}>لوحة التحكم</Link>
                                            </Button>
                                        ) : (
                                            <>
                                                <Button variant="outline" asChild className="w-full">
                                                    <Link href={route('login')}>تسجيل الدخول</Link>
                                                </Button>
                                                <Button asChild className="w-full">
                                                    <Link href={route('register')}>إنشاء حساب</Link>
                                                </Button>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </header>

                {/* Enhanced Content Sections */}
                <main className="relative pt-16 w-full" dir="ltr">
                    {/* Hero Section - Designer Dashboard */}
                    <section ref={sectionRefs.hero} id="hero" className="relative min-h-screen">
                        <DesignerDashboardHero />
                    </section>

                    {/* Features Section - Client Mobile App */}
                    <section ref={sectionRefs.features} id="features" className="  pt-40 relative min-h-screen">
                        <ClientMobileAppHero />
                    </section>

                    {/* About Section */}
                    <section ref={sectionRefs.about} id="about" className="relative min-h-screen">
                        <AboutUs1 />
                    </section>

                    {/* Enhanced Pricing Section */}
                    <section ref={sectionRefs.pricing} id="pricing" className="relative py-16 md:py-32" dir="rtl">
                        {/* Background Enhancement */}
                        <div className="absolute inset-0 -z-10 overflow-hidden">
                            <div className="absolute top-1/3 left-1/2 -translate-x-1/2 h-[400px] w-[500px] rounded-full bg-gradient-to-br from-primary/8 via-primary/4 to-transparent blur-3xl opacity-70" />
                            <div className="absolute bottom-1/4 right-1/4 h-[300px] w-[300px] rounded-full bg-gradient-to-tl from-rose-500/6 via-rose-500/3 to-transparent blur-3xl opacity-60" />
                        </div>

                        <div className="mx-auto max-w-6xl px-6">
                            <div className="mx-auto max-w-3xl text-center mb-16">
                                <h2 className="text-balance text-3xl font-extrabold md:text-4xl lg:text-5xl font-arabic mb-6">
                                    خطط الاشتراك للمصممين
                                </h2>
                                <p className="text-lg text-muted-foreground">
                                    انضم إلى منصة رشماتي واعرض تصاميمك لآلاف العملاء. 
                                    اختر الخطة المناسبة لك وابدأ رحلتك في عالم التطريز الرقمي.
                                </p>
                            </div>

                            <PricingSection />

                            <div className="text-center mt-12">
                                <p className="text-sm text-muted-foreground mb-4">
                                    جميع الخطط تشمل الدعم الفني المتواصل وإمكانية الإلغاء في أي وقت
                                </p>
                                <div className="flex justify-center gap-4 flex-wrap">
                                    <Link 
                                        href={route('register')} 
                                        className="text-primary hover:text-primary/80 text-sm underline"
                                    >
                                        إنشاء حساب جديد
                                    </Link>
                                    <Link 
                                        href={route('privacy-policy.show')} 
                                        className="text-muted-foreground hover:text-foreground text-sm underline"
                                    >
                                        سياسة الخصوصية
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>

                {/* Enhanced Footer */}
                <footer className="relative z-10 mt-8 w-full overflow-hidden pb-8 pt-16" dir="rtl">
                    <div className="pointer-events-none absolute left-1/2 top-0 z-0 h-full w-full -translate-x-1/2 select-none">
                        <div className="absolute -top-32 left-1/4 h-72 w-72 rounded-full bg-gradient-to-br from-primary/15 to-rose-500/10 blur-3xl"></div>
                        <div className="absolute -bottom-24 right-1/4 h-80 w-80 rounded-full bg-gradient-to-tl from-primary/10 to-rose-500/15 blur-3xl"></div>
                        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-96 w-96 rounded-full bg-gradient-to-r from-primary/5 via-transparent to-rose-500/5 blur-3xl"></div>
                    </div>
                    
                    <div className="relative mx-auto flex max-w-6xl flex-col items-center gap-8 rounded-2xl border border-border/30 bg-background/80 backdrop-blur-xl shadow-xl shadow-primary/5 px-6 py-10 md:flex-row md:items-start md:justify-between md:gap-12">
                        <div className="flex flex-col items-center md:items-start">
                            <Link href={route('home')} className="mb-4 flex items-center gap-2">
                                <span className="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-purple-400 to-purple-700 shadow-md">
                                    <img
                                        src="/logo.png"
                                        alt="Rachmat Logo"
                                        className="h-6 w-6 object-contain"
                                        loading="lazy"
                                    />
                                </span>
                                <span className="bg-gradient-to-br from-rose-200 to-rose-500 bg-clip-text text-xl font-semibold tracking-tight text-transparent">
                                    رشماتي
                                </span>
                            </Link>
                            <p className="mb-6 max-w-xs text-center text-sm text-foreground md:text-right">
                                منصة رشماتي تربط العملاء بأفضل مصممي التطريز المحترفين لتوفير تصاميم تطريز مخصصة عالية الجودة مع توصيل رقمي سريع.
                            </p>
                        </div>
                        
                        <nav className="flex w-full flex-col gap-9 text-center md:w-auto md:flex-row md:justify-end md:text-right">
                            <div>
                                <div className="mb-3 text-xs font-semibold uppercase tracking-widest text-rose-400">
                                    المنتج
                                </div>
                                <ul className="space-y-2">
                                    <li>
                                        <button 
                                            onClick={() => scrollToSection('features')}
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            تصفح التصاميم
                                        </button>
                                    </li>
                                    <li>
                                        <button 
                                            onClick={() => scrollToSection('about')}
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            المصممين
                                        </button>
                                    </li>
                                    <li>
                                        <button 
                                            onClick={() => scrollToSection('pricing')}
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            الأسعار
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <div className="mb-3 text-xs font-semibold uppercase tracking-widest text-rose-400">
                                    الشركة
                                </div>
                                <ul className="space-y-2">
                                    <li>
                                        <button 
                                            onClick={() => scrollToSection('about')}
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            عن المنصة
                                        </button>
                                    </li>
                                    <li>
                                        <Link 
                                            href={route('register')} 
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            انضم كمصمم
                                        </Link>
                                    </li>
                                    <li>
                                        <Link 
                                            href={route('login')} 
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            تسجيل الدخول
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <div className="mb-3 text-xs font-semibold uppercase tracking-widest text-rose-400">
                                    الموارد
                                </div>
                                <ul className="space-y-2">
                                    <li>
                                        <button 
                                            onClick={() => scrollToSection('features')}
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            دليل الاستخدام
                                        </button>
                                    </li>
                                    <li>
                                        <button 
                                            onClick={() => scrollToSection('about')}
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            الأسئلة الشائعة
                                        </button>
                                    </li>
                                    <li>
                                        <button 
                                            onClick={() => scrollToSection('features')}
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            الدعم الفني
                                        </button>
                                    </li>
                                    <li>
                                        <Link 
                                            href={route('privacy-policy.show')} 
                                            className="text-foreground/70 hover:text-foreground transition-colors"
                                        >
                                            سياسة الخصوصية
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                    
                    <div className="relative z-10 mt-10 text-center">
                        <div className="inline-flex items-center justify-center rounded-full border border-border/20 bg-background/50 backdrop-blur-sm px-4 py-2">
                            <span className="text-xs text-muted-foreground">
                                &copy; 2025 منصة رشماتي. جميع الحقوق محفوظة.
                            </span>
                        </div>
                    </div>
                </footer>

                {/* Scroll to Top Button */}
                {showScrollTop && (
                    <button
                        onClick={scrollToTop}
                        className="fixed bottom-6 left-6 z-50 p-3 bg-primary text-primary-foreground rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110"
                        aria-label="العودة إلى الأعلى"
                    >
                        <ArrowUp className="h-5 w-5" />
                    </button>
                )}
            </div>
        </>
    );
}
