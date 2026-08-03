<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'active_payment_gateway' => env('ACTIVE_PAYMENT_GATEWAY', 'xendit'),


    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'xendit' => [
        'secret_key' => env('XENDIT_SECRET_KEY'),
        'public_key' => env('XENDIT_PUBLIC_KEY'),
        'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),
    ],

    'faspay' => [
        'merchant_id' => env('FASPAY_MERCHANT_ID'),
        'user_id' => env('FASPAY_USER_ID'), // Legacy
        'password' => env('FASPAY_PASSWORD'), // Legacy
        'env' => env('FASPAY_ENV', 'dev'),
        // SNAP BI Configuration
        'va_partner_id' => env('FASPAY_VA_PARTNER_ID', '36850'),
        'qris_partner_id' => env('FASPAY_QRIS_PARTNER_ID', '37020'),
        'snap_base_url' => env('FASPAY_SNAP_BASE_URL', 'https://debit-sandbox.faspay.co.id/v1.0'),
        'snap_client_id' => env('FASPAY_SNAP_CLIENT_ID'),
        'private_key_path' => env('FASPAY_SNAP_PRIVATE_KEY_PATH', 'storage/app/faspay_private_key.pem'),
    ],

    'rajaongkir' => [
        'key' => env('RAJAONGKIR_KEY'),
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'ekspedisiku' => [
        'token' => env('EKSPEDISIKU_TOKEN'),
        'base_url' => env('EKSPEDISIKU_BASE_URL', 'http://localhost:8000/api'),
        /** Fallback kode pos pengirim (warehouse) bila tidak ada di DB; Lion Parcel wajib isi. */
        'default_sender_postal_code' => env('EKSPEDISIKU_DEFAULT_SENDER_POSTAL', ''),
        'lalamove_service_type' => env('EKSPEDISIKU_LALAMOVE_SERVICE_TYPE', 'MOTORCYCLE'),
    ],

];
