'use client';

import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { ArrowLeft, Package, Store, BarChart3, CreditCard, Settings, TrendingUp } from 'lucide-react';

export default function DesignerDashboardHero() {
  return (
    <div className="min-h-screen py-6 sm:py-14" dir="rtl">
      {/* Subtle local background accents */}
      <div className="pointer-events-none absolute inset-0 top-0 z-0 overflow-hidden">
        <div className="absolute -right-32 top-20 h-[400px] w-[400px] rounded-full bg-gradient-to-br from-primary/8 via-primary/4 to-transparent opacity-60 blur-[120px]" />
        <div className="absolute -left-32 bottom-20 h-[350px] w-[350px] rounded-full bg-gradient-to-tl from-rose-500/6 via-rose-500/3 to-transparent opacity-50 blur-[100px]" />
      </div>

      <main className="container relative mt-4 max-w-[1100px] px-2 py-4 lg:py-8 mx-auto">
        <div className="relative sm:overflow-hidden">
          <div className="relative flex flex-col items-center justify-center rounded-xl border border-primary/20 bg-background/70 px-4 pt-12 shadow-xl shadow-primary/10 backdrop-blur-md text-center md:px-12 md:pt-16">
            <div
              className="animate-gradient-x absolute inset-0 top-32 z-0 hidden blur-2xl dark:block"
              style={{
                maskImage:
                  'linear-gradient(to bottom, transparent, white, transparent)',
                background:
                  'repeating-linear-gradient(65deg, hsl(var(--primary)), hsl(var(--primary)/0.8) 12px, color-mix(in oklab, hsl(var(--primary)) 30%, transparent) 20px, transparent 200px)',
                backgroundSize: '200% 100%',
              }}
            />
            <div
              className="animate-gradient-x absolute inset-0 top-32 z-0 text-right blur-2xl dark:hidden"
              style={{
                maskImage:
                  'linear-gradient(to bottom, transparent, white, transparent)',
                background:
                  'repeating-linear-gradient(65deg, hsl(var(--primary)/0.9), hsl(var(--primary)/0.7) 12px, color-mix(in oklab, hsl(var(--primary)) 30%, transparent) 20px, transparent 200px)',
                backgroundSize: '200% 100%',
              }}
            />
            <h1 className="mb-4 text-center text-3xl font-medium leading-tight md:text-5xl font-arabic">
              لوحة تحكم <span className="text-primary">المصمم المحترف</span> في
              رشماتي
            </h1>
            <p className="mb-8 text-center text-muted-foreground max-w-4xl mx-auto md:text-xl">
              إدارة شاملة لأعمالك في التطريز الرقمي. رفع التصاميم، إدارة المتجر، تتبع المبيعات،
              وتحليل الأرباح - كل ما تحتاجه لنجاح مشروعك في مكان واحد.
            </p>
            <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 max-w-4xl mx-auto">
              <div className="flex items-center gap-3 rounded-lg border border-border/50 bg-card/50 p-3 backdrop-blur-sm">
                <Package className="h-6 w-6 text-primary flex-shrink-0" />
                <span className="font-medium">رفع وإدارة التصاميم</span>
              </div>
              <div className="flex items-center gap-3 rounded-lg border border-border/50 bg-card/50 p-3 backdrop-blur-sm">
                <Store className="h-6 w-6 text-primary flex-shrink-0" />
                <span className="font-medium">إدارة المتجر الشخصي</span>
              </div>
              <div className="flex items-center gap-3 rounded-lg border border-border/50 bg-card/50 p-3 backdrop-blur-sm">
                <BarChart3 className="h-6 w-6 text-primary flex-shrink-0" />
                <span className="font-medium">تتبع الطلبات والمبيعات</span>
              </div>
              <div className="flex items-center gap-3 rounded-lg border border-border/50 bg-card/50 p-3 backdrop-blur-sm">
                <TrendingUp className="h-6 w-6 text-primary flex-shrink-0" />
                <span className="font-medium">إحصائيات الأرباح</span>
              </div>
              <div className="flex items-center gap-3 rounded-lg border border-border/50 bg-card/50 p-3 backdrop-blur-sm">
                <CreditCard className="h-6 w-6 text-primary flex-shrink-0" />
                <span className="font-medium">إدارة الاشتراكات</span>
              </div>
              <div className="flex items-center gap-3 rounded-lg border border-border/50 bg-card/50 p-3 backdrop-blur-sm">
                <Settings className="h-6 w-6 text-primary flex-shrink-0" />
                <span className="font-medium">إعدادات متقدمة</span>
              </div>
            </div>

            <div className="z-10 mt-6 flex items-center justify-center gap-3">
              <a
                href="/register"
                className={cn(
                  buttonVariants({
                    size: 'lg',
                    className:
                      'rounded-full bg-gradient-to-b from-primary to-primary/80 text-primary-foreground',
                  }),
                )}
              >
                <ArrowLeft className="size-4 ml-2" />
                ابدأ كمصمم الآن
              </a>
              <a
                href="/login"
                className={cn(
                  buttonVariants({
                    size: 'lg',
                    variant: 'outline',
                    className: 'rounded-full bg-background',
                  }),
                )}
              >
                تسجيل الدخول
                <svg
                  className="mr-1 inline size-4"
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                >
                  <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                  <polyline points="10,17 15,12 10,7" />
                  <line x1="15" y1="12" x2="3" y2="12" />
                </svg>
              </a>
            </div>

            <div className="relative z-10 mt-16 w-full flex justify-center">
              {/* Designer Dashboard Screenshot */}
              <div className="relative max-w-5xl w-full">
                <div className="relative rounded-2xl border border-border bg-card/80 p-4 shadow-2xl backdrop-blur-sm duration-1000 animate-in fade-in slide-in-from-bottom-12 overflow-hidden">
                  <img
                    src="/designer-dashboard-screenshot.png"
                    alt="لوحة تحكم المصمم المحترف في رشماتي"
                    className="w-full h-auto rounded-lg shadow-lg"
                    loading="lazy"
                  />
                </div>
              </div>

              {/* Floating Success Badge */}
              <div className="absolute -left-6 -top-6 -rotate-6 transform rounded-lg bg-card p-3 shadow-lg animate-in fade-in slide-in-from-right-4 border border-border">
                <div className="flex items-center gap-2">
                  <TrendingUp className="h-5 w-5 text-green-500" />
                  <span className="font-medium text-card-foreground">نمو مستمر في الأرباح</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
