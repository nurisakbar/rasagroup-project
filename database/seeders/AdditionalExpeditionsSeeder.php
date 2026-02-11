<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expedition;

class AdditionalExpeditionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $expeditions = [
            ['code' => 'tiki', 'name' => 'Citra Van Titipan Kilat (TIKI)'],
            ['code' => 'ninja', 'name' => 'Ninja Xpress'],
            ['code' => 'lion', 'name' => 'Lion Parcel'],
            ['code' => 'wahana', 'name' => 'Wahana Prestasi Logistik'],
            ['code' => 'sap', 'name' => 'SAP Express'],
            ['code' => 'first', 'name' => 'First Logistics'],
            ['code' => 'ide', 'name' => 'ID Express'],
            // Ekspedisi tambahan lain yang mungkin didukung
            ['code' => 'rpx', 'name' => 'RPX Holding'],
            ['code' => 'pcp', 'name' => 'PCP Express'],
            ['code' => 'jet', 'name' => 'JET Express'],
            ['code' => 'rex', 'name' => 'Royal Express Indonesia (REX)'],
            ['code' => 'sentral', 'name' => 'Sentral Cargo'],
        ];

        foreach ($expeditions as $expedition) {
            Expedition::firstOrCreate(
                ['code' => $expedition['code']],
                ['name' => $expedition['name'], 'is_active' => false]
            );
        }
    }
}
