<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OperationalHour extends Model
{
    use HasUuids;

    protected $fillable = [
        'operatable_id',
        'operatable_type',
        'day',
        'is_open',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'open_time' => 'datetime:H:i',
        'close_time' => 'datetime:H:i',
    ];

    public function operatable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function getDayName($day)
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $days[$day] ?? '';
    }

    public function getDayNameAttribute()
    {
        return self::getDayName($this->day);
    }
}
