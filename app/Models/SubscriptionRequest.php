<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SubscriptionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'designer_id',
        'pricing_plan_id',
        'status',
        'notes',
        'payment_proof_path',
        'payment_proof_original_name',
        'payment_proof_size',
        'payment_proof_mime_type',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'subscription_price',
        'requested_start_date',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'requested_start_date' => 'date',
        'subscription_price' => 'decimal:2',
        'payment_proof_size' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'payment_proof_url',
        'formatted_file_size',
        'status_label',
        'has_payment_proof',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'معلق',
        self::STATUS_APPROVED => 'موافق عليه',
        self::STATUS_REJECTED => 'مرفوض',
    ];

    /**
     * Get the designer that owns the subscription request.
     */
    public function designer(): BelongsTo
    {
        return $this->belongsTo(Designer::class);
    }

    /**
     * Get the pricing plan for this request.
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class);
    }

    /**
     * Get the admin who reviewed this request.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get the status label in Arabic.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the payment proof URL.
     */
    public function getPaymentProofUrlAttribute(): ?string
    {
        if (!$this->payment_proof_path) {
            return null;
        }

        return Storage::url($this->payment_proof_path);
    }

    /**
     * Check if payment proof exists.
     */
    public function hasPaymentProof(): bool
    {
        return !empty($this->payment_proof_path) && Storage::exists($this->payment_proof_path);
    }

    /**
     * Get the has payment proof attribute.
     */
    public function getHasPaymentProofAttribute(): bool
    {
        return $this->hasPaymentProof();
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->payment_proof_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->payment_proof_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope for a specific designer.
     */
    public function scopeForDesigner($query, $designerId)
    {
        return $query->where('designer_id', $designerId);
    }
}
