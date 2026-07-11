<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class JubelioService
{
    private ?string $cachedToken = null;

    protected function http(?string $token = null)
    {
        $request = Http::retry(3, 1000)->timeout(30);
        if ($token) {
            return $request->withToken($token);
        }
        return $request;
    }


    private function baseUrl(): string
    {
        return rtrim((string) config('jubelio.base_url', 'https://api2.jubelio.com'), '/');
    }

    public function token(): string
    {
        if ($this->cachedToken) {
            return $this->cachedToken;
        }

        $this->cachedToken = $this->login();

        return $this->cachedToken;
    }

    public function login(): string
    {
        $email = config('jubelio.email') ?: env('JUBELIO_EMAIL');
        $password = config('jubelio.password') ?: env('JUBELIO_PASSWORD');

        if (!$email || !$password) {
            throw new RuntimeException('Kredensial Jubelio tidak ditemukan di file .env');
        }

        $response = $this->http()->post($this->baseUrl() . '/login', [
            'email' => $email,
            'password' => $password,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gagal login ke Jubelio. Periksa kembali email dan password di .env');
        }

        $token = $response->json('token');
        if (!$token) {
            throw new RuntimeException('Token Jubelio tidak ditemukan dalam response.');
        }

        return $token;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchAllLocations(string $token): array
    {
        $locations = [];
        $page = 1;

        do {
            $response = $this->http($token)->get($this->baseUrl() . '/locations/', [
                'page' => $page,
                'pageSize' => 200,
            ]);

            if (!$response->successful()) {
                throw new RuntimeException('Gagal mengambil lokasi Jubelio: HTTP ' . $response->status());
            }

            $data = $response->json();
            $batch = $data['data'] ?? [];
            $locations = array_merge($locations, $batch);
            $totalCount = (int) ($data['totalCount'] ?? count($locations));
            $page++;
        } while (count($locations) < $totalCount && count($batch) > 0);

        return $locations;
    }

    /**
     * @return array<int, int>
     */
    public function fetchAllItemIds(string $token): array
    {
        $itemIds = [];
        $page = 1;

        do {
            $response = $this->http($token)->get($this->baseUrl() . '/inventory/items/', [
                'page' => $page,
                'pageSize' => 200,
            ]);

            if (!$response->successful()) {
                throw new RuntimeException('Gagal mengambil produk Jubelio: HTTP ' . $response->status());
            }

            $batch = $response->json('data') ?? [];

            foreach ($batch as $group) {
                foreach ($group['variants'] ?? [] as $variant) {
                    if (isset($variant['item_id'])) {
                        $itemIds[] = (int) $variant['item_id'];
                    }
                }
            }

            $page++;
        } while (count($batch) > 0);

        return array_values(array_unique($itemIds));
    }

    /**
     * @param  array<int, int>  $itemIds
     * @return array<int, array<string, mixed>>
     */
    public function fetchStocksByItemIds(string $token, array $itemIds, int $chunkSize = 50): array
    {
        $items = [];

        foreach (array_chunk($itemIds, $chunkSize) as $chunk) {
            $response = $this->http($token)
                ->timeout(120)
                ->post($this->baseUrl() . '/inventory/items/all-stocks/', [
                    'ids' => array_values($chunk),
                ]);

            if (!$response->successful()) {
                Log::warning('Jubelio all-stocks chunk failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                    'ids_count' => count($chunk),
                ]);
                continue;
            }

            $batch = $response->json('data') ?? [];
            $items = array_merge($items, $batch);
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchCustomers(string $token, ?string $query = null, int $pageSize = 200): array
    {
        $customers = [];
        $page = 1;

        do {
            $params = [
                'page' => $page,
                'pageSize' => $pageSize,
            ];
            if ($query) {
                $params['q'] = $query;
            }

            $response = $this->http($token)->get($this->baseUrl() . '/contacts/customers/', $params);

            if (!$response->successful()) {
                throw new RuntimeException('Gagal mengambil customer Jubelio: HTTP ' . $response->status());
            }

            $data = $response->json();
            $batch = $data['data'] ?? [];
            $customers = array_merge($customers, $batch);
            $totalCount = (int) ($data['totalCount'] ?? count($customers));
            $page++;
        } while (count($customers) < $totalCount && count($batch) > 0);

        return $customers;
    }

    public function findLocationIdByCode(string $token, ?string $locationCode): ?int
    {
        if (! $locationCode) {
            return null;
        }

        foreach ($this->fetchAllLocations($token) as $location) {
            $code = (string) ($location['location_code'] ?? '');
            if ($code !== '' && strcasecmp($code, $locationCode) === 0) {
                return (int) ($location['location_id'] ?? 0) ?: null;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchItemsToSell(string $token, int $locationId): array
    {
        $response = $this->http($token)->get($this->baseUrl() . "/inventory/items/to-sell/{$locationId}");

        if (!$response->successful()) {
            throw new RuntimeException('Gagal mengambil item to-sell Jubelio: HTTP ' . $response->status());
        }

        $body = $response->json();
        if (! is_array($body)) {
            return [];
        }

        if (isset($body['data']) && is_array($body['data'])) {
            return $body['data'];
        }

        return array_is_list($body) ? $body : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findItemToSellByCode(string $token, int $locationId, string $itemCode): ?array
    {
        $needle = strtoupper(trim($itemCode));
        foreach ($this->fetchItemsToSell($token, $locationId) as $item) {
            if (strtoupper(trim((string) ($item['item_code'] ?? ''))) === $needle) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createSalesOrder(string $token, array $payload): array
    {
        $url = $this->baseUrl() . '/sales/orders/';

        Log::channel('jubelio_sales_order')->debug('JubelioService.createSalesOrder request', [
            'url' => $url,
            'payload' => $payload,
        ]);

        $response = $this->http($token)
            ->timeout(120)
            ->post($url, $payload);

        $body = $response->json();

        Log::channel('jubelio_sales_order')->debug('JubelioService.createSalesOrder response', [
            'status' => $response->status(),
            'body' => $body,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Gagal membuat Sales Order Jubelio: HTTP ' . $response->status() . ' — ' . substr($response->body(), 0, 500)
            );
        }

        return is_array($body) ? $body : ['raw' => $body];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchAllItemGroups(string $token): array
    {
        $groups = [];
        $page = 1;

        do {
            $response = $this->http($token)->get($this->baseUrl() . '/inventory/items/', [
                'page' => $page,
                'pageSize' => 200,
            ]);

            if (!$response->successful()) {
                throw new RuntimeException('Gagal mengambil produk Jubelio: HTTP ' . $response->status());
            }

            $data = $response->json();
            $batch = $data['data'] ?? [];
            $groups = array_merge($groups, $batch);
            $totalCount = (int) ($data['totalCount'] ?? count($groups));
            $page++;
        } while (count($groups) < $totalCount && count($batch) > 0);

        return $groups;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getItemGroup(string $token, int $itemGroupId): ?array
    {
        $response = $this->http($token)->get($this->baseUrl() . "/inventory/items/group/{$itemGroupId}");

        if (!$response->successful()) {
            Log::channel('jubelio_product_content')->warning('JubelioService.getItemGroup failed', [
                'item_group_id' => $itemGroupId,
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500),
            ]);

            return null;
        }

        $body = $response->json();

        return is_array($body) ? $body : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getItem(string $token, int $itemId): ?array
    {
        try {
            $response = $this->http($token)->get($this->baseUrl() . "/inventory/items/{$itemId}");

            if (!$response->successful()) {
                return null;
            }

            $body = $response->json();

            return is_array($body) ? $body : null;
        } catch (\Exception $e) {
            Log::warning('JubelioService.getItem connection error', [
                'item_id' => $itemId,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSalesOrder(string $token, int $salesOrderId): ?array
    {
        $response = $this->http($token)->get($this->baseUrl() . "/sales/orders/{$salesOrderId}");

        if (!$response->successful()) {
            Log::channel('jubelio_sales_order')->warning('JubelioService.getSalesOrder failed', [
                'salesorder_id' => $salesOrderId,
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500),
            ]);

            return null;
        }

        $body = $response->json();

        return is_array($body) ? $body : null;
    }
}
