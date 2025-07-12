<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\User;
use App\Models\Order;
use App\Models\Rachma;
use App\Models\Designer;
use App\Models\Part;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;

class TestNewTelegramFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-new-format {--chat-id=1635323740}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the new detailed Arabic Telegram message format';

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
        $chatId = $this->option('chat-id');
        
        $this->info('🚀 Testing New Telegram Message Format');
        $this->info('=====================================');
        $this->info("Chat ID: {$chatId}");
        $this->line('');

        // Test 1: Basic connection test
        if ($this->confirm('Test 1: Send basic connection test message?', true)) {
            $this->testBasicConnection($chatId);
        }

        // Test 2: Single rachma order format
        if ($this->confirm('Test 2: Test single rachma order format?', true)) {
            $this->testSingleRachmaFormat($chatId);
        }

        // Test 3: Multi-item order format
        if ($this->confirm('Test 3: Test multi-item order format?', true)) {
            $this->testMultiItemFormat($chatId);
        }

        // Test 4: Order rejection format
        if ($this->confirm('Test 4: Test order rejection format?', true)) {
            $this->testRejectionFormat($chatId);
        }

        $this->info('✨ Testing completed!');
        return 0;
    }

    private function testBasicConnection(string $chatId): void
    {
        $this->info('📤 Sending basic connection test...');
        
        $message = "🔧 *اختبار الاتصال*\n\n";
        $message .= "مرحباً! هذا اختبار للتأكد من عمل البوت\n";
        $message .= "Hello! This is a connection test\n\n";
        $message .= "🕐 " . now()->format('Y-m-d H:i:s');

        $sent = $this->telegramService->sendNotificationWithRetry($chatId, $message);
        
        if ($sent) {
            $this->info('✅ Basic connection test sent successfully!');
        } else {
            $this->error('❌ Failed to send basic connection test');
        }
        $this->line('');
    }

    private function testSingleRachmaFormat(string $chatId): void
    {
        $this->info('📤 Testing single rachma order format...');

        try {
            // Create a manual message in the new format for testing
            $message = $this->createSingleRachmaMessage();

            $sent = $this->telegramService->sendNotificationWithRetry($chatId, $message);

            if ($sent) {
                $this->info('✅ Single rachma format test sent successfully!');
            } else {
                $this->error('❌ Failed to send single rachma format test');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error testing single rachma format: ' . $e->getMessage());
        }
        $this->line('');
    }

    private function testMultiItemFormat(string $chatId): void
    {
        $this->info('📤 Testing multi-item order format...');

        try {
            // Create a manual message in the new format for testing
            $message = $this->createMultiItemMessage();

            $sent = $this->telegramService->sendNotificationWithRetry($chatId, $message);

            if ($sent) {
                $this->info('✅ Multi-item format test sent successfully!');
            } else {
                $this->error('❌ Failed to send multi-item format test');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error testing multi-item format: ' . $e->getMessage());
        }
        $this->line('');
    }

    private function testRejectionFormat(string $chatId): void
    {
        $this->info('📤 Testing order rejection format...');

        try {
            // Create a manual rejection message in the new format for testing
            $message = $this->createRejectionMessage();

            $sent = $this->telegramService->sendNotificationWithRetry($chatId, $message);

            if ($sent) {
                $this->info('✅ Order rejection format test sent successfully!');
            } else {
                $this->error('❌ Failed to send order rejection format test');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error testing rejection format: ' . $e->getMessage());
        }
        $this->line('');
    }

    private function createSingleRachmaMessage(): string
    {
        $message = "🎉 *تم تأكيد طلبك*\n\n";
        $message .= "• *رقم الطلب:* `12345`\n\n";

        $message .= "🎨 *الرشمة 1*\n";
        $message .= "• رشمة الورود الجميلة - متجر فاطمة للتطريز\n\n";

        $message .= "📐 *الجزء 1*\n";
        $message .= "• *تفاصيل الجزء:* الوردة الكبيرة\n";
        $message .= "• الطول: 12.5 سم | العرض: 15.0 سم | عدد الغرز: 2,500\n\n";

        $message .= "📐 *الجزء 2*\n";
        $message .= "• *تفاصيل الجزء:* الأوراق الجانبية\n";
        $message .= "• الطول: 8.0 سم | العرض: 10.0 سم | عدد الغرز: 1,200\n\n";

        $message .= "📐 *الجزء 3*\n";
        $message .= "• *تفاصيل الجزء:* الإطار الخارجي\n";
        $message .= "• الطول: 25.0 سم | العرض: 30.0 سم | عدد الغرز: 3,500\n\n";

        $message .= "📎 *الملف المرفق*\n";
        $message .= "شكراً لاختيارك منصة رشماتي! 🌟";

        return $message;
    }

    private function createMultiItemMessage(): string
    {
        $message = "🎉 *تم تأكيد طلبك*\n\n";
        $message .= "• *رقم الطلب:* `12346`\n\n";
        $message .= "• *الملف:* 2/5\n\n";

        $message .= "🎨 *الرشمة 1*\n";
        $message .= "• رشمة الطيور - متجر أحمد للفنون\n\n";

        $message .= "📐 *الجزء 1*\n";
        $message .= "• *تفاصيل الجزء:* الطائر الأساسي\n";
        $message .= "• الطول: 15.0 سم | العرض: 12.0 سم | عدد الغرز: 1,800\n\n";

        $message .= "📐 *الجزء 2*\n";
        $message .= "• *تفاصيل الجزء:* الأجنحة\n";
        $message .= "• الطول: 10.0 سم | العرض: 8.0 سم | عدد الغرز: 900\n\n";

        $message .= "🎨 *الرشمة 2*\n";
        $message .= "• رشمة الهندسية - متجر فاطمة للتطريز\n\n";

        $message .= "📐 *الجزء 1*\n";
        $message .= "• *تفاصيل الجزء:* المربع المركزي\n";
        $message .= "• الطول: 20.0 سم | العرض: 20.0 سم | عدد الغرز: 2,200\n\n";

        $message .= "📎 *الملف المرفق*\n";
        $message .= "شكراً لاختيارك منصة رشماتي! 🌟";

        return $message;
    }

    private function createRejectionMessage(): string
    {
        $message = "❌ *تم رفض طلبك*\n\n";
        $message .= "• *رقم الطلب:* `12347`\n\n";

        $message .= "🎨 *الرشمة 1*\n";
        $message .= "• رشمة الورود الجميلة - متجر فاطمة للتطريز\n\n";

        $message .= "📐 *الجزء 1*\n";
        $message .= "• *تفاصيل الجزء:* الوردة الكبيرة\n";
        $message .= "• الطول: 12.5 سم | العرض: 15.0 سم | عدد الغرز: 2,500\n\n";

        $message .= "❌ *سبب الرفض:* صورة إثبات الدفع غير واضحة\n\n";

        $message .= "📞 *يرجى التواصل مع الإدارة*\n";
        $message .= "نعتذر عن الإزعاج 🙏";

        return $message;
    }



}
