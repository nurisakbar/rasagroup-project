<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_purchase',
        'discount_percent',
        'is_active',
    ];

    protected $casts = [
        'min_purchase' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the applicable discount for a given amount.
     */
    public static function getApplicableDiscount($amount)
    {
        return self::where('is_active', true)
            ->where('min_purchase', '<=', $amount)
            ->orderBy('min_purchase', 'desc')
            ->first();
    }
}
