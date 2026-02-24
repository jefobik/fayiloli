<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/login', 'GET');
$request->headers->set('Host', 'admin.fcta.gov.local');
$response = $kernel->handle($request);

$cookies = [];
foreach ($response->headers->getCookies() as $cookie) {
    echo "Cookie: " . $cookie->getName() . "\n";
    echo "Domain: " . ($cookie->getDomain() ?: 'null') . "\n";
    echo "Path: " . $cookie->getPath() . "\n";
    echo "Secure: " . ($cookie->isSecure() ? 'true' : 'false') . "\n";
    echo "SameSite: " . ($cookie->getSameSite() ?: 'null') . "\n";
    echo "---\n";
}
