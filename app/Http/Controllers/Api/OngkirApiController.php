<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EkspedisiKuService;
use Illuminate\Http\Request;

class OngkirApiController extends Controller
{
    protected $ekspedisiku;

    public function __construct(EkspedisiKuService $ekspedisiku)
    {
        $this->ekspedisiku = $ekspedisiku;
    }

    /**
     * Get shipping rates (Proxy to EkspedisiKu)
     */
    public function index(Request $request)
    {
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'weight' => 'required|numeric',
            'courier' => 'required|string',
        ]);

        $result = $this->ekspedisiku->calculateCost(
            $request->origin,
            $request->destination,
            $request->weight,
            $request->courier
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan data ongkos kirim'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data']
        ]);
    }
}
