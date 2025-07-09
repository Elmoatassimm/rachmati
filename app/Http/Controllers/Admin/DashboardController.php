<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Designer;
use App\Models\Order;
use App\Models\Rachma;
use Carbon\Carbon;

class DashboardController extends Controller
{


    public function index()
    {
        // Overview Statistics
        $totalUsers = User::count();
        $totalDesigners = Designer::count();
        $activeDesigners = Designer::where('subscription_status', 'active')
            ->count();
        $totalRachmat = Rachma::count();
        $activeRachmat = Rachma::active()->count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();

        // Recent Activity (include both legacy and new order systems)
        $recentOrders = Order::with(['client', 'rachma.designer.user', 'orderItems.rachma.designer.user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pendingSubscriptions = Designer::with('user')
            ->where('subscription_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Revenue Analytics (last 30 days)
        $revenueData = Order::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Monthly Statistics
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->startOfMonth()->subSecond(); // End of last month

        // Count all orders (both legacy and new system)
        $currentMonthOrders = Order::where('created_at', '>=', $currentMonth)->count();
        $lastMonthOrders = Order::where('created_at', '>=', $lastMonth)
            ->where('created_at', '<=', $lastMonthEnd)
            ->count();

        // Calculate revenue from both direct orders and order items
        $currentMonthRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $currentMonth)
            ->sum('amount');
        $lastMonthRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $lastMonth)
            ->where('created_at', '<=', $lastMonthEnd)
            ->sum('amount');

        // Calculate growth percentages with better logic
        if ($lastMonthOrders > 0) {
            $orderGrowth = round(((($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100), 1);
        } elseif ($currentMonthOrders > 0) {
            $orderGrowth = 100; // Show 100% growth when there's current data but no previous data
        } else {
            $orderGrowth = 0; // No data in either month
        }

        if ($lastMonthRevenue > 0) {
            $revenueGrowth = round(((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100), 1);
        } elseif ($currentMonthRevenue > 0) {
            $revenueGrowth = 100; // Show 100% growth when there's current data but no previous data
        } else {
            $revenueGrowth = 0; // No data in either month
        }



        // Top Performing Designers (based on total orders count including both systems)
        $topDesigners = Designer::with('user')
            ->where('subscription_status', 'active')
            ->get()
            ->map(function ($designer) {
                // Calculate total orders including both direct orders and order items
                $totalOrders = Order::where(function ($q) use ($designer) {
                    $q->whereHas('rachma', function ($subQ) use ($designer) {
                        $subQ->where('designer_id', $designer->id);
                    })
                    ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                        $subQ->where('designer_id', $designer->id);
                    });
                })->where('status', 'completed')->count();

                $designer->orders_count = $totalOrders;
                return $designer;
            })
            ->sortByDesc('orders_count')
            ->take(5)
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalDesigners' => $totalDesigners,
                'activeDesigners' => $activeDesigners,
                'totalRachmat' => $totalRachmat,
                'activeRachmat' => $activeRachmat,
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'currentMonthOrders' => $currentMonthOrders,
                'lastMonthOrders' => $lastMonthOrders,
                'currentMonthRevenue' => $currentMonthRevenue,
                'lastMonthRevenue' => $lastMonthRevenue,
                'orderGrowth' => $orderGrowth,
                'revenueGrowth' => $revenueGrowth,
            ],
            'recentOrders' => $recentOrders,
            'pendingSubscriptions' => $pendingSubscriptions,
            'revenueData' => $revenueData,
            'topDesigners' => $topDesigners,
        ]);
    }
}
