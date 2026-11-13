<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan perubahan level pembeli oleh admin (mis. outlet -> reseller).
 */
class UserLevelChange extends Model
{
    public const UPDATED_AT = null;

    public const LABELS = [
        'outlet' => 'Outlet',
        'buyer' => 'User Biasa',
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
