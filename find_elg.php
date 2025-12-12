<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PROCURANDO CLIENTE ELG ===" . PHP_EOL . PHP_EOL;

$clientes = DB::table('cliente')
    ->where('nome', 'LIKE', '%ELG%')
    ->orWhere('nome_fantasia', 'LIKE', '%ELG%')
    ->get(['id', 'codigo', 'nome', 'nome_fantasia', 'tipo']);

if ($clientes->count() > 0) {
    echo "Clientes encontrados:" . PHP_EOL;
    foreach ($clientes as $c) {
        echo "ID: {$c->id} | Código: {$c->codigo} | Nome: {$c->nome} | Fantasia: {$c->nome_fantasia} | Tipo: {$c->tipo}" . PHP_EOL;
    }
} else {
    echo "Nenhum cliente com 'ELG' no nome encontrado." . PHP_EOL . PHP_EOL;
    echo "Listando os primeiros 20 clientes:" . PHP_EOL;

    $todosClientes = DB::table('cliente')
        ->orderBy('id')
        ->limit(20)
        ->get(['id', 'codigo', 'nome', 'nome_fantasia']);

    foreach ($todosClientes as $c) {
        echo "ID: {$c->id} | Código: {$c->codigo} | Nome: {$c->nome} | Fantasia: {$c->nome_fantasia}" . PHP_EOL;
    }
}
