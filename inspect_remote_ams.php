<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$conn = Illuminate\Support\Facades\DB::connection('ams_remote');

// Check users
$users = $conn->table('users')->get(['id', 'name', 'email', 'role']);
echo "Users in u621755393_ams (" . $users->count() . "):\n";
foreach ($users as $u) {
    echo " - ID {$u->id}: {$u->name} ({$u->email}) - Role: {$u->role}\n";
}

// Check migrations table
if (Illuminate\Support\Facades\Schema::connection('ams_remote')->hasTable('migrations')) {
    $migrations = $conn->table('migrations')->get();
    echo "\nMigrations in u621755393_ams (" . $migrations->count() . "):\n";
    foreach ($migrations as $m) {
        echo " - Batch {$m->batch}: {$m->migration}\n";
    }
}

// Check distribution_enrollments
echo "\nHas distribution_enrollments: " . (Illuminate\Support\Facades\Schema::connection('ams_remote')->hasTable('distribution_enrollments') ? 'YES' : 'NO') . "\n";
echo "Has funding_sources: " . (Illuminate\Support\Facades\Schema::connection('ams_remote')->hasTable('funding_sources') ? 'YES' : 'NO') . "\n";
echo "Has donations: " . (Illuminate\Support\Facades\Schema::connection('ams_remote')->hasTable('donations') ? 'YES' : 'NO') . "\n";
echo "Has settings: " . (Illuminate\Support\Facades\Schema::connection('ams_remote')->hasTable('settings') ? 'YES' : 'NO') . "\n";
echo "Has ggms_consolidated_transactions: " . (Illuminate\Support\Facades\Schema::connection('ams_remote')->hasTable('ggms_consolidated_transactions') ? 'YES' : 'NO') . "\n";
