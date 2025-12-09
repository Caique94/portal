<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking columns in relatorio_fechamento table...\n\n";

$columns = DB::select("SELECT column_name, data_type, column_default FROM information_schema.columns WHERE table_name = 'relatorio_fechamento' ORDER BY ordinal_position");

foreach ($columns as $col) {
    echo "- {$col->column_name} ({$col->data_type})" . ($col->column_default ? " DEFAULT {$col->column_default}" : "") . "\n";
}

echo "\n";
echo "Database: " . DB::connection()->getDatabaseName() . "\n";
echo "Connection: " . config('database.default') . "\n";
