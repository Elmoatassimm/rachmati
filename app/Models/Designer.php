<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Designer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'store_description',
        'subscription_status',
        'subscription_start_date',
        'subscription_end_date',
        'payment_proof_path',
        'earnings',
        'paid_earnings',
        'subscription_price',
        'pricing_plan_id',
    ];

    protected $casts = [
        'subscription_start_date' => 'date',
        'subscription_end_date' => 'date',
        'earnings' => 'decimal:2',
        'paid_earnings' => 'decimal:2',
        'subscription_price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the designer profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the rachmat for the designer.
     */
    public function rachmat(): HasMany
    {
        return $this->hasMany(Rachma::class);
    }

    /**
     * Get the pricing plan for the designer.
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class);
    }

    /**
     * Get the social media links for the designer.
     */
    public function socialMedia(): HasMany
    {
        return $this->hasMany(DesignerSocialMedia::class);
    }

    /**
     * Get the subscription requests for the designer.
     */
    public function subscriptionRequests(): HasMany
    {
        return $this->hasMany(SubscriptionRequest::class);
    }

    /**
     * Get the ratings for the designer's store.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'target_id')->where('target_type', 'store');
    }

    /**
     * Get the comments for the designer's store.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'target_id')->where('target_type', 'store');
    }

    /**
     * Check if subscription is active
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active' && 
               $this->subscription_end_date && 
               $this->subscription_end_date->isFuture();
    }

    /**
     * Check if subscription is expired
     */
    public function isSubscriptionExpired(): bool
    {
        return $this->subscription_end_date && $this->subscription_end_date->isPast();
    }

    /**
     * Get unpaid earnings
     */
    public function getUnpaidEarningsAttribute(): float
    {
        return $this->earnings - $this->paid_earnings;
    }

    /**
     * Calculate average rating
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    /**
     * Get total sales count (including both legacy and new order systems)
     */
    public function getTotalSalesAttribute(): int
    {
        // If total_sales is already set as an attribute (from controller calculation), use it
        if (array_key_exists('total_sales', $this->attributes)) {
            return (int) $this->attributes['total_sales'];
        }

        // Otherwise, calculate it dynamically (including both single and multi-item orders)
        return \App\Models\Order::where(function ($q) {
            $q->whereHas('rachma', function ($subQ) {
                $subQ->where('designer_id', $this->id);
            })
            ->orWhereHas('orderItems.rachma', function ($subQ) {
                $subQ->where('designer_id', $this->id);
            });
        })->where('status', 'completed')->count();
    }

    /**
     * Check if designer is active and has valid subscription
     */
    public function isActive(): bool
    {
        return $this->subscription_status === 'active' &&
               (!$this->subscription_end_date || $this->subscription_end_date > now());
    }
}
