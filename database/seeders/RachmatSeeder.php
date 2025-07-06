<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rachma;
use App\Models\Designer;
use App\Models\Category;
use App\Models\Part;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class RachmatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active designers, or all designers if no active ones exist
        $designers = Designer::where('subscription_status', 'active')->get();
        if ($designers->isEmpty()) {
            $designers = Designer::all();
        }

        // If still no designers, create some basic ones
        if ($designers->isEmpty()) {
            $this->command->warn('No designers found. Please run DesignerSeeder first.');
            return;
        }

        $categories = Category::all();

        // If no categories, create some basic ones
        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        // Create sample rachmat with specific data
        $this->createSampleRachmat($designers, $categories);
        
        // Create many more rachmat for pagination testing
        $this->createBulkRachmat($designers, $categories, 100);

        $this->command->info('Rachmat created successfully with all required fields!');
        $this->command->info('Total Rachmat: ' . \App\Models\Rachma::count());
    }

    private function createSampleRachmat($designers, $categories): void
    {
        $rachmatData = [
            'رشمة قسنطينية كلاسيكية' => ['price' => 8500, 'category' => 'الرشمات التقليدية'],
            'رشمة تلمسانية فاخرة' => ['price' => 12000, 'category' => 'الرشمات التقليدية'],
            'رشمة عنابية عصرية' => ['price' => 7500, 'category' => 'الرشمات التقليدية'],
            'رشمة هندسية معاصرة' => ['price' => 6500, 'category' => 'الرشمات العصرية'],
            'رشمة مجردة فنية' => ['price' => 9200, 'category' => 'الرشمات العصرية'],
            'رشمة بسيطة أنيقة' => ['price' => 4500, 'category' => 'الرشمات العصرية'],
            'رشمة الحيوانات المرحة' => ['price' => 3500, 'category' => 'رشمات الأطفال'],
            'رشمة الشخصيات الكرتونية' => ['price' => 4200, 'category' => 'رشمات الأطفال'],
            'رشمة الزفاف الملكية' => ['price' => 25000, 'category' => 'رشمات المناسبات'],
            'رشمة العيد المباركة' => ['price' => 6800, 'category' => 'رشمات المناسبات'],
            'رشمة رمضان الكريم' => ['price' => 5900, 'category' => 'رشمات المناسبات'],
            'رشمة الورود الدمشقية' => ['price' => 7200, 'category' => 'رشمات الطبيعة'],
            'رشمة الأشجار الخضراء' => ['price' => 6300, 'category' => 'رشمات الطبيعة'],
            'رشمة البحر الأزرق' => ['price' => 8100, 'category' => 'رشمات الطبيعة'],
        ];

        foreach ($rachmatData as $title => $data) {
            $this->createSingleRachma($title, $data, $designers, $categories);
        }
    }

    private function createBulkRachmat($designers, $categories, int $count): void
    {
        $titlePrefixes = [
            'رشمة', 'تطريز', 'نقش', 'زخرفة', 'رسمة', 'خياطة', 'تصميم', 'فن'
        ];
        
        $titleTypes = [
            'كلاسيكية', 'عصرية', 'فاخرة', 'بسيطة', 'أنيقة', 'جميلة', 'رائعة', 'مذهلة',
            'تقليدية', 'حديثة', 'فريدة', 'مميزة', 'استثنائية', 'رقيقة', 'ساحرة', 'متقنة'
        ];

        $titleDescriptors = [
            'الورود', 'الزهور', 'النجوم', 'القلوب', 'الفراشات', 'الطيور', 'الأشجار',
            'البحر', 'الطبيعة', 'الحيوانات', 'الأطفال', 'العرائس', 'الأعياد', 'رمضان',
            'الزفاف', 'المناسبات', 'الديكور', 'المطبخ', 'الحقائب', 'الملابس'
        ];

        for ($i = 1; $i <= $count; $i++) {
            $prefix = $titlePrefixes[array_rand($titlePrefixes)];
            $type = $titleTypes[array_rand($titleTypes)];
            $descriptor = $titleDescriptors[array_rand($titleDescriptors)];
            
            $title = "{$prefix} {$descriptor} {$type} {$i}";
            $category = $categories->random();
            
            $data = [
                'price' => rand(2000, 30000),
                'category' => $category->name_ar
            ];

            $this->createSingleRachma($title, $data, $designers, $categories);
        }
    }

    private function createSingleRachma(string $title, array $data, $designers, $categories): void
    {
        $designer = $designers->random();
        $category = $categories->where('name_ar', $data['category'])->first();

        $slug = \Illuminate\Support\Str::slug($title);

        // Create files array for multi-file support
        $files = $this->createFilesForRachma($slug);

        $description = $this->generateDescription($title);
        $frenchTitle = $this->generateFrenchTitle($title);
        $frenchDescription = $this->generateFrenchDescription($description);
      
        $rachma = Rachma::create([
            'designer_id' => $designer->id,
            'title_ar' => $title,
            'title_fr' => $frenchTitle,
            'description_ar' => $description,
            'description_fr' => $frenchDescription,
            'files' => $files,
            'preview_images' => $this->createPreviewImages(1),
            'color_numbers' => $this->getRandomColors(),
            'price' => $data['price'],
            'is_active' => true,
        ]);

        // Attach category using many-to-many relationship
        if ($category) {
            $rachma->categories()->attach($category->id);
        }

        // Optionally attach additional random categories (1-3 total categories)
        $availableCategories = $categories->where('id', '!=', $category?->id);
        if ($availableCategories->count() > 0) {
            $numAdditional = min(rand(0, 2), $availableCategories->count());
            if ($numAdditional > 0) {
                $additionalCategories = $availableCategories->random($numAdditional);
                foreach ($additionalCategories as $additionalCategory) {
                    $rachma->categories()->attach($additionalCategory->id);
                }
            }
        }

        // Create parts for this rachma
        $this->createPartsForRachma($rachma);
    }

    private function generateDescription($title): string
    {
        return "تصميم رائع ومميز " . $title . " بجودة عالية وتفاصيل دقيقة تناسب جميع الأذواق والمناسبات";
    }

    private function generateFrenchTitle($arabicTitle): string
    {
        $translations = [
            'رشمة قسنطينية كلاسيكية' => 'Rachmat Constantinois Classique',
            'رشمة تلمسانية فاخرة' => 'Rachmat Tlemcenien de Luxe',
            'رشمة عنابية عصرية' => 'Rachmat Annabi Moderne',
            'رشمة هندسية معاصرة' => 'Rachmat Géométrique Contemporain',
            'رشمة مجردة فنية' => 'Rachmat Abstrait Artistique',
            'رشمة بسيطة أنيقة' => 'Rachmat Simple et Élégant',
            'رشمة الحيوانات المرحة' => 'Rachmat Animaux Joyeux',
            'رشمة الشخصيات الكرتونية' => 'Rachmat Personnages de Dessins Animés',
            'رشمة الزفاف الملكية' => 'Rachmat de Mariage Royal',
            'رشمة العيد المباركة' => 'Rachmat de Fête Bénie',
            'رشمة رمضان الكريم' => 'Rachmat du Ramadan Généreux',
            'رشمة الورود الدمشقية' => 'Rachmat Roses de Damas',
            'رشمة الأشجار الخضراء' => 'Rachmat Arbres Verts',
            'رشمة البحر الأزرق' => 'Rachmat Mer Bleue',
        ];

        // If translation exists, use it
        if (isset($translations[$arabicTitle])) {
            return $translations[$arabicTitle];
        }

        // For new generated titles, create a French version
        $wordTranslations = [
            'رشمة' => 'Rachmat',
            'تطريز' => 'Broderie',
            'نقش' => 'Gravure',
            'زخرفة' => 'Décoration',
            'رسمة' => 'Dessin',
            'خياطة' => 'Couture',
            'تصميم' => 'Design',
            'فن' => 'Art',
            'كلاسيكية' => 'Classique',
            'عصرية' => 'Moderne',
            'فاخرة' => 'Luxueux',
            'بسيطة' => 'Simple',
            'أنيقة' => 'Élégant',
            'جميلة' => 'Belle',
            'رائعة' => 'Magnifique',
            'مذهلة' => 'Étonnant',
            'تقليدية' => 'Traditionnel',
            'حديثة' => 'Contemporain',
            'فريدة' => 'Unique',
            'مميزة' => 'Spécial',
            'استثنائية' => 'Exceptionnel',
            'رقيقة' => 'Délicat',
            'ساحرة' => 'Charmant',
            'متقنة' => 'Parfait',
            'الورود' => 'des Roses',
            'الزهور' => 'des Fleurs',
            'النجوم' => 'des Étoiles',
            'القلوب' => 'des Cœurs',
            'الفراشات' => 'des Papillons',
            'الطيور' => 'des Oiseaux',
            'الأشجار' => 'des Arbres',
            'البحر' => 'de la Mer',
            'الطبيعة' => 'de la Nature',
            'الحيوانات' => 'des Animaux',
            'الأطفال' => 'des Enfants',
            'العرائس' => 'des Mariées',
            'الأعياد' => 'des Fêtes',
            'رمضان' => 'du Ramadan',
            'الزفاف' => 'de Mariage',
            'المناسبات' => 'des Occasions',
            'الديكور' => 'de Décoration',
            'المطبخ' => 'de Cuisine',
            'الحقائب' => 'de Sacs',
            'الملابس' => 'de Vêtements',
        ];

        // Try to translate word by word
        $translatedTitle = $arabicTitle;
        foreach ($wordTranslations as $arabicWord => $frenchWord) {
            $translatedTitle = str_replace($arabicWord, $frenchWord, $translatedTitle);
        }

        // Clean up and ensure it starts with a capital letter
        $translatedTitle = trim($translatedTitle);
        if (!preg_match('/^[A-Z]/', $translatedTitle)) {
            $translatedTitle = 'Rachmat ' . $translatedTitle;
        }

        return $translatedTitle;
    }

    private function generateFrenchDescription($arabicDescription): string
    {
        return "Design magnifique et distinctif avec une haute qualité et des détails précis qui conviennent à tous les goûts et occasions";
    }


    private function getRandomColors(): array
    {
        $colors = ['001', '002', '025', '150', '208', '310', '340', '666', '700', '817'];
        $numColors = rand(3, 6);
        return array_slice($colors, 0, $numColors);
    }

    /**
     * Create files array for multi-file support
     */
    private function createFilesForRachma(string $slug): array
    {
        $files = [];
        $fileId = 1;

        // Create multiple file formats for each rachma
        $formats = [
            ['ext' => 'dst', 'format' => 'DST', 'description' => 'Tajima DST embroidery file', 'is_primary' => true],
            ['ext' => 'pes', 'format' => 'PES', 'description' => 'Brother PES embroidery file', 'is_primary' => false],
            ['ext' => 'pdf', 'format' => 'PDF', 'description' => 'Pattern documentation', 'is_primary' => false],
        ];

        foreach ($formats as $format) {
            $files[] = [
                'id' => $fileId++,
                'path' => "rachmat/files/sample_rachma.dst", // Using monterey.png as base file
                'original_name' => "{$slug}.{$format['ext']}",
                'format' => $format['format'],
                'size' => rand(50000, 500000), // Random file size between 50KB and 500KB
                'is_primary' => $format['is_primary'],
                'uploaded_at' => now()->toISOString(),
                'description' => $format['description']
            ];
        }

        return $files;
    }

    private function createPartsForRachma(Rachma $rachma): void
    {
        $numParts = rand(2, 4);

        for ($i = 1; $i <= $numParts; $i++) {
            $arabicName = "جزء {$i} - {$rachma->title_ar}";
            $frenchName = "Partie {$i} - {$rachma->title_fr}";

            Part::create([
                'rachma_id' => $rachma->id,
                'name_ar' => $arabicName,
                'name_fr' => $frenchName,
                'length' => rand(50, 200) / 10, // 5.0 to 20.0 cm
                'height' => rand(50, 200) / 10, // 5.0 to 20.0 cm
                'stitches' => rand(1000, 5000),
                'order' => $i,
            ]);
        }
    }

    private function createPreviewImages(int $index): array
    {
        return [
            "rachmat/preview_images/monterey.png",
            "rachmat/preview_images/monterey.png", 
            "rachmat/preview_images/monterey.png"
        ];
    }
}