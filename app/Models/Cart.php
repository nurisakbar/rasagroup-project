<?php

namespace App\Models;

use App\Models\Scopes\SyncedInJubelioAndQadScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\WarehouseStock;

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
        if (! $product || ! $product->hasDualUnitOrdering()) {
            return false;
        }
        
        $isDistributor = \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->isDistributor();
        
        if (! $isDistributor) {
            if ($this->order_uom !== 'large') {
                return false;
            }
            if ($this->quantity_ordered === null || $this->quantity_ordered < 1) {
                return false;
            }
        }

        $per = $product->unitsPerLargeEffective();

        return $per > 1 && (int) $this->quantity % $per === 0;
    }

    /**
     * Jumlah badge keranjang: satuan besar untuk baris yang tampil CTN, selain itu satuan basis.
     *
     * @param  iterable<int, self>  $carts
     */
    public static function badgeCountFromCarts(iterable $carts): int
    {
        $total = 0;
        foreach ($carts as $cart) {
            $total += $cart->cartQuantityInputValue();
        }

        return $total;
    }

    public static function badgeCountForCurrentShopper(): int
    {
        $query = static::query()->where('cart_type', 'regular')->with('product');
        if (\Illuminate\Support\Facades\Auth::check()) {
            $query->where('user_id', \Illuminate\Support\Facades\Auth::id());
        } else {
            $query->where('session_id', session()->getId());
        }

        return self::badgeCountFromCarts($query->get());
    }

    /** Nilai untuk input jumlah di halaman keranjang */
    public function cartQuantityInputValue(): int
    {
        if ($this->showsLargeUnitInCart()) {
            if ($this->quantity_ordered) {
                return (int) $this->quantity_ordered;
            }
            if ($this->product && $this->product->unitsPerLargeEffective() > 0) {
                return (int) ($this->quantity / $this->product->unitsPerLargeEffective());
            }
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
        $this->loadMissing(['product', 'user']);
        $product = $this->product;
        if (! $product) {
            return 0.0;
        }

        $user = $this->user ?? \Illuminate\Support\Facades\Auth::user();
        $unit = $user
            ? $user->getProductPrice($product)
            : \App\Support\TaxAwarePrice::applyDiscount((float) $product->final_price, 0.0);

        if ($this->showsLargeUnitInCart()) {
            return $unit * (float) $product->unitsPerLargeEffective();
        }

        return $unit;
    }

    /**
     * Get sisa stock produk di hub terkait.
     */
    public function currentStock(): int
    {
        if ($this->relationLoaded('warehouseStock')) {
            return (int) ($this->warehouseStock->stock ?? 0);
        }

        if (!$this->warehouse_id) {
            return 0;
        }
        
        return (int) WarehouseStock::where('warehouse_id', $this->warehouse_id)
            ->where('product_id', $this->product_id)
            ->value('stock');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)
            ->withoutGlobalScope(SyncedInJubelioAndQadScope::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseStock(): BelongsTo
    {
        return $this->belongsTo(WarehouseStock::class, 'product_id', 'product_id')
            ->where('warehouse_id', $this->warehouse_id);
    }

    /**
     * Merge session-based cart items to the authenticated user's cart.
     */
    public static function mergeSessionCartToUser($userId, $sessionId): void
    {
        $user = User::find($userId);
        $isDistributor = $user && $user->isDistributor();

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

                // Force large UOM for distributors if product supports it
                if ($isDistributor && $userCart->product && $userCart->product->hasDualUnitOrdering()) {
                    $userCart->order_uom = 'large';
                    $userCart->syncOrderedMetadataFromBaseQuantity();
                }

                $userCart->save();
                $sessionCart->delete();
            } else {
                // Transfer cart to user
                $sessionCart->user_id = $userId;
                $sessionCart->session_id = null;

                // Force large UOM for distributors if product supports it
                if ($isDistributor && $sessionCart->product && $sessionCart->product->hasDualUnitOrdering()) {
                    $sessionCart->order_uom = 'large';
                    $sessionCart->syncOrderedMetadataFromBaseQuantity();
                }

                $sessionCart->save();
            }
        }
    }
}
