<?php

return [

    /*
    |--------------------------------------------------------------------------
    | QAD Credit Terms
    |--------------------------------------------------------------------------
    |
    | Key = Terms Code QAD.
    | days = nilai yang disimpan di users.term_of_payment (integer hari).
    |   *D = N hari
    |   *M = N × 30 hari
    |   *Y = N × 365 hari
    | immediate = cash / CIA / COD (bukan opsi TOP).
    |
    */

    'CASH' => ['label' => 'Cash', 'days' => 0, 'immediate' => true],
    'CIA' => ['label' => 'Cash in Advance', 'days' => 0, 'immediate' => true],
    'COD' => ['label' => 'Cash on Delivery', 'days' => 0, 'immediate' => true],

    '2D' => ['label' => '2 Days', 'days' => 2],
    '7D' => ['label' => '7 Days', 'days' => 7],
    '13D' => ['label' => '13 Days', 'days' => 13],
    '14D' => ['label' => '14 Days', 'days' => 14],
    '15D' => ['label' => '15 Days', 'days' => 15],
    '16D' => ['label' => '16 Days', 'days' => 16],
    '17D' => ['label' => '17 Days', 'days' => 17],
    '18D' => ['label' => '18 Days', 'days' => 18],
    '20D' => ['label' => '20 Days', 'days' => 20],
    '21D' => ['label' => '21 Days', 'days' => 21],
    '23D' => ['label' => '23 Days', 'days' => 23],
    '26D' => ['label' => '26 Days', 'days' => 26],
    '29D' => ['label' => '29 Days', 'days' => 29],
    '30D' => ['label' => '30 Days', 'days' => 30],
    '40D' => ['label' => '40 Days', 'days' => 40],
    '45D' => ['label' => '45 Days', 'days' => 45],
    '60D' => ['label' => '60 Days', 'days' => 60],
    '61D' => ['label' => '61 Days', 'days' => 61],
    '335D' => ['label' => '335 Days', 'days' => 335],
    '35M' => ['label' => '35 Month', 'days' => 1050],
    '1Y' => ['label' => '1 Year', 'days' => 365],

];
