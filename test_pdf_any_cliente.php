<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RelatorioFechamento;
use App\Models\OrdemServico;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== CRIANDO FECHAMENTO CLIENTE (PRIMEIRO CLIENTE COM OSs) ===" . PHP_EOL . PHP_EOL;

// Buscar primeiro cliente que tenha OSs
$primeiraOS = OrdemServico::with('cliente')->whereNotNull('cliente_id')->first();

if (!$primeiraOS || !$primeiraOS->cliente) {
    echo "✗ Nenhuma OS com cliente encontrada!" . PHP_EOL;
    exit;
}

$cliente = $primeiraOS->cliente;
echo "✓ Cliente encontrado: {$cliente->nome} (ID: {$cliente->id})" . PHP_EOL;

// Definir período amplo para pegar todas as OSs
$dataInicio = '2024-01-01';
$dataFim = '2026-12-31';

echo "Período: {$dataInicio} a {$dataFim}" . PHP_EOL . PHP_EOL;

// Buscar OSs do período
$ordens = OrdemServico::with(['cliente', 'produtoTabela.produto', 'consultor'])
    ->where('cliente_id', $cliente->id)
    ->where(function($query) {
        $query->where('status', '<=', 5)
              ->orWhereRaw('status::integer <= 5');
    })
    ->whereBetween('created_at', [
        Carbon::parse($dataInicio)->startOfDay(),
        Carbon::parse($dataFim)->endOfDay(),
    ])
    ->get();

echo "Total de OSs encontradas: {$ordens->count()}" . PHP_EOL;

if ($ordens->count() === 0) {
    echo "⚠ Nenhuma OS encontrada no período!" . PHP_EOL;

    // Tentar sem filtro de status
    $ordens = OrdemServico::with(['cliente', 'produtoTabela.produto', 'consultor'])
        ->where('cliente_id', $cliente->id)
        ->whereBetween('created_at', [
            Carbon::parse($dataInicio)->startOfDay(),
            Carbon::parse($dataFim)->endOfDay(),
        ])
        ->get();

    echo "Total sem filtro de status: {$ordens->count()}" . PHP_EOL;

    if ($ordens->count() === 0) {
        exit;
    }
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

// Criar fechamento
$fechamento = new RelatorioFechamento();
$fechamento->tipo = 'cliente';
$fechamento->consultor_id = null;
$fechamento->data_inicio = $dataInicio;
$fechamento->data_fim = $dataFim;
$fechamento->valor_total = $valorTotal;
$fechamento->total_os = $ordens->count();
$fechamento->status = 'rascunho';
$fechamento->observacoes = "Fechamento de teste gerado automaticamente para o cliente {$cliente->nome}";
$fechamento->save();

echo "✓ Fechamento criado: ID #{$fechamento->id}" . PHP_EOL;
echo "Status: {$fechamento->status}" . PHP_EOL;
echo "Valor Total: R$ " . number_format($fechamento->valor_total, 2, ',', '.') . PHP_EOL;
echo PHP_EOL;

// Gerar PDF
echo "=== GERANDO PDF ===" . PHP_EOL;

try {
    $ordemServicos = $ordens; // Usar as OSs que já buscamos

    $relatorioFechamento = $fechamento; // Renomear para compatibilidade com a view

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
        'relatorio-fechamento.pdf-cliente',
        compact('relatorioFechamento', 'ordemServicos')
    );

    $pdf->setPaper('a4', 'portrait');

    $pdfPath = storage_path("app/fechamento_cliente_{$cliente->id}_teste.pdf");
    $pdf->save($pdfPath);

    echo "✓ PDF gerado com sucesso!" . PHP_EOL;
    echo "Localização: {$pdfPath}" . PHP_EOL;
    echo "Tamanho: " . number_format(filesize($pdfPath) / 1024, 2) . " KB" . PHP_EOL;
    echo PHP_EOL;
    echo "Para visualizar, abra o arquivo no caminho acima." . PHP_EOL;

} catch (\Exception $e) {
    echo "✗ Erro ao gerar PDF:" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
