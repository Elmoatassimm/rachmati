<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Rachma;
use App\Models\Designer;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Mockery;
use Mockery\MockInterface;

class TelegramBotIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private MockInterface $telegramService;
    private User $client;
    private User $admin;
    private Designer $designer;
    private Rachma $rachma;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'email' => 'admin@test.com'
        ]);

        $this->client = User::factory()->create([
            'user_type' => 'client',
            'phone' => '+213555123456',
            'telegram_chat_id' => null
        ]);

        // Create designer with user
        $designerUser = User::factory()->create([
            'user_type' => 'designer',
            'email' => 'designer@test.com'
        ]);

        $this->designer = Designer::factory()->create([
            'user_id' => $designerUser->id,
            'store_name' => 'Test Designer Store',
            'subscription_status' => 'active'
        ]);

        // Create rachma and order
        $this->rachma = Rachma::factory()->create([
            'designer_id' => $this->designer->id,
            'title' => 'Test Rachma',
            'file_path' => 'test/rachma.dst'
        ]);

        $this->order = Order::factory()->create([
            'client_id' => $this->client->id,
            'rachma_id' => $this->rachma->id,
            'status' => 'pending',
            'amount' => 1000
        ]);

        // Mock Telegram service for testing
        $this->telegramService = Mockery::mock(TelegramService::class);
        $this->app->instance(TelegramService::class, $this->telegramService);
    }

    /** @test */
    public function webhook_endpoint_accepts_valid_telegram_updates()
    {
        $webhookData = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 123456,
                    'is_bot' => false,
                    'first_name' => 'Test',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => 123456,
                    'first_name' => 'Test',
                    'type' => 'private'
                ],
                'date' => now()->timestamp,
                'text' => '/start'
            ]
        ];

        $this->telegramService
            ->shouldReceive('processWebhookUpdate')
            ->once()
            ->with($webhookData)
            ->andReturn(true);

        $response = $this->postJson('/api/telegram/webhook', $webhookData);

        $response->assertStatus(200)
                ->assertJson(['status' => 'ok']);
    }

    /** @test */
    public function webhook_endpoint_rejects_invalid_data()
    {
        $invalidData = [
            'invalid' => 'data'
        ];

        $response = $this->postJson('/api/telegram/webhook', $invalidData);

        $response->assertStatus(400)
                ->assertJson(['error' => 'Invalid data']);
    }

    /** @test */
    public function webhook_endpoint_has_rate_limiting()
    {
        $webhookData = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => ['id' => 123456, 'is_bot' => false, 'first_name' => 'Test'],
                'chat' => ['id' => 123456, 'type' => 'private'],
                'date' => now()->timestamp,
                'text' => '/start'
            ]
        ];

        $this->telegramService
            ->shouldReceive('processWebhookUpdate')
            ->andReturn(true);

        // Send 70 requests (more than the 60 per minute limit)
        for ($i = 0; $i < 70; $i++) {
            $response = $this->postJson('/api/telegram/webhook', $webhookData);
            
            if ($i < 60) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Rate limit exceeded
                break;
            }
        }
    }

    /** @test */
    public function start_command_sends_welcome_message()
    {
        $chatId = '123456789';
        $updateData = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => ['id' => 123456, 'is_bot' => false, 'first_name' => 'Test'],
                'chat' => ['id' => (int) $chatId, 'type' => 'private'],
                'date' => now()->timestamp,
                'text' => '/start'
            ]
        ];

        $this->telegramService
            ->shouldReceive('sendNotificationWithRetry')
            ->once()
            ->with($chatId, Mockery::pattern('/مرحباً بك في منصة رشمات/'))
            ->andReturn(true);

        // Use real service for this test
        $realService = new TelegramService();
        $realService->processWebhookUpdate($updateData);
    }

    /** @test */
    public function user_id_links_user_to_telegram_chat()
    {
        $chatId = '123456789';
        $updateData = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => ['id' => 123456, 'is_bot' => false, 'first_name' => 'Test'],
                'chat' => ['id' => (int) $chatId, 'type' => 'private'],
                'date' => now()->timestamp,
                'text' => "/start {$this->client->id}"
            ]
        ];

        $this->telegramService
            ->shouldReceive('sendNotificationWithRetry')
            ->once()
            ->with($chatId, Mockery::pattern('/تم ربط حسابك بنجاح/'))
            ->andReturn(true);

        // Use real service to test user linking
        $realService = new TelegramService();
        $realService->processWebhookUpdate($updateData);

        // Verify user was linked
        $this->client->refresh();
        $this->assertEquals($chatId, $this->client->telegram_chat_id);
    }

    /** @test */
    public function invalid_user_id_shows_error_message()
    {
        $chatId = '123456789';
        $nonExistentUserId = 99999; // Non-existent user ID
        $updateData = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => ['id' => 123456, 'is_bot' => false, 'first_name' => 'Test'],
                'chat' => ['id' => (int) $chatId, 'type' => 'private'],
                'date' => now()->timestamp,
                'text' => "/start {$nonExistentUserId}"
            ]
        ];

        $this->telegramService
            ->shouldReceive('sendNotificationWithRetry')
            ->once()
            ->with($chatId, Mockery::pattern('/لم يتم العثور على حساب/'))
            ->andReturn(true);

        $realService = new TelegramService();
        $realService->processWebhookUpdate($updateData);
    }

    /** @test */
    public function admin_can_set_webhook()
    {
        $this->telegramService
            ->shouldReceive('setWebhook')
            ->once()
            ->with('https://example.com/api/telegram/webhook')
            ->andReturn(true);

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/telegram/set-webhook', [
                'url' => 'https://example.com/api/telegram/webhook'
            ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Webhook set successfully'
                ]);
    }

    /** @test */
    public function admin_can_remove_webhook()
    {
        $this->telegramService
            ->shouldReceive('removeWebhook')
            ->once()
            ->andReturn(true);

        $response = $this->actingAs($this->admin)
            ->deleteJson('/admin/telegram/remove-webhook');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Webhook removed successfully'
                ]);
    }

    /** @test */
    public function admin_can_get_webhook_info()
    {
        $webhookInfo = [
            'url' => 'https://example.com/api/telegram/webhook',
            'has_custom_certificate' => false,
            'pending_update_count' => 0
        ];

        $this->telegramService
            ->shouldReceive('getWebhookInfo')
            ->once()
            ->andReturn($webhookInfo);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/telegram/webhook-info');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => $webhookInfo
                ]);
    }

    /** @test */
    public function admin_can_test_bot_connection()
    {
        $this->telegramService
            ->shouldReceive('verifyConnection')
            ->once()
            ->andReturn(true);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/telegram/test-connection');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Bot connection successful'
                ]);
    }

    /** @test */
    public function rachma_file_is_sent_when_order_completed()
    {
        // Link client to Telegram chat
        $this->client->update(['telegram_chat_id' => '123456789']);

        // Create a mock file for testing
        Storage::fake('local');
        Storage::put($this->rachma->file_path, 'fake rachma file content');

        $this->telegramService
            ->shouldReceive('sendRachmaFileWithRetry')
            ->once()
            ->with(Mockery::on(function ($order) {
                return $order->id === $this->order->id;
            }))
            ->andReturn(true);

        $this->telegramService
            ->shouldReceive('sendNotification')
            ->once()
            ->andReturn(true);

        // Update order status to completed
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/orders/{$this->order->id}", [
                'status' => 'completed',
                'admin_notes' => 'Order completed'
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function file_delivery_failure_sends_notification()
    {
        // Link client to Telegram chat
        $this->client->update(['telegram_chat_id' => '123456789']);

        $this->telegramService
            ->shouldReceive('sendRachmaFileWithRetry')
            ->once()
            ->andReturn(false); // Simulate file delivery failure

        $this->telegramService
            ->shouldReceive('sendNotification')
            ->twice() // Once for status change, once for file delivery failure
            ->andReturn(true);

        // Update order status to completed
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/orders/{$this->order->id}", [
                'status' => 'completed',
                'admin_notes' => 'Order completed'
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function telegram_notifications_are_sent_for_status_changes()
    {
        // Link client to Telegram chat
        $this->client->update(['telegram_chat_id' => '123456789']);

        $this->telegramService
            ->shouldReceive('sendNotification')
            ->once()
            ->with('123456789', Mockery::pattern('/تم إكمال طلبك/'))
            ->andReturn(true);

        $this->telegramService
            ->shouldReceive('sendRachmaFileWithRetry')
            ->once()
            ->andReturn(true);

        // Update order status to completed
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/orders/{$this->order->id}", [
                'status' => 'completed',
                'admin_notes' => 'Order completed'
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function health_check_endpoint_works()
    {
        $response = $this->getJson('/api/telegram/health');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'service',
                    'timestamp'
                ]);
    }

    /** @test */
    public function webhook_validates_secret_token_when_configured()
    {
        config(['services.telegram.webhook_secret' => 'test-secret']);

        $webhookData = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => ['id' => 123456, 'is_bot' => false, 'first_name' => 'Test'],
                'chat' => ['id' => 123456, 'type' => 'private'],
                'date' => now()->timestamp,
                'text' => '/start'
            ]
        ];

        // Request without secret token should fail
        $response = $this->postJson('/api/telegram/webhook', $webhookData);
        $response->assertStatus(401);

        // Request with correct secret token should succeed
        $this->telegramService
            ->shouldReceive('processWebhookUpdate')
            ->once()
            ->andReturn(true);

        $response = $this->postJson('/api/telegram/webhook', $webhookData, [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret'
        ]);
        $response->assertStatus(200);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 