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
        $this->order->load([
            'user',
            'address.district.city',
            'address.regency',
            'sourceWarehouse.district.city',
            'sourceWarehouse.regency',
            'items.product',
            'expedition',
        ]);

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

        $extractPostalFromText = function (?string $text): string {
            $text = (string) $text;
            if ($text !== '' && preg_match('/\b(\d{5})\b/', $text, $m)) {
                return $m[1];
            }
            return '';
        };

        $resolveWarehouseSenderPostCode = function () use ($extractPostalFromText): string {
            $w = $this->order->sourceWarehouse;
            if (! $w) {
                return '';
            }
            foreach ([
                $w->postal_code,
                $w->district?->postal_code,
                $w->regency?->postal_code,
                $w->district?->city?->postal_code,
            ] as $p) {
                $p = trim((string) $p);
                if ($p !== '' && preg_match('/^\d{5}$/', $p)) {
                    return $p;
                }
            }
            return $extractPostalFromText($w->address);
        };

        $resolveRecipientPostCode = function () use ($extractPostalFromText): string {
            $a = $this->order->address;
            if (! $a) {
                return '';
            }
            foreach ([
                $a->postal_code,
                $a->district?->postal_code,
                $a->regency?->postal_code,
                $a->district?->city?->postal_code,
            ] as $p) {
                $p = trim((string) $p);
                if ($p !== '' && preg_match('/^\d{5}$/', $p)) {
                    return $p;
                }
            }
            return $extractPostalFromText($a->address_detail);
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

        $senderPostCode = $resolveWarehouseSenderPostCode();
        $recipientPostCode = $resolveRecipientPostCode();
        if ($senderPostCode === '') {
            $fallback = trim((string) config('services.ekspedisiku.default_sender_postal_code', ''));
            if ($fallback !== '' && preg_match('/^\d{5}$/', $fallback)) {
                $senderPostCode = $fallback;
                Log::info('CreateShipmentBooking: Using configured default sender post_code', [
                    'order_id' => $this->order->id,
                ]);
            }
        }
        if ($recipientPostCode === '') {
            // Try to use a general default for recipient if missing, to avoid UPSTREAM_ERROR from carriers.
            // Better to have a slightly wrong postal code (for the city) than no postal code at all which blocks the order.
            $fallback = trim((string) config('services.ekspedisiku.default_recipient_postal_code', '10110')); 
            $recipientPostCode = $fallback;
            Log::warning('CreateShipmentBooking: Recipient post_code empty; using fallback', [
                'order_id' => $this->order->id,
                'fallback' => $fallback,
            ]);
        }
        if ($senderPostCode === '') {
            Log::warning('CreateShipmentBooking: Sender post_code empty after resolve; Lion Parcel may reject booking', [
                'order_id' => $this->order->id,
                'warehouse_id' => $this->order->sourceWarehouse?->id,
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
                    'post_code' => $senderPostCode,
                ],
                'recipient' => [
                    'name' => $this->order->address->recipient_name,
                    'phone' => $normalizePhoneE164Id($this->order->user->phone ?? $this->order->address->phone) ?? '089999999999',
                    'address' => $this->order->address->address_detail,
                    'email' => $this->order->user->email ?? 'customer@gmail.com',
                    'post_code' => $recipientPostCode,
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
            // Debug summary only (full payload can be large/noisy)
            'carrier' => $payload['carrier'] ?? null,
            'reference' => $payload['shipment']['reference'] ?? null,
            'service_code' => $payload['shipment']['package']['service_code'] ?? null,
            'origin' => $payload['shipment']['origin'] ?? null,
            'destination' => $payload['shipment']['destination'] ?? null,
            'sender_post_code' => $payload['shipment']['sender']['post_code'] ?? null,
            'recipient_post_code' => $payload['shipment']['recipient']['post_code'] ?? null,
            'items_count' => is_array($payload['items'] ?? null) ? count($payload['items']) : null,
        ]);

        Log::debug('CreateShipmentBooking: Payload snapshot', [
            'order_id' => $this->order->id,
            'shipment' => [
                'reference' => $payload['shipment']['reference'] ?? null,
                'sender' => [
                    'name' => $payload['shipment']['sender']['name'] ?? null,
                    'phone' => $payload['shipment']['sender']['phone'] ?? null,
                    'post_code' => $payload['shipment']['sender']['post_code'] ?? null,
                ],
                'recipient' => [
                    'name' => $payload['shipment']['recipient']['name'] ?? null,
                    'phone' => $payload['shipment']['recipient']['phone'] ?? null,
                    'post_code' => $payload['shipment']['recipient']['post_code'] ?? null,
                ],
                'package' => [
                    'service_code' => $payload['shipment']['package']['service_code'] ?? null,
                    'goods_value' => $payload['shipment']['package']['goods_value'] ?? null,
                    'pieces' => $payload['shipment']['package']['pieces'] ?? null,
                ],
            ],
        ]);

        // Persist attempt/reference before calling upstream to avoid reusing reference on retry.
        $this->order->update([
            'ekspedisiku_booking_attempt' => $nextAttempt,
            'ekspedisiku_booking_reference' => $bookingReference,
            'ekspedisiku_booking_status' => 'sending',
            'ekspedisiku_booking_last_error' => null,
        ]);

        $result = $service->createBooking($payload);

        Log::debug('CreateShipmentBooking: Upstream response received', [
            'order_id' => $this->order->id,
            'has_result' => $result !== null,
            'result_keys' => is_array($result) ? array_keys($result) : null,
            'success' => is_array($result) ? ($result['success'] ?? null) : null,
            'message' => is_array($result) ? ($result['message'] ?? null) : null,
            'data_keys' => (is_array($result) && is_array($result['data'] ?? null)) ? array_keys($result['data']) : null,
            'data' => (is_array($result) && is_array($result['data'] ?? null)) ? $result['data'] : null,
        ]);

        if ($result && isset($result['success']) && $result['success']) {
            $data = is_array($result['data'] ?? null) ? ($result['data'] ?? []) : [];

            // EkspedisiKu/Lion response variants seen in the wild:
            // - shipment_id (Lion middleware)
            // - lion_shipment_id (legacy naming)
            // - stt_no / stt_number (resi)
            $sttNumber = $data['stt_number']
                ?? $data['stt_no']
                ?? $data['sttNo']
                ?? null;

            $lionShipmentId = $data['lion_shipment_id']
                ?? $data['shipment_id']
                ?? $data['shipmentId']
                ?? null;

            // Some EkspedisiKu implementations only return internal id + stt_number on create.
            // For Lion pickup we need the Lion shipment_id; resolve it from EkspedisiKu shipment detail.
            if (($lionShipmentId === null || $lionShipmentId === '') && isset($data['id'])) {
                $detail = $service->getShipment((string) $data['id']);
                $resolved = data_get($detail, 'data.lion_shipment_id')
                    ?? data_get($detail, 'data.shipment_id')
                    ?? data_get($detail, 'data.shipmentId');

                if (is_string($resolved) && $resolved !== '') {
                    $lionShipmentId = $resolved;
                    Log::info('CreateShipmentBooking: Resolved lion shipment_id via getShipment', [
                        'order_id' => $this->order->id,
                        'shipment_internal_id' => (string) $data['id'],
                        'lion_shipment_id' => $lionShipmentId,
                    ]);
                } else {
                    Log::warning('CreateShipmentBooking: Could not resolve lion shipment_id via getShipment', [
                        'order_id' => $this->order->id,
                        'shipment_internal_id' => (string) $data['id'],
                        'detail' => $detail,
                    ]);
                }
            }
            // Lion v2 shipment/create often returns shipment_id only; STT/resi may appear later after pickup/print.
            $trackingForUi = (is_string($sttNumber) && $sttNumber !== '')
                ? $sttNumber
                : (is_string($lionShipmentId) && $lionShipmentId !== '' ? (string) $lionShipmentId : null);

            Log::info('CreateShipmentBooking: Success', [
                'order_id' => $this->order->id,
                'stt_number' => $sttNumber,
                'lion_shipment_id' => $lionShipmentId,
                'tracking_number_set' => $trackingForUi,
            ]);

            if ($lionShipmentId === null || $lionShipmentId === '') {
                Log::warning('CreateShipmentBooking: Success but shipment_id missing in response data', [
                    'order_id' => $this->order->id,
                    'data_keys' => array_keys($data),
                    'data' => $data,
                ]);
            }

            $updates = [
                'order_status' => 'processing', // Payment confirmed, now processing for shipment
                'shipped_at' => now(),
                'ekspedisiku_booking_created_at' => now(),
                'ekspedisiku_booking_status' => 'success',
                'ekspedisiku_booking_last_error' => null,
            ];
            if ($trackingForUi !== null) {
                $updates['tracking_number'] = $trackingForUi;
            }
            if ($lionShipmentId !== null && $lionShipmentId !== '') {
                $updates['ekspedisiku_shipment_id'] = (string) $lionShipmentId;
            }
            $this->order->update($updates);

            $this->order->refresh();
            Log::debug('CreateShipmentBooking: Order updated after success', [
                'order_id' => $this->order->id,
                'ekspedisiku_booking_status' => $this->order->ekspedisiku_booking_status,
                'ekspedisiku_shipment_id' => $this->order->ekspedisiku_shipment_id,
                'tracking_number' => $this->order->tracking_number,
                'booking_reference' => $this->order->ekspedisiku_booking_reference,
                'booking_attempt' => $this->order->ekspedisiku_booking_attempt,
            ]);
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
