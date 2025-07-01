<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
     
        'name_ar',
        'name_fr',
      
       
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the rachmat that belong to this category (many-to-many).
     */
    public function rachmat(): BelongsToMany
    {
        return $this->belongsToMany(Rachma::class, 'rachma_categories');
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

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
