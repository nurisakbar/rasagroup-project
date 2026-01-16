<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WarehouseApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\AddressApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ProductApiController;

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

// Protected routes (authentication required)
Route::middleware('auth')->group(function () {
    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartApiController::class, 'index'])->name('api.cart.index');
        Route::post('/', [CartApiController::class, 'store'])->name('api.cart.store');
        Route::put('/{id}', [CartApiController::class, 'update'])->name('api.cart.update');
        Route::delete('/{id}', [CartApiController::class, 'destroy'])->name('api.cart.destroy');
        Route::delete('/', [CartApiController::class, 'clear'])->name('api.cart.clear');
    });

    // Address routes
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

    // Order routes
    Route::prefix('orders')->group(function () {
        Route::get('/expeditions', [OrderApiController::class, 'getExpeditions'])->name('api.orders.expeditions');
        Route::post('/expeditions/services', [OrderApiController::class, 'getExpeditionServices'])->name('api.orders.expedition-services');
        Route::post('/', [OrderApiController::class, 'store'])->name('api.orders.store');
        Route::get('/{id}', [OrderApiController::class, 'show'])->name('api.orders.show');
    });
});

