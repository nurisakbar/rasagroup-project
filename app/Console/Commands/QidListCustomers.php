<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QidApiService;

class QidListCustomers extends Command
{
    protected $signature = 'qid:list-customers {--code=} {--limit=10}';
    protected $description = 'List customers from QidApi';

    public function handle(QidApiService $qidApi)
    {
        $code = $this->option('code') ?? '';
        $limit = (int) $this->option('limit');

        $this->info("Fetching customers (code: '$code', limit: $limit)...");
        
        $res = $qidApi->get('/api/master/customer/list', ['customerCode' => $code]);

        if ($res && isset($res['data'])) {
            $customers = array_slice($res['data'], 0, $limit);
            $this->table(
                ['Code', 'Name', 'City', 'Currency'],
                array_map(fn($c) => [
                    $c['customerCode'],
                    $c['addressName'],
                    $c['city'],
                    $c['customerCurrencyCode']
                ], $customers)
            );
            $this->info("Total found: " . count($res['data']));
        } else {
            $this->error('Failed to fetch customers. Check logs.');
        }

        return 0;
    }
}
