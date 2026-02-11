<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.key');
        $this->baseUrl = config('services.rajaongkir.base_url');
    }

    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        $cached = \App\Models\RajaOngkirProvince::orderBy('name')->get();
        if ($cached->count() > 0) {
            return [
                'meta' => ['message' => 'Success Get Province from Cache'],
                'data' => $cached->toArray()
            ];
        }

        try {
            $response = Http::withHeaders([
                'Key' => $this->apiKey
            ])->get("{$this->baseUrl}/destination/province");

            $result = $response->json();
            if ($response->successful() && isset($result['data'])) {
                foreach ($result['data'] as $item) {
                    \App\Models\RajaOngkirProvince::updateOrCreate(
                        ['id' => $item['id']],
                        ['name' => $item['name']]
                    );
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('RajaOngkirService: getProvinces error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get cities by province ID
     */
    public function getCities($provinceId)
    {
        $cached = \App\Models\RajaOngkirCity::where('province_id', $provinceId)->orderBy('name')->get();
        if ($cached->count() > 0) {
            return [
                'meta' => ['message' => 'Success Get City from Cache'],
                'data' => $cached->toArray()
            ];
        }

        try {
            $response = Http::withHeaders([
                'Key' => $this->apiKey
            ])->get("{$this->baseUrl}/destination/city/{$provinceId}");

            $result = $response->json();
            if ($response->successful() && isset($result['data'])) {
                foreach ($result['data'] as $item) {
                    \App\Models\RajaOngkirCity::updateOrCreate(
                        ['id' => $item['id']],
                        [
                            'province_id' => $provinceId,
                            'name' => $item['name'],
                            'type' => $item['type'] ?? null,
                            'postal_code' => $item['zip_code'] ?? ($item['postal_code'] ?? null)
                        ]
                    );
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('RajaOngkirService: getCities error', ['province_id' => $provinceId, 'message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get districts by city ID
     */
    public function getDistricts($cityId)
    {
        $cached = \App\Models\RajaOngkirDistrict::where('city_id', $cityId)->orderBy('name')->get();
        if ($cached->count() > 0) {
            return [
                'meta' => ['message' => 'Success Get District from Cache'],
                'data' => $cached->toArray()
            ];
        }

        try {
            $response = Http::withHeaders([
                'Key' => $this->apiKey
            ])->get("{$this->baseUrl}/destination/district/{$cityId}");

            $result = $response->json();
            if ($response->successful() && isset($result['data'])) {
                foreach ($result['data'] as $item) {
                    \App\Models\RajaOngkirDistrict::updateOrCreate(
                        ['id' => $item['id']],
                        [
                            'city_id' => $cityId,
                            'name' => $item['name'],
                            'postal_code' => $item['zip_code'] ?? ($item['postal_code'] ?? null)
                        ]
                    );
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('RajaOngkirService: getDistricts error', ['city_id' => $cityId, 'message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Calculate shipping cost
     * 
     * @param int $origin Origin district ID
     * @param int $destination Destination district ID
     * @param int $weight Weight in grams
     * @param string $courier Courier codes (e.g. "jne:sicepat:jnt")
     * @return array|null
     */
    public function calculateCost($origin, $destination, $weight, $courier = 'jne:sicepat:jnt:pos:tiki')
    {
        try {
            // Ensure origin and destination are integers, not strings
            $origin = (int) $origin;
            $destination = (int) $destination;
            $weight = (int) $weight;
            
            // Create cache key
            $cacheKey = "rajaongkir_cost_{$origin}_{$destination}_{$weight}_{$courier}";
            
            // Try to get from cache (1 hour TTL)
            $cached = \Cache::get($cacheKey);
            if ($cached) {
                Log::info('RajaOngkirService: Using cached result', ['cache_key' => $cacheKey]);
                return $cached;
            }
            
            $response = Http::asForm()
                ->withHeaders([
                    'Key' => $this->apiKey
                ])->post("{$this->baseUrl}/calculate/district/domestic-cost", [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier,
                    'price' => 'lowest'
                ]);

            if ($response->failed()) {
                Log::warning('RajaOngkirService: calculateCost failed', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier,
                    'response' => $response->json()
                ]);
            }

            $result = $response->json();
            
            // Cache successful results for 1 hour
            if ($response->successful() && isset($result['data']) && !empty($result['data'])) {
                \Cache::put($cacheKey, $result, 3600); // 1 hour
                Log::info('RajaOngkirService: Cached result', ['cache_key' => $cacheKey]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('RajaOngkirService: calculateCost error', [
                'origin' => $origin,
                'destination' => $destination,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Track waybill status
     * 
     * @param string $waybill Waybill number
     * @param string $courier Courier code (jne, pos, tiki, wahana, jnt, rpx, sap, sicepat, pcp, jet, dse, first, ninja, lion, idl, rex, ide, sentral)
     * @return array|null
     */
    public function trackWaybill($waybill, $courier)
    {
        try {
            $url = "{$this->baseUrl}/track/waybill?awb={$waybill}&courier=" . strtolower($courier);
            
            Log::info('RajaOngkirService: Tracking Request', [
                'url' => $url,
                'waybill' => $waybill,
                'courier' => $courier
            ]);

            // endpoint: /track/waybill?awb=...&courier=... (POST)
            $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->post($url);

            Log::info('RajaOngkirService: Tracking Response Raw', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->failed()) {
                Log::warning('RajaOngkirService: trackWaybill failed', [
                    'waybill' => $waybill,
                    'courier' => $courier,
                    'response' => $response->json()
                ]);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('RajaOngkirService: trackWaybill exception', [
                'waybill' => $waybill,
                'courier' => $courier,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
}
