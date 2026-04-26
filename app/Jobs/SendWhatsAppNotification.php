<?php

namespace App\Jobs;

use App\Models\Order;
use App\Helpers\WACloudHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var string
     */
    protected $type;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     * @param string $type
     */
    public function __construct(Order $order, string $type = 'payment')
    {
        $this->order = $order;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Ensure relationships are loaded for notification helpers
        $this->order->load(['user', 'address']);

        Log::info('Processing background WhatsApp notification', [
            'order_id' => $this->order->id,
            'type' => $this->type,
        ]);

        try {
            switch ($this->type) {
                case 'payment':
                    WACloudHelper::sendPaymentNotification($this->order);
                    break;
                case 'thank_you':
                    WACloudHelper::sendThankYouNotification($this->order);
                    break;
                case 'tracking':
                    WACloudHelper::sendTrackingNotification($this->order);
                    break;
                case 'warehouse_notification':
                    WACloudHelper::notifyWarehouseOwnersAboutPayment($this->order);
                    break;
                default:
                    Log::warning('Unknown notification type in background job', [
                        'type' => $this->type,
                    ]);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send background WhatsApp notification', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
            
            // Fail the job so it can be retried if needed
            throw $e;
        }
    }
}
