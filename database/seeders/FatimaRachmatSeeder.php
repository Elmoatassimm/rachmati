<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rachma;
use App\Models\Designer;
use App\Models\Category;
use App\Models\Part;
use Illuminate\Support\Str;

class FatimaRachmatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find Fatima's designer account
        $designer = Designer::whereHas('user', function($q) {
            $q->where('email', 'fatima@designer.com');
        })->first();

        if (!$designer) {
            $this->command->error('Fatima designer account not found!');
            return;
        }

        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->command->error('No categories found. Please run CategorySeeder first.');
            return;
        }

        $this->command->info("Creating 200 rachmat for Fatima (Designer ID: {$designer->id})...");

        // Create 200 rachmat for Fatima
        for ($i = 1; $i <= 200; $i++) {
            $this->createRachmaForFatima($designer, $categories, $i);
            
            if ($i % 20 == 0) {
                $this->command->info("Created {$i}/200 rachmat...");
            }
        }

        $this->command->info('✅ Successfully created 200 rachmat for Fatima!');
        $this->command->info('Total rachmat for Fatima: ' . Rachma::where('designer_id', $designer->id)->count());
    }

    private function createRachmaForFatima(Designer $designer, $categories, int $index): void
    {
        $titlePrefixes = [
            'رشمة', 'تطريز', 'نقش', 'زخرفة', 'رسمة', 'خياطة', 'تصميم', 'فن', 'إبداع', 'روعة'
        ];
        
        $titleTypes = [
            'كلاسيكية', 'عصرية', 'فاخرة', 'بسيطة', 'أنيقة', 'جميلة', 'رائعة', 'مذهلة',
            'تقليدية', 'حديثة', 'فريدة', 'مميزة', 'استثنائية', 'رقيقة', 'ساحرة', 'متقنة',
            'راقية', 'مبهرة', 'لامعة', 'ذهبية', 'فضية', 'ملكية', 'أميرية', 'عربية'
        ];

        $titleDescriptors = [
            'الورود', 'الزهور', 'النجوم', 'القلوب', 'الفراشات', 'الطيور', 'الأشجار',
            'البحر', 'الطبيعة', 'الحيوانات', 'الأطفال', 'العرائس', 'الأعياد', 'رمضان',
            'الزفاف', 'المناسبات', 'الديكور', 'المطبخ', 'الحقائب', 'الملابس', 'الصيف',
            'الربيع', 'الخريف', 'الشتاء', 'القمر', 'الشمس', 'النهار', 'الليل', 'الأحلام'
        ];

        $prefix = $titlePrefixes[array_rand($titlePrefixes)];
        $type = $titleTypes[array_rand($titleTypes)];
        $descriptor = $titleDescriptors[array_rand($titleDescriptors)];
        
        $titleAr = "فاطمة - {$prefix} {$descriptor} {$type} رقم {$index}";
        $titleFr = $this->generateFrenchTitle($titleAr, $index);
        
        $descriptionAr = $this->generateArabicDescription($titleAr);
        $descriptionFr = $this->generateFrenchDescription($descriptionAr);

        $category = $categories->random();
        $dimensions = $this->getRandomDimensions();
        $files = $this->createFilesArray($index);

        $rachma = Rachma::create([
            'designer_id' => $designer->id,
            'title_ar' => $titleAr,
            'title_fr' => $titleFr,
            'description_ar' => $descriptionAr,
            'description_fr' => $descriptionFr,
            'files' => $files,
            'preview_images' => $this->createPreviewImages($index),
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'gharazat' => rand(8000, 35000),
            'color_numbers' => $this->getRandomColors(),
            'price' => rand(2500, 25000),
            'average_rating' => round(rand(35, 50) / 10, 1),
            'ratings_count' => rand(1, 25),
            'created_at' => now()->subDays(rand(0, 90)),
        ]);

        // Attach category
        $rachma->categories()->attach($category->id);

        // Attach additional random categories (30% chance)
        if (rand(1, 100) <= 30) {
            $additionalCategory = $categories->where('id', '!=', $category->id)->random();
            $rachma->categories()->attach($additionalCategory->id);
        }

        // Create parts for this rachma
        $this->createPartsForRachma($rachma, $index);
    }

    private function generateFrenchTitle(string $arabicTitle, int $index): string
    {
        $frenchPrefixes = [
            'Rachmat', 'Broderie', 'Design', 'Art', 'Création', 'Beauté'
        ];
        
        $frenchTypes = [
            'Classique', 'Moderne', 'Luxueux', 'Simple', 'Élégant', 'Belle',
            'Magnifique', 'Unique', 'Spécial', 'Traditionnel', 'Contemporain'
        ];

        $prefix = $frenchPrefixes[array_rand($frenchPrefixes)];
        $type = $frenchTypes[array_rand($frenchTypes)];
        
        return "Fatima - {$prefix} {$type} #{$index}";
    }

    private function generateArabicDescription(string $title): string
    {
        $descriptions = [
            "تصميم رائع ومميز من إبداع المصممة فاطمة",
            "رشمة فاخرة بتفاصيل دقيقة وألوان متناسقة",
            "إبداع تراثي أصيل بلمسة عصرية مبتكرة",
            "تطريز متقن يجمع بين الأناقة والجمال",
            "قطعة فنية استثنائية تناسب جميع المناسبات",
            "تصميم أنيق يعكس الذوق الرفيع والحس الفني",
            "رشمة مميزة بخامات عالية الجودة",
            "إبداع يحمل روح التراث الجزائري الأصيل"
        ];

        return $descriptions[array_rand($descriptions)] . " - " . $title;
    }

    private function generateFrenchDescription(string $arabicDescription): string
    {
        $descriptions = [
            "Design magnifique et distinctif de la créatrice Fatima",
            "Rachmat luxueux avec des détails précis et des couleurs harmonieuses",
            "Création patrimoniale authentique avec une touche moderne innovante",
            "Broderie parfaite alliant élégance et beauté",
            "Pièce artistique exceptionnelle adaptée à toutes les occasions",
            "Design élégant reflétant un goût raffiné et un sens artistique",
            "Rachmat distinctif avec des matériaux de haute qualité",
            "Création portant l'esprit du patrimoine algérien authentique"
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function getRandomDimensions(): array
    {
        $dimensions = [
            ['width' => 15, 'height' => 20],
            ['width' => 20, 'height' => 25],
            ['width' => 25, 'height' => 30],
            ['width' => 30, 'height' => 35],
            ['width' => 35, 'height' => 40],
            ['width' => 40, 'height' => 45],
            ['width' => 45, 'height' => 50],
            ['width' => 50, 'height' => 55],
            ['width' => 55, 'height' => 60],
            ['width' => 60, 'height' => 65],
            ['width' => 65, 'height' => 70],
            ['width' => 70, 'height' => 75],
        ];

        return $dimensions[array_rand($dimensions)];
    }

    private function getRandomColors(): array
    {
        $colors = [
            '001', '002', '003', '004', '005', '025', '150', '208', '310', '340', 
            '666', '700', '817', '900', '123', '456', '789', '321', '654', '987'
        ];
        $numColors = rand(3, 8);
        shuffle($colors);
        return array_slice($colors, 0, $numColors);
    }

    private function createFilesArray(int $index): array
    {
        return [
            [
                'id' => 1,
                'path' => 'rachmat/files/sample_rachma.dst',
                'original_name' => "fatima_rachma_{$index}.dst",
                'format' => 'DST',
                'size' => filesize(storage_path('app/public/rachmat/files/sample_rachma.dst')),
                'is_primary' => true,
                'uploaded_at' => now()->toISOString(),
                'description' => 'Fichier principal de broderie DST'
            ],
            [
                'id' => 2,
                'path' => 'rachmat/files/sample_rachma.dst',
                'original_name' => "fatima_rachma_{$index}.pes",
                'format' => 'PES',
                'size' => rand(50000, 200000),
                'is_primary' => false,
                'uploaded_at' => now()->toISOString(),
                'description' => 'Fichier broderie PES compatible'
            ],
            [
                'id' => 3,
                'path' => 'rachmat/files/sample_rachma.dst',
                'original_name' => "fatima_rachma_{$index}.pdf",
                'format' => 'PDF',
                'size' => rand(100000, 500000),
                'is_primary' => false,
                'uploaded_at' => now()->toISOString(),
                'description' => 'Documentation du motif'
            ]
        ];
    }

    private function createPreviewImages(int $index): array
    {
        return [
            "rachmat/preview_images/monterey.png",
            "rachmat/preview_images/monterey.png", 
            "rachmat/preview_images/monterey.png"
        ];
    }

    private function createPartsForRachma(Rachma $rachma, int $index): void
    {
        $numParts = rand(2, 6);
        $partNames = [
            'الوسط', 'الحافة', 'الإطار', 'الزاوية', 'التفاصيل', 'الخط الخارجي',
            'التعبئة', 'الزخرفة', 'النقش', 'التطريز', 'الورود', 'الأوراق'
        ];

        $frenchPartNames = [
            'Centre', 'Bordure', 'Cadre', 'Coin', 'Détails', 'Contour',
            'Remplissage', 'Décoration', 'Motif', 'Broderie', 'Roses', 'Feuilles'
        ];

        for ($i = 1; $i <= $numParts; $i++) {
            $randomIndex = array_rand($partNames);
            $arabicName = $partNames[$randomIndex] . " رقم {$i}";
            $frenchName = $frenchPartNames[$randomIndex] . " #{$i}";

            Part::create([
                'rachma_id' => $rachma->id,
                'name_ar' => $arabicName,
                'name_fr' => $frenchName,
                'length' => rand(50, 300) / 10, // 5.0 to 30.0 cm
                'height' => rand(50, 300) / 10, // 5.0 to 30.0 cm
                'stitches' => rand(500, 8000),
                'order' => $i,
            ]);
        }
    }
} 