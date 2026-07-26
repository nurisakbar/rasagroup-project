<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'view_wilayah_administratif_indonesia_cache';
    protected $primaryKey = 'district_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function getNameAttribute(): ?string
    {
        return $this->district_name ?? $this->attributes['name'] ?? null;
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_id', 'regency_id');
    }

    public function city()
    {
        return $this->belongsTo(Regency::class, 'regency_id', 'regency_id');
    }
}
