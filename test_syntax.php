<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Mock the auth
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

// Add cart item
\App\Models\Cart::create([
    'id' => (string) \Illuminate\Support\Str::uuid(),
    'user_id' => $user->id,
    'product_id' => \App\Models\Product::first()->id,
    'quantity' => 1
]);

$request = Illuminate\Http\Request::create('/checkout', 'GET');
$response = $kernel->handle($request);
echo $response->getContent();

// Clean up
\App\Models\Cart::where('user_id', $user->id)->delete();
