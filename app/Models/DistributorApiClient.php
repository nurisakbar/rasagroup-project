<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DistributorApiClient extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'name', 'key_prefix', 'key_hash', 'is_active', 'last_used_at'];

    protected $hidden = ['key_hash'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Membuat client baru. Key mentah hanya tersedia di nilai kembalian ini; yang disimpan hanya hash-nya.
     *
     * @return array{client: self, plain_key: string}
     */
    public static function issueFor(User $distributor, string $name): array
    {
        $plainKey = 'rdk_' . Str::random(48);

        $client = static::create([
            'user_id' => $distributor->id,
            'name' => $name,
            'key_prefix' => substr($plainKey, 0, 12),
            'key_hash' => static::hashKey($plainKey),
        ]);

        return ['client' => $client, 'plain_key' => $plainKey];
    }

    public static function hashKey(string $plainKey): string
    {
        return hash('sha256', $plainKey);
    }
}
