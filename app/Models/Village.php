<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Village extends Model
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
        'district_id',
        'name',
    ];

    /**
     * Get the district that owns this village.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
