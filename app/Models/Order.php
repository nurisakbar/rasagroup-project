<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    const TYPE_REGULAR = 'regular';
    const TYPE_DISTRIBUTOR = 'distributor';
    const TYPE_POS = 'pos';
    const DISTRIBUTOR_POINTS_PER_ITEM = 5000;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_number',
        'user_id',
        'order_type',
        'address_id',
        'expedition_id',
        'expedition_service',
        'source_warehouse_id',
        'subtotal',
        'shipping_cost',
        'total_amount',
        'shipping_address',
        'payment_method',
        'payment_status',
        'paid_at',
        'order_status',
        'tracking_number',
        'ekspedisiku_shipment_id',
        'ekspedisiku_booking_attempt',
        'ekspedisiku_booking_reference',
        'ekspedisiku_booking_created_at',
        'ekspedisiku_booking_status',
        'ekspedisiku_booking_last_error',
        'ekspedisiku_pickup_requested_at',
        'ekspedisiku_pickup_status',
        'ekspedisiku_pickup_last_error',
        'shipped_at',
        'notes',
        'points_earned',
        'points_credited',
        'affiliate_id',
        'affiliate_points',
        'discount_percent',
        'discount_amount',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'preferred_shipping_date',
        'payment_proof',
        'payment_submit_note',
        'payment_submitted_at',
        'qad_so_number',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'points_credited' => 'boolean',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'ekspedisiku_booking_created_at' => 'datetime',
        'ekspedisiku_pickup_requested_at' => 'datetime',
        'preferred_shipping_date' => 'date',
        'payment_submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->order_status) {
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'default',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'default',
        };
    }

    /**
     * Check if order is online (regular order).
     */
    public function isOnline(): bool
    {
        return $this->order_type === self::TYPE_REGULAR;
    }

    /**
     * Check if order is offline (POS order).
     */
    public function isOffline(): bool
    {
        return $this->order_type === self::TYPE_POS;
    }

    /**
     * Get order type label.
     */
    public function getOrderTypeLabelAttribute(): string
    {
        return match ($this->order_type) {
            self::TYPE_REGULAR => 'Online',
            self::TYPE_POS => 'Offline (POS)',
            self::TYPE_DISTRIBUTOR => 'Distributor',
            default => ucfirst($this->order_type),
        };
    }

    /**
     * Get order type badge class.
     */
    public function getOrderTypeBadgeClassAttribute(): string
    {
        return match ($this->order_type) {
            self::TYPE_REGULAR => 'label-primary',
            self::TYPE_POS => 'label-info',
            self::TYPE_DISTRIBUTOR => 'label-warning',
            default => 'label-default',
        };
    }
    /**
     * Credit earned points to the buyer (DRiiPPreneur) and the affiliate.
     */
    public function creditPoints()
    {
        if ($this->points_credited) {
            return false;
        }

        $credited = false;

        // 1. Credit DRiiPPreneur points (for regular orders)
        if ($this->order_type === self::TYPE_REGULAR && $this->points_earned > 0) {
            $user = $this->user;
            if ($user && $user->isDriippreneurApproved()) {
                $user->increment('points', $this->points_earned);
                $credited = true;
            }
        }

        // 2. Credit Distributor points (for distributor orders)
        if ($this->order_type === self::TYPE_DISTRIBUTOR && $this->points_earned > 0) {
            $user = $this->user;
            if ($user) {
                $user->increment('points', $this->points_earned);
                $credited = true;
            }
        }

        // 3. Credit Affiliate points
        if ($this->affiliate_id && $this->affiliate_points > 0) {
            $affiliate = User::find($this->affiliate_id);
            if ($affiliate) {
                $affiliate->increment('points', $this->affiliate_points);
                $credited = true;
            }
        }

        if ($credited || ($this->points_earned == 0 && $this->affiliate_points == 0)) {
            $this->update(['points_credited' => true]);
            return true;
        }

        return false;
    }
}
