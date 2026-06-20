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
        if (! config('shop.products_require_jubelio_and_qad', true)) {
            return;
        }

        $builder->whereJsonContains('sync_sources', 'jubelio')
            ->whereJsonContains('sync_sources', 'qad');
    }
}
