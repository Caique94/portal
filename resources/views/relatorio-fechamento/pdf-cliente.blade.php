<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório Mensal de Ordens de Serviço</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            size: A4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            color: #2c3e50;
            line-height: 1.4;
        }

        /* === CAPA === */
        .capa {
            text-align: center;
            padding: 80px 40px;
            page-break-after: always;
        }
        .capa-logo {
            margin-bottom: 40px;
        }
        .capa h1 {
            font-size: 24pt;
            color: #2c3e50;
            margin: 30px 0 20px 0;
            font-weight: bold;
        }
        .capa .cliente-nome {
            font-size: 18pt;
            color: #3498db;
            margin: 20px 0;
            font-weight: bold;
        }
        .capa .periodo {
            font-size: 14pt;
            color: #7f8c8d;
            margin: 30px 0;
        }
        .capa .data-geracao {
            font-size: 10pt;
            color: #95a5a6;
            margin-top: 60px;
        }

        /* === CABEÇALHO DAS PÁGINAS === */
        .header {
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 16pt;
        }
        .header .subtitle {
            font-size: 10pt;
            color: #7f8c8d;
            margin-top: 5px;
        }

        /* === RESUMO EXECUTIVO === */
        .resumo-executivo {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
            page-break-inside: avoid;
        }
        .resumo-executivo h2 {
            margin: 0 0 15px 0;
            font-size: 14pt;
            color: white;
        }
        .resumo-executivo p {
            margin: 8px 0;
            font-size: 10pt;
            line-height: 1.6;
        }
        .resumo-cards {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        .resumo-card {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        .resumo-card:not(:last-child) {
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        .resumo-card-label {
            font-size: 8pt;
            text-transform: uppercase;
            opacity: 0.9;
            display: block;
            margin-bottom: 5px;
        }
        .resumo-card-value {
            font-size: 14pt;
            font-weight: bold;
            display: block;
        }

        /* === SEÇÕES === */
        h2 {
            color: #2c3e50;
            font-size: 13pt;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #3498db;
        }
        h3 {
            color: #34495e;
            font-size: 11pt;
            margin: 20px 0 10px 0;
        }

        /* === TABELAS === */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 8pt;
        }
        thead {
            background-color: #34495e;
            color: white;
        }
        th {
            padding: 10px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
        }
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e8f4f8;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-nowrap {
            white-space: nowrap;
        }

        /* === BOX DE TOTAIS === */
        .totais-box {
            background-color: #ecf0f1;
            padding: 15px 20px;
            margin: 20px 0;
            border-left: 5px solid #3498db;
            page-break-inside: avoid;
        }
        .totais-box h3 {
            margin: 0 0 12px 0;
            color: #2c3e50;
            font-size: 12pt;
        }
        .total-linha {
            display: table;
            width: 100%;
            margin: 6px 0;
        }
        .total-label {
            display: table-cell;
            font-weight: bold;
            color: #34495e;
            width: 70%;
        }
        .total-valor {
            display: table-cell;
            text-align: right;
            font-size: 11pt;
            color: #2c3e50;
            width: 30%;
        }

        /* === TOTAL GERAL DESTACADO === */
        .total-geral {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 30px 0;
            border-radius: 8px;
            page-break-inside: avoid;
        }
        .total-geral-label {
            font-size: 11pt;
            display: block;
            margin-bottom: 8px;
            opacity: 0.9;
        }
        .total-geral-valor {
            font-size: 22pt;
            font-weight: bold;
        }

        /* === OBSERVAÇÕES === */
        .observacoes {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 15px 20px;
            margin: 20px 0;
            page-break-inside: avoid;
        }
        .observacoes h3 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .observacoes p {
            margin: 0;
            color: #856404;
        }

        /* === RODAPÉ === */
        .footer {
            position: fixed;
            bottom: 15px;
            left: 1.5cm;
            right: 1.5cm;
            text-align: center;
            font-size: 7pt;
            color: #95a5a6;
            padding-top: 8px;
            border-top: 1px solid #ecf0f1;
        }
        .page-break {
            page-break-after: always;
        }

        /* === DESTAQUE DE VALORES === */
        .valor-positivo {
            color: #27ae60;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-presencial {
            background-color: #3498db;
            color: white;
        }
        .badge-remoto {
            background-color: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    @php
        // === PREPARAR DADOS ===
        $totalOS = $ordemServicos->count();
        $totalHorasTrabalhadas = 0;
        $totalServicos = 0;
        $totalDespesas = 0;
        $totalKMDeslocamento = 0;
        $valorTotalGeral = 0;

        // Agrupar por consultor
        $porConsultor = $ordemServicos->groupBy(function($os) {
            return $os->consultor_id ?: 0;
        });
        $totalConsultores = $porConsultor->filter(function($grupo, $key) {
            return $key != 0;
        })->count();

        // Processar cada OS para calcular totais
        foreach($ordemServicos as $os) {
            // Horas trabalhadas
            $totalHorasTrabalhadas += $os->qtde_total ?? 0;

            // Totalizador Administrativo
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

        $valorTotalGeral = $totalServicos + $totalDespesas + $totalKMDeslocamento;

        // Determinar o nome do cliente
        // Prioridade: relacionamento direto > primeira OS > padrão
        if ($relatorioFechamento->cliente) {
            $nomeCliente = $relatorioFechamento->cliente->nome;
        } else {
            $primeiraOS = $ordemServicos->first();
            $nomeCliente = $primeiraOS && $primeiraOS->cliente
                ? $primeiraOS->cliente->nome
                : 'Diversos Clientes';
        }
    @endphp

    {{-- === CAPA === --}}
    <div class="capa">
        <div class="capa-logo">
            {{-- Espaço para logo (pode ser adicionado depois) --}}
        </div>

        <h1>Relatório Mensal de Ordens de Serviço</h1>

        <div class="cliente-nome">{{ $nomeCliente }}</div>

        <div class="periodo">
            Período: {{ \Carbon\Carbon::parse($relatorioFechamento->data_inicio)->format('d/m/Y') }}
            a {{ \Carbon\Carbon::parse($relatorioFechamento->data_fim)->format('d/m/Y') }}
        </div>

        <div class="data-geracao">
            Documento gerado em {{ now()->format('d/m/Y \à\s H:i') }}
        </div>
    </div>

    {{-- === RESUMO EXECUTIVO === --}}
    <div class="resumo-executivo">
        <h2>Resumo Executivo</h2>

        <p>
            Este relatório consolida <strong>{{ $totalOS }} Ordem{{ $totalOS != 1 ? 's' : '' }} de Serviço</strong>
            executada{{ $totalOS != 1 ? 's' : '' }} no período de
            <strong>{{ \Carbon\Carbon::parse($relatorioFechamento->data_inicio)->format('d/m/Y') }}</strong> a
            <strong>{{ \Carbon\Carbon::parse($relatorioFechamento->data_fim)->format('d/m/Y') }}</strong>,
            totalizando <strong>{{ number_format($totalHorasTrabalhadas, 2, ',', '.') }} horas</strong> de trabalho
            realizado por <strong>{{ $totalConsultores }} consultor{{ $totalConsultores != 1 ? 'es' : '' }}</strong>.
        </p>

        <p>
            O valor total a faturar é de <strong>R$ {{ number_format($valorTotalGeral, 2, ',', '.') }}</strong>,
            composto por serviços prestados (R$ {{ number_format($totalServicos, 2, ',', '.') }}),
            despesas (R$ {{ number_format($totalDespesas, 2, ',', '.') }})
            @if($totalKMDeslocamento > 0)
                e custos de deslocamento/KM (R$ {{ number_format($totalKMDeslocamento, 2, ',', '.') }})
            @endif.
        </p>

        <div class="resumo-cards">
            <div class="resumo-card">
                <span class="resumo-card-label">Total de OSs</span>
                <span class="resumo-card-value">{{ $totalOS }}</span>
            </div>
            <div class="resumo-card">
                <span class="resumo-card-label">Horas Totais</span>
                <span class="resumo-card-value">{{ number_format($totalHorasTrabalhadas, 1, ',', '.') }}h</span>
            </div>
            <div class="resumo-card">
                <span class="resumo-card-label">Despesas</span>
                <span class="resumo-card-value">R$ {{ number_format($totalDespesas, 0, ',', '.') }}</span>
            </div>
            <div class="resumo-card">
                <span class="resumo-card-label">Total a Faturar</span>
                <span class="resumo-card-value">R$ {{ number_format($valorTotalGeral, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- === TABELA DETALHADA DAS OSs === --}}
    <h2>Detalhamento das Ordens de Serviço</h2>

    <table>
        <thead>
            <tr>
                <th style="width: 6%;">OS Nº</th>
                <th style="width: 13%;">Consultor</th>
                <th style="width: 8%;">Data</th>
                <th style="width: 15%;">Produto/Serviço</th>
                <th style="width: 12%;">Horário</th>
                <th style="width: 6%;" class="text-center">Horas</th>
                <th style="width: 8%;" class="text-right">Vlr Hora</th>
                <th style="width: 10%;" class="text-right">Serviço</th>
                <th style="width: 9%;" class="text-right">Despesas</th>
                <th style="width: 11%;" class="text-right">Total OS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ordemServicos as $os)
                @php
                    $precoServico = floatval($os->preco_produto ?? 0);
                    $despesas = floatval($os->valor_despesa ?? 0);
                    $kmDesloc = 0;

                    if ($os->is_presencial && $os->consultor) {
                        $km = floatval($os->km ?? 0);
                        $valorKM = $km * floatval($os->consultor->valor_km ?? 0);
                        $valorDesloc = floatval($os->consultor->valor_desloc ?? 0);
                        $kmDesloc = $valorKM + $valorDesloc;
                    }

                    $totalOS = $precoServico + $despesas + $kmDesloc;
                    $horas = floatval($os->qtde_total ?? 0);
                    $valorHora = $horas > 0 ? $precoServico / $horas : 0;

                    $horaInicio = $os->hora_inicio ? \Carbon\Carbon::parse($os->hora_inicio)->format('H:i') : '--:--';
                    $horaFinal = $os->hora_final ? \Carbon\Carbon::parse($os->hora_final)->format('H:i') : '--:--';
                    $horaDesconto = floatval($os->hora_desconto ?? 0);
                @endphp
                <tr>
                    <td class="text-center"><strong>#{{ $os->id }}</strong></td>
                    <td>{{ $os->consultor->name ?? 'N/A' }}</td>
                    <td class="text-nowrap">{{ $os->created_at->format('d/m/Y') }}</td>
                    <td>
                        {{ $os->produtoTabela->produto->descricao ?? 'N/A' }}
                        @if($os->is_presencial)
                            <span class="badge badge-presencial">Presencial</span>
                        @else
                            <span class="badge badge-remoto">Remoto</span>
                        @endif
                    </td>
                    <td class="text-nowrap" style="font-size: 7pt;">
                        {{ $horaInicio }} - {{ $horaFinal }}
                        @if($horaDesconto > 0)
                            <br><small style="color: #e74c3c;">(-{{ number_format($horaDesconto, 2, ',', '.') }}h)</small>
                        @endif
                    </td>
                    <td class="text-center"><strong>{{ number_format($horas, 2, ',', '.') }}</strong></td>
                    <td class="text-right">R$ {{ number_format($valorHora, 2, ',', '.') }}</td>
                    <td class="text-right"><strong>R$ {{ number_format($precoServico, 2, ',', '.') }}</strong></td>
                    <td class="text-right">R$ {{ number_format($despesas, 2, ',', '.') }}</td>
                    <td class="text-right valor-positivo">R$ {{ number_format($totalOS, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- === TOTALIZAÇÃO FINAL === --}}
    <div class="totais-box">
        <h3>Totalização Final</h3>

        <div class="total-linha">
            <div class="total-label">Total de Ordens de Serviço:</div>
            <div class="total-valor">{{ $totalOS }} OS</div>
        </div>

        <div class="total-linha">
            <div class="total-label">Total de Horas Trabalhadas:</div>
            <div class="total-valor">{{ number_format($totalHorasTrabalhadas, 2, ',', '.') }} horas</div>
        </div>

        <div class="total-linha" style="border-top: 1px solid #bdc3c7; padding-top: 8px; margin-top: 8px;">
            <div class="total-label">Soma de Todos os Serviços:</div>
            <div class="total-valor">R$ {{ number_format($totalServicos, 2, ',', '.') }}</div>
        </div>

        <div class="total-linha">
            <div class="total-label">Soma de Todas as Despesas:</div>
            <div class="total-valor">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
        </div>

        @if($totalKMDeslocamento > 0)
        <div class="total-linha">
            <div class="total-label">Soma de KM e Deslocamento:</div>
            <div class="total-valor">R$ {{ number_format($totalKMDeslocamento, 2, ',', '.') }}</div>
        </div>
        @endif
    </div>

    <div class="total-geral">
        <span class="total-geral-label">VALOR TOTAL GERAL A FATURAR</span>
        <span class="total-geral-valor">R$ {{ number_format($valorTotalGeral, 2, ',', '.') }}</span>
    </div>

    {{-- === OBSERVAÇÕES === --}}
    @if($relatorioFechamento->observacoes)
    <div class="observacoes">
        <h3>Observações</h3>
        <p>{{ $relatorioFechamento->observacoes }}</p>
    </div>
    @endif

    {{-- === RODAPÉ === --}}
    <div class="footer">
        <div>Documento gerado automaticamente pelo sistema | {{ now()->format('d/m/Y H:i:s') }}</div>
        @if($relatorioFechamento->aprovado_por)
        <div>Aprovado por: {{ $relatorioFechamento->aprovador->name ?? 'N/A' }} em {{ \Carbon\Carbon::parse($relatorioFechamento->data_aprovacao)->format('d/m/Y H:i') }}</div>
        @endif
    </div>
</body>
</html>
