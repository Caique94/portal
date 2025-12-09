<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Find a valid consultant
$consultor = App\Models\User::where('papel', 'consultor')->first();

if (!$consultor) {
    echo "❌ No consultant found in database!\n";
    exit(1);
}

echo "Testing with consultant: " . $consultor->name . " (ID: " . $consultor->id . ")\n\n";

try {
    $relatorio = App\Models\RelatorioFechamento::create([
        'consultor_id' => $consultor->id,
        'tipo' => 'cliente',
        'data_inicio' => '2025-06-05',
        'data_fim' => '2025-12-05',
        'valor_total' => 30.00,
        'total_os' => 1,
        'status' => 'rascunho',
    ]);

    echo "✅ Success! Created report ID: " . $relatorio->id . "\n";
    echo "Tipo: " . $relatorio->tipo . "\n";
    echo "Status: " . $relatorio->status . "\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
