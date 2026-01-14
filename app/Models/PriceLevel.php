<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PriceLevel extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'discount_percentage',
        'order',
        'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get products for this price level.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_price_levels')
            ->withPivot('price')
            ->withTimestamps();
    }

    /**
     * Get product price levels (pivot model).
     */
    public function productPriceLevels()
    {
        return $this->hasMany(ProductPriceLevel::class);
    }

    /**
     * Scope for active price levels.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Calculate price for a product based on this level.
     */
    public function calculatePriceForProduct(Product $product): float
    {
        // Check if there's a custom price for this product and level
        $productPriceLevel = ProductPriceLevel::where('product_id', $product->id)
            ->where('price_level_id', $this->id)
            ->first();

        if ($productPriceLevel && $productPriceLevel->price !== null) {
            return (float) $productPriceLevel->price;
        }

        // Otherwise, calculate based on discount percentage
        $discount = ($product->price * $this->discount_percentage) / 100;
        return (float) ($product->price - $discount);
    }
}
