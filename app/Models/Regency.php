<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regency extends Model
{
    protected $table = 'view_wilayah_administratif_indonesia_cache';
    protected $primaryKey = 'regency_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function getNameAttribute(): ?string
    {
        return $this->regency_name ?? $this->attributes['name'] ?? null;
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }
}
