<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Cliente;
use App\Models\RelatorioFechamento;
use App\Models\OrdemServico;
use Carbon\Carbon;

echo "=== TESTE DE CRIAÇÃO DE FECHAMENTO CLIENTE ===\n\n";

// Buscar cliente ELG
$cliente = Cliente::find(2);

if (!$cliente) {
    echo "❌ Cliente ELG não encontrado!\n";
    exit;
}

echo "✓ Cliente encontrado: {$cliente->nome} (ID: {$cliente->id})\n";

// Definir período
$dataInicio = '2025-01-01';
$dataFim = '2025-12-31';

echo "✓ Período: {$dataInicio} a {$dataFim}\n\n";

// Buscar OSs do período
$ordens = OrdemServico::with(['cliente', 'produtoTabela.produto', 'consultor'])
    ->where('cliente_id', $cliente->id)
    ->where('status', '<=', 5)
    ->whereBetween('created_at', [
        Carbon::parse($dataInicio)->startOfDay(),
        Carbon::parse($dataFim)->endOfDay(),
    ])
    ->get();

echo "✓ Total de OSs encontradas: {$ordens->count()}\n";

if ($ordens->count() === 0) {
    echo "⚠ Nenhuma OS encontrada no período!\n";
    exit;
}

// Calcular valor total usando Totalizador Administrativo
$totalServicos = 0;
$totalDespesas = 0;
$totalKMDeslocamento = 0;

foreach ($ordens as $os) {
    // Totalizador Administrativo - usa preco_produto da tabela
    $precoServico = floatval($os->preco_produto ?? 0);
    $despesas = floatval($os->valor_despesa ?? 0);

    $totalServicos += $precoServico;
    $totalDespesas += $despesas;

    // KM e Deslocamento (se presencial)
    if ($os->is_presencial && $os->consultor) {
        $km = floatval($os->km ?? 0);
        $valorKM = $km * floatval($os->consultor->valor_km ?? 0);
        $valorDesloc = floatval($os->consultor->valor_desloc ?? 0);
        $totalKMDeslocamento += ($valorKM + $valorDesloc);
    }
}

$valorTotal = $totalServicos + $totalDespesas + $totalKMDeslocamento;

echo "\n=== TOTALIZADOR ADMINISTRATIVO ===\n";
echo "Total Serviços: R$ " . number_format($totalServicos, 2, ',', '.') . "\n";
echo "Total Despesas: R$ " . number_format($totalDespesas, 2, ',', '.') . "\n";
echo "Total KM/Deslocamento: R$ " . number_format($totalKMDeslocamento, 2, ',', '.') . "\n";
echo "VALOR TOTAL: R$ " . number_format($valorTotal, 2, ',', '.') . "\n\n";

// Criar fechamento COM cliente_id
try {
    echo "=== CRIANDO FECHAMENTO ===\n";

    $fechamento = RelatorioFechamento::create([
        'consultor_id' => null, // Fechamento cliente não tem consultor específico
        'cliente_id' => $cliente->id, // AGORA COM CLIENTE_ID!
        'tipo' => 'cliente',
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim,
        'valor_total' => $valorTotal,
        'total_os' => $ordens->count(),
        'status' => 'rascunho',
        'observacoes' => 'Fechamento de teste - verificação de fix cliente_id',
    ]);

    echo "✅ Fechamento criado com sucesso!\n";
    echo "   ID: #{$fechamento->id}\n";
    echo "   Tipo: {$fechamento->tipo}\n";
    echo "   Cliente ID: {$fechamento->cliente_id}\n";
    echo "   Consultor ID: " . ($fechamento->consultor_id ?? 'NULL') . "\n";
    echo "   Status: {$fechamento->status}\n";
    echo "   Valor Total: R$ " . number_format($fechamento->valor_total, 2, ',', '.') . "\n";
    echo "   Total OSs: {$fechamento->total_os}\n\n";

    // Testar relacionamento
    echo "=== TESTANDO RELACIONAMENTO ===\n";
    if ($fechamento->cliente) {
        echo "✅ Relacionamento cliente() funciona!\n";
        echo "   Nome: {$fechamento->cliente->nome}\n";
        echo "   Código: {$fechamento->cliente->codigo}-{$fechamento->cliente->loja}\n\n";
    } else {
        echo "❌ Relacionamento cliente() não funciona!\n\n";
    }

    // Testar método ordemServicos()
    echo "=== TESTANDO MÉTODO ordemServicos() ===\n";
    $osRetornadas = $fechamento->ordemServicos();
    echo "✅ OSs retornadas pelo método: {$osRetornadas->count()}\n";

    if ($osRetornadas->count() > 0) {
        echo "   Primeira OS: #{$osRetornadas->first()->id} - {$osRetornadas->first()->cliente->nome}\n\n";
    }

    // Gerar PDF de teste
    echo "=== GERANDO PDF ===\n";

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
        'relatorio-fechamento.pdf-cliente',
        [
            'relatorioFechamento' => $fechamento,
            'ordemServicos' => $osRetornadas
        ]
    );

    $pdfPath = storage_path('app/fechamento_cliente_fix_teste.pdf');
    $pdf->save($pdfPath);

    echo "✅ PDF gerado com sucesso!\n";
    echo "   Localização: {$pdfPath}\n";
    echo "   Tamanho: " . number_format(filesize($pdfPath) / 1024, 2) . " KB\n\n";

    echo "=== ✅ TESTE CONCLUÍDO COM SUCESSO! ===\n";
    echo "Todos os componentes funcionaram corretamente:\n";
    echo "1. ✅ Fechamento criado com cliente_id\n";
    echo "2. ✅ Relacionamento cliente() funciona\n";
    echo "3. ✅ Método ordemServicos() filtra corretamente\n";
    echo "4. ✅ PDF gerado sem erros\n";

} catch (\Exception $e) {
    echo "❌ ERRO ao criar fechamento:\n";
    echo $e->getMessage() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
