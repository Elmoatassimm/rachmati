<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_id',
        'target_type',
        'comment',
    ];

    protected $casts = [
    ];

    /**
     * Get the user that owns the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the target model (Rachma or Designer)
     */
    public function target()
    {
        if ($this->target_type === 'rachma') {
            return $this->belongsTo(Rachma::class, 'target_id');
        } elseif ($this->target_type === 'store') {
            return $this->belongsTo(Designer::class, 'target_id');
        }
        
        return null;
    }

    /**
     * Scope to get comments for rachmat
     */
    public function scopeForRachmat($query)
    {
        return $query->where('target_type', 'rachma');
    }

    /**
     * Scope to get comments for stores
     */
    public function scopeForStores($query)
    {
        return $query->where('target_type', 'store');
    }

    /**
     * Scope to get comments by target
     */
    public function scopeForTarget($query, $targetId, $targetType)
    {
        return $query->where('target_id', $targetId)->where('target_type', $targetType);
    }

}
