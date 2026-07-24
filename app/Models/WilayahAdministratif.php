<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WilayahAdministratif extends Model
{
    protected $table = 'view_wilayah_administratif_indonesia_cache';
    protected $primaryKey = 'village_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
}
