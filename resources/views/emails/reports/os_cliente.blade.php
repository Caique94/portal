<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Atendimento</title>
</head>
<body style="margin: 0; padding: 20px; font-family: 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f7f9;">
    <div style="max-width: 900px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden;">
        <!-- HEADER -->
        <div style="background: linear-gradient(90deg, #2f88d8 0%, #4fa1e6 100%); color: #ffffff; padding: 30px 24px; display: flex; align-items: center; justify-content: space-between; gap: 30px;">
            <div style="display: flex; align-items: center; gap: 16px; flex: 0 0 auto;">
                <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec" style="height: 60px; width: auto;">
                <div style="font-size: 14px; font-weight: 600; line-height: 1.4;">
                    Personalitec<br>
                    <span style="font-size: 12px; opacity: 0.9;">Sua visão, nossa tecnologia</span>
                </div>
            </div>
            <div style="flex: 1; text-align: center; font-size: 32px; font-weight: 700; letter-spacing: 2px;">
                ORDEM DE ATENDIMENTO
            </div>
            <div style="background-color: #1f5fa1; padding: 16px 22px; border-radius: 6px; text-align: center; flex: 0 0 auto; min-width: 120px;">
                <div style="font-size: 11px; opacity: 0.9; margin-bottom: 6px; text-transform: uppercase;">Número</div>
                <div style="font-size: 20px; font-weight: 700;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div style="padding: 30px;">
            <!-- INFO SECTION - TWO COLUMNS -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                <tr>
                    <td style="width: 50%; padding-right: 24px; vertical-align: top;">
                        <div style="background-color: #ffffff; border: 1px solid #d4e0ed; border-radius: 8px; padding: 20px;">
                            <div style="margin: 8px 0; line-height: 1.6;">
                                <span style="font-weight: 700; color: #111;">Cliente:</span>
                                <span style="margin-left: 20px;">{{ $os->cliente->nome }}</span>
                            </div>
                            <div style="margin: 8px 0; line-height: 1.6;">
                                <span style="font-weight: 700; color: #111;">Contato:</span>
                                <span style="margin-left: 20px;">{{ $os->cliente->contato ?? '-' }}</span>
                            </div>
                            <div style="margin: 8px 0; line-height: 1.6;">
                                <span style="font-weight: 700; color: #111;">Emissão:</span>
                                <span style="margin-left: 20px;">{{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}</span>
                            </div>
                            <div style="margin: 8px 0; line-height: 1.6;">
                                <span style="font-weight: 700; color: #111;">Consultor:</span>
                                <span style="margin-left: 20px;">{{ $os->consultor->name }}</span>
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 24px; vertical-align: top;">
                        <table style="width: 100%; border: 1px solid #d4e0ed; border-radius: 6px; overflow: hidden; border-collapse: collapse;">
                            <tr style="background-color: #f7f9fc;">
                                <th style="padding: 12px 6px; text-align: center; font-weight: 700; color: #666; font-size: 11px; border-right: 1px solid #d4e0ed;">HORA<br>INICIO</th>
                                <th style="padding: 12px 6px; text-align: center; font-weight: 700; color: #666; font-size: 11px; border-right: 1px solid #d4e0ed;">HORA<br>FIM</th>
                                <th style="padding: 12px 6px; text-align: center; font-weight: 700; color: #666; font-size: 11px; border-right: 1px solid #d4e0ed;">DESCONTO</th>
                                <th style="padding: 12px 6px; text-align: center; font-weight: 700; color: #666; font-size: 11px; border-right: 1px solid #d4e0ed;">TRASLADO</th>
                                <th style="padding: 12px 6px; text-align: center; font-weight: 700; color: #666; font-size: 11px;">TOTAL<br>HORAS</th>
                            </tr>
                            <tr>
                                <td style="padding: 12px 6px; text-align: center; font-weight: 700; font-size: 13px; border-right: 1px solid #eee;">--:--</td>
                                <td style="padding: 12px 6px; text-align: center; font-weight: 700; font-size: 13px; border-right: 1px solid #eee;">--:--</td>
                                <td style="padding: 12px 6px; text-align: center; font-weight: 700; font-size: 13px; border-right: 1px solid #eee;">0:00</td>
                                <td style="padding: 12px 6px; text-align: center; font-weight: 700; font-size: 13px; border-right: 1px solid #eee;">{{ number_format($totalizador['deslocamento'] ?? 0, 2, ',', '.') }}</td>
                                <td style="padding: 12px 6px; text-align: center; font-weight: 700; font-size: 13px;">{{ number_format($totalizador['horas'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- DETALHAMENTO -->
            <div style="background-color: #2f88d8; color: #ffffff; padding: 14px 18px; font-weight: 700; font-size: 14px; margin-bottom: 0; border-radius: 6px 6px 0 0;">DETALHAMENTO</div>
            <div style="background-color: #fbfcfd; border: 1px solid #e8eef7; border-top: none; border-radius: 0 0 6px 6px; padding: 18px; margin-bottom: 30px; line-height: 1.8;">
                <p style="margin: 8px 0;"><strong style="color: #111; font-weight: 700;">Assunto:</strong> {{ $os->assunto ?? '-' }}</p>
                <p style="margin: 8px 0;"><strong style="color: #111; font-weight: 700;">Observações:</strong> {{ $os->observacao ?? 'Nenhuma observação adicionada' }}</p>
            </div>

            <!-- RESUMO -->
            <div style="background-color: #2f88d8; color: #ffffff; padding: 14px 18px; font-weight: 700; font-size: 14px; border-radius: 6px; display: inline-block; margin-bottom: 14px; width: calc(100% - 36px);">RESUMO</div>
            <table style="width: 100%; border: 1px solid #d4e0ed; border-radius: 6px; margin-bottom: 30px; border-collapse: collapse; overflow: hidden;">
                <tr>
                    <td style="width: 65%; padding: 16px; vertical-align: middle; border-right: 1px solid #d4e0ed;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <tr>
                                <th style="background-color: #f7f9fc; border: 1px solid #d4e0ed; padding: 10px; font-weight: 700; text-align: center; color: #666; font-size: 12px;">Chamado Personalitec</th>
                                <th style="background-color: #f7f9fc; border: 1px solid #d4e0ed; padding: 10px; font-weight: 700; text-align: center; color: #666; font-size: 12px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</th>
                                <th style="background-color: #f7f9fc; border: 1px solid #d4e0ed; padding: 10px; font-weight: 700; text-align: center; color: #666; font-size: 12px;">Previsão Retorno</th>
                                <th style="background-color: #f7f9fc; border: 1px solid #d4e0ed; padding: 10px; font-weight: 700; text-align: center; color: #666; font-size: 12px;">{{ \Carbon\Carbon::parse($os->data_emissao)->addDays(1)->format('d/m/Y') }}</th>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #d4e0ed; padding: 12px; text-align: center; font-size: 12px;">KM</td>
                                <td style="border: 1px solid #d4e0ed; padding: 12px; text-align: center; font-size: 12px;">{{ $os->km ?? '0' }}</td>
                                <td style="border: 1px solid #d4e0ed; padding: 12px; text-align: center; font-weight: 700; color: #2f88d8; font-size: 13px;">TOTAL OS</td>
                                <td style="border: 1px solid #d4e0ed; padding: 12px; text-align: center; font-weight: 700; color: #2f88d8; font-size: 13px;">R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 35%; padding: 16px; text-align: center; vertical-align: middle;">
                        <div style="font-weight: 700; color: #2f88d8; margin-bottom: 6px; font-size: 13px;">Personalitec</div>
                        <div style="font-size: 12px; color: #2f88d8; text-decoration: none;">atendimento@personalitec.com.br</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- FOOTER -->
        <div style="padding: 18px; border-top: 1px solid #eee; text-align: center; font-size: 11px; color: #666; background-color: #fafbfc;">
            <p style="margin: 6px 0;">Este é um e-mail automático. Por favor, não responda.</p>
            <p style="margin: 6px 0;">© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
