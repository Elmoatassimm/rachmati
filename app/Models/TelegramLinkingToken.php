<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TelegramLinkingToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new linking token for a user.
     */
    public static function generateForUser(int $userId): self
    {
        // Delete any existing tokens for this user
        static::where('user_id', $userId)->delete();

        // Generate a unique token
        do {
            $token = Str::random(32);
        } while (static::where('token', $token)->exists());

        // Create new token with 15 minutes expiry
        return static::create([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);
    }

    /**
     * Find a valid token.
     */
    public static function findValidToken(string $token): ?self
    {
        return static::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    /**
     * Scope for valid tokens.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    /**
     * Check if token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
