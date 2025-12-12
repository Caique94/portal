<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CLIENTES COM ORDENS DE SERVIÇO ===" . PHP_EOL . PHP_EOL;

$clientesComOS = DB::table('ordem_servico')
    ->join('cliente', 'ordem_servico.cliente_id', '=', 'cliente.id')
    ->selectRaw('cliente.id, cliente.codigo, cliente.nome, cliente.nome_fantasia, count(ordem_servico.id) as total_os')
    ->groupBy('cliente.id', 'cliente.codigo', 'cliente.nome', 'cliente.nome_fantasia')
    ->orderByDesc('total_os')
    ->limit(20)
    ->get();

echo "Top 20 clientes com mais Ordens de Serviço:" . PHP_EOL . PHP_EOL;

foreach ($clientesComOS as $c) {
    echo sprintf(
        "ID: %-4s | Código: %-8s | Nome: %-30s | OSs: %-5s",
        $c->id,
        $c->codigo,
        substr($c->nome, 0, 30),
        $c->total_os
    ) . PHP_EOL;
}

echo PHP_EOL . "=== INFORMAÇÕES ADICIONAIS ===" . PHP_EOL;
echo "Total de clientes no sistema: " . DB::table('cliente')->count() . PHP_EOL;
echo "Total de ordens de serviço: " . DB::table('ordem_servico')->count() . PHP_EOL;
