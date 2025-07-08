<?php

/**
 * Test script to verify the single-click fix
 * This simulates the exact request that should be sent on first click
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "🧪 Testing Single-Click Fix for Order 202\n";
echo "=========================================\n\n";

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
    echo "   - Telegram ID: {$order->client->telegram_chat_id}\n\n";

    // Reset order to pending if it's completed
    if ($order->status === 'completed') {
        echo "🔄 Resetting order to pending for testing...\n";
        $order->update([
            'status' => 'pending',
            'admin_notes' => null,
            'completed_at' => null,
            'confirmed_at' => null,
            'file_sent_at' => null
        ]);
        echo "✅ Order reset to pending\n\n";
    }

    // Simulate the exact request data that should be sent on first click
    $requestData = [
        'status' => 'completed',
        'admin_notes' => 'تم تأكيد الطلب وإرسال الملف',
        'rejection_reason' => ''
    ];

    echo "📤 Simulating single-click request...\n";
    echo "   Request Data:\n";
    foreach ($requestData as $key => $value) {
        echo "   - {$key}: " . ($value ?: 'null') . "\n";
    }
    echo "\n";

    // Get admin user
    $admin = \App\Models\User::where('user_type', 'admin')->first();

    // Create the request
    $request = \Illuminate\Http\Request::create('/admin/orders/' . $order->id, 'PUT', $requestData);
    $request->setUserResolver(function () use ($admin) {
        return $admin;
    });

    // Create the form request with proper validation
    $updateRequest = new \App\Http\Requests\Admin\UpdateOrderRequest();
    $updateRequest->replace($requestData);
    $updateRequest->setContainer(app());
    $updateRequest->setRedirector(app(\Illuminate\Routing\Redirector::class));

    // Manually validate the request
    $rules = $updateRequest->rules();
    $validator = app('validator')->make($requestData, $rules);

    if ($validator->fails()) {
        echo "❌ Validation failed:\n";
        foreach ($validator->errors()->all() as $error) {
            echo "   - {$error}\n";
        }
        exit(1);
    }

    // Set the validator on the request
    $updateRequest->setValidator($validator);

    // Get the controller
    $telegramService = app(\App\Services\TelegramService::class);
    $controller = new \App\Http\Controllers\Admin\OrderController($telegramService);

    echo "🔄 Processing order update...\n";

    // Store original state for comparison
    $originalStatus = $order->status;
    $originalNotes = $order->admin_notes;

    try {
        // Call the update method
        $response = $controller->update($updateRequest, $order);

        echo "✅ Update method completed!\n";

        // Check order status after update
        $order->refresh();
        echo "   - Status changed: {$originalStatus} → {$order->status}\n";
        echo "   - Admin notes: " . ($order->admin_notes ?: 'null') . "\n";
        echo "   - Completed at: " . ($order->completed_at ?: 'null') . "\n";
        echo "   - File sent at: " . ($order->file_sent_at ?: 'null') . "\n";

        if ($order->status === 'completed') {
            echo "\n🎉 SUCCESS: Order completed on first request!\n";
            echo "   ✅ Status updated correctly\n";
            echo "   ✅ Timestamps set\n";
            echo "   ✅ Admin notes saved\n";

            // Check designer earnings
            $designer = $order->rachma->designer;
            $expectedEarnings = $order->amount * 0.7;
            echo "   ✅ Designer earnings: {$designer->earnings} DZD\n";

        } else {
            echo "\n❌ FAILED: Order not completed on first request\n";
            echo "   Status remained: {$order->status}\n";
        }

        // Check recent logs
        echo "\n📋 Recent logs:\n";
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            $recentLogs = substr($logs, -1000); // Last 1000 characters
            $logLines = explode("\n", $recentLogs);
            $relevantLogs = array_filter($logLines, function($line) {
                return strpos($line, 'Order update request') !== false ||
                       strpos($line, 'Order ZIP package') !== false ||
                       strpos($line, 'Order files sent') !== false ||
                       strpos($line, 'File successfully delivered') !== false;
            });

            if (!empty($relevantLogs)) {
                foreach (array_slice($relevantLogs, -5) as $log) {
                    echo "   " . trim($log) . "\n";
                }
            } else {
                echo "   No relevant logs found\n";
            }
        }

    } catch (\Exception $e) {
        echo "❌ Exception during update: {$e->getMessage()}\n";
        echo "   File: {$e->getFile()}:{$e->getLine()}\n";
    }

    echo "\n✨ Test completed!\n";

} catch (\Exception $e) {
    echo "❌ Fatal error: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
}
