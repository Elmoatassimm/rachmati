import { Button } from '@/components/ui/button';
import { Check } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { PricingPlan } from '@/types';

interface PricingFourProps {
  pricingPlans?: PricingPlan[];
}

const defaultFeatures = [
  'رفع وبيع تصاميم التطريز',
  'عرض المتجر الشخصي',
  'إدارة الطلبات والمبيعات',
  'دعم فني متواصل',
];

const designerBenefits = [
  {
    name: 'مصممين محترفين',
    logo: '/logo.png',
    height: 20,
  },
  {
    name: 'تصاميم عالية الجودة',
    logo: '/logo.png',
    height: 16,
  },
  {
    name: 'توصيل سريع',
    logo: '/logo.png',
    height: 16,
  },
  {
    name: 'أسعار تنافسية',
    logo: '/logo.png',
    height: 20,
  },
];

export default function PricingFour({ pricingPlans = [] }: PricingFourProps) {
  // Use the first active plan or create a default one
  const plan = pricingPlans.length > 0 ? pricingPlans[0] : {
    id: 1,
    name: 'الخطة الشهرية',
    price: 2000,
    duration_months: 1,
    description: 'للمصممين الجدد والمحترفين',
    formatted_price: '2,000 دج',
    duration_text: 'شهر واحد',
    is_active: true,
    created_at: '',
    updated_at: '',
  };

  return (
    <div className="relative w-full py-16 md:py-32" dir="rtl" id="pricing">
      {/* Subtle local background enhancement */}
      <div className="absolute inset-0 -z-10 overflow-hidden">
        <div className="absolute top-1/3 left-1/2 -translate-x-1/2 h-[300px] w-[400px] rounded-full bg-gradient-to-br from-primary/6 via-primary/3 to-transparent blur-3xl opacity-70" />
        <div className="absolute bottom-1/4 right-1/4 h-[250px] w-[250px] rounded-full bg-gradient-to-tl from-rose-500/4 via-rose-500/2 to-transparent blur-3xl opacity-60" />
      </div>

      <div className="mx-auto max-w-5xl px-6">
        <div className="mx-auto max-w-2xl text-center">
          <h2 className="text-balance text-3xl font-extrabold md:text-4xl lg:text-5xl font-arabic">
            انضم إلى منصة رشماتي كمصمم محترف
          </h2>
          <p className="mt-4 text-lg text-muted-foreground">
            ابدأ رحلتك في عالم التطريز الرقمي واعرض تصاميمك لآلاف العملاء. 
            خطط اشتراك مرنة ومناسبة لجميع المصممين.
          </p>
        </div>
        <div className="mt-10 md:mt-20">
          <div className="relative rounded-3xl border border-border bg-card shadow-xl shadow-primary/5 backdrop-blur-sm">
            <div className="grid items-center gap-12 divide-y divide-border p-12 md:grid-cols-2 md:gap-x-2 md:divide-x-0 md:divide-y-0">
              {/* Right Side - Pricing */}
              <div className="pb-12 text-center md:pb-0 md:pl-12">
                <h3 className="text-2xl font-semibold font-arabic">{plan.name}</h3>
                <p className="mt-2 text-lg text-muted-foreground">{plan.description}</p>
                <span className="mb-6 mt-12 inline-block text-6xl font-extrabold text-primary">
                  {plan.formatted_price || `${plan.price} دج`}
                </span>
                <div className="text-sm text-muted-foreground mb-6">
                  لمدة {plan.duration_text || `${plan.duration_months} شهر`}
                </div>
                <div className="flex justify-center">
                  <Button asChild size="lg" className="shadow-md">
                    <Link href="/register">ابدأ الآن</Link>
                  </Button>
                </div>
                <p className="mt-12 text-sm text-muted-foreground">
                  يشمل: جميع المميزات الأساسية للمصممين
                </p>
              </div>

              {/* Left Side - Features */}
              <div className="relative m-3">
                <div className="text-right">
                  <h4 className="mb-4 text-lg font-medium font-arabic">ما يشمله الاشتراك:</h4>
                  <ul role="list" className="space-y-4">
                    {defaultFeatures.map((feature, index) => (
                      <li
                        key={index}
                        className="flex items-start gap-3 text-sm"
                      >
                        <Check className="mt-1 size-4 text-primary flex-shrink-0" />
                        <span className="text-right">{feature}</span>
                      </li>
                    ))}
                  </ul>
                </div>
                <p className="mt-6 text-sm text-muted-foreground text-right">
                  يمكنك إلغاء الاشتراك في أي وقت. المصممون الذين يستخدمون منصتنا:
                </p>
                <div className="mt-8 flex flex-wrap items-center justify-end gap-6">
                  {designerBenefits.map((benefit, i) => (
                    <div key={i} className="flex items-center gap-2">
                      <img
                        className="h-5 w-5 object-contain"
                        src={benefit.logo}
                        alt={`${benefit.name}`}
                        height={benefit.height}
                        width="auto"
                        loading="lazy"
                      />
                      <span className="text-xs text-muted-foreground">{benefit.name}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
