<?php

/**
 * Debug script to test order update functionality
 * This simulates the exact same process as the web interface
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "🔍 Debug: Order Update File Delivery\n";
echo "====================================\n\n";

try {
    // Get Order 202
    $order = \App\Models\Order::find(202);
    if (!$order) {
        echo "❌ Order 202 not found!\n";
        exit(1);
    }

    echo "✅ Found Order 202\n";
    echo "   - Status: {$order->status}\n";
    echo "   - Client: {$order->client->name}\n";
    echo "   - Telegram ID: {$order->client->telegram_chat_id}\n";
    echo "   - Rachma: {$order->rachma->title}\n\n";

    // Get admin user
    $admin = \App\Models\User::where('user_type', 'admin')->first();
    if (!$admin) {
        echo "❌ No admin user found!\n";
        exit(1);
    }

    echo "✅ Found admin user: {$admin->name}\n\n";

    // Simulate the exact request data from the frontend
    $requestData = [
        'status' => 'completed',
        'admin_notes' => 'تم تأكيد الطلب وإرسال الملف',
        'rejection_reason' => ''
    ];

    echo "📋 Request Data:\n";
    foreach ($requestData as $key => $value) {
        echo "   - {$key}: {$value}\n";
    }
    echo "\n";

    // Create the request
    $request = \Illuminate\Http\Request::create('/admin/orders/' . $order->id, 'PUT', $requestData);
    $request->setUserResolver(function () use ($admin) {
        return $admin;
    });

    // Get the controller
    $telegramService = app(\App\Services\TelegramService::class);
    $controller = new \App\Http\Controllers\Admin\OrderController($telegramService);

    echo "🔄 Testing order update process...\n\n";

    // Store original state
    $originalStatus = $order->status;
    $originalNotes = $order->admin_notes;
    $originalCompletedAt = $order->completed_at;

    // Test the update method directly
    echo "1️⃣ Testing validation...\n";
    
    // Load relationships
    $order->load(['client', 'rachma', 'orderItems.rachma']);
    
    // Check file delivery validation
    $deliveryValidation = $controller->checkFileDelivery($order);
    $validationData = $deliveryValidation->getData(true);
    
    echo "   - Can Complete: " . ($validationData['canComplete'] ? 'Yes' : 'No') . "\n";
    echo "   - Client Has Telegram: " . ($validationData['clientHasTelegram'] ? 'Yes' : 'No') . "\n";
    echo "   - Has Files: " . ($validationData['hasFiles'] ? 'Yes' : 'No') . "\n";
    echo "   - Files Count: {$validationData['filesCount']}\n";
    
    if (!$validationData['canComplete']) {
        echo "❌ Validation failed!\n";
        if (!empty($validationData['issues'])) {
            foreach ($validationData['issues'] as $issue) {
                echo "   - {$issue}\n";
            }
        }
        exit(1);
    }
    
    echo "✅ Validation passed!\n\n";

    echo "2️⃣ Testing file delivery...\n";
    
    // Test file delivery directly
    $delivered = $telegramService->sendRachmaFileWithRetry($order);
    
    if ($delivered) {
        echo "✅ File delivery successful!\n";
    } else {
        echo "❌ File delivery failed!\n";
        echo "   Check logs for more details.\n";
    }
    
    echo "\n3️⃣ Testing manual order update...\n";

    try {
        // Manually simulate the order update process
        $oldStatus = $order->status;
        $newStatus = 'completed';

        echo "   - Old status: {$oldStatus}\n";
        echo "   - New status: {$newStatus}\n";

        // Test file delivery again
        echo "   - Testing file delivery again...\n";
        $fileDelivered = $telegramService->sendRachmaFileWithRetry($order);

        if ($fileDelivered) {
            echo "   ✅ File delivery successful!\n";

            // Update the order manually
            $updateData = [
                'status' => $newStatus,
                'admin_notes' => 'تم تأكيد الطلب وإرسال الملف',
                'completed_at' => now(),
                'confirmed_at' => now(),
                'file_sent_at' => now()
            ];

            $order->update($updateData);

            echo "   ✅ Order updated successfully!\n";
            echo "   - New status: {$order->status}\n";
            echo "   - Completed at: {$order->completed_at}\n";
            echo "   - Admin notes: {$order->admin_notes}\n";

            // Update designer earnings
            $designer = $order->rachma->designer;
            $commission = $order->amount * 0.7;
            $designer->increment('earnings', $commission);

            echo "   ✅ Designer earnings updated by: " . number_format($commission, 2) . " DZD\n";

        } else {
            echo "   ❌ File delivery failed!\n";
        }

    } catch (\Exception $e) {
        echo "❌ Exception during manual update: {$e->getMessage()}\n";
        echo "   File: {$e->getFile()}:{$e->getLine()}\n";
    }

    echo "\n4️⃣ Checking logs...\n";
    
    // Check recent logs
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $recentLogs = substr($logs, -2000); // Last 2000 characters
        if (!empty(trim($recentLogs))) {
            echo "Recent logs:\n";
            echo $recentLogs . "\n";
        } else {
            echo "No recent logs found.\n";
        }
    } else {
        echo "Log file not found.\n";
    }

    // Restore original state if needed
    if ($order->status !== $originalStatus) {
        echo "\n🔄 Restoring original order state...\n";
        $order->update([
            'status' => $originalStatus,
            'admin_notes' => $originalNotes,
            'completed_at' => $originalCompletedAt,
            'confirmed_at' => null,
            'file_sent_at' => null
        ]);
        echo "✅ Order restored to original state\n";
    }

    echo "\n✨ Debug completed!\n";

} catch (\Exception $e) {
    echo "❌ Fatal error: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
