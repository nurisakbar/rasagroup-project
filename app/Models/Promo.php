<?php

namespace App\Models;

use App\Support\PublicMediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'awal' => 'datetime',
        'akhir' => 'datetime',
        'harga' => 'decimal:2',
    ];

    /**
     * URL tampilan gambar: upload di storage, aset tema di public/themes/..., atau URL absolut.
     */
    public function getImageUrlAttribute(): ?string
    {
        return PublicMediaUrl::resolve($this->image);
    }

    /** Hapus file disk hanya untuk gambar yang disimpan di storage/public. */
    public function deleteStoredImageFile(): void
    {
        $path = PublicMediaUrl::storagePathForDelete($this->image);
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
