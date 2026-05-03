<?php

namespace App\Models;

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
        if (!$this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'themes/')) {
            return asset($this->image);
        }

        return Storage::disk('public')->url($this->image);
    }

    /** Hapus file disk hanya untuk gambar yang disimpan di storage/public. */
    public function deleteStoredImageFile(): void
    {
        if (!$this->image || filter_var($this->image, FILTER_VALIDATE_URL)) {
            return;
        }
        if (str_starts_with($this->image, 'themes/')) {
            return;
        }
        Storage::disk('public')->delete($this->image);
    }
}
