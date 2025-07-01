<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\User;
use App\Models\Rachma;
use App\Models\Designer;
use Carbon\Carbon;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = User::where('user_type', 'client')->get();
        $rachmat = Rachma::all();
        $designers = Designer::all();

        // Create comments for rachmat (70% of comments) - increased for pagination testing
        $rachmatComments = 150;
        for ($i = 0; $i < $rachmatComments; $i++) {
            $client = $clients->random();
            $rachma = $rachmat->random();
            
            Comment::create([
                'user_id' => $client->id,
                'target_id' => $rachma->id,
                'target_type' => 'rachma',
                'comment' => $this->getRandomRachmaComment(),
                'created_at' => Carbon::now()->subDays(rand(0, 90)),
                'updated_at' => Carbon::now()->subDays(rand(0, 90)),
            ]);
        }

        // Create comments for designer stores (30% of comments) - increased for pagination testing
        $storeComments = 50;
        for ($i = 0; $i < $storeComments; $i++) {
            $client = $clients->random();
            $designer = $designers->random();
            
            Comment::create([
                'user_id' => $client->id,
                'target_id' => $designer->id,
                'target_type' => 'store',
                'comment' => $this->getRandomStoreComment(),
                'created_at' => Carbon::now()->subDays(rand(0, 90)),
                'updated_at' => Carbon::now()->subDays(rand(0, 90)),
            ]);
        }

        $this->command->info('Comments created successfully!');
        $this->command->info('- Rachma comments: ' . $rachmatComments);
        $this->command->info('- Store comments: ' . $storeComments);
    }

    private function getRandomRachmaComment(): string
    {
        $comments = [
            'رشمة رائعة جداً! التفاصيل دقيقة والألوان متناسقة بشكل مثالي.',
            'أعجبتني كثيراً هذه الرشمة. سهلة التطبيق والنتيجة مذهلة.',
            'تصميم جميل ومتقن. أنصح بها بشدة لكل من يحب التطريز.',
            'رشمة تقليدية أصيلة. تذكرني بتطريز جدتي رحمها الله.',
            'ممتازة! الحجم مناسب والغرز واضحة.',
            'تصميم عصري وجذاب. تناسب الديكور الحديث.',
            'رشمة معقدة قليلاً لكن النتيجة تستحق الجهد.',
            'ألوان زاهية وتصميم مبتكر. أحببتها كثيراً.',
            'مناسبة للمبتدئين. تعليمات واضحة وسهلة الفهم.',
            'تصميم كلاسيكي لا يمل منه. جودة عالية.',
            'رشمة مميزة بتفاصيل دقيقة. تحتاج صبر لكن النتيجة رائعة.',
            'أجمل رشمة اشتريتها حتى الآن. تستحق كل دينار.',
            'تصميم أنيق ومناسب لجميع الأعمار.',
            'رشمة تراثية جميلة. تحافظ على الطابع الأصيل.',
            'سهلة التطبيق ومناسبة للمشاريع السريعة.',
            'تصميم مبدع ومختلف عن المعتاد.',
            'ألوان هادئة ومريحة للعين. أحببت التدرج.',
            'رشمة متوسطة الصعوبة. مناسبة لمن لديه خبرة بسيطة.',
            'تصميم عملي وجميل في نفس الوقت.',
            'رشمة مفصلة بعناية. كل غرزة في مكانها الصحيح.',
        ];

        return $comments[array_rand($comments)];
    }

    private function getRandomStoreComment(): string
    {
        $comments = [
            'متجر ممتاز! رشمات عالية الجودة وخدمة عملاء رائعة.',
            'أفضل متجر للرشمات التقليدية. تصاميم أصيلة ومتقنة.',
            'خدمة سريعة وتصاميم مبتكرة. أنصح بالتعامل معهم.',
            'متجر موثوق ورشمات متنوعة. أشتري منهم دائماً.',
            'تصاميم عصرية وأسعار معقولة. تجربة ممتازة.',
            'صاحب المتجر متعاون جداً ويقدم نصائح مفيدة.',
            'رشمات عالية الدقة وتسليم في الوقت المحدد.',
            'متجر يحترم العملاء ويقدم منتجات أصيلة.',
            'تشكيلة واسعة من الرشمات لجميع الأذواق.',
            'جودة ممتازة وأسعار تنافسية. راضية جداً.',
            'متجر احترافي بتصاميم مميزة وخدمة متميزة.',
            'أجمل الرشمات التراثية موجودة هنا.',
            'تعامل راقي ومنتجات تفوق التوقعات.',
            'متجر يستحق الثقة. كل مشترياتي منه مميزة.',
            'خدمة عملاء ممتازة ورد سريع على الاستفسارات.',
        ];

        return $comments[array_rand($comments)];
    }
}
