<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Designer;
use App\Models\Order;
use App\Models\Rating;
use Carbon\Carbon;

class DashboardController extends Controller
{


    public function index()
    {
        $user = Auth::user();
        $designer = $user->designer;

        if (!$designer) {
            return redirect()->route('designer.setup');
        }

        // Basic Statistics
        $totalRachmat = $designer->rachmat()->count();
        $activeRachmat = $designer->rachmat()->count(); // All rachmat are considered active now
        $totalSales = $designer->rachmat()->withCount('orders')->get()->sum('orders_count');
        $totalEarnings = $designer->paid_earnings + $designer->unpaid_earnings;
        $unpaidEarnings = $designer->unpaid_earnings;

        // Recent Orders
        $recentOrders = Order::whereHas('rachma', function ($query) use ($designer) {
            $query->where('designer_id', $designer->id);
        })
        ->with(['client', 'rachma'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        // Monthly Sales Data (last 6 months)
        $monthlySales = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $sales = Order::whereHas('rachma', function ($query) use ($designer) {
                $query->where('designer_id', $designer->id);
            })
            ->where('status', 'completed')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count();

            $monthlySales[] = [
                'month' => $month->format('M Y'),
                'sales' => $sales,
            ];
        }

        // Top Performing Rachmat
        $topRachmat = $designer->rachmat()
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();

        // Recent Ratings
        $recentRatings = Rating::where('target_type', 'rachma')
            ->whereIn('target_id', $designer->rachmat()->pluck('id'))
            ->with(['user', 'ratable'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Average Rating
        $averageRating = Rating::where('target_type', 'rachma')
            ->whereIn('target_id', $designer->rachmat()->pluck('id'))
            ->avg('rating') ?? 0;

        return Inertia::render('Designer/Dashboard', [
            'designer' => $designer,
            'stats' => [
                'totalRachmat' => $totalRachmat,
                'activeRachmat' => $activeRachmat,
                'totalSales' => $totalSales,
                'totalEarnings' => $totalEarnings,
                'unpaidEarnings' => $unpaidEarnings,
                'averageRating' => round($averageRating, 1),
            ],
            'recentOrders' => $recentOrders,
            'monthlySales' => $monthlySales,
            'topRachmat' => $topRachmat,
            'recentRatings' => $recentRatings,
        ]);
    }

    /**
     * Show designer setup form
     */
    public function setup()
    {
        $user = Auth::user();
        
        if ($user->designer) {
            return redirect()->route('designer.dashboard');
        }

        return Inertia::render('Designer/Setup');
    }

    /**
     * Store designer setup
     */
    public function storeSetup(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255|unique:designers',
            'store_description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'payment_method' => 'required|in:ccp,baridi_mob',
            'payment_details' => 'required|string',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        // Store payment proof
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        $designer = Designer::create([
            'user_id' => $user->id,
            'store_name' => $request->store_name,
            'store_description' => $request->store_description,
            'phone' => $request->phone,
            'address' => $request->address,
            'payment_method' => $request->payment_method,
            'payment_details' => $request->payment_details,
            'payment_proof_path' => $paymentProofPath,
            'subscription_status' => 'pending',
        ]);

        return redirect()->route('designer.dashboard')
            ->with('success', 'تم إرسال طلب الاشتراك بنجاح. سيتم مراجعته من قبل الإدارة وإشعارك قريباً');
    }

    /**
     * Show analytics page with comprehensive data
     */
    public function analytics()
    {
        $user = Auth::user();
        $designer = $user->designer;

        if (!$designer) {
            return redirect()->route('designer.setup');
        }

        // Basic Statistics
        $totalRachmat = $designer->rachmat()->count();
        $activeRachmat = $designer->rachmat()->count(); // All rachmat are considered active now
        $totalSales = $designer->rachmat()->withCount('orders')->get()->sum('orders_count');
        $totalEarnings = $designer->paid_earnings + $designer->unpaid_earnings;
        $unpaidEarnings = $designer->unpaid_earnings;

        // Current month sales and revenue
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $currentMonthSales = Order::whereHas('rachma', function ($query) use ($designer) {
            $query->where('designer_id', $designer->id);
        })
        ->where('status', 'completed')
        ->whereYear('created_at', $currentMonth->year)
        ->whereMonth('created_at', $currentMonth->month)
        ->count();

        $lastMonthSales = Order::whereHas('rachma', function ($query) use ($designer) {
            $query->where('designer_id', $designer->id);
        })
        ->where('status', 'completed')
        ->whereYear('created_at', $lastMonth->year)
        ->whereMonth('created_at', $lastMonth->month)
        ->count();

        $currentMonthRevenue = Order::whereHas('rachma', function ($query) use ($designer) {
            $query->where('designer_id', $designer->id);
        })
        ->where('status', 'completed')
        ->whereYear('created_at', $currentMonth->year)
        ->whereMonth('created_at', $currentMonth->month)
        ->sum('amount');

        $lastMonthRevenue = Order::whereHas('rachma', function ($query) use ($designer) {
            $query->where('designer_id', $designer->id);
        })
        ->where('status', 'completed')
        ->whereYear('created_at', $lastMonth->year)
        ->whereMonth('created_at', $lastMonth->month)
        ->sum('amount');

        // Recent Orders
        $recentOrders = Order::whereHas('rachma', function ($query) use ($designer) {
            $query->where('designer_id', $designer->id);
        })
        ->with(['client', 'rachma'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        // Monthly Sales Data (last 6 months)
        $monthlySales = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $sales = Order::whereHas('rachma', function ($query) use ($designer) {
                $query->where('designer_id', $designer->id);
            })
            ->where('status', 'completed')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count();

            $revenue = Order::whereHas('rachma', function ($query) use ($designer) {
                $query->where('designer_id', $designer->id);
            })
            ->where('status', 'completed')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->sum('amount');

            $monthlySales[] = [
                'month' => $month->format('M Y'),
                'sales' => $sales,
                'revenue' => $revenue,
            ];
        }

        // Top Performing Rachmat
        $topRachmat = $designer->rachmat()
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($rachma) {
                $rachmaData = $rachma->toArray();
                $rachmaData['preview_image_urls'] = $rachma->preview_image_urls;
                return $rachmaData;
            });

        // Average Rating
        $averageRating = Rating::where('target_type', 'rachma')
            ->whereIn('target_id', $designer->rachmat()->pluck('id'))
            ->avg('rating') ?? 0;

        return Inertia::render('Designer/Analytics', [
            'designer' => $designer,
            'stats' => [
                'totalRachmat' => $totalRachmat,
                'activeRachmat' => $activeRachmat,
                'totalSales' => $totalSales,
                'totalEarnings' => $totalEarnings,
                'unpaidEarnings' => $unpaidEarnings,
                'averageRating' => round($averageRating, 1),
                'currentMonthSales' => $currentMonthSales,
                'lastMonthSales' => $lastMonthSales,
                'currentMonthRevenue' => $currentMonthRevenue,
                'lastMonthRevenue' => $lastMonthRevenue,
            ],
            'recentOrders' => $recentOrders,
            'monthlySales' => $monthlySales,
            'topRachmat' => $topRachmat,
        ]);
    }

    /**
     * Show subscription renewal form
     */
    public function renewSubscription()
    {
        $designer = Auth::user()->designer;

        return Inertia::render('Designer/RenewSubscription', [
            'designer' => $designer,
        ]);
    }

    /**
     * Show subscription request form
     */
    public function subscriptionRequest()
    {
        $user = Auth::user();
        $designer = $user->designer;
        
        // Get available pricing plans
        $pricingPlans = \App\Models\PricingPlan::active()->orderBy('duration_months')->get();

        return Inertia::render('Designer/SubscriptionRequest', [
            'designer' => $designer,
            'pricingPlans' => $pricingPlans,
            'user' => $user,
        ]);
    }

    /**
     * Store subscription request
     */
    public function storeSubscriptionRequest(Request $request)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:24',
            'pricing_plan_id' => 'nullable|exists:pricing_plans,id',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'store_name' => 'required_without:designer_exists|string|max:255',
            'store_description' => 'nullable|string',
        ]);

        $user = Auth::user();
        $designer = $user->designer;

        // Store payment proof
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        if (!$designer) {
            // Create new designer profile
            $designer = Designer::create([
                'user_id' => $user->id,
                'store_name' => $request->store_name,
                'store_description' => $request->store_description,
                'subscription_status' => 'pending',
                'payment_proof_path' => $paymentProofPath,
                'pricing_plan_id' => $request->pricing_plan_id,
            ]);
        } else {
            // Update existing designer
            $updateData = [
                'subscription_status' => 'pending',
                'payment_proof_path' => $paymentProofPath,
                'pricing_plan_id' => $request->pricing_plan_id,
            ];

            if ($request->store_name) {
                $updateData['store_name'] = $request->store_name;
            }
            if ($request->store_description) {
                $updateData['store_description'] = $request->store_description;
            }

            $designer->update($updateData);
        }

        return redirect()->route('designer.subscription.pending')
            ->with('success', 'تم إرسال طلب الاشتراك بنجاح. سيتم مراجعته من قبل الإدارة قريباً');
    }

    /**
     * Show subscription pending page
     */
    public function subscriptionPending()
    {
        $user = Auth::user();
        $designer = $user->designer;

        if (!$designer || $designer->subscription_status !== 'pending') {
            return redirect()->route('designer.dashboard');
        }

        return Inertia::render('Designer/SubscriptionPending', [
            'designer' => $designer->load('pricingPlan'),
        ]);
    }

    /**
     * Store subscription renewal
     */
    public function storeRenewal(Request $request)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $designer = Auth::user()->designer;

        // Store payment proof
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        $designer->update([
            'payment_proof_path' => $paymentProofPath,
            'subscription_status' => 'pending',
        ]);

        return redirect()->route('designer.dashboard')
            ->with('success', 'تم إرسال طلب تجديد الاشتراك بنجاح. سيتم مراجعته من قبل الإدارة قريباً');
    }
}
