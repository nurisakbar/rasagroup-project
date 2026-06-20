<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Asumsi stok ready — abaikan validasi stok saat belanja web
    |--------------------------------------------------------------------------
    */
    'assume_stock_ready' => env('SHOP_ASSUME_STOCK_READY', true),

    /*
    |--------------------------------------------------------------------------
    | Pilih hub pengirim otomatis berdasarkan alamat terdekat
    |--------------------------------------------------------------------------
    */
    'auto_hub_by_address' => env('SHOP_AUTO_HUB_BY_ADDRESS', true),

    /*
    |--------------------------------------------------------------------------
    | Tampilkan stok di storefront (katalog produk)
    |--------------------------------------------------------------------------
    */
    'show_stock_on_storefront' => env('SHOP_SHOW_STOCK', false),

    /*
    |--------------------------------------------------------------------------
    | Hanya tampilkan produk yang ada di Jubelio dan QAD (sync_sources)
    |--------------------------------------------------------------------------
    */
    'products_require_jubelio_and_qad' => env('SHOP_PRODUCTS_REQUIRE_JUBELIO_AND_QAD', true),

    /*
    |--------------------------------------------------------------------------
    | Pilih hub di halaman detail produk (/products/{slug})
    |--------------------------------------------------------------------------
    */
    'show_hub_picker_on_product_page' => env('SHOP_SHOW_HUB_PICKER_PRODUCT', false),
];
