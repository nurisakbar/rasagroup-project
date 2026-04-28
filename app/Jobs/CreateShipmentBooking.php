<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\EkspedisikuService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateShipmentBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EkspedisikuService $service)
    {
        $this->order->load(['user', 'address.district.city', 'sourceWarehouse.district.city', 'items.product', 'expedition']);

        Log::info('Processing background shipment booking', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'attempt' => (int) ($this->order->ekspedisiku_booking_attempt ?? 0) + 1,
        ]);

        if (!$this->order->sourceWarehouse || !$this->order->address || !$this->order->expedition) {
            Log::warning('CreateShipmentBooking: Missing required order relationships', [
                'order_id' => $this->order->id,
                'has_warehouse' => !!$this->order->sourceWarehouse,
                'has_address' => !!$this->order->address,
                'has_expedition' => !!$this->order->expedition,
            ]);
            return;
        }

        $normalizePhoneE164Id = function (?string $phone): ?string {
            $phone = trim((string) $phone);
            if ($phone === '') {
                return null;
            }
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            if (str_starts_with($phone, '+62')) {
                return $phone;
            }
            if (str_starts_with($phone, '62')) {
                return '+' . $phone;
            }
            if (str_starts_with($phone, '0')) {
                return '+62' . substr($phone, 1);
            }
            // Fallback: assume already local number without prefix
            return '+62' . $phone;
        };

        $normalizeCityLabel = function (?string $cityName): string {
            $cityName = strtoupper(trim((string) $cityName));
            if ($cityName === '') {
                return 'NOT FOUND';
            }
            // Our DB sometimes stores prefixes that Lion/EkspedisiKu location matcher doesn't like.
            $cityName = str_replace(
                ['KAB. ', 'KOTA ', 'ADM. ', 'ADMINISTRASI ', 'KOTA ADM. ', 'KOTA ADMINISTRASI '],
                '',
                $cityName
            );
            $cityName = preg_replace('/\s+/', ' ', $cityName);
            return trim((string) $cityName);
        };

        $resolveDistrictNameFromEkspedisiKu = function (?int $districtId, ?int $cityId) use ($service): ?string {
            if (! $districtId || ! $cityId) {
                return null;
            }
            $res = $service->getDistricts($cityId);
            $data = $res['data'] ?? null;
            if (! is_array($data)) {
                return null;
            }
            foreach ($data as $d) {
                if ((int) ($d['id'] ?? 0) === (int) $districtId) {
                    $name = $d['name'] ?? null;
                    return is_string($name) && $name !== '' ? $name : null;
                }
            }
            return null;
        };

        // Prepare location names
        $originDistrict = $this->order->sourceWarehouse->district;
        $originCity = $originDistrict ? $originDistrict->city : null;
        $originDistrictName = $resolveDistrictNameFromEkspedisiKu($originDistrict?->id, $originCity?->id) ?? ($originDistrict->name ?? null);
        $originCityLabel = $normalizeCityLabel($originCity?->name);
        $originName = $originDistrictName && $originCity
            ? strtoupper($originDistrictName . ', ' . $originCityLabel)
            : 'NOT FOUND';

        $destDistrict = $this->order->address->district;
        $destCity = $destDistrict ? $destDistrict->city : null;
        $destDistrictName = $resolveDistrictNameFromEkspedisiKu($destDistrict?->id, $destCity?->id) ?? ($destDistrict->name ?? null);
        $destCityLabel = $normalizeCityLabel($destCity?->name);
        $destName = $destDistrictName && $destCity
            ? strtoupper($destDistrictName . ', ' . $destCityLabel)
            : 'NOT FOUND';

        // Prepare items
        $items = [];
        $totalWeight = 0;
        foreach ($this->order->items as $item) {
            $items[] = [
                'name' => $item->product->display_name ?: $item->product->name,
                'qty' => (int) $item->quantity,
            ];
            $totalWeight += ($item->product->weight ?? 1000) * $item->quantity;
        }

        // Goods value must be at least 1000 for Lion Parcel
        $goodsValue = (float) $this->order->subtotal;
        if ($goodsValue <= 0) {
            $goodsValue = 1000;
        }

        // Lion requires external reference to be unique; if we reset & re-book, we must change it.
        $nextAttempt = ((int) ($this->order->ekspedisiku_booking_attempt ?? 0)) + 1;
        $bookingReference = $this->order->order_number . '-B' . str_pad((string) $nextAttempt, 2, '0', STR_PAD_LEFT);

        $carrier = $this->order->expedition->code ?? 'lion_parcel';

        // Validate service_code against /api/ongkir (when we have city IDs).
        // This prevents sending invalid service_code like JAGOPACK for Lion Parcel.
        $serviceCode = $this->order->expedition_service ?? 'REGPACK';
        $originCityId = $originCity?->id;
        $destCityId = $destCity?->id;
        $weightKg = (int) ceil(max(1, (float) ($totalWeight / 1000)));
        if ($originCityId && $destCityId) {
            $rates = $service->calculateCost($originCityId, $destCityId, $weightKg, $carrier);
            $services = $rates['data'] ?? null;
            if (is_array($services) && count($services) > 0) {
                $availableServiceCodes = array_values(array_unique(array_filter(array_map(fn ($s) => $s['service'] ?? null, $services))));
                if (!in_array($serviceCode, $availableServiceCodes, true)) {
                    $fallback = $availableServiceCodes[0] ?? 'REGPACK';
                    Log::warning('CreateShipmentBooking: Invalid service_code for carrier; falling back', [
                        'order_id' => $this->order->id,
                        'carrier' => $carrier,
                        'requested_service_code' => $serviceCode,
                        'fallback_service_code' => $fallback,
                        'available_service_codes' => $availableServiceCodes,
                        'origin_city_id' => $originCityId,
                        'destination_city_id' => $destCityId,
                        'weight_kg' => $weightKg,
                    ]);
                    $serviceCode = $fallback;
                }
            }
        } else {
            Log::warning('CreateShipmentBooking: Missing city IDs; cannot validate service_code', [
                'order_id' => $this->order->id,
                'origin_city_id' => $originCityId,
                'destination_city_id' => $destCityId,
                'carrier' => $carrier,
                'service_code' => $serviceCode,
            ]);
        }

        $payload = [
            'carrier' => $carrier,
            'shipment' => [
                'origin' => $originName,
                'destination' => $destName,
                'reference' => $bookingReference,
                'sender' => [
                    'name' => $this->order->sourceWarehouse->name,
                    // EkspedisiKu Postman example uses +62... for sender phone.
                    'phone' => $normalizePhoneE164Id($this->order->sourceWarehouse->phone) ?? '+628123456789',
                    'address' => $this->order->sourceWarehouse->address,
                ],
                'recipient' => [
                    'name' => $this->order->address->recipient_name,
                    'phone' => $normalizePhoneE164Id($this->order->user->phone ?? $this->order->address->phone) ?? '089999999999',
                    'address' => $this->order->address->address_detail,
                    'email' => $this->order->user->email ?? 'customer@gmail.com',
                ],
                'package' => [
                    'service_code' => $serviceCode,
                    'commodity_code' => 'ABR036', // Default commodity code for Multibev
                    'insurance_type' => 'free',
                    'goods_value' => $goodsValue,
                    'is_cod' => false,
                    'cod_amount' => 0,
                    'is_woodpacking' => false,
                    'pieces' => [
                        [
                            'length' => 10,
                            'width' => 10,
                            'height' => 10,
                            'weight' => max(1, (float) ($totalWeight / 1000)), // convert gram to kg, min 1kg
                        ]
                    ]
                ],
                'documents' => [
                    'cipls' => [
                        [
                            'commodity_name' => 'ABR036',
                            'item_detail' => 'Food & Beverages',
                            'quantity' => array_sum(array_column($items, 'qty')),
                            'item_price' => $goodsValue
                        ]
                    ]
                ]
            ],
            'items' => $items,
        ];

        Log::info('CreateShipmentBooking: Sending payload to EkspedisiKu', [
            'order_id' => $this->order->id,
            'payload' => $payload,
        ]);

        // Persist attempt/reference before calling upstream to avoid reusing reference on retry.
        $this->order->update([
            'ekspedisiku_booking_attempt' => $nextAttempt,
            'ekspedisiku_booking_reference' => $bookingReference,
            'ekspedisiku_booking_status' => 'sending',
            'ekspedisiku_booking_last_error' => null,
        ]);

        $result = $service->createBooking($payload);

        if ($result && isset($result['success']) && $result['success']) {
            $sttNumber = $result['data']['stt_number'] ?? null;
            $lionShipmentId = $result['data']['lion_shipment_id'] ?? null;
            
            Log::info('CreateShipmentBooking: Success', [
                'order_id' => $this->order->id,
                'stt_number' => $sttNumber,
                'lion_shipment_id' => $lionShipmentId,
            ]);

            $updates = [
                'order_status' => 'processing', // Payment confirmed, now processing for shipment
                'shipped_at' => now(),
                'ekspedisiku_booking_created_at' => now(),
                'ekspedisiku_booking_status' => 'success',
                'ekspedisiku_booking_last_error' => null,
            ];
            if ($sttNumber) {
                $updates['tracking_number'] = $sttNumber;
            }
            if ($lionShipmentId !== null && $lionShipmentId !== '') {
                $updates['ekspedisiku_shipment_id'] = (string) $lionShipmentId;
            }
            $this->order->update($updates);
        } else {
            $err = null;
            if (is_array($result)) {
                $err = $result['message'] ?? json_encode($result);
            }
            Log::error('CreateShipmentBooking: Failed', [
                'order_id' => $this->order->id,
                'response' => $result,
                'carrier' => $payload['carrier'] ?? null,
                'service_code' => $payload['shipment']['package']['service_code'] ?? null,
                'origin' => $payload['shipment']['origin'] ?? null,
                'destination' => $payload['shipment']['destination'] ?? null,
            ]);

            $this->order->update([
                'ekspedisiku_booking_status' => 'failed',
                'ekspedisiku_booking_last_error' => $err,
            ]);
        }
    }
}
