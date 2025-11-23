<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #ffffff; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; }
        .logo-section { text-align: center; padding: 30px 20px 20px; background-color: #ffffff; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .info-box { background: #ffffff; padding: 15px; border-left: 4px solid #667eea; margin: 15px 0; color: #333333; }
        .info-box strong { color: #667eea; }
        .highlight { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666666; }
        .attachment-notice { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .signature { margin-top: 35px; padding-top: 25px; border-top: 1px solid #e0e0e0; color: #333333; }

        /* Suporte para temas escuros */
        @media (prefers-color-scheme: dark) {
            body { background-color: #1a1a1a !important; color: #e0e0e0 !important; }
            .container { background-color: #1a1a1a !important; }
            .logo-section { background-color: #2d2d2d !important; }
            .content { background: #2d2d2d !important; }
            .info-box { background: #3d3d3d !important; color: #e0e0e0 !important; border-left-color: #667eea !important; }
            .info-box strong { color: #8b9dff !important; }
            .signature { color: #e0e0e0 !important; border-top-color: #4d4d4d !important; }
            .footer { color: #999999 !important; }
            .attachment-notice { background: #3d3510 !important; border-color: #ffc107 !important; color: #ffd966 !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-section">
            <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec Soluções" style="max-width: 220px; height: auto;">
        </div>

        <div class="header">
            <h1>Ordem de Serviço</h1>
            <p style="font-size: 18px; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; display: inline-block; margin-top: 10px;">OS Nº {{ $numero_os }}</p>
        </div>

        <div class="content">
            <p>Prezado(a) <strong>{{ $recipient_name ?? $os->cliente->nome }}</strong>,</p>

            <p>Segue abaixo os detalhes da Ordem de Serviço <strong>#{{ $numero_os }}</strong> referente aos serviços prestados.</p>

            <div class="info-box">
                <strong style="color: #667eea; font-size: 16px;">Informações da Ordem de Serviço</strong><br><br>
                <strong>Número da OS:</strong> {{ $numero_os }}<br>
                <strong>Data de Emissão:</strong> {{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}<br>
                @if($os->approved_at)
                <strong>Data de Aprovação:</strong> {{ \Carbon\Carbon::parse($os->approved_at)->format('d/m/Y H:i') }}<br>
                @endif
                @if($os->assunto)
                <strong>Assunto:</strong> {{ $os->assunto }}<br>
                @endif
                @if($os->projeto)
                <strong>Projeto:</strong> {{ $os->projeto }}<br>
                @endif
            </div>

            <div class="info-box">
                <strong style="color: #667eea; font-size: 16px;">Cliente</strong><br><br>
                @if($os->cliente->codigo)
                <strong>Código:</strong> {{ $os->cliente->codigo }}<br>
                @endif
                <strong>Nome:</strong> {{ $os->cliente->nome }}
            </div>

            <div class="info-box">
                <strong style="color: #667eea; font-size: 16px;">Serviço Prestado</strong><br><br>
                @if($os->produtoTabela && $os->produtoTabela->produto)
                <strong>Descrição:</strong> {{ $os->produtoTabela->produto->descricao }}<br>
                @endif
                @if($os->qtde_total)
                <strong>Quantidade (horas):</strong> {{ $os->qtde_total }}<br>
                @endif
                @if($os->cliente->km)
                <strong>Quilometragem:</strong> {{ $os->cliente->km }} km<br>
                @endif
                @if($os->cliente->deslocamento)
                <strong>Deslocamento:</strong> R$ {{ number_format((float)$os->cliente->deslocamento, 2, ',', '.') }}
                @endif
            </div>

            @if($os->detalhamento)
            <div class="info-box">
                <strong style="color: #667eea; font-size: 16px;">Descrição dos Serviços</strong><br><br>
                <div style="white-space: pre-wrap; line-height: 1.8;">{{ $os->detalhamento }}</div>
            </div>
            @endif

            @if($totalizador)
            <div class="info-box">
                <strong style="color: #667eea; font-size: 16px;">Totalizador</strong><br><br>
                <table style="width: 100%; border-collapse: collapse;">
                    <tbody>
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td><strong>{{ $totalizador['valor_hora_label'] }}:</strong></td>
                            <td style="text-align: right;">R$ {{ number_format($totalizador['valor_hora'], 2, ',', '.') }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td><strong>Horas:</strong></td>
                            <td style="text-align: right;">{{ number_format($totalizador['horas'], 2, ',', '.') }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td><strong>Valor Horas:</strong></td>
                            <td style="text-align: right;">R$ {{ number_format($totalizador['valor_horas'], 2, ',', '.') }}</td>
                        </tr>
                        @if($totalizador['km'] > 0 && $totalizador['is_presencial'] ?? false)
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td><strong>{{ $totalizador['valor_km_label'] }}:</strong></td>
                            <td style="text-align: right;">R$ {{ number_format($totalizador['valor_km'], 2, ',', '.') }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td><strong>KM:</strong></td>
                            <td style="text-align: right;">{{ number_format($totalizador['km'], 2, ',', '.') }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td><strong>Valor KM:</strong></td>
                            <td style="text-align: right;">R$ {{ number_format($totalizador['valor_km_total'], 2, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($totalizador['deslocamento'] > 0 && $totalizador['is_presencial'] ?? false)
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td><strong>Deslocamento (horas):</strong></td>
                            <td style="text-align: right;">{{ number_format($totalizador['deslocamento'], 2, ',', '.') }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td><strong>Valor Deslocamento:</strong></td>
                            <td style="text-align: right;">R$ {{ number_format($totalizador['valor_deslocamento'], 2, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($totalizador['despesas'] > 0)
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td><strong>Despesas:</strong></td>
                            <td style="text-align: right;">R$ {{ number_format($totalizador['despesas'], 2, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr style="background-color: #f0f0f0;">
                            <td><strong style="font-size: 16px;">TOTAL GERAL:</strong></td>
                            <td style="text-align: right; font-weight: bold; font-size: 16px;">R$ {{ number_format($totalizador['total_geral'], 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif

            <div class="highlight">
                <h2 style="margin: 0; font-size: 16px; text-transform: uppercase; letter-spacing: 1px;">VALOR TOTAL</h2>
                <p style="font-size: 36px; font-weight: bold; margin: 10px 0;">
                    R$ {{ number_format($totalizador['total_geral'] ?? $os->valor_total, 2, ',', '.') }}
                </p>
            </div>

            <div class="attachment-notice">
                <strong style="display: block; margin-bottom: 5px;">📎 Documento Anexo</strong>
                Uma versão em PDF desta ordem de serviço está anexada a este e-mail para seus registros.
            </div>

            <p style="color: #666;">Caso tenha alguma dúvida ou necessite de esclarecimentos adicionais, não hesite em entrar em contato conosco.</p>

            <div class="signature">
                <p><strong>Atenciosamente,</strong></p>
                <p style="color: #667eea; font-weight: 600; font-size: 16px;">Equipe Personalitec Soluções</p>
                <p style="font-size: 13px; color: #999; margin-top: 15px;"><em>Soluções personalizadas para o seu negócio</em></p>
            </div>
        </div>

        <div class="footer">
            <p style="font-weight: 600;">© {{ date('Y') }} Personalitec Soluções</p>
            <p>Este é um e-mail automático. Por favor, não responda diretamente a esta mensagem.</p>
        </div>
    </div>
</body>
</html>
