<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'cart_type',
        'session_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'order_uom',
        'quantity_ordered',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_ordered' => 'integer',
    ];

    /**
     * Gabungkan metadata satuan pembelian saat merge baris (basis = quantity).
     *
     * @return array{0: ?string, 1: ?int} [order_uom, quantity_ordered]
     */
    public static function computeMergedOrderUom(
        ?string $existingUom,
        ?int $existingOrdered,
        int $existingBaseQty,
        string $incomingUom,
        ?int $incomingOrdered,
        int $incomingBaseAdd
    ): array {
        $incOrd = $incomingOrdered ?? ($incomingUom === 'base' ? $incomingBaseAdd : null);
        if ($incomingUom === 'large' && ($incOrd === null || $incOrd < 1)) {
            return [null, null];
        }

        if ($existingUom === null && $existingOrdered === null) {
            if ($incomingUom === 'base' && $incOrd !== null) {
                return ['base', $existingBaseQty + $incOrd];
            }

            return [null, null];
        }

        if ($existingUom === $incomingUom && $existingOrdered !== null && $incOrd !== null) {
            return [$existingUom, $existingOrdered + $incOrd];
        }

        return [null, null];
    }

    /**
     * Set quantity_ordered & order_uom dari quantity (satuan terkecil), setelah edit keranjang.
     */
    public function syncOrderedMetadataFromBaseQuantity(): void
    {
        $this->loadMissing('product');
        $product = $this->product;
        if (! $product) {
            return;
        }

        if (! $product->hasDualUnitOrdering() || $this->order_uom === null) {
            $this->order_uom = 'base';
            $this->quantity_ordered = $this->quantity;

            return;
        }

        if ($this->order_uom === 'base') {
            $this->quantity_ordered = $this->quantity;

            return;
        }

        if ($this->order_uom === 'large') {
            $per = $product->unitsPerLargeEffective();
            if ($per > 1 && $this->quantity % $per === 0) {
                $this->quantity_ordered = (int) ($this->quantity / $per);

                return;
            }

            $this->order_uom = null;
            $this->quantity_ordered = null;
        }
    }

    /**
     * Baris ini ditampilkan & diinput dalam satuan besar (bukan basis).
     */
    public function showsLargeUnitInCart(): bool
    {
        $this->loadMissing('product');
        $product = $this->product;
        if ($this->order_uom !== 'large' || ! $product || ! $product->hasDualUnitOrdering()) {
            return false;
        }
        if ($this->quantity_ordered === null || $this->quantity_ordered < 1) {
            return false;
        }
        $per = $product->unitsPerLargeEffective();

        return $per > 1 && (int) $this->quantity % $per === 0;
    }

    /** Nilai untuk input jumlah di halaman keranjang */
    public function cartQuantityInputValue(): int
    {
        if ($this->showsLargeUnitInCart()) {
            return (int) $this->quantity_ordered;
        }

        return (int) $this->quantity;
    }

    public function cartQuantityUnitLabel(): string
    {
        $this->loadMissing('product');
        $product = $this->product;
        if ($this->showsLargeUnitInCart()) {
            return (string) ($product->large_unit ?: 'Sat.bsr');
        }

        return (string) ($product->unit ?: 'unit');
    }

    /**
     * Harga per satuan yang sama dengan kolom jumlah (basis = harga produk; besar = harga × isi per besar).
     */
    public function displayUnitPrice(): float
    {
        $this->loadMissing('product');
        $product = $this->product;
        if (! $product) {
            return 0.0;
        }
        if ($this->showsLargeUnitInCart()) {
            return (float) $product->price * (float) $product->unitsPerLargeEffective();
        }

        return (float) $product->price;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Merge session-based cart items to the authenticated user's cart.
     */
    public static function mergeSessionCartToUser($userId, $sessionId): void
    {
        $sessionCarts = self::where('session_id', $sessionId)
            ->where('cart_type', 'regular')
            ->get();

        if ($sessionCarts->isEmpty()) {
            return;
        }

        foreach ($sessionCarts as $sessionCart) {
            $userCart = self::where('user_id', $userId)
                ->where('product_id', $sessionCart->product_id)
                ->where('warehouse_id', $sessionCart->warehouse_id)
                ->where('cart_type', 'regular')
                ->first();

            if ($userCart) {
                $userBaseBefore = (int) $userCart->quantity;
                $userCart->quantity = $userBaseBefore + (int) $sessionCart->quantity;
                $sUom = $sessionCart->order_uom ?? 'base';
                $sOrd = $sessionCart->quantity_ordered ?? ($sUom === 'base' ? (int) $sessionCart->quantity : null);
                [$u, $o] = self::computeMergedOrderUom(
                    $userCart->order_uom,
                    $userCart->quantity_ordered,
                    $userBaseBefore,
                    $sUom,
                    $sOrd,
                    (int) $sessionCart->quantity
                );
                $userCart->order_uom = $u;
                $userCart->quantity_ordered = $o;
                $userCart->save();
                $sessionCart->delete();
            } else {
                // Transfer cart to user
                $sessionCart->user_id = $userId;
                $sessionCart->session_id = null;
                $sessionCart->save();
            }
        }
    }
}
