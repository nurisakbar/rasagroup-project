<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RajaOngkirDistrict extends Model
{
    public $incrementing = false;
    protected $fillable = ['id', 'city_id', 'name', 'postal_code'];

    public function city()
    {
        return $this->belongsTo(RajaOngkirCity::class, 'city_id');
    }
}
