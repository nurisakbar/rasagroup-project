<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaspayService
{
    protected string $merchantId;
    protected string $userId;
    protected string $password;
    protected string $env;

    public function __construct()
    {
        $this->merchantId = config('services.faspay.merchant_id');
        $this->userId = config('services.faspay.user_id');
        $this->password = config('services.faspay.password');
        $this->env = config('services.faspay.env');
    }

    protected function getBaseUrl(): string
    {
        if ($this->env === 'prod' || $this->env === 'production') {
            return 'https://web.faspay.co.id';
        }
        return 'https://dev.faspay.co.id';
    }

    /**
     * Create a bill (invoice) on Faspay
     *
     * @param Order $order
     * @param object $user The customer making the purchase
     * @return array|null Returns ['bill_no' => ..., 'redirect_url' => ...] on success
     */
    public function createBill(Order $order, $user): ?array
    {
        try {
            $billNo = $order->order_number;
            $billDate = date('Y-m-d H:i:s');
            $billExpired = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // Signature generation: sha1(md5(user_id + password + bill_no))
            $signature = sha1(md5($this->userId . $this->password . $billNo));

            // Format customer info
            // User name might be single string, fallback handling
            $nameParts = explode(' ', $user->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? ' ';

            // Prepare items
            $items = [];
            foreach ($order->items as $item) {
                $items[] = [
                    "id"            => $item->product_id ?? "ITEM",
                    "product"       => substr($item->product_name ?? 'Product', 0, 50),
                    "qty"           => (string) $item->quantity,
                    "amount"        => (string) ((int) $item->price), // Unit price in IDR
                    "payment_plan"  => "01", // Full payment
                    "merchant_id"   => $this->merchantId,
                    "tenor"         => "00",
                ];
            }

            // Fallback phone number
            $msisdn = $user->phone ?? '08000000000';

            $data = [
                'request'           => 'Transmisi Info Detil Pembelian',
                'merchant_id'       => $this->merchantId,
                'merchant'          => config('app.name', 'Faspay Store'),
                'bill_no'           => $billNo,
                'bill_reff'         => (string) $order->id,
                'bill_date'         => $billDate,
                'bill_expired'      => $billExpired,
                "bill_desc"         => "Pembayaran Pesanan #" . $billNo,
                "bill_currency"     => "IDR",
                "bill_gross"        => (string) ((int) $order->subtotal), // Ensure integer string
                "bill_miscfee"      => (string) ((int) $order->shipping_cost),
                "bill_total"        => (string) ((int) $order->total_amount),
                "cust_no"           => (string) $user->id,
                "cust_name"         => $firstName,
                "cust_lastname"     => $lastName,
                "payment_channel"   => "", // Let user choose on Faspay page
                "pay_type"          => "1",
                "bank_userid"       => "",
                "msisdn"            => $msisdn,
                "email"             => $user->email ?? 'no-reply@example.com',
                "terminal"          => "10",
                "billing_name"      => $firstName,
                "billing_lastname"  => $lastName,
                "billing_address"   => substr($order->shipping_address ?? 'Alamat', 0, 100),
                "billing_address_city" => "Kota",
                "billing_address_region" => "Provinsi",
                "billing_address_state" => "Indonesia",
                "billing_address_poscode" => "10000",
                "billing_msisdn"    => $msisdn,
                "billing_address_country_code" => "ID",
                "item"              => $items,
                "reserve1"          => "",
                "reserve2"          => "",
                "signature"         => $signature
            ];

            Log::info('Faspay createBill Request', ['bill_no' => $billNo, 'data' => $data]);

            // Endpoint POST json
            $url = $this->getBaseUrl() . '/cvr/300011/10';

            try {
                $response = Http::withoutVerifying()->timeout(5)->post($url, $data);

                if ($response->successful()) {
                    $responseData = $response->json();
                    Log::info('Faspay createBill Response', ['response' => $responseData]);

                    if (isset($responseData['redirect_url'])) {
                        return [
                            'bill_no' => $billNo,
                            'redirect_url' => $responseData['redirect_url']
                        ];
                    } else {
                        Log::warning('Faspay redirect_url missing in response', ['response' => $responseData]);
                    }
                } else {
                    Log::error('Faspay API Error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // If it's a dev environment and we can't connect, simulate a successful response
                if ($this->env === 'dev') {
                    Log::info('Simulating Faspay createBill because dev server is unreachable.', ['error' => $e->getMessage()]);
                    return [
                        'bill_no' => $billNo,
                        'redirect_url' => 'https://dev.faspay.co.id/cvr/300011/10/mock/' . $billNo
                    ];
                }
                
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Faspay Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return null;
    }

    /**
     * Generate signature for validating callback
     */
    public function generateCallbackSignature(string $billNo, string $paymentStatusCode): string
    {
        return sha1(md5($this->userId . $this->password . $billNo . $paymentStatusCode));
    }
}
