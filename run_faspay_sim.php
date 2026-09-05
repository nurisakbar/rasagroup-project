<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$request = Illuminate\Http\Request::create('/api/faspay/simulate-uat', 'GET');
$response = app(App\Http\Controllers\FaspayUatSimulatorController::class)->simulate($request);
file_put_contents('faspay_sim_results.json', $response->getContent());
echo "Done\n";
