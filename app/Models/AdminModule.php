<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminModule extends Model
{
    use HasUuids;

    protected $fillable = [
        'key', 'name', 'group', 'icon', 'menu_route', 'route_patterns',
        'sort_order', 'show_in_menu', 'is_active', 'is_system',
    ];

    protected $casts = [
        'route_patterns' => 'array',
        'sort_order' => 'integer',
        'show_in_menu' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(AdminRolePermission::class, 'module_id');
    }
}
