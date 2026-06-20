<?php

namespace App\Services\MasterSync;

use App\Models\RajaOngkirCity;
use App\Models\RajaOngkirDistrict;
use App\Models\RajaOngkirProvince;
use App\Models\Village;
use App\Models\Warehouse;
use App\Services\EkspedisiKuService;
use App\Services\JubelioService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JubelioHubSyncService
{
    public function __construct(
        private JubelioService $jubelio,
        private EkspedisiKuService $ekspedisiku
    ) {}

    /**
     * @return array{synced: int, skipped: int}
     */
    public function sync(): array
    {
        $token = $this->jubelio->login();
        $locations = $this->jubelio->fetchAllLocations($token);

        $this->ekspedisiku->getProvinces();

        $stats = ['synced' => 0, 'skipped' => 0];

        foreach ($locations as $index => $loc) {
            $locationCode = $loc['location_code'] ?? null;
            $locationName = $loc['location_name'] ?? null;

            if (! $locationCode || ! $locationName) {
                Log::channel('master_sync')->warning('JubelioHubSync: skip location', [
                    'index' => $index,
                    'location' => $loc,
                ]);
                $stats['skipped']++;

                continue;
            }

            $regionIds = $this->ensureRajaOngkirRegionIds(
                $loc['province_id'] ?? null,
                $loc['city_id'] ?? null,
                $loc['district_id'] ?? null
            );
            $coords = $this->parseCoordinate($loc['coordinate'] ?? null);
            $villageId = $this->resolveVillageId($loc['subdistrict_id'] ?? null);

            $warehouse = Warehouse::updateOrCreate(
                ['kode_hub' => $locationCode],
                array_merge([
                    'name' => $locationName,
                    'slug' => Str::slug($locationName) . '-' . strtolower($locationCode),
                    'address' => $loc['address'] ?? null,
                    'postal_code' => $loc['post_code'] ?? null,
                    'phone' => $loc['phone'] ?? null,
                    'description' => 'Synced from Jubelio',
                    'is_active' => (bool) ($loc['is_active'] ?? true),
                    'village_id' => $villageId,
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude'],
                ], $regionIds)
            );
            $warehouse->markSyncSource('jubelio')->save();

            $stats['synced']++;
        }

        Log::channel('master_sync')->info('JubelioHubSync finished', $stats);

        return $stats;
    }

    /**
     * @return array{province_id: ?string, regency_id: ?string, district_id: ?string}
     */
    private function ensureRajaOngkirRegionIds($provinceId, $cityId, $districtId): array
    {
        $result = [
            'province_id' => null,
            'regency_id' => null,
            'district_id' => null,
        ];

        $provinceId = $provinceId !== null && $provinceId !== '' ? (string) $provinceId : null;
        $cityId = $cityId !== null && $cityId !== '' ? (string) $cityId : null;
        $districtId = $districtId !== null && $districtId !== '' ? (string) $districtId : null;

        if ($provinceId) {
            $this->ekspedisiku->getProvinces();
            if (RajaOngkirProvince::where('id', $provinceId)->exists()) {
                $result['province_id'] = $provinceId;
            }
        }

        if ($result['province_id'] && $cityId) {
            $this->ekspedisiku->getRegencies($result['province_id']);
            if (RajaOngkirCity::where('id', $cityId)->where('province_id', $result['province_id'])->exists()) {
                $result['regency_id'] = $cityId;
            }
        }

        if ($result['regency_id'] && $districtId) {
            $this->ekspedisiku->getDistricts($result['regency_id']);
            if (RajaOngkirDistrict::where('id', $districtId)->where('city_id', $result['regency_id'])->exists()) {
                $result['district_id'] = $districtId;
            }
        }

        return $result;
    }

    private function resolveVillageId($subdistrictId): ?string
    {
        if ($subdistrictId === null || $subdistrictId === '') {
            return null;
        }

        $subdistrictId = (string) $subdistrictId;

        return Village::where('id', $subdistrictId)->exists() ? $subdistrictId : null;
    }

    /**
     * @return array{latitude: ?float, longitude: ?float}
     */
    private function parseCoordinate(?string $coordinate): array
    {
        if (! $coordinate || ! preg_match('/\(?\s*([-\d.]+)\s*,\s*([-\d.]+)\s*\)?/', $coordinate, $matches)) {
            return ['latitude' => null, 'longitude' => null];
        }

        return [
            'latitude' => (float) $matches[1],
            'longitude' => (float) $matches[2],
        ];
    }
}
