<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RajaOngkirProvince extends Model
{
    public $incrementing = false;
    protected $fillable = ['id', 'name'];

    public function cities()
    {
        return $this->hasMany(RajaOngkirCity::class, 'province_id');
    }
}
