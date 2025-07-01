<?php

use App\Models\User;
use App\Models\Designer;
use App\Models\SubscriptionRequest;
use App\Models\PricingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__ . '/Helpers/ApiTestHelpers.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create users
    $this->client = User::factory()->create(['user_type' => 'client']);
    $this->admin = User::factory()->create(['user_type' => 'admin']);
    $designerUser = User::factory()->create(['user_type' => 'designer']);
    $otherDesignerUser = User::factory()->create(['user_type' => 'designer']);
    
    // Create designers
    $this->designer = Designer::factory()->create([
        'user_id' => $designerUser->id,
        'subscription_status' => 'inactive'
    ]);
    
    $this->otherDesigner = Designer::factory()->create([
        'user_id' => $otherDesignerUser->id,
        'subscription_status' => 'active'
    ]);
    
    // Create pricing plans
    $this->basicPlan = PricingPlan::factory()->create([
        'name' => 'خطة أساسية',
        'price' => 5000.00,
        'duration_months' => 1,
        'is_active' => true
    ]);
    
    $this->premiumPlan = PricingPlan::factory()->create([
        'name' => 'خطة متقدمة',
        'price' => 15000.00,
        'duration_months' => 3,
        'is_active' => true
    ]);
    
    $this->inactivePlan = PricingPlan::factory()->create([
        'name' => 'خطة قديمة',
        'price' => 8000.00,
        'duration_months' => 2,
        'is_active' => false
    ]);
    
    // Create subscription requests
    $this->designerRequest = SubscriptionRequest::factory()->create([
        'designer_id' => $this->designer->id,
        'pricing_plan_id' => $this->basicPlan->id,
        'status' => 'pending'
    ]);
    
    $this->otherDesignerRequest = SubscriptionRequest::factory()->create([
        'designer_id' => $this->otherDesigner->id,
        'pricing_plan_id' => $this->premiumPlan->id,
        'status' => 'approved'
    ]);
});

// Designer Subscription Request Index Tests
test('index returns designer subscription requests', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/designer/subscription-requests');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'pricing_plan' => [
                            'name',
                            'price',
                            'duration_months'
                        ],
                        'created_at'
                    ]
                ],
                'links',
                'meta'
            ]
        ]);

    expect($response->json('success'))->toBeTrue();
    
    // Should only return current designer's requests
    $requestIds = collect($response->json('data.data'))->pluck('id');
    expect($requestIds)->toContain($this->designerRequest->id);
    expect($requestIds)->not->toContain($this->otherDesignerRequest->id);
});

test('index fails without authentication', function () {
    $response = $this->getJson('/api/designer/subscription-requests');

    $response->assertStatus(401);
});

test('index fails for non-designer users', function () {
    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/designer/subscription-requests');

    $response->assertStatus(403); // Assuming there's middleware to check user type
});

test('index returns empty result for designer with no requests', function () {
    $newDesignerUser = User::factory()->create(['user_type' => 'designer']);
    $newDesigner = Designer::factory()->create(['user_id' => $newDesignerUser->id]);
    $token = getAuthToken($newDesignerUser);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/designer/subscription-requests');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'data' => []
            ]
        ]);
});

// Designer Subscription Request Store Tests
test('store creates subscription request successfully', function () {
    $token = getAuthToken($this->designer->user);

    $requestData = [
        'pricing_plan_id' => $this->premiumPlan->id,
        'payment_method' => 'ccp',
        'notes' => 'أريد الاشتراك في الخطة المتقدمة'
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/designer/subscription-requests', $requestData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'designer_id',
                'pricing_plan_id',
                'status',
                'payment_method',
                'notes',
                'pricing_plan'
            ]
        ]);

    $this->assertDatabaseHas('subscription_requests', [
        'designer_id' => $this->designer->id,
        'pricing_plan_id' => $this->premiumPlan->id,
        'status' => 'pending',
        'payment_method' => 'ccp',
        'notes' => 'أريد الاشتراك في الخطة المتقدمة'
    ]);

    expect($response->json('success'))->toBeTrue();
});

test('store fails without authentication', function () {
    $response = $this->postJson('/api/designer/subscription-requests', [
        'pricing_plan_id' => $this->basicPlan->id,
        'payment_method' => 'ccp'
    ]);

    $response->assertStatus(401);
});

test('store fails with missing required fields', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/designer/subscription-requests', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pricing_plan_id', 'payment_method']);
});

test('store fails with non-existent pricing plan', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/designer/subscription-requests', [
            'pricing_plan_id' => 99999,
            'payment_method' => 'ccp'
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pricing_plan_id']);
});

test('store fails with inactive pricing plan', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/designer/subscription-requests', [
            'pricing_plan_id' => $this->inactivePlan->id,
            'payment_method' => 'ccp'
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Selected pricing plan is not available'
        ]);
});

test('store fails with invalid payment method', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/designer/subscription-requests', [
            'pricing_plan_id' => $this->basicPlan->id,
            'payment_method' => 'invalid_method'
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment_method']);
});

test('store fails when designer has pending request', function () {
    $token = getAuthToken($this->designer->user);
    // This designer already has a pending request from setup

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/designer/subscription-requests', [
            'pricing_plan_id' => $this->premiumPlan->id,
            'payment_method' => 'ccp'
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'You already have a pending subscription request'
        ]);
});

// Designer Subscription Request Show Tests
test('show returns subscription request details', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/designer/subscription-requests/{$this->designerRequest->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'status',
                'pricing_plan' => [
                    'name',
                    'price',
                    'duration_months'
                ],
                'payment_method',
                'notes',
                'reviewed_by',
                'reviewed_at',
                'created_at'
            ]
        ]);

    expect($response->json('success'))->toBeTrue();
    expect($response->json('data.id'))->toBe($this->designerRequest->id);
});

test('show fails for other designer request', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/designer/subscription-requests/{$this->otherDesignerRequest->id}");

    $response->assertStatus(404);
});

test('show fails for non-existent request', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/designer/subscription-requests/99999');

    $response->assertStatus(404);
});

// Pricing Plans Tests
test('pricing plans returns active plans', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/designer/pricing-plans');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'price',
                    'duration_months',
                    'features',
                    'is_active'
                ]
            ]
        ]);

    expect($response->json('success'))->toBeTrue();
    
    $planIds = collect($response->json('data'))->pluck('id');
    expect($planIds)->toContain($this->basicPlan->id);
    expect($planIds)->toContain($this->premiumPlan->id);
    expect($planIds)->not->toContain($this->inactivePlan->id);
});

test('pricing plans fails without authentication', function () {
    $response = $this->getJson('/api/designer/pricing-plans');

    $response->assertStatus(401);
});

// Has Pending Request Tests
test('has pending request returns true when designer has pending request', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/designer/has-pending-request');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'has_pending_request' => true,
                'pending_request' => [
                    'id' => $this->designerRequest->id,
                    'status' => 'pending'
                ]
            ]
        ]);
});

test('has pending request returns false when designer has no pending request', function () {
    // Update the existing request to approved
    $this->designerRequest->update(['status' => 'approved']);
    
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/designer/has-pending-request');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'has_pending_request' => false,
                'pending_request' => null
            ]
        ]);
});

test('has pending request fails without authentication', function () {
    $response = $this->getJson('/api/designer/has-pending-request');

    $response->assertStatus(401);
});

// Edge Cases
test('store accepts all valid payment methods', function () {
    // First, approve the existing request to avoid pending conflict
    $this->designerRequest->update(['status' => 'approved']);
    
    $token = getAuthToken($this->designer->user);
    $paymentMethods = ['ccp', 'baridi_mob', 'dahabiya'];

    foreach ($paymentMethods as $method) {
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/designer/subscription-requests', [
                'pricing_plan_id' => $this->basicPlan->id,
                'payment_method' => $method
            ]);

        $response->assertStatus(201);
        
        // Clean up for next iteration
        SubscriptionRequest::latest()->first()->delete();
    }
});

test('index filters by status', function () {
    // Create requests with different statuses
    $approvedRequest = SubscriptionRequest::factory()->create([
        'designer_id' => $this->designer->id,
        'pricing_plan_id' => $this->basicPlan->id,
        'status' => 'approved'
    ]);

    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/designer/subscription-requests?status=pending');

    $response->assertStatus(200);
    
    $statuses = collect($response->json('data.data'))->pluck('status')->unique();
    expect($statuses->toArray())->toBe(['pending']);
});

test('store validates notes length', function () {
    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/designer/subscription-requests', [
            'pricing_plan_id' => $this->basicPlan->id,
            'payment_method' => 'ccp',
            'notes' => str_repeat('a', 1001) // Too long
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['notes']);
});

test('show includes review information when available', function () {
    // Update request with review info
    $this->designerRequest->update([
        'status' => 'approved',
        'reviewed_by' => $this->admin->id,
        'reviewed_at' => now(),
        'admin_notes' => 'تمت الموافقة على الطلب'
    ]);

    $token = getAuthToken($this->designer->user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/designer/subscription-requests/{$this->designerRequest->id}");

    $response->assertStatus(200);
    
    $data = $response->json('data');
    expect($data['reviewed_by'])->not->toBeNull();
    expect($data['reviewed_at'])->not->toBeNull();
    expect($data)->toHaveKey('admin_notes');
}); 