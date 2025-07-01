<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PartsSuggestion;
use App\Models\User;

class PartsSuggestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user
        $admin = User::where('user_type', 'admin')->first();
        
        if (!$admin) {
            $this->command->error('No admin user found! Please create an admin user first.');
            return;
        }

        $suggestions = [
            ['name_ar' => 'الوسط', 'name_fr' => 'Centre'],
            ['name_ar' => 'الحافة', 'name_fr' => 'Bordure'],
            ['name_ar' => 'الإطار', 'name_fr' => 'Cadre'],
            ['name_ar' => 'الطرف', 'name_fr' => 'Extrémité'],
            ['name_ar' => 'الزاوية', 'name_fr' => 'Coin'],
            ['name_ar' => 'الجزء العلوي', 'name_fr' => 'Partie supérieure'],
            ['name_ar' => 'الجزء السفلي', 'name_fr' => 'Partie inférieure'],
            ['name_ar' => 'الجانب الأيمن', 'name_fr' => 'Côté droit'],
            ['name_ar' => 'الجانب الأيسر', 'name_fr' => 'Côté gauche'],
            ['name_ar' => 'الوردة', 'name_fr' => 'Rose'],
            ['name_ar' => 'الورقة', 'name_fr' => 'Feuille'],
            ['name_ar' => 'الغصن', 'name_fr' => 'Branche'],
            ['name_ar' => 'الساق', 'name_fr' => 'Tige'],
            ['name_ar' => 'البتلة', 'name_fr' => 'Pétale'],
            ['name_ar' => 'التفاصيل', 'name_fr' => 'Détails'],
            ['name_ar' => 'الخط الخارجي', 'name_fr' => 'Contour'],
            ['name_ar' => 'التعبئة', 'name_fr' => 'Remplissage'],
            ['name_ar' => 'النص', 'name_fr' => 'Texte'],
            ['name_ar' => 'الحرف', 'name_fr' => 'Lettre'],
            ['name_ar' => 'الكلمة', 'name_fr' => 'Mot'],
            ['name_ar' => 'الرقم', 'name_fr' => 'Numéro'],
            ['name_ar' => 'النجمة', 'name_fr' => 'Étoile'],
            ['name_ar' => 'القلب', 'name_fr' => 'Cœur'],
            ['name_ar' => 'الدائرة', 'name_fr' => 'Cercle'],
            ['name_ar' => 'المربع', 'name_fr' => 'Carré'],
            ['name_ar' => 'المثلث', 'name_fr' => 'Triangle'],
            ['name_ar' => 'الخط', 'name_fr' => 'Ligne'],
            ['name_ar' => 'النقطة', 'name_fr' => 'Point'],
            ['name_ar' => 'الزخرفة', 'name_fr' => 'Décoration'],
            ['name_ar' => 'الطائر', 'name_fr' => 'Oiseau'],
            ['name_ar' => 'الفراشة', 'name_fr' => 'Papillon'],
            ['name_ar' => 'الحيوان', 'name_fr' => 'Animal'],
            ['name_ar' => 'الشجرة', 'name_fr' => 'Arbre'],
            ['name_ar' => 'الزهرة', 'name_fr' => 'Fleur'],
            ['name_ar' => 'العقد', 'name_fr' => 'Nœud'],
            ['name_ar' => 'الشريط', 'name_fr' => 'Ruban'],
            ['name_ar' => 'التاج', 'name_fr' => 'Couronne'],
            ['name_ar' => 'الجناح', 'name_fr' => 'Aile'],
            ['name_ar' => 'القوس', 'name_fr' => 'Arc'],
            ['name_ar' => 'الحلقة', 'name_fr' => 'Anneau'],
            ['name_ar' => 'الشعار', 'name_fr' => 'Logo'],
            ['name_ar' => 'الرمز', 'name_fr' => 'Symbole'],
            ['name_ar' => 'الظل', 'name_fr' => 'Ombre'],
            ['name_ar' => 'اللمعة', 'name_fr' => 'Brillance'],
            ['name_ar' => 'التدرج', 'name_fr' => 'Dégradé'],
        ];

        foreach ($suggestions as $suggestion) {
            PartsSuggestion::create([
                'name_ar' => $suggestion['name_ar'],
                'name_fr' => $suggestion['name_fr'],
                'admin_id' => $admin->id,
                'is_active' => true,
            ]);
        }

        $this->command->info('Parts suggestions seeded successfully!');
    }
}
