<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Models\Rachma;
use App\Models\Designer;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class TestTelegramFileDelivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-file-delivery {--user-id=} {--order-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram file delivery functionality with specific user and order';

    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing Telegram File Delivery Functionality');
        $this->info('================================================');

        // Get test user with Telegram ID 6494748643
        $userId = $this->option('user-id');
        $orderId = $this->option('order-id');

        if ($userId) {
            $testUser = User::find($userId);
        } else {
            $testUser = User::where('telegram_chat_id', '6494748643')->first();
        }

        if (!$testUser) {
            $this->error('❌ Test user with Telegram ID 6494748643 not found!');
            $this->info('💡 Run: php artisan db:seed --class=UserSeeder to create test users');
            return 1;
        }

        $this->info("✅ Found test user: {$testUser->name} ({$testUser->email})");
        $this->info("📱 Telegram Chat ID: {$testUser->telegram_chat_id}");

        // Find or create a test order
        if ($orderId) {
            $testOrder = Order::find($orderId);
            if (!$testOrder) {
                $this->error("❌ Order with ID {$orderId} not found!");
                return 1;
            }
        } else {
            $testOrder = Order::where('client_id', $testUser->id)
                ->where('status', 'pending')
                ->first();
        }

        if (!$testOrder) {
            $this->info('📦 No pending orders found for test user. Creating a test order...');
            $testOrder = $this->createTestOrder($testUser);
        }

        if (!$testOrder) {
            $this->error('❌ Failed to create test order!');
            return 1;
        }

        $this->info("✅ Using order: #{$testOrder->id} (Status: {$testOrder->status})");

        // Load order relationships
        $testOrder->load(['client', 'rachma', 'orderItems.rachma']);

        // Test file delivery validation
        $this->info("\n🔍 Testing file delivery validation...");
        $validation = $this->validateOrderFiles($testOrder);
        
        if (!$validation['canComplete']) {
            $this->error("❌ Order validation failed: {$validation['message']}");
            $this->info("Issues found:");
            foreach ($validation['issues'] as $issue) {
                $this->warn("  - {$issue}");
            }
            return 1;
        }

        $this->info("✅ Order validation passed!");
        $this->info("📁 Files count: {$validation['filesCount']}");
        $this->info("📏 Total size: " . $this->formatFileSize($validation['totalSize'] ?? 0));

        // Test actual file delivery
        if ($this->confirm('🚀 Proceed with actual file delivery test?')) {
            $this->info("\n📤 Testing file delivery...");
            
            try {
                $delivered = $this->telegramService->sendRachmaFileWithRetry($testOrder);
                
                if ($delivered) {
                    $this->info("✅ File delivery successful!");
                    $this->info("📱 Files sent to Telegram chat: {$testUser->telegram_chat_id}");
                } else {
                    $this->error("❌ File delivery failed!");
                }
            } catch (\Exception $e) {
                $this->error("❌ Exception during file delivery: {$e->getMessage()}");
                Log::error("Test file delivery exception", [
                    'order_id' => $testOrder->id,
                    'user_id' => $testUser->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("\n✨ Test completed!");
        return 0;
    }

    private function createTestOrder(User $testUser): ?Order
    {
        // Find an active designer with rachma
        $designer = Designer::whereHas('rachmat', function($query) {
            $query->where('is_active', 1);
        })->first();

        if (!$designer) {
            $this->error('❌ No active designers with rachmat found!');
            return null;
        }

        $rachma = $designer->rachmat()->where('is_active', 1)->first();
        if (!$rachma) {
            $this->error('❌ No active rachmat found for designer!');
            return null;
        }

        // Create order using new items-only approach
        $order = Order::create([
            'client_id' => $testUser->id,
            'amount' => $rachma->price ?? 1000,
            'payment_method' => 'ccp',
            'payment_proof_path' => 'test/payment_proof.jpg',
            'status' => 'pending',
        ]);

        // Create order item
        $order->orderItems()->create([
            'rachma_id' => $rachma->id,
            'price' => $rachma->price ?? 1000,
        ]);

        return $order;
    }

    private function validateOrderFiles(Order $order): array
    {
        $issues = [];
        $totalSize = 0;
        $filesCount = 0;
        $rachmatCount = 0;

        // Check if order has rachma files
        if ($order->rachma_id && $order->rachma) {
            $rachmatCount = 1;
            $rachma = $order->rachma;
            
            if (!$rachma->hasFiles()) {
                $issues[] = "الرشمة الرئيسية #{$rachma->id} لا تحتوي على ملفات";
            } else {
                $files = $rachma->files;
                $filesCount += count($files);
                foreach ($files as $file) {
                    $totalSize += $file->size ?? 0;
                }
            }
        }

        // Check order items
        if ($order->orderItems && $order->orderItems->count() > 0) {
            $rachmatCount = $order->orderItems->count();
            foreach ($order->orderItems as $item) {
                if (!$item->rachma) {
                    $issues[] = "عنصر الطلب #{$item->id} لا يحتوي على رشمة";
                    continue;
                }

                if (!$item->rachma->hasFiles()) {
                    $issues[] = "الرشمة #{$item->rachma->id} في عنصر الطلب لا تحتوي على ملفات";
                } else {
                    $files = $item->rachma->files;
                    $filesCount += count($files);
                    foreach ($files as $file) {
                        $totalSize += $file->size ?? 0;
                    }
                }
            }
        }

        // Check if client has Telegram
        if (!$order->client->telegram_chat_id) {
            $issues[] = "العميل غير مربوط بتيليجرام";
        }

        $canComplete = empty($issues) && $filesCount > 0;

        return [
            'canComplete' => $canComplete,
            'message' => $canComplete ? 'يمكن إكمال الطلب' : 'لا يمكن إكمال الطلب',
            'issues' => $issues,
            'totalSize' => $totalSize,
            'filesCount' => $filesCount,
            'rachmatCount' => $rachmatCount,
        ];
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
