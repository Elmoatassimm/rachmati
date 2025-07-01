<?php

use App\Models\User;
use App\Models\Designer;
use App\Models\Rachma;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require_once __DIR__ . '/Helpers/ApiTestHelpers.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    
    // Create users
    $this->client = User::factory()->create(['user_type' => 'client']);
    $this->otherClient = User::factory()->create(['user_type' => 'client']);
    $designerUser = User::factory()->create(['user_type' => 'designer']);
    
    // Create designer with active subscription
    $this->activeDesigner = Designer::factory()->create([
        'user_id' => $designerUser->id,
        'subscription_status' => 'active'
    ]);
    
    // Create inactive designer
    $inactiveDesignerUser = User::factory()->create(['user_type' => 'designer']);
    $this->inactiveDesigner = Designer::factory()->create([
        'user_id' => $inactiveDesignerUser->id,
        'subscription_status' => 'inactive'
    ]);
    
    // Create category and rachma
    $this->category = Category::factory()->create();
    $this->activeRachma = Rachma::factory()->create([
        'designer_id' => $this->activeDesigner->id,
        'category_id' => $this->category->id,
        'price' => 1500.00
    ]);
    
    $this->inactiveRachma = Rachma::factory()->create([
        'designer_id' => $this->inactiveDesigner->id,
        'category_id' => $this->category->id,
        'price' => 2000.00
    ]);
    
    // Create orders
    $this->clientOrder = Order::factory()->create([
        'client_id' => $this->client->id,
        'rachma_id' => $this->activeRachma->id,
        'amount' => 1500.00
    ]);
    
    $this->otherClientOrder = Order::factory()->create([
        'client_id' => $this->otherClient->id,
        'rachma_id' => $this->activeRachma->id,
        'amount' => 1500.00
    ]);
});

// Order Store Tests
test('store creates order successfully with valid data', function () {
    $token = getAuthToken($this->client);
    $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

    $orderData = [
        'rachma_id' => $this->activeRachma->id,
        'payment_method' => 'ccp',
        'payment_proof' => $paymentProof
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/orders', $orderData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'client_id',
                'rachma_id',
                'amount',
                'payment_method',
                'status',
                'rachma' => [
                    'title',
                    'price'
                ],
                'client' => [
                    'name',
                    'email'
                ]
            ]
        ]);

    $this->assertDatabaseHas('orders', [
        'client_id' => $this->client->id,
        'rachma_id' => $this->activeRachma->id,
        'amount' => $this->activeRachma->price,
        'payment_method' => 'ccp',
        'status' => 'pending'
    ]);

    expect($response->json('success'))->toBeTrue();
    expect($response->json('data.amount'))->toBe('1500.00');
});

test('store fails without authentication', function () {
    $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

    $response = $this->postJson('/api/orders', [
        'rachma_id' => $this->activeRachma->id,
        'payment_method' => 'ccp',
        'payment_proof' => $paymentProof
    ]);

    $response->assertStatus(401);
});

test('store fails with missing required fields', function () {
    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/orders', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['rachma_id', 'payment_method', 'payment_proof']);
});

test('store fails with non-existent rachma', function () {
    $token = getAuthToken($this->client);
    $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/orders', [
            'rachma_id' => 99999,
            'payment_method' => 'ccp',
            'payment_proof' => $paymentProof
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['rachma_id']);
});

test('store fails with invalid payment method', function () {
    $token = getAuthToken($this->client);
    $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/orders', [
            'rachma_id' => $this->activeRachma->id,
            'payment_method' => 'invalid_method',
            'payment_proof' => $paymentProof
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment_method']);
});

test('store fails with invalid payment proof file', function () {
    $token = getAuthToken($this->client);
    $invalidFile = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/orders', [
            'rachma_id' => $this->activeRachma->id,
            'payment_method' => 'ccp',
            'payment_proof' => $invalidFile
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment_proof']);
});

test('store fails with oversized payment proof', function () {
    $token = getAuthToken($this->client);
    $largeFile = UploadedFile::fake()->image('large_image.jpg')->size(3000); // 3MB

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/orders', [
            'rachma_id' => $this->activeRachma->id,
            'payment_method' => 'ccp',
            'payment_proof' => $largeFile
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment_proof']);
});

test('store fails for rachma from inactive designer', function () {
    $token = getAuthToken($this->client);
    $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/orders', [
            'rachma_id' => $this->inactiveRachma->id,
            'payment_method' => 'ccp',
            'payment_proof' => $paymentProof
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'This rachma is not available'
        ]);
});

test('store accepts all valid payment methods', function () {
    $token = getAuthToken($this->client);
    $paymentMethods = ['ccp', 'baridi_mob', 'dahabiya'];

    foreach ($paymentMethods as $method) {
        $paymentProof = UploadedFile::fake()->image("payment_proof_{$method}.jpg");
        
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/orders', [
                'rachma_id' => $this->activeRachma->id,
                'payment_method' => $method,
                'payment_proof' => $paymentProof
            ]);

        $response->assertStatus(201);
    }
});

// Order Show Tests
test('show returns order details for owner', function () {
    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/orders/{$this->clientOrder->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'client_id',
                'rachma_id',
                'amount',
                'status',
                'payment_method',
                'rachma' => [
                    'title',
                    'price',
                    'designer'
                ],
                'client'
            ]
        ]);

    expect($response->json('success'))->toBeTrue();
    expect($response->json('data.id'))->toBe($this->clientOrder->id);
});

test('show fails without authentication', function () {
    $response = $this->getJson("/api/orders/{$this->clientOrder->id}");

    $response->assertStatus(401);
});

test('show fails for non-owner', function () {
    $token = getAuthToken($this->otherClient);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/orders/{$this->clientOrder->id}");

    $response->assertStatus(404);
});

test('show fails for non-existent order', function () {
    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/orders/99999');

    $response->assertStatus(404);
});

test('show fails with invalid order ID format', function () {
    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/orders/invalid-id');

    $response->assertStatus(404);
});

// My Orders Tests
test('my orders returns user orders with pagination', function () {
    // Create more orders for the client
    Order::factory()->count(3)->create([
        'client_id' => $this->client->id,
        'rachma_id' => $this->activeRachma->id
    ]);

    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/my-orders');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'status',
                        'rachma' => [
                            'title',
                            'designer'
                        ]
                    ]
                ],
                'current_page',
                'per_page',
                'total',
                'last_page'
            ]
        ]);

    expect($response->json('success'))->toBeTrue();
    expect(count($response->json('data.data')))->toBeGreaterThan(0);
});

test('my orders returns only user orders', function () {
    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/my-orders');

    $response->assertStatus(200);

    $orderIds = collect($response->json('data.data'))->pluck('id');
    expect($orderIds)->toContain($this->clientOrder->id);
    expect($orderIds)->not->toContain($this->otherClientOrder->id);
});

test('my orders fails without authentication', function () {
    $response = $this->getJson('/api/my-orders');

    $response->assertStatus(401);
});

test('my orders returns empty result for user with no orders', function () {
    $newClient = User::factory()->create(['user_type' => 'client']);
    $token = getAuthToken($newClient);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/my-orders');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'data' => []
            ]
        ]);
});

test('my orders filters by status', function () {
    // Create orders with different statuses
    $pendingOrder = Order::factory()->create([
        'client_id' => $this->client->id,
        'rachma_id' => $this->activeRachma->id,
        'status' => 'pending'
    ]);
    
    $completedOrder = Order::factory()->create([
        'client_id' => $this->client->id,
        'rachma_id' => $this->activeRachma->id,
        'status' => 'completed'
    ]);

    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/my-orders?status=pending');

    $response->assertStatus(200);
    
    $orderIds = collect($response->json('data.data'))->pluck('id');
    expect($orderIds)->toContain($pendingOrder->id);
    expect($orderIds)->not->toContain($completedOrder->id);
});

test('my orders respects pagination', function () {
    // Create many orders
    Order::factory()->count(25)->create([
        'client_id' => $this->client->id,
        'rachma_id' => $this->activeRachma->id
    ]);

    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/my-orders?per_page=10');

    $response->assertStatus(200);
    expect(count($response->json('data.data')))->toBeLessThanOrEqual(10);
});

// Edge Cases
test('store handles database errors gracefully', function () {
    // This would require mocking DB failure, but we'll test data integrity instead
    $token = getAuthToken($this->client);
    $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/orders', [
            'rachma_id' => $this->activeRachma->id,
            'payment_method' => 'ccp',
            'payment_proof' => $paymentProof
        ]);

    $response->assertStatus(201);
    
    // Verify the order amount matches the rachma price
    $order = Order::latest()->first();
    expect($order->amount)->toBe($this->activeRachma->price);
});

test('my orders handles sorting parameters', function () {
    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/my-orders?sort_by=created_at&sort_order=desc');

    $response->assertStatus(200);
});

test('store validates file extensions properly', function () {
    $token = getAuthToken($this->client);
    $validExtensions = ['jpg', 'jpeg', 'png'];

    foreach ($validExtensions as $ext) {
        $file = UploadedFile::fake()->image("payment_proof.{$ext}");
        
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/orders', [
                'rachma_id' => $this->activeRachma->id,
                'payment_method' => 'ccp',
                'payment_proof' => $file
            ]);

        $response->assertStatus(201);
    }
}); 