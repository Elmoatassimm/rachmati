<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;

class TelegramManageUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:users 
                            {action : Action to perform (list|link|unlink|test|send)}
                            {--user= : User ID or email}
                            {--chat-id= : Telegram chat ID}
                            {--phone= : Phone number}
                            {--message= : Message to send}
                            {--order= : Order ID for testing file delivery}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage user Telegram connections and send test notifications';

    private TelegramService $telegramService;

    /**
     * Create a new command instance.
     */
    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listUsers(),
            'link' => $this->linkUser(),
            'unlink' => $this->unlinkUser(),
            'test' => $this->testNotification(),
            'send' => $this->sendMessage(),
            default => $this->showHelp(),
        };
    }

    /**
     * List users with Telegram connections
     */
    private function listUsers(): int
    {
        $this->info('📋 Users with Telegram Connections');
        $this->line('');

        $users = User::whereNotNull('telegram_chat_id')
            ->select('id', 'name', 'email', 'phone', 'user_type', 'telegram_chat_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No users with Telegram connections found.');
            return self::SUCCESS;
        }

        $tableData = $users->map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->email,
                $user->phone ?: 'N/A',
                $user->user_type,
                $user->telegram_chat_id,
                $user->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Email', 'Phone', 'Type', 'Chat ID', 'Created'],
            $tableData
        );

        $this->info('Total: ' . $users->count() . ' users');
        return self::SUCCESS;
    }

    /**
     * Link user to Telegram chat
     */
    private function linkUser(): int
    {
        $userIdentifier = $this->option('user');
        $chatId = $this->option('chat-id');

        if (!$userIdentifier) {
            $userIdentifier = $this->ask('Enter user ID or email');
        }

        if (!$chatId) {
            $chatId = $this->ask('Enter Telegram chat ID');
        }

        // Find user
        $user = $this->findUser($userIdentifier);
        if (!$user) {
            $this->error('❌ User not found!');
            return self::FAILURE;
        }

        // Validate chat ID
        if (!is_numeric($chatId)) {
            $this->error('❌ Invalid chat ID format!');
            return self::FAILURE;
        }

        // Check if chat ID is already linked to another user
        $existingUser = User::where('telegram_chat_id', $chatId)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            $this->warn("⚠️  Chat ID is already linked to user: {$existingUser->name} ({$existingUser->email})");
            if (!$this->confirm('Do you want to unlink it from the existing user and link to the new user?')) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
            $existingUser->update(['telegram_chat_id' => null]);
            $this->info("Unlinked from {$existingUser->name}");
        }

        // Link user
        $user->update(['telegram_chat_id' => $chatId]);
        
        $this->info("✅ Successfully linked {$user->name} ({$user->email}) to chat ID: {$chatId}");

        // Send welcome message
        if ($this->confirm('Send welcome message to user?')) {
            $message = "🌟 *مرحباً {$user->name}*\n\n";
            $message .= "تم ربط حسابك بنجاح مع بوت رشمات\n";
            $message .= "Your account has been successfully linked to Rachmat bot\n\n";
            $message .= "ستتلقى إشعارات الطلبات هنا\nYou will receive order notifications here";

            $sent = $this->telegramService->sendNotificationWithRetry($chatId, $message);
            if ($sent) {
                $this->info('✅ Welcome message sent successfully!');
            } else {
                $this->warn('⚠️  Failed to send welcome message');
            }
        }

        return self::SUCCESS;
    }

    /**
     * Unlink user from Telegram
     */
    private function unlinkUser(): int
    {
        $userIdentifier = $this->option('user');

        if (!$userIdentifier) {
            $userIdentifier = $this->ask('Enter user ID or email');
        }

        // Find user
        $user = $this->findUser($userIdentifier);
        if (!$user) {
            $this->error('❌ User not found!');
            return self::FAILURE;
        }

        if (!$user->telegram_chat_id) {
            $this->warn('⚠️  User is not linked to any Telegram chat.');
            return self::SUCCESS;
        }

        $chatId = $user->telegram_chat_id;

        if (!$this->confirm("Unlink {$user->name} from Telegram chat {$chatId}?")) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }

        $user->update(['telegram_chat_id' => null]);
        
        $this->info("✅ Successfully unlinked {$user->name} from Telegram");
        return self::SUCCESS;
    }

    /**
     * Test notification sending
     */
    private function testNotification(): int
    {
        $userIdentifier = $this->option('user');
        $orderId = $this->option('order');

        if (!$userIdentifier) {
            $userIdentifier = $this->ask('Enter user ID or email');
        }

        // Find user
        $user = $this->findUser($userIdentifier);
        if (!$user) {
            $this->error('❌ User not found!');
            return self::FAILURE;
        }

        if (!$user->telegram_chat_id) {
            $this->error('❌ User is not linked to Telegram!');
            return self::FAILURE;
        }

        // Test different types of notifications
        $this->info("Testing notifications for {$user->name}...");

        // Test 1: Simple notification
        $this->line('1. Testing simple notification...');
        $message = "🧪 *اختبار الإشعارات / Notification Test*\n\n";
        $message .= "هذا اختبار للتأكد من عمل الإشعارات\n";
        $message .= "This is a test to verify notifications are working\n\n";
        $message .= "التوقيت / Time: " . now()->format('Y-m-d H:i:s');

        $sent = $this->telegramService->sendNotificationWithRetry($user->telegram_chat_id, $message);
        if ($sent) {
            $this->info('  ✅ Simple notification sent successfully');
        } else {
            $this->error('  ❌ Failed to send simple notification');
        }

        // Test 2: Order-related notification (if order ID provided)
        if ($orderId) {
            $this->line('2. Testing order notification...');
            $order = Order::find($orderId);
            
            if ($order && $order->client_id === $user->id) {
                $orderMessage = "📦 *تحديث الطلب / Order Update*\n\n";
                $orderMessage .= "رقم الطلب / Order ID: `{$order->id}`\n";
                $orderMessage .= "الحالة / Status: {$order->status}\n";
                $orderMessage .= "المبلغ / Amount: {$order->amount} DZD\n\n";
                $orderMessage .= "هذا إشعار تجريبي / This is a test notification";

                $sent = $this->telegramService->sendNotificationWithRetry($user->telegram_chat_id, $orderMessage);
                if ($sent) {
                    $this->info('  ✅ Order notification sent successfully');
                } else {
                    $this->error('  ❌ Failed to send order notification');
                }
            } else {
                $this->warn('  ⚠️  Order not found or not owned by user');
            }
        }

        return self::SUCCESS;
    }

    /**
     * Send custom message
     */
    private function sendMessage(): int
    {
        $userIdentifier = $this->option('user');
        $message = $this->option('message');

        if (!$userIdentifier) {
            $userIdentifier = $this->ask('Enter user ID or email');
        }

        if (!$message) {
            $message = $this->ask('Enter message to send');
        }

        // Find user
        $user = $this->findUser($userIdentifier);
        if (!$user) {
            $this->error('❌ User not found!');
            return self::FAILURE;
        }

        if (!$user->telegram_chat_id) {
            $this->error('❌ User is not linked to Telegram!');
            return self::FAILURE;
        }

        $this->info("Sending message to {$user->name}...");
        
        $sent = $this->telegramService->sendNotificationWithRetry($user->telegram_chat_id, $message);
        
        if ($sent) {
            $this->info('✅ Message sent successfully!');
            return self::SUCCESS;
        } else {
            $this->error('❌ Failed to send message!');
            return self::FAILURE;
        }
    }

    /**
     * Find user by ID or email
     */
    private function findUser(string $identifier): ?User
    {
        if (is_numeric($identifier)) {
            return User::find($identifier);
        }
        
        return User::where('email', $identifier)->first();
    }

    /**
     * Show help information
     */
    private function showHelp(): int
    {
        $this->error('❌ Invalid action!');
        $this->line('');
        $this->info('Available actions:');
        $this->table(
            ['Action', 'Description', 'Options'],
            [
                ['list', 'List all users with Telegram connections', ''],
                ['link', 'Link user to Telegram chat', '--user, --chat-id'],
                ['unlink', 'Unlink user from Telegram', '--user'],
                ['test', 'Send test notifications', '--user, --order'],
                ['send', 'Send custom message', '--user, --message'],
            ]
        );
        
        $this->line('');
        $this->info('Examples:');
        $this->line('  php artisan telegram:users list');
        $this->line('  php artisan telegram:users link --user=1 --chat-id=123456789');
        $this->line('  php artisan telegram:users test --user=user@example.com');
        $this->line('  php artisan telegram:users send --user=1 --message="Hello from admin!"');
        
        return self::FAILURE;
    }
} 