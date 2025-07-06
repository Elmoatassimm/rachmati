<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DesignerController as AdminDesignerController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\PricingPlanController as AdminPricingPlanController;
use App\Http\Controllers\Admin\AdminPaymentInfoController;
use App\Http\Controllers\Admin\SubscriptionRequestController as AdminSubscriptionRequestController;
use App\Http\Controllers\Admin\RachmatController as AdminRachmatController;
use App\Http\Controllers\Admin\PartsSuggestionsController as AdminPartsSuggestionsController;
use App\Http\Controllers\Admin\PrivacyPolicyController as AdminPrivacyPolicyController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\Designer\DashboardController as DesignerDashboardController;
use App\Http\Controllers\Designer\RachmatController as DesignerRachmaController;
use App\Http\Controllers\Designer\OrderController as DesignerOrderController;
use App\Http\Controllers\Designer\SubscriptionRequestController as DesignerSubscriptionRequestController;
use App\Http\Controllers\Designer\StoreController as DesignerStoreController;
use App\Http\Controllers\Designer\SocialMediaController as DesignerSocialMediaController;
use App\Http\Controllers\TelegramWebhookController;


// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Designer management
    Route::get('/designers', [AdminDesignerController::class, 'index'])->name('designers.index');
    Route::get('/designers/{designer}', [AdminDesignerController::class, 'show'])->name('designers.show');
    Route::delete('/designers/{designer}', [AdminDesignerController::class, 'destroy'])->name('designers.destroy');
    Route::post('/designers/{designer}/approve-subscription', [AdminDesignerController::class, 'approveSubscription'])->name('designers.approve-subscription');
    Route::post('/designers/{designer}/reject-subscription', [AdminDesignerController::class, 'rejectSubscription'])->name('designers.reject-subscription');
    Route::post('/designers/{designer}/toggle-status', [AdminDesignerController::class, 'toggleStatus'])->name('designers.toggle-status');

    // Enhanced subscription management
    Route::post('/designers/{designer}/activate-subscription', [AdminDesignerController::class, 'activateSubscription'])->name('designers.activate-subscription');
    Route::post('/designers/{designer}/deactivate-subscription', [AdminDesignerController::class, 'deactivateSubscription'])->name('designers.deactivate-subscription');
    Route::post('/designers/{designer}/extend-subscription', [AdminDesignerController::class, 'extendSubscription'])->name('designers.extend-subscription');

    // Paid earnings management
    Route::get('/designers/{designer}/edit-paid-earnings', [AdminDesignerController::class, 'editPaidEarnings'])->name('designers.edit-paid-earnings');
    Route::put('/designers/{designer}/update-paid-earnings', [AdminDesignerController::class, 'updatePaidEarnings'])->name('designers.update-paid-earnings');
    
    // Order management
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [AdminOrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::get('/orders/{order}/check-file-delivery', [AdminOrderController::class, 'checkFileDelivery'])->name('orders.check-file-delivery');
    // Old routes removed - now using simplified inline status updates
    
    // Category management
    Route::resource('categories', AdminCategoryController::class);
    Route::delete('/categories/{category}/force', [AdminCategoryController::class, 'forceDestroy'])->name('categories.force-destroy');

    // Pricing Plans management
    Route::resource('pricing-plans', AdminPricingPlanController::class);
    Route::post('/pricing-plans/{pricingPlan}/toggle-status', [AdminPricingPlanController::class, 'toggleStatus'])->name('pricing-plans.toggle-status');

    // Admin Payment Info management
    Route::resource('payment-info', AdminPaymentInfoController::class);
    Route::post('/payment-info/{paymentInfo}/toggle-status', [AdminPaymentInfoController::class, 'toggleStatus'])->name('payment-info.toggle-status');

    // Subscription Requests management
    Route::get('/subscription-requests', [AdminSubscriptionRequestController::class, 'index'])->name('subscription-requests.index');
    Route::get('/subscription-requests/{subscriptionRequest}', [AdminSubscriptionRequestController::class, 'show'])->name('subscription-requests.show');
    Route::put('/subscription-requests/{subscriptionRequest}', [AdminSubscriptionRequestController::class, 'update'])->name('subscription-requests.update');
    Route::post('/subscription-requests/bulk-update', [AdminSubscriptionRequestController::class, 'bulkUpdate'])->name('subscription-requests.bulk-update');
    Route::get('/subscription-requests-statistics', [AdminSubscriptionRequestController::class, 'statistics'])->name('subscription-requests.statistics');

    // Parts Suggestions management
    Route::resource('parts-suggestions', AdminPartsSuggestionsController::class);
    Route::post('/parts-suggestions/{partsSuggestion}/toggle-status', [AdminPartsSuggestionsController::class, 'toggleStatus'])->name('parts-suggestions.toggle-status');
    Route::get('/api/parts-suggestions/active', [AdminPartsSuggestionsController::class, 'getActive'])->name('parts-suggestions.active');

    // Privacy Policy management
    Route::resource('privacy-policy', AdminPrivacyPolicyController::class);
    Route::post('/privacy-policy/{privacyPolicy}/toggle-status', [AdminPrivacyPolicyController::class, 'toggleStatus'])->name('privacy-policy.toggle-status');

    // Rachmat management
    Route::prefix('rachmat')->name('rachmat.')->group(function () {
        Route::get('/', [AdminRachmatController::class, 'index'])->name('index');
        Route::get('/{rachma}', [AdminRachmatController::class, 'show'])->name('show');
        Route::delete('/{rachma}', [AdminRachmatController::class, 'destroy'])->name('destroy');
        Route::delete('/{rachma}/force', [AdminRachmatController::class, 'forceDestroy'])->name('force-destroy');

        // File management routes
        Route::get('/{rachma}/download', [AdminRachmatController::class, 'downloadFile'])->name('download-file');
        Route::get('/{rachma}/download/{fileId}', [AdminRachmatController::class, 'downloadFile'])->name('download-file-by-id');
        Route::get('/{rachma}/preview/{imageIndex}', [AdminRachmatController::class, 'getPreviewImage'])->name('preview-image');
        Route::get('/{rachma}/preview/{imageIndex}/download', [AdminRachmatController::class, 'downloadPreviewImage'])->name('download-preview');
    });

    // Telegram bot management (admin only)
    Route::prefix('telegram')->name('telegram.')->group(function () {
        Route::post('/set-webhook', [TelegramWebhookController::class, 'setWebhook'])->name('set-webhook');
        Route::delete('/remove-webhook', [TelegramWebhookController::class, 'removeWebhook'])->name('remove-webhook');
        Route::get('/webhook-info', [TelegramWebhookController::class, 'getWebhookInfo'])->name('webhook-info');
        Route::get('/test-connection', [TelegramWebhookController::class, 'testConnection'])->name('test-connection');
    });
});

// Designer routes
Route::middleware(['auth', 'verified'])->prefix('designer')->name('designer.')->group(function () {
    // Dashboard accessible without active subscription (for subscription management)
    Route::get('/dashboard', [DesignerDashboardController::class, 'index'])->name('dashboard');
    Route::post('/subscription', [DesignerDashboardController::class, 'store'])->name('subscription.store');

    
    
    // Subscription management routes (accessible without active subscription)
    Route::get('/subscription/request', [DesignerDashboardController::class, 'subscriptionRequest'])->name('subscription.request');
    Route::post('/subscription/request', [DesignerDashboardController::class, 'storeSubscriptionRequest'])->name('subscription.request.store');
    Route::get('/subscription/pending', [DesignerDashboardController::class, 'subscriptionPending'])->name('subscription.pending');
    
    // New Subscription Request management routes
    Route::get('/subscription-requests', [DesignerSubscriptionRequestController::class, 'index'])->name('subscription-requests.index');
    Route::get('/subscription-requests/create', [DesignerSubscriptionRequestController::class, 'create'])->name('subscription-requests.create');
    Route::post('/subscription-requests', [DesignerSubscriptionRequestController::class, 'store'])->name('subscription-requests.store');
    Route::get('/subscription-requests/{subscriptionRequest}', [DesignerSubscriptionRequestController::class, 'show'])->name('subscription-requests.show');
    
    // Legacy renewal routes (keeping for backward compatibility)
    Route::get('/subscription', [DesignerDashboardController::class, 'renewSubscription'])->name('subscription.index');
    Route::post('/subscription/renewal', [DesignerDashboardController::class, 'storeRenewal'])->name('subscription.renewal');
    
    // Temporary testing routes without subscription middleware
    Route::get('/rachmat/test/{rachma}', [DesignerRachmaController::class, 'show'])->name('rachmat.test-show');

    // Protected routes requiring active subscription
    Route::middleware(['designer.subscription'])->group(function () {
        // Rachma management (requires active subscription)
        Route::resource('rachmat', DesignerRachmaController::class)->except(['edit', 'update']);

        // Rachma file download routes
        Route::get('/rachmat/{rachma}/download', [DesignerRachmaController::class, 'downloadFile'])->name('rachmat.download');
        Route::get('/rachmat/{rachma}/download/{fileId}', [DesignerRachmaController::class, 'downloadFile'])->name('rachmat.download-file');
        Route::get('/rachmat/{rachma}/preview/{imageIndex}', [DesignerRachmaController::class, 'getPreviewImage'])->name('rachmat.preview-image');

        // Orders management
        Route::get('/orders', [DesignerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [DesignerOrderController::class, 'show'])->name('orders.show');
        
        // Additional protected features can be added here
        Route::get('/analytics', [DesignerDashboardController::class, 'analytics'])->name('analytics');

        // Store Management
        Route::get('/store', [DesignerStoreController::class, 'index'])->name('store.index');
        Route::get('/store/show', [DesignerStoreController::class, 'show'])->name('store.show');
        Route::put('/store/profile', [DesignerStoreController::class, 'updateProfile'])->name('store.profile.update');

        // Social Media Management
        Route::post('/social-media', [DesignerSocialMediaController::class, 'store'])->name('social-media.store');
        Route::put('/social-media/{socialMedia}', [DesignerSocialMediaController::class, 'update'])->name('social-media.update');
        Route::patch('/social-media/{socialMedia}/toggle', [DesignerSocialMediaController::class, 'toggleStatus'])->name('social-media.toggle');
        Route::delete('/social-media/{socialMedia}', [DesignerSocialMediaController::class, 'destroy'])->name('social-media.destroy');

        Route::get('/earnings', function () {
            return 'Earnings page - requires active subscription';
        })->name('earnings');
    });
});

// Authenticated user dashboard redirect
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isDesigner()) {
            return redirect()->route('designer.dashboard');
        } else {
            return Inertia::render('Dashboard');
        }
    })->name('dashboard');
});





// Public routes (placed at the end to avoid conflicts with admin routes)
Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('privacy-policy.show');



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
