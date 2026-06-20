<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class PromoProducts
{
    public static function query(bool $withActivePromos = false): Builder
    {
        $selectedHubId = session('selected_hub_id');

        $query = Product::query()
            ->where('status', 'active')
            ->withBuyerPrice()
            ->with(['category', 'brand', 'warehouseStocks'])
            ->whereHas('promos', function ($q) {
                $q->currentlyActive();
                static::applyAudienceFilter($q);
            });

        if ($withActivePromos) {
            $query->with(['promos' => function ($q) {
                $q->currentlyActive();
                static::applyAudienceFilter($q);
                $q->orderBy('awal');
            }]);
        }

        if ($selectedHubId) {
            $query->with(['warehouseStocks' => function ($q) use ($selectedHubId) {
                $q->where('warehouse_id', $selectedHubId);
            }]);
        }

        return $query
            ->orderByInStockFirst($selectedHubId)
            ->orderByRaw('COALESCE(commercial_name, name) ASC');
    }

    public static function get(?int $limit = null, bool $withActivePromos = false)
    {
        $query = static::query($withActivePromos);

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    private static function applyAudienceFilter($query): void
    {
        $user = auth()->user();
        if (!$user) {
            $query->whereJsonContains('target_audience', 'umum');

            return;
        }

        $allowedAudiences = ['umum'];
        if ($user->isDistributor()) {
            $allowedAudiences[] = 'distributor';
        }
        if ($user->isDriippreneur() || $user->hasAffiliateBankDetails()) {
            $allowedAudiences[] = 'affiliator';
        }
        if ($user->isSuperAdmin() || $user->isAgent()) {
            $allowedAudiences = ['umum', 'affiliator', 'distributor'];
        }

        $query->where(function ($q) use ($allowedAudiences) {
            foreach ($allowedAudiences as $aud) {
                $q->orWhereJsonContains('target_audience', $aud);
            }
        });
    }
}
