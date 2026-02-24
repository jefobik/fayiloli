<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => '',
    'password' => ''
]);
$request->headers->set('Host', 'admin.fcta.gov.local');

// Force CSRF pass for testing validation by disabling the middleware temporarily
$app->instance(\App\Http\Middleware\VerifyCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Headers: " . json_encode($response->headers->all()) . "\n";
if ($response->getStatusCode() == 302 && $response->headers->has('Location')) {
    echo "Location: " . $response->headers->get('Location') . "\n";
    if ($response->headers->has('set-cookie')) {
       echo current($response->headers->all('set-cookie'));
    }
}
