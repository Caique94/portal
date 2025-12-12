<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Cliente;
use App\Models\RelatorioFechamento;
use App\Models\OrdemServico;
use Carbon\Carbon;

echo "=== CRIANDO FECHAMENTO CLIENTE ELG ===" . PHP_EOL . PHP_EOL;

// Buscar cliente ELG
$cliente = Cliente::find(2);

if (!$cliente) {
    echo "Cliente ELG não encontrado!" . PHP_EOL;
    exit;
}

echo "✓ Cliente encontrado: {$cliente->nome}" . PHP_EOL;

// Definir período
$dataInicio = '2025-01-01';
$dataFim = '2025-12-31';

echo "Período: {$dataInicio} a {$dataFim}" . PHP_EOL . PHP_EOL;

// Buscar OSs do período
$ordens = OrdemServico::with(['cliente', 'produtoTabela.produto', 'consultor'])
    ->where('cliente_id', $cliente->id)
    ->where('status', '<=', 5)
    ->whereBetween('created_at', [
        Carbon::parse($dataInicio)->startOfDay(),
        Carbon::parse($dataFim)->endOfDay(),
    ])
    ->get();

echo "Total de OSs encontradas: {$ordens->count()}" . PHP_EOL;

if ($ordens->count() === 0) {
    echo "⚠ Nenhuma OS encontrada no período!" . PHP_EOL;
    exit;
}

// Calcular valor total
$totalServicos = 0;
$totalDespesas = 0;
$totalKMDeslocamento = 0;

foreach ($ordens as $os) {
    $precoServico = floatval($os->preco_produto ?? 0);
    $despesas = floatval($os->valor_despesa ?? 0);

    $totalServicos += $precoServico;
    $totalDespesas += $despesas;

    if ($os->is_presencial && $os->consultor) {
        $km = floatval($os->km ?? 0);
        $valorKM = $km * floatval($os->consultor->valor_km ?? 0);
        $valorDesloc = floatval($os->consultor->valor_desloc ?? 0);
        $totalKMDeslocamento += ($valorKM + $valorDesloc);
    }
}

$valorTotal = $totalServicos + $totalDespesas + $totalKMDeslocamento;

echo PHP_EOL;
echo "=== TOTALIZADOR ===" . PHP_EOL;
echo "Total Serviços: R$ " . number_format($totalServicos, 2, ',', '.') . PHP_EOL;
echo "Total Despesas: R$ " . number_format($totalDespesas, 2, ',', '.') . PHP_EOL;
echo "Total KM/Deslocamento: R$ " . number_format($totalKMDeslocamento, 2, ',', '.') . PHP_EOL;
echo "VALOR TOTAL: R$ " . number_format($valorTotal, 2, ',', '.') . PHP_EOL;
echo PHP_EOL;

// Criar ou atualizar fechamento
$fechamento = RelatorioFechamento::updateOrCreate(
    [
        'tipo' => 'cliente',
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim,
        'consultor_id' => null, // Fechamento cliente não tem consultor específico
    ],
    [
        'valor_total' => $valorTotal,
        'total_os' => $ordens->count(),
        'status' => 'rascunho',
        'observacoes' => 'Fechamento de teste gerado automaticamente para o cliente ELG',
    ]
);

echo "✓ Fechamento criado/atualizado: ID #{$fechamento->id}" . PHP_EOL;
echo "Status: {$fechamento->status}" . PHP_EOL;
echo "Valor Total: R$ " . number_format($fechamento->valor_total, 2, ',', '.') . PHP_EOL;
echo PHP_EOL;

// Gerar PDF
echo "=== GERANDO PDF ===" . PHP_EOL;

try {
    $ordemServicos = $fechamento->ordemServicos();

    if ($ordemServicos->count() === 0) {
        echo "⚠ Método ordemServicos() retornou vazio!" . PHP_EOL;
        echo "Tentando buscar manualmente..." . PHP_EOL;

        $ordemServicos = OrdemServico::with(['cliente', 'produtoTabela.produto', 'consultor'])
            ->where('cliente_id', $cliente->id)
            ->where('status', '<=', 5)
            ->whereBetween('created_at', [
                Carbon::parse($fechamento->data_inicio)->startOfDay(),
                Carbon::parse($fechamento->data_fim)->endOfDay(),
            ])
            ->orderByDesc('id')
            ->get();

        echo "OSs encontradas manualmente: {$ordemServicos->count()}" . PHP_EOL;
    }

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
        'relatorio-fechamento.pdf-cliente',
        compact('fechamento', 'ordemServicos')
    );

    $pdfPath = storage_path('app/fechamento_elg_teste.pdf');
    $pdf->save($pdfPath);

    echo "✓ PDF gerado com sucesso!" . PHP_EOL;
    echo "Localização: {$pdfPath}" . PHP_EOL;
    echo "Tamanho: " . number_format(filesize($pdfPath) / 1024, 2) . " KB" . PHP_EOL;

} catch (\Exception $e) {
    echo "✗ Erro ao gerar PDF:" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
