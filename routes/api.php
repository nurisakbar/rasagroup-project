<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WarehouseApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\AddressApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\OngkirApiController;

// Public routes (no authentication required)
Route::prefix('warehouses')->group(function () {
    Route::get('/', [WarehouseApiController::class, 'index'])->name('api.warehouses.index');
    Route::get('/all-with-products', [WarehouseApiController::class, 'getAllWithProducts'])->name('api.warehouses.all-with-products');
    Route::get('/{warehouse}/products', [WarehouseApiController::class, 'getProducts'])->name('api.warehouses.products');
});

// Products routes (public for chatbot knowledge base)
Route::prefix('products')->group(function () {
    Route::get('/', [ProductApiController::class, 'index'])->name('api.products.index');
    Route::get('/all', [ProductApiController::class, 'getAll'])->name('api.products.all');
});

// User routes (public - no authentication required)
Route::prefix('users')->group(function () {
    Route::get('/current', [UserApiController::class, 'getCurrentUser'])->name('api.users.current');
    Route::get('/search', [UserApiController::class, 'getUser'])->name('api.users.search');
});

// Cart routes (public - no authentication required)
Route::prefix('cart')->group(function () {
    Route::get('/', [CartApiController::class, 'index'])->name('api.cart.index');
    Route::post('/', [CartApiController::class, 'store'])->name('api.cart.store');
    Route::put('/{id}', [CartApiController::class, 'update'])->name('api.cart.update');
    Route::delete('/{id}', [CartApiController::class, 'destroy'])->name('api.cart.destroy');
    Route::delete('/', [CartApiController::class, 'clear'])->name('api.cart.clear');
});

// Address routes (public - no authentication required)
Route::prefix('addresses')->group(function () {
    Route::get('/', [AddressApiController::class, 'index'])->name('api.addresses.index');
    Route::post('/', [AddressApiController::class, 'store'])->name('api.addresses.store');
    Route::put('/{id}', [AddressApiController::class, 'update'])->name('api.addresses.update');
    Route::delete('/{id}', [AddressApiController::class, 'destroy'])->name('api.addresses.destroy');
    
    // Address helper routes
    Route::get('/provinces', [AddressApiController::class, 'getProvinces'])->name('api.addresses.provinces');
    Route::get('/regencies', [AddressApiController::class, 'getRegencies'])->name('api.addresses.regencies');
    Route::get('/districts', [AddressApiController::class, 'getDistricts'])->name('api.addresses.districts');
    Route::get('/villages', [AddressApiController::class, 'getVillages'])->name('api.addresses.villages');
});

// Order routes (public - no authentication required)
Route::prefix('orders')->group(function () {
    Route::get('/expeditions', [OrderApiController::class, 'getExpeditions'])->name('api.orders.expeditions');
    Route::post('/expeditions/services', [OrderApiController::class, 'getExpeditionServices'])->name('api.orders.expedition-services');
    Route::post('/', [OrderApiController::class, 'store'])->name('api.orders.store');
    Route::get('/{id}', [OrderApiController::class, 'show'])->name('api.orders.show');
});

// Ongkir route
Route::get('/ongkir', [OngkirApiController::class, 'index'])->name('api.ongkir');

// Webhook routes
Route::prefix('webhooks')->group(function () {
    Route::get('/meta', [\App\Http\Controllers\Api\MetaWebhookController::class, 'verify'])->name('api.webhooks.meta.verify');
    Route::post('/meta', [\App\Http\Controllers\Api\MetaWebhookController::class, 'handle'])->name('api.webhooks.meta.handle');
});

// Faspay SNAP VA & Payment Notification routes
Route::prefix('faspay')->group(function () {
    Route::post('/snap/inquiry', [\App\Http\Controllers\Api\FaspaySnapController::class, 'inquiry'])->name('api.faspay.snap.inquiry');
    Route::post('/snap/payment', [\App\Http\Controllers\Api\FaspaySnapController::class, 'payment'])->name('api.faspay.snap.payment');
    Route::post('/payment-inquiry', [\App\Http\Controllers\Api\FaspaySnapController::class, 'inquiry'])->name('api.faspay.payment-inquiry');
    Route::post('/payment-notification', [\App\Http\Controllers\Api\FaspaySnapController::class, 'paymentNotification'])->name('api.faspay.payment-notification');

    // Standar SNAP BI (V1.0) jika Base URL Merchant di Faspay diset: https://dev.rasaconnect.com/api/faspay
    Route::prefix('v1.0/transfer-va')->group(function () {
        Route::post('/inquiry', [\App\Http\Controllers\Api\FaspaySnapController::class, 'inquiry'])->name('api.faspay.v1.inquiry');
        Route::post('/payment', [\App\Http\Controllers\Api\FaspaySnapController::class, 'payment'])->name('api.faspay.v1.payment');
    });
});

// Standar SNAP BI (V1.0) jika Base URL Merchant di Faspay diset: https://dev.rasaconnect.com/api
Route::prefix('v1.0/transfer-va')->group(function () {
    Route::post('/inquiry', [\App\Http\Controllers\Api\FaspaySnapController::class, 'inquiry'])->name('api.v1.inquiry');
    Route::post('/payment', [\App\Http\Controllers\Api\FaspaySnapController::class, 'payment'])->name('api.v1.payment');
});

