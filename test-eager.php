<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = App\Models\Tenant::all();
foreach ($tenants as $t) {
    \Stancl\Tenancy\Facades\Tenancy::initialize($t);
    try {
        $activities = App\Models\MongoActivity::orderByDesc('created_at')->limit(20)->get();
        foreach ($activities as $a) {
            $c = $a->causer; // trigger lazy load
        }
        echo "Tenant {$t->id} okay\n";
    } catch (\Exception $e) {
        echo "Tenant {$t->id} failed: " . $e->getMessage() . "\n";
    }
}
