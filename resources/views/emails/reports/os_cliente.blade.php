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
        <div style="background: linear-gradient(90deg, #2f88d8 0%, #4fa1e6 100%); color: #ffffff; padding: 20px 25px; display: flex; align-items: center; justify-content: space-between; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 0 0 auto;">
                <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec" style="height: 50px; width: auto; display: block;">
                <div style="font-size: 12px; font-weight: 500; line-height: 1.3;">
                    Personalitec<br>
                    <span style="font-size: 10px; opacity: 0.85; font-weight: 400;">Sua visão, nossa tecnologia</span>
                </div>
            </div>
            <div style="flex: 1; text-align: center; font-size: 28px; font-weight: 700; letter-spacing: 1.5px;">
                ORDEM DE ATENDIMENTO
            </div>
            <div style="background-color: rgba(31, 95, 161, 0.95); padding: 12px 18px; border-radius: 6px; text-align: center;">
                <div style="font-size: 9px; opacity: 0.8; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600;">Número</div>
                <div style="font-size: 18px; font-weight: 700; letter-spacing: 0.5px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div style="padding: 25px;">
            <!-- INFO SECTION - TWO COLUMNS -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                <tr>
                    <td style="width: 50%; padding-right: 20px; vertical-align: top;">
                        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px;">
                            <div style="margin: 8px 0; line-height: 1.5; font-size: 13px;">
                                <span style="font-weight: 700; color: #111; display: inline-block; min-width: 80px;">Cliente:</span>
                                <span style="color: #374151;">{{ $os->cliente->nome }}</span>
                            </div>
                            <div style="margin: 8px 0; line-height: 1.5; font-size: 13px;">
                                <span style="font-weight: 700; color: #111; display: inline-block; min-width: 80px;">Contato:</span>
                                <span style="color: #374151;">{{ $os->cliente->contato ?? '-' }}</span>
                            </div>
                            <div style="margin: 8px 0; line-height: 1.5; font-size: 13px;">
                                <span style="font-weight: 700; color: #111; display: inline-block; min-width: 80px;">Emissão:</span>
                                <span style="color: #374151;">{{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}</span>
                            </div>
                            <div style="margin: 8px 0; line-height: 1.5; font-size: 13px;">
                                <span style="font-weight: 700; color: #111; display: inline-block; min-width: 80px;">Consultor:</span>
                                <span style="color: #374151;">{{ $os->consultor->name }}</span>
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 20px; vertical-align: top;">
                        <table style="width: 100%; border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden; border-collapse: collapse;">
                            <tr style="background-color: #f3f4f6;">
                                <th style="padding: 10px 6px; text-align: center; font-weight: 600; color: #4b5563; font-size: 11px; border-right: 1px solid #e5e7eb; letter-spacing: 0.3px;">HORA<br>INICIO</th>
                                <th style="padding: 10px 6px; text-align: center; font-weight: 600; color: #4b5563; font-size: 11px; border-right: 1px solid #e5e7eb; letter-spacing: 0.3px;">HORA<br>FIM</th>
                                <th style="padding: 10px 6px; text-align: center; font-weight: 600; color: #4b5563; font-size: 11px; border-right: 1px solid #e5e7eb; letter-spacing: 0.3px;">DESCONTO</th>
                                <th style="padding: 10px 6px; text-align: center; font-weight: 600; color: #4b5563; font-size: 11px; border-right: 1px solid #e5e7eb; letter-spacing: 0.3px;">TRASLADO</th>
                                <th style="padding: 10px 6px; text-align: center; font-weight: 600; color: #4b5563; font-size: 11px; letter-spacing: 0.3px;">TOTAL<br>HORAS</th>
                            </tr>
                            <tr style="background-color: #ffffff;">
                                <td style="padding: 11px 6px; text-align: center; font-weight: 600; font-size: 12px; color: #1f2937; border-right: 1px solid #f3f4f6;">--:--</td>
                                <td style="padding: 11px 6px; text-align: center; font-weight: 600; font-size: 12px; color: #1f2937; border-right: 1px solid #f3f4f6;">--:--</td>
                                <td style="padding: 11px 6px; text-align: center; font-weight: 600; font-size: 12px; color: #1f2937; border-right: 1px solid #f3f4f6;">0:00</td>
                                <td style="padding: 11px 6px; text-align: center; font-weight: 600; font-size: 12px; color: #1f2937; border-right: 1px solid #f3f4f6;">{{ number_format($totalizador['deslocamento'] ?? 0, 2, ',', '.') }}</td>
                                <td style="padding: 11px 6px; text-align: center; font-weight: 700; font-size: 12px; color: #2563eb;">{{ number_format($totalizador['horas'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- DETALHAMENTO -->
            <div style="background-color: #2563eb; color: #ffffff; padding: 12px 16px; font-weight: 600; font-size: 13px; margin-bottom: 0; border-radius: 6px 6px 0 0; letter-spacing: 0.5px;">DETALHAMENTO</div>
            <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 6px 6px; padding: 16px; margin-bottom: 25px; line-height: 1.6;">
                <p style="margin: 8px 0; font-size: 13px; color: #374151;"><strong style="color: #111; font-weight: 600;">Assunto:</strong> {{ $os->assunto ?? '-' }}</p>
                <p style="margin: 8px 0; font-size: 13px; color: #374151;"><strong style="color: #111; font-weight: 600;">Observações:</strong> {{ $os->observacao ?? 'Nenhuma observação adicionada' }}</p>
            </div>

            <!-- RESUMO -->
            <div style="background-color: #2563eb; color: #ffffff; padding: 12px 16px; font-weight: 600; font-size: 13px; border-radius: 6px; display: inline-block; margin-bottom: 12px; letter-spacing: 0.5px;">RESUMO</div>
            <table style="width: 100%; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 25px; border-collapse: collapse; overflow: hidden;">
                <tr>
                    <td style="width: 65%; padding: 14px; vertical-align: middle; border-right: 1px solid #e5e7eb; background-color: #ffffff;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <tr>
                                <th style="background-color: #f3f4f6; border: 1px solid #e5e7eb; padding: 9px; font-weight: 600; text-align: center; color: #4b5563; font-size: 11px; letter-spacing: 0.3px;">Chamado Personalitec</th>
                                <th style="background-color: #f3f4f6; border: 1px solid #e5e7eb; padding: 9px; font-weight: 600; text-align: center; color: #4b5563; font-size: 11px; letter-spacing: 0.3px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</th>
                                <th style="background-color: #f3f4f6; border: 1px solid #e5e7eb; padding: 9px; font-weight: 600; text-align: center; color: #4b5563; font-size: 11px; letter-spacing: 0.3px;">Previsão Retorno</th>
                                <th style="background-color: #f3f4f6; border: 1px solid #e5e7eb; padding: 9px; font-weight: 600; text-align: center; color: #4b5563; font-size: 11px; letter-spacing: 0.3px;">{{ \Carbon\Carbon::parse($os->data_emissao)->addDays(1)->format('d/m/Y') }}</th>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #e5e7eb; padding: 10px; text-align: center; font-size: 12px; color: #374151; background-color: #ffffff;">KM</td>
                                <td style="border: 1px solid #e5e7eb; padding: 10px; text-align: center; font-size: 12px; color: #374151; background-color: #ffffff;">{{ $os->km ?? '0' }}</td>
                                <td style="border: 1px solid #e5e7eb; padding: 10px; text-align: center; font-weight: 600; color: #2563eb; font-size: 12px; background-color: #ffffff;">TOTAL OS</td>
                                <td style="border: 1px solid #e5e7eb; padding: 10px; text-align: center; font-weight: 700; color: #2563eb; font-size: 12px; background-color: #ffffff;">R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 35%; padding: 14px; text-align: center; vertical-align: middle; background-color: #f9fafb;">
                        <div style="font-weight: 600; color: #2563eb; margin-bottom: 4px; font-size: 12px; letter-spacing: 0.3px;">Personalitec</div>
                        <div style="font-size: 11px; color: #2563eb; text-decoration: none; line-height: 1.4;">atendimento@personalitec.com.br</div>
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
