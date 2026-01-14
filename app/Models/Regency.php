<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regency extends Model
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
        'province_id',
        'name',
    ];

    /**
     * Get the province of this regency.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get all warehouses in this regency.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Get all districts in this regency.
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
