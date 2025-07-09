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

        // Calculate total sales including both single and multi-item orders
        $totalSales = Order::where(function ($q) use ($designer) {
            // Orders with direct rachma_id belonging to this designer
            $q->whereHas('rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            })
            // OR orders with order items containing this designer's rachmat
            ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            });
        })->count();

        $totalEarnings = $designer->paid_earnings + $designer->unpaid_earnings;
        $unpaidEarnings = $designer->unpaid_earnings;

        // Recent Orders
        $recentOrders = Order::where(function ($q) use ($designer) {
            // Orders with direct rachma_id belonging to this designer
            $q->whereHas('rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            })
            // OR orders with order items containing this designer's rachmat
            ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            });
        })
        ->with(['client', 'rachma', 'orderItems.rachma'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        // Monthly Sales Data (last 6 months)
        $monthlySales = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $sales = Order::where(function ($q) use ($designer) {
                // Orders with direct rachma_id belonging to this designer
                $q->whereHas('rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                })
                // OR orders with order items containing this designer's rachmat
                ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                });
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

        // Top Performing Rachmat (including both direct orders and order items)
        $topRachmat = $designer->rachmat()
            ->withCount([
                'orders', // Direct orders (legacy)
                'orderItems' // Orders through order_items table
            ])
            ->get()
            ->map(function ($rachma) {
                $rachmaData = $rachma->toArray();
                $rachmaData['preview_image_urls'] = $rachma->preview_image_urls;
                // Calculate total orders count (direct + order items)
                $rachmaData['total_orders_count'] = $rachma->orders_count + $rachma->order_items_count;
                return $rachmaData;
            })
            ->sortByDesc('total_orders_count')
            ->take(5)
            ->values();

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
                'totalEarnings' => (float) $totalEarnings,
                'unpaidEarnings' => (float) $unpaidEarnings,
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

        // Calculate total sales including both single and multi-item orders
        $totalSales = Order::where(function ($q) use ($designer) {
            // Orders with direct rachma_id belonging to this designer
            $q->whereHas('rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            })
            // OR orders with order items containing this designer's rachmat
            ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            });
        })->count();

        $totalEarnings = $designer->paid_earnings + $designer->unpaid_earnings;
        $unpaidEarnings = $designer->unpaid_earnings;

        // Current month sales and revenue
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $currentMonthSales = Order::where(function ($q) use ($designer) {
            // Orders with direct rachma_id belonging to this designer
            $q->whereHas('rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            })
            // OR orders with order items containing this designer's rachmat
            ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            });
        })
        ->where('status', 'completed')
        ->whereYear('created_at', $currentMonth->year)
        ->whereMonth('created_at', $currentMonth->month)
        ->count();

        $lastMonthSales = Order::where(function ($q) use ($designer) {
            // Orders with direct rachma_id belonging to this designer
            $q->whereHas('rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            })
            // OR orders with order items containing this designer's rachmat
            ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            });
        })
        ->where('status', 'completed')
        ->whereYear('created_at', $lastMonth->year)
        ->whereMonth('created_at', $lastMonth->month)
        ->count();

        $currentMonthRevenue = Order::where(function ($q) use ($designer) {
            // Orders with direct rachma_id belonging to this designer
            $q->whereHas('rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            })
            // OR orders with order items containing this designer's rachmat
            ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            });
        })
        ->where('status', 'completed')
        ->whereYear('created_at', $currentMonth->year)
        ->whereMonth('created_at', $currentMonth->month)
        ->sum('amount');

        $lastMonthRevenue = Order::where(function ($q) use ($designer) {
            // Orders with direct rachma_id belonging to this designer
            $q->whereHas('rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            })
            // OR orders with order items containing this designer's rachmat
            ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            });
        })
        ->where('status', 'completed')
        ->whereYear('created_at', $lastMonth->year)
        ->whereMonth('created_at', $lastMonth->month)
        ->sum('amount');

        // Calculate growth percentages with better logic
        if ($lastMonthSales > 0) {
            $salesGrowth = round((($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1);
        } elseif ($currentMonthSales > 0) {
            $salesGrowth = 100; // Show 100% growth when there's current data but no previous data
        } else {
            $salesGrowth = 0; // No data in either month
        }

        if ($lastMonthRevenue > 0) {
            $revenueGrowth = round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1);
        } elseif ($currentMonthRevenue > 0) {
            $revenueGrowth = 100; // Show 100% growth when there's current data but no previous data
        } else {
            $revenueGrowth = 0; // No data in either month
        }

        // Debug: Log the values for testing
        \Log::info('Analytics Debug', [
            'currentMonthSales' => $currentMonthSales,
            'lastMonthSales' => $lastMonthSales,
            'salesGrowth' => $salesGrowth,
            'currentMonthRevenue' => $currentMonthRevenue,
            'lastMonthRevenue' => $lastMonthRevenue,
            'revenueGrowth' => $revenueGrowth,
        ]);

        // Recent Orders
        $recentOrders = Order::where(function ($q) use ($designer) {
            // Orders with direct rachma_id belonging to this designer
            $q->whereHas('rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            })
            // OR orders with order items containing this designer's rachmat
            ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            });
        })
        ->with(['client', 'rachma', 'orderItems.rachma'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        // Monthly Sales Data (last 6 months)
        $monthlySales = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $sales = Order::where(function ($q) use ($designer) {
                // Orders with direct rachma_id belonging to this designer
                $q->whereHas('rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                })
                // OR orders with order items containing this designer's rachmat
                ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                });
            })
            ->where('status', 'completed')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count();

            $revenue = Order::where(function ($q) use ($designer) {
                // Orders with direct rachma_id belonging to this designer
                $q->whereHas('rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                })
                // OR orders with order items containing this designer's rachmat
                ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                });
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

        // Top Performing Rachmat (including both direct orders and order items)
        $topRachmat = $designer->rachmat()
            ->withCount([
                'orders', // Direct orders (legacy)
                'orderItems' // Orders through order_items table
            ])
            ->get()
            ->map(function ($rachma) {
                $rachmaData = $rachma->toArray();
                $rachmaData['preview_image_urls'] = $rachma->preview_image_urls;
                // Calculate total orders count (direct + order items)
                $rachmaData['total_orders_count'] = $rachma->orders_count + $rachma->order_items_count;
                return $rachmaData;
            })
            ->sortByDesc('total_orders_count')
            ->take(5)
            ->values();

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
                'totalEarnings' => (float) $totalEarnings,
                'unpaidEarnings' => (float) $unpaidEarnings,
                'averageRating' => round($averageRating, 1),
                'currentMonthSales' => $currentMonthSales,
                'lastMonthSales' => $lastMonthSales,
                'currentMonthRevenue' => $currentMonthRevenue,
                'lastMonthRevenue' => $lastMonthRevenue,
                'salesGrowth' => $salesGrowth,
                'revenueGrowth' => $revenueGrowth,
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
