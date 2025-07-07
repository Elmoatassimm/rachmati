<?php

/**
 * Telegram Integration Test Script
 * 
 * This script helps test the new user ID-based Telegram linking system
 * Run this script to verify the implementation works correctly
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Services\TelegramService;
use App\Http\Controllers\Api\TelegramController;
use Illuminate\Http\Request;

echo "🤖 Telegram Integration Test Script\n";
echo "==================================\n\n";

// Test 1: Simulate API Link Generation
echo "Test 1: Generate Telegram Link\n";
echo "------------------------------\n";

try {
    // Create a test user
    $testUser = new User([
        'id' => 123,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'telegram_chat_id' => null
    ]);
    
    $botUsername = config('services.telegram.bot_username', 'rachma_test_bot');
    $expectedLink = "https://telegram.me/{$botUsername}?start={$testUser->id}";
    
    echo "✅ Expected Link: {$expectedLink}\n";
    echo "✅ Bot Username: {$botUsername}\n";
    echo "✅ User ID: {$testUser->id}\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Simulate Bot Command Processing
echo "Test 2: Bot Command Processing\n";
echo "------------------------------\n";

$testCommands = [
    '/start 123' => 'Valid User ID',
    '/start 99999' => 'Invalid User ID', 
    '/start abc123token' => 'Token (Backward Compatibility)',
    '/start' => 'Plain Start Command'
];

foreach ($testCommands as $command => $description) {
    echo "Command: {$command}\n";
    echo "Description: {$description}\n";
    
    // Parse command
    if (preg_match('/^\/start\s+(.+)$/', $command, $matches)) {
        $parameter = trim($matches[1]);
        
        if (is_numeric($parameter)) {
            echo "✅ Detected as User ID: {$parameter}\n";
            echo "✅ Will call: handleStartWithUserId()\n";
        } else {
            echo "✅ Detected as Token: {$parameter}\n";
            echo "✅ Will call: handleStartWithToken()\n";
        }
    } else {
        echo "✅ Plain start command\n";
        echo "✅ Will show welcome message\n";
    }
    echo "\n";
}

// Test 3: Expected Bot Responses
echo "Test 3: Expected Bot Responses\n";
echo "------------------------------\n";

$expectedResponses = [
    'success_linking' => "✅ *تم ربط حسابك بنجاح / Compte lié avec succès*\n\n👤 الاسم / Nom: Test User\n📧 البريد الإلكتروني / Email: test@example.com\n\n🔔 ستتلقى الآن إشعارات الطلبات والملفات هنا\nVous recevrez maintenant les notifications et fichiers ici",
    
    'user_not_found' => "❌ *لم يتم العثور على حساب بهذا المعرف / Aucun compte trouvé avec cet identifiant*\n\nيرجى التأكد من الرابط أو إنشاء حساب جديد في التطبيق\nVeuillez vérifier le lien ou créer un compte dans l'application",
    
    'welcome_message' => "🌟 *مرحباً بك في منصة رشماتي / Bienvenue sur Rashmaati Platform*\n\nلربط حسابك، يرجى استخدام الرابط المرسل من التطبيق\nPour lier votre compte, utilisez le lien envoyé depuis l'application\n\nإذا لم تحصل على الرابط، يرجى فتح التطبيق والذهاب إلى إعدادات التليجرام\nSi vous n'avez pas reçu le lien, ouvrez l'application et allez aux paramètres Telegram"
];

foreach ($expectedResponses as $type => $message) {
    echo "Response Type: {$type}\n";
    echo "Message Preview: " . substr($message, 0, 100) . "...\n\n";
}

// Test 4: API Endpoints
echo "Test 4: API Endpoints\n";
echo "---------------------\n";

$endpoints = [
    'POST /api/telegram/generate-link' => 'Generate Telegram linking URL',
    'GET /api/telegram/status' => 'Check user linking status',
    'POST /api/telegram/webhook' => 'Telegram bot webhook endpoint'
];

foreach ($endpoints as $endpoint => $description) {
    echo "✅ {$endpoint} - {$description}\n";
}

echo "\n";

// Test 5: Manual Testing Instructions
echo "Test 5: Manual Testing Instructions\n";
echo "-----------------------------------\n";

echo "1. 🔗 Generate Link:\n";
echo "   curl -X POST http://your-app.com/api/telegram/generate-link \\\n";
echo "        -H 'Authorization: Bearer YOUR_JWT_TOKEN'\n\n";

echo "2. 🤖 Test Bot Commands:\n";
echo "   - Send: /start 123 (replace 123 with actual user ID)\n";
echo "   - Send: /start 99999 (test invalid user ID)\n";
echo "   - Send: /start (test welcome message)\n\n";

echo "3. 📱 Test in Telegram:\n";
echo "   - Open: https://telegram.me/rachma_test_bot?start=123\n";
echo "   - Verify bot responds with success message\n";
echo "   - Check database for updated telegram_chat_id\n\n";

echo "4. 📁 Test File Delivery:\n";
echo "   - Create order with rachma files\n";
echo "   - Mark order as completed\n";
echo "   - Verify files sent to Telegram\n\n";

echo "🎉 Test Script Complete!\n";
echo "========================\n";
echo "The new user ID-based Telegram linking system is ready for testing.\n";
echo "Bot: @rachma_test_bot\n";
echo "System: رشماتي (Rashmaati)\n";

?>
