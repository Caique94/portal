<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Fechamento - Consultor</title>
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
            border-bottom: 2px solid #2196F3;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2196F3;
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
            background-color: #e3f2fd;
            padding: 15px;
            margin-bottom: 30px;
            border-left: 4px solid #2196F3;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
            font-size: 11pt;
        }
        .summary-label {
            font-weight: bold;
            color: #1565c0;
        }
        .cliente-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .cliente-header {
            background-color: #e8f5e9;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 3px solid #4CAF50;
            font-weight: bold;
            font-size: 11pt;
            color: #2e7d32;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }
        th {
            background-color: #2196F3;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .subtotals {
            background-color: #fff3e0;
            padding: 10px;
            margin: 10px 0;
            font-size: 10pt;
        }
        .subtotal-row {
            margin: 3px 0;
        }
        .totalizadores {
            background-color: #e1f5fe;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
        .totalizadores h3 {
            margin: 0 0 10px 0;
            color: #0277bd;
            font-size: 12pt;
        }
        .totalizador-item {
            margin: 8px 0;
            font-size: 11pt;
        }
        .grand-total {
            background-color: #2196F3;
            color: white;
            padding: 15px;
            text-align: right;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
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
        <h1>Relatório de Fechamento - Consultor</h1>
        @if($consultor)
            <div class="subtitle">{{ $consultor->nome }}</div>
        @endif
    </div>

    <div class="metadata">
        <div class="metadata-row">
            <span class="metadata-label">Período:</span>
            {{ \Carbon\Carbon::parse($relatorioFechamento->data_inicio)->format('d/m/Y') }} a
            {{ \Carbon\Carbon::parse($relatorioFechamento->data_fim)->format('d/m/Y') }}
        </div>
        @if($consultor)
        <div class="metadata-row">
            <span class="metadata-label">Consultor:</span>
            {{ $consultor->nome }} ({{ $consultor->email }})
        </div>
        @endif
        <div class="metadata-row">
            <span class="metadata-label">Status:</span>
            {{ ucfirst($relatorioFechamento->status) }}
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Gerado em:</span>
            {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    @php
        $totalOS = $ordemServicos->count();
        $valorTotalGeral = 0;
        $subtotalServicos = 0;
        $subtotalDespesas = 0;
        $subtotalKM = 0;
        $subtotalDeslocamento = 0;
        $totalHoras = 0;

        // Agrupar OSs por cliente
        $porCliente = $ordemServicos->groupBy('cliente.razao_social')->sortKeys();
    @endphp

    <div class="summary-box">
        <div class="summary-item">
            <span class="summary-label">Total de OSs:</span> {{ $totalOS }}
        </div>
        <div class="summary-item">
            <span class="summary-label">Valor Total:</span> R$ {{ number_format($relatorioFechamento->valor_total, 2, ',', '.') }}
        </div>
        @if($totalOS > 0)
        <div class="summary-item">
            <span class="summary-label">Média por OS:</span> R$ {{ number_format($relatorioFechamento->valor_total / $totalOS, 2, ',', '.') }}
        </div>
        @endif
    </div>

    <h2 style="color: #2196F3; margin-bottom: 20px;">Ordens de Serviço Detalhadas</h2>

    @foreach($porCliente as $clienteNome => $ordensCliente)
        <div class="cliente-section">
            <div class="cliente-header">
                Cliente: {{ $clienteNome ?: 'Não informado' }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 6%;">OS</th>
                        <th style="width: 10%;">Data</th>
                        <th style="width: 20%;">Produto</th>
                        <th style="width: 8%; text-align: right;">Horas</th>
                        <th style="width: 12%; text-align: right;">Serviço</th>
                        <th style="width: 11%; text-align: right;">Despesas</th>
                        <th style="width: 9%; text-align: right;">KM</th>
                        <th style="width: 11%; text-align: right;">Desloc.</th>
                        <th style="width: 13%; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ordensCliente as $os)
                        @php
                            $horas = $os->horas ?? 0;
                            $valorHora = $consultor ? ($consultor->valor_hora ?? 0) : 0;
                            $valorServico = $horas * $valorHora;

                            $despesas = $os->valor_despesa ?? 0;

                            $km = $os->km ?? 0;
                            $valorKM = $os->is_presencial && $consultor ? ($km * ($consultor->valor_km ?? 0)) : 0;

                            $valorDeslocamento = $os->is_presencial && $consultor ? ($consultor->valor_deslocamento ?? 0) : 0;

                            $totalOS = $valorServico + $despesas + $valorKM + $valorDeslocamento;

                            $subtotalServicos += $valorServico;
                            $subtotalDespesas += $despesas;
                            $subtotalKM += $valorKM;
                            $subtotalDeslocamento += $valorDeslocamento;
                            $valorTotalGeral += $totalOS;
                            $totalHoras += $horas;
                        @endphp
                        <tr>
                            <td>{{ $os->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($os->created_at)->format('d/m/Y') }}</td>
                            <td>{{ $os->produtoTabela->produto->nome ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($horas, 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format($valorServico, 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format($despesas, 2, ',', '.') }}</td>
                            <td class="text-right">{{ $km > 0 ? number_format($km, 0, ',', '.') . ' km' : '-' }}</td>
                            <td class="text-right">{{ $valorDeslocamento > 0 ? 'R$ ' . number_format($valorDeslocamento, 2, ',', '.') : '-' }}</td>
                            <td class="text-right"><strong>R$ {{ number_format($totalOS, 2, ',', '.') }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="subtotals">
                <div class="subtotal-row">
                    <strong>Subtotal Cliente:</strong> {{ $ordensCliente->count() }} OSs |
                    R$ {{ number_format($ordensCliente->sum(function($os) use ($consultor) {
                        $horas = $os->horas ?? 0;
                        $valorHora = $consultor ? ($consultor->valor_hora ?? 0) : 0;
                        $valorServico = $horas * $valorHora;
                        $despesas = $os->valor_despesa ?? 0;
                        $km = $os->km ?? 0;
                        $valorKM = $os->is_presencial && $consultor ? ($km * ($consultor->valor_km ?? 0)) : 0;
                        $valorDeslocamento = $os->is_presencial && $consultor ? ($consultor->valor_deslocamento ?? 0) : 0;
                        return $valorServico + $despesas + $valorKM + $valorDeslocamento;
                    }), 2, ',', '.') }}
                </div>
            </div>
        </div>
    @endforeach

    <div class="totalizadores">
        <h3>Totalizadores por Componente</h3>
        <div class="totalizador-item">
            <strong>Total de Horas Trabalhadas:</strong> {{ number_format($totalHoras, 2, ',', '.') }} horas
        </div>
        <div class="totalizador-item">
            <strong>Subtotal Serviços (Horas):</strong> R$ {{ number_format($subtotalServicos, 2, ',', '.') }}
        </div>
        <div class="totalizador-item">
            <strong>Subtotal Despesas:</strong> R$ {{ number_format($subtotalDespesas, 2, ',', '.') }}
        </div>
        <div class="totalizador-item">
            <strong>Subtotal KM:</strong> R$ {{ number_format($subtotalKM, 2, ',', '.') }}
        </div>
        <div class="totalizador-item">
            <strong>Subtotal Deslocamento:</strong> R$ {{ number_format($subtotalDeslocamento, 2, ',', '.') }}
        </div>
    </div>

    <div class="grand-total">
        TOTAL GERAL: {{ $totalOS }} Ordens de Serviço | R$ {{ number_format($valorTotalGeral, 2, ',', '.') }}
    </div>

    <div class="footer">
        <div class="page-number"></div>
        <div>Documento gerado automaticamente pelo sistema em {{ now()->format('d/m/Y H:i:s') }}</div>
        @if($consultor)
            <div>Assinatura: _________________________________</div>
        @endif
    </div>
</body>
</html>
