<?php

namespace App\Jobs;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\JubelioService;
use App\Services\ZohoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOrderToJubelio implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;

    protected Order $order;

    public function uniqueId(): string
    {
        return $this->order->id;
    }

    public function __construct(Order $order)
    {
        $this->order = $order->load([
            'user',
            'items.product',
            'address.province',
            'address.regency',
            'address.district',
            'sourceWarehouse',
            'expedition',
        ]);
    }

    public function handle(JubelioService $jubelio): void
    {
        $this->order->loadMissing('sourceWarehouse');
        $warehouse = $this->order->sourceWarehouse;
        $hasJubelioHub = $warehouse && (
            ! empty($warehouse->kode_hub) ||
            in_array('jubelio', is_array($warehouse->sync_sources) ? $warehouse->sync_sources : [], true)
        );

        if (! $this->order->shouldSyncToJubelio() && ! $hasJubelioHub) {
            Log::info('SyncOrderToJubelio: skipped (bukan order hub/online)', [
                'order_id' => $this->order->id,
                'order_type' => $this->order->order_type,
            ]);

            return;
        }

        if ($this->order->jubelio_salesorder_id) {
            Log::info('SyncOrderToJubelio: already synced', [
                'order_id' => $this->order->id,
                'jubelio_salesorder_id' => $this->order->jubelio_salesorder_id,
            ]);

            // Pastikan data juga langsung dikirim ke Zoho jika belum terkirim
            if (ZohoService::isConfigured() && ! $this->order->zoho_salesorder_id) {
                try {
                    app(ZohoService::class)->createOrderToPaymentFlow($this->order->fresh());
                    Log::channel('jubelio_sales_order')->info('SyncOrderToJubelio: also synced directly to Zoho for existing Jubelio SO', [
                        'order_id' => $this->order->id,
                        'zoho_salesorder_id' => $this->order->fresh()->zoho_salesorder_id,
                    ]);
                } catch (\Throwable $ze) {
                    Log::channel('jubelio_sales_order')->error('SyncOrderToJubelio: failed syncing to Zoho', [
                        'order_id' => $this->order->id,
                        'message' => $ze->getMessage(),
                    ]);
                }
            }

            return;
        }

        $user = $this->order->user;
        $address = $this->order->address;

        if (! $user || ! $address) {
            Log::error('SyncOrderToJubelio: missing user or address', ['order_id' => $this->order->id]);

            return;
        }

        Log::channel('jubelio_sales_order')->info('SyncOrderToJubelio: start', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
        ]);

        try {
            $token = $jubelio->token();
            $prepared = $this->prepareSalesOrderPayload($jubelio, $token);
            if (! ($prepared['ok'] ?? false)) {
                Log::error('SyncOrderToJubelio: payload preparation failed', [
                    'order_id' => $this->order->id,
                    'error' => $prepared['error'] ?? null,
                    'issues' => $prepared['issues'] ?? null,
                ]);

                return;
            }

            $payload = $prepared['payload'];

            Log::channel('jubelio_sales_order')->debug('SyncOrderToJubelio: payload ready', [
                'order_id' => $this->order->id,
                'payload' => $payload,
                'meta' => $prepared['meta'] ?? null,
            ]);

            $result = $jubelio->createSalesOrder($token, $payload);
            $salesOrderId = (int) ($result['id'] ?? $result['salesorder_id'] ?? 0);

            if ($salesOrderId <= 0) {
                Log::error('SyncOrderToJubelio: create response missing id', [
                    'order_id' => $this->order->id,
                    'response' => $result,
                ]);

                return;
            }

            $salesOrderNo = null;
            $detail = $jubelio->getSalesOrder($token, $salesOrderId);
            if ($detail) {
                $salesOrderNo = $detail['salesorder_no'] ?? null;
            }

            $this->order->update([
                'jubelio_salesorder_id' => $salesOrderId,
                'jubelio_salesorder_no' => is_string($salesOrderNo) ? $salesOrderNo : null,
            ]);

            Log::channel('jubelio_sales_order')->info('SyncOrderToJubelio: success', [
                'order_id' => $this->order->id,
                'jubelio_salesorder_id' => $salesOrderId,
                'jubelio_salesorder_no' => $salesOrderNo,
            ]);

            // Kirim data langsung ke Zoho setelah berhasil dikirim ke Jubelio
            try {
                if (ZohoService::isConfigured()) {
                    app(ZohoService::class)->createOrderToPaymentFlow($this->order->fresh());
                    Log::channel('jubelio_sales_order')->info('SyncOrderToJubelio: also synced directly to Zoho', [
                        'order_id' => $this->order->id,
                        'zoho_salesorder_id' => $this->order->fresh()->zoho_salesorder_id,
                        'zoho_invoice_id' => $this->order->fresh()->zoho_invoice_id,
                        'zoho_payment_id' => $this->order->fresh()->zoho_payment_id,
                    ]);
                }
            } catch (\Throwable $ze) {
                Log::channel('jubelio_sales_order')->error('SyncOrderToJubelio: failed syncing to Zoho', [
                    'order_id' => $this->order->id,
                    'message' => $ze->getMessage(),
                ]);
                Log::error('SyncOrderToJubelio: failed syncing to Zoho', [
                    'order_id' => $this->order->id,
                    'message' => $ze->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('jubelio_sales_order')->error('SyncOrderToJubelio: failed', [
                'order_id' => $this->order->id,
                'message' => $e->getMessage(),
            ]);
            Log::error('SyncOrderToJubelio: failed', [
                'order_id' => $this->order->id,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Siapkan payload SO Jubelio tanpa POST (untuk debug / artisan test).
     *
     * @return array<string, mixed>
     */
    public function prepareSalesOrderPayload(JubelioService $jubelio, ?string $token = null): array
    {
        $this->order->refresh();
        $this->order->loadMissing([
            'user',
            'items.product',
            'address.province',
            'address.regency',
            'address.district',
            'sourceWarehouse',
            'expedition',
        ]);

        $user = $this->order->user;
        $address = $this->order->address;

        if (! $user || ! $address) {
            return ['ok' => false, 'error' => 'User atau alamat pesanan tidak ada.'];
        }

        try {
            $token = $token ?: $jubelio->token();
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Login Jubelio gagal: ' . $e->getMessage()];
        }

        $locationId = $this->resolveLocationId($jubelio, $token);
        if (! $locationId) {
            return [
                'ok' => false,
                'error' => 'location_id tidak ditemukan',
                'issues' => [
                    'warehouse_id' => $this->order->source_warehouse_id,
                    'kode_hub' => $this->order->sourceWarehouse?->kode_hub,
                    'hint' => 'Sync hub Jubelio atau set JUBELIO_DEFAULT_LOCATION_ID',
                ],
            ];
        }

        $contact = $this->resolveContact($jubelio, $token, $user);
        if (! $contact) {
            return [
                'ok' => false,
                'error' => 'contact_id tidak ditemukan',
                'issues' => ['user_id' => $user->id, 'email' => $user->email],
            ];
        }

        $linesBuild = $this->buildSalesOrderItems($jubelio, $token, $locationId);
        if (! ($linesBuild['ok'] ?? false)) {
            return [
                'ok' => false,
                'error' => 'Baris item tidak valid',
                'issues' => $linesBuild['issues'] ?? [],
            ];
        }

        $salesman = $this->resolveSalesman($jubelio, $token);
        $payload = $this->buildSalesOrderPayload(
            $contact,
            $locationId,
            $address,
            $linesBuild['items'],
            $linesBuild['sub_total'],
            $linesBuild['total_tax'],
            $linesBuild['total_discount'] ?? 0.0,
            $salesman
        );

        return [
            'ok' => true,
            'endpoint' => rtrim((string) config('jubelio.base_url'), '/') . '/sales/orders/',
            'method' => 'POST',
            'payload' => $payload,
            'meta' => [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'location_id' => $locationId,
                'contact_id' => $contact['contact_id'],
                'customer_name' => $contact['customer_name'],
                'kode_hub' => $this->order->sourceWarehouse?->kode_hub,
                'item_count' => count($linesBuild['items']),
            ],
        ];
    }

    /**
     * Debug: payload + optional POST ke Jubelio (tanpa update DB kecuali --sync di command).
     *
     * @return array<string, mixed>
     */
    public function debugSalesOrderToJubelio(JubelioService $jubelio, bool $executeRequest): array
    {
        $prepared = $this->prepareSalesOrderPayload($jubelio);
        if (! ($prepared['ok'] ?? false)) {
            return $prepared;
        }

        if ($this->order->jubelio_salesorder_id) {
            return $prepared + [
                'skipped_http' => true,
                'note' => 'Order sudah punya jubelio_salesorder_id; POST tidak dijalankan.',
                'jubelio_salesorder_id' => $this->order->jubelio_salesorder_id,
                'jubelio_salesorder_no' => $this->order->jubelio_salesorder_no,
                'response' => null,
                'dry_run' => ! $executeRequest,
            ];
        }

        if (! $executeRequest) {
            return $prepared + [
                'dry_run' => true,
                'response' => null,
                'note' => 'Dry run: payload siap. Tambahkan --execute untuk POST ke Jubelio.',
            ];
        }

        $token = $jubelio->token();
        $result = $jubelio->createSalesOrder($token, $prepared['payload']);
        $salesOrderId = (int) ($result['id'] ?? $result['salesorder_id'] ?? 0);
        $salesOrderNo = null;

        if ($salesOrderId > 0) {
            $detail = $jubelio->getSalesOrder($token, $salesOrderId);
            $salesOrderNo = is_array($detail) ? ($detail['salesorder_no'] ?? null) : null;
        }

        return $prepared + [
            'dry_run' => false,
            'response' => $result,
            'jubelio_salesorder_id' => $salesOrderId > 0 ? $salesOrderId : null,
            'jubelio_salesorder_no' => $salesOrderNo,
            'note' => 'POST dijalankan; database order tidak diubah dari debug ini (gunakan --sync untuk simpan).',
        ];
    }

    private function resolveLocationId(JubelioService $jubelio, string $token): ?int
    {
        $default = config('jubelio.sales_order.default_location_id');
        if ($default) {
            return (int) $default;
        }

        $kodeHub = $this->order->sourceWarehouse?->kode_hub;
        if ($kodeHub) {
            $found = $jubelio->findLocationIdByCode($token, $kodeHub);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @return array{contact_id: int, customer_name: string}|null
     */
    private function resolveContact(JubelioService $jubelio, string $token, User $user): ?array
    {
        $defaultId = config('jubelio.sales_order.default_contact_id');
        if ($defaultId) {
            return [
                'contact_id' => (int) $defaultId,
                'customer_name' => $user->name ?: 'Pelanggan',
            ];
        }

        $searchTerms = array_filter([
            $user->email,
            $user->phone,
            $user->name,
        ]);

        foreach ($searchTerms as $term) {
            $customers = $jubelio->fetchCustomers($token, (string) $term);
            foreach ($customers as $customer) {
                $email = strtolower(trim((string) ($customer['email'] ?? '')));
                if ($user->email && $email === strtolower(trim((string) $user->email))) {
                    return [
                        'contact_id' => (int) $customer['contact_id'],
                        'customer_name' => (string) ($customer['contact_name'] ?? $user->name),
                    ];
                }
            }
        }
        return [
            'contact_id' => (int) (config('jubelio.sales_order.default_contact_id') ?: -1),
            'customer_name' => (string) ($user->name ?: 'Pelanggan Umum'),
        ];
    }

    /**
     * @return array{ok: bool, items?: array<int, array<string, mixed>>, sub_total?: float, total_tax?: float, issues?: array<int, string>}
     */
    /**
     * @return array{ok: bool, items?: array<int, array<string, mixed>>, sub_total?: float, total_tax?: float, total_discount?: float, issues?: array<int, string>}
     */
    private function buildSalesOrderItems(JubelioService $jubelio, string $token, int $locationId): array
    {
        $shipperMap = [
            'Sicepat Ekspres'      => 'SiCepat',
            'J&T Express'          => 'J&T',
            'POS Indonesia'        => 'Pos Indonesia',
            'AnterAja'             => 'Anteraja',
            'Diantar Ketempat'     => 'Kirim Sendiri',
            'Pengambilan Ditempat' => 'Ambil Sendiri',
        ];
        $rawShipper = $this->order->expedition?->name ?? 'Internal';
        $courierName = $shipperMap[$rawShipper] ?? $rawShipper;
        $service = trim((string) ($this->order->expedition_service ?? ''));

        if ($courierName === 'JNE') {
            $upperService = strtoupper($service);
            if (str_starts_with($upperService, 'REG') || str_starts_with($upperService, 'CTC')) {
                $shipper = 'JNE REG';
            } elseif (str_starts_with($upperService, 'JTR')) {
                $shipper = 'JNE JTR';
            } elseif (str_starts_with($upperService, 'YES')) {
                $shipper = 'JNE YES';
            } elseif (str_starts_with($upperService, 'OKE')) {
                $shipper = 'JNE OKE';
            } elseif ($service !== '') {
                $shipper = 'JNE ' . $service;
            } else {
                $shipper = 'JNE REG';
            }
        } elseif ($courierName === 'SiCepat' && $service !== '') {
            $shipper = 'SiCepat ' . $service;
        } elseif ($courierName === 'J&T' && $service !== '') {
            $shipper = 'J&T ' . $service;
        } else {
            $shipper = $courierName;
        }
        $items = [];
        $issues = [];
        $subTotal = 0.0;
        $totalTax = 0.0;

        // 1. Kumpulkan baris item dan hitung subtotal katalog bruto
        $itemRows = [];
        $grossSubtotal = 0.0;

        foreach ($this->order->items as $item) {
            $itemCode = trim((string) ($item->product?->code ?? ''));
            if ($itemCode === '') {
                $issues[] = "Order item {$item->id} tidak punya product code";
                continue;
            }

            $jubelioItem = $jubelio->findItemToSellByCode($token, $locationId, $itemCode);
            if (! $jubelioItem) {
                $issues[] = "Item code {$itemCode} tidak ditemukan di Jubelio location {$locationId}";
                continue;
            }

            $price = (float) $item->orderedPriceWithTax();
            if ($price <= 0) {
                $price = (float) ($jubelioItem['sell_price'] ?? $item->product?->price ?? 0);
            }
            if ($price <= 0) {
                $issues[] = "Harga invalid untuk item {$itemCode}";
                continue;
            }

            $qty = (int) $item->quantity;
            $lineGross = round($price * $qty, 2);
            $grossSubtotal += $lineGross;

            $itemRows[] = [
                'item' => $item,
                'jubelio_item' => $jubelioItem,
                'item_code' => $itemCode,
                'price' => $price,
                'qty' => $qty,
                'line_gross' => $lineGross,
            ];
        }

        if ($issues !== [] || $itemRows === []) {
            return ['ok' => false, 'issues' => $issues];
        }

        // 2. Tentukan total diskon (baik diskon eksplisit maupun diskon tiering keranjang)
        $orderSubtotal = (float) ($this->order->subtotal ?? 0);
        $explicitDiscount = (float) ($this->order->discount_amount ?? 0);
        $tierDiscount = ($orderSubtotal > 0 && $grossSubtotal > $orderSubtotal)
            ? ($grossSubtotal - $orderSubtotal)
            : 0.0;
        $totalDiscount = max($explicitDiscount, $tierDiscount);
        $totalDiscount = round($totalDiscount, 2);

        // 3. Alokasikan diskon ke masing-masing item
        $allocatedDiscountTotal = 0.0;
        $count = count($itemRows);

        foreach ($itemRows as $idx => $row) {
            $item = $row['item'];
            $jubelioItem = $row['jubelio_item'];
            $itemCode = $row['item_code'];
            $price = $row['price'];
            $qty = $row['qty'];
            $lineGross = $row['line_gross'];

            $lineDisc = 0.0;
            if ($totalDiscount > 0.5 && $grossSubtotal > 0) {
                if ($idx === $count - 1) {
                    $lineDisc = max(0.0, round($totalDiscount - $allocatedDiscountTotal, 2));
                } else {
                    $lineDisc = round(($lineGross / $grossSubtotal) * $totalDiscount, 2);
                    $allocatedDiscountTotal += $lineDisc;
                }
            }

            $rate = (float) ($jubelioItem['rate'] ?? 0);
            $amount = max(0.0, round($lineGross - $lineDisc, 2));
            $taxAmount = $rate > 0 ? round($amount * $rate / 100, 2) : 0.0;
            $description = (string) ($jubelioItem['item_short_name'] ?? $jubelioItem['item_name'] ?? $item->product?->display_name ?? $itemCode);
            $discPercent = $lineGross > 0 ? round(($lineDisc / $lineGross) * 100, 2) : 0;

            $items[] = [
                'salesorder_detail_id' => 0,
                'item_id' => (int) $jubelioItem['item_id'],
                'serial_no' => null,
                'description' => $description,
                'tax_id' => (int) ($jubelioItem['sell_tax_id'] ?? 1),
                'price' => (int) round($price),
                'unit' => (string) ($jubelioItem['sell_unit'] ?? $item->product?->unit ?? 'Buah'),
                'qty_in_base' => $qty,
                'disc' => $discPercent,
                'disc_amount' => (int) round($lineDisc),
                'tax_amount' => (int) round($taxAmount),
                'amount' => (int) round($amount),
                'location_id' => $locationId,
                'shipper' => $shipper,
                'channel_order_detail_id' => null,
            ];

            $subTotal += $lineGross;
            $totalTax += $taxAmount;
        }

        return [
            'ok' => true,
            'items' => $items,
            'sub_total' => $subTotal,
            'total_tax' => $totalTax,
            'total_discount' => $totalDiscount,
        ];
    }

    /**
     * @param  array{contact_id: int, customer_name: string}  $contact
     * @param  array<int, array<string, mixed>>  $items
     */
    private function buildSalesOrderPayload(
        array $contact,
        int $locationId,
        Address $address,
        array $items,
        float $subTotal,
        float $totalTax,
        float $totalDiscount = 0.0,
        ?array $salesman = null
    ): array {
        $shippingCost = (float) ($this->order->shipping_cost ?? 0);
        $grandTotal = round($subTotal + $totalTax + $shippingCost - $totalDiscount, 2);
        $isPaid = $this->order->payment_status === 'paid';
        $transactionDate = $this->order->created_at->copy()->utc()->format('Y-m-d\TH:i:s.000\Z');

        $baseNote = trim((string) ($this->order->notes ?: $this->order->order_number));
        if ($salesman && !empty($salesman['sales_code'])) {
            $salesTag = '[Sales: ' . $salesman['sales_code'] . (!empty($salesman['sales_name']) ? ' - ' . $salesman['sales_name'] : '') . ']';
            if (!str_contains($baseNote, $salesTag)) {
                $baseNote = trim($baseNote . ' ' . $salesTag);
            }
        }

        return [
            'salesorder_id' => 0,
            'salesorder_no' => '[auto]',
            'contact_id' => $contact['contact_id'],
            'customer_name' => $contact['customer_name'],
            'transaction_date' => $transactionDate,
            'is_tax_included' => false,
            'note' => $baseNote,
            'sub_total' => (int) round($subTotal),
            'total_disc' => (int) round($totalDiscount),
            'total_tax' => (int) round($totalTax),
            'grand_total' => (int) round($grandTotal),
            'ref_no' => $this->order->order_number,
            'location_id' => $locationId,
            'source' => (int) config('jubelio.sales_order.source', 1),
            'is_canceled' => false,
            'cancel_reason' => '',
            'cancel_reason_detail' => '',
            'channel_status' => $isPaid ? 'Paid' : 'Pending',
            'shipping_cost' => (int) round($shippingCost),
            'insurance_cost' => 0,
            'is_paid' => $isPaid,
            'shipping_full_name' => $address->recipient_name ?? $contact['customer_name'],
            'shipping_phone' => $this->normalizePhone($address->phone ?? $this->order->user?->phone),
            'shipping_address' => $address->address_detail ?? $address->full_address ?? '-',
            'shipping_area' => $address->district?->name ?? '',
            'shipping_city' => $address->regency?->name ?? '',
            'shipping_province' => $address->province?->name ?? '',
            'shipping_post_code' => $this->resolvePostalCode($address),
            'shipping_country' => 'Indonesia',
            'add_disc' => 0,
            'add_fee' => 0,
            'salesmen_id' => $salesman['salesmen_id'] ?? null,
            'store_id' => null,
            'service_fee' => 0,
            'payment_method' => $this->mapPaymentMethod(),
            'items' => $items,
        ];
    }

    private function resolveSalesman(JubelioService $jubelio, string $token): ?array
    {
        $salesCode = trim((string) ($this->order->sales_code ?? ''));
        if ($salesCode === '') {
            return null;
        }

        $salesUser = User::where('sales_code', $salesCode)->first();
        $salesName = $salesUser?->name;
        $salesEmail = $salesUser?->email;

        $cacheKey = 'jubelio_salesmen_list';
        $salesmen = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(1), function () use ($jubelio, $token) {
            $res = \Illuminate\Support\Facades\Http::withToken($token)
                ->get(rtrim((string) config('jubelio.base_url'), '/') . '/contacts/salesman/', [
                    'page' => 1,
                    'pageSize' => 100,
                ]);
            return $res->json('data') ?? [];
        });

        foreach ($salesmen as $s) {
            $cId = (int) ($s['contact_id'] ?? $s['salesmen_id'] ?? 0);
            $sEmail = strtolower(trim((string) ($s['email'] ?? '')));
            $sName = strtolower(trim((string) ($s['contact_name'] ?? '')));

            if ($salesEmail && $sEmail !== '' && $sEmail === strtolower(trim($salesEmail))) {
                return ['salesmen_id' => $cId, 'sales_name' => (string) $s['contact_name'], 'sales_code' => $salesCode];
            }
            if ($salesName && $sName !== '' && (stripos($sName, strtolower(trim($salesName))) !== false || stripos(strtolower(trim($salesName)), $sName) !== false)) {
                return ['salesmen_id' => $cId, 'sales_name' => (string) $s['contact_name'], 'sales_code' => $salesCode];
            }
        }

        return $salesName ? ['salesmen_id' => null, 'sales_name' => $salesName, 'sales_code' => $salesCode] : ['salesmen_id' => null, 'sales_name' => null, 'sales_code' => $salesCode];
    }

    private function mapCourierId(): ?int
    {
        $code = strtolower(trim((string) $this->order->expedition?->code));
        return match ($code) {
            'jne' => 1,
            'jnt' => 2,
            'grab' => 3,
            'sicepat' => 4,
            'gosend' => 5,
            'pos' => 7,
            'tiki' => 9,
            'self_pickup' => 36, // Ambil Sendiri
            'kurir_toko' => 17, // Kirim Sendiri
            'anteraja' => 100,
            'lion_parcel' => 113,
            'lalamove' => 17, // Lalamove tidak ada di Jubelio, fallback ke Kirim Sendiri
            default => null, // Biarkan kosong/null jika tidak terdaftar
        };
    }

    private function mapPaymentMethod(): ?string
    {
        return match ($this->order->payment_method) {
            'faspay' => 'online',
            'manual_transfer' => 'transfer',
            'term_of_payment' => 'credit',
            default => $this->order->payment_method,
        };
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return $digits;
    }

    private function resolvePostalCode(Address $address): ?string
    {
        foreach ([
            $address->postal_code,
            $address->district?->postal_code,
            $address->regency?->postal_code,
        ] as $postal) {
            $postal = trim((string) $postal);
            if ($postal !== '' && preg_match('/^\d{5}$/', $postal)) {
                return $postal;
            }
        }

        if ($address->address_detail && preg_match('/\b(\d{5})\b/', (string) $address->address_detail, $m)) {
            return $m[1];
        }

        return null;
    }
}
