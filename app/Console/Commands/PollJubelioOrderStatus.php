<?php

namespace App\Console\Commands;

use App\Services\JubelioOrderStatusSyncService;
use Illuminate\Console\Command;

class PollJubelioOrderStatus extends Command
{
    protected $signature = 'jubelio:poll-order-status
                            {--order= : UUID order lokal untuk poll satu order}
                            {--limit= : Batas jumlah order yang di-poll}';

    protected $description = 'Poll status Sales Order Jubelio dan update order lokal';

    public function handle(JubelioOrderStatusSyncService $syncService): int
    {
        if (! config('jubelio.status_poll.enabled', true)) {
            $this->warn('Polling status Jubelio dinonaktifkan (JUBELIO_STATUS_POLL_ENABLED=false).');

            return self::SUCCESS;
        }

        $orderId = $this->option('order') ?: null;
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $this->info('Memulai poll status order Jubelio...');

        $stats = $syncService->sync($limit, $orderId);

        $this->table(
            ['Metric', 'Count'],
            collect($stats)->map(fn ($value, $key) => [$key, $value])->values()->all()
        );

        if ($stats['failed'] > 0) {
            $this->warn('Beberapa order gagal di-poll. Cek storage/logs/jubelio-sales-order.log');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
