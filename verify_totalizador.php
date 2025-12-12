<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RelatorioFechamento;
use App\Models\OrdemServico;

echo "=== VERIFICANDO TOTALIZADOR DO FECHAMENTO #31 ===\n\n";

$fechamento = RelatorioFechamento::find(31);

if (!$fechamento) {
    echo "❌ Fechamento #31 não encontrado!\n";
    exit;
}

echo "Fechamento #31:\n";
echo "Cliente: " . ($fechamento->cliente->nome ?? 'N/A') . "\n";
echo "Período: {$fechamento->data_inicio} até {$fechamento->data_fim}\n";
echo "Tipo: {$fechamento->tipo}\n";
echo "Valor armazenado: R$ " . number_format($fechamento->valor_total, 2, ',', '.') . "\n\n";

// Buscar OSs do fechamento
$ordens = $fechamento->ordemServicos();

echo "Total de OSs: {$ordens->count()}\n\n";

// Calcular manualmente usando Totalizador Administrativo
$totalServicos = 0;
$totalDespesas = 0;
$totalKM = 0;
$totalDeslocamento = 0;

echo "=== DETALHAMENTO POR OS ===\n";
echo str_pad("ID", 5) . str_pad("Data", 12) . str_pad("Serviço", 12) . str_pad("Despesas", 12) . str_pad("KM", 10) . str_pad("Desloc", 10) . str_pad("Total OS", 12) . "\n";
echo str_repeat("-", 75) . "\n";

foreach ($ordens as $os) {
    // Totalizador Administrativo
    $precoServico = floatval($os->preco_produto ?? 0);
    $despesas = floatval($os->valor_despesa ?? 0);
    $km = 0;
    $desloc = 0;

    // KM e Deslocamento (se presencial)
    if ($os->is_presencial && $os->consultor) {
        $kmValue = floatval($os->km ?? 0);
        $valorKM = floatval($os->consultor->valor_km ?? 0);
        $km = $kmValue * $valorKM;

        $desloc = floatval($os->consultor->valor_desloc ?? 0);
    }

    $totalOS = $precoServico + $despesas + $km + $desloc;

    echo str_pad("#{$os->id}", 5);
    echo str_pad($os->created_at->format('d/m/Y'), 12);
    echo str_pad("R$ " . number_format($precoServico, 2, ',', '.'), 12);
    echo str_pad("R$ " . number_format($despesas, 2, ',', '.'), 12);
    echo str_pad("R$ " . number_format($km, 2, ',', '.'), 10);
    echo str_pad("R$ " . number_format($desloc, 2, ',', '.'), 10);
    echo str_pad("R$ " . number_format($totalOS, 2, ',', '.'), 12);
    echo "\n";

    $totalServicos += $precoServico;
    $totalDespesas += $despesas;
    $totalKM += $km;
    $totalDeslocamento += $desloc;
}

$totalGeral = $totalServicos + $totalDespesas + $totalKM + $totalDeslocamento;

echo str_repeat("-", 75) . "\n";
echo "\n=== TOTALIZADORES ===\n";
echo "Total Serviços:     R$ " . number_format($totalServicos, 2, ',', '.') . "\n";
echo "Total Despesas:     R$ " . number_format($totalDespesas, 2, ',', '.') . "\n";
echo "Total KM:           R$ " . number_format($totalKM, 2, ',', '.') . "\n";
echo "Total Deslocamento: R$ " . number_format($totalDeslocamento, 2, ',', '.') . "\n";
echo str_repeat("-", 40) . "\n";
echo "TOTAL GERAL:        R$ " . number_format($totalGeral, 2, ',', '.') . "\n\n";

// Comparar com valor armazenado
$diferenca = abs($totalGeral - $fechamento->valor_total);

if ($diferenca < 0.01) {
    echo "✅ Totalizador CORRETO! Valores conferem.\n";
} else {
    echo "❌ DIFERENÇA ENCONTRADA!\n";
    echo "Valor esperado (calculado): R$ " . number_format($totalGeral, 2, ',', '.') . "\n";
    echo "Valor armazenado:           R$ " . number_format($fechamento->valor_total, 2, ',', '.') . "\n";
    echo "Diferença:                  R$ " . number_format($diferenca, 2, ',', '.') . "\n";
}
