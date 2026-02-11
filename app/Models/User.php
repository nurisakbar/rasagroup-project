<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    const ROLE_BUYER = 'buyer';
    const ROLE_RESELLER = 'reseller';
    const ROLE_AGENT = 'agent';
    const ROLE_WAREHOUSE = 'warehouse';
    const ROLE_DRIIPPRENEUR = 'driippreneur';
    const ROLE_DISTRIBUTOR = 'distributor';
    const ROLE_SUPER_ADMIN = 'super_admin';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'warehouse_id',
        'no_ktp',
        'no_npwp',
        'distributor_status',
        'distributor_province_id',
        'distributor_regency_id',
        'distributor_address',
        'driippreneur_status',
        'driippreneur_province_id',
        'driippreneur_regency_id',
        'driippreneur_address',
        'driippreneur_applied_at',
        'points',
        'price_level_id',
        'referral_code',
        'referred_by_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });
    }

    public static function generateUniqueReferralCode()
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'points' => 'integer',
            'driippreneur_applied_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function priceLevel(): BelongsTo
    {
        return $this->belongsTo(PriceLevel::class);
    }

    public function distributorProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'distributor_province_id');
    }

    public function distributorRegency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'distributor_regency_id');
    }

    public function driippreneurProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'driippreneur_province_id');
    }

    public function driippreneurRegency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'driippreneur_regency_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function pointWithdrawals(): HasMany
    {
        return $this->hasMany(PointWithdrawal::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAgent(): bool
    {
        return in_array($this->role, [self::ROLE_AGENT, self::ROLE_SUPER_ADMIN]);
    }

    public function isWarehouse(): bool
    {
        return $this->role === self::ROLE_WAREHOUSE;
    }

    public function isDriippreneur(): bool
    {
        return $this->role === self::ROLE_DRIIPPRENEUR;
    }

    public function isDistributor(): bool
    {
        return $this->role === self::ROLE_DISTRIBUTOR;
    }

    public function isBuyer(): bool
    {
        return $this->role === self::ROLE_BUYER;
    }

    public function canApplyAsDistributor(): bool
    {
        return $this->role === self::ROLE_BUYER && empty($this->distributor_status);
    }

    public function hasDistributorApplicationPending(): bool
    {
        return $this->distributor_status === 'pending';
    }

    public function isDistributorApproved(): bool
    {
        return $this->distributor_status === 'approved';
    }

    public function isDistributorRejected(): bool
    {
        return $this->distributor_status === 'rejected';
    }

    public function canApplyAsDriippreneur(): bool
    {
        return $this->role === self::ROLE_BUYER && empty($this->driippreneur_status);
    }

    public function hasDriippreneurApplicationPending(): bool
    {
        return $this->driippreneur_status === 'pending';
    }

    public function isDriippreneurApproved(): bool
    {
        return $this->driippreneur_status === 'approved';
    }

    public function isDriippreneurRejected(): bool
    {
        return $this->driippreneur_status === 'rejected';
    }

    /**
     * Get product price for this user (distributor with price level or regular price).
     */
    public function getProductPrice(Product $product): float
    {
        // If user is distributor and has price level, use price level pricing
        if ($this->isDistributor() && $this->priceLevel) {
            return $this->priceLevel->calculatePriceForProduct($product);
        }
        
        // Otherwise return regular price
        return (float) $product->price;
    }
}
