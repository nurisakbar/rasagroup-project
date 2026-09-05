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

    'active_payment_gateway' => env('ACTIVE_PAYMENT_GATEWAY', 'faspay'),
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

    'faspay' => [
        'default' => env('FASPAY_DEFAULT_COMPANY', 'rdi'),

        // Backward-compatibility keys (points to default / RDI)
        'merchant_id' => env('FASPAY_RDI_MERCHANT_ID', env('FASPAY_MERCHANT_ID', '37020')),
        'user_id' => env('FASPAY_RDI_USER_ID', env('FASPAY_USER_ID', 'bot37020')),
        'password' => env('FASPAY_RDI_PASSWORD', env('FASPAY_PASSWORD')),
        'env' => env('FASPAY_RDI_ENV', env('FASPAY_ENV', 'dev')),
        'va_partner_id' => env('FASPAY_RDI_VA_PARTNER_ID', env('FASPAY_VA_PARTNER_ID', '37020')),
        'qris_partner_id' => env('FASPAY_RDI_QRIS_PARTNER_ID', env('FASPAY_QRIS_PARTNER_ID', '37020')),
        'snap_base_url' => env('FASPAY_SNAP_BASE_URL', 'https://debit-sandbox.faspay.co.id/v1.0'),
        'snap_client_id' => env('FASPAY_RDI_SNAP_CLIENT_ID', env('FASPAY_SNAP_CLIENT_ID', '37020')),
        'private_key_path' => env('FASPAY_RDI_SNAP_PRIVATE_KEY_PATH', env('FASPAY_SNAP_PRIVATE_KEY_PATH', 'storage/app/faspay_private_key.pem')),
        'public_key_path' => env('FASPAY_RDI_SNAP_PUBLIC_KEY_PATH', env('FASPAY_SNAP_PUBLIC_KEY_PATH', 'storage/app/37020_server.crt')),

        // Multi-company credentials
        'companies' => [
            'rdi' => [
                'code' => 'rdi',
                'name' => 'PT Rasa Distribusi Indonesia',
                'merchant_id' => env('FASPAY_RDI_MERCHANT_ID', env('FASPAY_MERCHANT_ID', '37020')),
                'user_id' => env('FASPAY_RDI_USER_ID', env('FASPAY_USER_ID', 'bot37020')),
                'password' => env('FASPAY_RDI_PASSWORD', env('FASPAY_PASSWORD')),
                'env' => env('FASPAY_RDI_ENV', env('FASPAY_ENV', 'dev')),
                'va_partner_id' => env('FASPAY_RDI_VA_PARTNER_ID', env('FASPAY_VA_PARTNER_ID', '37020')),
                'qris_partner_id' => env('FASPAY_RDI_QRIS_PARTNER_ID', env('FASPAY_QRIS_PARTNER_ID', '37020')),
                'snap_base_url' => env('FASPAY_RDI_SNAP_BASE_URL', env('FASPAY_SNAP_BASE_URL', 'https://debit-sandbox.faspay.co.id/v1.0')),
                'snap_client_id' => env('FASPAY_RDI_SNAP_CLIENT_ID', env('FASPAY_SNAP_CLIENT_ID', '37020')),
                'private_key_path' => env('FASPAY_RDI_SNAP_PRIVATE_KEY_PATH', env('FASPAY_SNAP_PRIVATE_KEY_PATH', 'storage/app/faspay_private_key.pem')),
                'public_key_path' => env('FASPAY_RDI_SNAP_PUBLIC_KEY_PATH', env('FASPAY_SNAP_PUBLIC_KEY_PATH', 'storage/app/37020_server.crt')),
                'va_prefixes' => [
                    'faspay_permata_va'  => env('FASPAY_RDI_VA_PERMATA', '370201'),
                    'faspay_mandiri_va'  => env('FASPAY_RDI_VA_MANDIRI', '37020002'),
                    'faspay_bri_va'      => env('FASPAY_RDI_VA_BRI', '370202'),
                    'faspay_cimb_va'     => env('FASPAY_RDI_VA_CIMB', '370204'),
                    'faspay_bni_va'      => env('FASPAY_RDI_VA_BNI', '9881236387'),
                    'faspay_sinarmas_va' => env('FASPAY_RDI_VA_SINARMAS', '885648'),
                    'faspay_maybank_va'  => env('FASPAY_RDI_VA_MAYBANK', '78218052'),
                    'faspay_danamon_va'  => env('FASPAY_RDI_VA_DANAMON', '797039'),
                    'faspay_bsi_va'      => env('FASPAY_RDI_VA_BSI', '12601021'),
                ],
            ],

            'mcr' => [
                'code' => 'mcr',
                'name' => 'PT Multi Citra Rasa',
                'merchant_id' => env('FASPAY_MCR_MERCHANT_ID'),
                'user_id' => env('FASPAY_MCR_USER_ID'),
                'password' => env('FASPAY_MCR_PASSWORD'),
                'env' => env('FASPAY_MCR_ENV', env('FASPAY_ENV', 'dev')),
                'va_partner_id' => env('FASPAY_MCR_VA_PARTNER_ID'),
                'qris_partner_id' => env('FASPAY_MCR_QRIS_PARTNER_ID'),
                'snap_base_url' => env('FASPAY_MCR_SNAP_BASE_URL', env('FASPAY_SNAP_BASE_URL', 'https://debit-sandbox.faspay.co.id/v1.0')),
                'snap_client_id' => env('FASPAY_MCR_SNAP_CLIENT_ID'),
                'private_key_path' => env('FASPAY_MCR_SNAP_PRIVATE_KEY_PATH', 'storage/app/faspay/mcr/faspay_private_key.pem'),
                'public_key_path' => env('FASPAY_MCR_SNAP_PUBLIC_KEY_PATH', 'storage/app/faspay/mcr/faspay_public_key.pem'),
                'va_prefixes' => [
                    'faspay_permata_va'  => env('FASPAY_MCR_VA_PERMATA', '371161'),
                    'faspay_mandiri_va'  => env('FASPAY_MCR_VA_MANDIRI', '37116001'),
                    'faspay_bri_va'      => env('FASPAY_MCR_VA_BRI', '371162'),
                    'faspay_cimb_va'     => env('FASPAY_MCR_VA_CIMB', '371164'),
                    'faspay_bni_va'      => env('FASPAY_MCR_VA_BNI', '9881236387'),
                    'faspay_sinarmas_va' => env('FASPAY_MCR_VA_SINARMAS', '371164'),
                    'faspay_maybank_va'  => env('FASPAY_MCR_VA_MAYBANK', '37116002'),
                    'faspay_danamon_va'  => env('FASPAY_MCR_VA_DANAMON', '371165'),
                    'faspay_bsi_va'      => env('FASPAY_MCR_VA_BSI', '37116003'),
                ],
            ],
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'ekspedisiku' => [
        'token' => env('EKSPEDISIKU_TOKEN'),
        'base_url' => env('EKSPEDISIKU_BASE_URL', 'http://localhost:8000/api'),
        /** Fallback kode pos pengirim (warehouse) bila tidak ada di DB; Lion Parcel wajib isi. */
        'default_sender_postal_code' => env('EKSPEDISIKU_DEFAULT_SENDER_POSTAL', ''),
        'lalamove_service_type' => env('EKSPEDISIKU_LALAMOVE_SERVICE_TYPE', 'MOTORCYCLE'),
    ],

    'lalamove' => [
        'api_key' => env('LALAMOVE_API_KEY'),
        'api_secret' => env('LALAMOVE_API_SECRET'),
        'base_url' => env('LALAMOVE_BASE_URL', 'https://rest.sandbox.lalamove.com'),
    ],

];
