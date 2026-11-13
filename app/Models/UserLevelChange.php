<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan audit perubahan level pembeli (outlet / ecom <-> reseller): siapa, kapan, dari level apa ke apa.
 * Hanya untuk audit; tidak ditampilkan di aplikasi.
 */
class UserLevelChange extends Model
{
    public const UPDATED_AT = null;

    public const LABELS = [
        'outlet' => 'Outlet',
        'buyer' => 'Ecom',
        'reseller' => 'Reseller',
    ];

    protected $fillable = ['user_id', 'from_level', 'to_level', 'changed_by'];

    protected $casts = ['created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public static function label(string $level): string
    {
        return self::LABELS[$level] ?? $level;
    }
}
