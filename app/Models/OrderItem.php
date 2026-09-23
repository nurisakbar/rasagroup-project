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
     * Order distributor ditampilkan dalam satuan terbesar bila qty basis kelipatan konversi.
     * Qty yang dikirim ke QAD/WMS tetap $this->quantity (satuan terkecil).
     */
    public function orderedQuantityDescription(): string
    {
        $this->loadMissing(['order', 'product']);
        $p = $this->product;
        $base = (int) $this->quantity;
        $unit = $p?->unit ?: 'unit';

        if ($this->displaysLargeUnit()) {
            return sprintf('%d %s', $this->displayQuantity(), $p->large_unit);
        }

        if ($this->order_uom === 'base' && $this->quantity_ordered !== null) {
            return sprintf('%d %s', (int) $this->quantity_ordered, $unit);
        }

        return sprintf('%d %s', $base, $unit);
    }

    public function isLargeOrderUom(): bool
    {
        $this->loadMissing('product');
        $uom = strtolower(trim((string) $this->order_uom));
        if ($uom === 'large') {
            return true;
        }

        $large = strtolower(trim((string) ($this->product?->large_unit ?? '')));

        return $large !== '' && $uom === $large;
    }

    /**
     * Order distributor: tampilkan & hitung harga per satuan terbesar
     * jika qty basis kelipatan units_per_large.
     */
    public function displaysLargeUnit(): bool
    {
        $this->loadMissing(['order', 'product']);
        $p = $this->product;
        if (! $p || ! $p->hasDualUnitOrdering()) {
            return false;
        }

        $per = $p->unitsPerLargeEffective();
        if ($per <= 1 || ((int) $this->quantity % $per) !== 0) {
            return false;
        }

        if ($this->order && $this->order->order_type === 'distributor') {
            return true;
        }

        return $this->isLargeOrderUom();
    }

    public function displayQuantity(): int
    {
        if ($this->displaysLargeUnit()) {
            if ($this->quantity_ordered) {
                return (int) $this->quantity_ordered;
            }

            return (int) ($this->quantity / $this->product->unitsPerLargeEffective());
        }

        return (int) $this->quantity;
    }

    public function displayPriceMultiplier(): int
    {
        return $this->displaysLargeUnit()
            ? $this->product->unitsPerLargeEffective()
            : 1;
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

    /**
     * Harga satuan sebelum diskon (DPP). Pajak dikeluarkan dulu sesuai Setting::tax_percent.
     * Distributor: dikalikan isi satuan terbesar.
     */
    public function unitPriceBeforeDiscount(): float
    {
        return \App\Support\TaxAwarePrice::excludingTax($this->catalogUnitPrice())
            * $this->displayPriceMultiplier();
    }

    /**
     * Harga satuan sesudah diskon. Diskon dihitung dari DPP, bukan dari harga termasuk pajak.
     * Distributor: dikalikan isi satuan terbesar.
     */
    public function unitPriceAfterDiscount(): float
    {
        $sold = $this->discountedUnitPrice();
        $base = $this->hasUnitDiscount()
            ? $sold
            : \App\Support\TaxAwarePrice::excludingTax($sold);

        return $base * $this->displayPriceMultiplier();
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
