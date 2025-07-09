<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\TelegramService;

class DesignerController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Display a listing of designers
     */
    public function index(Request $request)
    {
        $query = Designer::with(['user', 'rachmat'])
            ->withCount('rachmat')
            ->selectRaw('designers.*, COALESCE(earnings, 0) - COALESCE(paid_earnings, 0) as unpaid_earnings');

        // Filters
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('subscription_status', 'active');
            } elseif ($request->status === 'pending') {
                $query->where('subscription_status', 'pending');
            } elseif ($request->status === 'expired') {
                $query->where('subscription_status', 'expired')
                      ->orWhere('subscription_end_date', '<', now());
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $designers = $query->paginate(15);

        // Calculate total sales for each designer (including both legacy and new order systems)
        $transformedCollection = $designers->getCollection()->map(function ($designer) {
            // Calculate total sales including both single and multi-item orders
            $totalSales = Order::where(function ($q) use ($designer) {
                $q->whereHas('rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                })
                ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                    $subQ->where('designer_id', $designer->id);
                });
            })->where('status', 'completed')->count();

            $designer->total_sales = $totalSales;
            return $designer;
        });

        $designers->setCollection($transformedCollection);

        // Calculate statistics
        $stats = [
            'total' => Designer::count(),
            'active' => Designer::where('subscription_status', 'active')->count(),
            'pending' => Designer::where('subscription_status', 'pending')->count(),
            'totalRachmat' => \App\Models\Rachma::count(),
            'totalOrders' => Order::count(),
        ];

        return Inertia::render('Admin/Designers/Index', [
            'designers' => $designers,
            'filters' => $request->only(['status', 'search']),
            'stats' => $stats,
        ]);
    }

    /**
     * Display the specified designer
     */
    public function show(Designer $designer)
    {
        $designer->load([
            'user',
            'socialMedia',
            'pricingPlan',
            'rachmat' => function ($query) {
                $query->with('categories')->orderBy('created_at', 'desc');
            }
        ]);

        // Calculate sales count for each rachma (including both legacy and new order systems)
        $rachmatWithSales = $designer->rachmat->map(function ($rachma) {
            // Calculate total sales for this specific rachma including both single and multi-item orders
            $totalSales = Order::where(function ($q) use ($rachma) {
                $q->where('rachma_id', $rachma->id) // Direct orders (legacy)
                  ->orWhereHas('orderItems', function ($subQ) use ($rachma) {
                      $subQ->where('rachma_id', $rachma->id); // Order items (new system)
                  });
            })->where('status', 'completed')->count();

            $rachma->orders_count = $totalSales;
            return $rachma;
        });

        // Calculate earnings and statistics (support both legacy and new order systems)
        // Calculate total earnings from both direct orders and order items

        // For direct orders (legacy system)
        $directOrdersEarnings = Order::whereHas('rachma', function ($q) use ($designer) {
            $q->where('designer_id', $designer->id);
        })->where('status', 'completed')->sum('amount');

        // For multi-item orders (new system) - sum only this designer's items
        $orderItemsEarnings = \App\Models\OrderItem::whereHas('rachma', function ($q) use ($designer) {
            $q->where('designer_id', $designer->id);
        })->whereHas('order', function ($q) {
            $q->where('status', 'completed');
        })->sum('price');

        $totalEarnings = $directOrdersEarnings + $orderItemsEarnings;

        // Update the designer's stored earnings if they don't match
        if ($designer->earnings != $totalEarnings) {
            $designer->update(['earnings' => $totalEarnings]);
            $designer->refresh();
        }

        $unpaidEarnings = (float) ($totalEarnings - $designer->paid_earnings);

        // Calculate total sales including both single and multi-item orders
        $totalSales = Order::where(function ($q) use ($designer) {
            $q->whereHas('rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            })
            ->orWhereHas('orderItems.rachma', function ($subQ) use ($designer) {
                $subQ->where('designer_id', $designer->id);
            });
        })->where('status', 'completed')->count();

        // Get active pricing plans for approval process
        $activePricingPlans = \App\Models\PricingPlan::active()->orderBy('duration_months')->get();

        // Debug: Log the calculated values
        \Log::info('Designer Show Stats', [
            'designer_id' => $designer->id,
            'totalEarnings' => $totalEarnings,
            'unpaidEarnings' => $unpaidEarnings,
            'totalSales' => $totalSales,
        ]);

        return Inertia::render('Admin/Designers/Show', [
            'designer' => $designer,
            'stats' => [
                'totalEarnings' => $totalEarnings,
                'unpaidEarnings' => $unpaidEarnings,
                'totalSales' => $totalSales,
            ],
            'rachmat' => $rachmatWithSales,
            'pricingPlans' => $activePricingPlans,
        ]);
    }

    /**
     * Approve designer subscription
     */
    public function approveSubscription(Request $request, Designer $designer)
    {
        $request->validate([
            'pricing_plan_id' => 'nullable|exists:pricing_plans,id',
            'duration_months' => 'required|integer|min:1|max:24',
        ]);

        $months = $request->duration_months;
        $pricingPlan = null;

        if ($request->pricing_plan_id) {
            $pricingPlan = \App\Models\PricingPlan::findOrFail($request->pricing_plan_id);
        }

        $startDate = now();
        $endDate = $startDate->copy()->addMonths($months);

        $updateData = [
            'subscription_status' => 'active',
            'subscription_start_date' => $startDate,
            'subscription_end_date' => $endDate,
        ];

        if ($pricingPlan) {
            $updateData['pricing_plan_id'] = $pricingPlan->id;
            $updateData['subscription_price'] = $pricingPlan->price;
        }

        $designer->update($updateData);

        // Send notification via Telegram if user has chat_id
        if ($designer->user->telegram_chat_id) {
            $message = "🎉 *تم تفعيل اشتراكك / Votre abonnement est activé*\n\n";
            $message .= "متجرك الآن نشط ويمكنك رفع الرشمات\n";
            $message .= "Votre boutique est maintenant active\n";
            $message .= "تاريخ انتهاء الاشتراك / Date d'expiration: " . $endDate->format('Y-m-d');

            $this->telegramService->sendNotification($designer->user->telegram_chat_id, $message);
        }

        $planInfo = $pricingPlan ? " بسعر {$pricingPlan->formatted_price}" : "";
        return redirect()->back()->with('success', "تم تفعيل اشتراك المصمم بنجاح لمدة {$months} أشهر{$planInfo}");
    }

    /**
     * Reject designer subscription
     */
    public function rejectSubscription(Request $request, Designer $designer)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $designer->update([
            'subscription_status' => 'expired',
        ]);

        // Send notification via Telegram if user has chat_id
        if ($designer->user->telegram_chat_id) {
            $message = "❌ *تم رفض اشتراكك / Votre abonnement a été rejeté*\n\n";
            if ($request->reason) {
                $message .= "السبب / Raison: " . $request->reason . "\n";
            }
            $message .= "يرجى التواصل مع الإدارة / Veuillez contacter l'administration";

            $this->telegramService->sendNotification($designer->user->telegram_chat_id, $message);
        }

        return redirect()->back()->with('success', 'تم رفض اشتراك المصمم وإشعاره بالسبب');
    }

    /**
     * Toggle designer status (now based on subscription status)
     */
    public function toggleStatus(Designer $designer)
    {
        $newStatus = $designer->subscription_status === 'active' ? 'expired' : 'active';
        
        $designer->update([
            'subscription_status' => $newStatus,
        ]);

        $message = $newStatus === 'active' 
            ? 'تم تفعيل حساب المصمم بنجاح! يمكنه الآن رفع الرشمات' 
            : 'تم إلغاء تفعيل حساب المصمم! لن يتمكن من رفع رشمات جديدة';
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Process designer earnings payment
     */
    public function payEarnings(Request $request, Designer $designer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = (float) $request->amount;
        $maxPayable = $designer->unpaid_earnings;

        if ($amount > $maxPayable) {
            return redirect()->back()->withErrors([
                'amount' => 'المبلغ أكبر من الأرباح المستحقة للمصمم'
            ]);
        }

        $designer->increment('paid_earnings', $amount);
        $designer->decrement('unpaid_earnings', $amount);

        // Send notification via Telegram if user has chat_id
        if ($designer->user->telegram_chat_id) {
            $message = "💰 *تم تحويل أرباحك / Vos gains ont été transférés*\n\n";
            $message .= "المبلغ / Montant: {$amount} DZD\n";
            if ($request->notes) {
                $message .= "ملاحظة / Note: " . $request->notes;
            }

            $this->telegramService->sendNotification($designer->user->telegram_chat_id, $message);
        }

        return redirect()->back()->with('success', 'تم تحويل مبلغ ' . $amount . ' دينار للمصمم بنجاح');
    }

    /**
     * Delete the specified designer
     */
    public function destroy(Designer $designer)
    {
        // Delete all associated rachmat and their files
        foreach ($designer->rachmat as $rachma) {
            // Delete files if they exist
            if ($rachma->image_path) {
                Storage::delete($rachma->image_path);
            }
            if ($rachma->file_path) {
                Storage::delete($rachma->file_path);
            }
            $rachma->delete();
        }

        // Delete social media links
        $designer->socialMedia()->delete();

        // Delete the designer
        $designer->delete();

        return redirect()->route('admin.designers.index')->with('success', 'تم حذف المصمم وجميع رشماته بنجاح');
    }

    /**
     * Activate designer subscription for specific months
     */
    public function activateSubscription(Request $request, Designer $designer)
    {
        // Log the received request for debugging
        Log::info('Activating subscription', [
            'designer_id' => $designer->id,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'months' => 'required|integer|min:1|max:24',
            'pricing_plan_id' => 'nullable|exists:pricing_plans,id',
        ]);

        $months = $request->months;
        $pricingPlan = null;

        if ($request->pricing_plan_id) {
            $pricingPlan = \App\Models\PricingPlan::findOrFail($request->pricing_plan_id);
        }

        // Calculate dates
        $startDate = now();
        $endDate = $startDate->copy()->addMonths($months);

        Log::info('Calculated subscription dates', [
            'months' => $months,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]);

        $updateData = [
            'subscription_status' => 'active',
            'subscription_start_date' => $startDate,
            'subscription_end_date' => $endDate,
        ];

        if ($pricingPlan) {
            $updateData['pricing_plan_id'] = $pricingPlan->id;
            $updateData['subscription_price'] = $pricingPlan->price;
        }

        $designer->update($updateData);

        return redirect()->back()->with('success', "تم تفعيل اشتراك المصمم لمدة {$months} شهر بنجاح");
    }

    /**
     * Deactivate designer subscription
     */
    public function deactivateSubscription(Designer $designer)
    {
        $designer->update([
            'subscription_status' => 'expired',
            'subscription_end_date' => now(),
        ]);

        return redirect()->back()->with('success', 'تم إلغاء تفعيل اشتراك المصمم بنجاح');
    }

    /**
     * Extend designer subscription by additional months
     */
    public function extendSubscription(Request $request, Designer $designer)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:12',
        ]);

        $months = $request->months;
        $currentEndDate = $designer->subscription_end_date ?: now();
        $newEndDate = $currentEndDate->copy()->addMonths($months);

        $designer->update([
            'subscription_end_date' => $newEndDate,
            'subscription_status' => 'active',
        ]);

        return redirect()->back()->with('success', "تم تمديد اشتراك المصمم لمدة {$months} شهر إضافي بنجاح");
    }

    /**
     * Show the form for editing designer paid earnings
     */
    public function editPaidEarnings(Designer $designer)
    {
        $designer->load(['user', 'rachmat']);

        // Calculate total earnings from actual completed sales (100% commission)
        // Include both direct orders and order items

        // For direct orders (legacy system)
        $directOrdersEarnings = Order::whereHas('rachma', function ($q) use ($designer) {
            $q->where('designer_id', $designer->id);
        })->where('status', 'completed')->sum('amount');

        // For multi-item orders (new system) - sum only this designer's items
        $orderItemsEarnings = \App\Models\OrderItem::whereHas('rachma', function ($q) use ($designer) {
            $q->where('designer_id', $designer->id);
        })->whereHas('order', function ($q) {
            $q->where('status', 'completed');
        })->sum('price');

        $totalEarnings = $directOrdersEarnings + $orderItemsEarnings;

        // Update the designer's total earnings if needed
        if ($designer->earnings != $totalEarnings) {
            $designer->update(['earnings' => $totalEarnings]);
            $designer->refresh();
        }

        $stats = [
            'totalEarnings' => $totalEarnings,
            'paidEarnings' => $designer->paid_earnings,
            'unpaidEarnings' => $totalEarnings - $designer->paid_earnings,
        ];

        return Inertia::render('Admin/Designers/EditPaidEarnings', [
            'designer' => $designer,
            'stats' => $stats,
        ]);
    }

    /**
     * Update designer paid earnings
     */
    public function updatePaidEarnings(Request $request, Designer $designer)
    {
        $request->validate([
            'paid_earnings' => [
                'required',
                'numeric',
                'min:0',
                'max:' . $designer->earnings,
            ],
            'admin_notes' => 'nullable|string|max:1000',
        ], [
            'paid_earnings.required' => 'مبلغ الأرباح المدفوعة مطلوب',
            'paid_earnings.numeric' => 'مبلغ الأرباح المدفوعة يجب أن يكون رقماً',
            'paid_earnings.min' => 'مبلغ الأرباح المدفوعة لا يمكن أن يكون أقل من صفر',
            'paid_earnings.max' => 'مبلغ الأرباح المدفوعة لا يمكن أن يتجاوز إجمالي الأرباح (' . number_format((float) $designer->earnings, 2) . ' دج)',
            'admin_notes.max' => 'ملاحظات الإدارة لا يجب أن تتجاوز 1000 حرف',
        ]);

        $oldPaidEarnings = $designer->paid_earnings;
        $newPaidEarnings = $request->paid_earnings;

        $designer->update([
            'paid_earnings' => $newPaidEarnings,
        ]);

        // Log the change for audit purposes
        Log::info('Designer paid earnings updated', [
            'designer_id' => $designer->id,
            'designer_name' => $designer->store_name,
            'old_paid_earnings' => $oldPaidEarnings,
            'new_paid_earnings' => $newPaidEarnings,
            'admin_id' => Auth::id(),
            'admin_notes' => $request->admin_notes,
        ]);

        $message = $newPaidEarnings > $oldPaidEarnings
            ? 'تم تحديث الأرباح المدفوعة بنجاح. تم إضافة ' . number_format($newPaidEarnings - $oldPaidEarnings, 2) . ' دج'
            : 'تم تحديث الأرباح المدفوعة بنجاح';

        return redirect()->route('admin.designers.show', $designer)->with('success', $message);
    }
}
