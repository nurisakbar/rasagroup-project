<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sales milik distributor (bukan akun login; hanya data yang dikelola distributor).
 * distributor_id sengaja tidak fillable: selalu diisi dari distributor yang login dan tidak bisa dipindah.
 */
class DistributorSalesperson extends Model
{
    use HasUuids;

    protected $table = 'distributor_salespeople';

    protected $fillable = ['name', 'phone'];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}
