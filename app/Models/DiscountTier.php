<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_quantity',
        'discount_percent',
        'is_active',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the applicable discount for a given quantity.
     */
    public static function getApplicableDiscount($quantity)
    {
        return self::where('is_active', true)
            ->where('min_quantity', '<=', $quantity)
            ->orderBy('min_quantity', 'desc')
            ->first();
    }
}
