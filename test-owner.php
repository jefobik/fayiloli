<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$docs = App\Models\Document::withoutGlobalScopes()->get();
foreach ($docs as $d) {
    if ($d->getRawOriginal('owner') === 'admin') {
        echo "Found document ID {$d->id} with raw owner admin\n";
    }
}
