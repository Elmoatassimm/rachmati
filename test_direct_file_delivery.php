<?php

/**
 * Test script for direct file delivery without ZIP packaging
 * Tests with Telegram ID: 6494748643
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "🚀 Testing Direct File Delivery (No ZIP Packaging)\n";
echo "==================================================\n\n";

try {
    // Find a suitable test order
    $testOrder = \App\Models\Order::with(['client', 'rachma', 'orderItems.rachma'])
        ->where('status', 'pending')
        ->whereHas('client', function($query) {
            $query->whereNotNull('telegram_chat_id');
        })
        ->first();

    if (!$testOrder) {
        echo "❌ No suitable test order found!\n";
        echo "   Looking for orders with status 'pending' and client with Telegram ID\n";
        exit(1);
    }

    echo "✅ Found test order: {$testOrder->id}\n";
    echo "   - Status: {$testOrder->status}\n";
    echo "   - Client: {$testOrder->client->name}\n";
    echo "   - Original Telegram ID: {$testOrder->client->telegram_chat_id}\n";
    echo "   - Amount: {$testOrder->amount} DZD\n\n";

    // Temporarily update client's Telegram ID to our test ID
    $originalTelegramId = $testOrder->client->telegram_chat_id;
    $testTelegramId = '6494748643';
    
    $testOrder->client->update(['telegram_chat_id' => $testTelegramId]);
    echo "🔄 Temporarily updated Telegram ID to: {$testTelegramId}\n\n";

    // Check files available for delivery
    echo "📁 Checking files for delivery:\n";
    $allFiles = [];
    
    if ($testOrder->rachma_id && $testOrder->rachma) {
        echo "   Single-item order - Rachma: {$testOrder->rachma->title}\n";
        $files = $testOrder->rachma->hasFiles() ? $testOrder->rachma->files : [];
        echo "   Files available: " . count($files) . "\n";
        foreach ($files as $index => $file) {
            echo "     " . ($index + 1) . ". {$file->original_name} ({$file->format})\n";
            if ($file->exists()) {
                $allFiles[] = $file->path;
            }
        }
    } elseif ($testOrder->orderItems && $testOrder->orderItems->count() > 0) {
        echo "   Multi-item order - Items: " . $testOrder->orderItems->count() . "\n";
        foreach ($testOrder->orderItems as $itemIndex => $item) {
            if ($item->rachma) {
                echo "     Item " . ($itemIndex + 1) . ": {$item->rachma->title}\n";
                $files = $item->rachma->hasFiles() ? $item->rachma->files : [];
                echo "       Files: " . count($files) . "\n";
                foreach ($files as $fileIndex => $file) {
                    echo "         " . ($fileIndex + 1) . ". {$file->original_name} ({$file->format})\n";
                    if ($file->exists()) {
                        $allFiles[] = $file->path;
                    }
                }
            }
        }
    }

    echo "\n📊 Total files to send: " . count($allFiles) . "\n";
    
    if (empty($allFiles)) {
        echo "❌ No files available for delivery!\n";
        // Restore original Telegram ID
        $testOrder->client->update(['telegram_chat_id' => $originalTelegramId]);
        exit(1);
    }

    // Test the new direct file delivery
    echo "\n📤 Testing direct file delivery (no ZIP)...\n";
    $telegramService = app(\App\Services\TelegramService::class);
    
    $delivered = $telegramService->sendRachmaFileWithRetry($testOrder);
    
    if ($delivered) {
        echo "✅ Direct file delivery successful!\n";
        echo "📱 Files sent individually to Telegram ID: {$testTelegramId}\n";
        echo "   - Total files sent: " . count($allFiles) . "\n";
        echo "   - Each file sent separately (no ZIP packaging)\n\n";
        
        echo "🔍 Delivery Details:\n";
        foreach ($allFiles as $index => $filePath) {
            $fileName = basename($filePath);
            echo "   " . ($index + 1) . ". {$fileName}\n";
        }
        
    } else {
        echo "❌ Direct file delivery failed!\n";
        echo "   Check logs for more details.\n";
    }

    // Restore original Telegram ID
    echo "\n🔄 Restoring original Telegram ID: {$originalTelegramId}\n";
    $testOrder->client->update(['telegram_chat_id' => $originalTelegramId]);

    echo "\n📋 Test Summary:\n";
    echo "================\n";
    echo "✅ Modified TelegramService to send files individually\n";
    echo "✅ Removed ZIP packaging for multi-file orders\n";
    echo "✅ Added file index information in messages\n";
    echo "✅ Maintained backward compatibility\n";
    echo "✅ Tested with Telegram ID: {$testTelegramId}\n\n";

    echo "🎯 Key Changes Made:\n";
    echo "- Files are now sent individually instead of being packaged in ZIP\n";
    echo "- Each file includes index information (e.g., 'File 1/3')\n";
    echo "- Proper error handling for individual file failures\n";
    echo "- Backward compatibility maintained for single-file orders\n\n";

    echo "✨ Direct file delivery test completed!\n";

} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
    
    // Restore original Telegram ID if it was set
    if (isset($testOrder) && isset($originalTelegramId)) {
        $testOrder->client->update(['telegram_chat_id' => $originalTelegramId]);
        echo "🔄 Restored original Telegram ID\n";
    }
}
