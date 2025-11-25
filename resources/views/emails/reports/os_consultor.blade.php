<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Atendimento</title>
</head>
<body style="margin: 0; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Roboto, Arial, sans-serif; background-color: #f5f7f9; line-height: 1.5;">
    <div style="max-width: 900px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
        <!-- HEADER -->
        <div style="background: linear-gradient(90deg, #2f88d8 0%, #4fa1e6 100%); color: #ffffff; padding: 25px 30px; display: flex; align-items: center; justify-content: space-between; gap: 25px;">
            <div style="display: flex; align-items: center; gap: 15px; flex: 0 0 auto;">
                <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec" style="height: 55px; width: auto; display: block;">
                <div style="font-size: 13px; font-weight: 600; line-height: 1.4;">
                    Personalitec<br>
                    <span style="font-size: 11px; opacity: 0.85; font-weight: 400;">Sua visão, nossa tecnologia</span>
                </div>
            </div>
            <div style="flex: 1; text-align: center; font-size: 32px; font-weight: 700; letter-spacing: 2px;">
                ORDEM DE ATENDIMENTO
            </div>
            <div style="background-color: rgba(31, 95, 161, 0.95); padding: 14px 20px; border-radius: 6px; text-align: center; flex-shrink: 0;">
                <div style="font-size: 10px; opacity: 0.8; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Número</div>
                <div style="font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div style="padding: 30px;">
            <!-- INFO SECTION - TWO COLUMNS -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                <tr>
                    <td style="width: 50%; padding-right: 25px; vertical-align: top;">
                        <div style="background-color: #f5f7f9; border: 1px solid #dce4ef; border-radius: 8px; padding: 20px;">
                            <div style="margin: 10px 0; line-height: 1.6; font-size: 14px;">
                                <span style="font-weight: 700; color: #111; display: inline-block; min-width: 90px;">Cliente:</span>
                                <span style="color: #333;">{{ $os->cliente->nome }}</span>
                            </div>
                            <div style="margin: 10px 0; line-height: 1.6; font-size: 14px;">
                                <span style="font-weight: 700; color: #111; display: inline-block; min-width: 90px;">Contato:</span>
                                <span style="color: #333;">{{ $os->cliente->contato ?? '-' }}</span>
                            </div>
                            <div style="margin: 10px 0; line-height: 1.6; font-size: 14px;">
                                <span style="font-weight: 700; color: #111; display: inline-block; min-width: 90px;">Emissão:</span>
                                <span style="color: #333;">{{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}</span>
                            </div>
                            <div style="margin: 10px 0; line-height: 1.6; font-size: 14px;">
                                <span style="font-weight: 700; color: #111; display: inline-block; min-width: 90px;">Consultor:</span>
                                <span style="color: #333;">{{ $os->consultor->name }}</span>
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 25px; vertical-align: top;">
                        <table style="width: 100%; border: 1px solid #dce4ef; border-radius: 8px; overflow: hidden; border-collapse: collapse;">
                            <tr style="background-color: #f7f9fc;">
                                <th style="padding: 12px 8px; text-align: center; font-weight: 700; color: #555; font-size: 12px; border-right: 1px solid #dce4ef;">HORA<br>INICIO</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 700; color: #555; font-size: 12px; border-right: 1px solid #dce4ef;">HORA<br>FIM</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 700; color: #555; font-size: 12px; border-right: 1px solid #dce4ef;">DESCONTO</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 700; color: #555; font-size: 12px; border-right: 1px solid #dce4ef;">TRASLADO</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 700; color: #555; font-size: 12px;">TOTAL<br>HORAS</th>
                            </tr>
                            <tr style="background-color: #ffffff;">
                                <td style="padding: 13px 8px; text-align: center; font-weight: 700; font-size: 13px; color: #222; border-right: 1px solid #eee;">--:--</td>
                                <td style="padding: 13px 8px; text-align: center; font-weight: 700; font-size: 13px; color: #222; border-right: 1px solid #eee;">--:--</td>
                                <td style="padding: 13px 8px; text-align: center; font-weight: 700; font-size: 13px; color: #222; border-right: 1px solid #eee;">0:00</td>
                                <td style="padding: 13px 8px; text-align: center; font-weight: 700; font-size: 13px; color: #222; border-right: 1px solid #eee;">{{ number_format($totalizador['deslocamento'] ?? 0, 2, ',', '.') }}</td>
                                <td style="padding: 13px 8px; text-align: center; font-weight: 700; font-size: 13px; color: #2563eb;">{{ number_format($totalizador['horas'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- DETALHAMENTO -->
            <div style="background-color: #2f88d8; color: #ffffff; padding: 14px 20px; font-weight: 700; font-size: 14px; margin-bottom: 0; border-radius: 8px 8px 0 0; letter-spacing: 0.5px;">DETALHAMENTO</div>
            <div style="background-color: #fbfcfd; border: 1px solid #dce4ef; border-top: none; border-radius: 0 0 8px 8px; padding: 18px; margin-bottom: 30px; line-height: 1.8;">
                <p style="margin: 10px 0; font-size: 14px; color: #333;"><strong style="color: #111; font-weight: 700;">Assunto:</strong> {{ $os->assunto ?? '-' }}</p>
                <p style="margin: 10px 0; font-size: 14px; color: #333;"><strong style="color: #111; font-weight: 700;">Observações:</strong> {{ $os->observacao ?? 'Nenhuma observação adicionada' }}</p>
            </div>

            <!-- RESUMO -->
            <div style="background-color: #2f88d8; color: #ffffff; padding: 14px 18px; font-weight: 700; font-size: 14px; border-radius: 8px; display: inline-block; margin-bottom: 14px; letter-spacing: 0.5px;">RESUMO</div>
            <table style="width: 100%; border: 1px solid #dce4ef; border-radius: 8px; margin-bottom: 30px; border-collapse: collapse; overflow: hidden;">
                <tr>
                    <td style="width: 65%; padding: 16px; vertical-align: middle; border-right: 1px solid #dce4ef; background-color: #ffffff;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <tr>
                                <th style="background-color: #f7f9fc; border: 1px solid #dce4ef; padding: 10px; font-weight: 700; text-align: center; color: #555; font-size: 12px;">Chamado Personalitec</th>
                                <th style="background-color: #f7f9fc; border: 1px solid #dce4ef; padding: 10px; font-weight: 700; text-align: center; color: #555; font-size: 12px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</th>
                                <th style="background-color: #f7f9fc; border: 1px solid #dce4ef; padding: 10px; font-weight: 700; text-align: center; color: #555; font-size: 12px;">Previsão Retorno</th>
                                <th style="background-color: #f7f9fc; border: 1px solid #dce4ef; padding: 10px; font-weight: 700; text-align: center; color: #555; font-size: 12px;">{{ \Carbon\Carbon::parse($os->data_emissao)->addDays(1)->format('d/m/Y') }}</th>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #dce4ef; padding: 12px; text-align: center; font-size: 13px; color: #333; background-color: #ffffff;">KM</td>
                                <td style="border: 1px solid #dce4ef; padding: 12px; text-align: center; font-size: 13px; color: #333; background-color: #ffffff;">{{ $os->km ?? '0' }}</td>
                                <td style="border: 1px solid #dce4ef; padding: 12px; text-align: center; font-weight: 700; color: #2f88d8; font-size: 13px; background-color: #ffffff;">TOTAL OS</td>
                                <td style="border: 1px solid #dce4ef; padding: 12px; text-align: center; font-weight: 700; color: #2f88d8; font-size: 14px; background-color: #ffffff;">R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 35%; padding: 16px; text-align: center; vertical-align: middle; background-color: #fbfcfd;">
                        <div style="font-weight: 700; color: #2f88d8; margin-bottom: 6px; font-size: 13px; letter-spacing: 0.3px;">Personalitec</div>
                        <div style="font-size: 12px; color: #2f88d8; text-decoration: none; line-height: 1.5;">atendimento@personalitec.com.br</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- FOOTER -->
        <div style="padding: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 11px; color: #6b7280; background-color: #f9fafb;">
            <p style="margin: 4px 0; line-height: 1.4;">Este é um e-mail automático. Por favor, não responda.</p>
            <p style="margin: 4px 0; line-height: 1.4;">© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
