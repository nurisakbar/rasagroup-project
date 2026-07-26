<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'view_wilayah_administratif_indonesia_cache';
    protected $primaryKey = 'province_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function getNameAttribute(): ?string
    {
        return $this->province_name ?? $this->attributes['name'] ?? null;
    }
}
