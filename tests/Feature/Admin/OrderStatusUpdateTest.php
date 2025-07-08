<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Models\Rachma;
use App\Models\Designer;
use App\Models\PricingPlan;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Mockery;

class OrderStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

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

        // Create client user with Telegram ID
        $this->client = User::factory()->create([
            'user_type' => 'client',
            'email' => 'client@test.com',
            'telegram_chat_id' => '6494748643'
        ]);

        // Create designer
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

        // Create rachma with files
        $this->rachma = Rachma::factory()->create([
            'designer_id' => $this->designer->id,
            'price' => 2500.00,
            'is_active' => 1,
            'files' => [
                [
                    'id' => 1,
                    'path' => 'rachmat/files/test.dst',
                    'original_name' => 'test.dst',
                    'format' => 'DST',
                    'size' => 100000,
                    'is_primary' => true,
                    'uploaded_at' => now()->toDateTimeString(),
                    'description' => 'Test file'
                ]
            ]
        ]);

        // Create test file in storage
        Storage::disk('private')->put('rachmat/files/test.dst', 'fake content');

        // Create order
        $this->order = Order::factory()->create([
            'client_id' => $this->client->id,
            'rachma_id' => $this->rachma->id,
            'amount' => 2500.00,
            'status' => 'pending',
            'payment_proof_path' => 'test/proof.jpg'
        ]);

        // Reset designer earnings to 0 for clean testing
        $this->designer->update(['earnings' => 0]);
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

        // Verify order was updated
        $this->order->refresh();
        $this->assertEquals('completed', $this->order->status);
        $this->assertNotNull($this->order->completed_at);

        // Verify designer earnings were updated
        $this->designer->refresh();
        $expectedEarnings = $this->order->amount * 0.7;
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

    public function test_order_completion_updates_all_required_timestamps()
    {
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

        $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف',
                'rejection_reason' => ''
            ]);

        $this->order->refresh();

        // Verify all timestamps are set
        $this->assertEquals('completed', $this->order->status);
        $this->assertNotNull($this->order->completed_at);
        $this->assertNotNull($this->order->confirmed_at);
        $this->assertNotNull($this->order->file_sent_at);
        $this->assertEquals('تم تأكيد الطلب وإرسال الملف', $this->order->admin_notes);
    }

    public function test_order_rejection_works_correctly()
    {
        // Mock TelegramService for rejection notification
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('sendNotification')
                ->once()
                ->with($this->client->telegram_chat_id, \Mockery::type('string'))
                ->andReturn(true);
        });

        $rejectionReason = 'Invalid payment proof';

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'rejected',
                'admin_notes' => 'Order rejected',
                'rejection_reason' => $rejectionReason,
            ]);

        $response->assertRedirect(route('admin.orders.show', $this->order));

        $this->order->refresh();
        $this->assertEquals('rejected', $this->order->status);
        $this->assertEquals($rejectionReason, $this->order->rejection_reason);
        $this->assertNotNull($this->order->rejected_at);

        // Verify designer earnings were NOT updated
        $this->designer->refresh();
        $this->assertEquals(0, $this->designer->earnings);
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
