<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequestRequest;
use App\Models\Designer;
use App\Models\PricingPlan;
use App\Models\SubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SubscriptionRequestController extends Controller
{
    /**
     * Display a listing of the subscription requests for the authenticated designer.
     */
    public function index()
    {
        $designer = Auth::user()->designer;
        
        if (!$designer) {
            return redirect()->route('dashboard')->with('error', 'لم يتم العثور على ملف المصمم');
        }

        $subscriptionRequests = SubscriptionRequest::with(['pricingPlan', 'reviewedBy'])
            ->forDesigner($designer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Designer/SubscriptionRequests/Index', [
            'subscriptionRequests' => $subscriptionRequests,
        ]);
    }

    /**
     * Show the form for creating a new subscription request.
     */
    public function create()
    {
        $designer = Auth::user()->designer;
        
        if (!$designer) {
            return redirect()->route('dashboard')->with('error', 'لم يتم العثور على ملف المصمم');
        }

        $pricingPlans = PricingPlan::orderBy('price')->get();

        return Inertia::render('Designer/SubscriptionRequests/Create', [
            'pricingPlans' => $pricingPlans,
        ]);
    }

    /**
     * Store a newly created subscription request in storage.
     */
    public function store(StoreSubscriptionRequestRequest $request)
    {
        $designer = Auth::user()->designer;
        
        if (!$designer) {
            return back()->with('error', 'لم يتم العثور على ملف المصمم');
        }

        $validated = $request->validated();

        // Get pricing plan details
        $pricingPlan = PricingPlan::findOrFail($validated['pricing_plan_id']);

        // Handle payment proof upload
        $paymentProofData = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofData = $this->handlePaymentProofUpload($request->file('payment_proof'));
        }

        // Create subscription request
        $subscriptionRequest = SubscriptionRequest::create([
            'designer_id' => $designer->id,
            'pricing_plan_id' => $validated['pricing_plan_id'],
            'notes' => $validated['notes'] ?? null,
            'subscription_price' => $pricingPlan->price,
            'requested_start_date' => now(),
            'payment_proof_path' => $paymentProofData['path'] ?? null,
            'payment_proof_original_name' => $paymentProofData['original_name'] ?? null,
            'payment_proof_size' => $paymentProofData['size'] ?? null,
            'payment_proof_mime_type' => $paymentProofData['mime_type'] ?? null,
        ]);

        return redirect()->route('designer.subscription-requests.index')
            ->with('success', 'تم إرسال طلب الاشتراك بنجاح. سيتم مراجعته قريباً.');
    }

    /**
     * Display the specified subscription request.
     */
    public function show(SubscriptionRequest $subscriptionRequest)
    {
        $designer = Auth::user()->designer;
        
        if (!$designer || $subscriptionRequest->designer_id !== $designer->id) {
            abort(404);
        }

        $subscriptionRequest->load(['pricingPlan', 'reviewedBy']);

        return Inertia::render('Designer/SubscriptionRequests/Show', [
            'subscriptionRequest' => $subscriptionRequest,
        ]);
    }

    /**
     * Handle payment proof file upload.
     */
    private function handlePaymentProofUpload($file): array
    {
        // Generate a secure filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Store the file
        $path = $file->storeAs('subscription-requests/payment-proofs', $filename, 'public');

        return [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }
}
