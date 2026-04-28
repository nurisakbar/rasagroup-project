<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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
        'sub_role',
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
        'ktp_file',
        'npwp_file',
        'selfie_file',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'points',
        'price_level_id',
        'referral_code',
        'referred_by_id',
        'google_id',
        'google_token',
        'google_refresh_token',
        'term_of_payment',
        'monthly_target',
        'wa_verified_at',
        'wa_verification_code',
        'qad_customer_code',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode($user->name);
            }
        });
    }

    public static function generateUniqueReferralCode($name = null)
    {
        if (empty($name)) {
            return static::generateRandomReferralCode();
        }

        // Clean name: lowercase, remove spaces and non-alphanumeric characters
        $baseCode = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
        
        // If empty or too short after cleaning, use random
        if (strlen($baseCode) < 3) {
            return static::generateRandomReferralCode();
        }

        // Limit length to 20 characters
        $baseCode = substr($baseCode, 0, 20);
        
        $code = $baseCode;
        $counter = 1;

        while (static::where('referral_code', $code)->exists()) {
            $suffix = (string)$counter;
            // Shorten baseCode if necessary to fit suffix within 20 chars
            $maxBaseLen = 20 - strlen($suffix);
            $code = substr($baseCode, 0, $maxBaseLen) . $suffix;
            $counter++;
        }
        
        return $code;
    }

    private static function generateRandomReferralCode()
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
            'wa_verified_at' => 'datetime',
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
        return $this->belongsTo(RajaOngkirProvince::class, 'distributor_province_id');
    }

    public function distributorRegency(): BelongsTo
    {
        return $this->belongsTo(RajaOngkirCity::class, 'distributor_regency_id');
    }

    public function driippreneurProvince(): BelongsTo
    {
        return $this->belongsTo(RajaOngkirProvince::class, 'driippreneur_province_id');
    }

    public function driippreneurRegency(): BelongsTo
    {
        return $this->belongsTo(RajaOngkirCity::class, 'driippreneur_regency_id');
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

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }
}
