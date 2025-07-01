<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminPaymentInfoRequest;
use App\Http\Requests\Admin\UpdateAdminPaymentInfoRequest;
use App\Models\AdminPaymentInfo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPaymentInfoController extends Controller
{
    /**
     * Display a listing of admin payment info.
     */
    public function index(Request $request)
    {
        $query = AdminPaymentInfo::orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        $paymentInfos = $query->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => AdminPaymentInfo::count(),
        ];

        return Inertia::render('Admin/PaymentInfo/Index', [
            'paymentInfos' => $paymentInfos,
            'filters' => $request->only(['search']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new payment info.
     */
    public function create()
    {
        return Inertia::render('Admin/PaymentInfo/Create');
    }

    /**
     * Store a newly created payment info.
     */
    public function store(StoreAdminPaymentInfoRequest $request)
    {
        try {
            $paymentInfo = AdminPaymentInfo::create($request->validated());

            return redirect()
                ->route('admin.payment-info.index')
                ->with('success', 'تم إنشاء معلومات الدفع بنجاح');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'فشل في إنشاء معلومات الدفع: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment info.
     */
    public function show(AdminPaymentInfo $paymentInfo)
    {
        return Inertia::render('Admin/PaymentInfo/Show', [
            'paymentInfo' => $paymentInfo,
        ]);
    }

    /**
     * Show the form for editing the specified payment info.
     */
    public function edit(AdminPaymentInfo $paymentInfo)
    {
        return Inertia::render('Admin/PaymentInfo/Edit', [
            'paymentInfo' => $paymentInfo,
        ]);
    }

    /**
     * Update the specified payment info.
     */
    public function update(UpdateAdminPaymentInfoRequest $request, AdminPaymentInfo $paymentInfo)
    {
        try {
            $paymentInfo->update($request->validated());

            return redirect()
                ->route('admin.payment-info.index')
                ->with('success', 'تم تحديث معلومات الدفع بنجاح');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'فشل في تحديث معلومات الدفع: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified payment info.
     */
    public function destroy(AdminPaymentInfo $paymentInfo)
    {
        try {
            $paymentInfo->delete();

            return redirect()
                ->route('admin.payment-info.index')
                ->with('success', 'تم حذف معلومات الدفع بنجاح');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'فشل في حذف معلومات الدفع: ' . $e->getMessage());
        }
    }
}
