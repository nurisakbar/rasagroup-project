<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EkspedisiKuService
{
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = config('services.ekspedisiku.token');
        $this->baseUrl = config('services.ekspedisiku.base_url', 'http://localhost:8001/api');
    }

    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/provinces");

            if ($response->failed()) {
                Log::warning('EkspedisiKuService: getProvinces failed', [
                    'url' => "{$this->baseUrl}/provinces",
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'token_prefix' => substr($this->token, 0, 10) . '...'
                ]);
                return null;
            }

            $result = $response->json();
            if (isset($result['data']) && is_array($result['data'])) {
                foreach ($result['data'] as $item) {
                    \App\Models\RajaOngkirProvince::updateOrCreate(
                        ['id' => $item['id']],
                        ['name' => $item['name']]
                    );
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('EkspedisiKuService: getProvinces error', [
                'url' => "{$this->baseUrl}/provinces",
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Get regencies by province ID
     */
    public function getRegencies($provinceId)
    {
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/regencies", [
                    'province_id' => $provinceId
                ]);

            if ($response->failed()) {
                Log::warning('EkspedisiKuService: getRegencies failed', [
                    'url' => "{$this->baseUrl}/regencies",
                    'province_id' => $provinceId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'token_prefix' => substr($this->token, 0, 10) . '...'
                ]);
                return null;
            }

            $result = $response->json();
            if (isset($result['data']) && is_array($result['data'])) {
                foreach ($result['data'] as $item) {
                    \App\Models\RajaOngkirCity::updateOrCreate(
                        ['id' => $item['id']],
                        [
                            'province_id' => $provinceId,
                            'name' => $item['name'],
                            'type' => $item['type'] ?? null,
                            'postal_code' => $item['postal_code'] ?? null
                        ]
                    );
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('EkspedisiKuService: getRegencies error', ['province_id' => $provinceId, 'message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get districts by regency ID
     */
    public function getDistricts($regencyId)
    {
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/districts", [
                    'regency_id' => $regencyId
                ]);

            if ($response->failed()) {
                Log::warning('EkspedisiKuService: getDistricts failed', [
                    'url' => "{$this->baseUrl}/districts",
                    'regency_id' => $regencyId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $result = $response->json();
            if (isset($result['data']) && is_array($result['data'])) {
                foreach ($result['data'] as $item) {
                    \App\Models\RajaOngkirDistrict::updateOrCreate(
                        ['id' => $item['id']],
                        [
                            'city_id' => $regencyId,
                            'name' => $item['name'],
                            'postal_code' => $item['postal_code'] ?? null
                        ]
                    );
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('EkspedisiKuService: getDistricts error', ['regency_id' => $regencyId, 'message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get villages by district ID
     */
    public function getVillages($districtId)
    {
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/villages", [
                    'district_id' => $districtId
                ]);

            if ($response->failed()) {
                Log::warning('EkspedisiKuService: getVillages failed', [
                    'url' => "{$this->baseUrl}/villages",
                    'district_id' => $districtId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $result = $response->json();
            if (isset($result['data']) && is_array($result['data'])) {
                foreach ($result['data'] as $item) {
                    \App\Models\Village::updateOrCreate(
                        ['id' => $item['id']],
                        [
                            'district_id' => $districtId,
                            'name' => $item['name']
                        ]
                    );
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('EkspedisiKuService: getVillages error', ['district_id' => $districtId, 'message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Calculate shipping cost using EkspedisiKu API
     * 
     * @param string $origin Origin name (e.g. "Jakarta")
     * @param string $destination Destination name (e.g. "Bandung")
     * @param int $weight Weight in kg
     * @return array|null
     */
    public function calculateCost($originId, $destinationId, $weight, $courier = 'lion_parcel')
    {
        try {
            /*
            Log::info('EkspedisiKuService: Requesting cost', [
                'url' => "{$this->baseUrl}/ongkir",
                'params' => [
                    'origin' => $originId,
                    'destination' => $destinationId,
                    'weight' => $weight,
                    'courier' => $courier,
                ]
            ]);
            */

            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/ongkir", [
                    'origin' => $originId,
                    'destination' => $destinationId,
                    'weight' => $weight,
                    'courier' => $courier,
                ]);

            /*
            Log::info('EkspedisiKuService: Received response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            */

            if ($response->failed()) {
                Log::warning('EkspedisiKuService: calculateCost failed', [
                    'origin' => $originId,
                    'destination' => $destinationId,
                    'weight' => $weight,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }

            $result = $response->json();
            
            // Normalize to RajaOngkir format
            $normalizedData = [];
            if (isset($result['rates']) && isset($result['rates']['services'])) {
                $carrier = $result['rates'];
                foreach ($carrier['services'] as $service) {
                    $normalizedData[] = [
                        'code' => $carrier['id'], // e.g. lion_parcel
                        'name' => $carrier['label'], // e.g. Lion Parcel
                        'service' => $service['code'], // e.g. REGPACK
                        'description' => $service['name'], // e.g. Regular Package
                        'cost' => $service['price'],
                        'etd' => $service['etd'],
                    ];
                }
            }

            return ['data' => $normalizedData];
        } catch (\Exception $e) {
            Log::error('EkspedisiKuService: calculateCost error', [
                'origin' => $originId,
                'destination' => $destinationId,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create a shipment booking
     * 
     * @param array $payload
     * @return array|null
     */
    public function createBooking(array $payload)
    {
        try {
            $response = Http::withToken($this->token)
                ->post("{$this->baseUrl}/shipments", $payload);

            if ($response->failed()) {
                Log::warning('EkspedisiKuService: createBooking failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('EkspedisiKuService: createBooking error', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Track a shipment
     * 
     * @param string $resi
     * @param string $carrier
     * @return array|null
     */
    public function track($resi, $carrier)
    {
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/track/{$resi}", [
                    'carrier' => $carrier
                ]);

            if ($response->failed()) {
                Log::warning('EkspedisiKuService: track failed', [
                    'resi' => $resi,
                    'carrier' => $carrier,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('EkspedisiKuService: track error', [
                'resi' => $resi,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Request pickup
     * 
     * @param array $shipmentIds
     * @param string $startAt
     * @param string $endAt
     * @return array|null
     */
    public function requestPickup(array $shipmentIds, $startAt, $endAt)
    {
        try {
            $payload = [
                'pickup' => [
                    'shipment_ids' => $shipmentIds,
                    'start_at' => $startAt,
                    'end_at' => $endAt
                ]
            ];

            Log::info('EkspedisiKuService: Requesting pickup', [
                'url' => "{$this->baseUrl}/pickup",
                'payload' => $payload
            ]);

            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Timezone' => '+07:00'
                ])
                ->post("{$this->baseUrl}/pickup", $payload);

            Log::info('EkspedisiKuService: Pickup response', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->failed()) {
                Log::warning('EkspedisiKuService: requestPickup failed', [
                    'shipment_ids' => $shipmentIds,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return $response->json(); // Return json even if failed to get error message
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('EkspedisiKuService: requestPickup error', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get list of available couriers
     */
    public function getCouriers()
    {
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/couriers");

            if ($response->failed()) {
                Log::warning('EkspedisiKuService: getCouriers failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('EkspedisiKuService: getCouriers error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
