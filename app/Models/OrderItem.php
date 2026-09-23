<?php

namespace App\Models;

use App\Models\Scopes\SyncedInJubelioAndQadScope;
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
        'allocated_batches',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity_ordered' => 'integer',
        'allocated_batches' => 'array',
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
                '%d %s',
                (int) $this->quantity_ordered,
                $p->large_unit
            );
        }

        if ($this->order_uom === 'base' && $this->quantity_ordered !== null) {
            return sprintf('%d %s', (int) $this->quantity_ordered, $unit);
        }

        return sprintf('%d %s', $base, $unit);
    }

    public function orderedPrice(): float
    {
        if ($this->quantity_ordered > 0) {
            return $this->subtotal / $this->quantity_ordered;
        }
        return $this->price;
    }

    public function catalogUnitPrice(): float
    {
        $this->loadMissing('product');
        if (! $this->product) {
            return (float) $this->price;
        }

        return (float) $this->product->price;
    }

    public function discountedUnitPrice(): float
    {
        return (float) $this->price;
    }

    public function hasUnitDiscount(): bool
    {
        return ($this->catalogUnitPrice() - $this->discountedUnitPrice()) > 0.5;
    }

    public function unitDiscountPercent(): float
    {
        $catalog = $this->catalogUnitPrice();
        if ($catalog <= 0) {
            return 0.0;
        }

        return round((1 - ($this->discountedUnitPrice() / $catalog)) * 100, 1);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)
            ->withoutGlobalScope(SyncedInJubelioAndQadScope::class);
    }
}
