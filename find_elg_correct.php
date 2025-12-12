<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PROCURANDO CLIENTE ELG ===" . PHP_EOL . PHP_EOL;

// Buscar pelo nome exato que vi no screenshot
$clientes = DB::table('cliente')
    ->where('nome', 'ELG')
    ->orWhere('nome_fantasia', 'ELG')
    ->get();

if ($clientes->count() > 0) {
    echo "✓ Cliente(s) ELG encontrado(s):" . PHP_EOL . PHP_EOL;
    foreach ($clientes as $c) {
        echo "ID: {$c->id}" . PHP_EOL;
        echo "Código: {$c->codigo}-{$c->loja}" . PHP_EOL;
        echo "Nome: {$c->nome}" . PHP_EOL;
        echo "Nome Fantasia: {$c->nome_fantasia}" . PHP_EOL;
        echo "Tipo: {$c->tipo}" . PHP_EOL;
        echo "---" . PHP_EOL;
    }
} else {
    echo "Cliente ELG não encontrado. Listando todos os clientes:" . PHP_EOL . PHP_EOL;

    $todos = DB::table('cliente')
        ->orderBy('id')
        ->limit(20)
        ->get(['id', 'codigo', 'loja', 'nome', 'nome_fantasia']);

    foreach ($todos as $c) {
        echo "ID: {$c->id} | Código: {$c->codigo}-{$c->loja} | Nome: {$c->nome} | Fantasia: {$c->nome_fantasia}" . PHP_EOL;
    }
}
