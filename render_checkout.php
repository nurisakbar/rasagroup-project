<?php
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

$request = Illuminate\Http\Request::create('/checkout', 'GET');
$response = app()->handle($request);
echo $response->getContent();
