<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-wa', function() {
    $metaService = app(\App\Services\MetaWhatsAppService::class);
    $waCode = "123456";
    $templateName = "otp_register";
    $languageCode = "en";
    
    $components = [
        [
            'type' => 'body',
            'parameters' => [
                [
                    'type' => 'text',
                    'text' => $waCode
                ]
            ]
        ],
        [
            'type' => 'button',
            'sub_type' => 'url',
            'index' => '0',
            'parameters' => [
                [
                    'type' => 'text',
                    'text' => $waCode
                ]
            ]
        ]
    ];
    
    return $metaService->sendTemplate('6289699935552', $templateName, $languageCode, $components);
});
