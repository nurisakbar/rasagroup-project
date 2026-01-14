<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'address_detail',
        'postal_code',
        'notes',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [$this->address_detail];
        
        if ($this->village) {
            $parts[] = $this->village->name;
        }
        if ($this->district) {
            $parts[] = 'Kec. ' . $this->district->name;
        }
        if ($this->regency) {
            $parts[] = $this->regency->name;
        }
        if ($this->province) {
            $parts[] = $this->province->name;
        }
        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }

        return implode(', ', array_filter($parts));
    }
}
