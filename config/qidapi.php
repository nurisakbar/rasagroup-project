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
];
