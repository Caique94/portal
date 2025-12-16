<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Ordem de Serviço #{{ $ordemServico->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: #1F3A56;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            background-color: #1E88E5;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #42A5F5;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-logo {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
        }

        .header-logo img {
            height: 50px;
            width: auto;
        }

        .header-title {
            display: table-cell;
            vertical-align: middle;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .header-numero {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
            text-align: right;
        }

        .numero-box {
            background-color: #1565C0;
            padding: 8px 12px;
            border-radius: 4px;
            display: inline-block;
            font-weight: bold;
            font-size: 16px;
        }

        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .row-2col {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .col-50 {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 8px;
        }

        .col-50:first-child {
            padding-left: 0;
        }

        .col-50:last-child {
            padding-right: 0;
        }

        .info-box {
            background-color: #F5F8FA;
            border: 1px solid #E0E8F0;
            padding: 12px;
            margin-bottom: 10px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .info-label {
            display: table-cell;
            width: 100px;
            font-weight: bold;
            color: #1565C0;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            vertical-align: top;
            color: #1F3A56;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .hours-table {
            background-color: #F5F8FA;
            border: 1px solid #E0E8F0;
        }

        .hours-table th {
            background-color: #1E88E5;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #42A5F5;
        }

        .hours-table td {
            padding: 8px;
            text-align: center;
            border-right: 1px solid #E0E8F0;
            border-bottom: 1px solid #E0E8F0;
        }

        .hours-table td:last-child {
            border-right: none;
        }

        .section-title {
            background-color: #1E88E5;
            color: white;
            padding: 12px 16px;
            font-weight: bold;
            font-size: 12px;
            margin: 20px 0 10px 0;
            letter-spacing: 0.5px;
        }

        .detail-box {
            background-color: #F5F8FA;
            border: 1px solid #E0E8F0;
            padding: 12px;
            margin-bottom: 15px;
            text-align: justify;
            line-height: 1.5;
            font-size: 12px;
        }

        .summary-box {
            background-color: #F5F8FA;
            border: 1px solid #E0E8F0;
            padding: 15px;
            margin: 20px 0;
        }

        .summary-title {
            background-color: #1E88E5;
            color: white;
            padding: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .summary-table td {
            border: 1px solid #DEDEDE;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }

        .summary-table .label {
            background-color: #F2F2F2;
            font-weight: 500;
            font-size: 10px;
            color: #555;
        }

        .summary-table .value {
            background-color: white;
            font-size: 14px;
            color: #1F3A56;
        }

        .summary-table .value.total {
            font-weight: 700;
            color: #0A5FA6;
            font-size: 14px;
        }

        .summary-footer {
            text-align: center;
            background: linear-gradient(to right, #1E88E5, #42A5F5);
            padding: 15px;
            margin-top: 10px;
        }

        .summary-footer img {
            max-height: 60px;
            width: auto;
        }

        .footer {
            text-align: center;
            color: #666;
            font-size: 10px;
            margin-top: 30px;
            border-top: 1px solid #DDD;
            padding-top: 10px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="header-content">
        <div class="header-logo">
            @if(isset($logoPath) && file_exists($logoPath))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Personalitec">
            @else
                <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec">
            @endif
        </div>
        <div class="header-title">ORDEM DE ATENDIMENTO</div>
        <div class="header-numero">
            <div class="numero-box">{{ $ordemServico->id }}</div>
        </div>
    </div>
</div>

<!-- INFORMAÇÕES GERAIS -->
<div class="section">
    <div class="row-2col">
        <div class="col-50">
            <div class="info-box">
                <div class="info-row">
                    <div class="info-label">Cliente:</div>
                    <div class="info-value">
                        @if($ordemServico->cliente)
                            {{ $ordemServico->cliente->nome ?? $ordemServico->cliente->nome_fantasia ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Contato:</div>
                    <div class="info-value">
                        @if($ordemServico->cliente)
                            {{ $ordemServico->cliente->email ?? $ordemServico->cliente->contato ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Emissão:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($ordemServico->data_emissao)->format('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Consultor:</div>
                    <div class="info-value">{{ $ordemServico->consultor->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <div class="col-50">
            <table class="hours-table">
                <thead>
                    <tr>
                        <th>HORA INICIO</th>
                        <th>HORA FIM</th>
                        <th>HORA DESCONTO</th>
                        <th>DESPESA</th>
                        <th>TRANSLADO</th>
                        <th>TOTAL HORAS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $ordemServico->hora_inicio ?? '00:00' }}</td>
                        <td>{{ $ordemServico->hora_final ?? '00:00' }}</td>
                        <td>{{ $ordemServico->hora_desconto ? $ordemServico->hora_desconto : '00:00' }}</td>
                        <td>{{ $ordemServico->valor_despesa ? 'R$ ' . number_format($ordemServico->valor_despesa, 2, ',', '.') : '--' }}</td>
                        <td>
                            @php
                              if ($ordemServico->deslocamento) {
                                $deslocamento = floatval($ordemServico->deslocamento);
                                $horas = intval($deslocamento);
                                $minutos = intval(($deslocamento - $horas) * 60);
                                echo sprintf('%02d:%02d', $horas, $minutos);
                              } else {
                                echo '--';
                              }
                            @endphp
                        </td>
                        <td>{{ $ordemServico->horas_trabalhadas ? number_format(floatval($ordemServico->horas_trabalhadas), 2, '.', '') : '--' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DETALHAMENTO -->
<div class="section-title">DETALHAMENTO</div>
<div class="detail-box">
    {!! nl2br($ordemServico->detalhamento ?? 'Nenhum detalhamento fornecido.') !!}
</div>

<!-- RESUMO -->
<div class="section-title">
    @if($tipoDestinatario === 'consultor')
        RESUMO - SEU GANHO
    @else
        RESUMO FINANCEIRO
    @endif
</div>

<div class="summary-box">
    <!-- ROW 1 -->
    <table class="summary-table">
        <tr>
            <td colspan="2" class="label">Chamado<br>Personalitec</td>
            <td colspan="2" class="value">{{ $ordemServico->nr_atendimento ?? $ordemServico->id }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label">Data de<br>Emissão</td>
            <td colspan="2" class="value">{{ $ordemServico->data_emissao ? \Carbon\Carbon::parse($ordemServico->data_emissao)->format('d/m/Y') : '--' }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label">KM</td>
            <td colspan="2" class="value">{{ $ordemServico->km ?? '--' }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label">VALOR TOTAL</td>
            <td colspan="2" class="value total">
                @if($tipoDestinatario === 'consultor')
                    @php
                        $valor_horas = floatval($ordemServico->horas_trabalhadas ?? 0) * floatval($ordemServico->consultor->valor_hora ?? 0);
                        $valor_km = floatval($ordemServico->km ?? 0) * floatval($ordemServico->consultor->valor_km ?? 0);
                        $valor_despesa = floatval($ordemServico->valor_despesa ?? 0);
                        $total_ganho = $valor_horas + $valor_km + $valor_despesa;
                    @endphp
                    R$ {{ number_format($total_ganho, 2, ',', '.') }}
                @else
                    {{ $ordemServico->valor_total ? 'R$ ' . number_format($ordemServico->valor_total, 2, ',', '.') : '--' }}
                @endif
            </td>
        </tr>
    </table>

    <div class="summary-footer">
        @if(isset($logoPath) && file_exists($logoPath))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Personalitec">
        @else
            <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec">
        @endif
    </div>
</div>

<!-- FOOTER -->
<div class="footer">
    <p>© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
</div>

</body>
</html>
