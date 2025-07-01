<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Designer;
use App\Models\DesignerSocialMedia;
use App\Models\PricingPlan;

class DesignerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designerUsers = User::where('user_type', 'designer')->get();
        $pricingPlans = PricingPlan::where('is_active', true)->get();

        $designerData = [
            [
                'store_name' => 'متجر فاطمة للرشمات التقليدية',
                'store_description' => 'متخصصون في الرشمات التقليدية الجزائرية بأجود الخامات وأروع التصاميم. نقدم رشمات أصيلة تعكس جمال التراث الجزائري.',
                'subscription_status' => 'active',
                'subscription_start_date' => now()->subDays(30),
                'subscription_end_date' => now()->addDays(335),
                'earnings' => 25000.00,
                'paid_earnings' => 18000.00,
                'social_media' => [
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/fatima.rachmat'],
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/fatima_rachmat'],
                    ['platform' => 'website', 'url' => 'https://fatima-rachmat.com'],
                ],
            ],
            [
                'store_name' => 'أحمد للتصاميم العصرية',
                'store_description' => 'تصاميم عصرية ومبتكرة تلبي احتياجات العصر الحديث. نجمع بين الأصالة والمعاصرة في تصاميم فريدة.',
                'subscription_status' => 'active',
                'subscription_start_date' => now()->subDays(45),
                'subscription_end_date' => now()->addDays(320),
                'earnings' => 31500.00,
                'paid_earnings' => 22000.00,
                'social_media' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/ahmed_modern_designs'],
                    ['platform' => 'website', 'url' => 'https://ahmed-designs.com'],
                ],
            ],
            [
                'store_name' => 'خديجة للفنون الراقية',
                'store_description' => 'فنون راقية ورشمات فاخرة للمناسبات الخاصة. نبدع في تصميم الرشمات التي تناسب جميع المناسبات والأذواق.',
                'subscription_status' => 'pending',
                'subscription_start_date' => null,
                'subscription_end_date' => null,
                'earnings' => 5500.00,
                'paid_earnings' => 0.00,
                'social_media' => [
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/khadija.arts'],
                ],
            ],
            [
                'store_name' => 'يوسف للإبداع الفني',
                'store_description' => 'إبداعات فنية متميزة ورشمات تحمل طابع الأصالة والجمال. متخصصون في الرشمات الفنية والتصاميم المميزة.',
                'subscription_status' => 'active',
                'subscription_start_date' => now()->subDays(15),
                'subscription_end_date' => now()->addDays(350),
                'earnings' => 18750.00,
                'paid_earnings' => 12000.00,
                'social_media' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/youssef_creativity'],
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/youssef.art'],
                    ['platform' => 'website', 'url' => 'https://youssef-art.com'],
                ],
            ],
            [
                'store_name' => 'نور الهدى للتصاميم النسائية',
                'store_description' => 'تصاميم نسائية أنيقة ورشمات تناسب جميع الأعمار. نهتم بالتفاصيل الدقيقة والجودة العالية في كل تصميم.',
                'subscription_status' => 'expired',
                'subscription_start_date' => now()->subDays(180),
                'subscription_end_date' => now()->subDays(15),
                'earnings' => 12800.00,
                'paid_earnings' => 8500.00,
                'social_media' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/nour_feminine_designs'],
                ],
            ],
        ];

        foreach ($designerUsers as $index => $user) {
            if (isset($designerData[$index])) {
                $data = $designerData[$index];
                $socialMediaData = $data['social_media'];
                unset($data['social_media']);

                $data['user_id'] = $user->id;

                // Assign pricing plan for active subscriptions
                if ($data['subscription_status'] === 'active') {
                    $pricingPlan = $pricingPlans->random();
                    $data['pricing_plan_id'] = $pricingPlan->id;
                    $data['subscription_price'] = $pricingPlan->price;

                    // Add payment proof for active subscriptions
                    $paymentProofs = [
                        'payment_proofs/ccp_receipt_1.jpg',
                        'payment_proofs/baridi_mob_1.jpg',
                        'payment_proofs/dahabiya_receipt_1.jpg',
                    ];
                    $data['payment_proof_path'] = $paymentProofs[array_rand($paymentProofs)];
                }

                $designer = Designer::create($data);

                // Create social media links
                foreach ($socialMediaData as $socialMedia) {
                    DesignerSocialMedia::create([
                        'designer_id' => $designer->id,
                        'platform' => $socialMedia['platform'],
                        'url' => $socialMedia['url'],
                    ]);
                }
            }
        }

        $this->command->info('Designer profiles and social media links created successfully!');
    }
}
