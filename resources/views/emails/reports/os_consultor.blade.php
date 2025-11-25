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
        <div style="background: linear-gradient(90deg, #2f88d8 0%, #4fa1e6 100%); color: #ffffff; padding: 35px 30px; display: flex; align-items: center; justify-content: space-between; gap: 30px;">
            <div style="display: flex; align-items: center; gap: 18px; flex: 0 0 auto; min-width: 200px;">
                <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec" style="height: 65px; width: auto; display: block;">
                <div style="font-size: 13px; font-weight: 500; line-height: 1.5; letter-spacing: 0.3px;">
                    Personalitec<br>
                    <span style="font-size: 11px; opacity: 0.85; font-weight: 400;">Sua visão, nossa tecnologia</span>
                </div>
            </div>
            <div style="flex: 1; text-align: center; font-size: 36px; font-weight: 700; letter-spacing: 2.5px;">
                ORDEM DE ATENDIMENTO
            </div>
            <div style="background-color: rgba(31, 95, 161, 0.9); padding: 18px 24px; border-radius: 8px; text-align: center; flex: 0 0 auto; min-width: 130px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                <div style="font-size: 10px; opacity: 0.85; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600;">Número</div>
                <div style="font-size: 22px; font-weight: 700; letter-spacing: 1px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div style="padding: 35px;">
            <!-- INFO SECTION - TWO COLUMNS -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 35px;">
                <tr>
                    <td style="width: 50%; padding-right: 28px; vertical-align: top;">
                        <div style="background-color: #ffffff; border: 1px solid #dce4ef; border-radius: 10px; padding: 24px; box-shadow: 0 1px 3px rgba(47, 136, 216, 0.06);">
                            <div style="margin: 12px 0; line-height: 1.7; font-size: 14px;">
                                <span style="font-weight: 600; color: #111; display: inline-block; min-width: 90px;">Cliente:</span>
                                <span style="color: #333;">{{ $os->cliente->nome }}</span>
                            </div>
                            <div style="margin: 12px 0; line-height: 1.7; font-size: 14px;">
                                <span style="font-weight: 600; color: #111; display: inline-block; min-width: 90px;">Contato:</span>
                                <span style="color: #333;">{{ $os->cliente->contato ?? '-' }}</span>
                            </div>
                            <div style="margin: 12px 0; line-height: 1.7; font-size: 14px;">
                                <span style="font-weight: 600; color: #111; display: inline-block; min-width: 90px;">Emissão:</span>
                                <span style="color: #333;">{{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}</span>
                            </div>
                            <div style="margin: 12px 0; line-height: 1.7; font-size: 14px;">
                                <span style="font-weight: 600; color: #111; display: inline-block; min-width: 90px;">Consultor:</span>
                                <span style="color: #333;">{{ $os->consultor->name }}</span>
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 28px; vertical-align: top;">
                        <table style="width: 100%; border: 1px solid #dce4ef; border-radius: 10px; overflow: hidden; border-collapse: collapse; box-shadow: 0 1px 3px rgba(47, 136, 216, 0.06);">
                            <tr style="background: linear-gradient(180deg, #f7f9fc 0%, #f1f5fb 100%);">
                                <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #555; font-size: 12px; border-right: 1px solid #dce4ef; letter-spacing: 0.4px;">HORA<br>INÍCIO</th>
                                <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #555; font-size: 12px; border-right: 1px solid #dce4ef; letter-spacing: 0.4px;">HORA<br>FIM</th>
                                <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #555; font-size: 12px; border-right: 1px solid #dce4ef; letter-spacing: 0.4px;">DESCONTO</th>
                                <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #555; font-size: 12px; border-right: 1px solid #dce4ef; letter-spacing: 0.4px;">TRASLADO</th>
                                <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #555; font-size: 12px; letter-spacing: 0.4px;">TOTAL<br>HORAS</th>
                            </tr>
                            <tr style="background-color: #ffffff;">
                                <td style="padding: 16px 8px; text-align: center; font-weight: 600; font-size: 14px; color: #222; border-right: 1px solid #f0f2f7;">--:--</td>
                                <td style="padding: 16px 8px; text-align: center; font-weight: 600; font-size: 14px; color: #222; border-right: 1px solid #f0f2f7;">--:--</td>
                                <td style="padding: 16px 8px; text-align: center; font-weight: 600; font-size: 14px; color: #222; border-right: 1px solid #f0f2f7;">0:00</td>
                                <td style="padding: 16px 8px; text-align: center; font-weight: 600; font-size: 14px; color: #222; border-right: 1px solid #f0f2f7;">{{ number_format($totalizador['deslocamento'] ?? 0, 2, ',', '.') }}</td>
                                <td style="padding: 16px 8px; text-align: center; font-weight: 700; font-size: 14px; color: #2f88d8;">{{ number_format($totalizador['horas'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- DETALHAMENTO -->
            <div style="background: linear-gradient(90deg, #2f88d8 0%, #3a94e3 100%); color: #ffffff; padding: 16px 20px; font-weight: 600; font-size: 14px; margin-bottom: 0; border-radius: 10px 10px 0 0; letter-spacing: 1px;">DETALHAMENTO</div>
            <div style="background-color: #fafbfd; border: 1px solid #dce4ef; border-top: none; border-radius: 0 0 10px 10px; padding: 22px; margin-bottom: 35px; line-height: 1.8; box-shadow: 0 1px 3px rgba(47, 136, 216, 0.06);">
                <p style="margin: 12px 0; font-size: 14px; color: #333;"><strong style="color: #111; font-weight: 600;">Assunto:</strong> <span style="margin-left: 8px;">{{ $os->assunto ?? '-' }}</span></p>
                <p style="margin: 12px 0; font-size: 14px; color: #333;"><strong style="color: #111; font-weight: 600;">Observações:</strong> <span style="margin-left: 8px;">{{ $os->observacao ?? 'Nenhuma observação adicionada' }}</span></p>
            </div>

            <!-- RESUMO -->
            <div style="background: linear-gradient(90deg, #2f88d8 0%, #3a94e3 100%); color: #ffffff; padding: 16px 20px; font-weight: 600; font-size: 14px; border-radius: 10px; display: inline-block; margin-bottom: 16px; letter-spacing: 1px;">RESUMO</div>
            <table style="width: 100%; border: 1px solid #dce4ef; border-radius: 10px; margin-bottom: 35px; border-collapse: collapse; overflow: hidden; box-shadow: 0 1px 3px rgba(47, 136, 216, 0.06);">
                <tr>
                    <td style="width: 65%; padding: 20px; vertical-align: middle; border-right: 1px solid #dce4ef; background-color: #ffffff;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <tr>
                                <th style="background: linear-gradient(180deg, #f7f9fc 0%, #f1f5fb 100%); border: 1px solid #dce4ef; padding: 12px; font-weight: 600; text-align: center; color: #555; font-size: 12px; letter-spacing: 0.4px;">Chamado Personalitec</th>
                                <th style="background: linear-gradient(180deg, #f7f9fc 0%, #f1f5fb 100%); border: 1px solid #dce4ef; padding: 12px; font-weight: 600; text-align: center; color: #555; font-size: 12px; letter-spacing: 0.4px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</th>
                                <th style="background: linear-gradient(180deg, #f7f9fc 0%, #f1f5fb 100%); border: 1px solid #dce4ef; padding: 12px; font-weight: 600; text-align: center; color: #555; font-size: 12px; letter-spacing: 0.4px;">Previsão Retorno</th>
                                <th style="background: linear-gradient(180deg, #f7f9fc 0%, #f1f5fb 100%); border: 1px solid #dce4ef; padding: 12px; font-weight: 600; text-align: center; color: #555; font-size: 12px; letter-spacing: 0.4px;">{{ \Carbon\Carbon::parse($os->data_emissao)->addDays(1)->format('d/m/Y') }}</th>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #dce4ef; padding: 14px; text-align: center; font-size: 13px; color: #333; background-color: #fafbfd;">KM</td>
                                <td style="border: 1px solid #dce4ef; padding: 14px; text-align: center; font-size: 13px; color: #333; background-color: #fafbfd;">{{ $os->km ?? '0' }}</td>
                                <td style="border: 1px solid #dce4ef; padding: 14px; text-align: center; font-weight: 600; color: #2f88d8; font-size: 13px; background-color: #fafbfd;">TOTAL OS</td>
                                <td style="border: 1px solid #dce4ef; padding: 14px; text-align: center; font-weight: 700; color: #2f88d8; font-size: 14px; background-color: #fafbfd;">R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 35%; padding: 20px; text-align: center; vertical-align: middle; background: linear-gradient(135deg, #fafbfd 0%, #f5f8fc 100%);">
                        <div style="font-weight: 600; color: #2f88d8; margin-bottom: 8px; font-size: 14px; letter-spacing: 0.5px;">Personalitec</div>
                        <div style="font-size: 12px; color: #2f88d8; text-decoration: none; line-height: 1.6;">atendimento@personalitec.com.br</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- FOOTER -->
        <div style="padding: 22px; border-top: 1px solid #e8ecf3; text-align: center; font-size: 12px; color: #777; background-color: #fafbfd;">
            <p style="margin: 6px 0; line-height: 1.6;">Este é um e-mail automático. Por favor, não responda.</p>
            <p style="margin: 6px 0; line-height: 1.6;">© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
