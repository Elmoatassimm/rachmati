<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Designer;
use App\Models\Rachma;
use App\Models\Category;
use App\Models\Part;
use App\Models\PartsSuggestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class MultilingualApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create admin user
        $admin = User::factory()->create(['user_type' => 'admin']);

        // Create designer
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create([
            'user_id' => $user->id,
            'subscription_status' => 'active',
        ]);

        // Create category with multilingual names
        $category = Category::create([
            'name' => 'Test Category',
            'name_ar' => 'فئة تجريبية',
            'name_fr' => 'Catégorie de Test',
            'slug' => 'test-category',
            'description' => 'Test category description',
            'is_active' => true,
        ]);

        // Create rachma with multilingual content
        $rachma = Rachma::create([
            'designer_id' => $designer->id,
            'title' => 'Test Rachma',
            'title_ar' => 'رشمة تجريبية',
            'title_fr' => 'Rachmat de Test',
            'description' => 'Test description',
            'description_ar' => 'وصف تجريبي',
            'description_fr' => 'Description de test',
            'file_path' => 'test/file.dst',
            'files' => [
                [
                    'id' => 1,
                    'path' => 'test/file.dst',
                    'original_name' => 'test.dst',
                    'format' => 'DST',
                    'is_primary' => true,
                    'uploaded_at' => now(),
                ]
            ],
            'preview_images' => ['test1.jpg', 'test2.jpg'],
            'size' => '20x25 cm',
            'width' => 20,
            'height' => 25,
            'gharazat' => 10000,
            'color_numbers' => ['001', '002', '003'],
            'price' => 5000,
            'original_price' => 5000,
            'is_active' => true,
        ]);

        // Attach category to rachma
        $rachma->categories()->attach($category->id);

        // Create part with multilingual names
        Part::create([
            'rachma_id' => $rachma->id,
            'name' => 'Test Part',
            'name_ar' => 'جزء تجريبي',
            'name_fr' => 'Partie de Test',
            'length' => 10.5,
            'height' => 15.0,
            'stitches' => 2500,
            'order' => 1,
        ]);

        // Create parts suggestion
        PartsSuggestion::create([
            'name_ar' => 'اقتراح تجريبي',
            'name_fr' => 'Suggestion de Test',
            'admin_id' => $admin->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_returns_rachmat_with_arabic_localization()
    {
        $response = $this->getJson('/api/rachmat?lang=ar');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'title_ar',
                                'title_fr',
                                'localized_title',
                                'description',
                                'description_ar',
                                'description_fr',
                                'localized_description',
                                'formatted_size',
                                'categories' => [
                                    '*' => [
                                        'id',
                                        'name',
                                        'name_ar',
                                        'name_fr',
                                        'localized_name',
                                    ]
                                ],
                                'parts' => [
                                    '*' => [
                                        'id',
                                        'name',
                                        'name_ar',
                                        'name_fr',
                                        'localized_name',
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'locale'
                ]);

        $data = $response->json('data.data.0');
        $this->assertEquals('ar', $response->json('locale'));
        $this->assertEquals('رشمة تجريبية', $data['localized_title']);
        $this->assertEquals('وصف تجريبي', $data['localized_description']);
        $this->assertEquals('فئة تجريبية', $data['categories'][0]['localized_name']);
        $this->assertEquals('جزء تجريبي', $data['parts'][0]['localized_name']);
    }

    /** @test */
    public function it_returns_rachmat_with_french_localization()
    {
        $response = $this->getJson('/api/rachmat?lang=fr');

        $response->assertStatus(200);

        $data = $response->json('data.data.0');
        $this->assertEquals('fr', $response->json('locale'));
        $this->assertEquals('Rachmat de Test', $data['localized_title']);
        $this->assertEquals('Description de test', $data['localized_description']);
        $this->assertEquals('Catégorie de Test', $data['categories'][0]['localized_name']);
        $this->assertEquals('Partie de Test', $data['parts'][0]['localized_name']);
    }

    /** @test */
    public function it_returns_categories_with_localization()
    {
        $response = $this->getJson('/api/categories?lang=ar');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'name_ar',
                            'name_fr',
                            'localized_name',
                        ]
                    ],
                    'locale'
                ]);

        $this->assertEquals('ar', $response->json('locale'));
    }

    /** @test */
    public function it_returns_popular_rachmat_with_localization()
    {
        $response = $this->getJson('/api/popular?lang=fr');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'localized_title',
                            'localized_description',
                            'formatted_size',
                            'categories' => [
                                '*' => [
                                    'localized_name',
                                ]
                            ]
                        ]
                    ],
                    'locale'
                ]);

        $this->assertEquals('fr', $response->json('locale'));
    }

    /** @test */
    public function it_returns_rachma_details_with_localization()
    {
        $rachma = Rachma::first();
        
        $response = $this->getJson("/api/rachmat/{$rachma->id}?lang=ar");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'rachma' => [
                            'localized_title',
                            'localized_description',
                            'formatted_size',
                            'categories' => [
                                '*' => [
                                    'localized_name',
                                ]
                            ],
                            'parts' => [
                                '*' => [
                                    'localized_name',
                                ]
                            ]
                        ],
                        'related_rachmat'
                    ],
                    'locale'
                ]);

        $data = $response->json('data.rachma');
        $this->assertEquals('ar', $response->json('locale'));
        $this->assertEquals('رشمة تجريبية', $data['localized_title']);
    }

    /** @test */
    public function it_returns_parts_suggestions_with_localization()
    {
        $response = $this->getJson('/api/parts-suggestions?lang=ar');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name_ar',
                            'name_fr',
                            'localized_name',
                            'display_name',
                        ]
                    ],
                    'locale'
                ]);

        $data = $response->json('data.0');
        $this->assertEquals('ar', $response->json('locale'));
        $this->assertEquals('اقتراح تجريبي', $data['localized_name']);
        $this->assertEquals('اقتراح تجريبي', $data['display_name']);
    }

    /** @test */
    public function it_defaults_to_arabic_when_no_language_specified()
    {
        $response = $this->getJson('/api/rachmat');

        $response->assertStatus(200);
        
        $data = $response->json('data.data.0');
        $this->assertEquals('رشمة تجريبية', $data['localized_title']);
    }

    /** @test */
    public function it_handles_invalid_language_parameter()
    {
        $response = $this->getJson('/api/rachmat?lang=invalid');

        $response->assertStatus(200);
        
        // Should default to Arabic
        $data = $response->json('data.data.0');
        $this->assertEquals('رشمة تجريبية', $data['localized_title']);
    }

    /** @test */
    public function it_supports_dimension_filtering()
    {
        $response = $this->getJson('/api/rachmat?width=20&height=25');

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertEquals(20, $data[0]['width']);
        $this->assertEquals(25, $data[0]['height']);
    }
}
