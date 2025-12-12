<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== QUERY DIRETA PARA ENCONTRAR ELG ===" . PHP_EOL . PHP_EOL;

try {
    // Query direta no banco
    $result = DB::select("SELECT id, codigo, loja, nome, nome_fantasia, tipo FROM cliente WHERE nome = 'ELG' OR nome_fantasia = 'ELG'");

    if (count($result) > 0) {
        echo "✓ Cliente ELG encontrado!" . PHP_EOL . PHP_EOL;
        foreach ($result as $row) {
            echo "ID: {$row->id}" . PHP_EOL;
            echo "Código: {$row->codigo}-{$row->loja}" . PHP_EOL;
            echo "Nome: {$row->nome}" . PHP_EOL;
            echo "Nome Fantasia: {$row->nome_fantasia}" . PHP_EOL;
            echo "Tipo: {$row->tipo}" . PHP_EOL;
            echo "---" . PHP_EOL . PHP_EOL;

            // Buscar OSs para este cliente
            $totalOS = DB::select("SELECT COUNT(*) as total FROM ordem_servico WHERE cliente_id = ?", [$row->id]);
            echo "Total de OSs: " . $totalOS[0]->total . PHP_EOL;
        }
    } else {
        echo "Cliente ELG não encontrado. Listando os primeiros 10 clientes:" . PHP_EOL . PHP_EOL;

        $todos = DB::select("SELECT id, codigo, loja, nome, nome_fantasia FROM cliente ORDER BY id LIMIT 10");

        foreach ($todos as $row) {
            echo "ID: {$row->id} | {$row->codigo}-{$row->loja} | {$row->nome} | {$row->nome_fantasia}" . PHP_EOL;
        }
    }
} catch (\Exception $e) {
    echo "Erro: " . $e->getMessage() . PHP_EOL;
}
