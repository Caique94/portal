<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de OS - Consultor</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        .header img { max-width: 200px; height: auto; margin-bottom: 10px; }
        .header h1 { color: #0d6efd; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .section { margin-bottom: 20px; }
        .section-title { background: #0d6efd; color: white; padding: 8px; font-weight: bold; margin-bottom: 10px; }
        .info-row { display: table; width: 100%; margin-bottom: 5px; }
        .info-label { display: table-cell; width: 30%; font-weight: bold; color: #333; }
        .info-value { display: table-cell; width: 70%; color: #555; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATÓRIO DE ORDEM DE SERVIÇO</h1>
        <p><strong>Consultor</strong></p>
        <p>OS Nº {{ $numero_os }}</p>
    </div>

    <div class="section">
        <div class="section-title">Informações da Ordem de Serviço</div>
        <div class="info-row">
            <div class="info-label">Número da OS:</div>
            <div class="info-value">{{ $numero_os }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Data de Emissão:</div>
            <div class="info-value">{{ $data_emissao }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Data de Aprovação:</div>
            <div class="info-value">{{ $data_aprovacao ?? 'Pendente' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Assunto:</div>
            <div class="info-value">{{ $assunto ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Projeto:</div>
            <div class="info-value">{{ $projeto ?? '-' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Dados do Cliente</div>
        <div class="info-row">
            <div class="info-label">Código:</div>
            <div class="info-value">{{ $cliente->codigo ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nome:</div>
            <div class="info-value">{{ $cliente->nome ?? '-' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Dados do Consultor</div>
        <div class="info-row">
            <div class="info-label">Nome:</div>
            <div class="info-value">{{ $consultor->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">E-mail:</div>
            <div class="info-value">{{ $consultor->email ?? '-' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Serviço Realizado</div>
        <div class="info-row">
            <div class="info-label">Produto:</div>
            <div class="info-value">{{ $produto->descricao ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Hora Início:</div>
            <div class="info-value">{{ $hora_inicio ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Hora Final:</div>
            <div class="info-value">{{ $hora_final ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Quantidade Total (horas):</div>
            <div class="info-value">{{ $qtde_total ?? '-' }}</div>
        </div>
    </div>

    <!-- Exibir valor total do totalizador do consultor -->
    @if($totalizador)
    <div class="section" style="background: #f0f4ff; border-left: 4px solid #0d6efd; padding: 15px; margin: 20px 0;">
        <div class="info-row">
            <div class="info-label" style="font-weight: bold;">VALOR TOTAL (Consultor):</div>
            <div class="info-value" style="text-align: right; font-weight: bold; font-size: 14px; color: #0d6efd;">
                R$ {{ number_format($totalizador['total_geral'], 2, ',', '.') }}
            </div>
        </div>
    </div>
    @endif

    @if($detalhamento)
    <div class="section">
        <div class="section-title">Detalhamento</div>
        <p style="padding: 10px; background: #f9f9f9;">{{ $detalhamento }}</p>
    </div>
    @endif

    @if($totalizador)
    <div class="section">
        <div class="section-title">Totalizador</div>
        <table>
            <tbody>
                <tr>
                    <td style="width: 50%;">{{ $totalizador['valor_hora_label'] }}:</td>
                    <td style="text-align: right;">R$ {{ number_format($totalizador['valor_hora'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Horas:</td>
                    <td style="text-align: right;">{{ number_format($totalizador['horas'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Valor Horas:</td>
                    <td style="text-align: right;">R$ {{ number_format($totalizador['valor_horas'], 2, ',', '.') }}</td>
                </tr>
                @if($totalizador['km'] > 0 && $totalizador['is_presencial'])
                <tr>
                    <td>{{ $totalizador['valor_km_label'] }}:</td>
                    <td style="text-align: right;">R$ {{ number_format($totalizador['valor_km'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>KM:</td>
                    <td style="text-align: right;">{{ number_format($totalizador['km'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Valor KM:</td>
                    <td style="text-align: right;">R$ {{ number_format($totalizador['valor_km_total'], 2, ',', '.') }}</td>
                </tr>
                @endif
                @if($totalizador['deslocamento'] > 0 && $totalizador['is_presencial'])
                <tr>
                    <td>Deslocamento (horas):</td>
                    <td style="text-align: right;">{{ number_format($totalizador['deslocamento'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Valor Deslocamento:</td>
                    <td style="text-align: right;">R$ {{ number_format($totalizador['valor_deslocamento'], 2, ',', '.') }}</td>
                </tr>
                @endif
                @if($totalizador['despesas'] > 0)
                <tr>
                    <td>Despesas:</td>
                    <td style="text-align: right;">R$ {{ number_format($totalizador['despesas'], 2, ',', '.') }}</td>
                </tr>
                @endif
                <tr style="background-color: #e8eef7; font-weight: bold;">
                    <td>TOTAL GERAL:</td>
                    <td style="text-align: right;">R$ {{ number_format($totalizador['total_geral'], 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Documento gerado automaticamente em {{ now()->format('d/m/Y H:i') }}</p>
        <p>© {{ date('Y') }} Personalitec Soluções</p>
    </div>
</body>
</html>
