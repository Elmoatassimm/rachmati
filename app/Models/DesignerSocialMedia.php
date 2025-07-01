<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignerSocialMedia extends Model
{
    use HasFactory;

    protected $table = 'designer_social_media';

    protected $fillable = [
        'designer_id',
        'platform',
        'url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the designer that owns the social media link.
     */
    public function designer(): BelongsTo
    {
        return $this->belongsTo(Designer::class);
    }

    /**
     * Scope to get only active social media links
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get links by platform
     */
    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Get platform icon class (for frontend)
     */
    public function getPlatformIconAttribute(): string
    {
        return match($this->platform) {
            'facebook' => 'fab fa-facebook',
            'instagram' => 'fab fa-instagram',
            'twitter' => 'fab fa-twitter',
            'telegram' => 'fab fa-telegram',
            'whatsapp' => 'fab fa-whatsapp',
            'youtube' => 'fab fa-youtube',
           
            default => 'fas fa-link',
        };
    }

    /**
     * Get platform display name
     */
    public function getPlatformNameAttribute(): string
    {
        return match($this->platform) {
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'twitter' => 'Twitter',
            'telegram' => 'Telegram',
            'whatsapp' => 'WhatsApp',
            'youtube' => 'YouTube',
            'website' => 'Website',
            default => ucfirst($this->platform),
        };
    }
}
