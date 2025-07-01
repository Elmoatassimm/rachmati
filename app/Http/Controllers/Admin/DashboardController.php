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

        // Recent Activity
        $recentOrders = Order::with(['client', 'rachma.designer.user'])
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

        $currentMonthOrders = Order::where('created_at', '>=', $currentMonth)->count();
        $lastMonthOrders = Order::where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $currentMonth)
            ->count();

        $currentMonthRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $currentMonth)
            ->sum('amount');
        $lastMonthRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $currentMonth)
            ->sum('amount');

        // Top Performing Designers (based on orders count)
        $topDesigners = Designer::with('user')
            ->where('subscription_status', 'active')
            ->withCount(['rachmat as orders_count' => function($query) {
                $query->whereHas('orders');
            }])
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();

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
            ],
            'recentOrders' => $recentOrders,
            'pendingSubscriptions' => $pendingSubscriptions,
            'revenueData' => $revenueData,
            'topDesigners' => $topDesigners,
        ]);
    }
}
