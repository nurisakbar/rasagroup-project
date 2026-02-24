<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/about', [App\Http\Controllers\PageController::class, 'about'])->name('about');
Route::get('/contact', [App\Http\Controllers\PageController::class, 'contact'])->name('contact');
Route::post('/contact', [App\Http\Controllers\PageController::class, 'sendContact'])->name('contact.send');
Route::get('/p/{slug}', [App\Http\Controllers\PageController::class, 'show'])->name('pages.show');

// Xendit Webhook (no CSRF protection needed)
Route::post('/webhooks/xendit', [App\Http\Controllers\XenditWebhookController::class, 'handle'])->name('webhooks.xendit');

// Redirect old dashboard based on user role
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if (in_array($user->role, ['agent', 'super_admin'])) {
        return redirect()->route('admin.dashboard');
    }
    
    if ($user->role === 'warehouse' && $user->warehouse_id) {
        return redirect()->route('warehouse.dashboard');
    }
    
    if ($user->role === 'driippreneur') {
        return redirect()->route('driippreneur.dashboard');
    }
    
    if ($user->role === 'distributor') {
        return redirect()->route('buyer.dashboard');
    }
    
    return redirect()->route('buyer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Public Routes
Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/quick-view/{product}', [App\Http\Controllers\ProductController::class, 'quickView'])->name('products.quick-view');
Route::get('/products/{product}', [App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
Route::get('/promo', [App\Http\Controllers\PromoController::class, 'index'])->name('promo.index');
Route::get('/promo/{slug}', [App\Http\Controllers\PromoController::class, 'show'])->name('promo.show');

// Hub & Distributor Routes
Route::get('/hubs', [App\Http\Controllers\HubController::class, 'index'])->name('hubs.index');
Route::get('/hubs/get-regencies', [App\Http\Controllers\HubController::class, 'getRegencies'])->name('hubs.get-regencies');
Route::get('/hubs/nearby', [App\Http\Controllers\HubController::class, 'getNearbyHubs'])->name('hubs.nearby');
Route::get('/hubs/check-stock', [App\Http\Controllers\HubController::class, 'checkStock'])->name('hubs.check-stock');
Route::post('/hubs/detect-nearest', [App\Http\Controllers\HubController::class, 'detectNearestHub'])->name('hubs.detect-nearest');
Route::post('/hubs/select', [App\Http\Controllers\HubController::class, 'select'])->name('hubs.select');
Route::get('/hubs/{warehouse}', [App\Http\Controllers\HubController::class, 'show'])->name('hubs.show');

// Cart Routes (both guest and auth)
Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [App\Http\Controllers\CartController::class, 'store'])->name('cart.store');
Route::delete('/cart/clear', [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
Route::get('/cart/product-stock/{product}', [App\Http\Controllers\CartController::class, 'getProductStock'])->name('cart.product-stock');
Route::middleware('auth')->group(function () {
    Route::put('/cart/{cart}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy');
});

// Checkout Routes
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/calculate-shipping', [App\Http\Controllers\CheckoutController::class, 'calculateShipping'])->name('checkout.calculate-shipping');
    Route::get('/checkout/expedition-services', [App\Http\Controllers\CheckoutController::class, 'getExpeditionServices'])->name('checkout.expedition-services');
    Route::get('/checkout/success/{order}', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
});

// Buyer Routes
Route::prefix('buyer')->name('buyer.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Buyer\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [App\Http\Controllers\Buyer\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Buyer\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/track', [App\Http\Controllers\Buyer\OrderController::class, 'trackOrder'])->name('orders.track');
    Route::get('/profile', [App\Http\Controllers\Buyer\ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [App\Http\Controllers\Buyer\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Buyer\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\Buyer\ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Address Management
    Route::get('/addresses', [App\Http\Controllers\Buyer\AddressController::class, 'index'])->name('addresses.index');
    Route::get('/addresses/create', [App\Http\Controllers\Buyer\AddressController::class, 'create'])->name('addresses.create');
    Route::post('/addresses', [App\Http\Controllers\Buyer\AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [App\Http\Controllers\Buyer\AddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/addresses/{address}', [App\Http\Controllers\Buyer\AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [App\Http\Controllers\Buyer\AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::put('/addresses/{address}/set-default', [App\Http\Controllers\Buyer\AddressController::class, 'setDefault'])->name('addresses.set-default');
    Route::get('/addresses/get-regencies', [App\Http\Controllers\Buyer\AddressController::class, 'getRegencies'])->name('addresses.get-regencies');
    Route::get('/addresses/get-districts', [App\Http\Controllers\Buyer\AddressController::class, 'getDistricts'])->name('addresses.get-districts');
    Route::get('/addresses/get-villages', [App\Http\Controllers\Buyer\AddressController::class, 'getVillages'])->name('addresses.get-villages');
    
    // Distributor Application
    Route::get('/distributor/apply', [App\Http\Controllers\Buyer\DistributorApplicationController::class, 'create'])->name('distributor.apply');
    Route::post('/distributor/apply', [App\Http\Controllers\Buyer\DistributorApplicationController::class, 'store']);
    Route::get('/distributor/status', [App\Http\Controllers\Buyer\DistributorApplicationController::class, 'status'])->name('distributor.status');
    Route::get('/distributor/get-regencies', [App\Http\Controllers\Buyer\DistributorApplicationController::class, 'getRegencies'])->name('distributor.get-regencies');
    
    // DRiiPPreneur Application
    Route::get('/driippreneur/apply', [App\Http\Controllers\Buyer\DriippreneurApplicationController::class, 'create'])->name('driippreneur.apply');
    Route::post('/driippreneur/apply', [App\Http\Controllers\Buyer\DriippreneurApplicationController::class, 'store']);
    Route::get('/driippreneur/status', [App\Http\Controllers\Buyer\DriippreneurApplicationController::class, 'status'])->name('driippreneur.status');
    
    // Point Withdrawals
    Route::get('/point-withdrawals', [App\Http\Controllers\Buyer\PointWithdrawalController::class, 'index'])->name('point-withdrawals.index');
    Route::get('/point-withdrawals/create', [App\Http\Controllers\Buyer\PointWithdrawalController::class, 'create'])->name('point-withdrawals.create');
    Route::post('/point-withdrawals', [App\Http\Controllers\Buyer\PointWithdrawalController::class, 'store'])->name('point-withdrawals.store');
    Route::get('/point-withdrawals/{pointWithdrawal}', [App\Http\Controllers\Buyer\PointWithdrawalController::class, 'show'])->name('point-withdrawals.show');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Login (Guest only)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [App\Http\Controllers\Admin\Auth\AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [App\Http\Controllers\Admin\Auth\AdminLoginController::class, 'store']);
    });

    // Admin Protected Routes
    Route::middleware(['auth', 'agent'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Master Data - Brands
        Route::resource('brands', App\Http\Controllers\Admin\BrandController::class)->except(['show']);
        
        // Master Data - Price Levels
        Route::resource('price-levels', App\Http\Controllers\Admin\PriceLevelController::class);

        // Master Data - Categories
        Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class)->except(['show']);

        // Products CRUD (Master Data)
        Route::post('/products/import', [App\Http\Controllers\Admin\ProductController::class, 'import'])->name('products.import');
        Route::get('/products/template', [App\Http\Controllers\Admin\ProductController::class, 'downloadTemplate'])->name('products.template');
        Route::resource('products', App\Http\Controllers\Admin\ProductController::class);

        // Sliders Management
        Route::resource('sliders', App\Http\Controllers\Admin\SliderController::class)->except(['show']);

        // Promos Management
        Route::resource('promos', App\Http\Controllers\Admin\PromoController::class)->except(['show']);

        // Website Pop-ups Management
        Route::resource('website-popups', App\Http\Controllers\Admin\WebsitePopupController::class)->except(['show']);

        // Pages Management
        Route::resource('pages', App\Http\Controllers\Admin\PageController::class);

        // Warehouses CRUD
        Route::resource('warehouses', App\Http\Controllers\Admin\WarehouseController::class);
        Route::post('/warehouses/{warehouse}/stock', [App\Http\Controllers\Admin\WarehouseController::class, 'addStock'])->name('warehouses.add-stock');
        Route::post('/warehouses/{warehouse}/sync-products', [App\Http\Controllers\Admin\WarehouseController::class, 'syncProducts'])->name('warehouses.sync-products');
        Route::put('/warehouses/{warehouse}/stock/{stock}', [App\Http\Controllers\Admin\WarehouseController::class, 'updateStock'])->name('warehouses.update-stock');
        Route::delete('/warehouses/{warehouse}/stock/{stock}', [App\Http\Controllers\Admin\WarehouseController::class, 'removeStock'])->name('warehouses.remove-stock');
        Route::post('/warehouses/{warehouse}/users', [App\Http\Controllers\Admin\WarehouseController::class, 'addUser'])->name('warehouses.add-user');
        Route::delete('/warehouses/{warehouse}/users/{user}', [App\Http\Controllers\Admin\WarehouseController::class, 'removeUser'])->name('warehouses.remove-user');
        Route::get('/get-regencies', [App\Http\Controllers\Admin\WarehouseController::class, 'getRegencies'])->name('get-regencies');
        Route::get('/get-districts', [App\Http\Controllers\Admin\WarehouseController::class, 'getDistricts'])->name('get-districts');
        Route::get('/get-villages', [App\Http\Controllers\Admin\WarehouseController::class, 'getVillages'])->name('get-villages');

        // Users Management
        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');

        // Orders Management
        Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'update'])->name('orders.update');
        Route::put('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::put('/orders/{order}/tracking', [App\Http\Controllers\Admin\OrderController::class, 'updateTracking'])->name('orders.update-tracking');
        Route::put('/orders/{order}/payment', [App\Http\Controllers\Admin\OrderController::class, 'updatePayment'])->name('orders.update-payment');
        Route::get('/orders/{order}/track', [App\Http\Controllers\Admin\OrderController::class, 'trackOrder'])->name('orders.track');

        // Settings
        Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings/driippreneur-point-rate', [App\Http\Controllers\Admin\SettingController::class, 'updateDriippreneurPointRate'])->name('settings.update-driippreneur-point-rate');
        Route::put('/settings/wacloud', [App\Http\Controllers\Admin\SettingController::class, 'updateWACloudSettings'])->name('settings.update-wacloud');
        Route::put('/settings/expeditions', [App\Http\Controllers\Admin\SettingController::class, 'updateExpeditions'])->name('settings.update-expeditions');
        Route::get('/settings/wacloud/quota', [App\Http\Controllers\Admin\SettingController::class, 'getWACloudQuota'])->name('settings.wacloud-quota');

        // DRiiPPreneur Management
        Route::get('/driippreneurs', [App\Http\Controllers\Admin\DriippreneurController::class, 'index'])->name('driippreneurs.index');
        Route::get('/driippreneurs/{driippreneur}', [App\Http\Controllers\Admin\DriippreneurController::class, 'show'])->name('driippreneurs.show');
        Route::put('/driippreneurs/{driippreneur}/approve', [App\Http\Controllers\Admin\DriippreneurController::class, 'approve'])->name('driippreneurs.approve');
        Route::put('/driippreneurs/{driippreneur}/reject', [App\Http\Controllers\Admin\DriippreneurController::class, 'reject'])->name('driippreneurs.reject');

        // Point Withdrawals Management
        Route::get('/point-withdrawals', [App\Http\Controllers\Admin\PointWithdrawalController::class, 'index'])->name('point-withdrawals.index');
        Route::get('/point-withdrawals/{pointWithdrawal}', [App\Http\Controllers\Admin\PointWithdrawalController::class, 'show'])->name('point-withdrawals.show');
        Route::put('/point-withdrawals/{pointWithdrawal}/approve', [App\Http\Controllers\Admin\PointWithdrawalController::class, 'approve'])->name('point-withdrawals.approve');
        Route::put('/point-withdrawals/{pointWithdrawal}/reject', [App\Http\Controllers\Admin\PointWithdrawalController::class, 'reject'])->name('point-withdrawals.reject');
        Route::put('/point-withdrawals/{pointWithdrawal}/complete', [App\Http\Controllers\Admin\PointWithdrawalController::class, 'complete'])->name('point-withdrawals.complete');
        Route::put('/point-withdrawals/{pointWithdrawal}/status', [App\Http\Controllers\Admin\PointWithdrawalController::class, 'updateStatus'])->name('point-withdrawals.update-status');

        // Distributor Management
        Route::get('/distributors', [App\Http\Controllers\Admin\DistributorController::class, 'index'])->name('distributors.index');
        Route::get('/distributors/applications', [App\Http\Controllers\Admin\DistributorController::class, 'applications'])->name('distributors.applications');
        Route::get('/distributors/applications/{user}', [App\Http\Controllers\Admin\DistributorController::class, 'showApplication'])->name('distributors.application-detail');
        Route::post('/distributors/applications/{user}/approve', [App\Http\Controllers\Admin\DistributorController::class, 'approve'])->name('distributors.approve');
        Route::post('/distributors/applications/{user}/reject', [App\Http\Controllers\Admin\DistributorController::class, 'reject'])->name('distributors.reject');
        Route::get('/distributors/create', [App\Http\Controllers\Admin\DistributorController::class, 'create'])->name('distributors.create');
        Route::post('/distributors', [App\Http\Controllers\Admin\DistributorController::class, 'store'])->name('distributors.store');
        Route::get('/distributors/get-regencies', [App\Http\Controllers\Admin\DistributorController::class, 'getRegencies'])->name('distributors.get-regencies');
        Route::get('/distributors/{distributor}', [App\Http\Controllers\Admin\DistributorController::class, 'show'])->name('distributors.show');
        Route::get('/distributors/{distributor}/edit', [App\Http\Controllers\Admin\DistributorController::class, 'edit'])->name('distributors.edit');
        Route::put('/distributors/{distributor}', [App\Http\Controllers\Admin\DistributorController::class, 'update'])->name('distributors.update');
        Route::post('/distributors/{distributor}/sync-products', [App\Http\Controllers\Admin\DistributorController::class, 'syncProducts'])->name('distributors.sync-products');
        Route::delete('/distributors/{distributor}', [App\Http\Controllers\Admin\DistributorController::class, 'destroy'])->name('distributors.destroy');
    });
});

// Warehouse Panel Routes
Route::prefix('warehouse')->name('warehouse.')->group(function () {
    // Warehouse Login (Guest only)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [App\Http\Controllers\Warehouse\Auth\WarehouseLoginController::class, 'create'])->name('login');
        Route::post('/login', [App\Http\Controllers\Warehouse\Auth\WarehouseLoginController::class, 'store']);
    });

    // Warehouse Logout
    Route::post('/logout', [App\Http\Controllers\Warehouse\Auth\WarehouseLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    // Warehouse Protected Routes
    Route::middleware(['auth', 'warehouse'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Warehouse\DashboardController::class, 'index'])->name('dashboard');
        
        // Stock Management
        Route::get('/stock', [App\Http\Controllers\Warehouse\StockController::class, 'index'])->name('stock.index');
        Route::put('/stock/{stock}', [App\Http\Controllers\Warehouse\StockController::class, 'update'])->name('stock.update');
        Route::post('/stock/sync', [App\Http\Controllers\Warehouse\StockController::class, 'sync'])->name('stock.sync');
        
        // Orders Management
        Route::get('/orders', [App\Http\Controllers\Warehouse\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\Warehouse\OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}', [App\Http\Controllers\Warehouse\OrderController::class, 'update'])->name('orders.update');
    });
});

// DRiiPPreneur Panel Routes
Route::prefix('driippreneur')->name('driippreneur.')->group(function () {
    // DRiiPPreneur Auth (Guest only)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [App\Http\Controllers\Driippreneur\Auth\DriippreneurLoginController::class, 'create'])->name('login');
        Route::post('/login', [App\Http\Controllers\Driippreneur\Auth\DriippreneurLoginController::class, 'store']);
        Route::get('/register', [App\Http\Controllers\Driippreneur\Auth\DriippreneurRegisterController::class, 'create'])->name('register');
        Route::post('/register', [App\Http\Controllers\Driippreneur\Auth\DriippreneurRegisterController::class, 'store']);
    });

    // DRiiPPreneur Logout
    Route::post('/logout', [App\Http\Controllers\Driippreneur\Auth\DriippreneurLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    // DRiiPPreneur Protected Routes
    Route::middleware(['auth', 'driippreneur'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Driippreneur\DashboardController::class, 'index'])->name('dashboard');
        
        // Stock Management
        Route::get('/stock', [App\Http\Controllers\Driippreneur\StockController::class, 'index'])->name('stock.index');
        Route::put('/stock/{stock}', [App\Http\Controllers\Driippreneur\StockController::class, 'updateStock'])->name('stock.update');
        Route::post('/stock/sync', [App\Http\Controllers\Driippreneur\StockController::class, 'syncProducts'])->name('stock.sync');
    });
});

// Distributor Panel Routes
Route::prefix('distributor')->name('distributor.')->group(function () {
    // Distributor Login (Guest only)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [App\Http\Controllers\Distributor\Auth\DistributorLoginController::class, 'create'])->name('login');
        Route::post('/login', [App\Http\Controllers\Distributor\Auth\DistributorLoginController::class, 'store']);
    });

    // Distributor Logout
    Route::post('/logout', [App\Http\Controllers\Distributor\Auth\DistributorLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    // Distributor Protected Routes
    Route::middleware(['auth', 'distributor'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Distributor\DashboardController::class, 'index'])->name('dashboard');
        
        // Stock Management
        Route::get('/stock', [App\Http\Controllers\Distributor\StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/{stock}/history', [App\Http\Controllers\Distributor\StockController::class, 'history'])->name('stock.history');
        Route::put('/stock/{stock}', [App\Http\Controllers\Distributor\StockController::class, 'update'])->name('stock.update');
        Route::post('/stock/sync', [App\Http\Controllers\Distributor\StockController::class, 'sync'])->name('stock.sync');

        // Point of Sales (POS)
        Route::get('/pos', [App\Http\Controllers\Distributor\PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/search-products', [App\Http\Controllers\Distributor\PosController::class, 'searchProducts'])->name('pos.search-products');
        Route::post('/pos/add-to-cart', [App\Http\Controllers\Distributor\PosController::class, 'addToCart'])->name('pos.add-to-cart');
        Route::get('/pos/get-cart', [App\Http\Controllers\Distributor\PosController::class, 'getCart'])->name('pos.get-cart');
        Route::post('/pos/update-cart/{productId}', [App\Http\Controllers\Distributor\PosController::class, 'updateCart'])->name('pos.update-cart');
        Route::post('/pos/remove-from-cart/{productId}', [App\Http\Controllers\Distributor\PosController::class, 'removeFromCart'])->name('pos.remove-from-cart');
        Route::post('/pos/clear-cart', [App\Http\Controllers\Distributor\PosController::class, 'clearCart'])->name('pos.clear-cart');
        Route::post('/pos/checkout', [App\Http\Controllers\Distributor\PosController::class, 'checkout'])->name('pos.checkout');

        // Manage Orders (Orders masuk ke warehouse distributor)
        Route::get('/manage-orders', [App\Http\Controllers\Distributor\ManageOrderController::class, 'index'])->name('manage-orders.index');
        Route::get('/manage-orders/{order}', [App\Http\Controllers\Distributor\ManageOrderController::class, 'show'])->name('manage-orders.show');
        Route::put('/manage-orders/{order}', [App\Http\Controllers\Distributor\ManageOrderController::class, 'update'])->name('manage-orders.update');
        Route::post('/manage-orders/{order}/convert-to-stock', [App\Http\Controllers\Distributor\ManageOrderController::class, 'convertToStock'])->name('manage-orders.convert-to-stock');
        Route::post('/manage-orders/{order}/convert-to-stock', [App\Http\Controllers\Distributor\ManageOrderController::class, 'convertToStock'])->name('manage-orders.convert-to-stock');

        // Order Management
        Route::get('/orders/products', [App\Http\Controllers\Distributor\OrderController::class, 'products'])->name('orders.products');
        Route::get('/orders/cart', [App\Http\Controllers\Distributor\OrderController::class, 'cart'])->name('orders.cart');
        Route::post('/orders/add-to-cart', [App\Http\Controllers\Distributor\OrderController::class, 'addToCart'])->name('orders.add-to-cart');
        Route::put('/orders/cart/{cart}', [App\Http\Controllers\Distributor\OrderController::class, 'updateCart'])->name('orders.update-cart');
        Route::delete('/orders/cart/{cart}', [App\Http\Controllers\Distributor\OrderController::class, 'removeFromCart'])->name('orders.remove-from-cart');
        Route::get('/orders/checkout', [App\Http\Controllers\Distributor\OrderController::class, 'checkout'])->name('orders.checkout');
        Route::post('/orders/checkout', [App\Http\Controllers\Distributor\OrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/calculate-shipping', [App\Http\Controllers\Distributor\OrderController::class, 'calculateShipping'])->name('orders.calculate-shipping');
        Route::get('/orders/expedition-services', [App\Http\Controllers\Distributor\OrderController::class, 'getExpeditionServices'])->name('orders.expedition-services');
        Route::get('/orders/success/{order}', [App\Http\Controllers\Distributor\OrderController::class, 'success'])->name('orders.success');
        Route::get('/orders/history', [App\Http\Controllers\Distributor\OrderController::class, 'history'])->name('orders.history');
        Route::get('/orders/{order}', [App\Http\Controllers\Distributor\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/convert-to-stock', [App\Http\Controllers\Distributor\OrderController::class, 'convertToStock'])->name('orders.convert-to-stock');
    });
});
