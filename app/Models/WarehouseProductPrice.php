<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris = satu harga yang berlaku untuk produk di warehouse pada rentang waktu tertentu.
 * Tidak pernah diubah setelah dibuat; harga baru selalu baris baru.
 */
class WarehouseProductPrice extends Model
{
    use HasUuids;

    const SOURCE_MANUAL = 'manual';
    const SOURCE_IMPORT = 'import';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'price',
        'effective_from',
        'effective_to',
        'source',
        'note',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Baris yang berlaku pada waktu tertentu, terbaru di depan (baris pertama = harga yang dipakai).
     */
    public function scopeEffectiveAt(Builder $query, CarbonInterface $at): Builder
    {
        return $query->where('effective_from', '<=', $at)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>', $at))
            ->orderByDesc('effective_from')
            ->orderByDesc('created_at');
    }

    public function isScheduled(): bool
    {
        return $this->effective_from->isFuture();
    }
}
