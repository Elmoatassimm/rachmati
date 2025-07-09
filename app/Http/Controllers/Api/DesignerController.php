<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DesignerController extends Controller
{
    /**
     * Get active designers
     * 
     * Returns designers with active subscription status and valid subscription end date
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Designer::with(['user:id,name,email', 'socialMedia'])
                ->where('subscription_status', 'active')
                ->where(function ($q) {
                    $q->whereNull('subscription_end_date')
                      ->orWhere('subscription_end_date', '>=', now());
                });

            // Optional search by store name
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('store_name', 'like', "%{$search}%");
            }

            // Optional pagination
            $perPage = $request->get('per_page', 15);
            $perPage = min($perPage, 50); // Limit max per page to 50

            if ($request->has('paginate') && $request->paginate === 'false') {
                // Return all results without pagination
                $designers = $query->orderBy('store_name')->get();
                
                // Transform the data
                $transformedDesigners = $designers->map(function ($designer) {
                    return $this->transformDesigner($designer);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'تم جلب المصممين النشطين بنجاح',
                    'data' => $transformedDesigners,
                    'meta' => [
                        'total' => $transformedDesigners->count(),
                        'active_designers_count' => $transformedDesigners->count()
                    ]
                ]);
            } else {
                // Return paginated results
                $designers = $query->orderBy('store_name')->paginate($perPage);
                
                // Transform the data
                $transformedDesigners = $designers->getCollection()->map(function ($designer) {
                    return $this->transformDesigner($designer);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'تم جلب المصممين النشطين بنجاح',
                    'data' => $transformedDesigners,
                    'meta' => [
                        'current_page' => $designers->currentPage(),
                        'last_page' => $designers->lastPage(),
                        'per_page' => $designers->perPage(),
                        'total' => $designers->total(),
                        'from' => $designers->firstItem(),
                        'to' => $designers->lastItem(),
                        'active_designers_count' => $designers->total()
                    ]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب المصممين النشطين',
                'error' => config('app.debug') ? $e->getMessage() : 'خطأ في الخادم'
            ], 500);
        }
    }

    /**
     * Transform designer data for API response
     */
    private function transformDesigner(Designer $designer): array
    {
        return [
            'id' => $designer->id,
            'store_name' => $designer->store_name,
            'store_description' => $designer->store_description,
            'subscription_status' => $designer->subscription_status,
            'subscription_end_date' => $designer->subscription_end_date?->format('Y-m-d'),
            'is_subscription_active' => $designer->hasActiveSubscription(),
            'created_at' => $designer->created_at->format('Y-m-d H:i:s'),
            'user' => $designer->user ? [
                'id' => $designer->user->id,
                'name' => $designer->user->name,
                'email' => $designer->user->email,
            ] : null,
            'social_media' => $designer->socialMedia->map(function ($social) {
                return [
                    'platform' => $social->platform,
                    'url' => $social->url,
                ];
            }),
            'rachmat_count' => $designer->rachmat()->count(),
            'total_sales' => $designer->total_sales,
        ];
    }
}
