<?php

namespace App\Jobs;

use App\Services\JubelioProductContentSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncJubelioProductContent implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        private ?string $productCode = null
    ) {}

    public function uniqueId(): string
    {
        return 'jubelio-product-content:' . ($this->productCode ?: 'all');
    }

    public function handle(JubelioProductContentSyncService $syncService): void
    {
        if (! config('jubelio.product_content.enabled', true)) {
            Log::channel('jubelio_product_content')->info('SyncJubelioProductContent: disabled');

            return;
        }

        Log::channel('jubelio_product_content')->info('SyncJubelioProductContent: job started', [
            'product_code' => $this->productCode,
        ]);

        $syncService->sync($this->productCode);
    }
}
