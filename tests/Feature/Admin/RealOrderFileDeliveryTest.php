<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

/**
 * Test file delivery functionality with real order 202 from the database
 * This test does NOT use RefreshDatabase to work with existing data
 */
class RealOrderFileDeliveryTest extends TestCase
{
    protected User $admin;
    protected MockInterface $telegramService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create or get admin user for testing
        $this->admin = User::where('user_type', 'admin')->first();
        if (!$this->admin) {
            $this->admin = User::factory()->create([
                'user_type' => 'admin',
                'email' => 'test.admin@rachmat.com'
            ]);
        }

        // Mock TelegramService to avoid actual Telegram API calls during testing
        $this->telegramService = Mockery::mock(TelegramService::class);
        $this->app->instance(TelegramService::class, $this->telegramService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function order_202_exists_and_has_required_data()
    {
        $order = Order::find(202);
        
        $this->assertNotNull($order, 'Order 202 should exist in the database');
        $this->assertNotNull($order->client, 'Order 202 should have a client');
        $this->assertNotNull($order->rachma, 'Order 202 should have a rachma');
        
        // Log order details for debugging
        Log::info('Order 202 details', [
            'id' => $order->id,
            'status' => $order->status,
            'client_id' => $order->client_id,
            'client_telegram_id' => $order->client->telegram_chat_id ?? 'none',
            'rachma_id' => $order->rachma_id,
            'has_files' => $order->rachma ? $order->rachma->hasFiles() : false,
            'files_count' => $order->rachma ? count($order->rachma->files) : 0
        ]);
    }

    /** @test */
    public function order_202_client_has_telegram_id_6494748643()
    {
        $order = Order::find(202);
        $this->assertNotNull($order, 'Order 202 should exist');
        
        $client = $order->client;
        $this->assertNotNull($client, 'Order 202 should have a client');
        $this->assertEquals('6494748643', $client->telegram_chat_id, 
            'Order 202 client should have Telegram ID 6494748643');
    }

    /** @test */
    public function order_202_rachma_has_files()
    {
        $order = Order::find(202);
        $this->assertNotNull($order, 'Order 202 should exist');
        
        $rachma = $order->rachma;
        $this->assertNotNull($rachma, 'Order 202 should have a rachma');
        $this->assertTrue($rachma->hasFiles(), 'Order 202 rachma should have files');
        $this->assertGreaterThan(0, count($rachma->files), 'Order 202 rachma should have at least one file');
    }

    /** @test */
    public function order_202_delivery_check_returns_valid_data()
    {
        $order = Order::find(202);
        $this->assertNotNull($order, 'Order 202 should exist');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.delivery-check', 202));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('canComplete', $data);
        $this->assertArrayHasKey('clientHasTelegram', $data);
        $this->assertArrayHasKey('hasFiles', $data);
        $this->assertArrayHasKey('filesCount', $data);
        $this->assertArrayHasKey('totalSize', $data);
        
        // Log delivery check results
        Log::info('Order 202 delivery check results', $data);
        
        // Verify the data makes sense
        $this->assertTrue($data['clientHasTelegram'], 'Client should have Telegram linked');
        $this->assertTrue($data['hasFiles'], 'Order should have files');
        $this->assertGreaterThan(0, $data['filesCount'], 'Should have at least one file');
    }

    /** @test */
    public function order_202_can_be_completed_with_successful_file_delivery()
    {
        $order = Order::find(202);
        $this->assertNotNull($order, 'Order 202 should exist');
        
        // Store original status to restore later
        $originalStatus = $order->status;
        $originalNotes = $order->admin_notes;
        $originalCompletedAt = $order->completed_at;
        
        // Mock successful file delivery
        $this->telegramService
            ->shouldReceive('sendRachmaFileWithRetry')
            ->once()
            ->with(Mockery::on(function ($orderParam) {
                return $orderParam->id === 202;
            }))
            ->andReturn(true);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', 202), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف - اختبار تلقائي',
                'rejection_reason' => ''
            ]);

        $response->assertRedirect(route('admin.orders.show', 202));

        // Verify order was updated
        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertEquals('تم تأكيد الطلب وإرسال الملف - اختبار تلقائي', $order->admin_notes);
        $this->assertNotNull($order->completed_at);
        $this->assertNotNull($order->confirmed_at);
        $this->assertNotNull($order->file_sent_at);

        // Log the successful update
        Log::info('Order 202 successfully updated to completed', [
            'id' => $order->id,
            'status' => $order->status,
            'completed_at' => $order->completed_at,
            'admin_notes' => $order->admin_notes
        ]);

        // Restore original state for other tests
        $order->update([
            'status' => $originalStatus,
            'admin_notes' => $originalNotes,
            'completed_at' => $originalCompletedAt,
            'confirmed_at' => null,
            'file_sent_at' => null
        ]);
    }

    /** @test */
    public function order_202_completion_fails_when_file_delivery_fails()
    {
        $order = Order::find(202);
        $this->assertNotNull($order, 'Order 202 should exist');
        
        // Store original status
        $originalStatus = $order->status;
        
        // Mock failed file delivery
        $this->telegramService
            ->shouldReceive('sendRachmaFileWithRetry')
            ->once()
            ->with(Mockery::on(function ($orderParam) {
                return $orderParam->id === 202;
            }))
            ->andReturn(false);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', 202), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف - اختبار فشل',
                'rejection_reason' => ''
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['file_delivery']);

        // Verify order status remains unchanged
        $order->refresh();
        $this->assertEquals($originalStatus, $order->status);
        
        Log::info('Order 202 completion correctly failed when file delivery failed', [
            'id' => $order->id,
            'status' => $order->status,
            'error' => 'File delivery failed as expected'
        ]);
    }

    /** @test */
    public function order_202_designer_earnings_are_updated_on_completion()
    {
        $order = Order::find(202);
        $this->assertNotNull($order, 'Order 202 should exist');
        
        $designer = $order->rachma->designer;
        $this->assertNotNull($designer, 'Order 202 should have a designer');
        
        // Store original earnings
        $originalEarnings = $designer->earnings;
        
        // Mock successful file delivery
        $this->telegramService
            ->shouldReceive('sendRachmaFileWithRetry')
            ->once()
            ->andReturn(true);

        $this->actingAs($this->admin)
            ->put(route('admin.orders.update', 202), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف - اختبار الأرباح',
                'rejection_reason' => ''
            ]);

        // Verify designer earnings were updated
        $designer->refresh();
        $expectedEarnings = $originalEarnings + ($order->amount * 0.7); // 70% commission
        $this->assertEquals($expectedEarnings, $designer->earnings);
        
        Log::info('Order 202 designer earnings updated correctly', [
            'designer_id' => $designer->id,
            'original_earnings' => $originalEarnings,
            'order_amount' => $order->amount,
            'commission' => $order->amount * 0.7,
            'new_earnings' => $designer->earnings
        ]);

        // Restore original earnings
        $designer->update(['earnings' => $originalEarnings]);
        
        // Restore original order status
        $order->update([
            'status' => 'pending',
            'completed_at' => null,
            'confirmed_at' => null,
            'file_sent_at' => null
        ]);
    }

    /** @test */
    public function order_202_show_page_displays_correctly()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.show', 202));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Orders/Show')
                ->has('order')
                ->where('order.id', 202)
        );
    }

    /** @test */
    public function order_202_admin_can_access_edit_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.edit', 202));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Orders/Edit')
                ->has('order')
                ->where('order.id', 202)
        );
    }

    /** @test */
    public function order_202_validation_prevents_completion_without_files()
    {
        // This test verifies that the validation system works
        // We'll temporarily remove files from the rachma to test validation
        $order = Order::find(202);
        $rachma = $order->rachma;
        
        // Store original files
        $originalFiles = $rachma->files;
        
        // Temporarily remove files
        $rachma->update(['files' => []]);
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', 202), [
                'status' => 'completed',
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف - اختبار بدون ملفات',
                'rejection_reason' => ''
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['file_delivery']);
        
        // Restore original files
        $rachma->update(['files' => $originalFiles]);
        
        Log::info('Order 202 validation correctly prevented completion without files');
    }
}
