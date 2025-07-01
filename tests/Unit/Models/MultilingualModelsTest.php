<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Rachma;
use App\Models\Category;
use App\Models\Part;
use App\Models\PartsSuggestion;
use App\Models\User;
use App\Models\Designer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MultilingualModelsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function rachma_model_returns_correct_localized_attributes()
    {
        // Create test designer
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);

        $rachma = Rachma::create([
            'designer_id' => $designer->id,
            'title' => 'Original Title',
            'title_ar' => 'العنوان العربي',
            'title_fr' => 'Titre Français',
            'description' => 'Original Description',
            'description_ar' => 'الوصف العربي',
            'description_fr' => 'Description Française',
            'file_path' => 'test/file.dst',
            'files' => [['id' => 1, 'path' => 'test.dst', 'is_primary' => true]],
            'preview_images' => ['test.jpg'],
            'size' => '20x25 cm',
            'width' => 20,
            'height' => 25,
            'gharazat' => 10000,
            'color_numbers' => ['001'],
            'price' => 5000,
            'original_price' => 5000,
        ]);

        // Test Arabic locale
        app()->setLocale('ar');
        $this->assertEquals('العنوان العربي', $rachma->localized_title);
        $this->assertEquals('الوصف العربي', $rachma->localized_description);

        // Test French locale
        app()->setLocale('fr');
        $this->assertEquals('Titre Français', $rachma->localized_title);
        $this->assertEquals('Description Française', $rachma->localized_description);

        // Test fallback to Arabic when locale not available
        app()->setLocale('en');
        $this->assertEquals('العنوان العربي', $rachma->localized_title);
        $this->assertEquals('الوصف العربي', $rachma->localized_description);
    }

    /** @test */
    public function rachma_model_returns_formatted_size()
    {
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);

        $rachma = Rachma::create([
            'designer_id' => $designer->id,
            'title' => 'Test',
            'file_path' => 'test.dst',
            'files' => [['id' => 1, 'path' => 'test.dst', 'is_primary' => true]],
            'preview_images' => ['test.jpg'],
            'width' => 30,
            'height' => 40,
            'gharazat' => 10000,
            'color_numbers' => ['001'],
            'price' => 5000,
            'original_price' => 5000,
        ]);

        $this->assertEquals('30 x 40 cm', $rachma->formatted_size);
    }

    /** @test */
    public function rachma_model_falls_back_to_size_field_when_dimensions_not_available()
    {
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);

        $rachma = Rachma::create([
            'designer_id' => $designer->id,
            'title' => 'Test',
            'file_path' => 'test.dst',
            'files' => [['id' => 1, 'path' => 'test.dst', 'is_primary' => true]],
            'preview_images' => ['test.jpg'],
            'size' => '25x35 cm',
            'gharazat' => 10000,
            'color_numbers' => ['001'],
            'price' => 5000,
            'original_price' => 5000,
        ]);

        $this->assertEquals('25x35 cm', $rachma->formatted_size);
    }

    /** @test */
    public function category_model_returns_correct_localized_name()
    {
        $category = Category::create([
            'name' => 'Original Name',
            'name_ar' => 'الاسم العربي',
            'name_fr' => 'Nom Français',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        // Test Arabic locale
        app()->setLocale('ar');
        $this->assertEquals('الاسم العربي', $category->localized_name);

        // Test French locale
        app()->setLocale('fr');
        $this->assertEquals('Nom Français', $category->localized_name);

        // Test fallback
        app()->setLocale('en');
        $this->assertEquals('الاسم العربي', $category->localized_name);
    }

    /** @test */
    public function part_model_returns_correct_localized_name()
    {
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);
        
        $rachma = Rachma::factory()->create(['designer_id' => $designer->id]);

        $part = Part::create([
            'rachma_id' => $rachma->id,
            'name' => 'Original Name',
            'name_ar' => 'الاسم العربي',
            'name_fr' => 'Nom Français',
            'length' => 10,
            'height' => 15,
            'stitches' => 2000,
            'order' => 1,
        ]);

        // Test Arabic locale
        app()->setLocale('ar');
        $this->assertEquals('الاسم العربي', $part->localized_name);

        // Test French locale
        app()->setLocale('fr');
        $this->assertEquals('Nom Français', $part->localized_name);

        // Test fallback
        app()->setLocale('en');
        $this->assertEquals('الاسم العربي', $part->localized_name);
    }

    /** @test */
    public function parts_suggestion_model_returns_correct_localized_name()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $suggestion = PartsSuggestion::create([
            'name_ar' => 'الاقتراح العربي',
            'name_fr' => 'Suggestion Française',
            'admin_id' => $admin->id,
            'is_active' => true,
        ]);

        // Test Arabic locale
        app()->setLocale('ar');
        $this->assertEquals('الاقتراح العربي', $suggestion->localized_name);

        // Test French locale
        app()->setLocale('fr');
        $this->assertEquals('Suggestion Française', $suggestion->localized_name);

        // Test fallback to Arabic
        app()->setLocale('en');
        $this->assertEquals('الاقتراح العربي', $suggestion->localized_name);
    }

    /** @test */
    public function models_handle_missing_translations_gracefully()
    {
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);

        // Create rachma with only Arabic title
        $rachma = Rachma::create([
            'designer_id' => $designer->id,
            'title_ar' => 'العنوان العربي فقط',
            'file_path' => 'test.dst',
            'files' => [['id' => 1, 'path' => 'test.dst', 'is_primary' => true]],
            'preview_images' => ['test.jpg'],
            'size' => '20x25 cm',
            'gharazat' => 10000,
            'color_numbers' => ['001'],
            'price' => 5000,
            'original_price' => 5000,
        ]);

        // Should fallback to Arabic even when requesting French
        app()->setLocale('fr');
        $this->assertEquals('العنوان العربي فقط', $rachma->localized_title);
    }

    /** @test */
    public function models_use_default_name_attribute_correctly()
    {
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);

        $rachma = Rachma::create([
            'designer_id' => $designer->id,
            'title' => 'Original Title',
            'title_ar' => 'العنوان العربي',
            'file_path' => 'test.dst',
            'files' => [['id' => 1, 'path' => 'test.dst', 'is_primary' => true]],
            'preview_images' => ['test.jpg'],
            'size' => '20x25 cm',
            'gharazat' => 10000,
            'color_numbers' => ['001'],
            'price' => 5000,
            'original_price' => 5000,
        ]);

        // The title attribute should return Arabic version
        $this->assertEquals('العنوان العربي', $rachma->title);
    }

    /** @test */
    public function category_name_attribute_defaults_to_arabic()
    {
        $category = Category::create([
            'name' => 'Original Name',
            'name_ar' => 'الاسم العربي',
            'name_fr' => 'Nom Français',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        // The name attribute should return Arabic version
        $this->assertEquals('الاسم العربي', $category->name);
    }

    /** @test */
    public function part_name_attribute_defaults_to_arabic()
    {
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);
        $rachma = Rachma::factory()->create(['designer_id' => $designer->id]);

        $part = Part::create([
            'rachma_id' => $rachma->id,
            'name' => 'Original Name',
            'name_ar' => 'الاسم العربي',
            'name_fr' => 'Nom Français',
            'length' => 10,
            'height' => 15,
            'stitches' => 2000,
            'order' => 1,
        ]);

        // The name attribute should return Arabic version
        $this->assertEquals('الاسم العربي', $part->name);
    }
}
