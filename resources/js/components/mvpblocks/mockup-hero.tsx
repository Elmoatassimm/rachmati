'use client';

import React, { useRef, useEffect, useState } from 'react';
import {
  motion,
  useAnimation,
  useInView,
  useScroll,
  useTransform,
  useMotionValue,
} from 'framer-motion';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import PhoneMockup from '@/components/ui/phone-mockup';
import { ArrowLeft, Search, Filter, Star, ShoppingBag, Smartphone } from 'lucide-react';

export default function ClientMobileAppHero() {
  const isDark = false; // For now, we'll use light theme
  const heroRef = useRef<HTMLDivElement>(null);
  const mockupRef = useRef<HTMLDivElement>(null);
  const isInView = useInView(heroRef, { once: false, amount: 0.3 });
  const controls = useAnimation();
  const { scrollYProgress } = useScroll({
    target: heroRef,
    offset: ['start start', 'end start'],
  });

  const backgroundY = useTransform(scrollYProgress, [0, 1], [0, 100]);
  const contentY = useTransform(scrollYProgress, [0, 1], [0, 50]);

  const [isHovered, setIsHovered] = useState(false);
  const mouseX = useMotionValue(0);
  const mouseY = useMotionValue(0);

  const rotateX = useTransform(mouseY, [-0.5, 0, 0.5], [20, 0, -20]);
  const rotateY = useTransform(mouseX, [-0.5, 0, 0.5], [-20, 0, 20]);

  useEffect(() => {
    if (isInView) {
      controls.start('visible');
    }
  }, [isInView, controls]);

  const GradientText = ({
    children,
    className,
  }: {
    children: React.ReactNode;
    className?: string;
  }) => (
    <span
      className={cn(
        'bg-gradient-to-r from-primary via-rose-400 to-rose-300 bg-clip-text text-transparent dark:from-primary dark:via-rose-300 dark:to-red-400',
        className,
      )}
    >
      {children}
    </span>
  );

  return (
    <div
      ref={heroRef}
      className="  relative min-h-screen w-full overflow-hidden "
      dir="rtl"
    >
      {/* Subtle local background enhancement */}
      <motion.div className="absolute inset-0 z-0" style={{ y: backgroundY }}>
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_60%_60%_at_70%_30%,rgba(229,62,62,0.04),transparent_70%)] dark:bg-[radial-gradient(ellipse_60%_60%_at_70%_30%,rgba(229,62,62,0.03),transparent_70%)]"></div>
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_80%,rgba(255,100,150,0.02),transparent_60%)] dark:bg-[radial-gradient(circle_at_30%_80%,rgba(100,150,255,0.02),transparent_60%)]"></div>
      </motion.div>

      <motion.div
        className="container relative z-10 mx-auto max-w-7xl"
        style={{ y: contentY }}
      >
        <div className="grid items-center gap-16 md:grid-cols-2">
          <motion.div
            variants={{
              hidden: { opacity: 0, x: -50 },
              visible: {
                opacity: 1,
                x: 0,
                transition: {
                  duration: 0.7,
                  staggerChildren: 0.2,
                },
              },
            }}
            initial="hidden"
            animate={controls}
            className="flex flex-col text-center md:text-right"
          >
            <motion.div
              variants={{
                hidden: { opacity: 0, y: 20 },
                visible: { opacity: 1, y: 0 },
              }}
            >
              <h2 className="mb-6 text-4xl font-bold leading-tight tracking-tight text-foreground md:text-5xl lg:text-6xl font-arabic">
                تطبيق رشماتي <GradientText>للعملاء</GradientText> - تصفح{' '}
                <GradientText>تصاميم التطريز</GradientText> بسهولة
              </h2>
            </motion.div>

            <motion.p
              variants={{
                hidden: { opacity: 0, y: 20 },
                visible: { opacity: 1, y: 0 },
              }}
              className="mb-8 text-lg leading-relaxed text-muted-foreground text-right"
            >
              اكتشف عالم التطريز الرقمي من خلال تطبيق رشماتي المحمول. تصفح آلاف التصاميم،
              اطلب تصاميم مخصصة، وتابع طلباتك بسهولة. كل ما تحتاجه في مكان واحد.{' '}
              <span className="font-semibold text-foreground">
                تجربة استثنائية.
              </span>
            </motion.p>

            <motion.div
              variants={{
                hidden: { opacity: 0, y: 20 },
                visible: { opacity: 1, y: 0 },
              }}
              className="flex flex-wrap justify-center gap-4 md:justify-start"
            >
              <motion.div
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                className="relative"
              >
                <Button className="relative rounded-full flex-row-reverse">
                  <ArrowLeft className="h-4 w-4 mr-2 rotate-180" />
                  تصفح التصاميم

                </Button>
              </motion.div>

              <motion.div
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                className="relative"
              >
                <div className="absolute inset-0 -z-10 rounded-full bg-background/50 backdrop-blur-sm"></div>
                <Button
                  variant="outline"
                  className="rounded-full border-primary/20 backdrop-blur-sm transition-all duration-300 hover:border-primary/30 hover:bg-primary/5 flex-row-reverse"
                >
                  <Smartphone className="ml-2 h-4 w-4" />
                  حمل التطبيق
                </Button>
              </motion.div>
            </motion.div>

            <motion.div
              variants={{ hidden: { opacity: 0 }, visible: { opacity: 1 } }}
              className="mt-10 flex flex-wrap justify-center gap-3 md:justify-start"
            >
              {['تصفح سهل', 'بحث متقدم', 'تقييمات موثوقة'].map(
                (feature, index) => (
                  <motion.div
                    key={feature}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.6 + index * 0.1 }}
                    whileHover={{ scale: 1.05, y: -2 }}
                    className="relative rounded-full px-4 py-1.5 text-sm font-medium text-foreground shadow-sm text-center"
                  >
                    <div className="absolute inset-0 rounded-full border border-primary/10 bg-background/80 backdrop-blur-md dark:border-white/5 dark:bg-background/30"></div>
                    <div className="absolute bottom-0 left-1/2 h-px w-1/2 -translate-x-1/2 bg-gradient-to-r from-rose-500/0 via-primary/20 to-rose-500/0 dark:from-blue-500/0 dark:via-primary/30 dark:to-indigo-500/0"></div>

                    <span className="relative z-10">{feature}</span>
                  </motion.div>
                ),
              )}
            </motion.div>
          </motion.div>

          <motion.div
            variants={{
              hidden: { opacity: 0, scale: 0.9 },
              visible: {
                opacity: 1,
                scale: 1,
                transition: {
                  duration: 0.8,
                  type: 'spring',
                  stiffness: 100,
                },
              },
            }}
            initial="hidden"
            animate={controls}
            ref={mockupRef}
            className="relative mx-auto flex justify-center"
            style={{
              transformStyle: 'preserve-3d',
              perspective: '1000px',
            }}
            onMouseMove={(e) => {
              const rect = e.currentTarget.getBoundingClientRect();
              const x = (e.clientX - rect.left) / rect.width - 0.5;
              const y = (e.clientY - rect.top) / rect.height - 0.5;
              mouseX.set(x);
              mouseY.set(y);

              if (!isHovered) {
                setIsHovered(true);
              }
            }}
            onMouseLeave={() => {
              mouseX.set(0);
              mouseY.set(0);
              setIsHovered(false);
            }}
          >
            <motion.div
              className="relative z-10"
              style={{
                transformStyle: 'preserve-3d',
                rotateX: rotateX,
                rotateY: rotateY,
                scale: isHovered ? 1.05 : 1,
                transition: 'scale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)',
              }}
            >
              {/* Custom Mobile App Mockup */}
              <div className="relative max-w-[380px] mx-auto">
                <div className="relative rounded-[2.5rem] border-8 border-gray-800 dark:border-gray-200 bg-gray-800 dark:bg-gray-200 shadow-2xl">
                  <div className="rounded-[2rem] overflow-hidden bg-white dark:bg-gray-900">
                    {/* Phone Screen Content */}
                    <div className="px-4 py-6 space-y-4">
                      {/* Header */}
                      <div className="flex items-center justify-between mb-6" dir="rtl">
                        <div className="flex items-center gap-2">
                          <span className="font-bold text-lg">رشماتي</span>
                          <img src="/logo.png" alt="رشماتي" className="h-8 w-8 object-contain" loading="lazy" />
                        </div>
                        <div className="flex gap-2">
                          <Filter className="h-5 w-5 text-gray-600" />
                          <Search className="h-5 w-5 text-gray-600" />
                        </div>
                      </div>

                      {/* Search Bar */}
                      <div className="bg-gray-100 dark:bg-gray-800 rounded-full px-4 py-3 mb-4" dir="rtl">
                        <div className="flex items-center gap-2 text-gray-500">
                          <span className="text-sm">ابحث عن تصاميم التطريز...</span>
                          <Search className="h-4 w-4" />
                        </div>
                      </div>

                      {/* Featured Design Cards */}
                      <div className="space-y-3" dir="rtl">
                        <div className="bg-gradient-to-r from-rose-50 to-pink-50 dark:from-rose-900/20 dark:to-pink-900/20 rounded-lg p-4">
                          <div className="flex items-center justify-between mb-2">
                            <div className="flex items-center gap-1">
                              <span className="text-xs">4.9</span>
                              <Star className="h-4 w-4 text-yellow-500 fill-current" />
                            </div>
                            <span className="font-semibold text-sm">تصميم ورود كلاسيكي</span>
                          </div>
                          <div className="flex items-center justify-between">
                            <span className="font-bold text-primary">850 دج</span>
                            <span className="text-xs text-gray-600">مصمم: أحمد محمد</span>
                          </div>
                        </div>

                        <div className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4">
                          <div className="flex items-center justify-between mb-2">
                            <div className="flex items-center gap-1">
                              <span className="text-xs">4.8</span>
                              <Star className="h-4 w-4 text-yellow-500 fill-current" />
                            </div>
                            <span className="font-semibold text-sm">تطريز هندسي حديث</span>
                          </div>
                          <div className="flex items-center justify-between">
                            <span className="font-bold text-primary">1,200 دج</span>
                            <span className="text-xs text-gray-600">مصمم: فاطمة علي</span>
                          </div>
                        </div>
                      </div>

                      {/* Bottom Navigation */}
                      <div className="flex justify-around pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div className="flex flex-col items-center gap-1">
                          <Search className="h-5 w-5 text-primary" />
                          <span className="text-xs text-primary">تصفح</span>
                        </div>
                        <div className="flex flex-col items-center gap-1">
                          <ShoppingBag className="h-5 w-5 text-gray-400" />
                          <span className="text-xs text-gray-400">طلباتي</span>
                        </div>
                        <div className="flex flex-col items-center gap-1">
                          <Star className="h-5 w-5 text-gray-400" />
                          <span className="text-xs text-gray-400">المفضلة</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Phone Frame Details */}
                  <div className="absolute top-0 inset-x-0">
                    <div className="flex justify-center">
                      <div className="w-16 h-1 bg-gray-600 dark:bg-gray-400 rounded-full mt-2"></div>
                    </div>
                  </div>
                </div>

                {/* Glow Effect */}
                <div className="absolute inset-0 rounded-[2.5rem] bg-gradient-to-r from-primary/20 to-rose-500/20 blur-2xl -z-10 scale-110"></div>
              </div>
            </motion.div>
          </motion.div>
        </div>
      </motion.div>
    </div>
  );
}
