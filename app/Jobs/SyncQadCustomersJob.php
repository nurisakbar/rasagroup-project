<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Http\Controllers\Admin\DistributorController;
use App\Services\QidApiService;
use Illuminate\Support\Facades\Log;

class SyncQadCustomersJob implements ShouldQueue
{
    use Queueable;

    /**
     * Waktu maksimal job (dalam detik) sebelum dianggap timeout (30 menit).
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(QidApiService $qid): void
    {
        try {
            Log::info('SyncQadCustomersJob: Memulai sinkronisasi QAD...');
            app(DistributorController::class)->processQadSync($qid);
            Log::info('SyncQadCustomersJob: Selesai mengeksekusi sinkronisasi QAD.');
        } catch (\Exception $e) {
            Log::error('SyncQadCustomersJob Error: ' . $e->getMessage());
        }
    }
}
