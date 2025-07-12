<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetCode extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'password_reset_codes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'expires_at',
        'used_at',
        'created_at',
    ];

    /**
     * Generate a new 6-digit verification code
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new password reset code for the given email
     */
    public static function createForEmail(string $email): self
    {
        // Delete any existing codes for this email
        static::where('email', $email)->delete();

        // Create new code
        return static::create([
            'email' => $email,
            'code' => static::generateCode(),
            'expires_at' => Carbon::now()->addMinutes(15), // 15 minutes expiration
        ]);
    }

    /**
     * Check if the code is valid (not expired and not used)
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture() && is_null($this->used_at);
    }

    /**
     * Mark the code as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => Carbon::now()]);
    }

    /**
     * Find a valid code for the given email and code
     */
    public static function findValidCode(string $email, string $code): ?self
    {
        return static::where('email', $email)
            ->where('code', $code)
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('used_at')
            ->first();
    }

    /**
     * Clean up expired codes
     */
    public static function cleanupExpired(): int
    {
        return static::where('expires_at', '<', Carbon::now())->delete();
    }
}
