<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingLocationResolver
{
    /**
     * @return array{lat: string, lng: string, address: string}|null
     */
    public function resolvePickup(Warehouse $warehouse): ?array
    {
        if ($this->hasCoords($warehouse->latitude, $warehouse->longitude)) {
            return [
                'lat' => (string) $warehouse->latitude,
                'lng' => (string) $warehouse->longitude,
                'address' => trim($warehouse->address ?: $warehouse->name.', '.$warehouse->full_location),
            ];
        }

        $warehouse->loadMissing(['district', 'regency', 'province']);

        return $this->geocode($this->buildWarehouseQuery($warehouse), 'warehouse.'.$warehouse->id);
    }

    /**
     * @return array{lat: string, lng: string, address: string}|null
     */
    public function resolveDropoff(Address $address): ?array
    {
        $address->loadMissing(['district', 'regency', 'province', 'village']);

        $queries = array_values(array_filter([
            $this->buildAdministrativeQuery($address),
            $address->full_address.', Indonesia',
        ]));

        foreach ($queries as $query) {
            $result = $this->geocode($query, 'address.'.$address->id.':'.md5($query));
            if ($result !== null) {
                $result['address'] = $address->full_address;

                return $result;
            }
        }

        return null;
    }

    protected function buildAdministrativeQuery(Address $address): ?string
    {
        $parts = array_filter([
            $address->district?->name ? 'Kecamatan '.$address->district->name : null,
            $this->cleanRegencyName($address->regency?->name),
            $address->province?->name,
            'Indonesia',
        ]);

        $query = implode(', ', $parts);

        return $query !== '' ? $query : null;
    }

    protected function buildWarehouseQuery(Warehouse $warehouse): string
    {
        $parts = array_filter([
            $warehouse->address,
            $warehouse->district ? 'Kecamatan '.$warehouse->district->name : null,
            $this->cleanRegencyName($warehouse->regency?->name),
            $warehouse->province?->name,
            'Indonesia',
        ]);

        return implode(', ', $parts) ?: $warehouse->name;
    }

    protected function cleanRegencyName(?string $name): ?string
    {
        if ($name === null || $name === '') {
            return null;
        }

        return trim(str_replace(['KAB. ', 'KOTA ADM. ', 'KOTA ', 'ADM. '], '', $name));
    }

    /**
     * @return array{lat: string, lng: string, address: string}|null
     */
    protected function geocode(string $query, string $cacheKey): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $cacheKeyFull = 'geocode.'.md5($cacheKey.':'.$query);

        if (Cache::has($cacheKeyFull)) {
            $cached = Cache::get($cacheKeyFull);
            if (is_array($cached)) {
                return $cached;
            }
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => config('app.name', 'RasaGroup').'/1.0 (shipping-rates)',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'id',
                ]);

            if ($response->failed()) {
                return null;
            }

            $results = $response->json();
            if (! is_array($results) || empty($results[0]['lat']) || empty($results[0]['lon'])) {
                Log::warning('ShippingLocationResolver: geocode miss', ['query' => $query]);

                return null;
            }

            $resolved = [
                'lat' => (string) $results[0]['lat'],
                'lng' => (string) $results[0]['lon'],
                'address' => $query,
            ];

            Cache::put($cacheKeyFull, $resolved, 604800);

            return $resolved;
        } catch (\Throwable $e) {
            Log::error('ShippingLocationResolver: geocode error', [
                'query' => $query,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function hasCoords(mixed $lat, mixed $lng): bool
    {
        return $lat !== null && $lng !== null && $lat !== '' && $lng !== '';
    }
}
