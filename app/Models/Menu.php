<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama_menu',
        'deskripsi',
        'slug',
        'gambar',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->gambar) {
            return null;
        }

        if (filter_var($this->gambar, FILTER_VALIDATE_URL)) {
            return $this->gambar;
        }

        return '/storage/' . $this->gambar;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($menu) {
            if (!$menu->slug) {
                $menu->slug = \Illuminate\Support\Str::slug($menu->nama_menu);
                
                // Ensure uniqueness
                $originalSlug = $menu->slug;
                $count = 1;
                while (static::where('slug', $menu->slug)->exists()) {
                    $menu->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($menu) {
            if ($menu->isDirty('nama_menu')) {
                $menu->slug = \Illuminate\Support\Str::slug($menu->nama_menu);
                
                // Ensure uniqueness
                $originalSlug = $menu->slug;
                $count = 1;
                while (static::where('slug', $menu->slug)->where('id', '!=', $menu->id)->exists()) {
                    $menu->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function details(): HasMany
    {
        return $this->hasMany(MenuDetail::class);
    }
}
