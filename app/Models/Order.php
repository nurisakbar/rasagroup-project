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
        'shipped_at',
        'notes',
        'points_earned',
        'points_credited',
        'xendit_invoice_id',
        'xendit_invoice_url',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'points_credited' => 'boolean',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
