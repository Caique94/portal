<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Ordens de Serviço</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }

        .container {
            padding: 20px;
        }

        .header {
            border-bottom: 3px solid #1f6feb;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            color: #1f6feb;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        .info-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .info-item {
            flex: 1;
        }

        .info-item strong {
            color: #1f6feb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background-color: #1f6feb;
            color: white;
        }

        thead th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1f6feb;
        }

        tbody td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .status {
            padding: 3px 6px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
        }

        .status-aberta {
            background-color: #fff7ed;
            color: #92400e;
        }

        .status-enviada-para-aprovacao {
            background-color: #fff7ed;
            color: #92400e;
        }

        .status-finalizada {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .status-contestada {
            background-color: #fee2e2;
            color: #7f1d1d;
            font-weight: bold;
        }

        .status-faturada {
            background-color: #eff6ff;
            color: #1d4ed8;
        }

        .status-aguardando-rps {
            background-color: #fff7ed;
            color: #92400e;
        }

        .footer {
            border-top: 1px solid #e5e7eb;
            margin-top: 20px;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }

        .summary {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .summary-item {
            display: inline-block;
            margin-right: 30px;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .summary-item strong {
            color: #1f6feb;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Relatório de Ordens de Serviço</h1>
            <p>Consultor: <strong>{{ $consultor }}</strong></p>
            <p>Data do Relatório: <strong>{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</strong></p>
            @if($data_inicio || $data_fim)
                <p>Período:
                    <strong>
                        @if($data_inicio)
                            {{ \Carbon\Carbon::parse($data_inicio)->format('d/m/Y') }}
                        @else
                            Início
                        @endif
                        até
                        @if($data_fim)
                            {{ \Carbon\Carbon::parse($data_fim)->format('d/m/Y') }}
                        @else
                            Hoje
                        @endif
                    </strong>
                </p>
            @endif
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-item">
                <strong>Total de OS:</strong> {{ count($dados) }}
            </div>
            <div class="summary-item">
                @php
                    $totalValor = 0;
                    foreach($dados as $item) {
                        $totalValor += (float)($item->valor_total ?? 0);
                    }
                @endphp
                <strong>Valor Total:</strong> R$ {{ number_format($totalValor, 2, ',', '.') }}
            </div>
            <div class="summary-item">
                <strong>Data Geração:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
            </div>
        </div>

        <!-- Table -->
        @if(count($dados) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 12%;">Data</th>
                        <th style="width: 30%;">Cliente</th>
                        <th style="width: 20%;">Status</th>
                        <th style="width: 15%; text-align: right;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        @php
                            $status = $item->status_txt ?? 'Desconhecido';
                            $statusClass = 'status-' . str_replace(' ', '-', strtolower($status));
                        @endphp
                        <tr>
                            <td>#{{ $item->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->data)->format('d/m/Y') }}</td>
                            <td>{{ $item->cliente ?? '-' }}</td>
                            <td>
                                <span class="status {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="text-right">R$ {{ number_format($item->valor_total ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; color: #999; padding: 20px;">Nenhum registro encontrado.</p>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Este documento foi gerado automaticamente pelo sistema Personalitec.</p>
            <p>{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
