<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rekening distributor untuk pembayaran subdist dan outlet milik distributor (metode distributor_transfer).
 * distributor_id tidak fillable: selalu diisi dari distributor yang login.
 */
class DistributorBankAccount extends Model
{
    use HasUuids;

    protected $fillable = ['bank_name', 'account_number', 'account_name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}
