<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Cliente;
use App\Models\OrdemServico;
use Carbon\Carbon;

echo "=== BUSCANDO CLIENTE ELG ===" . PHP_EOL;

// Buscar o cliente ELG - já sabemos que é ID=2
$cliente = Cliente::find(2);

if (!$cliente) {
    echo 'Cliente ELG (ID=2) não encontrado!' . PHP_EOL;
    exit;
}

echo "✓ Cliente encontrado!" . PHP_EOL;
echo "ID: {$cliente->id}" . PHP_EOL;
echo "Nome: {$cliente->nome}" . PHP_EOL;
echo "Nome Fantasia: {$cliente->nome_fantasia}" . PHP_EOL;
echo PHP_EOL;

// Verificar quantas OS existem para este cliente
$totalOSCliente = OrdemServico::where('cliente_id', $cliente->id)->count();
echo "Total de OS para este cliente (sem filtros): {$totalOSCliente}" . PHP_EOL;
echo PHP_EOL;

if ($totalOSCliente > 0) {
    // Mostrar range de datas das OS
    $primeiraOS = OrdemServico::where('cliente_id', $cliente->id)->orderBy('created_at')->first();
    $ultimaOS = OrdemServico::where('cliente_id', $cliente->id)->orderBy('created_at', 'desc')->first();

    echo "Primeira OS criada em: " . $primeiraOS->created_at->format('d/m/Y H:i:s') . PHP_EOL;
    echo "Última OS criada em: " . $ultimaOS->created_at->format('d/m/Y H:i:s') . PHP_EOL;
    echo PHP_EOL;

    // Mostrar distribuição por status
    echo "=== DISTRIBUIÇÃO POR STATUS ===" . PHP_EOL;
    $porStatus = OrdemServico::where('cliente_id', $cliente->id)
        ->selectRaw('status, count(*) as total')
        ->groupBy('status')
        ->orderBy('status')
        ->get();

    foreach ($porStatus as $s) {
        echo "Status {$s->status}: {$s->total} OSs" . PHP_EOL;
    }
    echo PHP_EOL;
}

// Montar a query que seria executada
echo "=== QUERY PARA BUSCAR ORDENS DE SERVIÇO ===" . PHP_EOL;
$dataInicio = '2024-01-01';  // Mudei para 2024 para pegar um range maior
$dataFim = '2026-12-31';

$query = OrdemServico::with(['cliente', 'produtoTabela.produto', 'consultor'])
    ->where('cliente_id', $cliente->id)
    ->where('status', '<=', 5)
    ->whereBetween('created_at', [
        Carbon::parse($dataInicio)->startOfDay(),
        Carbon::parse($dataFim)->endOfDay(),
    ]);

// Mostrar a SQL que seria executada
echo "SQL: " . str_replace(['?'], ['%s'], $query->toSql()) . PHP_EOL;
echo "Bindings: " . json_encode($query->getBindings()) . PHP_EOL;
echo PHP_EOL;

// SQL completa com valores substituídos
$sql = $query->toSql();
$bindings = $query->getBindings();
foreach ($bindings as $binding) {
    $value = is_string($binding) ? "'$binding'" : $binding;
    $sql = preg_replace('/\?/', $value, $sql, 1);
}
echo "SQL COMPLETA:" . PHP_EOL;
echo $sql . ";" . PHP_EOL;
echo PHP_EOL;

// Executar e mostrar resultados
$ordens = $query->get();
echo "=== RESULTADOS ===" . PHP_EOL;
echo "Total de Ordens encontradas: {$ordens->count()}" . PHP_EOL;
echo PHP_EOL;

if ($ordens->count() > 0) {
    echo "=== DETALHES DAS ORDENS ===" . PHP_EOL;
    foreach ($ordens as $os) {
        echo "OS #{$os->id} | " .
             "Consultor: " . ($os->consultor ? $os->consultor->name : 'N/A') . " | " .
             "Produto: " . ($os->produtoTabela->produto->nome ?? 'N/A') . " | " .
             "Valor: R$ " . number_format($os->preco_produto ?? 0, 2, ',', '.') . " | " .
             "Data: " . $os->created_at->format('d/m/Y') . PHP_EOL;
    }
    echo PHP_EOL;

    // Calcular totais
    $valorTotal = 0;
    $totalServicos = 0;
    $totalDespesas = 0;
    $totalKMDeslocamento = 0;

    foreach ($ordens as $os) {
        $precoServico = $os->preco_produto ?? 0;
        $despesas = $os->valor_despesa ?? 0;

        $totalServicos += $precoServico;
        $totalDespesas += $despesas;

        if ($os->is_presencial && $os->consultor) {
            $km = $os->km ?? 0;
            $valorKM = $km * ($os->consultor->valor_km ?? 0);
            $valorDeslocamento = $os->consultor->valor_desloc ?? 0;
            $totalKMDeslocamento += ($valorKM + $valorDeslocamento);
        }

        $valorTotal = $totalServicos + $totalDespesas + $totalKMDeslocamento;
    }

    echo "=== TOTALIZADOR (ADMINISTRATIVO) ===" . PHP_EOL;
    echo "Total Serviços: R$ " . number_format($totalServicos, 2, ',', '.') . PHP_EOL;
    echo "Total Despesas: R$ " . number_format($totalDespesas, 2, ',', '.') . PHP_EOL;
    echo "Total KM/Deslocamento: R$ " . number_format($totalKMDeslocamento, 2, ',', '.') . PHP_EOL;
    echo "VALOR TOTAL: R$ " . number_format($valorTotal, 2, ',', '.') . PHP_EOL;
    echo "TOTAL OS: {$ordens->count()}" . PHP_EOL;
    echo PHP_EOL;

    // Agrupar por consultor
    echo "=== AGRUPADO POR CONSULTOR ===" . PHP_EOL;
    $porConsultor = $ordens->groupBy(function($os) {
        return $os->consultor ? $os->consultor->name : 'Sem Consultor';
    });

    foreach ($porConsultor as $consultorNome => $ordensConsultor) {
        echo "Consultor: {$consultorNome} | OSs: {$ordensConsultor->count()}" . PHP_EOL;
    }
} else {
    echo "⚠ Nenhuma ordem de serviço encontrada no período especificado." . PHP_EOL;
    echo PHP_EOL;

    // Listar outros clientes que TÊM OS
    echo "=== CLIENTES COM ORDENS DE SERVIÇO ===" . PHP_EOL;
    $clientesComOS = OrdemServico::selectRaw('cliente_id, count(*) as total')
        ->groupBy('cliente_id')
        ->orderBy('total', 'desc')
        ->limit(10)
        ->with('cliente')
        ->get();

    foreach ($clientesComOS as $item) {
        $clienteNome = $item->cliente ? $item->cliente->nome : 'N/A';
        echo "Cliente ID {$item->cliente_id} ({$clienteNome}): {$item->total} OSs" . PHP_EOL;
    }
}
