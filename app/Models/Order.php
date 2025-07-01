<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'rachma_id',
        'amount',
        'payment_method',
        'payment_proof_path',
        'status',
        'confirmed_at',
        'file_sent_at',
        'admin_notes',
        'rejection_reason',
        'rejected_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'file_sent_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the payment proof URL
     */
    public function getPaymentProofUrlAttribute(): ?string
    {
        if (!$this->payment_proof_path) {
            return null;
        }

        // If it's already a full URL, return as is
        if (str_starts_with($this->payment_proof_path, 'http')) {
            return $this->payment_proof_path;
        }

        // If it starts with storage/, return as is (already has storage prefix)
        if (str_starts_with($this->payment_proof_path, 'storage/')) {
            return asset($this->payment_proof_path);
        }

        // If it's a public path, construct the storage URL
        if (str_starts_with($this->payment_proof_path, 'public/')) {
            return asset('storage/' . str_replace('public/', '', $this->payment_proof_path));
        }

        // Otherwise, assume it's in payment_proofs directory
        return asset('storage/payment_proofs/' . basename($this->payment_proof_path));
    }

    /**
     * Get the available order statuses.
     */
    public static function getAvailableStatuses(): array
    {
        return ['pending', 'completed', 'rejected'];
    }

    /**
     * Get the client that owns the order.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the rachma that belongs to the order.
     */
    public function rachma(): BelongsTo
    {
        return $this->belongsTo(Rachma::class);
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }



    /**
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Complete the order
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'confirmed_at' => now(), // Keep for backward compatibility
            'file_sent_at' => now(), // Keep for backward compatibility
        ]);
    }

    /**
     * Reject the order
     */
    public function reject($rejectionReason = null, $adminNotes = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
            'rejected_at' => now(),
            'admin_notes' => $adminNotes,
        ]);
    }

    /**
     * Reopen a rejected order (set back to pending)
     */
    public function reopen()
    {
        $this->update([
            'status' => 'pending',
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);
    }



    /**
     * Scope to get orders by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
