<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slider extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'link',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * URL gambar untuk tampilan: path relatif ke domain saat ini (bukan APP_URL / localhost),
     * atau URL absolut jika sudah disimpan lengkap di database.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        if (Str::startsWith($this->image, ['http://', 'https://', '//'])) {
            return $this->image;
        }

        $path = str_replace('\\', '/', $this->image);
        $path = ltrim($path, '/');

        return '/storage/' . $path;
    }

    /**
     * Inline style background hero dari gambar slide.
     */
    public function heroSlideStyle(): string
    {
        if (!$this->image_url) {
            return '';
        }

        return 'background-image: url(' . e($this->image_url) . ')';
    }

    /**
     * Link tombol: path relatif ke host yang dipakai pengunjung (bukan url() dari APP_URL).
     */
    public function resolvedLink(): ?string
    {
        if (!$this->link) {
            return null;
        }
        $link = trim($this->link);
        if (Str::startsWith($link, ['http://', 'https://', '//'])) {
            return $link;
        }
        if (Str::startsWith($link, ['mailto:', 'tel:', '#'])) {
            return $link;
        }
        if (str_starts_with($link, '?')) {
            return $link;
        }

        return '/' . ltrim($link, '/');
    }
}
