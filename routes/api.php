<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RachmatController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\AdminPaymentInfoController;
use App\Http\Controllers\Api\DesignerSubscriptionRequestController;
use App\Http\Controllers\Api\AdminSubscriptionRequestController;
use App\Http\Controllers\TelegramWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Public rachmat browsing
Route::get('/rachmat', [RachmatController::class, 'index']);
Route::get('/rachmat/{id}', [RachmatController::class, 'show']);

// Public categories
Route::get('/categories', [RachmatController::class, 'categories']);

// Popular rachmat
Route::get('/popular', [RachmatController::class, 'popular']);

// Designer details
Route::get('/designers/{id}', [RachmatController::class, 'designer']);

// Parts suggestions
Route::get('/parts-suggestions', [RachmatController::class, 'partsSuggestions']);


// Public admin payment info (for mobile app)
Route::get('/admin-payment-info', [AdminPaymentInfoController::class, 'index']);

// Telegram webhook (public endpoint - no auth required)
Route::post( '/telegram/webhook', [TelegramWebhookController::class, 'webhook']);
Route::get('/telegram/health', [TelegramWebhookController::class, 'health']);

// Protected routes (JWT Auth - Clients only)
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    
    // Order routes
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    
    // Rating routes
    Route::post('/ratings', [RatingController::class, 'store']);
    Route::get('/ratings/{targetType}/{targetId}', [RatingController::class, 'index']);
    
    // Rachma file download and telegram management
    Route::post('/rachmat/{rachma}/download-files', [RachmatController::class, 'downloadFiles']);
    Route::post('/rachmat/{rachma}/resend-telegram-files', [RachmatController::class, 'resendTelegramFiles']);
    Route::post('/unlink-telegram', [RachmatController::class, 'unlinkTelegram']);
});

// Public temporary file download (no auth required - security through temporary URLs)
Route::get('/download-temp/{filename}', function ($filename) {
    $filePath = storage_path('app/temp/' . $filename);
    
    if (!file_exists($filePath)) {
        abort(404, 'ملف التحميل غير موجود أو انتهت صلاحيته');
    }
    
    // Check if file is too old (1 hour)
    if (filemtime($filePath) < time() - 3600) {
        unlink($filePath);
        abort(404, 'انتهت صلاحية رابط التحميل');
    }
    
    return response()->download($filePath)->deleteFileAfterSend(true);
})->name('api.download-temp');

 