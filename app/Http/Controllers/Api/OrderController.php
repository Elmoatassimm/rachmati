<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rachma;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Create a new order (items-only approach)
     */
    public function store(Request $request): JsonResponse
    {
        // Only support items array approach
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1|max:20',
            'items.*.rachma_id' => 'required|exists:rachmat,id',
            'payment_method' => 'required|in:ccp,baridi_mob,dahabiya',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'items.required' => 'يجب تحديد الرشمات المراد شراؤها.',
            'items.array' => 'تنسيق الرشمات غير صحيح.',
            'items.min' => 'يجب تحديد رشمة واحدة على الأقل.',
            'items.max' => 'لا يمكن شراء أكثر من 20 رشمة في طلب واحد.',
            'items.*.rachma_id.required' => 'معرف الرشمة مطلوب.',
            'items.*.rachma_id.exists' => 'الرشمة المحددة غير موجودة.',
            'payment_method.required' => 'طريقة الدفع مطلوبة.',
            'payment_method.in' => 'طريقة الدفع المحددة غير صحيحة.',
            'payment_proof.required' => 'إثبات الدفع مطلوب.',
            'payment_proof.image' => 'إثبات الدفع يجب أن يكون صورة.',
            'payment_proof.mimes' => 'إثبات الدفع يجب أن يكون من نوع: jpeg, png, jpg.',
            'payment_proof.max' => 'حجم إثبات الدفع يجب أن يكون أقل من 2 ميجابايت.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'أخطاء في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get all rachmat IDs from the request
            $rachmaIds = collect($request->items)->pluck('rachma_id')->unique()->toArray();

            // Check for duplicate purchases
            $user = $request->user();
            $alreadyPurchasedIds = $user->hasPurchasedAnyRachmat($rachmaIds);

            if (!empty($alreadyPurchasedIds)) {
                // Get the titles of already purchased rachmat for error message
                $alreadyPurchasedRachmat = Rachma::whereIn('id', $alreadyPurchasedIds)
                    ->get(['id', 'title_ar', 'title_fr'])
                    ->map(function ($rachma) {
                        return $rachma->title_ar ?: $rachma->title_fr ?: "رشمة #{$rachma->id}";
                    });

                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'لقد قمت بشراء بعض الرشمات من قبل',
                    'error_type' => 'duplicate_purchase',
                    'already_purchased' => [
                        'rachma_ids' => $alreadyPurchasedIds,
                        'rachma_titles' => $alreadyPurchasedRachmat->toArray(),
                        'message' => 'الرشمات التي تملكها بالفعل: ' . $alreadyPurchasedRachmat->join('، ')
                    ]
                ], 400);
            }

            // Prepare order items
            $orderItems = [];
            $totalAmount = 0;

            // Get all rachmat for the items
            $rachmat = Rachma::with('designer')->whereIn('id', $rachmaIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                $rachma = $rachmat->get($item['rachma_id']);

                if (!$rachma) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "الرشمة رقم {$item['rachma_id']} غير موجودة"
                    ], 400);
                }

                if ($rachma->designer->subscription_status !== 'active') {
                    $rachmaTitle = $rachma->title_ar ?: $rachma->title_fr ?: "رشمة #{$rachma->id}";
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "الرشمة '{$rachmaTitle}' غير متاحة حالياً"
                    ], 400);
                }

                $orderItems[] = [
                    'rachma_id' => $rachma->id,
                    'price' => $rachma->price,
                ];
                $totalAmount += $rachma->price;
            }

            // Store payment proof
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');

            // Create order (items-only approach, no rachma_id)
            $order = Order::create([
                'client_id' => $request->user()->id,
                'amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'payment_proof_path' => $paymentProofPath,
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                $order->orderItems()->create($itemData);
            }

            DB::commit();

            // Load relationships for response
            $order->load(['orderItems.rachma.designer.user', 'client']);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الطلب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(string $id): JsonResponse
    {
        try {
            $order = Order::with([
                'client',
                'orderItems.rachma.designer.user'
            ])
                ->where('client_id', request()->user()->id)
                ->findOrFail($id);

            // Add URL attributes to the order
            $orderData = $order->toArray();
            $orderData['payment_proof_url'] = $order->payment_proof_url;

            // Add preview image URLs for order items
            if (isset($orderData['order_items']) && is_array($orderData['order_items'])) {
                foreach ($orderData['order_items'] as $index => $item) {
                    if (isset($item['rachma'])) {
                        // Get the rachma from the loaded relationship
                        $orderItem = $order->orderItems->get($index);
                        if ($orderItem && $orderItem->rachma) {
                            $orderData['order_items'][$index]['rachma']['preview_image_urls'] = $orderItem->rachma->preview_image_urls;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $orderData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get user's orders
     */
    public function myOrders(Request $request): JsonResponse
    {
        try {
            $query = Order::with([
                'orderItems.rachma.designer.user'
            ])
                ->where('client_id', $request->user()->id);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSorts = ['created_at', 'amount', 'status'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 50);
            $orders = $query->paginate($perPage);

            // Add preview image URLs to each order
            $orders->getCollection()->transform(function ($order) {
                $orderData = $order->toArray();
                $orderData['payment_proof_url'] = $order->payment_proof_url;

                // Add preview URLs for order items
                if (isset($orderData['order_items']) && is_array($orderData['order_items'])) {
                    foreach ($orderData['order_items'] as $index => $item) {
                        if (isset($item['rachma'])) {
                            $orderItem = $order->orderItems->get($index);
                            if ($orderItem && $orderItem->rachma) {
                                $orderData['order_items'][$index]['rachma']['preview_image_urls'] = $orderItem->rachma->preview_image_urls;
                            }
                        }
                    }
                }

                return $orderData;
            });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order statuses for filtering (simplified system)
     */
    public function statuses(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pending' => 'Pending',
                'completed' => 'Completed',
                'rejected' => 'Rejected',
            ]
        ]);
    }
}
