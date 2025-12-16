<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Atendimentos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h2 {
            color: #333;
            text-align: center;
        }
        p {
            margin: 5px 0;
            text-align: center;
        }
        .order-block {
            margin-top: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .total-label {
            font-weight: bold;
            background-color: #f9f9f9;
            text-align: center;
            padding: 10px 0;
        }
        .total-value {
            font-weight: bold;
            background-color: #f9f9f9;
            text-align: center;
        }
        .descricao {
            text-align: left;
            padding-left: 10px;
            border: 1px solid #ddd;
            border-top: none;
        }
    </style>
</head>
<body>
    @php
        $start = $data_inicio ?? ($relatorioFechamento->data_inicio ?? null);
        $end = $data_fim ?? ($relatorioFechamento->data_fim ?? null);
        $clienteNome = $cliente->nome ?? ($nomeCliente ?? 'N/A');
        $collection = collect($registros ?? []);
        $groups = $collection->groupBy('NUMORC');
        $totalGeral = $total_geral ?? $collection->sum(function($i){ return (float)($i['TOTAL_ORCAMENTO'] ?? 0); });
    @endphp

    <h2>Relatório de Atendimentos</h2>
    <p>Data de Início: {{ $start ? \Carbon\Carbon::parse($start)->format('d/m/Y') : '' }}</p>
    <p>Data de Fim: {{ $end ? \Carbon\Carbon::parse($end)->format('d/m/Y') : '' }}</p>
    <p>Cliente: {{ $clienteNome }}</p>

    @foreach($groups as $atendimento => $itens)
        @php
            $itens = collect($itens);
            $total_orcamento = (float)($itens->first()['TOTAL_ORCAMENTO'] ?? 0);
        @endphp
        <div class="order-block">
            <table>
                <thead>
                    <tr>
                        <th>Nº Atendimento</th>
                        <th>Consultor</th>
                        <th>Data de Emissão</th>
                        <th>Produto</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Quantidade</th>
                        <th>Desconto</th>
                        <th>Valor Unitário</th>
                        <th>Valor Final</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itens as $item)
                        @php
                            $vlunit = (float)($item['VLUNIT'] ?? 0);
                            $qtd = 0.0;
                            $hrInicio = $item['HRINICIO'] ?? null;
                            $hrFim = $item['HRFIM'] ?? null;
                            $hrDescontoRaw = $item['HRDESCONTO'] ?? 0;
                            $hrDesconto = 0.0;
                            if (is_numeric($hrDescontoRaw)) {
                                $hrDesconto = (float)$hrDescontoRaw;
                            } else {
                                try { $d = \Carbon\Carbon::parse($hrDescontoRaw); $hrDesconto = $d->hour + ($d->minute/60) + ($d->second/3600); } catch (\Exception $e) { $hrDesconto = 0.0; }
                            }

                            if ($hrInicio && $hrInicio !== '--:--' && $hrFim && $hrFim !== '--:--') {
                                try {
                                    $startT = \Carbon\Carbon::parse($hrInicio);
                                    $endT = \Carbon\Carbon::parse($hrFim);
                                    if ($endT->lessThanOrEqualTo($startT)) { $endT->addDay(); }
                                    $minutes = $endT->diffInMinutes($startT);
                                    $hours = $minutes / 60.0;
                                    $qtd = round($hours - $hrDesconto, 2);
                                    if ($qtd < 0) { $qtd = 0; }
                                } catch (\Exception $e) {
                                    $qtd = (float)($item['QTD'] ?? 0);
                                }
                            } else {
                                $qtd = (float)($item['QTD'] ?? 0);
                            }

                            $valor_final = $qtd * $vlunit;
                        @endphp
                        <tr>
                            @if($loop->first)
                                <td rowspan="{{ $itens->count() }}">{{ $atendimento }}</td>
                                <td rowspan="{{ $itens->count() }}">{{ $item['CONSULTOR'] ?? '-' }}</td>
                                <td rowspan="{{ $itens->count() }}">{{ $item['DTEMISSAO'] ?? '-' }}</td>
                            @endif
                            <td>{{ $item['PRODUTO'] ?? '-' }}</td>
                            <td>{{ $item['HRINICIO'] ?? '-' }}</td>
                            <td>{{ $item['HRFIM'] ?? '-' }}</td>
                            <td>{{ number_format($qtd, 2, ',', '.') }}</td>
                            <td>{{ $item['HRDESCONTO'] ?? '-' }}</td>
                            <td>R$ {{ number_format($vlunit, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($valor_final, 2, ',', '.') }}</td>
                            @if($loop->first)
                                <td rowspan="{{ $itens->count() }}" class="total-value">R$ {{ number_format($total_orcamento, 2, ',', '.') }}</td>
                            @endif
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="11" class="descricao">{{ $itens->pluck('TEXTO')->filter()->join(', ') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    <div style="text-align: center; margin-top: 20px;">
        <p><strong>Total Geral:</strong> R$ {{ number_format($totalGeral, 2, ',', '.') }}</p>
    </div>
    
</body>
</html>
