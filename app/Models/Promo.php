<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'kode_promo',
        'judul_promo',
        'slug',
        'image',
        'deskripsi',
        'harga',
        'awal',
        'akhir',
    ];

    protected $casts = [
        'awal' => 'date',
        'akhir' => 'date',
        'harga' => 'decimal:2',
    ];
}
