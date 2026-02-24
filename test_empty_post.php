<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Force CSRF pass for testing validation by disabling the middleware temporarily
$app->instance(\App\Http\Middleware\VerifyCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

foreach ([
    ['email' => 'superadmin@fcta.gov.local', 'password' => 'passw0rd!'], // valid
    [], // empty payload like disabled inputs
] as $payload) {
    echo "--- Testing payload: " . json_encode($payload) . " ---\n";
    $request = Illuminate\Http\Request::create('/login', 'POST', $payload);
    $request->headers->set('Host', 'admin.fcta.gov.local');
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 302 && $response->headers->has('Location')) {
        echo "Location: " . $response->headers->get('Location') . "\n";
    }
}
