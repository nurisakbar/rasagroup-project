<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QidApiService;

class QidTestCustomer extends Command
{
    protected $signature = 'qid:test-customer';
    protected $description = 'Test API Customer: Create, Get, Update';

    public function handle(QidApiService $qidApi)
    {
        $this->info("--- TESTING CUSTOMER API ---");

        // 1. TEST GET
        $this->comment("1. Menguji GET Customer (CS00003)...");
        $getRes = $qidApi->get('/api/master/customer/get', [
            'CustomerCode' => 'CS00003',
            'SharedSetCode' => 'MCR-CUST'
        ]);
        if ($getRes) {
            $this->info("GET Berhasil!");
        } else {
            $this->error("GET Gagal (BadRequest).");
        }

        // 2. TEST CREATE
        $this->comment("2. Menguji CREATE Customer (LARA-TST)...");
        $payload = [
            "customerCode" => "LARA-TST-" . rand(100, 999),
            "addressName" => "Laravel Test Customer",
            "city" => "Jakarta",
            "countryCode" => "ID",
            "currencyCode" => "IDR",
            "taxZone" => "IDN",
            "taxClass" => "PPN",
            "sharedSetCode" => "MCR-CUST",
            "isActive" => true
        ];
        $createRes = $qidApi->post('/api/master/customer/create', $payload);
        if ($createRes) {
            $this->info("CREATE Berhasil!");
        } else {
            $this->error("CREATE Gagal (BadRequest).");
        }

        // 3. TEST UPDATE
        $this->comment("3. Menguji UPDATE Customer (CS00003)...");
        $updatePayload = [
            "customerCode" => "CS00003",
            "sharedSetCode" => "MCR-CUST",
            "addressName" => "coco dose (Indah) - UPDATED"
        ];
        $updateRes = $qidApi->patch('/api/master/customer/update', $updatePayload);
        if ($updateRes) {
            $this->info("UPDATE Berhasil!");
        } else {
            $this->error("UPDATE Gagal (BadRequest).");
        }

        return 0;
    }
}
