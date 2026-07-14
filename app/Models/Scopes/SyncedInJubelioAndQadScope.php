<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Batasi query produk ke SKU yang terdaftar di Jubelio dan QAD (sync_sources).
 */
class SyncedInJubelioAndQadScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->user_type === 'distributor') {
            $builder->whereJsonContains('sync_sources', 'qad');
        } else {
            // Guest or Regular User
            $builder->whereJsonContains('sync_sources', 'jubelio');
        }
    }
}
