<?php

namespace App\Console\Commands;

use App\Services\ZohoService;
use Illuminate\Console\Command;

class ZohoMarkInvoiceSentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:mark-invoice-sent {invoice_id : The Zoho Books Invoice ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark a draft invoice as sent in Zoho Books via API';

    /**
     * Execute the console command.
     */
    public function handle(ZohoService $zohoService): int
    {
        $invoiceId = $this->argument('invoice_id');
        $this->info("Attempting to mark Zoho Invoice [{$invoiceId}] as sent...");

        try {
            $result = $zohoService->markInvoiceAsSent($invoiceId);
            if ($result) {
                $this->info("Success: Invoice [{$invoiceId}] has been successfully marked as sent.");
                return Command::SUCCESS;
            }

            $this->error("Failed to mark invoice [{$invoiceId}] as sent.");
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
