<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'description',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($warehouse) {
            if (empty($warehouse->slug)) {
                $warehouse->slug = static::generateUniqueSlug($warehouse->name);
            }
        });
    }

    public static function generateUniqueSlug($name)
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $count = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        return $slug;
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(RajaOngkirProvince::class, 'province_id');
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(RajaOngkirCity::class, 'regency_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(RajaOngkirDistrict::class, 'district_id');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'warehouse_stocks')
            ->withPivot('stock')
            ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->sum('stock');
    }

    public function getProductsCountAttribute(): int
    {
        return $this->stocks()->count();
    }

    public function getFullLocationAttribute(): string
    {
        $parts = [];
        if ($this->regency) {
            $parts[] = $this->regency->name;
        }
        if ($this->province) {
            $parts[] = $this->province->name;
        }
        return implode(', ', $parts) ?: '-';
    }
}
