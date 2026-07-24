<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'label',
        'store_name',
        'recipient_name',
        'phone',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'address_detail',
        'postal_code',
        'notes',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(WilayahAdministratif::class, 'village_id', 'village_id');
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

    public function getFullAddressAttribute(): string
    {
        $parts = [$this->address_detail];
        
        if ($this->village_id || $this->village_name) {
            $parts[] = 'Ds/Kel. ' . ($this->village_name ?? $this->village_id);
        }
        if ($this->district_id || $this->district_name) {
            $parts[] = 'Kec. ' . ($this->district_name ?? $this->district_id);
        }
        if ($this->regency_id || $this->regency_name) {
            $parts[] = $this->regency_name ?? $this->regency_id;
        }
        if ($this->province_id || $this->province_name) {
            $parts[] = $this->province_name ?? $this->province_id;
        }
        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }

        return implode(', ', array_filter($parts));
    }
}
