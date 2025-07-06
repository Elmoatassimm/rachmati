<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories first (safe for foreign key constraints)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Category::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            // For SQLite - use delete instead of truncate for foreign key safety
            Category::query()->delete();
        }

        $categories = [
            // Traditional Categories
            [
                'name_ar' => 'الرشمات التقليدية',
                'name_fr' => 'Rachmat Traditionnels',
            ],
            [
                'name_ar' => 'الرشمات العصرية',
                'name_fr' => 'Rachmat Modernes',
            ],
            [
                'name_ar' => 'رشمات الأطفال',
                'name_fr' => 'Rachmat pour Enfants',
            ],
            [
                'name_ar' => 'رشمات المناسبات',
                'name_fr' => 'Rachmat d\'Occasions',
            ],
            [
                'name_ar' => 'رشمات الطبيعة',
                'name_fr' => 'Rachmat de la Nature',
            ],
            [
                'name_ar' => 'رشمات الديكور',
                'name_fr' => 'Rachmat de Décoration',
            ],
            [
                'name_ar' => 'رشمات الحيوانات',
                'name_fr' => 'Rachmat d\'Animaux',
            ],
            [
                'name_ar' => 'رشمات الزهور',
                'name_fr' => 'Rachmat de Fleurs',
            ],
            [
                'name_ar' => 'رشمات هندسية',
                'name_fr' => 'Rachmat Géométriques',
            ],
            [
                'name_ar' => 'رشمات دينية',
                'name_fr' => 'Rachmat Religieux',
            ],
            [
                'name_ar' => 'رشمات الأعراس',
                'name_fr' => 'Rachmat de Mariage',
            ],
            [
                'name_ar' => 'رشمات المطبخ',
                'name_fr' => 'Rachmat de Cuisine',
            ],
            // Additional categories for pagination testing
            [
                'name_ar' => 'رشمات الأزياء الشعبية',
                'name_fr' => 'Rachmat de Mode Populaire',
            ],
            [
                'name_ar' => 'رشمات التراث الجزائري',
                'name_fr' => 'Rachmat du Patrimoine Algérien',
            ],
            [
                'name_ar' => 'رشمات الأعياد الدينية',
                'name_fr' => 'Rachmat des Fêtes Religieuses',
            ],
            [
                'name_ar' => 'رشمات الطيور',
                'name_fr' => 'Rachmat d\'Oiseaux',
            ],
            [
                'name_ar' => 'رشمات البحر',
                'name_fr' => 'Rachmat de la Mer',
            ],
            [
                'name_ar' => 'رشمات الفراشات',
                'name_fr' => 'Rachmat de Papillons',
            ],
            [
                'name_ar' => 'رشمات النجوم والأقمار',
                'name_fr' => 'Rachmat d\'Étoiles et Lunes',
            ],
            [
                'name_ar' => 'رشمات الأشكال الشرقية',
                'name_fr' => 'Rachmat de Formes Orientales',
            ],
            [
                'name_ar' => 'رشمات الطاولات',
                'name_fr' => 'Rachmat de Tables',
            ],
            [
                'name_ar' => 'رشمات الوسائد',
                'name_fr' => 'Rachmat de Coussins',
            ],
            [
                'name_ar' => 'رشمات الستائر',
                'name_fr' => 'Rachmat de Rideaux',
            ],
            [
                'name_ar' => 'رشمات الملابس النسائية',
                'name_fr' => 'Rachmat de Vêtements Féminins',
            ],
            [
                'name_ar' => 'رشمات الملابس الرجالية',
                'name_fr' => 'Rachmat de Vêtements Masculins',
            ],
            [
                'name_ar' => 'رشمات الحقائب',
                'name_fr' => 'Rachmat de Sacs',
            ],
            [
                'name_ar' => 'رشمات الأحذية',
                'name_fr' => 'Rachmat de Chaussures',
            ],
            [
                'name_ar' => 'رشمات الإكسسوارات',
                'name_fr' => 'Rachmat d\'Accessoires',
            ],
            [
                'name_ar' => 'رشمات القبعات',
                'name_fr' => 'Rachmat de Chapeaux',
            ],
            [
                'name_ar' => 'رشمات الشالات',
                'name_fr' => 'Rachmat de Châles',
            ],
            [
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],[
                'name_ar' => 'رشمات العباءات',
                'name_fr' => 'Rachmat d\'Abayas',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        $this->command->info('Categories created successfully!');
        $this->command->info('- Total Categories: ' . Category::count());
    }
}
