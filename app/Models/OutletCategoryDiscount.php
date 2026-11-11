<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Diskon per brand & kategori dari distributor untuk satu outlet rekrutannya.
 */
class OutletCategoryDiscount extends Model
{
    protected $fillable = [
        'outlet_id',
        'distributor_id',
        'brand_id',
        'category_id',
        'discount_percentage',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(User::class, 'outlet_id');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
