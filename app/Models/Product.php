<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'commercial_name',
        'description',
        'technical_description',
        'brand_id',
        'category_id',
        'size',
        'unit',
        'price',
        'weight',
        'image',
        'status',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            Log::info('Product: Creating product', [
                'code' => $product->code,
                'name' => $product->name,
                'data' => $product->toArray(),
            ]);
        });

        static::created(function ($product) {
            Log::info('Product: Product created successfully', [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'exists_in_db' => Product::where('id', $product->id)->exists(),
            ]);
        });

        static::saving(function ($product) {
            Log::debug('Product: Saving product', [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'is_dirty' => $product->isDirty(),
                'dirty_attributes' => $product->getDirty(),
            ]);
        });

        static::saved(function ($product) {
            Log::info('Product: Product saved successfully', [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'was_recently_created' => $product->wasRecentlyCreated,
            ]);
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function priceLevels(): BelongsToMany
    {
        return $this->belongsToMany(PriceLevel::class, 'product_price_levels')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function productPriceLevels(): HasMany
    {
        return $this->hasMany(ProductPriceLevel::class);
    }

    /**
     * Get price for a specific price level.
     */
    public function getPriceForLevel(PriceLevel $priceLevel): float
    {
        return $priceLevel->calculatePriceForProduct($this);
    }

    public function getFormattedWeightAttribute(): string
    {
        if ($this->weight >= 1000) {
            return number_format($this->weight / 1000, 1) . ' kg';
        }
        return $this->weight . ' gram';
    }

    public function getFullNameAttribute(): string
    {
        if ($this->code) {
            return "[{$this->code}] {$this->name}";
        }
        return $this->name;
    }

    /**
     * Get the image URL attribute.
     * Handles different path formats: products/filename.jpg, storage/products/filename.jpg, or full URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // If already a full URL, return as is
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        // Handle different path formats
        $imagePath = ltrim($this->image, '/');
        
        // If path already starts with storage/, use it directly
        if (strpos($imagePath, 'storage/') === 0) {
            return asset($imagePath);
        }
        
        // Otherwise, prepend storage/
        return asset('storage/' . $imagePath);
    }
}
