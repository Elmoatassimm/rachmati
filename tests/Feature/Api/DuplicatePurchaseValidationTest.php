<?php

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rachma;
use App\Models\Designer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

// Helper function to create a test user
function createTestUser(): User
{
    return User::factory()->create([
        'user_type' => 'client',
        'email' => 'client@test.com',
    ]);
}

// Helper function to create a test designer with active subscription
function createTestDesigner(): Designer
{
    $user = User::factory()->create(['user_type' => 'designer']);
    return Designer::factory()->create([
        'user_id' => $user->id,
        'subscription_status' => 'active',
    ]);
}

// Helper function to create a test rachma
function createTestRachma(Designer $designer): Rachma
{
    return Rachma::factory()->create([
        'designer_id' => $designer->id,
        'title_ar' => 'رشمة تجريبية',
        'price' => 1000,
    ]);
}

// Helper function to create a completed order for a user
function createCompletedOrder(User $user, array $rachmaIds): Order
{
    $totalAmount = count($rachmaIds) * 1000;
    
    $order = Order::factory()->create([
        'client_id' => $user->id,
        'amount' => $totalAmount,
        'status' => 'completed',
    ]);

    foreach ($rachmaIds as $rachmaId) {
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'rachma_id' => $rachmaId,
            'price' => 1000,
        ]);
    }

    return $order;
}

// Helper function to create order request data
function createOrderRequestData(array $rachmaIds): array
{
    return [
        'items' => array_map(fn($id) => ['rachma_id' => $id], $rachmaIds),
        'payment_method' => 'ccp',
        'payment_proof' => UploadedFile::fake()->image('payment_proof.jpg'),
    ];
}

// User Model Tests
test('user can check if they purchased a specific rachma', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma1 = createTestRachma($designer);
    $rachma2 = createTestRachma($designer);

    // User hasn't purchased anything yet
    expect($user->hasPurchasedRachma($rachma1->id))->toBeFalse();
    expect($user->hasPurchasedRachma($rachma2->id))->toBeFalse();

    // Create completed order for rachma1
    createCompletedOrder($user, [$rachma1->id]);

    // Refresh user to get updated relationships
    $user->refresh();

    // User should have purchased rachma1 but not rachma2
    expect($user->hasPurchasedRachma($rachma1->id))->toBeTrue();
    expect($user->hasPurchasedRachma($rachma2->id))->toBeFalse();
});

test('user can check purchased rachmat from multiple orders', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma1 = createTestRachma($designer);
    $rachma2 = createTestRachma($designer);
    $rachma3 = createTestRachma($designer);

    // Create multiple completed orders
    createCompletedOrder($user, [$rachma1->id]);
    createCompletedOrder($user, [$rachma2->id, $rachma3->id]);

    $user->refresh();

    // Check individual purchases
    expect($user->hasPurchasedRachma($rachma1->id))->toBeTrue();
    expect($user->hasPurchasedRachma($rachma2->id))->toBeTrue();
    expect($user->hasPurchasedRachma($rachma3->id))->toBeTrue();

    // Check batch method
    $purchasedIds = $user->hasPurchasedAnyRachmat([$rachma1->id, $rachma2->id, $rachma3->id]);
    expect($purchasedIds)->toHaveCount(3);
    expect($purchasedIds)->toContain($rachma1->id, $rachma2->id, $rachma3->id);
});

test('user hasPurchasedAnyRachmat returns only purchased ones', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma1 = createTestRachma($designer);
    $rachma2 = createTestRachma($designer);
    $rachma3 = createTestRachma($designer);
    $rachma4 = createTestRachma($designer);

    // User purchased rachma1 and rachma3
    createCompletedOrder($user, [$rachma1->id, $rachma3->id]);

    $user->refresh();

    // Check which ones are purchased from a mixed list
    $purchasedIds = $user->hasPurchasedAnyRachmat([$rachma1->id, $rachma2->id, $rachma3->id, $rachma4->id]);
    
    expect($purchasedIds)->toHaveCount(2);
    expect($purchasedIds)->toContain($rachma1->id, $rachma3->id);
    expect($purchasedIds)->not->toContain($rachma2->id, $rachma4->id);
});

// Order Creation Tests
test('order creation succeeds when no duplicates exist', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma1 = createTestRachma($designer);
    $rachma2 = createTestRachma($designer);

    $requestData = createOrderRequestData([$rachma1->id, $rachma2->id]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/orders', $requestData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'تم إنشاء الطلب بنجاح',
        ]);

    // Check that order was created
    $this->assertDatabaseHas('orders', [
        'client_id' => $user->id,
        'status' => 'pending',
    ]);

    // Check that order items were created
    $this->assertDatabaseHas('order_items', ['rachma_id' => $rachma1->id]);
    $this->assertDatabaseHas('order_items', ['rachma_id' => $rachma2->id]);
});

test('order creation fails when user tries to purchase already owned rachma', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma1 = createTestRachma($designer);
    $rachma2 = createTestRachma($designer);

    // User already purchased rachma1
    createCompletedOrder($user, [$rachma1->id]);

    // Try to purchase rachma1 again
    $requestData = createOrderRequestData([$rachma1->id]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/orders', $requestData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'لقد قمت بشراء بعض الرشمات من قبل',
            'error_type' => 'duplicate_purchase',
        ])
        ->assertJsonStructure([
            'already_purchased' => [
                'rachma_ids',
                'rachma_titles',
                'message'
            ]
        ]);

    // Check that no new order was created
    $this->assertDatabaseMissing('orders', [
        'client_id' => $user->id,
        'status' => 'pending',
    ]);
});

test('order creation fails when user tries to purchase mix of new and owned rachmat', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma1 = createTestRachma($designer);
    $rachma2 = createTestRachma($designer);
    $rachma3 = createTestRachma($designer);

    // User already purchased rachma1 and rachma2
    createCompletedOrder($user, [$rachma1->id, $rachma2->id]);

    // Try to purchase rachma1 (owned), rachma2 (owned), and rachma3 (new)
    $requestData = createOrderRequestData([$rachma1->id, $rachma2->id, $rachma3->id]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/orders', $requestData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'لقد قمت بشراء بعض الرشمات من قبل',
            'error_type' => 'duplicate_purchase',
        ]);

    $responseData = $response->json();
    
    // Check that both owned rachmat are listed
    expect($responseData['already_purchased']['rachma_ids'])->toHaveCount(2);
    expect($responseData['already_purchased']['rachma_ids'])->toContain($rachma1->id, $rachma2->id);
    expect($responseData['already_purchased']['rachma_ids'])->not->toContain($rachma3->id);

    // Check that no new order was created
    $this->assertDatabaseMissing('orders', [
        'client_id' => $user->id,
        'status' => 'pending',
    ]);
});

test('order creation fails when all rachmat are already owned', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma1 = createTestRachma($designer);
    $rachma2 = createTestRachma($designer);

    // User already purchased both rachmat
    createCompletedOrder($user, [$rachma1->id, $rachma2->id]);

    // Try to purchase both again
    $requestData = createOrderRequestData([$rachma1->id, $rachma2->id]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/orders', $requestData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'لقد قمت بشراء بعض الرشمات من قبل',
            'error_type' => 'duplicate_purchase',
        ]);

    $responseData = $response->json();
    
    // Check that both rachmat are listed as already purchased
    expect($responseData['already_purchased']['rachma_ids'])->toHaveCount(2);
    expect($responseData['already_purchased']['rachma_ids'])->toContain($rachma1->id, $rachma2->id);
});

test('duplicate validation works with legacy single-item orders', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma = createTestRachma($designer);

    // Create legacy single-item order (with rachma_id on order table)
    Order::factory()->create([
        'client_id' => $user->id,
        'rachma_id' => $rachma->id,
        'status' => 'completed',
        'amount' => 1000,
    ]);

    // Try to purchase the same rachma again
    $requestData = createOrderRequestData([$rachma->id]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/orders', $requestData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'لقد قمت بشراء بعض الرشمات من قبل',
            'error_type' => 'duplicate_purchase',
        ]);
});

test('duplicate validation ignores pending and rejected orders', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma = createTestRachma($designer);

    // Create pending order (should not block new purchase)
    createCompletedOrder($user, [$rachma->id]);
    Order::where('client_id', $user->id)->update(['status' => 'pending']);

    // Create rejected order (should not block new purchase)
    $rejectedOrder = createCompletedOrder($user, [$rachma->id]);
    $rejectedOrder->update(['status' => 'rejected']);

    // Should be able to purchase since no completed orders exist
    $requestData = createOrderRequestData([$rachma->id]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/orders', $requestData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'تم إنشاء الطلب بنجاح',
        ]);
});

test('duplicate validation handles empty rachma list gracefully', function () {
    $user = createTestUser();
    
    // Test with empty array
    $purchasedIds = $user->hasPurchasedAnyRachmat([]);
    expect($purchasedIds)->toBeEmpty();
});

test('order creation validates rachma existence before duplicate check', function () {
    $user = createTestUser();

    // Try to purchase non-existent rachma
    $requestData = createOrderRequestData([99999]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/orders', $requestData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.rachma_id']);
});

test('duplicate validation works with inactive designer rachmat', function () {
    $user = createTestUser();
    $designer = createTestDesigner();
    $rachma = createTestRachma($designer);

    // User purchased rachma when designer was active
    createCompletedOrder($user, [$rachma->id]);

    // Designer becomes inactive
    $designer->update(['subscription_status' => 'inactive']);

    // Try to purchase again - should fail due to duplicate, not inactive designer
    $requestData = createOrderRequestData([$rachma->id]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/orders', $requestData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'لقد قمت بشراء بعض الرشمات من قبل',
            'error_type' => 'duplicate_purchase',
        ]);
});
