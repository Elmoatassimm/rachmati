<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    /**
     * Generate a Telegram bot linking URL for the authenticated user
     */
    public function generateLink(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is already linked
            if ($user->telegram_chat_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'حسابك مرتبط بالفعل بالتليجرام / Your account is already linked to Telegram',
                    'is_linked' => true
                ], 400);
            }

            // Get bot username from config
            $botUsername = config('services.telegram.bot_username');

            if (!$botUsername) {
                Log::error('Telegram bot username not configured');
                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في إعدادات النظام / System configuration error'
                ], 500);
            }

            // Generate the Telegram bot link using user ID directly
            $telegramLink = "https://telegram.me/{$botUsername}?start={$user->id}";

            Log::info('Telegram linking URL generated', [
                'user_id' => $user->id,
                'telegram_link' => $telegramLink
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء رابط التليجرام بنجاح / Telegram link generated successfully',
                'data' => [
                    'telegram_link' => $telegramLink
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate Telegram link', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في النظام / System error occurred'
            ], 500);
        }
    }

    /**
     * Get current Telegram linking status
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'is_linked' => $user->telegram_chat_id !== null,
                    'telegram_chat_id' => $user->telegram_chat_id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get Telegram status', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في النظام / System error occurred'
            ], 500);
        }
    }
}
