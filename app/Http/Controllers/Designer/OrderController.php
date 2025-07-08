<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of designer's orders
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();
        $designer = $user->designer;

        if (!$designer) {
            return redirect()->route('designer.dashboard')
                ->with('error', 'لم يتم العثور على ملف المصمم');
        }

        // Get orders for this designer's rachmat (both single and multi-item orders)
        $query = Order::with(['client', 'rachma', 'orderItems.rachma'])
            ->where(function ($q) use ($designer) {
                // Orders with direct rachma_id belonging to this designer
                $q->whereHas('rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                })
                // OR orders with order items containing this designer's rachmat
                ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                });
            })
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('rachma', function ($rachmaQuery) use ($search) {
                    $rachmaQuery->where('title', 'like', "%{$search}%");
                })
                ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Get paginated orders
        $orders = $query->paginate(15)->withQueryString();

        // Get statistics (including both single and multi-item orders)
        $baseQuery = function ($status = null) use ($designer) {
            $query = Order::where(function ($q) use ($designer) {
                // Orders with direct rachma_id belonging to this designer
                $q->whereHas('rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                })
                // OR orders with order items containing this designer's rachmat
                ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                });
            });

            if ($status) {
                $query->where('status', $status);
            }

            return $query;
        };

        $stats = [
            'total' => $baseQuery()->count(),
            'completed' => $baseQuery('completed')->count(),
            'pending' => $baseQuery('pending')->count(),
            'processing' => $baseQuery('processing')->count(),
        ];

        return Inertia::render('Designer/Orders/Index', [
            'orders' => $orders,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search'])
        ]);
    }

    /**
     * Display the specified order
     */
    public function show(Order $order): Response
    {
        $user = Auth::user();
        $designer = $user->designer;

        if (!$designer) {
            abort(403, 'Designer profile not found');
        }

        // Load order relationships first
        $order->load([
            'client',
            'rachma.categories',
            'rachma.parts',
            'orderItems.rachma.categories',
            'orderItems.rachma.parts'
        ]);

        // Check authorization for both single and multi-item orders
        $hasAccess = false;

        // For single-item orders (legacy)
        if ($order->rachma && $order->rachma->designer_id === $designer->id) {
            $hasAccess = true;
        }

        // For multi-item orders
        if ($order->orderItems && $order->orderItems->count() > 0) {
            foreach ($order->orderItems as $orderItem) {
                if ($orderItem->rachma && $orderItem->rachma->designer_id === $designer->id) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        if (!$hasAccess) {
            abort(403, 'Unauthorized access to order');
        }

        return Inertia::render('Designer/Orders/Show', [
            'order' => $order
        ]);
    }
} 