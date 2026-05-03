<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        'tampil_mulai',
        'tampil_sampai',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'tampil_mulai' => 'datetime',
        'tampil_sampai' => 'datetime',
    ];

    /**
     * Menu ditampilkan tanpa batas jadwal jika kedua timestamp kosong.
     */
    public function isWithinDisplayWindow(?Carbon $at = null): bool
    {
        $at ??= now();

        if ($this->tampil_mulai === null && $this->tampil_sampai === null) {
            return true;
        }

        if ($this->tampil_mulai && $at->lt($this->tampil_mulai)) {
            return false;
        }

        if ($this->tampil_sampai && $at->gt($this->tampil_sampai)) {
            return false;
        }

        return true;
    }

    /**
     * Menu aktif yang sedang dalam jendela tampil (sinkron dengan isWithinDisplayWindow).
     */
    public function scopeCurrentlyVisible(Builder $query): Builder
    {
        $now = now();

        return $query->where(function (Builder $q) use ($now) {
            $q->where(function (Builder $inner) use ($now) {
                $inner->whereNull('tampil_mulai')->orWhere('tampil_mulai', '<=', $now);
            })->where(function (Builder $inner) use ($now) {
                $inner->whereNull('tampil_sampai')->orWhere('tampil_sampai', '>=', $now);
            });
        });
    }

    /**
     * Estimasi total harga paket (jumlah × harga master produk).
     */
    public function bundledPrice(): float
    {
        $this->loadMissing('details.product');

        return (float) $this->details->sum(function ($detail) {
            $price = (float) ($detail->product->price ?? 0);

            return $price * (int) $detail->jumlah;
        });
    }

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
