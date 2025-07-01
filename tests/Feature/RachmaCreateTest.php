<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Designer;
use App\Models\Category;
use App\Models\PricingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Rachma;

class RachmaCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('public');
    }

    /** @test */
    public function authenticated_designer_can_access_create_page()
    {
        // Create a pricing plan
        $pricingPlan = PricingPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 100,
            'duration_months' => 1,
        ]);

        // Create a user and designer
        $user = User::factory()->create([
            'user_type' => 'designer',
        ]);

        $designer = Designer::factory()->create([
            'user_id' => $user->id,
            'subscription_status' => 'active',
            'subscription_start_date' => now(),
            'subscription_end_date' => now()->addMonth(),
            'pricing_plan_id' => $pricingPlan->id,
        ]);

        // Create some categories
        Category::factory()->count(3)->create();

        // Act as the designer and visit create page
        $response = $this->actingAs($user)
            ->get(route('designer.rachmat.create'));

        // Assert the page loads successfully
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Designer/Rachmat/Create')
                ->has('categories')
        );
    }

    /** @test */
    public function inactive_designer_cannot_access_create_page()
    {
        // Create a user and inactive designer
        $user = User::factory()->create([
            'user_type' => 'designer',
        ]);

        $designer = Designer::factory()->create([
            'user_id' => $user->id,
            'subscription_status' => 'expired',
        ]);

        // Act as the designer and try to visit create page
        $response = $this->actingAs($user)
            ->get(route('designer.rachmat.create'));

        // Assert redirect to subscription request page (updated expectation)
        $response->assertRedirect(route('designer.subscription.request'));
    }

    /** @test */
    public function designer_can_create_rachma_with_multiple_files()
    {
        // Create a pricing plan
        $pricingPlan = PricingPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 100,
            'duration_months' => 1,
        ]);

        // Create a user and designer
        $user = User::factory()->create([
            'user_type' => 'designer',
        ]);

        $designer = Designer::factory()->create([
            'user_id' => $user->id,
            'subscription_status' => 'active',
            'subscription_start_date' => now(),
            'subscription_end_date' => now()->addMonth(),
            'pricing_plan_id' => $pricingPlan->id,
        ]);

        // Create categories
        $categories = Category::factory()->count(2)->create();

        // Create test files with supported extensions
        $rachmaFiles = [
            UploadedFile::fake()->create('design.zip', 1024, 'application/zip'),
            UploadedFile::fake()->create('pattern.pdf', 512, 'application/pdf'),
        ];

        $previewImages = [
            UploadedFile::fake()->image('preview1.jpg', 800, 600),
            UploadedFile::fake()->image('preview2.jpg', 800, 600),
        ];

        // Prepare form data with updated field structure
        $formData = [
            'title' => 'تصميم تطريز جميل', // Updated from name_ar
            'description' => 'وصف التصميم', // Updated from description_ar
            'categories' => $categories->pluck('id')->toArray(),
            'size' => '10x10 cm',
            'gharazat' => 5000,
            'color_numbers' => 8, // Single integer instead of array
            'price' => 50.00,
            'files' => $rachmaFiles,
            'preview_images' => $previewImages,
            'parts' => [
                [
                    'name' => 'الجزء الأول',
                    'length' => '5',
                    'height' => '5',
                    'stitches' => '2500',
                ],
                [
                    'name' => 'الجزء الثاني',
                    'length' => '5',
                    'height' => '5',
                    'stitches' => '2500',
                ],
            ],
        ];

        // Submit the form
        $response = $this->actingAs($user)
            ->post(route('designer.rachmat.store'), $formData);

        // Assert successful creation and redirect
        $response->assertRedirect(route('designer.rachmat.index'));
        $response->assertSessionHas('success');

        // Assert rachma was created in database
        $this->assertDatabaseHas('rachmat', [
            'designer_id' => $designer->id,
            'title' => 'تصميم تطريز جميل',
            'description' => 'وصف التصميم',
            'size' => '10x10 cm',
            'gharazat' => 5000,
            'price' => 50.00,
        ]);

        // Assert files were stored
        Storage::disk('private')->assertExists('rachmat_files/' . $rachmaFiles[0]->hashName());
        Storage::disk('private')->assertExists('rachmat_files/' . $rachmaFiles[1]->hashName());
        
        // Assert preview images were stored
        Storage::disk('public')->assertExists('rachmat_previews/' . $previewImages[0]->hashName());
        Storage::disk('public')->assertExists('rachmat_previews/' . $previewImages[1]->hashName());
    }

    /** @test */
    public function create_rachma_requires_valid_data()
    {
        // Create a user and designer
        $user = User::factory()->create([
            'user_type' => 'designer',
        ]);

        $designer = Designer::factory()->create([
            'user_id' => $user->id,
            'subscription_status' => 'active',
            'subscription_start_date' => now(),
            'subscription_end_date' => now()->addMonth(),
        ]);

        // Submit form with missing required fields
        $response = $this->actingAs($user)
            ->post(route('designer.rachmat.store'), []);

        // Assert validation errors for updated field structure
        $response->assertSessionHasErrors([
            'title', // Updated from name_ar
            'categories',
            'size',
            'gharazat',
            'color_numbers',
            'price',
            'files',
            // parts is now optional, so not included in required errors
        ]);
    }

    /** @test */
    public function designer_can_create_rachma_without_parts()
    {
        // Create a pricing plan
        $pricingPlan = PricingPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 100,
            'duration_months' => 1,
        ]);

        // Create a user and designer
        $user = User::factory()->create([
            'user_type' => 'designer',
        ]);

        $designer = Designer::factory()->create([
            'user_id' => $user->id,
            'subscription_status' => 'active',
            'subscription_start_date' => now(),
            'subscription_end_date' => now()->addMonth(),
            'pricing_plan_id' => $pricingPlan->id,
        ]);

        // Create categories
        $categories = Category::factory()->count(2)->create();

        // Create test files with supported extensions
        $rachmaFiles = [
            UploadedFile::fake()->create('design.zip', 1024, 'application/zip'),
        ];

        // Prepare form data without parts
        $formData = [
            'title' => 'تصميم بدون أجزاء',
            'description' => 'وصف التصميم',
            'categories' => $categories->pluck('id')->toArray(),
            'size' => '10x10 cm',
            'gharazat' => 5000,
            'color_numbers' => 8,
            'price' => 50.00,
            'files' => $rachmaFiles,
            // no parts provided
        ];

        // Submit the form
        $response = $this->actingAs($user)
            ->post(route('designer.rachmat.store'), $formData);

        // Assert successful creation and redirect
        $response->assertRedirect(route('designer.rachmat.index'));
        $response->assertSessionHas('success');

        // Assert rachma was created in database
        $this->assertDatabaseHas('rachmat', [
            'designer_id' => $designer->id,
            'title' => 'تصميم بدون أجزاء',
            'description' => 'وصف التصميم',
            'size' => '10x10 cm',
            'gharazat' => 5000,
            'price' => 50.00,
        ]);

        // Assert no parts were created
        $rachma = Rachma::where('title', 'تصميم بدون أجزاء')->first();
        $this->assertEquals(0, $rachma->parts()->count());
    }
}
