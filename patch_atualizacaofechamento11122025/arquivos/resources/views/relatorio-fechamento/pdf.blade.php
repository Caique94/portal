<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Fechamento #{{ $relatorioFechamento->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            background-color: white;
        }

        .container {
            max-width: 210mm;
            background-color: white;
            margin: 0 auto;
            padding: 20px 30px;
        }

        /* Header */
        .header {
            border-bottom: 1px solid #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }

        .header-info {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #666;
        }

        /* Report Info */
        .report-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .info-box {
            padding: 0;
        }

        .info-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
        }

        .info-value {
            color: #555;
        }

        /* Summary Stats */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .stat-card {
            text-align: center;
            font-size: 10px;
        }

        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }

        .stat-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Table */
        .table-section h3 {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            margin: 20px 0 10px 0;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 15px;
        }

        thead {
            background-color: #f5f5f5;
            border-top: 1px solid #333;
            border-bottom: 2px solid #333;
        }

        th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            color: #333;
            border: none;
        }

        td {
            padding: 6px 5px;
            border: none;
            border-bottom: 1px solid #ddd;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        /* Detail breakdown */
        .detail-table {
            width: 100%;
            margin-top: 5px;
            font-size: 9px;
        }

        .detail-table td {
            padding: 4px 5px;
            border-bottom: 1px solid #ddd;
        }

        .detail-label {
            text-align: right;
            font-weight: bold;
            width: 40%;
            color: #333;
        }

        .detail-value {
            text-align: right;
            width: 60%;
            color: #555;
        }

        .total-row td {
            border-top: 2px solid #333;
            border-bottom: none;
            font-weight: bold;
            padding: 8px 5px;
        }

        .final-total {
            margin-top: 15px;
            border-top: 1px solid #333;
            border-bottom: 2px solid #333;
            padding: 10px 0;
            font-weight: bold;
            font-size: 11px;
        }

        .final-total-value {
            text-align: right;
            color: #333;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #999;
            text-align: center;
            line-height: 1.4;
        }

        .observations {
            background-color: #fafafa;
            border-left: 3px solid #333;
            padding: 10px;
            margin-top: 20px;
            font-size: 9px;
        }

        .observations-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        /* Print Styles */
        @media print {
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
        }

        .os-block {
            page-break-inside: avoid;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-title">RELATÓRIO DE FECHAMENTO</div>
            <div class="header-info">
                <span>#{{ $relatorioFechamento->id }}</span>
                <span>{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Report Information -->
        <div class="report-info">
            <div class="info-box">
                <div class="info-label">Consultor</div>
                <div class="info-value">{{ $consultor->name }}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Período</div>
                <div class="info-value">
                    {{ $relatorioFechamento->data_inicio->format('d/m/Y') }} até
                    {{ $relatorioFechamento->data_fim->format('d/m/Y') }}
                </div>
            </div>
            <div class="info-box">
                <div class="info-label">Status</div>
                <div class="info-value">{{ ucfirst($relatorioFechamento->status) }}</div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="summary-stats">
            <div class="stat-card">
                <div class="stat-value">{{ $relatorioFechamento->total_os }}</div>
                <div class="stat-label">Ordens de Serviço</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">R$ {{ number_format($relatorioFechamento->valor_total, 2, ',', '.') }}</div>
                <div class="stat-label">Valor Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">R$ {{ number_format($relatorioFechamento->valor_total / max($relatorioFechamento->total_os, 1), 2, ',', '.') }}</div>
                <div class="stat-label">Valor Médio</div>
            </div>
        </div>

        <!-- Orders Table -->
        @if($ordemServicos->count() > 0)
            <div class="table-section">
                <h3>Ordens de Serviço</h3>

                @php
                    $statusMap = [
                        0 => 'Aberta',
                        1 => 'Enviada para Aprovação',
                        2 => 'Finalizada',
                        3 => 'Contestada',
                        4 => 'Faturada',
                        5 => 'Faturada',
                        6 => 'Faturada',
                    ];
                    $totalValor = 0;
                @endphp

                @foreach($ordemServicos as $os)
                    @php
                        $dataEmissao = $os->data_emissao ? \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') : \Carbon\Carbon::parse($os->created_at)->format('d/m/Y');
                        $assunto = $os->assunto ?? 'Serviço Prestado';
                        $descricao = $os->detalhamento ?? 'Atendimento prestado';
                        $horas = $os->qtde_total ?? '1.0';
                        $valorServico = (float)($os->valor_total ?? 0);
                        $despesas = (float)($os->valor_despesa ?? 0);
                        $valorKm = $os->km ? (float)$os->km : 0;
                        $deslocamento = (isset($os->deslocamento) && $os->deslocamento) ? (float)$os->deslocamento : 0;
                        $totalOS = $valorServico + $despesas + $valorKm + $deslocamento;
                        $totalValor += $totalOS;
                    @endphp

                    <div class="os-block">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 8%;">OS</th>
                                    <th style="width: 15%;">Cliente</th>
                                    <th style="width: 15%;">Assunto</th>
                                    <th style="width: 30%;">Descrição</th>
                                    <th style="width: 10%; text-align: center;">Data</th>
                                    <th style="width: 8%; text-align: center;">Horas</th>
                                    <th style="width: 14%; text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#{{ $os->id }}</td>
                                    <td>{{ $os->cliente ? (string)$os->cliente : '-' }}</td>
                                    <td>{{ $assunto }}</td>
                                    <td>{{ substr($descricao, 0, 60) }}{{ strlen($descricao) > 60 ? '...' : '' }}</td>
                                    <td class="text-right">{{ $dataEmissao }}</td>
                                    <td class="text-right">{{ $horas }}</td>
                                    <td class="text-right"><strong>R$ {{ number_format($totalOS, 2, ',', '.') }}</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Detail Breakdown -->
                        @if($valorServico > 0 || $despesas > 0 || $valorKm > 0 || $deslocamento > 0)
                            <table class="detail-table">
                                <tbody>
                                    @if($valorServico > 0)
                                        <tr>
                                            <td class="detail-label">Serviço</td>
                                            <td class="detail-value">R$ {{ number_format($valorServico, 2, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if($despesas > 0)
                                        <tr>
                                            <td class="detail-label">Demais Despesas</td>
                                            <td class="detail-value">R$ {{ number_format($despesas, 2, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if($valorKm > 0)
                                        <tr>
                                            <td class="detail-label">KM</td>
                                            <td class="detail-value">R$ {{ number_format($valorKm, 2, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if($deslocamento > 0)
                                        <tr>
                                            <td class="detail-label">Deslocamento</td>
                                            <td class="detail-value">R$ {{ number_format($deslocamento, 2, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    <tr class="total-row">
                                        <td class="detail-label">Total</td>
                                        <td class="detail-value">R$ {{ number_format($totalOS, 2, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endforeach

                <!-- Final Summary -->
                <div class="final-total">
                    <div style="display: flex; justify-content: space-between;">
                        <span>TOTAL DO PERÍODO</span>
                        <span class="final-total-value">R$ {{ number_format($totalValor, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Observations if Rejected -->
        @if($relatorioFechamento->observacoes)
            <div class="observations">
                <div class="observations-label">Motivo da Rejeição:</div>
                <div>{{ $relatorioFechamento->observacoes }}</div>
            </div>
        @endif

        <!-- Approval Information -->
        @if($relatorioFechamento->data_aprovacao)
            <div class="section-title">Informações de Aprovação</div>
            <div style="font-size: 9px; color: #666; line-height: 1.6;">
                <div><strong>Aprovado por:</strong> {{ $relatorioFechamento->aprovador->name ?? '-' }}</div>
                <div><strong>Data de Aprovação:</strong> {{ $relatorioFechamento->data_aprovacao->format('d/m/Y H:i') }}</div>
                @if($relatorioFechamento->data_envio_email)
                    <div><strong>Data de Envio ao Consultor:</strong> {{ $relatorioFechamento->data_envio_email->format('d/m/Y H:i') }}</div>
                @endif
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            Portal Personalitec<br>
            Este documento é confidencial
        </div>
    </div>
</body>
</html>
