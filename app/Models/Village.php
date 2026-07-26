<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $table = 'view_wilayah_administratif_indonesia_cache';
    protected $primaryKey = 'village_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function getNameAttribute(): ?string
    {
        return $this->village_name ?? $this->attributes['name'] ?? null;
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_id', 'regency_id');
    }

    public function city()
    {
        return $this->belongsTo(Regency::class, 'regency_id', 'regency_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }
}
