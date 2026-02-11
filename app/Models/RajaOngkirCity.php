<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RajaOngkirCity extends Model
{
    public $incrementing = false;
    protected $fillable = ['id', 'province_id', 'name', 'type', 'postal_code'];

    public function province()
    {
        return $this->belongsTo(RajaOngkirProvince::class, 'province_id');
    }

    public function districts()
    {
        return $this->hasMany(RajaOngkirDistrict::class, 'city_id');
    }
}
