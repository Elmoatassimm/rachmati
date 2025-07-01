<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminPaymentInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentInfoController extends Controller
{
    /**
     * Get admin payment information for mobile app
     */
    public function index(): JsonResponse
    {
        try {
            $paymentInfos = AdminPaymentInfo::orderBy('created_at', 'desc')
                ->get()
                ->map(function ($paymentInfo) {
                    return [
                        'id' => $paymentInfo->id,
                        'ccp_number' => $paymentInfo->ccp_number,
                        'ccp_key' => $paymentInfo->masked_ccp_key, // Use masked version for security
                        'nom' => $paymentInfo->nom,
                        'adress' => $paymentInfo->adress,
                        'baridimob' => $paymentInfo->baridimob,
                        'formatted_ccp_number' => $paymentInfo->formatted_ccp_number,
                        'created_at' => $paymentInfo->created_at,
                        'updated_at' => $paymentInfo->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب معلومات الدفع بنجاح',
                'message_en' => 'Payment information retrieved successfully',
                'data' => $paymentInfos,
                'count' => $paymentInfos->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب معلومات الدفع: ' . $e->getMessage(),
                'message_en' => 'Failed to retrieve payment information: ' . $e->getMessage(),
                'data' => [],
                'count' => 0,
            ], 500);
        }
    }
}
