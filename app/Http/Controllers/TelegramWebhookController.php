<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebhookController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // Rate limiting to prevent abuse
            $key = 'telegram_webhook:' . $request->ip();
            if (RateLimiter::tooManyAttempts($key, 60)) { // 60 requests per minute
                Log::warning('Telegram webhook rate limit exceeded', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json(['error' => 'Rate limit exceeded'], 429);
            }
            
            RateLimiter::hit($key, 60); // 1 minute decay

            // Validate request method
            if (!$request->isMethod('POST')) {
                Log::warning('Invalid request method for Telegram webhook', [
                    'method' => $request->method(),
                    'ip' => $request->ip()
                ]);
                
                return response()->json(['error' => 'Method not allowed'], 405);
            }

            // Validate content type
            if (!$request->isJson()) {
                Log::warning('Invalid content type for Telegram webhook', [
                    'content_type' => $request->header('Content-Type'),
                    'ip' => $request->ip()
                ]);
                
                return response()->json(['error' => 'Invalid content type'], 400);
            }

            // Get webhook data
            $updateData = $request->json()->all();
            
            // Basic validation
            if (empty($updateData) || !isset($updateData['update_id'])) {
                Log::warning('Invalid webhook data received', [
                    'data' => $updateData,
                    'ip' => $request->ip()
                ]);
                
                return response()->json(['error' => 'Invalid data'], 400);
            }

            // Validate webhook signature (optional but recommended)
            if (!$this->validateWebhookSignature($request)) {
                Log::warning('Invalid webhook signature', [
                    'ip' => $request->ip(),
                    'update_id' => $updateData['update_id'] ?? 'unknown'
                ]);
                
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Process the webhook update
            $processed = $this->telegramService->processWebhookUpdate($updateData);
            
            if ($processed) {
                Log::info('Telegram webhook processed successfully', [
                    'update_id' => $updateData['update_id'],
                    'ip' => $request->ip()
                ]);
                
                return response()->json(['status' => 'ok']);
            } else {
                Log::error('Failed to process Telegram webhook', [
                    'update_id' => $updateData['update_id'],
                    'ip' => $request->ip()
                ]);
                
                return response()->json(['error' => 'Processing failed'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception in Telegram webhook handler', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Set webhook URL (admin only)
     */
    public function setWebhook(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url' => 'required|url|max:255'
            ]);

            $url = $request->input('url');
            $result = $this->telegramService->setWebhook($url);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook set successfully',
                    'url' => $url
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to set webhook'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to set webhook', [
                'error' => $e->getMessage(),
                'url' => $request->input('url')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error setting webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove webhook (admin only)
     */
    public function removeWebhook(): JsonResponse
    {
        try {
            $result = $this->telegramService->removeWebhook();

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook removed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove webhook'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to remove webhook', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error removing webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get webhook info (admin only)
     */
    public function getWebhookInfo(): JsonResponse
    {
        try {
            $info = $this->telegramService->getWebhookInfo();
            
            return response()->json([
                'success' => true,
                'data' => $info
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get webhook info', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting webhook info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test bot connection (admin only)
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->telegramService->verifyConnection();
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Bot connection successful' : 'Bot connection failed'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to test bot connection', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error testing connection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate webhook signature (basic implementation)
     * For production, implement proper HMAC validation if needed
     */
    private function validateWebhookSignature(Request $request): bool
    {
        // For basic security, check if request comes from expected source
        // In production, you might want to implement HMAC signature validation
        
        $secretToken = config('services.telegram.webhook_secret');
        
        if (!$secretToken) {
            // If no secret is configured, skip validation
            return true;
        }
        
        $providedToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
        
        return hash_equals($secretToken, (string) $providedToken);
    }

    /**
     * Health check endpoint
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'service' => 'telegram-webhook',
            'timestamp' => now()->toISOString()
        ]);
    }
} 