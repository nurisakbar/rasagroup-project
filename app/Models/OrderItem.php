<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'order_uom',
        'quantity_ordered',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity_ordered' => 'integer',
    ];

    /**
     * quantity = satuan terkecil (basis); quantity_ordered + order_uom = pilihan pembeli.
     */
    public function orderedQuantityDescription(): string
    {
        $this->loadMissing('product');
        $p = $this->product;
        $base = (int) $this->quantity;
        $unit = $p?->unit ?: 'unit';

        if ($this->order_uom === 'large' && $p && filled($p->large_unit) && $this->quantity_ordered !== null) {
            return sprintf(
                '%d %s (= %d %s)',
                (int) $this->quantity_ordered,
                $p->large_unit,
                $base,
                $unit
            );
        }

        if ($this->order_uom === 'base' && $this->quantity_ordered !== null) {
            return sprintf('%d %s', (int) $this->quantity_ordered, $unit);
        }

        return sprintf('%d %s', $base, $unit);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
