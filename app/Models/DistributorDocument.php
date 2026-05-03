<?php

namespace App\Models;

use App\Support\PublicMediaUrl;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DistributorDocument extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'nama_dokumen',
        'keterangan',
        'file_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return PublicMediaUrl::resolve($this->file_path);
    }

    public function deleteStoredFile(): void
    {
        $path = PublicMediaUrl::storagePathForDelete($this->file_path);
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
