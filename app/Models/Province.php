<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    /**
     * The primary key type (CHAR, not UUID or integer)
     */
    protected $keyType = 'string';
    
    /**
     * Indicates if the IDs are auto-incrementing (no, they are CHAR codes)
     */
    public $incrementing = false;
    
    /**
     * Indicates if the model should be timestamped
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * Get all regencies in this province.
     */
    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class);
    }

    /**
     * Get all warehouses in this province.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
}
