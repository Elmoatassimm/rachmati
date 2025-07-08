<?php

/**
 * Manual script to complete Order 202 and send files
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "🚀 Manually Completing Order 202\n";
echo "=================================\n\n";

try {
    // Get Order 202
    $order = \App\Models\Order::find(202);
    if (!$order) {
        echo "❌ Order 202 not found!\n";
        exit(1);
    }

    echo "✅ Found Order 202\n";
    echo "   - Current Status: {$order->status}\n";
    echo "   - Client: {$order->client->name}\n";
    echo "   - Telegram ID: {$order->client->telegram_chat_id}\n";
    echo "   - Amount: {$order->amount} DZD\n\n";

    if ($order->status === 'completed') {
        echo "⚠️  Order is already completed!\n";
        echo "   - Completed at: {$order->completed_at}\n";
        echo "   - Admin notes: {$order->admin_notes}\n";
        exit(0);
    }

    // Get TelegramService
    $telegramService = app(\App\Services\TelegramService::class);

    echo "📤 Sending files to client...\n";
    $delivered = $telegramService->sendRachmaFileWithRetry($order);

    if ($delivered) {
        echo "✅ Files sent successfully!\n\n";

        echo "📝 Updating order status...\n";
        $order->update([
            'status' => 'completed',
            'admin_notes' => 'تم تأكيد الطلب وإرسال الملف - تم يدوياً',
            'completed_at' => now(),
            'confirmed_at' => now(),
            'file_sent_at' => now()
        ]);

        echo "✅ Order status updated!\n";

        // Update designer earnings
        $designer = $order->rachma->designer;
        $commission = $order->amount * 0.7;
        $designer->increment('earnings', $commission);

        echo "✅ Designer earnings updated by: " . number_format($commission, 2) . " DZD\n";

        echo "\n🎉 Order 202 completed successfully!\n";
        echo "   - Status: {$order->status}\n";
        echo "   - Completed at: {$order->completed_at}\n";
        echo "   - Files sent to Telegram: {$order->client->telegram_chat_id}\n";

    } else {
        echo "❌ File delivery failed!\n";
        echo "   Check logs for more details.\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
}
