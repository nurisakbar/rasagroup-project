<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\XenditService;
use App\Support\QadAddressSnapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Pekerjaan pasca halaman checkout/success: cek Xendit, antre sync customer/QAD SO, notifikasi.
 * Menghindari pemanggilan API berat secara sinkron di request HTTP.
 */
class ProcessCheckoutSuccessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public function __construct(
        public string $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::query()
            ->with([
                'user',
                'address.district',
                'address.regency',
                'address.province',
                'address.district.city',
                'items.product',
            ])
            ->find($this->orderId);

        if (! $order) {
            Log::warning('ProcessCheckoutSuccessJob: order not found', ['order_id' => $this->orderId]);

            return;
        }

        try {
            $user = $order->user;
            $address = $order->address;
            if ($user && empty($user->qad_customer_code) && $address) {
                $snapshot = QadAddressSnapshot::fromBuyerAddress($address);
                SyncCustomerToQad::dispatch($user, $snapshot);
            }
        } catch (\Throwable $e) {
            Log::warning('ProcessCheckoutSuccessJob: failed to dispatch SyncCustomerToQad', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($order->payment_method === 'xendit' && $order->xendit_invoice_id && $order->payment_status !== 'paid') {
            try {
                $xenditService = new XenditService();
                $invoiceDetails = $xenditService->getInvoice($order->xendit_invoice_id);

                Log::info('ProcessCheckoutSuccessJob: Xendit API response', [
                    'order_id' => $order->id,
                    'invoice_id' => $order->xendit_invoice_id,
                    'has_response' => ! empty($invoiceDetails),
                    'invoice_status' => $invoiceDetails['status'] ?? 'unknown',
                ]);

                if ($invoiceDetails && isset($invoiceDetails['status']) && in_array($invoiceDetails['status'], ['PAID', 'SETTLED'], true)) {
                    $order->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                    ]);
                    if ($order->order_status === 'pending') {
                        $order->update(['order_status' => 'processing']);
                    }
                    Log::info('ProcessCheckoutSuccessJob: payment marked paid from Xendit', [
                        'order_id' => $order->id,
                        'invoice_status' => $invoiceDetails['status'],
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('ProcessCheckoutSuccessJob: Xendit verify failed', [
                    'order_id' => $order->id,
                    'invoice_id' => $order->xendit_invoice_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $order->refresh();

        if ($order->payment_status === 'paid' && empty($order->qad_so_number)) {
            SyncOrderToQad::dispatch($order);
            Log::info('ProcessCheckoutSuccessJob: SyncOrderToQad dispatched', [
                'order_id' => $order->id,
            ]);
        }

        $order->refresh();

        if ($order->payment_status === 'paid') {
            SendWhatsAppNotification::dispatch($order, 'thank_you');
        }
    }
}
