<?php

namespace Tests\Feature\Designer;

use App\Models\User;
use App\Models\Designer;
use App\Models\Category;
use App\Models\PricingPlan;
use App\Models\Rachma;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RachmatControllerTest extends TestCase
{
    use RefreshDatabase;

    private $activeDesigner;
    private $inactiveDesigner;
    private $user;
    private $inactiveUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('public');

        // Create pricing plan
        $pricingPlan = PricingPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 100,
            'duration_months' => 1,
        ]);

        // Create active designer
        $this->user = User::factory()->create(['user_type' => 'designer']);
        $this->activeDesigner = Designer::factory()->create([
            'user_id' => $this->user->id,
            'subscription_status' => 'active',
            'subscription_start_date' => now(),
            'subscription_end_date' => now()->addMonth(),
            'pricing_plan_id' => $pricingPlan->id,
        ]);

        // Create inactive designer
        $this->inactiveUser = User::factory()->create(['user_type' => 'designer']);
        $this->inactiveDesigner = Designer::factory()->create([
            'user_id' => $this->inactiveUser->id,
            'subscription_status' => 'expired',
        ]);
    }

    /** @test */
    public function active_designer_can_view_rachmat_index()
    {
        // Create some rachmat for the designer
        Rachma::factory()->count(3)->create([
            'designer_id' => $this->activeDesigner->id,
        ]);

        Category::factory()->count(2)->create();

        $response = $this->actingAs($this->user)
            ->get(route('designer.rachmat.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Designer/Rachmat/Index')
                ->has('rachmat')
                ->has('categories')
                ->has('stats')
                ->has('filters')
        );
    }

    /** @test */
    public function designer_without_profile_redirected_to_subscription()
    {
        $userWithoutDesigner = User::factory()->create(['user_type' => 'designer']);

        $response = $this->actingAs($userWithoutDesigner)
            ->get(route('designer.rachmat.index'));

        $response->assertRedirect(route('designer.subscription.request'));
    }

    /** @test */
    public function rachmat_index_can_be_filtered_by_search()
    {
        Rachma::factory()->create([
            'designer_id' => $this->activeDesigner->id,
            'title' => 'تصميم زهور',
        ]);

        Rachma::factory()->create([
            'designer_id' => $this->activeDesigner->id,
            'title' => 'تصميم نجوم',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('designer.rachmat.index', ['search' => 'زهور']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('filters.search', 'زهور')
        );
    }

    /** @test */
    public function rachmat_index_can_be_filtered_by_category()
    {
        $category = Category::factory()->create();
        
        $rachma = Rachma::factory()->create([
            'designer_id' => $this->activeDesigner->id,
        ]);
        $rachma->categories()->attach($category->id);

        $response = $this->actingAs($this->user)
            ->get(route('designer.rachmat.index', ['category' => $category->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('filters.category', (string)$category->id)
        );
    }

    /** @test */
    public function active_designer_can_access_create_page()
    {
        Category::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get(route('designer.rachmat.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Designer/Rachmat/Create')
                ->has('categories')
        );
    }

    /** @test */
    public function inactive_designer_cannot_access_create_page()
    {
        $response = $this->actingAs($this->inactiveUser)
            ->get(route('designer.rachmat.create'));

        $response->assertRedirect(route('designer.subscription.request'));
    }

    /** @test */
    public function designer_can_store_new_rachma()
    {
        $categories = Category::factory()->count(2)->create();

        $files = [
            UploadedFile::fake()->create('design.zip', 1024, 'application/zip'),
            UploadedFile::fake()->create('pattern.pdf', 512, 'application/pdf'),
        ];

        $previewImages = [
            UploadedFile::fake()->image('preview1.jpg', 800, 600),
        ];

        $formData = [
            'title' => 'تصميم تطريز جميل',
            'description' => 'وصف التصميم',
            'categories' => $categories->pluck('id')->toArray(),
            'size' => '10x10 cm',
            'gharazat' => 5000,
            'color_numbers' => 8,
            'price' => 50.00,
            'files' => $files,
            'preview_images' => $previewImages,
            'parts' => [
                [
                    'name' => 'الجزء الأول',
                    'length' => '5',
                    'height' => '5',
                    'stitches' => '2500',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('designer.rachmat.store'), $formData);

        $response->assertRedirect(route('designer.rachmat.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rachmat', [
            'designer_id' => $this->activeDesigner->id,
            'title' => 'تصميم تطريز جميل',
            'price' => 50.00,
        ]);

        // Check files were stored
        $this->assertTrue(Storage::disk('private')->exists('rachmat_files/' . $files[0]->hashName()));
        $this->assertTrue(Storage::disk('public')->exists('rachmat_previews/' . $previewImages[0]->hashName()));
    }

    /** @test */
    public function store_rachma_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('designer.rachmat.store'), []);

        $response->assertSessionHasErrors([
            'title',
            'categories', 
            'size',
            'gharazat',
            'color_numbers',
            'price',
            'files',
        ]);
    }

    /** @test */
    public function inactive_designer_cannot_store_rachma()
    {
        $formData = [
            'title' => 'تصميم تطريز',
            'categories' => [1],
            'size' => '10x10',
            'gharazat' => 5000,
            'color_numbers' => 5,
            'price' => 50,
            'files' => [UploadedFile::fake()->create('design.zip')],
        ];

        $response = $this->actingAs($this->inactiveUser)
            ->post(route('designer.rachmat.store'), $formData);

        $response->assertRedirect(route('designer.subscription.request'));
    }

    /** @test */
    public function designer_can_view_own_rachma()
    {
        $rachma = Rachma::factory()->create([
            'designer_id' => $this->activeDesigner->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('designer.rachmat.show', $rachma));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Designer/Rachmat/Show')
                ->has('rachma')
                ->has('stats')
        );
    }

    /** @test */
    public function designer_cannot_view_others_rachma()
    {
        $otherDesigner = Designer::factory()->create();
        $rachma = Rachma::factory()->create([
            'designer_id' => $otherDesigner->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('designer.rachmat.show', $rachma));

        $response->assertStatus(403);
    }

    /** @test */
    public function designer_can_delete_own_rachma_without_orders()
    {
        $rachma = Rachma::factory()->create([
            'designer_id' => $this->activeDesigner->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('designer.rachmat.destroy', $rachma));

        $response->assertRedirect(route('designer.rachmat.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('rachmat', [
            'id' => $rachma->id,
        ]);
    }

    /** @test */
    public function designer_cannot_delete_rachma_with_orders()
    {
        $rachma = Rachma::factory()->create([
            'designer_id' => $this->activeDesigner->id,
        ]);

        // Create an order for this rachma
        Order::factory()->create([
            'rachma_id' => $rachma->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('designer.rachmat.destroy', $rachma));

        $response->assertRedirect(route('designer.rachmat.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('rachmat', [
            'id' => $rachma->id,
        ]);
    }

    /** @test */
    public function designer_cannot_delete_others_rachma()
    {
        $otherDesigner = Designer::factory()->create();
        $rachma = Rachma::factory()->create([
            'designer_id' => $otherDesigner->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('designer.rachmat.destroy', $rachma));

        $response->assertStatus(403);
    }

    /** @test */
    public function index_displays_correct_stats()
    {
        // Create rachmat for the designer
        $rachma1 = Rachma::factory()->create([
            'designer_id' => $this->activeDesigner->id,
            'price' => 100,
        ]);
        
        $rachma2 = Rachma::factory()->create([
            'designer_id' => $this->activeDesigner->id,
            'price' => 200,
        ]);

        // Create completed orders
        Order::factory()->create([
            'rachma_id' => $rachma1->id,
            'status' => 'completed',
        ]);

        Order::factory()->create([
            'rachma_id' => $rachma2->id,
            'status' => 'completed',
        ]);

        // Create pending order (shouldn't count in earnings)
        Order::factory()->create([
            'rachma_id' => $rachma1->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('designer.rachmat.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('stats')
                ->where('stats.total', 2) // Total rachmat
                ->where('stats.active', 2) // Active rachmat
                ->where('stats.totalSales', 3) // Total orders
                ->where('stats.totalEarnings', 300) // Only completed orders
        );
    }
} 