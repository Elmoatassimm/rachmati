<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPaymentInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'ccp_number',
        'ccp_key',
        'nom',
        'adress',
        'baridimob',
    ];

    /**
     * Scope to search payment info
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('ccp_number', 'like', "%{$search}%")
              ->orWhere('nom', 'like', "%{$search}%")
              ->orWhere('baridimob', 'like', "%{$search}%");
        });
    }

    /**
     * Get formatted CCP number for display
     */
    public function getFormattedCcpNumberAttribute(): ?string
    {
        if (!$this->ccp_number) {
            return null;
        }

        // Format CCP number for better readability
        return chunk_split($this->ccp_number, 3, ' ');
    }

    /**
     * Get masked CCP key for security
     */
    public function getMaskedCcpKeyAttribute(): ?string
    {
        if (!$this->ccp_key) {
            return null;
        }

        // Show only first 2 and last 2 characters
        $length = strlen($this->ccp_key);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($this->ccp_key, 0, 2) . str_repeat('*', $length - 4) . substr($this->ccp_key, -2);
    }
}
