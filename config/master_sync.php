<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Debug pencocokan SKU Jubelio vs QAD
    |--------------------------------------------------------------------------
    | Saat aktif, sync menulis log perbandingan kode/nama produk ke
    | storage/logs/master-sync.log dan storage/logs/product-match-debug.json
    */
    'product_match_debug' => env('MASTER_SYNC_PRODUCT_MATCH_DEBUG', env('APP_DEBUG', false)),
];
