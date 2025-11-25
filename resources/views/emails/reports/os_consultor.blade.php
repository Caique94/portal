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
        <div style="background: linear-gradient(90deg, #2f88d8 0%, #4fa1e6 100%); color: #ffffff; padding: 25px 30px; display: flex; align-items: center; justify-content: center; gap: 30px; position: relative;">
            <div style="display: flex; align-items: center; gap: 15px; position: absolute; left: 30px;">
                <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec" style="height: 55px; width: auto; display: block;">
                <div style="font-size: 13px; font-weight: 600; line-height: 1.4;">
                    Personalitec<br>
                    <span style="font-size: 11px; opacity: 0.85; font-weight: 400;">Sua visão, nossa tecnologia</span>
                </div>
            </div>
            <div style="text-align: center; font-size: 20px; font-weight: 700; letter-spacing: 2px;">
                ORDEM DE ATENDIMENTO
            </div>
            <div style="background-color: rgba(31, 95, 161, 0.95); padding: 14px 20px; border-radius: 6px; text-align: center; position: absolute; right: 30px; top: 50%; transform: translateY(-50%);">
                <div style="font-size: 10px; opacity: 0.8; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Número</div>
                <div style="font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div style="padding: 30px;">
            <!-- INFO SECTION - TWO COLUMNS -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
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
                        <table style="width: 100%; border: 1px solid #d4dce8; border-radius: 6px; overflow: hidden; border-collapse: collapse;">
                            <tr style="background-color: #f5f7fa;">
                                <th style="padding: 14px 10px; text-align: center; font-weight: 700; color: #666; font-size: 13px; border-right: 1px solid #d4dce8;">HORA<br>INICIO</th>
                                <th style="padding: 14px 10px; text-align: center; font-weight: 700; color: #666; font-size: 13px; border-right: 1px solid #d4dce8;">HORA<br>FIM</th>
                                <th style="padding: 14px 10px; text-align: center; font-weight: 700; color: #666; font-size: 13px; border-right: 1px solid #d4dce8;">DESCONTO</th>
                                <th style="padding: 14px 10px; text-align: center; font-weight: 700; color: #666; font-size: 13px; border-right: 1px solid #d4dce8;">TRASLADO</th>
                                <th style="padding: 14px 10px; text-align: center; font-weight: 700; color: #666; font-size: 13px;">TOTAL<br>HORAS</th>
                            </tr>
                            <tr style="background-color: #ffffff;">
                                <td style="padding: 14px 10px; text-align: center; font-weight: 700; font-size: 14px; color: #333; border-right: 1px solid #d4dce8;">--:--</td>
                                <td style="padding: 14px 10px; text-align: center; font-weight: 700; font-size: 14px; color: #333; border-right: 1px solid #d4dce8;">--:--</td>
                                <td style="padding: 14px 10px; text-align: center; font-weight: 700; font-size: 14px; color: #333; border-right: 1px solid #d4dce8;">0:00</td>
                                <td style="padding: 14px 10px; text-align: center; font-weight: 700; font-size: 14px; color: #333; border-right: 1px solid #d4dce8;">{{ number_format($totalizador['deslocamento'] ?? 0, 2, ',', '.') }}</td>
                                <td style="padding: 14px 10px; text-align: center; font-weight: 700; font-size: 14px; color: #3B93E3;">{{ number_format($totalizador['horas'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- DETALHAMENTO -->
            <div style="background-color: #3B93E3; color: #ffffff; padding: 12px 16px; font-weight: 700; font-size: 16px; margin-bottom: 0; border-radius: 6px 6px 0 0; letter-spacing: 0.5px; text-align: center;">DETALHAMENTO</div>
            <div style="background-color: #fafbfc; border: 1px solid #d4dce8; border-top: none; border-radius: 0 0 6px 6px; padding: 20px; margin-bottom: 25px; line-height: 1.8;">
                <p style="margin: 8px 0; font-size: 15px; color: #222;"><strong style="color: #111; font-weight: 700;">Assunto:</strong> {{ $os->assunto ?? '-' }}</p>
                <p style="margin: 8px 0; font-size: 15px; color: #222;"><strong style="color: #111; font-weight: 700;">Observações:</strong> {{ $os->observacao ?? 'Nenhuma observação adicionada' }}</p>
            </div>

            <!-- RESUMO -->
            <div style="background-color: #3B93E3; color: #ffffff; padding: 12px 16px; font-weight: 700; font-size: 16px; border-radius: 6px; display: inline-block; margin-bottom: 16px; letter-spacing: 0.5px; text-align: left;">RESUMO</div>
            <table style="width: 100%; border: 1px solid #d4dce8; border-radius: 6px; margin-bottom: 25px; border-collapse: collapse; overflow: hidden;">
                <tr>
                    <td style="width: 60%; padding: 18px; vertical-align: middle; border-right: 1px solid #d4dce8; background-color: #ffffff;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <tr>
                                <th style="background-color: #f5f7fa; border: 1px solid #d4dce8; padding: 12px; font-weight: 700; text-align: center; color: #666; font-size: 13px;">Chamado Personalitec</th>
                                <th style="background-color: #f5f7fa; border: 1px solid #d4dce8; padding: 12px; font-weight: 700; text-align: center; color: #666; font-size: 13px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</th>
                                <th style="background-color: #f5f7fa; border: 1px solid #d4dce8; padding: 12px; font-weight: 700; text-align: center; color: #666; font-size: 13px;">Previsão Retorno</th>
                                <th style="background-color: #f5f7fa; border: 1px solid #d4dce8; padding: 12px; font-weight: 700; text-align: center; color: #666; font-size: 13px;">{{ \Carbon\Carbon::parse($os->data_emissao)->addDays(1)->format('d/m/Y') }}</th>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #d4dce8; padding: 14px; text-align: center; font-size: 14px; color: #333; background-color: #ffffff; font-weight: 600;">KM</td>
                                <td style="border: 1px solid #d4dce8; padding: 14px; text-align: center; font-size: 14px; color: #333; background-color: #ffffff; font-weight: 600;">{{ $os->km ?? '0' }}</td>
                                <td style="border: 1px solid #d4dce8; padding: 14px; text-align: center; font-weight: 700; color: #3B93E3; font-size: 14px; background-color: #ffffff;">TOTAL OS</td>
                                <td style="border: 1px solid #d4dce8; padding: 14px; text-align: center; font-weight: 700; color: #3B93E3; font-size: 16px; background-color: #ffffff;">R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 40%; padding: 18px; text-align: center; vertical-align: middle; background-color: #fafbfc;">
                        <div style="font-weight: 700; color: #3B93E3; margin-bottom: 8px; font-size: 14px; letter-spacing: 0.3px;">Personalitec</div>
                        <div style="font-size: 13px; color: #3B93E3; text-decoration: none; line-height: 1.6;">atendimento@personalitec.com.br</div>
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
