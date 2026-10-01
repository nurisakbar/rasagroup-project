<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CarrierManager;
use Illuminate\Http\Request;

class OngkirController extends Controller
{
    /**
     * Display the shipping rates based on GET parameters.
     */
    public function index(Request $request, CarrierManager $carrierManager, \App\Services\LocationGeocodingService $geo)
    {
        $originId = $request->query('origin');
        $destinationId = $request->query('destination');
        $courier = $request->query('courier');
        $weight = $request->query('weight', 1);

        $result = [
            'success' => true,
            'data' => [
                'courier' => $courier,
                'weight' => $weight,
                'origin' => [
                    'id' => $originId,
                ],
                'destination' => [
                    'id' => $destinationId,
                ],
            ],
        ];

        // Jika kurir valid, panggil manager
        if ($courier) {
            try {
                $carrierStrategy = $carrierManager->driver($courier);
                
                $payload = [
                    'origin_id' => $originId,
                    'destination_id' => $destinationId,
                    'weight' => $weight,
                    'origin' => $geo->lionLabelFromId($originId),
                    'destination' => $geo->lionLabelFromId($destinationId),
                ];

                $rates = $carrierStrategy->checkRates($payload);
                $result['rates'] = $rates;
            } catch (\InvalidArgumentException $e) {
                // Courier not supported
                $result['success'] = false;
                $result['message'] = $e->getMessage();
            }
        } else {
            // Check all rates? Based on the original code, it only returned rates if $courier was present and valid.
            // Wait, original code:
            // if ($courier && $originName && $destName) {
            //      $rates = $rateService->checkRates($payload);
            //      $carrierRates = collect($rates['carriers'])->firstWhere('id', $courier);
            //      $result['rates'] = $carrierRates;
            // }
            // Since it only checks for the specific courier, we maintain that behavior.
        }

        return response()->json($result);
    }
}
