<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Jobs\RetryFailedQadSalesOrdersJob;
use App\Jobs\RetryFailedWmsSalesOrdersJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new RetryFailedQadSalesOrdersJob)->everyFiveMinutes()->withoutOverlapping();
Schedule::job(new RetryFailedWmsSalesOrdersJob)->everyFiveMinutes()->withoutOverlapping();
