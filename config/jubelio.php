<?php

return [
    'base_url' => env('JUBELIO_BASE_URL', 'https://api2.jubelio.com'),

    'email' => env('JUBELIO_EMAIL'),
    'password' => env('JUBELIO_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Sales Order sync (POST /sales/orders/)
    |--------------------------------------------------------------------------
    | Order hub/online (order_type=regular) → Jubelio.
    | Aktifkan bersamaan dengan QAD; routing di SalesOrderSyncDispatcher.
    */
    'sales_order' => [
        'enabled' => env('JUBELIO_SALES_ORDER_ENABLED', true),
        'source' => (int) env('JUBELIO_SALES_ORDER_SOURCE', 1), // 1 = Internal
        'default_contact_id' => env('JUBELIO_DEFAULT_CONTACT_ID'),
        'default_location_id' => env('JUBELIO_DEFAULT_LOCATION_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uji coba Sales Order (artisan jubelio:test-so)
    |--------------------------------------------------------------------------
    */
    'test' => [
        'contact_id' => env('JUBELIO_TEST_CONTACT_ID'),
        'location_id' => env('JUBELIO_TEST_LOCATION_ID'),
        'item_code' => env('JUBELIO_TEST_ITEM_CODE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Poll status Sales Order (GET /sales/orders/{id})
    |--------------------------------------------------------------------------
    */
    'status_poll' => [
        'enabled' => env('JUBELIO_STATUS_POLL_ENABLED', true),
        'interval_minutes' => (int) env('JUBELIO_STATUS_POLL_INTERVAL', 5),
        'batch_size' => (int) env('JUBELIO_STATUS_POLL_BATCH_SIZE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sinkronisasi deskripsi & foto produk (GET /inventory/items/group/{id})
    |--------------------------------------------------------------------------
    */
    'product_content' => [
        'enabled' => env('JUBELIO_PRODUCT_CONTENT_SYNC_ENABLED', true),
        'replace_gallery' => env('JUBELIO_PRODUCT_CONTENT_REPLACE_GALLERY', true),
    ],
];
