<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expedition extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'logo',
        'description',
        'base_cost',
        'est_days_min',
        'est_days_max',
        'is_active',
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getServicesAttribute(): array
    {
        $services = [
            'jne' => [
                ['code' => 'REG', 'name' => 'Reguler', 'multiplier' => 1.0, 'days_add' => 0],
                ['code' => 'YES', 'name' => 'Yakin Esok Sampai', 'multiplier' => 1.5, 'days_add' => -1],
                ['code' => 'OKE', 'name' => 'Ongkos Kirim Ekonomis', 'multiplier' => 0.8, 'days_add' => 2],
            ],
            'jnt' => [
                ['code' => 'EZ', 'name' => 'J&T EZ', 'multiplier' => 1.0, 'days_add' => 0],
                ['code' => 'EXPRESS', 'name' => 'J&T Express', 'multiplier' => 1.2, 'days_add' => -1],
            ],
            'sicepat' => [
                ['code' => 'REG', 'name' => 'Reguler', 'multiplier' => 1.0, 'days_add' => 0],
                ['code' => 'BEST', 'name' => 'Besok Sampai Tujuan', 'multiplier' => 1.4, 'days_add' => -1],
                ['code' => 'GOKIL', 'name' => 'Gokil', 'multiplier' => 0.85, 'days_add' => 1],
            ],
            'anteraja' => [
                ['code' => 'REG', 'name' => 'Reguler', 'multiplier' => 1.0, 'days_add' => 0],
                ['code' => 'SD', 'name' => 'Same Day', 'multiplier' => 2.0, 'days_add' => -2],
                ['code' => 'ND', 'name' => 'Next Day', 'multiplier' => 1.5, 'days_add' => -1],
            ],
            'pos' => [
                ['code' => 'REG', 'name' => 'Paket Kilat Khusus', 'multiplier' => 1.0, 'days_add' => 0],
                ['code' => 'EXPRESS', 'name' => 'Express Next Day', 'multiplier' => 1.5, 'days_add' => -2],
            ],
        ];

        return $services[$this->code] ?? [
            ['code' => 'REG', 'name' => 'Reguler', 'multiplier' => 1.0, 'days_add' => 0],
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
