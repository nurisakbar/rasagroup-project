<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Armada extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'hub_id',
        'description',
    ];

    public function hub()
    {
        return $this->belongsTo(Warehouse::class, 'hub_id');
    }
}
