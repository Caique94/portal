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
        .metadata {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 9pt;
        }
        .metadata-grid {
            display: table;
            width: 100%;
        }
        .metadata-row {
            display: table-row;
        }
        .metadata-cell {
            display: table-cell;
            padding: 5px;
        }
        .metadata-label {
            font-weight: bold;
            width: 30%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #2196F3;
            color: white;
            padding: 10px;
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
        .consultor-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        .consultor-header {
            background-color: #e3f2fd;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #2196F3;
        }
        .consultor-nome {
            font-size: 14pt;
            font-weight: bold;
            color: #1565c0;
        }
        .totals {
            background-color: #fff3e0;
            padding: 10px;
            margin-top: 5px;
            font-weight: bold;
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
    </div>

    <div class="metadata">
        <div class="metadata-grid">
            <div class="metadata-row">
                <div class="metadata-cell metadata-label">Período:</div>
                <div class="metadata-cell">{{ $metadata['periodo'] }}</div>
                <div class="metadata-cell metadata-label">Tipo:</div>
                <div class="metadata-cell">{{ $metadata['tipo'] }}</div>
            </div>
            <div class="metadata-row">
                <div class="metadata-cell metadata-label">Gerado por:</div>
                <div class="metadata-cell">{{ $metadata['gerado_por'] }}</div>
                <div class="metadata-cell metadata-label">Data de Geração:</div>
                <div class="metadata-cell">{{ $metadata['gerado_em'] }}</div>
            </div>
            <div class="metadata-row">
                <div class="metadata-cell metadata-label">Versão:</div>
                <div class="metadata-cell">{{ $metadata['versao'] }}</div>
                <div class="metadata-cell metadata-label">Hash SHA-256:</div>
                <div class="metadata-cell" style="font-size: 7pt;">{{ $job->pdf_checksum ?? 'Gerando...' }}</div>
            </div>
        </div>
    </div>

    @php
        $grandTotalHoras = 0;
        $grandTotalValor = 0;
    @endphp

    @foreach($data as $consultorData)
        <div class="consultor-section">
            <div class="consultor-header">
                <div class="consultor-nome">{{ $consultorData['consultor_nome'] }}</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 30%;">Cliente</th>
                        <th style="width: 25%;">Produto</th>
                        <th style="width: 12%; text-align: right;">Horas</th>
                        <th style="width: 12%; text-align: right;">Valor/Hora</th>
                        <th style="width: 13%; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consultorData['ordens'] as $ordem)
                        <tr>
                            <td>{{ $ordem['id'] }}</td>
                            <td>{{ $ordem['cliente'] }}</td>
                            <td>{{ $ordem['produto'] }}</td>
                            <td style="text-align: right;">{{ number_format($ordem['horas'], 2, ',', '.') }}</td>
                            <td style="text-align: right;">R$ {{ number_format($ordem['valor_hora'], 2, ',', '.') }}</td>
                            <td style="text-align: right;">R$ {{ number_format($ordem['total'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals">
                Total de Ordens: {{ $consultorData['total_ordens'] }} |
                Total de Horas: {{ number_format($consultorData['total_horas'], 2, ',', '.') }} |
                Valor Total: R$ {{ number_format($consultorData['valor_total'], 2, ',', '.') }}
            </div>

            @php
                $grandTotalHoras += $consultorData['total_horas'];
                $grandTotalValor += $consultorData['valor_total'];
            @endphp
        </div>
    @endforeach

    <div class="grand-total">
        TOTAL GERAL: {{ count($data) }} Consultores | {{ number_format($grandTotalHoras, 2, ',', '.') }} Horas | R$ {{ number_format($grandTotalValor, 2, ',', '.') }}
    </div>

    <div class="footer">
        <div class="page-number"></div>
        <div>Documento gerado automaticamente pelo sistema em {{ $metadata['gerado_em'] }}</div>
    </div>
</body>
</html>
