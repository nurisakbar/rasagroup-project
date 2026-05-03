<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
     * URL gambar (storage relatif, atau URL absolut).
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        if (Str::startsWith($this->image, ['http://', 'https://', '//'])) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
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

    public function resolvedLink(): ?string
    {
        if (!$this->link) {
            return null;
        }
        if (Str::startsWith($this->link, ['http://', 'https://', '//'])) {
            return $this->link;
        }

        return url($this->link);
    }
}
