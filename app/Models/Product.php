<?php

namespace App\Models;

use App\Models\Scopes\SyncedInJubelioAndQadScope;
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
        'slug',
        'size',
        'unit',
        'large_unit',
        'units_per_large',
        'price',
        'reseller_point',
        'weight',
        'image',
        'status',
        'sync_sources',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'reseller_point' => 'integer',
        'weight' => 'integer',
        'units_per_large' => 'integer',
        'sync_sources' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SyncedInJubelioAndQadScope());

        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = \Illuminate\Support\Str::slug($product->display_name ?: $product->name);
                
                // Ensure uniqueness (bypass global scope — slug bisa bentrok dengan produk tersembunyi)
                $originalSlug = $product->slug;
                $count = 1;
                while (static::withoutGlobalScopes()->where('slug', $product->slug)->exists()) {
                    $product->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }

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
                'exists_in_db' => static::withoutGlobalScopes()->where('id', $product->id)->exists(),
            ]);
        });

        static::saving(function ($product) {
            if ($product->isDirty('name') || $product->isDirty('commercial_name')) {
                $product->slug = \Illuminate\Support\Str::slug($product->display_name ?: $product->name);
                
                // Ensure uniqueness (bypass global scope)
                $originalSlug = $product->slug;
                $count = 1;
                while (static::withoutGlobalScopes()->where('slug', $product->slug)->where('id', '!=', $product->id)->exists()) {
                    $product->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }

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

    public function promos(): BelongsToMany
    {
        return $this->belongsToMany(Promo::class, 'promo_product')
            ->withTimestamps();
    }

    public function markSyncSource(string $source): self
    {
        $sources = is_array($this->sync_sources) ? $this->sync_sources : [];

        if (! in_array($source, $sources, true)) {
            $sources[] = $source;
            $this->sync_sources = $sources;
        }

        return $this;
    }

    public function syncSourceBadgesHtml(): string
    {
        $sources = is_array($this->sync_sources) ? $this->sync_sources : [];
        $hasJubelio = in_array('jubelio', $sources, true);
        $hasQad = in_array('qad', $sources, true);

        if (! $hasJubelio && ! $hasQad) {
            return '<span class="text-muted">-</span>';
        }

        $badges = [];

        if ($hasJubelio && $hasQad) {
            $badges[] = '<span class="label label-success">Keduanya</span>';
        }

        if ($hasJubelio) {
            $badges[] = '<span class="label label-warning">Jubelio</span>';
        }

        if ($hasQad) {
            $badges[] = '<span class="label label-primary">QAD</span>';
        }

        return implode(' ', $badges);
    }

    public function hasSyncSource(string $source): bool
    {
        return in_array($source, is_array($this->sync_sources) ? $this->sync_sources : [], true);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeFilterBySyncSource($query, string $source): void
    {
        match ($source) {
            'jubelio' => $query->withoutGlobalScope(SyncedInJubelioAndQadScope::class)
                ->whereJsonContains('sync_sources', 'jubelio')
                ->whereJsonDoesntContain('sync_sources', 'qad'),
            'qad' => $query->withoutGlobalScope(SyncedInJubelioAndQadScope::class)
                ->whereJsonContains('sync_sources', 'qad')
                ->whereJsonDoesntContain('sync_sources', 'jubelio'),
            'both' => $query->whereJsonContains('sync_sources', 'jubelio')
                ->whereJsonContains('sync_sources', 'qad'),
            default => null,
        };
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeIncludingUnsyncedSources($query): void
    {
        $query->withoutGlobalScope(SyncedInJubelioAndQadScope::class);
    }

    /**
     * Hanya produk dengan harga > 0 untuk tampilan pembeli (storefront).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeWithBuyerPrice($query): void
    {
        $query->where('price', '>', 0);
    }

    /**
     * Urutkan: produk dengan stok > 0 di depan (sesuai hub terpilih di session, atau total semua gudang).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeOrderByInStockFirst($query, ?string $warehouseId = null): void
    {
        if (! \App\Support\ShopFulfillment::showStockOnStorefront()) {
            return;
        }

        if ($warehouseId) {
            $query->orderByRaw(
                '(SELECT CASE WHEN COALESCE(SUM(ws.stock), 0) > 0 THEN 1 ELSE 0 END FROM warehouse_stocks ws WHERE ws.product_id = products.id AND ws.warehouse_id = ?) DESC',
                [$warehouseId]
            );
        } else {
            $query->orderByRaw(
                '(SELECT CASE WHEN COALESCE(SUM(ws.stock), 0) > 0 THEN 1 ELSE 0 END FROM warehouse_stocks ws WHERE ws.product_id = products.id) DESC'
            );
        }
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc');
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
        $displayName = $this->commercial_name ?: $this->name;
        if ($this->code) {
            return "[{$this->code}] {$displayName}";
        }
        return $displayName;
    }

    /**
     * Get display name attribute (uses commercial_name as primary, falls back to name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->commercial_name ?: $this->name;
    }

    /**
     * Get the image URL attribute.
     * Handles different path formats: products/filename.jpg, storage/products/filename.jpg, or full URL
     * Always returns full URL with domain in format: domain.com/storage/products/filename.jpg
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Get current stock based on selected hub in session.
     */
    public function getCurrentStockAttribute(): int
    {
        $selectedHubId = session('selected_hub_id');
        
        if ($selectedHubId) {
            $stock = $this->warehouseStocks->where('warehouse_id', $selectedHubId)->first();
            return $stock ? $stock->stock : 0;
        }

        // If no hub selected, sum all stocks (or handle as needed)
        return $this->warehouseStocks->sum('stock');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return request()->is('admin/*') ? 'id' : 'slug';
    }

    /**
     * Produk bisa dipesan dalam satuan kecil (UoM) atau satuan besar bila konversi diisi.
     */
    public function hasDualUnitOrdering(): bool
    {
        return filled($this->unit)
            && filled($this->large_unit)
            && (int) ($this->units_per_large ?? 0) > 1;
    }

    /**
     * Jumlah isi satuan besar dalam satuan UoM kecil (stok & harga mengacu ke satuan kecil).
     */
    public function unitsPerLargeEffective(): int
    {
        return $this->hasDualUnitOrdering() ? (int) $this->units_per_large : 1;
    }

    /**
     * Konversi qty yang diinput user (per satuan besar atau kecil) ke qty basis (satuan kecil).
     *
     * @param  string  $uom  'base' | 'large'
     */
    public function orderedQuantityToBase(int $orderedQty, string $uom): int
    {
        if ($orderedQty < 1) {
            return 0;
        }

        if ($uom === 'large' && $this->hasDualUnitOrdering()) {
            return $orderedQty * $this->unitsPerLargeEffective();
        }

        return $orderedQty;
    }
}
