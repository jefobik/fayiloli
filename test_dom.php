<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$html = view('livewire.dashboard-stats', [
    'documentCount' => 0, 'folderCount' => 0, 'tagCount' => 0, 'sharedCount' => 0, 'userCount' => 0, 'unreadCount' => 0,
    'docsByExt' => [], 'monthlyLabels' => [], 'monthlyData' => [], 'recentActivity' => [], 'lastUpdated' => now()->format('H:i:s')
])->render();
echo "HTML Length: " . strlen($html) . "\n";
$dom = new DOMDocument();
@$dom->loadHTML($html);
$body = $dom->getElementsByTagName('body')->item(0);
if (!$body) { echo "BODY IS NULL\n"; } else { echo "BODY IS GOOD\n"; }
