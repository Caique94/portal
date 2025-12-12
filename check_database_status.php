<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO STATUS DO BANCO DE DADOS ===\n\n";

try {
    // Test connection
    DB::connection()->getPdo();
    echo "✓ Conexão com banco de dados OK\n\n";

    // Count tables
    $totalClientes = DB::table('cliente')->count();
    $totalOS = DB::table('ordem_servico')->count();
    $totalFechamentos = DB::table('relatorio_fechamento')->count();
    $totalUsers = DB::table('users')->count();

    echo "Contadores:\n";
    echo "- Clientes: {$totalClientes}\n";
    echo "- Ordens de Serviço: {$totalOS}\n";
    echo "- Relatórios de Fechamento: {$totalFechamentos}\n";
    echo "- Usuários: {$totalUsers}\n\n";

    // Check relatorio_fechamento structure
    echo "=== ESTRUTURA relatorio_fechamento ===\n";
    $columns = DB::select("SELECT column_name, data_type, is_nullable
                          FROM information_schema.columns
                          WHERE table_name = 'relatorio_fechamento'
                          ORDER BY ordinal_position");

    foreach ($columns as $col) {
        echo "- {$col->column_name} ({$col->data_type}) " .
             ($col->is_nullable === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }

    echo "\n=== ÚLTIMOS FECHAMENTOS ===\n";
    $fechamentos = DB::table('relatorio_fechamento')
        ->orderByDesc('id')
        ->limit(5)
        ->get();

    if ($fechamentos->count() > 0) {
        foreach ($fechamentos as $f) {
            echo "ID: {$f->id} | Tipo: {$f->tipo} | ";
            echo "Consultor: " . ($f->consultor_id ?? 'NULL') . " | ";
            echo "Cliente: " . ($f->cliente_id ?? 'NULL') . " | ";
            echo "Status: {$f->status}\n";
        }
    } else {
        echo "Nenhum fechamento encontrado.\n";
    }

} catch (\Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
