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

        // Prepare location names
        $originDistrict = $this->order->sourceWarehouse->district;
        $originCity = $originDistrict ? $originDistrict->city : null;
        $originName = $originDistrict && $originCity 
            ? strtoupper($originDistrict->name . ', ' . str_replace(['KAB. ', 'KOTA '], '', $originCity->name))
            : 'NOT FOUND';

        $destDistrict = $this->order->address->district;
        $destCity = $destDistrict ? $destDistrict->city : null;
        $destName = $destDistrict && $destCity 
            ? strtoupper($destDistrict->name . ', ' . str_replace(['KAB. ', 'KOTA '], '', $destCity->name))
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

        $payload = [
            'carrier' => $this->order->expedition->code ?? 'lion_parcel',
            'shipment' => [
                'origin' => $originName,
                'destination' => $destName,
                'reference' => $this->order->order_number,
                'sender' => [
                    'name' => $this->order->sourceWarehouse->name,
                    'phone' => $this->order->sourceWarehouse->phone ?? '08123456789',
                    'address' => $this->order->sourceWarehouse->address,
                ],
                'recipient' => [
                    'name' => $this->order->address->recipient_name,
                    'phone' => $this->order->user->phone ?? $this->order->address->phone,
                    'address' => $this->order->address->address_detail,
                    'email' => $this->order->user->email ?? 'customer@gmail.com',
                ],
                'package' => [
                    'service_code' => $this->order->expedition_service ?? 'REGPACK',
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

        $result = $service->createBooking($payload);

        if ($result && isset($result['success']) && $result['success']) {
            $sttNumber = $result['data']['stt_number'] ?? null;
            
            Log::info('CreateShipmentBooking: Success', [
                'order_id' => $this->order->id,
                'stt_number' => $sttNumber,
            ]);

            if ($sttNumber) {
                $this->order->update([
                    'tracking_number' => $sttNumber,
                    'order_status' => 'processing', // Payment confirmed, now processing for shipment
                    'shipped_at' => now(),
                ]);
            }
        } else {
            Log::error('CreateShipmentBooking: Failed', [
                'order_id' => $this->order->id,
                'response' => $result,
            ]);
        }
    }
}
