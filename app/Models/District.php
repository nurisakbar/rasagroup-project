<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
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
        'regency_id',
        'name',
    ];

    /**
     * Get the regency that owns this district.
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    /**
     * Get the villages in this district.
     */
    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }
}
