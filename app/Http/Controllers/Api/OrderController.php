<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rachma;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Create a new order
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rachma_id' => 'required|exists:rachmat,id',
            'payment_method' => 'required|in:ccp,baridi_mob,dahabiya',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rachma = Rachma::with('designer')->findOrFail($request->rachma_id);

            // Check if rachma is available
            if ($rachma->designer->subscription_status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This rachma is not available'
                ], 400);
            }

            // Check if designer store is active
            if ($rachma->designer->subscription_status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Designer store is not active'
                ], 400);
            }

            // Store payment proof
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');

            // Create order
            $order = Order::create([
                'client_id' => $request->user()->id,
                'rachma_id' => $rachma->id,
                'amount' => $rachma->price,
                'payment_method' => $request->payment_method,
                'payment_proof_path' => $paymentProofPath,
                'status' => 'pending',
            ]);

            $order->load(['rachma', 'client']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
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
            $order = Order::with(['rachma.designer.user', 'client'])
                ->where('client_id', request()->user()->id)
                ->findOrFail($id);

            // Add URL attributes to the order and rachma
            $orderData = $order->toArray();
            $orderData['payment_proof_url'] = $order->payment_proof_url;

            if ($order->rachma) {
                $orderData['rachma']['preview_image_urls'] = $order->rachma->preview_image_urls;
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
            $query = Order::with(['rachma.designer.user'])
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
