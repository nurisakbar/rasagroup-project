<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Jumlah produk yang tampil di storefront (aktif, harga > 0, scope sync Jubelio+QAD).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeWithStorefrontProductsCount($query): void
    {
        $query->withCount([
            'products as products_count' => function ($q) {
                $q->where('status', 'active')->where('price', '>', 0);
            },
        ]);
    }

    /**
     * Kategori aktif untuk sidebar / filter storefront.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function forStorefrontSidebar()
    {
        return static::query()
            ->active()
            ->withStorefrontProductsCount()
            ->whereHas('products', function ($q) {
                $q->where('status', 'active')->where('price', '>', 0);
            })
            ->orderBy('name')
            ->get();
    }

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
}

