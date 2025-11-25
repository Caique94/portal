<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(to right, #4a90e2 0%, #357abd 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: 5px solid #2c5aa0;
        }
        .header h1 {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .header .numero {
            font-size: 14px;
            font-weight: normal;
        }
        .content {
            padding: 30px;
        }
        .header-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
            margin-bottom: 10px;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 25%;
            padding: 10px 0;
        }
        .info-value {
            display: table-cell;
            padding: 10px 15px;
        }
        .detalhamento-section {
            background-color: #f0f4f8;
            border-left: 4px solid #4a90e2;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
            line-height: 1.6;
        }
        .detalhamento-section strong {
            display: block;
            margin-bottom: 10px;
            color: #2c5aa0;
        }
        .resumo-section {
            background-color: #f8f9fa;
            border: 2px solid #4a90e2;
            border-radius: 5px;
            padding: 0;
            margin: 20px 0;
            overflow: hidden;
        }
        .resumo-title {
            background-color: #4a90e2;
            color: white;
            padding: 12px;
            font-weight: bold;
            text-align: center;
        }
        .resumo-content {
            padding: 20px;
        }
        .resumo-grid {
            display: table;
            width: 100%;
        }
        .resumo-row {
            display: table-row;
        }
        .resumo-col {
            display: table-cell;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .resumo-col:last-child {
            border-bottom: none;
        }
        .resumo-label {
            font-weight: bold;
            color: #333;
            width: 50%;
        }
        .resumo-value {
            text-align: right;
            font-weight: bold;
            color: #4a90e2;
            font-size: 16px;
        }
        .totalizador-section {
            background-color: #f8f9fa;
            border: 2px solid #4a90e2;
            border-radius: 5px;
            padding: 0;
            margin: 20px 0;
            overflow: hidden;
        }
        .totalizador-title {
            background-color: #4a90e2;
            color: white;
            padding: 12px;
            font-weight: bold;
            text-align: center;
        }
        .totalizador-content {
            padding: 20px;
        }
        .totalizador-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totalizador-table tr {
            border-bottom: 1px solid #e0e0e0;
        }
        .totalizador-table tr:last-child {
            border-bottom: none;
        }
        .totalizador-table td {
            padding: 12px;
        }
        .totalizador-table .label {
            font-weight: 600;
            width: 60%;
        }
        .totalizador-table .value {
            text-align: right;
            font-weight: 600;
            color: #4a90e2;
        }
        .totalizador-table .total {
            background-color: #e3f2fd;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e0e0e0;
            margin-top: 30px;
        }
        .personalitec-info {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
        }
        .personalitec-info strong {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #4a90e2;
        }
        .personalitec-email {
            color: #4a90e2;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>ORDEM DE ATENDIMENTO</h1>
            <div class="numero">NÚMERO {{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <!-- INFORMAÇÕES PRINCIPAIS -->
            <div class="header-box">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Cliente:</div>
                        <div class="info-value">{{ $os->cliente->nome }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Contato:</div>
                        <div class="info-value">{{ $os->cliente->contato ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Emissão:</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Consultor:</div>
                        <div class="info-value">{{ $os->consultor->name }}</div>
                    </div>
                </div>
            </div>

            <!-- DETALHAMENTO -->
            <div class="detalhamento-section">
                <strong>DETALHAMENTO</strong>
                <strong>Assunto:</strong> {{ $os->assunto ?? '-' }}<br>
                <strong>Observações:</strong> {{ $os->observacao ?? 'Nenhuma observação adicionada' }}
            </div>

            <!-- RESUMO -->
            <div class="resumo-section">
                <div class="resumo-title">RESUMO</div>
                <div class="resumo-content">
                    <div class="resumo-grid">
                        <div class="resumo-row">
                            <div class="resumo-col resumo-label">Chamado Personalitec</div>
                            <div class="resumo-col resumo-value">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div class="resumo-row">
                            <div class="resumo-col resumo-label">KM</div>
                            <div class="resumo-col resumo-value">{{ $os->km ?? '0' }}</div>
                        </div>
                        <div class="resumo-row">
                            <div class="resumo-col resumo-label">TOTAL OS</div>
                            <div class="resumo-col resumo-value">R$ {{ number_format($totalizador['total_geral'] ?? $os->valor_total, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOTALIZADOR -->
            @if($totalizador)
            <div class="totalizador-section">
                <div class="totalizador-title">TOTALIZADOR</div>
                <div class="totalizador-content">
                    <table class="totalizador-table">
                        <tbody>
                            <tr>
                                <td class="label">{{ $totalizador['valor_hora_label'] }}:</td>
                                <td class="value">R$ {{ number_format($totalizador['valor_hora'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="label">Horas:</td>
                                <td class="value">{{ number_format($totalizador['horas'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="label">Valor Horas:</td>
                                <td class="value">R$ {{ number_format($totalizador['valor_horas'], 2, ',', '.') }}</td>
                            </tr>
                            @if($totalizador['km'] > 0 && $totalizador['is_presencial'])
                            <tr>
                                <td class="label">{{ $totalizador['valor_km_label'] }}:</td>
                                <td class="value">R$ {{ number_format($totalizador['valor_km'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="label">KM:</td>
                                <td class="value">{{ number_format($totalizador['km'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="label">Valor KM:</td>
                                <td class="value">R$ {{ number_format($totalizador['valor_km_total'], 2, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($totalizador['deslocamento'] > 0 && $totalizador['is_presencial'])
                            <tr>
                                <td class="label">Deslocamento (horas):</td>
                                <td class="value">{{ number_format($totalizador['deslocamento'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="label">Valor Deslocamento:</td>
                                <td class="value">R$ {{ number_format($totalizador['valor_deslocamento'], 2, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($totalizador['despesas'] > 0)
                            <tr>
                                <td class="label">Despesas:</td>
                                <td class="value">R$ {{ number_format($totalizador['despesas'], 2, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="total">
                                <td class="label">TOTAL GERAL:</td>
                                <td class="value">R$ {{ number_format($totalizador['total_geral'], 2, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- PERSONALITEC INFO -->
            <div class="personalitec-info">
                <strong>Personalitec</strong>
                <strong>Sua visão, nossa tecnologia</strong>
                <a href="mailto:atendimento@personalitec.com.br" class="personalitec-email">atendimento@personalitec.com.br</a>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p>Este é um e-mail automático. Por favor, não responda.</p>
            <p>© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
