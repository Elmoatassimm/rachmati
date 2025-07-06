<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class EnsureDesignerSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $gracePeriodDays  Days to allow access after subscription expiry
     * @param  string|null  $redirectRoute  Custom redirect route
     */
    public function handle(Request $request, Closure $next, ?string $gracePeriodDays = null, ?string $redirectRoute = null): Response
    {
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'يجب تسجيل الدخول أولاً');
        }

        // Check if user is a designer
        if (!$user->isDesigner()) {
            return redirect()->route('dashboard')
                ->with('error', 'هذه الصفحة مخصصة للمصممين فقط');
        }

        // Get designer profile
        $designer = $user->designer;
        
        // Check if designer profile exists
        if (!$designer) {
            return redirect()->route('dashboard')
                ->with('error', 'لم يتم العثور على ملف المصمم');
        }

        // Update subscription status based on end date
        $this->updateSubscriptionStatus($designer);

        // Check subscription status with grace period
        if (!$this->hasValidSubscription($designer, $gracePeriodDays)) {
            return $this->handleInvalidSubscription($designer, $gracePeriodDays, $redirectRoute, $request, $next);
        }

        // Add warning for near-expiry subscriptions
        if ($designer->subscription_status === 'active' && $designer->subscription_end_date) {
            $endDate = Carbon::parse($designer->subscription_end_date);
            $daysLeft = Carbon::now()->diffInDays($endDate);

            if ($daysLeft <= 7) {
                session()->flash('warning', "اشتراكك سينتهي خلال {$daysLeft} أيام");
            }
        }

        return $next($request);
    }

    /**
     * Update subscription status based on end date
     */
    private function updateSubscriptionStatus($designer): void
    {
        // Skip if no end date or status is pending
        if (!$designer->subscription_end_date || $designer->subscription_status === 'pending') {
            return;
        }

        $now = Carbon::now();
        $endDate = Carbon::parse($designer->subscription_end_date);

        if ($now->gt($endDate)) {
            // Subscription has expired
            if ($designer->subscription_status !== 'expired') {
                $designer->subscription_status = 'expired';
                $designer->save();
            }
        } elseif ($designer->subscription_status !== 'active') {
            // Active subscription
            $designer->subscription_status = 'active';
            $designer->save();
        }
    }

    /**
     * Check if designer has valid subscription considering grace period
     */
    private function hasValidSubscription($designer, ?string $gracePeriodDays): bool
    {
        // If subscription is active
        if ($designer->subscription_status === 'active') {
            return true;
        }

        // If subscription is pending
        if ($designer->subscription_status === 'pending') {
            return false;
        }

        // If grace period is specified and subscription recently expired
        if ($gracePeriodDays && $designer->subscription_status === 'expired' && $designer->subscription_end_date) {
            $gracePeriod = (int) $gracePeriodDays;
            $graceEndDate = Carbon::parse($designer->subscription_end_date)->addDays($gracePeriod);
            
            return Carbon::now()->lte($graceEndDate);
        }

        return false;
    }

    /**
     * Handle invalid subscription by redirecting to appropriate page
     */
    private function handleInvalidSubscription($designer, ?string $gracePeriodDays, ?string $redirectRoute, Request $request, Closure $next)
    {
        if ($designer->subscription_status === 'pending') {
            return redirect()->route('designer.subscription.pending')
                ->with('info', 'اشتراكك قيد المراجعة. يرجى انتظار موافقة الإدارة');
        }

        if ($designer->subscription_status === 'expired') {
            // Check if within grace period
            if ($gracePeriodDays && $designer->subscription_end_date) {
                $gracePeriod = (int) $gracePeriodDays;
                $graceEndDate = Carbon::parse($designer->subscription_end_date)->addDays($gracePeriod);
                
                if (Carbon::now()->lte($graceEndDate)) {
                    $remainingDays = Carbon::now()->diffInDays($graceEndDate);
                    session()->flash('warning', "انتهت صلاحية اشتراكك. يمكنك الوصول لهذه الصفحة لمدة {$remainingDays} أيام أخرى");
                    return $next($request);
                }
            }

            // If redirect route is specified, use it
            if ($redirectRoute) {
                return redirect()->route($redirectRoute);
            }

            return redirect()->route('designer.subscription-requests.create')
                ->with('error', 'انتهت صلاحية اشتراكك. يرجى تجديد الاشتراك للمتابعة');
        }

        return redirect()->route('designer.subscription-requests.create')
            ->with('error', 'يجب الاشتراك للوصول لهذه الصفحة');
    }
} 