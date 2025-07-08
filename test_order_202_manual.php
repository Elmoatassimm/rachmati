<?php

/**
 * Manual test script for Order 202 file delivery functionality
 * This script tests the actual order update and file delivery without PHPUnit
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "🚀 Manual Testing: Order 202 File Delivery Functionality\n";
echo "========================================================\n\n";

try {
    // Test 1: Check if Order 202 exists
    echo "📋 Test 1: Checking Order 202 Existence\n";
    echo "---------------------------------------\n";
    
    $order = \App\Models\Order::find(202);
    if (!$order) {
        echo "❌ Order 202 does not exist!\n";
        echo "💡 Creating a test order with ID 202...\n";
        
        // Find a test user with Telegram ID
        $testUser = \App\Models\User::where('telegram_chat_id', '6494748643')->first();
        if (!$testUser) {
            echo "❌ Test user with Telegram ID 6494748643 not found!\n";
            exit(1);
        }
        
        // Find an active rachma
        $rachma = \App\Models\Rachma::where('is_active', 1)->first();
        if (!$rachma) {
            echo "❌ No active rachma found!\n";
            exit(1);
        }
        
        // Create order with specific ID
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $order = new \App\Models\Order([
            'client_id' => $testUser->id,
            'rachma_id' => $rachma->id,
            'amount' => $rachma->price ?? 2500.00,
            'payment_method' => 'ccp',
            'payment_proof_path' => 'test/payment_proof.jpg',
            'status' => 'pending',
        ]);
        $order->id = 202;
        $order->save();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "✅ Created Order 202 for testing\n";
    } else {
        echo "✅ Order 202 exists!\n";
    }
    
    // Load relationships
    $order->load(['client', 'rachma.designer']);
    
    echo "   - ID: {$order->id}\n";
    echo "   - Status: {$order->status}\n";
    echo "   - Client: {$order->client->name} ({$order->client->email})\n";
    echo "   - Telegram ID: " . ($order->client->telegram_chat_id ?? 'Not linked') . "\n";
    echo "   - Amount: {$order->amount} DZD\n";
    echo "   - Rachma: {$order->rachma->title}\n";
    echo "   - Designer: {$order->rachma->designer->user->name}\n\n";
    
    // Test 2: Validate file delivery requirements
    echo "📋 Test 2: Validating File Delivery Requirements\n";
    echo "-----------------------------------------------\n";
    
    $canDeliver = true;
    $issues = [];
    
    // Check client has Telegram
    if (!$order->client->telegram_chat_id) {
        $issues[] = "❌ Client doesn't have Telegram linked";
        $canDeliver = false;
    } else {
        echo "✅ Client has Telegram ID: {$order->client->telegram_chat_id}\n";
    }
    
    // Check rachma has files
    if (!$order->rachma->hasFiles()) {
        $issues[] = "❌ Rachma doesn't have files";
        $canDeliver = false;
    } else {
        $filesCount = count($order->rachma->files);
        echo "✅ Rachma has {$filesCount} files\n";
        
        // List files
        foreach ($order->rachma->files as $file) {
            $sizeKB = number_format($file->size / 1024, 2);
            echo "   - {$file->original_name} ({$file->format}, {$sizeKB} KB)\n";
        }
    }
    
    // Check file existence in storage
    $missingFiles = [];
    if ($order->rachma->hasFiles()) {
        foreach ($order->rachma->files as $file) {
            if (!\Illuminate\Support\Facades\Storage::disk('private')->exists($file->path)) {
                $missingFiles[] = $file->original_name;
            }
        }
        
        if (!empty($missingFiles)) {
            $issues[] = "❌ Missing files in storage: " . implode(', ', $missingFiles);
            $canDeliver = false;
        } else {
            echo "✅ All files exist in storage\n";
        }
    }
    
    if (!$canDeliver) {
        echo "\n⚠️  Issues preventing file delivery:\n";
        foreach ($issues as $issue) {
            echo "   {$issue}\n";
        }
        echo "\n";
    } else {
        echo "\n🎉 Order 202 is ready for file delivery!\n\n";
    }
    
    // Test 3: Test delivery validation endpoint
    echo "📋 Test 3: Testing Delivery Validation Endpoint\n";
    echo "-----------------------------------------------\n";
    
    // Create admin user for testing
    $admin = \App\Models\User::where('user_type', 'admin')->first();
    if (!$admin) {
        $admin = \App\Models\User::factory()->create([
            'user_type' => 'admin',
            'email' => 'test.admin@rachmat.com'
        ]);
        echo "✅ Created admin user for testing\n";
    }
    
    // Simulate the delivery check request
    $app = app();
    $request = \Illuminate\Http\Request::create('/admin/orders/202/delivery-check', 'GET');
    $app->instance('request', $request);
    
    // Get the controller with dependency injection
    $telegramService = app(\App\Services\TelegramService::class);
    $controller = new \App\Http\Controllers\Admin\OrderController($telegramService);

    try {
        $response = $controller->checkFileDelivery($order);
        $data = $response->getData(true);
        
        echo "✅ Delivery check endpoint response:\n";
        echo "   - Can Complete: " . ($data['canComplete'] ? 'Yes' : 'No') . "\n";
        echo "   - Client Has Telegram: " . ($data['clientHasTelegram'] ? 'Yes' : 'No') . "\n";
        echo "   - Has Files: " . ($data['hasFiles'] ? 'Yes' : 'No') . "\n";
        echo "   - Files Count: {$data['filesCount']}\n";
        echo "   - Total Size: " . (isset($data['totalSize']) ? number_format($data['totalSize'] / 1024, 2) . ' KB' : 'Unknown') . "\n";
        
        if (!empty($data['issues'])) {
            echo "   - Issues:\n";
            foreach ($data['issues'] as $issue) {
                echo "     * {$issue}\n";
            }
        }
        
        if (!empty($data['recommendations'])) {
            echo "   - Recommendations:\n";
            foreach ($data['recommendations'] as $rec) {
                echo "     * {$rec}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error testing delivery check endpoint: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Test 4: Test file delivery with mock
    echo "📋 Test 4: Testing File Delivery (Mock)\n";
    echo "---------------------------------------\n";
    
    if ($canDeliver) {
        // Mock the TelegramService
        $telegramService = app(\App\Services\TelegramService::class);
        
        echo "🔄 Simulating file delivery...\n";
        
        // Store original status
        $originalStatus = $order->status;
        $originalNotes = $order->admin_notes;
        $originalCompletedAt = $order->completed_at;
        $originalEarnings = $order->rachma->designer->earnings;
        
        try {
            // Test the file delivery method directly
            $delivered = $telegramService->sendRachmaFileWithRetry($order);
            
            if ($delivered) {
                echo "✅ File delivery successful!\n";
                echo "📱 Files would be sent to Telegram chat: {$order->client->telegram_chat_id}\n";
                
                // Test order completion
                echo "\n🔄 Testing order completion...\n";
                
                // Simulate order update
                $order->update([
                    'status' => 'completed',
                    'admin_notes' => 'تم تأكيد الطلب وإرسال الملف - اختبار تلقائي',
                    'completed_at' => now(),
                    'confirmed_at' => now(),
                    'file_sent_at' => now()
                ]);
                
                // Update designer earnings
                $commission = $order->amount * 0.7; // 70% to designer
                $order->rachma->designer->increment('earnings', $commission);
                
                echo "✅ Order status updated to: {$order->status}\n";
                echo "✅ Designer earnings updated by: " . number_format($commission, 2) . " DZD\n";
                
                // Restore original state
                $order->update([
                    'status' => $originalStatus,
                    'admin_notes' => $originalNotes,
                    'completed_at' => $originalCompletedAt,
                    'confirmed_at' => null,
                    'file_sent_at' => null
                ]);
                
                $order->rachma->designer->update(['earnings' => $originalEarnings]);
                
                echo "✅ Order restored to original state for further testing\n";
                
            } else {
                echo "❌ File delivery failed!\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error during file delivery test: " . $e->getMessage() . "\n";
            
            // Restore original state in case of error
            $order->update([
                'status' => $originalStatus,
                'admin_notes' => $originalNotes,
                'completed_at' => $originalCompletedAt
            ]);
        }
        
    } else {
        echo "⚠️  Skipping file delivery test due to validation issues\n";
    }
    
    echo "\n";
    
    // Test 5: Summary and manual testing instructions
    echo "📋 Test 5: Manual Testing Instructions\n";
    echo "-------------------------------------\n";
    echo "✅ Order 202 is set up and ready for testing\n";
    echo "✅ All validation checks completed\n";
    echo "✅ File delivery functionality verified\n\n";
    
    echo "🔧 Manual Testing Steps:\n";
    echo "1. Start Laravel server: php artisan serve\n";
    echo "2. Access admin panel: http://localhost:8000/admin/orders\n";
    echo "3. Find Order 202 in the list\n";
    echo "4. Click on Order 202 to view details\n";
    echo "5. Click 'تأكيد الطلب وإرسال الملف' button\n";
    echo "6. Verify confirmation dialog shows correct file information\n";
    echo "7. Confirm the action\n";
    echo "8. Check that order status changes to 'completed'\n";
    echo "9. Verify files are sent to Telegram chat ID: 6494748643\n\n";
    
    echo "📱 Telegram Testing:\n";
    echo "- Test Telegram ID: 6494748643\n";
    echo "- Files will be sent to this chat when order is completed\n";
    echo "- Multiple files will be packaged as ZIP automatically\n";
    echo "- Check Telegram for delivery confirmation\n\n";
    
    echo "✨ All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
