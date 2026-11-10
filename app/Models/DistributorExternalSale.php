<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Penjualan distributor dari sistem eksternal. Hanya pencatatan, bukan Order RasaConnect.
 */
class DistributorExternalSale extends Model
{
    use HasUuids;

    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'distributor_id',
        'api_client_id',
        'external_order_number',
        'sold_at',
        'sold_date',
        'status',
        'customer_name',
        'customer_code',
        'subtotal',
        'discount',
        'tax',
        'total',
        'raw_payload',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'sold_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(DistributorExternalSaleItem::class, 'sale_id');
    }
}
