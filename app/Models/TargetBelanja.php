<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetBelanja extends Model
{
    use HasUuids;

    protected $table = 'target_belanja';

    protected $fillable = [
        'distributor_id',
        'brand_id',
        'bulan_tahun',
        'jumlah_target',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}

