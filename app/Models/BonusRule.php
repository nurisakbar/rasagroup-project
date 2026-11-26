<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BonusRule extends Model
{
    use HasUuids;

    public const TYPE_FIXED = 'fixed_gift';
    public const TYPE_CHOICE = 'free_choice';
    public const CUSTOMER_TYPES = ['ecom' => 'Ecom', 'outlet' => 'Outlet', 'subdist' => 'Subdist', 'distributor' => 'Distributor'];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name', 'type', 'buy_qty', 'gift_qty', 'unit', 'is_multiple', 'max_gift_qty',
        'customer_types', 'starts_at', 'ends_at', 'is_active', 'notes',
    ];

    protected $casts = [
        'customer_types' => 'array',
        'is_multiple' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function buyProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'bonus_rule_buy_products');
    }

    public function giftProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'bonus_rule_gift_products');
    }

    public function scopeRunning($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function isFixed(): bool
    {
        return $this->type === self::TYPE_FIXED;
    }

    public function unitLabel(): string
    {
        return $this->unit === 'carton' ? 'karton' : 'pcs';
    }

    public function typeLabel(): string
    {
        return $this->isFixed() ? 'Beli X dapat produk tertentu' : 'Beli X pilih produk bebas';
    }

    public function periodLabel(): string
    {
        if (! $this->starts_at && ! $this->ends_at) {
            return 'Tanpa batas waktu';
        }

        return ($this->starts_at?->format('d/m/Y') ?? '…') . ' - ' . ($this->ends_at?->format('d/m/Y') ?? '…');
    }
}
