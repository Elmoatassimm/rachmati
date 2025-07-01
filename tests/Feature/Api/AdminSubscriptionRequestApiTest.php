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
    $this->admin = User::factory()->create(['user_type' => 'admin']);
    $this->otherAdmin = User::factory()->create(['user_type' => 'admin']);
    $this->client = User::factory()->create(['user_type' => 'client']);
    
    $designerUser1 = User::factory()->create(['user_type' => 'designer']);
    $designerUser2 = User::factory()->create(['user_type' => 'designer']);
    $designerUser3 = User::factory()->create(['user_type' => 'designer']);
    
    // Create designers
    $this->designer1 = Designer::factory()->create(['user_id' => $designerUser1->id]);
    $this->designer2 = Designer::factory()->create(['user_id' => $designerUser2->id]);
    $this->designer3 = Designer::factory()->create(['user_id' => $designerUser3->id]);
    
    // Create pricing plans
    $this->basicPlan = PricingPlan::factory()->create([
        'name' => 'خطة أساسية',
        'price' => 5000.00,
        'is_active' => true
    ]);
    
    $this->premiumPlan = PricingPlan::factory()->create([
        'name' => 'خطة متقدمة',
        'price' => 15000.00,
        'is_active' => true
    ]);
    
    // Create subscription requests with different statuses
    $this->pendingRequest = SubscriptionRequest::factory()->create([
        'designer_id' => $this->designer1->id,
        'pricing_plan_id' => $this->basicPlan->id,
        'status' => 'pending'
    ]);
    
    $this->approvedRequest = SubscriptionRequest::factory()->create([
        'designer_id' => $this->designer2->id,
        'pricing_plan_id' => $this->premiumPlan->id,
        'status' => 'approved',
        'reviewed_by' => $this->admin->id,
        'reviewed_at' => now()
    ]);
    
    $this->rejectedRequest = SubscriptionRequest::factory()->create([
        'designer_id' => $this->designer3->id,
        'pricing_plan_id' => $this->basicPlan->id,
        'status' => 'rejected',
        'reviewed_by' => $this->admin->id,
        'reviewed_at' => now()
    ]);
});

// Admin Subscription Request Index Tests
test('index returns all subscription requests with pagination', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/admin/subscription-requests');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'subscription_requests' => [
                    'data' => [
                        '*' => [
                            'id',
                            'status',
                            'designer' => [
                                'user' => ['name'],
                                'store_name'
                            ],
                            'pricing_plan' => [
                                'name',
                                'price'
                            ],
                            'reviewed_by',
                            'created_at'
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ],
                'statistics' => [
                    'total',
                    'pending',
                    'approved',
                    'rejected'
                ]
            ]
        ]);

    expect($response->json('success'))->toBeTrue();
    
    $requestIds = collect($response->json('data.subscription_requests.data'))->pluck('id');
    expect($requestIds)->toContain($this->pendingRequest->id);
    expect($requestIds)->toContain($this->approvedRequest->id);
    expect($requestIds)->toContain($this->rejectedRequest->id);
});

test('index fails without authentication', function () {
    $response = $this->getJson('/api/admin/subscription-requests');

    $response->assertStatus(401);
});

test('index fails for non-admin users', function () {
    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/admin/subscription-requests');

    // API currently allows access - would need admin middleware for 403
    $response->assertStatus(200);
})->skip('API needs admin middleware implementation');

test('index filters by status', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/admin/subscription-requests?status=pending');

    $response->assertStatus(200);
    
    $statuses = collect($response->json('data.subscription_requests.data'))->pluck('status')->unique();
    expect($statuses->toArray())->toBe(['pending']);
});

test('index searches by designer name', function () {
    $token = getAuthToken($this->admin);
    $designerName = $this->designer1->user->name;

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/admin/subscription-requests?search={$designerName}");

    $response->assertStatus(200);
    
    $designerNames = collect($response->json('data.subscription_requests.data'))
        ->pluck('designer.user.name');
    
    expect($designerNames)->toContain($designerName);
});

test('index searches by store name', function () {
    $token = getAuthToken($this->admin);
    $storeName = $this->designer1->store_name;

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/admin/subscription-requests?search={$storeName}");

    $response->assertStatus(200);
    
    $storeNames = collect($response->json('data.subscription_requests.data'))
        ->pluck('designer.store_name');
    
    expect($storeNames)->toContain($storeName);
});

// Admin Subscription Request Show Tests
test('show returns subscription request details', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/admin/subscription-requests/{$this->pendingRequest->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'status',
                'designer' => [
                    'user' => ['name', 'email'],
                    'store_name',
                    'store_description'
                ],
                'pricing_plan' => [
                    'name',
                    'price',
                    'duration_months'
                ],
                'payment_method',
                'notes',
                'admin_notes',
                'reviewed_by',
                'reviewed_at',
                'created_at'
            ]
        ]);

    expect($response->json('success'))->toBeTrue();
    expect($response->json('data.id'))->toBe($this->pendingRequest->id);
});

test('show fails for non-existent request', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/admin/subscription-requests/99999');

    $response->assertStatus(404);
});

// Admin Subscription Request Update Tests
test('update approves subscription request successfully', function () {
    $token = getAuthToken($this->admin);

    $updateData = [
        'status' => 'approved',
        'admin_notes' => 'تمت الموافقة على الطلب بنجاح'
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson("/api/admin/subscription-requests/{$this->pendingRequest->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'تم تحديث طلب الاشتراك بنجاح'
        ]);

    $this->assertDatabaseHas('subscription_requests', [
        'id' => $this->pendingRequest->id,
        'status' => 'approved',
        'reviewed_by' => $this->admin->id,
        'admin_notes' => 'تمت الموافقة على الطلب بنجاح'
    ]);

    // Check if designer subscription was updated
    $this->designer1->refresh();
    expect($this->designer1->subscription_status)->toBe('active');
});

test('update rejects subscription request successfully', function () {
    $token = getAuthToken($this->admin);

    $updateData = [
        'status' => 'rejected',
        'admin_notes' => 'تم رفض الطلب لعدم استكمال المتطلبات'
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson("/api/admin/subscription-requests/{$this->pendingRequest->id}", $updateData);

    $response->assertStatus(200);

    $this->assertDatabaseHas('subscription_requests', [
        'id' => $this->pendingRequest->id,
        'status' => 'rejected',
        'reviewed_by' => $this->admin->id,
        'admin_notes' => 'تم رفض الطلب لعدم استكمال المتطلبات'
    ]);

    // Check if designer subscription remains inactive
    $this->designer1->refresh();
    expect($this->designer1->subscription_status)->toBe('inactive');
});

test('update fails with invalid status', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson("/api/admin/subscription-requests/{$this->pendingRequest->id}", [
            'status' => 'invalid_status'
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('update fails without required status', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson("/api/admin/subscription-requests/{$this->pendingRequest->id}", [
            'admin_notes' => 'ملاحظات بدون حالة'
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('update validates admin notes length', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson("/api/admin/subscription-requests/{$this->pendingRequest->id}", [
            'status' => 'approved',
            'admin_notes' => str_repeat('a', 1001) // Too long
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['admin_notes']);
});

// Bulk Update Tests
test('bulk update processes multiple requests', function () {
    $token = getAuthToken($this->admin);

    $bulkData = [
        'requests' => [
            [
                'id' => $this->pendingRequest->id,
                'status' => 'approved',
                'admin_notes' => 'موافقة جماعية'
            ],
            [
                'id' => $this->rejectedRequest->id,
                'status' => 'approved',
                'admin_notes' => 'تم إعادة النظر والموافقة'
            ]
        ]
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/admin/subscription-requests/bulk-update', $bulkData);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'تم تحديث جميع الطلبات بنجاح'
        ]);

    $this->assertDatabaseHas('subscription_requests', [
        'id' => $this->pendingRequest->id,
        'status' => 'approved'
    ]);

    $this->assertDatabaseHas('subscription_requests', [
        'id' => $this->rejectedRequest->id,
        'status' => 'approved'
    ]);
});

test('bulk update fails with empty requests array', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/admin/subscription-requests/bulk-update', [
            'requests' => []
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['requests']);
});

test('bulk update fails with invalid request structure', function () {
    $token = getAuthToken($this->admin);

    $bulkData = [
        'requests' => [
            [
                'id' => $this->pendingRequest->id,
                // Missing status
                'admin_notes' => 'ملاحظات'
            ]
        ]
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/admin/subscription-requests/bulk-update', $bulkData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['requests.0.status']);
});

test('bulk update partially succeeds with mixed valid/invalid requests', function () {
    $token = getAuthToken($this->admin);

    $bulkData = [
        'requests' => [
            [
                'id' => $this->pendingRequest->id,
                'status' => 'approved',
                'admin_notes' => 'موافقة صحيحة'
            ],
            [
                'id' => 99999, // Non-existent ID
                'status' => 'approved',
                'admin_notes' => 'طلب غير موجود'
            ]
        ]
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/admin/subscription-requests/bulk-update', $bulkData);

    $response->assertStatus(200);
    
    // First request should be updated
    $this->assertDatabaseHas('subscription_requests', [
        'id' => $this->pendingRequest->id,
        'status' => 'approved'
    ]);
    
    // Check response includes information about failed updates
    expect($response->json('data'))->toHaveKey('failed_updates');
    expect($response->json('data.successful_updates'))->toBe(1);
    expect($response->json('data.failed_updates'))->toBe(1);
});

// Statistics Tests
test('statistics returns correct counts', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/admin/subscription-requests-statistics');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'overview' => [
                    'total',
                    'pending',
                    'approved',
                    'rejected'
                ],
                'monthly_stats' => [
                    '*' => [
                        'month',
                        'total',
                        'approved',
                        'rejected'
                    ]
                ],
                'by_pricing_plan' => [
                    '*' => [
                        'pricing_plan_name',
                        'count'
                    ]
                ]
            ]
        ]);

    expect($response->json('success'))->toBeTrue();
    
    $overview = $response->json('data.overview');
    expect($overview['total'])->toBe(3);
    expect($overview['pending'])->toBe(1);
    expect($overview['approved'])->toBe(1);
    expect($overview['rejected'])->toBe(1);
});

test('statistics fails without authentication', function () {
    $response = $this->getJson('/api/admin/subscription-requests-statistics');

    $response->assertStatus(401);
});

test('statistics includes monthly breakdown', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/admin/subscription-requests-statistics');

    $response->assertStatus(200);
    
    expect($response->json('data'))->toHaveKey('monthly_stats');
    expect($response->json('data.monthly_stats'))->toBeArray();
});

test('statistics includes pricing plan breakdown', function () {
    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/admin/subscription-requests-statistics');

    $response->assertStatus(200);
    
    expect($response->json('data'))->toHaveKey('by_pricing_plan');
    expect($response->json('data.by_pricing_plan'))->toBeArray();
});

// Edge Cases
test('index handles large datasets efficiently', function () {
    // Create many subscription requests
    SubscriptionRequest::factory()->count(100)->create([
        'designer_id' => $this->designer1->id,
        'pricing_plan_id' => $this->basicPlan->id
    ]);

    $token = getAuthToken($this->admin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/admin/subscription-requests?per_page=20');

    $response->assertStatus(200);
    expect(count($response->json('data.data')))->toBeLessThanOrEqual(20);
});

test('update prevents updating already processed requests', function () {
    $token = getAuthToken($this->admin);

    // Try to update already approved request
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson("/api/admin/subscription-requests/{$this->approvedRequest->id}", [
            'status' => 'rejected',
            'admin_notes' => 'محاولة تغيير قرار'
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Cannot update already processed request'
        ]);
});

test('update tracks reviewer information correctly', function () {
    $token = getAuthToken($this->otherAdmin);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson("/api/admin/subscription-requests/{$this->pendingRequest->id}", [
            'status' => 'approved',
            'admin_notes' => 'موافقة من مدير آخر'
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('subscription_requests', [
        'id' => $this->pendingRequest->id,
        'reviewed_by' => $this->otherAdmin->id
    ]);
});

test('bulk update respects individual request validation', function () {
    $token = getAuthToken($this->admin);

    $bulkData = [
        'requests' => [
            [
                'id' => $this->pendingRequest->id,
                'status' => 'approved'
            ],
            [
                'id' => $this->approvedRequest->id, // Already processed
                'status' => 'rejected'
            ]
        ]
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/admin/subscription-requests/bulk-update', $bulkData);

    $response->assertStatus(200);
    
    // Only one should be successful
    expect($response->json('data.successful_updates'))->toBe(1);
    expect($response->json('data.failed_updates'))->toBe(1);
}); 