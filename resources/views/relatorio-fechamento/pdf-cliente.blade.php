<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Fechamento - Cliente</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            size: A4;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #4CAF50;
            font-size: 20pt;
        }
        .header .subtitle {
            font-size: 12pt;
            color: #666;
            margin-top: 5px;
        }
        .metadata {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 9pt;
        }
        .metadata-row {
            margin: 5px 0;
        }
        .metadata-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .summary-box {
            background-color: #e8f5e9;
            padding: 15px;
            margin-bottom: 30px;
            border-left: 4px solid #4CAF50;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
            font-size: 11pt;
        }
        .summary-label {
            font-weight: bold;
            color: #2e7d32;
        }
        h2 {
            color: #4CAF50;
            margin-bottom: 20px;
            font-size: 14pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }
        th {
            background-color: #4CAF50;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-summary {
            background-color: #e3f2fd;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
        .totals-summary h3 {
            margin: 0 0 10px 0;
            color: #1976d2;
            font-size: 12pt;
        }
        .total-item {
            margin: 8px 0;
            font-size: 11pt;
        }
        .grand-total {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: right;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
        }
        .observacoes {
            background-color: #fff3e0;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #ff9800;
        }
        .observacoes h3 {
            margin: 0 0 10px 0;
            color: #e65100;
            font-size: 11pt;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .page-number:after {
            content: "Página " counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Fechamento - Cliente</h1>
        <div class="subtitle">Resumo Administrativo/Financeiro</div>
    </div>

    <div class="metadata">
        <div class="metadata-row">
            <span class="metadata-label">Período:</span>
            {{ \Carbon\Carbon::parse($relatorioFechamento->data_inicio)->format('d/m/Y') }} a
            {{ \Carbon\Carbon::parse($relatorioFechamento->data_fim)->format('d/m/Y') }}
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Status:</span>
            {{ ucfirst($relatorioFechamento->status) }}
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Gerado em:</span>
            {{ now()->format('d/m/Y H:i:s') }}
        </div>
        @if($relatorioFechamento->aprovado_por)
        <div class="metadata-row">
            <span class="metadata-label">Aprovado por:</span>
            {{ $relatorioFechamento->aprovador->nome ?? 'N/A' }} em
            {{ \Carbon\Carbon::parse($relatorioFechamento->data_aprovacao)->format('d/m/Y H:i') }}
        </div>
        @endif
    </div>

    @php
        $totalOS = $ordemServicos->count();
        $totalServicos = 0;
        $totalDespesas = 0;
        $totalKMDeslocamento = 0;
        $valorTotalGeral = 0;

        // Agrupar OSs por consultor
        $porConsultor = $ordemServicos->groupBy(function($os) {
            return $os->consultor ? $os->consultor->nome : 'Sem Consultor';
        })->sortKeys();

        $totalConsultores = $porConsultor->count();
    @endphp

    <div class="summary-box">
        <div class="summary-item">
            <span class="summary-label">Total de OSs:</span> {{ $totalOS }}
        </div>
        <div class="summary-item">
            <span class="summary-label">Total de Consultores:</span> {{ $totalConsultores }}
        </div>
        <div class="summary-item">
            <span class="summary-label">Valor Total:</span> R$ {{ number_format($relatorioFechamento->valor_total, 2, ',', '.') }}
        </div>
    </div>

    <h2>Resumo por Consultor</h2>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Consultor</th>
                <th style="width: 10%; text-align: center;">Qtd OSs</th>
                <th style="width: 15%; text-align: right;">Total Serviços</th>
                <th style="width: 15%; text-align: right;">Total Despesas</th>
                <th style="width: 15%; text-align: right;">KM/Desloc.</th>
                <th style="width: 20%; text-align: right;">Total Geral</th>
            </tr>
        </thead>
        <tbody>
            @foreach($porConsultor as $consultorNome => $ordensConsultor)
                @php
                    $qtdOS = $ordensConsultor->count();
                    $subtotalServicos = 0;
                    $subtotalDespesas = 0;
                    $subtotalKMDeslocamento = 0;

                    foreach($ordensConsultor as $os) {
                        // Usar preco_produto (totalizador administrativo)
                        $precoServico = $os->preco_produto ?? 0;
                        $subtotalServicos += $precoServico;

                        $despesas = $os->valor_despesa ?? 0;
                        $subtotalDespesas += $despesas;

                        // KM e deslocamento do consultor
                        if ($os->is_presencial && $os->consultor) {
                            $km = $os->km ?? 0;
                            $valorKM = $km * ($os->consultor->valor_km ?? 0);
                            $valorDeslocamento = $os->consultor->valor_deslocamento ?? 0;
                            $subtotalKMDeslocamento += ($valorKM + $valorDeslocamento);
                        }
                    }

                    $totalConsultor = $subtotalServicos + $subtotalDespesas + $subtotalKMDeslocamento;

                    $totalServicos += $subtotalServicos;
                    $totalDespesas += $subtotalDespesas;
                    $totalKMDeslocamento += $subtotalKMDeslocamento;
                    $valorTotalGeral += $totalConsultor;
                @endphp
                <tr>
                    <td>{{ $consultorNome }}</td>
                    <td class="text-center">{{ $qtdOS }}</td>
                    <td class="text-right">R$ {{ number_format($subtotalServicos, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($subtotalDespesas, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($subtotalKMDeslocamento, 2, ',', '.') }}</td>
                    <td class="text-right"><strong>R$ {{ number_format($totalConsultor, 2, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-summary">
        <h3>Totais Consolidados</h3>
        <div class="total-item">
            <strong>Total de Ordens de Serviço:</strong> {{ $totalOS }}
        </div>
        <div class="total-item">
            <strong>Total de Consultores:</strong> {{ $totalConsultores }}
        </div>
        <div class="total-item">
            <strong>Subtotal Serviços:</strong> R$ {{ number_format($totalServicos, 2, ',', '.') }}
        </div>
        <div class="total-item">
            <strong>Subtotal Despesas:</strong> R$ {{ number_format($totalDespesas, 2, ',', '.') }}
        </div>
        <div class="total-item">
            <strong>Subtotal KM/Deslocamento:</strong> R$ {{ number_format($totalKMDeslocamento, 2, ',', '.') }}
        </div>
    </div>

    <div class="grand-total">
        VALOR TOTAL GERAL: R$ {{ number_format($valorTotalGeral, 2, ',', '.') }}
    </div>

    @if($relatorioFechamento->observacoes)
    <div class="observacoes">
        <h3>Observações</h3>
        <p>{{ $relatorioFechamento->observacoes }}</p>
    </div>
    @endif

    <div class="footer">
        <div class="page-number"></div>
        <div>Documento gerado automaticamente pelo sistema em {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>
</body>
</html>
