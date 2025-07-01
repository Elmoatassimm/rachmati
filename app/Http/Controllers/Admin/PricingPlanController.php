<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PricingPlanController extends Controller
{
    /**
     * Display a listing of pricing plans.
     */
    public function index(Request $request)
    {
        $query = PricingPlan::query();

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $pricingPlans = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => PricingPlan::count(),
            'active' => PricingPlan::where('is_active', true)->count(),
            'inactive' => PricingPlan::where('is_active', false)->count(),
        ];

        return Inertia::render('Admin/PricingPlans/Index', [
            'pricingPlans' => $pricingPlans,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new pricing plan.
     */
    public function create()
    {
        return Inertia::render('Admin/PricingPlans/Create');
    }

    /**
     * Store a newly created pricing plan in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'duration_months' => 'required|integer|min:1|max:24',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        PricingPlan::create([
            'name' => $request->name,
            'duration_months' => $request->duration_months,
            'price' => $request->price,
            'description' => $request->description,
            'is_active' => true, // Default to active
        ]);

        return redirect()->route('admin.pricing-plans.index')
            ->with('success', 'تم إنشاء خطة التسعير بنجاح');
    }

    /**
     * Display the specified pricing plan.
     */
    public function show(PricingPlan $pricingPlan)
    {
        $pricingPlan->load(['designers.user']);

        // Calculate statistics for this pricing plan
        $stats = [
            'totalSubscriptions' => $pricingPlan->designers()->count(),
            'activeSubscriptions' => $pricingPlan->designers()->where('subscription_status', 'active')->count(),
            'totalRevenue' => $pricingPlan->designers()->sum('subscription_price'),
        ];

        return Inertia::render('Admin/PricingPlans/Show', [
            'pricingPlan' => $pricingPlan,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified pricing plan.
     */
    public function edit(PricingPlan $pricingPlan)
    {
        return Inertia::render('Admin/PricingPlans/Edit', [
            'pricingPlan' => $pricingPlan,
        ]);
    }

    /**
     * Update the specified pricing plan in storage.
     */
    public function update(Request $request, PricingPlan $pricingPlan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'duration_months' => 'required|integer|min:1|max:24',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $pricingPlan->update([
            'name' => $request->name,
            'duration_months' => $request->duration_months,
            'price' => $request->price,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.pricing-plans.index')
            ->with('success', 'تم تحديث خطة التسعير بنجاح');
    }

    /**
     * Toggle the active status of the specified pricing plan.
     */
    public function toggleStatus(PricingPlan $pricingPlan)
    {
        $pricingPlan->update([
            'is_active' => !$pricingPlan->is_active,
        ]);

        $message = $pricingPlan->is_active
            ? 'تم تفعيل خطة التسعير بنجاح'
            : 'تم إلغاء تفعيل خطة التسعير بنجاح';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove the specified pricing plan from storage.
     */
    public function destroy(PricingPlan $pricingPlan)
    {
        // Check if pricing plan has active subscriptions
        $activeSubscriptions = $pricingPlan->designers()->where('subscription_status', 'active')->count();
        
        if ($activeSubscriptions > 0) {
            return redirect()->back()->with([
                'error' => 'لا يمكن حذف خطة التسعير لأنها مرتبطة باشتراكات نشطة'
            ]);
        }

        $pricingPlan->delete();

        return redirect()->route('admin.pricing-plans.index')
            ->with('success', 'تم حذف خطة التسعير بنجاح');
    }
}
