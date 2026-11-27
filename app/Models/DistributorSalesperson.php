<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sales milik distributor (bukan akun login; hanya data yang dikelola distributor).
 */
class DistributorSalesperson extends Model
{
    use HasUuids;

    protected $table = 'distributor_salespeople';

    protected $fillable = ['distributor_id', 'name', 'phone', 'email', 'area', 'is_active', 'notes'];

    protected $casts = ['is_active' => 'boolean'];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}
