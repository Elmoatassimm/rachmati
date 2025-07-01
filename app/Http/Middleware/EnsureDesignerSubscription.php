<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
            $fallbackRoute = $redirectRoute ?? 'designer.dashboard';
            return redirect()->route($fallbackRoute)
                ->with('error', 'لم يتم العثور على ملف المصمم');
        }

        // Check subscription status with grace period
        if (!$this->hasValidSubscription($designer, $gracePeriodDays)) {
            return $this->handleInvalidSubscription($designer, $gracePeriodDays, $redirectRoute);
        }

        return $next($request);
    }

    /**
     * Check if designer has valid subscription considering grace period
     */
    private function hasValidSubscription($designer, ?string $gracePeriodDays): bool
    {
        // If subscription is active and not expired
        if ($designer->hasActiveSubscription()) {
            return true;
        }

        // If grace period is specified and subscription recently expired
        if ($gracePeriodDays && $designer->subscription_status === 'expired' && $designer->subscription_end_date) {
            $gracePeriod = (int) $gracePeriodDays;
            $graceEndDate = $designer->subscription_end_date->addDays($gracePeriod);
            
            return now()->lte($graceEndDate);
        }

        return false;
    }

    /**
     * Get appropriate message based on subscription status
     */
    private function getSubscriptionMessage($designer, ?string $gracePeriodDays): string
    {
        return match($designer->subscription_status) {
            'pending' => 'اشتراكك قيد المراجعة. يرجى انتظار موافقة الإدارة',
            'expired' => $this->getExpiredMessage($designer, $gracePeriodDays),
            default => 'يجب تفعيل الاشتراك للوصول لهذه الصفحة'
        };
    }

    /**
     * Handle invalid subscription by redirecting to appropriate page
     */
    private function handleInvalidSubscription($designer, ?string $gracePeriodDays, ?string $redirectRoute)
    {
        return match($designer->subscription_status) {
            'pending' => redirect()->route('designer.subscription.pending'),
            'expired' => $this->handleExpiredSubscription($designer, $gracePeriodDays, $redirectRoute),
            default => redirect()->route('designer.subscription.request')
                ->with('info', 'يجب الاشتراك للوصول لهذه الصفحة')
        };
    }

    /**
     * Handle expired subscription with grace period consideration
     */
    private function handleExpiredSubscription($designer, ?string $gracePeriodDays, ?string $redirectRoute)
    {
        // Check if within grace period
        if ($gracePeriodDays && $designer->subscription_end_date) {
            $gracePeriod = (int) $gracePeriodDays;
            $graceEndDate = $designer->subscription_end_date->addDays($gracePeriod);
            
            if (now()->lte($graceEndDate)) {
                $remainingDays = now()->diffInDays($graceEndDate);
                $fallbackRoute = $redirectRoute ?? 'designer.dashboard';
                return redirect()->route($fallbackRoute)
                    ->with('warning', "انتهت صلاحية اشتراكك. يمكنك الوصول لهذه الصفحة لمدة {$remainingDays} أيام أخرى");
            }
        }

        return redirect()->route('designer.subscription.request')
            ->with('error', 'انتهت صلاحية اشتراكك. يرجى تجديد الاشتراك للمتابعة');
    }

    /**
     * Get expired subscription message
     */
    private function getExpiredMessage($designer, ?string $gracePeriodDays): string
    {
        if ($gracePeriodDays && $designer->subscription_end_date) {
            $gracePeriod = (int) $gracePeriodDays;
            $graceEndDate = $designer->subscription_end_date->addDays($gracePeriod);
            
            if (now()->lte($graceEndDate)) {
                $remainingDays = now()->diffInDays($graceEndDate);
                return "انتهت صلاحية اشتراكك. يمكنك الوصول لهذه الصفحة لمدة {$remainingDays} أيام أخرى";
            }
        }

        return 'انتهت صلاحية اشتراكك. يرجى تجديد الاشتراك للمتابعة';
    }
} 