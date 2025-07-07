<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Designer;
use App\Models\Rachma;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MultiItemOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected Designer $designer1;
    protected Designer $designer2;
    protected Rachma $rachma1;
    protected Rachma $rachma2;
    protected Rachma $rachma3;

    protected function setUp(): void
    {
        parent::setUp();

        // Create client user
        $this->client = User::factory()->create([
            'user_type' => 'client',
            'email' => 'client@test.com'
        ]);

        // Create designers with active subscriptions
        $designerUser1 = User::factory()->create(['user_type' => 'designer']);
        $this->designer1 = Designer::factory()->create([
            'user_id' => $designerUser1->id,
            'subscription_status' => 'active'
        ]);

        $designerUser2 = User::factory()->create(['user_type' => 'designer']);
        $this->designer2 = Designer::factory()->create([
            'user_id' => $designerUser2->id,
            'subscription_status' => 'active'
        ]);

        // Create rachmat
        $this->rachma1 = Rachma::factory()->create([
            'designer_id' => $this->designer1->id,
            'price' => 5000.00
        ]);

        $this->rachma2 = Rachma::factory()->create([
            'designer_id' => $this->designer1->id,
            'price' => 3000.00
        ]);

        $this->rachma3 = Rachma::factory()->create([
            'designer_id' => $this->designer2->id,
            'price' => 7000.00
        ]);

        Storage::fake('public');
    }

    #[Test]
    public function client_can_create_single_item_order_backward_compatibility()
    {
        $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->actingAs($this->client, 'api')
            ->postJson('/api/orders', [
                'rachma_id' => $this->rachma1->id,
                'payment_method' => 'ccp',
                'payment_proof' => $paymentProof,
            ]);

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
                    'order_items' => [
                        '*' => [
                            'id',
                            'rachma_id',
                            'price',
                            'rachma'
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'client_id' => $this->client->id,
            'rachma_id' => $this->rachma1->id,
            'amount' => 5000.00,
            'payment_method' => 'ccp',
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('order_items', [
            'rachma_id' => $this->rachma1->id,
            'price' => 5000.00
        ]);
    }

    #[Test]
    public function client_can_create_multi_item_order_with_different_rachmat()
    {
        $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->actingAs($this->client, 'api')
            ->postJson('/api/orders', [
                'items' => [
                    ['rachma_id' => $this->rachma1->id],
                    ['rachma_id' => $this->rachma2->id],
                    ['rachma_id' => $this->rachma3->id],
                ],
                'payment_method' => 'baridi_mob',
                'payment_proof' => $paymentProof,
            ]);

        $response->assertStatus(201);

        $order = Order::latest()->first();
        $this->assertEquals(15000.00, $order->amount); // 5000 + 3000 + 7000
        $this->assertNull($order->rachma_id); // Multi-item orders have null rachma_id
        $this->assertEquals(3, $order->orderItems()->count());

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'rachma_id' => $this->rachma1->id,
            'price' => 5000.00
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'rachma_id' => $this->rachma2->id,
            'price' => 3000.00
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'rachma_id' => $this->rachma3->id,
            'price' => 7000.00
        ]);
    }

    #[Test]
    public function client_can_create_order_with_multiple_instances_of_same_rachma()
    {
        $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->actingAs($this->client, 'api')
            ->postJson('/api/orders', [
                'items' => [
                    ['rachma_id' => $this->rachma1->id],
                    ['rachma_id' => $this->rachma1->id], // Same rachma twice
                    ['rachma_id' => $this->rachma2->id],
                ],
                'payment_method' => 'dahabiya',
                'payment_proof' => $paymentProof,
            ]);

        $response->assertStatus(201);

        $order = Order::latest()->first();
        $this->assertEquals(13000.00, $order->amount); // 5000 + 5000 + 3000
        $this->assertEquals(3, $order->orderItems()->count());

        // Should have two separate order items for the same rachma
        $rachma1Items = $order->orderItems()->where('rachma_id', $this->rachma1->id)->get();
        $this->assertEquals(2, $rachma1Items->count());
        $this->assertEquals(5000.00, $rachma1Items->first()->price);
        $this->assertEquals(5000.00, $rachma1Items->last()->price);
    }

    #[Test]
    public function order_creation_fails_with_invalid_rachma_id()
    {
        $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->actingAs($this->client, 'api')
            ->postJson('/api/orders', [
                'items' => [
                    ['rachma_id' => 999], // Non-existent rachma
                ],
                'payment_method' => 'ccp',
                'payment_proof' => $paymentProof,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.rachma_id']);
    }

    #[Test]
    public function order_creation_fails_with_inactive_designer()
    {
        // Create rachma with inactive designer
        $inactiveDesignerUser = User::factory()->create(['user_type' => 'designer']);
        $inactiveDesigner = Designer::factory()->create([
            'user_id' => $inactiveDesignerUser->id,
            'subscription_status' => 'pending' // Use 'pending' instead of 'inactive'
        ]);
        $inactiveRachma = Rachma::factory()->create([
            'designer_id' => $inactiveDesigner->id
        ]);

        $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->actingAs($this->client, 'api')
            ->postJson('/api/orders', [
                'items' => [
                    ['rachma_id' => $inactiveRachma->id],
                ],
                'payment_method' => 'ccp',
                'payment_proof' => $paymentProof,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => "Rachma '{$inactiveRachma->title}' is not available"
            ]);
    }

    #[Test]
    public function order_creation_validates_maximum_items()
    {
        $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

        // Create 21 items (exceeds max of 20)
        $items = [];
        for ($i = 0; $i < 21; $i++) {
            $items[] = ['rachma_id' => $this->rachma1->id];
        }

        $response = $this->actingAs($this->client, 'api')
            ->postJson('/api/orders', [
                'items' => $items,
                'payment_method' => 'ccp',
                'payment_proof' => $paymentProof,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    #[Test]
    public function client_can_retrieve_order_with_multiple_items()
    {
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'rachma_id' => null,
            'amount' => 8000.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'rachma_id' => $this->rachma1->id,
            'price' => 5000.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'rachma_id' => $this->rachma2->id,
            'price' => 3000.00
        ]);

        $response = $this->actingAs($this->client, 'api')
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'amount',
                    'order_items' => [
                        '*' => [
                            'id',
                            'rachma_id',
                            'price',
                            'rachma' => [
                                'id',
                                'title_ar',
                                'price'
                            ]
                        ]
                    ]
                ]
            ]);

        $responseData = $response->json('data');
        $this->assertEquals(2, count($responseData['order_items']));
        $this->assertEquals(8000.00, $responseData['amount']);
    }

    #[Test]
    public function client_can_get_my_orders_with_multiple_items()
    {
        // Create single-item order
        $singleOrder = Order::factory()->create([
            'client_id' => $this->client->id,
            'rachma_id' => $this->rachma1->id,
            'amount' => 5000.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $singleOrder->id,
            'rachma_id' => $this->rachma1->id,
            'price' => 5000.00
        ]);

        // Create multi-item order
        $multiOrder = Order::factory()->create([
            'client_id' => $this->client->id,
            'rachma_id' => null,
            'amount' => 8000.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $multiOrder->id,
            'rachma_id' => $this->rachma1->id,
            'price' => 5000.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $multiOrder->id,
            'rachma_id' => $this->rachma2->id,
            'price' => 3000.00
        ]);

        $response = $this->actingAs($this->client, 'api')
            ->getJson('/api/my-orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'amount',
                            'order_items'
                        ]
                    ]
                ]
            ]);

        $orders = $response->json('data.data');
        $this->assertEquals(2, count($orders));

        // Check multi-item order
        $multiOrderData = collect($orders)->firstWhere('id', $multiOrder->id);
        $this->assertEquals(2, count($multiOrderData['order_items']));
        $this->assertEquals(8000.00, $multiOrderData['amount']);

        // Check single-item order
        $singleOrderData = collect($orders)->firstWhere('id', $singleOrder->id);
        $this->assertEquals(1, count($singleOrderData['order_items']));
        $this->assertEquals(5000.00, $singleOrderData['amount']);
    }

    #[Test]
    public function order_total_calculation_is_correct_for_multiple_items()
    {
        $paymentProof = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->actingAs($this->client, 'api')
            ->postJson('/api/orders', [
                'items' => [
                    ['rachma_id' => $this->rachma1->id], // 5000
                    ['rachma_id' => $this->rachma1->id], // 5000 (same rachma again)
                    ['rachma_id' => $this->rachma2->id], // 3000
                    ['rachma_id' => $this->rachma3->id], // 7000
                ],
                'payment_method' => 'ccp',
                'payment_proof' => $paymentProof,
            ]);

        $response->assertStatus(201);

        $order = Order::latest()->first();
        $this->assertEquals(20000.00, $order->amount); // 5000 + 5000 + 3000 + 7000

        // Verify individual order items
        $orderItems = $order->orderItems;
        $this->assertEquals(4, $orderItems->count());

        $totalFromItems = $orderItems->sum('price');
        $this->assertEquals($order->amount, $totalFromItems);
    }

    #[Test]
    public function order_model_methods_work_correctly_for_multi_item_orders()
    {
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'rachma_id' => null,
            'amount' => 8000.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'rachma_id' => $this->rachma1->id,
            'price' => 5000.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'rachma_id' => $this->rachma2->id,
            'price' => 3000.00
        ]);

        // Test isMultiItem method
        $this->assertTrue($order->isMultiItem());

        // Test getTotalItemsCount method
        $this->assertEquals(2, $order->getTotalItemsCount());

        // Test recalculateAmount method
        $order->update(['amount' => 0]);
        $order->recalculateAmount();
        $this->assertEquals(8000.00, $order->fresh()->amount);

        // Test getDesigners method
        $designers = $order->getDesigners();
        $this->assertEquals(1, $designers->count()); // Both rachmat belong to same designer
        $this->assertEquals($this->designer1->id, $designers->first()->id);
    }
}
