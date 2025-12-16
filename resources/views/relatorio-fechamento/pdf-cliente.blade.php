<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fechamento de Ordens de Serviço</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 11px; }
        h2 { color: #333; text-align: center; margin: 10px 0; }
        p { margin: 3px 0; text-align: center; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        thead { display: table-header-group; }
        th { background-color: #f4f4f4; padding: 6px; border: 1px solid #999; font-weight: bold; text-align: center; font-size: 10px; }
        td { padding: 5px 6px; border: 1px solid #ccc; font-size: 10px; }
        .text-left, .col-left { text-align: left; }
        .text-right, .col-right { text-align: right; }
        .text-center { text-align: center; }
        .nowrap { white-space: nowrap; text-align: center; }
        .total-row { background-color: #f0f0f0; font-weight: bold; }
        .grand-total { background-color: #e0e0e0; font-weight: bold; }
        .total-value { background-color: #fffacd; font-weight: bold; }
        .descricao { background-color: #f9f9f9; font-style: italic; }
        .order-block { page-break-inside: avoid; margin-bottom: 20px; }
        .summary-box {
            background-color: #e8f4f8;
            border: 2px solid #0066cc;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 12px;
        }
        .summary-label {
            font-weight: bold;
            color: #333;
        }
        .summary-value {
            color: #0066cc;
            font-weight: bold;
        }
        .detail-breakdown {
            background-color: #fafafa;
            padding: 8px;
            margin: 5px 0;
            border-left: 3px solid #0066cc;
            font-size: 9px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
    </style>
</head>
<body>
    @php
        $registrosCollection = collect($registros ?? []);
        if ($registrosCollection->isEmpty() && isset($ordemServicos)) {
            $registrosCollection = collect($ordemServicos->map(function($os) {
                return [
                    'NUMORC' => $os->id,
                    'CONSULTOR' => $os->consultor->name ?? 'N/A',
                    'DTEMISSAO' => optional($os->created_at)->format('d/m/Y') ?? '',
                    'PRODUTO' => $os->produtoTabela->produto->descricao ?? ($os->produto_nome ?? ''),
                    'HRINICIO' => $os->hora_inicio ? \Carbon\Carbon::parse($os->hora_inicio)->format('H:i') : '--:--',
                    'HRFIM' => $os->hora_final ? \Carbon\Carbon::parse($os->hora_final)->format('H:i') : '--:--',
                    'QTD' => $os->horas_trabalhadas ?? $os->qtde_total ?? 0,
                    'HRDESCONTO' => $os->hora_desconto ?? 0,
                    'VLUNIT' => $os->preco_produto ?? $os->valor_unitario ?? 0,
                    'VLSERVICO' => $os->valor_total ?? $os->valor_servico ?? 0,
                    'DESPESAS' => $os->valor_despesa ?? 0,
                    'VLKM' => $os->km ?? 0,
                    'DESLOCAMENTO' => $os->deslocamento ?? 0,
                    'TEXTO' => $os->detalhamento ?? $os->assunto ?? '',
                ];
            }));
        }

        $groups = $registrosCollection->groupBy('NUMORC');
        $start = $data_inicio ?? ($relatorioFechamento->data_inicio ?? null);
        $end = $data_fim ?? ($relatorioFechamento->data_fim ?? null);
        $clienteNome = $cliente->nome ?? ($nomeCliente ?? ($relatorioFechamento->cliente->nome ?? 'N/A'));
        
        // Calcular totais gerais
        $totalHorasGeral = 0;
        $totalValorServicosGeral = 0;
        $totalGeralFinal = 0;
    @endphp

    <h2>Relatório de Atendimentos</h2>
    <p>Data de Início: {{ $start ? \Carbon\Carbon::parse($start)->format('d/m/Y') : '' }}</p>
    <p>Data de Fim: {{ $end ? \Carbon\Carbon::parse($end)->format('d/m/Y') : '' }}</p>
    <p>Cliente: {{ $clienteNome }}</p>

    @foreach($groups as $atendimento => $itens)
        @php
            $itens = collect($itens);
            $totalHorasAtendimento = 0;
            $totalValorAtendimento = 0;
            
            // Pegar valores da primeira linha
            $firstItem = $itens->first();
            $vlServico = (float)($firstItem['VLSERVICO'] ?? 0);
            $despesas = (float)($firstItem['DESPESAS'] ?? 0);
            $vlKm = (float)($firstItem['VLKM'] ?? 0);
            $deslocamento = (float)($firstItem['DESLOCAMENTO'] ?? 0);
            $total_orcamento = $vlServico + $despesas + $vlKm + $deslocamento;
        @endphp
        <div class="order-block">
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">Nº Atendimento</th>
                        <th style="width: 12%;">Consultor</th>
                        <th style="width: 10%;">Data de Emissão</th>
                        <th style="width: 15%;">Produto</th>
                        <th style="width: 7%;">Início</th>
                        <th style="width: 7%;">Fim</th>
                        <th style="width: 8%;">Horas</th>
                        <th style="width: 7%;">Desconto</th>
                        <th style="width: 10%;">Valor Unitário</th>
                        <th style="width: 10%;">Valor Final</th>
                        <th style="width: 10%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itens as $item)
                        @php
                            $vlunit = (float)($item['VLUNIT'] ?? 0);
                            $qtdHoras = 0.0;
                            $hrInicio = $item['HRINICIO'] ?? null;
                            $hrFim = $item['HRFIM'] ?? null;
                            $hrDescontoRaw = $item['HRDESCONTO'] ?? 0;
                            $hrDesconto = 0.0;

                            // Parse HRDESCONTO: numérico ou HH:MM
                            if (is_numeric($hrDescontoRaw)) {
                                $hrDesconto = (float)$hrDescontoRaw;
                            } elseif (is_string($hrDescontoRaw) && $hrDescontoRaw !== '') {
                                if (strpos($hrDescontoRaw, ':') !== false) {
                                    $parts = explode(':', trim($hrDescontoRaw));
                                    if (count($parts) >= 2) {
                                        $hrDesconto = (int)$parts[0] + ((int)$parts[1] / 60.0);
                                        if (isset($parts[2])) { $hrDesconto += ((int)$parts[2] / 3600.0); }
                                    }
                                } else {
                                    $hrDesconto = (float)$hrDescontoRaw;
                                }
                            }

                            // SEMPRE calcular QTD de HRINICIO/HRFIM quando disponível
                            if ($hrInicio && $hrInicio !== '--:--' && $hrFim && $hrFim !== '--:--') {
                                try {
                                    $startT = \Carbon\Carbon::parse($hrInicio);
                                    $endT = \Carbon\Carbon::parse($hrFim);
                                    if ($endT->lessThanOrEqualTo($startT)) { $endT->addDay(); }
                                    $minutes = $endT->diffInMinutes($startT);
                                    $hours = $minutes / 60.0;
                                    $qtdHoras = $hours - $hrDesconto;
                                    if ($qtdHoras < 0) { $qtdHoras = 0; }
                                } catch (\Exception $e) {
                                    $qtdHoras = (float)($item['QTD'] ?? 0);
                                }
                            } else {
                                // Se não tiver hora início/fim, usar o valor direto
                                $qtdHoras = (float)($item['QTD'] ?? 0);
                            }

                            // Usar o valor de serviço total ou calcular baseado em horas
                            if (isset($item['VLSERVICO']) && $item['VLSERVICO'] > 0) {
                                $valor_final = (float)$item['VLSERVICO'];
                            } else {
                                $valor_final = $qtdHoras * $vlunit;
                            }
                            
                            // Acumular totais do atendimento
                            $totalHorasAtendimento += $qtdHoras;
                            $totalValorAtendimento += $valor_final;
                        @endphp
                        <tr>
                            @if($loop->first)
                                <td rowspan="{{ $itens->count() + 2 }}" class="nowrap">{{ $atendimento }}</td>
                                <td rowspan="{{ $itens->count() + 2 }}" class="col-left">{{ $item['CONSULTOR'] ?? '-' }}</td>
                                <td rowspan="{{ $itens->count() + 2 }}" class="nowrap">{{ $item['DTEMISSAO'] ?? '-' }}</td>
                            @endif
                            <td class="col-left">{{ $item['PRODUTO'] ?? '-' }}</td>
                            <td class="nowrap">{{ $hrInicio ?? '-' }}</td>
                            <td class="nowrap">{{ $hrFim ?? '-' }}</td>
                            <td class="col-right"><strong>{{ number_format($qtdHoras, 2, ',', '.') }}h</strong></td>
                            <td class="nowrap">{{ is_numeric($hrDescontoRaw) ? number_format($hrDesconto, 2, ',', '.') : $hrDescontoRaw }}</td>
                            <td class="col-right">R$ {{ number_format($vlunit, 2, ',', '.') }}</td>
                            <td class="col-right">R$ {{ number_format($valor_final, 2, ',', '.') }}</td>
                            @if($loop->first)
                                <td rowspan="{{ $itens->count() + 2 }}" class="total-value col-right">R$ {{ number_format($total_orcamento, 2, ',', '.') }}</td>
                            @endif
                        </tr>
                    @endforeach
                    
                    @php
                        // Acumular nos totais gerais
                        $totalHorasGeral += $totalHorasAtendimento;
                        $totalValorServicosGeral += $totalValorAtendimento;
                        $totalGeralFinal += $total_orcamento;
                    @endphp
                    
                    <!-- Linha de Breakdown de Valores -->
                    <tr>
                        <td colspan="7" class="col-right" style="background-color: #fafafa; font-size: 9px;">
                            @if($vlServico > 0)
                                <div style="padding: 2px 0;"><strong>Serviço:</strong> R$ {{ number_format($vlServico, 2, ',', '.') }}</div>
                            @endif
                            @if($despesas > 0)
                                <div style="padding: 2px 0;"><strong>Despesas:</strong> R$ {{ number_format($despesas, 2, ',', '.') }}</div>
                            @endif
                            @if($vlKm > 0)
                                <div style="padding: 2px 0;"><strong>KM:</strong> R$ {{ number_format($vlKm, 2, ',', '.') }}</div>
                            @endif
                            @if($deslocamento > 0)
                                <div style="padding: 2px 0;"><strong>Deslocamento:</strong> R$ {{ number_format($deslocamento, 2, ',', '.') }}</div>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Linha de Subtotal -->
                    <tr class="total-row">
                        <td colspan="3" class="col-right"><strong>Subtotal:</strong></td>
                        <td class="col-right"><strong>{{ number_format($totalHorasAtendimento, 2, ',', '.') }}h</strong></td>
                        <td colspan="2"></td>
                        <td class="col-right"><strong>R$ {{ number_format($totalValorAtendimento, 2, ',', '.') }}</strong></td>
                    </tr>
                    
                    <!-- Linha de Descrição -->
                    <tr>
                        <td colspan="11" class="descricao">{{ $itens->pluck('TEXTO')->filter()->join(', ') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- Resumo Final -->
    <div class="summary-box">
        <div class="summary-row">
            <span class="summary-label">Total de Atendimentos:</span>
            <span class="summary-value">{{ $groups->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total de Horas Trabalhadas:</span>
            <span class="summary-value">{{ number_format($totalHorasGeral, 2, ',', '.') }}h</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total em Serviços:</span>
            <span class="summary-value">R$ {{ number_format($totalValorServicosGeral, 2, ',', '.') }}</span>
        </div>
        <div class="summary-row" style="border-top: 2px solid #0066cc; padding-top: 10px; margin-top: 10px; font-size: 14px;">
            <span class="summary-label">TOTAL GERAL:</span>
            <span class="summary-value">R$ {{ number_format($totalGeralFinal, 2, ',', '.') }}</span>
        </div>
    </div>

</body>
</html>