<?php

use App\Models\User;
use App\Models\Designer;
use App\Models\Category;
use App\Models\PricingPlan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
    Storage::fake('public');
    
    $this->user = User::factory()->create();
    $this->pricingPlan = PricingPlan::factory()->create();
    $this->designer = Designer::factory()->create([
        'user_id' => $this->user->id,
        'pricing_plan_id' => $this->pricingPlan->id,
        'subscription_status' => 'active',
        'subscription_ends_at' => now()->addMonth(),
    ]);
    $this->category = Category::factory()->create();
});

test('يمكن إنشاء رشمة مع أجزاء متعددة', function () {
    $this->actingAs($this->user);
    
    $file = UploadedFile::fake()->create('rachma.dst', 1000);
    $previewImage = UploadedFile::fake()->image('preview.jpg', 800, 600);
    
    $response = $this->post(route('designer.rachmat.store'), [
        'name_ar' => 'رشمة تجريبية',
        'name_fr' => 'Test Rachma',
        'description_ar' => 'وصف تجريبي',
        'description_fr' => 'Test Description',
        'category_id' => $this->category->id,
        'size' => '20x25 cm',
        'gharazat' => 15000,
        'color_numbers' => 5,
        'price' => 2500,
        'file' => $file,
        'preview_images' => [$previewImage],
        'parts' => [
            [
                'name' => 'الوسط',
                'length' => '10.5',
                'height' => '8.2',
                'stitches' => '8000',
            ],
            [
                'name' => 'الحافة',
                'length' => '15.0',
                'height' => '5.0',
                'stitches' => '7000',
            ]
        ]
    ]);

    $response->assertRedirect(route('designer.rachmat.index'));
    $response->assertSessionHas('success');
    
    $this->assertDatabaseCount('rachmat', 1);
    $this->assertDatabaseCount('parts', 2);
    
    $rachma = $this->designer->rachmat()->first();
    expect($rachma->parts)->toHaveCount(2);
    
    $centerPart = $rachma->parts->where('name', 'الوسط')->first();
    expect($centerPart->length)->toBe(10.5);
    expect($centerPart->height)->toBe(8.2);
    expect($centerPart->stitches)->toBe(8000);
    expect($centerPart->order)->toBe(1);
    
    $borderPart = $rachma->parts->where('name', 'الحافة')->first();
    expect($borderPart->length)->toBe(15.0);
    expect($borderPart->height)->toBe(5.0);
    expect($borderPart->stitches)->toBe(7000);
    expect($borderPart->order)->toBe(2);
});

test('يتطلب جزء واحد على الأقل لإنشاء الرشمة', function () {
    $this->actingAs($this->user);
    
    $file = UploadedFile::fake()->create('rachma.dst', 1000);
    
    $response = $this->post(route('designer.rachmat.store'), [
        'name_ar' => 'رشمة تجريبية',
        'name_fr' => 'Test Rachma',
        'category_id' => $this->category->id,
        'size' => '20x25 cm',
        'gharazat' => 15000,
        'color_numbers' => 5,
        'price' => 2500,
        'file' => $file,
        'parts' => []
    ]);

    $response->assertSessionHasErrors(['parts']);
    $this->assertDatabaseCount('rachmat', 0);
    $this->assertDatabaseCount('parts', 0);
});

test('يتطلب اسم وعدد الغرز لكل جزء', function () {
    $this->actingAs($this->user);
    
    $file = UploadedFile::fake()->create('rachma.dst', 1000);
    
    $response = $this->post(route('designer.rachmat.store'), [
        'name_ar' => 'رشمة تجريبية',
        'name_fr' => 'Test Rachma',
        'category_id' => $this->category->id,
        'size' => '20x25 cm',
        'gharazat' => 15000,
        'color_numbers' => 5,
        'price' => 2500,
        'file' => $file,
        'parts' => [
            [
                'name' => '',
                'stitches' => '',
            ]
        ]
    ]);

    $response->assertSessionHasErrors(['parts.0.name', 'parts.0.stitches']);
    $this->assertDatabaseCount('rachmat', 0);
    $this->assertDatabaseCount('parts', 0);
}); 