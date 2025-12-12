<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Cliente;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\DB;

echo "=== PROCURANDO CLIENTES COM OSs ===\n\n";

// Buscar clientes que têm OSs
$clientesComOS = DB::table('ordem_servico as os')
    ->join('cliente as c', 'os.cliente_id', '=', 'c.id')
    ->select('c.id', 'c.codigo', 'c.loja', 'c.nome', 'c.nome_fantasia', DB::raw('COUNT(os.id) as total_os'))
    ->where('os.status', '<=', 5)
    ->groupBy('c.id', 'c.codigo', 'c.loja', 'c.nome', 'c.nome_fantasia')
    ->orderByDesc('total_os')
    ->limit(10)
    ->get();

if ($clientesComOS->count() > 0) {
    echo "✓ Top 10 clientes com mais OSs:\n\n";
    foreach ($clientesComOS as $c) {
        echo "ID: {$c->id}\n";
        echo "Código: {$c->codigo}-{$c->loja}\n";
        echo "Nome: {$c->nome}\n";
        echo "Nome Fantasia: {$c->nome_fantasia}\n";
        echo "Total OSs: {$c->total_os}\n";
        echo "---\n";
    }
} else {
    echo "❌ Nenhum cliente com OSs encontrado!\n";
}
