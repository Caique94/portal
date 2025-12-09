<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Database conectado: " . DB::connection()->getDatabaseName() . "\n";
echo "Connection: " . config('database.default') . "\n\n";

echo "Colunas da tabela 'produto':\n";
$columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'produto' ORDER BY ordinal_position");

foreach ($columns as $col) {
    echo "  - " . $col->column_name . "\n";
}

// Verificar se is_presencial existe
$hasColumn = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'produto' AND column_name = 'is_presencial'");

echo "\n";
if (count($hasColumn) > 0) {
    echo "✅ Coluna 'is_presencial' EXISTE\n";
} else {
    echo "❌ Coluna 'is_presencial' NÃO EXISTE\n";
}
