<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Designer;
use App\Models\Rachma;
use App\Models\Order;
use App\Models\Category;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Mockery;
use ZipArchive;

class RachmatControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $clientUser;
    protected $designer;
    protected $rachma;
    protected $order;
    protected $telegramService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test client
        $this->clientUser = User::factory()->create([
            'user_type' => 'client',
            'telegram_chat_id' => '123456789'
        ]);

        // Create test designer
        $designerUser = User::factory()->create();
        $this->designer = Designer::factory()->create([
            'user_id' => $designerUser->id,
            'subscription_status' => 'active'
        ]);

        // Create test category
        $category = Category::factory()->create();

        // Create test rachma with files
        $this->rachma = Rachma::factory()->create([
            'designer_id' => $this->designer->id,
            'title_ar' => 'رشمة تجريبية',
            'title_fr' => 'Test Rachma',
            'price' => 100
        ]);

        // Attach category to rachma
        $this->rachma->categories()->attach($category->id);

        // Create test files for rachma
        Storage::fake('private');
        $this->createTestFiles();

        // Create completed order
        $this->order = Order::factory()->create([
            'client_id' => $this->clientUser->id,
            'rachma_id' => $this->rachma->id,
            'status' => 'completed',
            'amount' => 100
        ]);

        // Mock TelegramService
        $this->telegramService = Mockery::mock(TelegramService::class);
        $this->app->instance(TelegramService::class, $this->telegramService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createTestFiles(): void
    {
        // Create test files
        $files = [
            [
                'id' => 1,
                'path' => 'private/rachmat/files/1/test.dst',
                'original_name' => 'test.dst',
                'format' => 'DST',
                'size' => 1024,
                'is_primary' => true,
                'uploaded_at' => now(),
                'description' => 'Primary DST file'
            ],
            [
                'id' => 2,
                'path' => 'private/rachmat/files/1/test.pes',
                'original_name' => 'test.pes',
                'format' => 'PES',
                'size' => 2048,
                'is_primary' => false,
                'uploaded_at' => now(),
                'description' => 'PES file'
            ]
        ];

        // Create actual files
        foreach ($files as $file) {
            Storage::disk('private')->put($file['path'], 'Test file content');
        }

        // Update rachma with files
        $this->rachma->update(['files' => $files]);
    }

    private function getAuthHeaders(): array
    {
        $token = auth('api')->login($this->clientUser);
        return ['Authorization' => 'Bearer ' . $token];
    }

    /** @test */
    public function client_can_download_rachma_files_after_purchase()
    {
        $response = $this->postJson("/api/rachmat/{$this->rachma->id}/download-files", [], $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم إنشاء ملف التحميل بنجاح'
            ])
            ->assertJsonStructure([
                'data' => [
                    'download_url',
                    'expires_in',
                    'file_count',
                    'rachma_title'
                ]
            ]);

        $this->assertEquals(2, $response->json('data.file_count'));
        $this->assertStringContainsString('api/download-temp/', $response->json('data.download_url'));
    }

    /** @test */
    public function client_cannot_download_files_without_purchase()
    {
        // Delete the order to simulate no purchase
        $this->order->delete();

        $response = $this->postJson("/api/rachmat/{$this->rachma->id}/download-files", [], $this->getAuthHeaders());

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'يجب شراء الرشمة أولاً للتمكن من تحميل الملفات'
            ]);
    }

    /** @test */
    public function client_cannot_download_files_if_no_files_exist()
    {
        // Update rachma to have no files
        $this->rachma->update(['files' => []]);

        $response = $this->postJson("/api/rachmat/{$this->rachma->id}/download-files", [], $this->getAuthHeaders());

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'لا توجد ملفات متاحة للتحميل'
            ]);
    }

    /** @test */
    public function client_can_resend_files_via_telegram()
    {
        $this->telegramService->shouldReceive('sendMessage')
            ->once()
            ->with($this->client->telegram_chat_id, Mockery::type('string'))
            ->andReturn(true);

        $this->telegramService->shouldReceive('sendDocument')
            ->twice()
            ->with(
                $this->client->telegram_chat_id,
                Mockery::type('string'),
                Mockery::type('string')
            )
            ->andReturn(true);

        $this->telegramService->shouldReceive('sendMessage')
            ->once()
            ->with($this->client->telegram_chat_id, '✅ تم إرسال 2 ملف بنجاح')
            ->andReturn(true);

        $response = $this->postJson("/api/rachmat/{$this->rachma->id}/resend-telegram-files", [
            'client_id' => $this->client->id
        ], $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم إرسال 2 ملف عبر التليجرام بنجاح'
            ])
            ->assertJsonStructure([
                'data' => [
                    'files_sent',
                    'total_files',
                    'rachma_title'
                ]
            ]);

        $this->assertEquals(2, $response->json('data.files_sent'));
        $this->assertEquals(2, $response->json('data.total_files'));
    }

    /** @test */
    public function client_cannot_resend_files_without_telegram_link()
    {
        // Remove telegram chat ID
        $this->client->update(['telegram_chat_id' => null]);

        $response = $this->postJson("/api/rachmat/{$this->rachma->id}/resend-telegram-files", [
            'client_id' => $this->client->id
        ], $this->getAuthHeaders());

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'لم يتم ربط حساب التليجرام بعد'
            ]);
    }

    /** @test */
    public function client_cannot_resend_files_without_purchase()
    {
        // Delete the order
        $this->order->delete();

        $response = $this->postJson("/api/rachmat/{$this->rachma->id}/resend-telegram-files", [
            'client_id' => $this->client->id
        ], $this->getAuthHeaders());

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'يجب شراء الرشمة أولاً للتمكن من إرسال الملفات'
            ]);
    }

    /** @test */
    public function client_can_unlink_telegram_account()
    {
        $this->telegramService->shouldReceive('sendMessage')
            ->once()
            ->with($this->client->telegram_chat_id, Mockery::pattern('/تم إلغاء ربط حسابك/'))
            ->andReturn(true);

        $response = $this->postJson('/api/unlink-telegram', [
            'client_id' => $this->client->id
        ], $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم إلغاء ربط حسابك بالتليجرام بنجاح'
            ])
            ->assertJsonStructure([
                'data' => [
                    'client_id',
                    'unlinked_at'
                ]
            ]);

        // Verify that telegram_chat_id is null
        $this->client->refresh();
        $this->assertNull($this->client->telegram_chat_id);
    }

    /** @test */
    public function client_cannot_unlink_if_not_linked_to_telegram()
    {
        // Remove telegram chat ID
        $this->client->update(['telegram_chat_id' => null]);

        $response = $this->postJson('/api/unlink-telegram', [
            'client_id' => $this->client->id
        ], $this->getAuthHeaders());

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'الحساب غير مرتبط بالتليجرام'
            ]);
    }

    /** @test */
    public function unlink_continues_even_if_telegram_message_fails()
    {
        $this->telegramService->shouldReceive('sendMessage')
            ->once()
            ->with($this->client->telegram_chat_id, Mockery::pattern('/تم إلغاء ربط حسابك/'))
            ->andThrow(new \Exception('Telegram API error'));

        $response = $this->postJson('/api/unlink-telegram', [
            'client_id' => $this->client->id
        ], $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم إلغاء ربط حسابك بالتليجرام بنجاح'
            ]);

        // Verify that telegram_chat_id is still null
        $this->client->refresh();
        $this->assertNull($this->client->telegram_chat_id);
    }

    /** @test */
    public function download_endpoint_requires_valid_client_id()
    {
        $response = $this->postJson("/api/rachmat/{$this->rachma->id}/download-files", [
            'client_id' => 99999 // Non-existent client
        ], $this->getAuthHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    }

    /** @test */
    public function resend_endpoint_requires_valid_client_id()
    {
        $response = $this->postJson("/api/rachmat/{$this->rachma->id}/resend-telegram-files", [
            'client_id' => 99999 // Non-existent client
        ], $this->getAuthHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    }

    /** @test */
    public function unlink_endpoint_requires_valid_client_id()
    {
        $response = $this->postJson('/api/unlink-telegram', [
            'client_id' => 99999 // Non-existent client
        ], $this->getAuthHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    }

    /** @test */
    public function endpoints_require_authentication()
    {
        $endpoints = [
            ['POST', "/api/rachmat/{$this->rachma->id}/download-files"],
            ['POST', "/api/rachmat/{$this->rachma->id}/resend-telegram-files"],
            ['POST', '/api/unlink-telegram']
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url, ['client_id' => $this->client->id]);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function temp_download_route_works_for_valid_files()
    {
        // Create a temporary file
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $filename = 'test_' . time() . '.zip';
        $filePath = $tempDir . '/' . $filename;
        file_put_contents($filePath, 'Test ZIP content');

        $response = $this->get("/api/download-temp/{$filename}");

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=' . $filename);
    }

    /** @test */
    public function temp_download_route_fails_for_expired_files()
    {
        // Create an old temporary file
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $filename = 'old_test.zip';
        $filePath = $tempDir . '/' . $filename;
        file_put_contents($filePath, 'Test ZIP content');

        // Set the file modification time to 2 hours ago
        touch($filePath, time() - 7200);

        $response = $this->get("/api/download-temp/{$filename}");

        $response->assertStatus(404);
        $this->assertStringContainsString('انتهت صلاحية رابط التحميل', $response->getContent());
    }

    /** @test */
    public function temp_download_route_fails_for_non_existent_files()
    {
        $response = $this->get('/api/download-temp/non_existent_file.zip');

        $response->assertStatus(404);
        $this->assertStringContainsString('ملف التحميل غير موجود', $response->getContent());
    }
} 