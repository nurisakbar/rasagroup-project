<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class SyncDistributorArBalanceJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webhookUrl = env('N8N_CIS_AR_BALANCE_URL');
        if (!$webhookUrl) {
            Log::warning('SyncDistributorArBalanceJob: N8N_CIS_AR_BALANCE_URL is not set.');
            return;
        }

        $distributors = User::where('role', User::ROLE_DISTRIBUTOR)
            ->whereNotNull('qad_customer_code')
            ->where('qad_customer_code', '!=', '')
            ->get();

        foreach ($distributors as $distributor) {
            try {
                $response = Http::timeout(10)->get($webhookUrl, [
                    'debtorcode' => $distributor->qad_customer_code
                ]);

                if ($response->successful() && $response->json('success')) {
                    $data = $response->json('data');
                    $updateData = [];

                    if (isset($data['outstanding'])) {
                        $updateData['ar_outstanding'] = $data['outstanding'];
                    }
                    if (isset($data['credit_limit'])) {
                        $updateData['credit_limit'] = $data['credit_limit'];
                    }

                    if (!empty($updateData)) {
                        $distributor->update($updateData);
                    }
                } else {
                    Log::error('SyncDistributorArBalanceJob: Webhook failed for debtorcode', [
                        'debtorcode' => $distributor->qad_customer_code,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('SyncDistributorArBalanceJob: Exception when calling webhook', [
                    'debtorcode' => $distributor->qad_customer_code,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
