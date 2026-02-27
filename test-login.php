<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'admin@example.com',
    'password' => 'password',
    '_token' => csrf_token() // Need actual token or bypass CSRF
]);
// Actually, just testing the endpoint with an HTTP client is easier.
