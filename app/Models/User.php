<?php

namespace App\Models;

 use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;

class User extends Authenticatable implements JWTSubject , MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_type',
        'is_verified',
        'telegram_chat_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if user is designer
     */
    public function isDesigner(): bool
    {
        return $this->user_type === 'designer';
    }

    /**
     * Check if user is client
     */
    public function isClient(): bool
    {
        return $this->user_type === 'client';
    }

    /**
     * Get the designer profile associated with the user.
     */
    public function designer(): HasOne
    {
        return $this->hasOne(Designer::class);
    }

    /**
     * Get the admin payment info associated with the user.
     */
    public function adminPaymentInfo(): HasOne
    {
        return $this->hasOne(AdminPaymentInfo::class, 'admin_id');
    }

    /**
     * Get the orders for the user (as client).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    /**
     * Get the ratings given by the user.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the comments made by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Check if user has purchased a specific rachma
     */
    public function hasPurchasedRachma(int $rachmaId): bool
    {
        return $this->orders()
            ->where('status', 'completed')
            ->where(function ($query) use ($rachmaId) {
                // Legacy single-item orders
                $query->where('rachma_id', $rachmaId)
                    // OR new multi-item orders
                    ->orWhereHas('orderItems', function ($subQuery) use ($rachmaId) {
                        $subQuery->where('rachma_id', $rachmaId);
                    });
            })
            ->exists();
    }

    /**
     * Check if user has purchased any of the given rachmat IDs
     */
    public function hasPurchasedAnyRachmat(array $rachmaIds): array
    {
        if (empty($rachmaIds)) {
            return [];
        }

        // Get all completed orders for this user
        $purchasedRachmaIds = collect();

        // Check legacy single-item orders
        $legacyPurchases = $this->orders()
            ->where('status', 'completed')
            ->whereIn('rachma_id', $rachmaIds)
            ->whereNotNull('rachma_id')
            ->pluck('rachma_id');

        $purchasedRachmaIds = $purchasedRachmaIds->merge($legacyPurchases);

        // Check new multi-item orders
        $multiItemPurchases = $this->orders()
            ->where('status', 'completed')
            ->with(['orderItems' => function ($query) use ($rachmaIds) {
                $query->whereIn('rachma_id', $rachmaIds);
            }])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->pluck('rachma_id');
            });

        $purchasedRachmaIds = $purchasedRachmaIds->merge($multiItemPurchases);

        return $purchasedRachmaIds->unique()->values()->toArray();
    }

    /**
     * Get all purchased rachmat IDs for this user
     */
    public function getPurchasedRachmatIds(): array
    {
        $purchasedRachmaIds = collect();

        // Get from legacy single-item orders
        $legacyPurchases = $this->orders()
            ->where('status', 'completed')
            ->whereNotNull('rachma_id')
            ->pluck('rachma_id');

        $purchasedRachmaIds = $purchasedRachmaIds->merge($legacyPurchases);

        // Get from new multi-item orders
        $multiItemPurchases = $this->orders()
            ->where('status', 'completed')
            ->with('orderItems')
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->pluck('rachma_id');
            });

        $purchasedRachmaIds = $purchasedRachmaIds->merge($multiItemPurchases);

        return $purchasedRachmaIds->unique()->values()->toArray();
    }
}
