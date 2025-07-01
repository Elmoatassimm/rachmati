<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'rachma_id',
        'name',
        'name_ar',
        'name_fr',
        'length',
        'height',
        'stitches',
        'order',
    ];

    protected $casts = [
        'length' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    /**
     * Get the rachma that owns the part.
     */
    public function rachma(): BelongsTo
    {
        return $this->belongsTo(Rachma::class);
    }

    /**
     * Get the name based on the current locale.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"name_$locale"} ?? $this->name_ar ?? $this->name;
    }

    /**
     * Get the name attribute (defaults to Arabic, then original name).
     */
    public function getNameAttribute(): string
    {
        return $this->attributes['name_ar'] ?? $this->attributes['name'];
    }
} 