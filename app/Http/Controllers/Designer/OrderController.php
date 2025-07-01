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

        // Get orders for this designer's rachmat
        $query = Order::with(['client', 'rachma'])
            ->whereHas('rachma', function ($q) use ($designer) {
                $q->where('designer_id', $designer->id);
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

        // Get statistics
        $stats = [
            'total' => Order::whereHas('rachma', function ($q) use ($designer) {
                $q->where('designer_id', $designer->id);
            })->count(),
            'completed' => Order::whereHas('rachma', function ($q) use ($designer) {
                $q->where('designer_id', $designer->id);
            })->where('status', 'completed')->count(),
            'pending' => Order::whereHas('rachma', function ($q) use ($designer) {
                $q->where('designer_id', $designer->id);
            })->where('status', 'pending')->count(),
            'processing' => Order::whereHas('rachma', function ($q) use ($designer) {
                $q->where('designer_id', $designer->id);
            })->where('status', 'processing')->count(),
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

        // Ensure the order belongs to this designer's rachma
        if ($order->rachma->designer_id !== $designer->id) {
            abort(403, 'Unauthorized access to order');
        }

        $order->load(['client', 'rachma.categories', 'rachma.parts']);

        return Inertia::render('Designer/Orders/Show', [
            'order' => $order
        ]);
    }
} 