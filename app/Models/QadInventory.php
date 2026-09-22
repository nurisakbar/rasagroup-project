<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QadInventory extends Model
{
    use HasFactory;

    protected $table = 'qad_inventories';

    protected $fillable = [
        'item_code',
        'qad_location_code',
        'lot_serial',
        'qty',
        'expired_date',
        'last_sync_at',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'expired_date' => 'date',
        'last_sync_at' => 'datetime',
    ];
}
