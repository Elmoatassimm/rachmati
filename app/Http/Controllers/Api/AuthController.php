<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Designer;
use App\Models\PasswordResetCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Notifications\Auth\ApiPasswordResetNotification;

class AuthController extends Controller
{
    /**
     * Register a new client user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'telegram_chat_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'أخطاء في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Only allow client registration through API
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'client', // Force client type
                'phone' => $request->phone,
                'telegram_chat_id' => $request->telegram_chat_id,
            ]);

            $token = auth('api')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الحساب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login client user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'أخطاء في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة',
            ], 401);
        }

        $user = auth('api')->user();

        // Only allow clients to login through API
        if (!$user->isClient()) {
            auth('api')->logout();
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالدخول من هذا المكان',
            ], 403);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated client user
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Logout client user
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    /**
     * Refresh JWT token
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::getToken();
            $newToken = JWTAuth::refresh($token);
            return $this->respondWithToken($newToken);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في تحديث الرمز المميز',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Update client profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'telegram_chat_id' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'أخطاء في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->update($request->only(['name', 'phone', 'telegram_chat_id']));

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الملف الشخصي بنجاح',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في تحديث الملف الشخصي',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send password reset verification code
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'يجب أن يكون البريد الإلكتروني صالحاً.',
            'email.exists' => 'البريد الإلكتروني غير مسجل في النظام.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'أخطاء في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Only allow clients to reset password through API
            if (!$user->isClient()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بإعادة تعيين كلمة المرور من هذا المكان',
                ], 403);
            }

            // Create verification code
            $resetCode = PasswordResetCode::createForEmail($request->email);

            // Send notification
            $user->notify(new ApiPasswordResetNotification($resetCode->code));

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إرسال رمز التحقق',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password using verification code
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'يجب أن يكون البريد الإلكتروني صالحاً.',
            'email.exists' => 'البريد الإلكتروني غير مسجل في النظام.',
            'code.required' => 'رمز التحقق مطلوب.',
            'code.size' => 'رمز التحقق يجب أن يكون 6 أرقام.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'أخطاء في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Only allow clients to reset password through API
            if (!$user->isClient()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بإعادة تعيين كلمة المرور من هذا المكان',
                ], 403);
            }

            // Find and validate the verification code
            $resetCode = PasswordResetCode::findValidCode($request->email, $request->code);

            if (!$resetCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Mark code as used
            $resetCode->markAsUsed();

            // Clean up expired codes
            PasswordResetCode::cleanupExpired();

            return response()->json([
                'success' => true,
                'message' => 'تم تغيير كلمة المرور بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في تغيير كلمة المرور',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check password reset system status
     */
    public function passwordResetStatus(): JsonResponse
    {
        try {
            $activeCodesCount = PasswordResetCode::where('expires_at', '>', now())
                ->whereNull('used_at')
                ->count();

            $expiredCodesCount = PasswordResetCode::where('expires_at', '<=', now())
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'system_status' => 'operational',
                    'active_codes' => $activeCodesCount,
                    'expired_codes' => $expiredCodesCount,
                    'code_expiration_minutes' => 15,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في التحقق من حالة النظام',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'user' => auth('api')->user(),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]
        ]);
    }
}
  


/**
 * 
 
 - now i went in order checkout i went client cant buy rachma 2 times 
- i went change order confirmation form in telegram to be like this 
🎉 تم تأكيد طلبك
• رقم الطلب: 11

الرشمة 1

• تصميم الزهور عصرية 99 - متجر Oussama Benrabah

الجزء 1 
تفاصيل الجزء
الطول العرض عدد الغرز 

 
 الجزء 2 
تفاصيل الجزء
الطول العرض عدد الغرز


......
after that send files 
- in rachma create page imrove choose rachma part UX

 */