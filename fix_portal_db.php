<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========================================\n";
echo "Conectando no banco 'portal'\n";
echo "========================================\n\n";

// Forçar conexão com portal
config(['database.connections.pgsql.database' => 'portal']);
DB::purge('pgsql');
DB::reconnect('pgsql');

echo "Database atual: " . DB::connection()->getDatabaseName() . "\n\n";

// Verificar se coluna exists
$hasColumn = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'produto' AND column_name = 'is_presencial'");

if (count($hasColumn) > 0) {
    echo "✅ Coluna 'is_presencial' já existe no banco 'portal'\n";
} else {
    echo "❌ Coluna 'is_presencial' NÃO existe. Criando...\n";
    DB::statement("ALTER TABLE produto ADD COLUMN is_presencial BOOLEAN DEFAULT FALSE");
    echo "✅ Coluna criada com sucesso!\n";
}

// Listar todos os produtos
echo "\nProdutos no banco 'portal':\n";
$produtos = DB::table('produto')->select('id', 'codigo', 'descricao', 'is_presencial')->get();
foreach ($produtos as $p) {
    echo "  ID: {$p->id} | Código: {$p->codigo} | Presencial: " . ($p->is_presencial ? 'SIM' : 'NÃO') . "\n";
}
