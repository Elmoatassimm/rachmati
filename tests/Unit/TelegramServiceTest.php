<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TelegramService;
use App\Models\User;
use App\Models\Order;
use App\Models\Rachma;
use App\Models\Designer;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Mockery;

class TelegramServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private Order $order;
    private Rachma $rachma;
    private Designer $designer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $designerUser = User::factory()->create(['user_type' => 'designer']);
        $this->designer = Designer::factory()->create([
            'user_id' => $designerUser->id,
            'store_name' => 'Test Store'
        ]);

        $this->client = User::factory()->create([
            'user_type' => 'client',
            'phone' => '+213555123456',
            'telegram_chat_id' => '123456789'
        ]);

        $this->rachma = Rachma::factory()->create([
            'designer_id' => $this->designer->id,
            'title' => 'Test Rachma',
            'file_path' => 'test/rachma.dst',
            'size' => 'Medium',
            'gharazat' => 1500
        ]);

        $this->order = Order::factory()->create([
            'client_id' => $this->client->id,
            'rachma_id' => $this->rachma->id,
            'amount' => 1000,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_can_process_start_command()
    {
        $updateData = [
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
                    'id' => 123456789,
                    'first_name' => 'Test',
                    'type' => 'private'
                ],
                'date' => now()->timestamp,
                'text' => '/start'
            ]
        ];

        // Mock the bot API
        $mockBot = Mockery::mock(BotApi::class);
        $mockBot->shouldReceive('sendMessage')
            ->once()
            ->with(
                '123456789',
                Mockery::pattern('/مرحباً بك في منصة رشمات/'),
                'Markdown',
                false,
                null,
                null
            )
            ->andReturn(true);

        // Create service with mocked bot
        $service = new TelegramService();
        $reflection = new \ReflectionClass($service);
        $telegramProperty = $reflection->getProperty('telegram');
        $telegramProperty->setAccessible(true);
        $telegramProperty->setValue($service, $mockBot);

        $result = $service->processWebhookUpdate($updateData);
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_link_user_by_phone_number()
    {
        // Create user without telegram_chat_id
        $user = User::factory()->create([
            'user_type' => 'client',
            'phone' => '+213555987654',
            'telegram_chat_id' => null
        ]);

        $updateData = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 123456,
                    'is_bot' => false,
                    'first_name' => 'Test'
                ],
                'chat' => [
                    'id' => 987654321,
                    'type' => 'private'
                ],
                'date' => now()->timestamp,
                'text' => '+213555987654'
            ]
        ];

        // Mock the bot API
        $mockBot = Mockery::mock(BotApi::class);
        $mockBot->shouldReceive('sendMessage')
            ->once()
            ->with(
                '987654321',
                Mockery::pattern('/تم ربط حسابك بنجاح/'),
                'Markdown',
                false,
                null,
                null
            )
            ->andReturn(true);

        // Create service with mocked bot
        $service = new TelegramService();
        $reflection = new \ReflectionClass($service);
        $telegramProperty = $reflection->getProperty('telegram');
        $telegramProperty->setAccessible(true);
        $telegramProperty->setValue($service, $mockBot);

        $result = $service->processWebhookUpdate($updateData);
        $this->assertTrue($result);

        // Check that user was linked
        $user->refresh();
        $this->assertEquals('987654321', $user->telegram_chat_id);
    }

    /** @test */
    public function it_handles_invalid_phone_number()
    {
        $updateData = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 123456,
                    'is_bot' => false,
                    'first_name' => 'Test'
                ],
                'chat' => [
                    'id' => 987654321,
                    'type' => 'private'
                ],
                'date' => now()->timestamp,
                'text' => '+213999999999' // Non-existent phone
            ]
        ];

        // Mock the bot API
        $mockBot = Mockery::mock(BotApi::class);
        $mockBot->shouldReceive('sendMessage')
            ->once()
            ->with(
                '987654321',
                Mockery::pattern('/لم يتم العثور على حساب/'),
                'Markdown',
                false,
                null,
                null
            )
            ->andReturn(true);

        // Create service with mocked bot
        $service = new TelegramService();
        $reflection = new \ReflectionClass($service);
        $telegramProperty = $reflection->getProperty('telegram');
        $telegramProperty->setAccessible(true);
        $telegramProperty->setValue($service, $mockBot);

        $result = $service->processWebhookUpdate($updateData);
        $this->assertTrue($result);
    }

    /** @test */
    public function it_normalizes_phone_numbers_correctly()
    {
        $service = new TelegramService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('normalizePhoneNumber');
        $method->setAccessible(true);

        // Test various phone number formats
        $this->assertEquals('+213555123456', $method->invoke($service, '+213555123456'));
        $this->assertEquals('+213555123456', $method->invoke($service, '213555123456'));
        $this->assertEquals('+213555123456', $method->invoke($service, '0555123456'));
        $this->assertEquals('+213555123456', $method->invoke($service, '555123456'));
    }

    /** @test */
    public function it_validates_webhook_data()
    {
        $service = new TelegramService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('validateWebhookData');
        $method->setAccessible(true);

        // Valid data should pass
        $validData = ['update_id' => 123456789, 'message' => []];
        $this->assertTrue($method->invoke($service, $validData));

        // Invalid data should fail
        $invalidData = ['invalid' => 'data'];
        $this->assertFalse($method->invoke($service, $invalidData));
    }

    /** @test */
    public function it_can_send_notifications_with_retry()
    {
        $service = new TelegramService();
        
        // Mock successful send
        $mockBot = Mockery::mock(BotApi::class);
        $mockBot->shouldReceive('sendMessage')
            ->once()
            ->with('123456789', 'Test message', 'Markdown', false, null, null)
            ->andReturn(true);

        $reflection = new \ReflectionClass($service);
        $telegramProperty = $reflection->getProperty('telegram');
        $telegramProperty->setAccessible(true);
        $telegramProperty->setValue($service, $mockBot);

        $result = $service->sendNotificationWithRetry('123456789', 'Test message');
        $this->assertTrue($result);
    }

    /** @test */
    public function it_retries_failed_notifications()
    {
        $service = new TelegramService();
        
        // Mock failed sends that succeed on retry
        $mockBot = Mockery::mock(BotApi::class);
        $mockBot->shouldReceive('sendMessage')
            ->twice()
            ->with('123456789', 'Test message', 'Markdown', false, null, null)
            ->andThrow(new Exception('API Error'))
            ->once()
            ->andReturn(true);

        $reflection = new \ReflectionClass($service);
        $telegramProperty = $reflection->getProperty('telegram');
        $telegramProperty->setAccessible(true);
        $telegramProperty->setValue($service, $mockBot);

        $result = $service->sendNotificationWithRetry('123456789', 'Test message');
        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_file_size_validation()
    {
        Storage::fake('local');
        
        // Create a large file content (simulate large file)
        $largeContent = str_repeat('x', 51 * 1024 * 1024); // 51MB
        Storage::put($this->rachma->file_path, $largeContent);

        $service = new TelegramService();
        
        // Mock notification for file too large
        $mockBot = Mockery::mock(BotApi::class);
        $mockBot->shouldReceive('sendMessage')
            ->once()
            ->with(
                $this->client->telegram_chat_id,
                Mockery::pattern('/الملف كبير جداً/'),
                'Markdown',
                false,
                null,
                null
            )
            ->andReturn(true);

        $reflection = new \ReflectionClass($service);
        $telegramProperty = $reflection->getProperty('telegram');
        $telegramProperty->setAccessible(true);
        $telegramProperty->setValue($service, $mockBot);

        $result = $service->sendRachmaFileWithRetry($this->order);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_verify_bot_connection()
    {
        $service = new TelegramService();
        
        // Mock successful getMe call
        $mockBot = Mockery::mock(BotApi::class);
        $mockUser = new \stdClass();
        $mockUser->username = 'rachma_test_bot';
        
        $mockBot->shouldReceive('getMe')
            ->once()
            ->andReturn((object)['getUsername' => function() { return 'rachma_test_bot'; }]);

        $reflection = new \ReflectionClass($service);
        $telegramProperty = $reflection->getProperty('telegram');
        $telegramProperty->setAccessible(true);
        $telegramProperty->setValue($service, $mockBot);

        $result = $service->verifyConnection();
        $this->assertTrue($result);
    }

    /** @test */
    public function it_prepares_file_message_correctly()
    {
        $service = new TelegramService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('prepareFileMessage');
        $method->setAccessible(true);

        $message = $method->invoke($service, $this->order);

        $this->assertStringContainsString('تم تأكيد طلبك', $message);
        $this->assertStringContainsString($this->order->id, $message);
        $this->assertStringContainsString($this->rachma->title, $message);
        $this->assertStringContainsString($this->designer->store_name, $message);
        $this->assertStringContainsString($this->rachma->size, $message);
        $this->assertStringContainsString($this->rachma->gharazat, $message);
        $this->assertStringContainsString($this->order->amount, $message);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 