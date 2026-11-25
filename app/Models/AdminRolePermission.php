<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminRolePermission extends Model
{
    use HasUuids;

    /** Aksi yang bisa diatur per modul. */
    public const ACTIONS = ['view', 'create', 'edit', 'delete'];

    protected $fillable = ['role', 'module_id', 'can_view', 'can_create', 'can_edit', 'can_delete'];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(AdminModule::class, 'module_id');
    }
}
