<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_months',
        'price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the designers that have used this pricing plan.
     */
    public function designers(): HasMany
    {
        return $this->hasMany(Designer::class, 'pricing_plan_id');
    }

    /**
     * Scope a query to only include active pricing plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive pricing plans.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, '.', ',') . ' دج';
    }

    /**
     * Get duration text in Arabic.
     */
    public function getDurationTextAttribute(): string
    {
        if ($this->duration_months == 1) {
            return 'شهر واحد';
        } elseif ($this->duration_months == 2) {
            return 'شهران';
        } elseif ($this->duration_months <= 10) {
            return $this->duration_months . ' أشهر';
        } else {
            return $this->duration_months . ' شهر';
        }
    }
}
