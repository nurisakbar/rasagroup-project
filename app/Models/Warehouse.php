<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasOperationalHours;

class Warehouse extends Model
{
    use HasFactory, HasUuids, HasOperationalHours;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'kode_hub',
        'name',
        'slug',
        'address',
        'postal_code',
        'phone',
        'description',
        'sync_sources',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'latitude',
        'longitude',
        'is_active',
        'target_role',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        
        'sync_sources' => 'array',
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

    public function wilayah()
    {
        return $this->belongsTo(WilayahAdministratif::class, 'village_id', 'village_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id', 'village_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_id', 'regency_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    public function getProvinceNameAttribute(): ?string
    {
        return $this->wilayah?->province_name ?? WilayahAdministratif::where('province_id', $this->province_id)->first()?->province_name;
    }

    public function getRegencyNameAttribute(): ?string
    {
        return $this->wilayah?->regency_name ?? WilayahAdministratif::where('regency_id', $this->regency_id)->first()?->regency_name;
    }

    public function getDistrictNameAttribute(): ?string
    {
        return $this->wilayah?->district_name ?? WilayahAdministratif::where('district_id', $this->district_id)->first()?->district_name;
    }

    public function getVillageNameAttribute(): ?string
    {
        return $this->wilayah?->village_name ?? WilayahAdministratif::where('village_id', $this->village_id)->first()?->village_name;
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
        $badges = [];

        if (in_array('jubelio', $sources, true)) {
            $badges[] = '<span class="label label-warning">Jubelio</span>';
        }
        if (in_array('qad', $sources, true)) {
            $badges[] = '<span class="label label-primary">QAD</span>';
        }

        return $badges === [] ? '<span class="text-muted">-</span>' : implode(' ', $badges);
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

    public function getFullAddressAttribute(): string
    {
        $parts = [];
        if ($this->address) {
            $parts[] = $this->address;
        }
        
        $regionParts = [];
        if ($this->village_name) {
            $regionParts[] = $this->village_name;
        }
        if ($this->district_name) {
            $regionParts[] = $this->district_name;
        }
        if ($this->regency_name) {
            $regionParts[] = $this->regency_name;
        }
        if ($this->province_name) {
            $regionParts[] = $this->province_name;
        }
        
        if (!empty($regionParts)) {
            $parts[] = implode(', ', $regionParts);
        }
        
        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }
        
        return implode(', ', $parts) ?: '-';
    }

    /**
     * Find the best warehouse for a given address.
     *
     * @param  string|null  $excludeWarehouseId  Mis. hub distributor sendiri — tidak dipilih untuk pengiriman belanja.
     */
    public static function findBestHubForAddress(Address $address, ?string $excludeWarehouseId = null, float $totalAmount = 0): ?self
    {
                $exclude = $excludeWarehouseId;
        
        // Custom Logic: If total >= 25,000,000 force MM2100 hub
        if ($totalAmount >= 25000000) {
            $mm2100 = self::where('is_active', true)->where('name', 'like', '%MM2100%')->first();
            if ($mm2100) {
                return $mm2100;
            }
        }
        $user = $address->user;
        
        // User role mapping
        $rolesAllowed = ['umum', 'ecommerce']; // default for regular users
        
        if ($user) {
            if ($user->isDistributor()) {
                $rolesAllowed = ['distributor']; // STRICTLY distributor only
            } elseif ($user->isOutlet()) {
                $rolesAllowed = ['outlet']; // STRICTLY outlet only
            }
        }

        $queryBuilder = function () use ($exclude, $rolesAllowed) {
            return self::where('is_active', true)
                ->when($exclude, fn ($q) => $q->where('id', '!=', $exclude))
                ->whereIn('target_role', $rolesAllowed);
        };

        // 1. Try finding nearest by Latitude/Longitude first
        if ($address->latitude && $address->longitude) {
            $lat = (float) $address->latitude;
            $lng = (float) $address->longitude;

            $hub = $queryBuilder()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderByRaw("POW(latitude - ?, 2) + POW(longitude - ?, 2) ASC", [$lat, $lng])
                ->first();
                
            if ($hub) {
                return $hub;
            }
        }

        // 2. Try Same District
        if ($address->district_id) {
            $hub = $queryBuilder()
                ->where('district_id', $address->district_id)
                ->first();
            if ($hub) {
                return $hub;
            }
        }

        // 3. Try Same Regency
        if ($address->regency_id) {
            $hub = $queryBuilder()
                ->where('regency_id', $address->regency_id)
                ->first();
            if ($hub) {
                return $hub;
            }
        }

        // 4. Try Same Province
        if ($address->province_id) {
            $hub = $queryBuilder()
                ->where('province_id', $address->province_id)
                ->first();
            if ($hub) {
                return $hub;
            }
        }

        // 5. Fallback: any active hub (respect exclusion; hindari fallback ke hub sendiri distributor)
        return $queryBuilder()
            ->orderByRaw("CASE WHEN name LIKE '%MM2100%' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->first();
    }
}
