<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Models\Rachma;
use App\Models\Designer;
use App\Models\Category;
use App\Models\PricingPlan;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class OrderFileDeliveryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $client;
    protected Designer $designer;
    protected Rachma $rachma;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock storage
        Storage::fake('private');

        // Create admin user
        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'email' => 'admin@test.com'
        ]);

        // Create client user with Telegram ID 6494748643 (test user)
        $this->client = User::factory()->create([
            'user_type' => 'client',
            'email' => 'test.telegram@rachmat.com',
            'phone' => '+213555000099',
            'telegram_chat_id' => '6494748643'
        ]);

        // Create designer user and profile
        $designerUser = User::factory()->create([
            'user_type' => 'designer',
            'email' => 'designer@test.com'
        ]);

        $pricingPlan = PricingPlan::factory()->create();
        $this->designer = Designer::factory()->create([
            'user_id' => $designerUser->id,
            'subscription_status' => 'active',
            'pricing_plan_id' => $pricingPlan->id
        ]);

        // Create rachma with files (without category to avoid factory issues)
        $this->rachma = Rachma::factory()->create([
            'designer_id' => $this->designer->id,
            'price' => 2500.00,
            'is_active' => 1,
            'files' => [
                [
                    'id' => 1,
                    'path' => 'rachmat/files/test_rachma.dst',
                    'original_name' => 'test_rachma.dst',
                    'format' => 'DST',
                    'size' => 337685,
                    'is_primary' => true,
                    'uploaded_at' => now()->toDateTimeString(),
                    'description' => 'Tajima DST embroidery file'
                ],
                [
                    'id' => 2,
                    'path' => 'rachmat/files/test_rachma.pes',
                    'original_name' => 'test_rachma.pes',
                    'format' => 'PES',
                    'size' => 362040,
                    'is_primary' => false,
                    'uploaded_at' => now()->toDateTimeString(),
                    'description' => 'Brother PES embroidery file'
                ]
            ]
        ]);

        // Create test files in storage
        Storage::disk('private')->put('rachmat/files/test_rachma.dst', 'fake dst content');
        Storage::disk('private')->put('rachmat/files/test_rachma.pes', 'fake pes content');

        // Create test order
        $this->order = Order::factory()->create([
            'client_id' => $this->client->id,
            'rachma_id' => $this->rachma->id,
            'amount' => 2500.00,
            'status' => 'pending',
            'payment_proof_path' => 'payment_proofs/test_proof.jpg'
        ]);

        // Reset designer earnings to 0 for clean testing
        $this->designer->update(['earnings' => 0]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_admin_can_complete_order_with_successful_file_delivery()
    {
        // Mock TelegramService with all required methods
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('sendRachmaFileWithRetry')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('sendNotification')
                ->once()
                ->with($this->client->telegram_chat_id, \Mockery::type('string'))
                ->andReturn(true);
        });

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف',
                'rejection_reason' => ''
            ]);

        $response->assertRedirect(route('admin.orders.show', $this->order));

        // Verify order status and timestamps
        $this->order->refresh();
        $this->assertEquals('completed', $this->order->status);
        $this->assertEquals('تم تأكيد الطلب وإرسال الملف', $this->order->admin_notes);
        $this->assertNotNull($this->order->completed_at);
        $this->assertNotNull($this->order->confirmed_at);
        $this->assertNotNull($this->order->file_sent_at);

        // Verify designer earnings were updated
        $this->designer->refresh();
        $expectedEarnings = $this->order->amount * 0.7; // 70% commission
        $this->assertEquals($expectedEarnings, $this->designer->earnings);
    }

    public function test_admin_cannot_complete_order_when_file_delivery_fails()
    {
        // Mock TelegramService to return false (delivery failed)
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('sendRachmaFileWithRetry')
                ->once()
                ->andReturn(false);
        });

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف',
                'rejection_reason' => ''
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['file_delivery']);

        // Verify order status remains unchanged
        $this->order->refresh();
        $this->assertEquals('pending', $this->order->status);
        $this->assertNull($this->order->completed_at);
        $this->assertNull($this->order->confirmed_at);
        $this->assertNull($this->order->file_sent_at);

        // Verify designer earnings were NOT updated
        $this->designer->refresh();
        $this->assertEquals(0, $this->designer->earnings);
    }

    public function test_admin_cannot_complete_order_when_client_has_no_telegram()
    {
        // Remove telegram_chat_id from client
        $this->client->update(['telegram_chat_id' => null]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف',
                'rejection_reason' => ''
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['file_delivery']);

        // Verify order status remains unchanged
        $this->order->refresh();
        $this->assertEquals('pending', $this->order->status);
    }

    public function test_admin_cannot_complete_order_when_rachma_has_no_files()
    {
        // Update rachma to have no files
        $this->rachma->update(['files' => []]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف',
                'rejection_reason' => ''
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['file_delivery']);

        // Verify order status remains unchanged
        $this->order->refresh();
        $this->assertEquals('pending', $this->order->status);
    }

    public function test_file_delivery_check_endpoint_returns_correct_data()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $this->order));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Orders/Show')
                ->has('order')
                ->where('order.id', $this->order->id)
                ->where('order.status', 'pending')
                ->where('order.client.telegram_chat_id', '6494748643')
        );
    }

    public function test_delivery_check_fails_when_client_has_no_telegram()
    {
        // Remove telegram_chat_id from client
        $this->client->update(['telegram_chat_id' => null]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $this->order));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Orders/Show')
                ->has('order')
                ->where('order.client.telegram_chat_id', null)
        );
    }

    public function test_order_status_update_logs_are_created()
    {
        Log::shouldReceive('info')
            ->atLeast()
            ->once()
            ->with('Order update request', Mockery::type('array'));

        Log::shouldReceive('info')
            ->atLeast()
            ->once()
            ->with('File successfully delivered for order completion', Mockery::type('array'));

        // Mock successful file delivery
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('sendRachmaFileWithRetry')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('sendNotification')
                ->once()
                ->with($this->client->telegram_chat_id, \Mockery::type('string'))
                ->andReturn(true);
        });

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف',
                'rejection_reason' => ''
            ]);

        // Assert that the request was successful
        $response->assertRedirect(route('admin.orders.show', $this->order));
    }

    public function test_specific_order_202_functionality()
    {
        // Update the order ID to 202 for specific testing
        $this->order->id = 202;
        $this->order->save();

        // Mock successful file delivery
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('sendRachmaFileWithRetry')
                ->once()
                ->with(Mockery::on(function ($order) {
                    return $order->id === 202;
                }))
                ->andReturn(true);

            $mock->shouldReceive('sendNotification')
                ->once()
                ->with($this->client->telegram_chat_id, \Mockery::type('string'))
                ->andReturn(true);
        });

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', 202), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف للطلب رقم 202',
                'rejection_reason' => ''
            ]);

        $response->assertRedirect(route('admin.orders.show', 202));

        // Verify the specific order 202 was updated
        $order202 = Order::find(202);
        $this->assertNotNull($order202);
        $this->assertEquals('completed', $order202->status);
        $this->assertEquals('تم تأكيد الطلب وإرسال الملف للطلب رقم 202', $order202->admin_notes);
        $this->assertNotNull($order202->completed_at);
    }
}
