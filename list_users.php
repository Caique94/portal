<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = App\Models\User::select('id', 'name', 'papel')->get();

echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo $user->id . " - " . $user->name . " (" . $user->papel . ")\n";
}
