<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSubscriptionRequestRequest;

use App\Models\SubscriptionRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SubscriptionRequestController extends Controller
{
    /**
     * Display a listing of all subscription requests.
     */
    public function index(Request $request)
    {
        $query = SubscriptionRequest::with(['designer.user', 'pricingPlan', 'reviewedBy'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $subscriptionRequests = $query->paginate(20);

        // Get statistics
        $statistics = [
            'total' => SubscriptionRequest::count(),
            'pending' => SubscriptionRequest::pending()->count(),
            'approved' => SubscriptionRequest::approved()->count(),
            'rejected' => SubscriptionRequest::rejected()->count(),
        ];

        return Inertia::render('Admin/SubscriptionRequests/Index', [
            'subscriptionRequests' => $subscriptionRequests,
            'statistics' => $statistics,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Display the specified subscription request.
     */
    public function show(SubscriptionRequest $subscriptionRequest)
    {
        $subscriptionRequest->load(['designer.user', 'pricingPlan', 'reviewedBy']);

        return Inertia::render('Admin/SubscriptionRequests/Show', [
            'subscriptionRequest' => $subscriptionRequest,
        ]);
    }

    /**
     * Update the specified subscription request (approve/reject).
     */
    public function update(UpdateSubscriptionRequestRequest $request, SubscriptionRequest $subscriptionRequest)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $subscriptionRequest) {
            // Update the subscription request
            $subscriptionRequest->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? null,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // If approved, update the designer's subscription
            if ($validated['status'] === SubscriptionRequest::STATUS_APPROVED) {
                $this->activateDesignerSubscription($subscriptionRequest);
            }
        });

        $statusMessage = match($validated['status']) {
            SubscriptionRequest::STATUS_APPROVED => 'تم قبول طلب الاشتراك وتفعيل اشتراك المصمم',
            SubscriptionRequest::STATUS_REJECTED => 'تم رفض طلب الاشتراك',
            default => 'تم تحديث حالة طلب الاشتراك'
        };

        return back()->with('success', $statusMessage);
    }

    /**
     * Bulk update subscription requests.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:subscription_requests,id',
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $subscriptionRequests = SubscriptionRequest::whereIn('id', $request->ids)->get();

        DB::transaction(function () use ($request, $subscriptionRequests) {
            foreach ($subscriptionRequests as $subscriptionRequest) {
                if ($subscriptionRequest->isPending()) {
                    $subscriptionRequest->update([
                        'status' => $request->status,
                        'admin_notes' => $request->admin_notes,
                        'reviewed_by' => Auth::id(),
                        'reviewed_at' => now(),
                    ]);

                    if ($request->status === SubscriptionRequest::STATUS_APPROVED) {
                        $this->activateDesignerSubscription($subscriptionRequest);
                    }
                }
            }
        });

        $count = count($request->ids);
        $action = $request->status === 'approved' ? 'قبول' : 'رفض';
        
        return back()->with('success', "تم {$action} {$count} طلب اشتراك بنجاح");
    }

    /**
     * Get subscription request statistics for dashboard.
     */
    public function statistics()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return response()->json([
            'total_requests' => SubscriptionRequest::count(),
            'pending_requests' => SubscriptionRequest::pending()->count(),
            'approved_today' => SubscriptionRequest::approved()
                ->whereDate('reviewed_at', $today)
                ->count(),
            'requests_this_month' => SubscriptionRequest::where('created_at', '>=', $thisMonth)
                ->count(),
        ]);
    }

    /**
     * Activate designer subscription after approval.
     */
    private function activateDesignerSubscription(SubscriptionRequest $subscriptionRequest): void
    {
        $designer = $subscriptionRequest->designer;
        $pricingPlan = $subscriptionRequest->pricingPlan;

        // Calculate subscription dates
        $startDate = Carbon::parse($subscriptionRequest->requested_start_date);
        $endDate = $startDate->copy()->addMonths($pricingPlan->duration_months);

        // Update designer subscription
        $designer->update([
            'subscription_status' => 'active',
            'subscription_start_date' => $startDate,
            'subscription_end_date' => $endDate,
            'subscription_price' => $subscriptionRequest->subscription_price,
            'pricing_plan_id' => $subscriptionRequest->pricing_plan_id,
            'payment_proof_path' => $subscriptionRequest->payment_proof_path,
        ]);
    }
}
