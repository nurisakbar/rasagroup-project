<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QadService;

class FixQadCustomer extends Command
{
    protected $signature = 'qad:fix-customer {code}';
    protected $description = 'Fix customer in QAD';

    public function handle(QadService $qad)
    {
        $code = $this->argument('code');
        
        $customer = $qad->getCustomer($code);
        if (!$customer || ($customer['error']['isError'] ?? false)) {
            $this->error('Not found');
            return;
        }

        $data = $customer['data'];
        
        // Truncate fields that might be too long
        $payload = [
            'customerCode' => $code,
            'addressName' => substr($data['addressName'] ?? 'Customer', 0, 20),
            'addressSearchName' => substr($data['addressSearchName'] ?? 'Customer', 0, 20),
            'businessRelationName' => substr($data['businessRelationName'] ?? 'Customer', 0, 20),
            'street1' => substr($data['street1'] ?? '', 0, 20),
            'city' => substr($data['city'] ?? '', 0, 20),
            'sharedSetCode' => 'MCR-CUST',
        ];

        $res = $qad->updateCustomer($payload);
        $this->info("Update Customer:");
        $this->line(json_encode($res, JSON_PRETTY_PRINT));
        
        // Then retry createCustomerData
        $res2 = $qad->createCustomerData(['customerCode' => $code]);
        $this->info("Create Data:");
        $this->line(json_encode($res2, JSON_PRETTY_PRINT));
    }
}
