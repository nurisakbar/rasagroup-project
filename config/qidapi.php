<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QidApi Base URL
    |--------------------------------------------------------------------------
    | URL dasar dari QidApi. Contoh: https://development-qadwebapi.rasagroupoffice.com
    */
    'base_url' => env('QIDAPI_BASE_URL', 'https://development-qadwebapi.rasagroupoffice.com'),

    /*
    |--------------------------------------------------------------------------
    | Kredensial Autentikasi
    |--------------------------------------------------------------------------
    */
    'username' => env('QIDAPI_USERNAME', 'user.zoho'),
    'password' => env('QIDAPI_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Apps ID
    |--------------------------------------------------------------------------
    | ID aplikasi yang digunakan untuk login ke QidApi.
    */
    'apps_id'  => env('QIDAPI_APPS_ID', '86770460-cfd3-11ee-b044-d747f56a0d39'),

    /*
    |--------------------------------------------------------------------------
    | Default address fields (QAD city / zip master)
    |--------------------------------------------------------------------------
    | Nama kota panjang dari Raja Ongkir (mis. "KOTA ADM. JAKARTA PUSAT") sering
    | tidak cocok dengan master QAD. Normalisasi memetakan ke nilai aman.
    */
    'default_customer_city' => env('QIDAPI_DEFAULT_CUSTOMER_CITY', 'Jakarta'),
    'default_customer_zip' => env('QIDAPI_DEFAULT_CUSTOMER_ZIP', '10110'),

    /*
    |--------------------------------------------------------------------------
    | Lock alokasi nomor WS (detik)
    |--------------------------------------------------------------------------
    */
    'ws_order_number_lock_seconds' => (int) env('QIDAPI_WS_ORDER_LOCK_SECONDS', 15),

    /*
    |--------------------------------------------------------------------------
    | Uji coba Sales Order (artisan qid:test-so-format)
    |--------------------------------------------------------------------------
    */
    'test_so_customer' => env('QIDAPI_TEST_SO_CUSTOMER', 'ZH78584'),
    'test_so_item' => env('QIDAPI_TEST_SO_ITEM', 'FMB010-MD03'),
    'shared_set_code' => env('QIDAPI_SHARED_SET_CODE', 'MCR-CUST'),
];
