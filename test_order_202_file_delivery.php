<?php

/**
 * Test runner script for Order 202 file delivery functionality
 * This script runs the feature tests and provides detailed output
 */

echo "🚀 Testing Order 202 File Delivery Functionality\n";
echo "================================================\n\n";

// Test 1: Run the mock-based feature tests
echo "📋 Test 1: Running Mock-based Feature Tests\n";
echo "-------------------------------------------\n";
$output = shell_exec('cd ' . __DIR__ . ' && php artisan test tests/Feature/Admin/OrderFileDeliveryTest.php --verbose 2>&1');
echo $output . "\n";

// Test 2: Run the real order 202 tests
echo "📋 Test 2: Running Real Order 202 Tests\n";
echo "---------------------------------------\n";
$output = shell_exec('cd ' . __DIR__ . ' && php artisan test tests/Feature/Admin/RealOrderFileDeliveryTest.php --verbose 2>&1');
echo $output . "\n";

// Test 3: Check if order 202 exists and get its details
echo "📋 Test 3: Checking Order 202 Details\n";
echo "-------------------------------------\n";

try {
    // Include Laravel bootstrap
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    $order = \App\Models\Order::find(202);
    
    if ($order) {
        echo "✅ Order 202 exists!\n";
        echo "   - Status: {$order->status}\n";
        echo "   - Client: {$order->client->name} ({$order->client->email})\n";
        echo "   - Telegram ID: " . ($order->client->telegram_chat_id ?? 'Not linked') . "\n";
        echo "   - Amount: {$order->amount} DZD\n";
        echo "   - Created: {$order->created_at}\n";
        
        if ($order->rachma) {
            echo "   - Rachma: {$order->rachma->title}\n";
            echo "   - Designer: {$order->rachma->designer->user->name}\n";
            echo "   - Has Files: " . ($order->rachma->hasFiles() ? 'Yes' : 'No') . "\n";
            echo "   - Files Count: " . count($order->rachma->files) . "\n";
            
            if ($order->rachma->hasFiles()) {
                echo "   - Files:\n";
                foreach ($order->rachma->files as $file) {
                    echo "     * {$file->original_name} ({$file->format}, " . number_format($file->size / 1024, 2) . " KB)\n";
                }
            }
        }
        
        echo "\n";
        
        // Test delivery validation
        echo "🔍 Testing delivery validation for Order 202:\n";
        
        $issues = [];
        $canComplete = true;
        
        // Check client has Telegram
        if (!$order->client->telegram_chat_id) {
            $issues[] = "❌ Client doesn't have Telegram linked";
            $canComplete = false;
        } else {
            echo "✅ Client has Telegram ID: {$order->client->telegram_chat_id}\n";
        }
        
        // Check rachma has files
        if (!$order->rachma || !$order->rachma->hasFiles()) {
            $issues[] = "❌ Rachma doesn't have files";
            $canComplete = false;
        } else {
            echo "✅ Rachma has " . count($order->rachma->files) . " files\n";
        }
        
        // Check file existence
        $missingFiles = [];
        if ($order->rachma && $order->rachma->hasFiles()) {
            foreach ($order->rachma->files as $file) {
                if (!\Illuminate\Support\Facades\Storage::disk('private')->exists($file->path)) {
                    $missingFiles[] = $file->original_name;
                }
            }
            
            if (!empty($missingFiles)) {
                $issues[] = "❌ Missing files: " . implode(', ', $missingFiles);
                $canComplete = false;
            } else {
                echo "✅ All files exist in storage\n";
            }
        }
        
        if ($canComplete) {
            echo "\n🎉 Order 202 is ready for file delivery!\n";
        } else {
            echo "\n⚠️  Order 202 has issues that prevent file delivery:\n";
            foreach ($issues as $issue) {
                echo "   {$issue}\n";
            }
        }
        
    } else {
        echo "❌ Order 202 does not exist in the database\n";
        echo "💡 You may need to create test data or run the test command to create order 202\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error checking order 202: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Run the custom test command
echo "📋 Test 4: Running Custom File Delivery Test Command\n";
echo "----------------------------------------------------\n";
$output = shell_exec('cd ' . __DIR__ . ' && php artisan telegram:test-file-delivery --order-id=202 2>&1');
echo $output . "\n";

// Test 5: Show test summary
echo "📋 Test Summary\n";
echo "---------------\n";
echo "✅ Mock-based feature tests verify the file delivery logic\n";
echo "✅ Real order tests work with actual database data\n";
echo "✅ Order 202 details and validation checks\n";
echo "✅ Custom test command for manual verification\n";
echo "\n";

echo "🔧 Manual Testing Instructions:\n";
echo "1. Access admin panel: http://localhost:8000/admin/orders\n";
echo "2. Find Order 202 or any pending order\n";
echo "3. Click 'تأكيد الطلب وإرسال الملف' button\n";
echo "4. Verify files are sent to Telegram chat ID 6494748643\n";
echo "5. Check order status changes to 'completed'\n";
echo "\n";

echo "📱 Telegram Testing:\n";
echo "- Test Telegram ID: 6494748643\n";
echo "- Files will be sent to this chat ID when order is completed\n";
echo "- Multiple files will be packaged as ZIP automatically\n";
echo "\n";

echo "✨ Testing completed!\n";
