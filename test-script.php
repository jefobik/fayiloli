<?php
require 'vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = App\Models\Tenant::all();
foreach ($tenants as $t) {
    \Stancl\Tenancy\Facades\Tenancy::initialize($t);
    $owners = \Illuminate\Support\Facades\DB::table('documents')->distinct()->pluck('owner');
    foreach ($owners as $owner) {
        if (!\Illuminate\Support\Str::isUuid($owner) && !is_null($owner)) {
            echo "Tenant {$t->id} has non-UUID owner: {$owner}\n";
            $doc = App\Models\Document::withoutGlobalScopes()->where('owner', $owner)->first();
            var_dump([
                'raw_owner' => $doc->getRawOriginal('owner'),
                'accessor_owner' => $doc->owner,
                'foreign_key' => $doc->ownerUser()->getForeignKeyName(),
            ]);

            // let's eager load and catch the exact exception
            try {
                App\Models\Document::withoutGlobalScopes()->where('owner', $owner)->with('ownerUser')->get();
                echo "Eager load succeeded?!\n";
            } catch (\Exception $e) {
                echo "Eager load failed: " . $e->getMessage() . "\n";
            }
        }
    }
}
