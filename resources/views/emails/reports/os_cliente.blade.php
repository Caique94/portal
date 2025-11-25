<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Atendimento</title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            background-color: #f5f7f9;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(90deg, #2f88d8 0%, #4fa1e6 100%);
            color: #ffffff;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 0 0 auto;
        }
        .header-left img {
            height: 48px;
            width: auto;
        }
        .header-brand {
            font-size: 12px;
            font-weight: 600;
            line-height: 1.3;
        }
        .header-brand-sub {
            font-size: 10px;
            opacity: 0.9;
        }
        .header-title {
            flex: 1;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header-number {
            background-color: #1f5fa1;
            padding: 12px 16px;
            border-radius: 6px;
            text-align: center;
            flex: 0 0 auto;
            min-width: 100px;
        }
        .header-number-label {
            display: block;
            font-size: 11px;
            opacity: 0.9;
            margin-bottom: 4px;
        }
        .header-number-value {
            display: block;
            font-size: 18px;
            font-weight: 700;
        }
        .main {
            padding: 24px;
        }
        .info-section {
            display: block;
            margin-bottom: 24px;
        }
        .two-col {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .col-left {
            display: table-cell;
            width: 55%;
            padding-right: 20px;
            vertical-align: top;
        }
        .col-right {
            display: table-cell;
            width: 45%;
            padding-left: 20px;
            vertical-align: top;
        }
        .client-box {
            background-color: #f2f6fb;
            border: 1px solid #d4e0ed;
            border-radius: 6px;
            padding: 16px;
            line-height: 1.8;
        }
        .client-box-row {
            display: block;
            margin: 8px 0;
        }
        .client-box-label {
            font-weight: 700;
            display: inline-block;
            min-width: 100px;
            color: #2f88d8;
        }
        .times-table {
            background-color: #ffffff;
            border: 1px solid #d4e0ed;
            border-radius: 6px;
            overflow: hidden;
        }
        .times-table-header {
            display: table;
            width: 100%;
            background-color: #f7f9fc;
            border-bottom: 2px solid #d4e0ed;
        }
        .times-table-header-cell {
            display: table-cell;
            width: 20%;
            padding: 12px 8px;
            text-align: center;
            font-weight: 700;
            color: #666;
            font-size: 12px;
            border-right: 1px solid #d4e0ed;
        }
        .times-table-header-cell:last-child {
            border-right: none;
        }
        .times-table-body {
            display: table;
            width: 100%;
        }
        .times-table-row {
            display: table-cell;
            width: 20%;
            padding: 14px 8px;
            text-align: center;
            font-weight: 700;
            border-right: 1px solid #eee;
            font-size: 13px;
        }
        .times-table-row:last-child {
            border-right: none;
        }
        .section-title {
            background-color: #2f88d8;
            color: #ffffff;
            padding: 12px 16px;
            font-weight: 700;
            font-size: 14px;
            margin-top: 24px;
            margin-bottom: 0;
            border-radius: 4px 4px 0 0;
        }
        .detail-box {
            background-color: #fbfcfd;
            border: 1px solid #e8eef7;
            border-top: none;
            border-radius: 0 0 6px 6px;
            padding: 16px;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .detail-box p {
            margin: 8px 0;
        }
        .detail-box strong {
            color: #111;
        }
        .resumo-header {
            background-color: #2f88d8;
            color: #ffffff;
            padding: 12px 16px;
            font-weight: 700;
            font-size: 14px;
            border-radius: 4px;
            display: block;
            margin-bottom: 12px;
        }
        .resumo-wrap {
            background-color: #ffffff;
            border: 1px solid #d4e0ed;
            border-radius: 6px;
            overflow: hidden;
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }
        .resumo-table-wrap {
            display: table-cell;
            width: 55%;
            padding: 16px;
            vertical-align: middle;
        }
        .resumo-logo-wrap {
            display: table-cell;
            width: 45%;
            padding: 16px;
            border-left: 1px solid #d4e0ed;
            text-align: center;
            vertical-align: middle;
        }
        .resumo-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .resumo-table th {
            background-color: #f7f9fc;
            border: 1px solid #d4e0ed;
            padding: 10px;
            font-weight: 700;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .resumo-table td {
            border: 1px solid #d4e0ed;
            padding: 12px 10px;
            text-align: center;
            font-size: 12px;
        }
        .resumo-table .big {
            font-weight: 700;
            color: #2f88d8;
            font-size: 14px;
        }
        .logo-company {
            font-weight: 700;
            color: #2f88d8;
            margin-bottom: 6px;
            font-size: 13px;
        }
        .contact-info {
            font-size: 12px;
            color: #2f88d8;
            margin-top: 8px;
        }
        .footer {
            padding: 16px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 11px;
            color: #666;
            background-color: #fafbfc;
        }
        @media only screen and (max-width: 800px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            .header-title {
                font-size: 20px;
                order: 2;
                width: 100%;
                margin: 12px 0;
            }
            .header-left {
                order: 1;
            }
            .header-number {
                order: 3;
                margin-top: 12px;
            }
            .col-left,
            .col-right {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
                margin-bottom: 16px;
            }
            .resumo-table-wrap,
            .resumo-logo-wrap {
                display: block !important;
                width: 100% !important;
                padding: 16px !important;
                border: none !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 20px 0; font-family: 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f7f9;">
    <div class="container" style="max-width: 900px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <!-- HEADER -->
        <div class="header" style="background: linear-gradient(90deg, #2f88d8 0%, #4fa1e6 100%); color: #ffffff; padding: 20px; display: flex; align-items: center; justify-content: space-between;">
            <div class="header-left" style="display: flex; align-items: center; gap: 12px; flex: 0 0 auto;">
                <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec" style="height: 48px; width: auto;">
                <div class="header-brand" style="font-size: 12px; font-weight: 600; line-height: 1.3;">
                    Personalitec<br>
                    <span class="header-brand-sub" style="font-size: 10px; opacity: 0.9;">Sua visão, nossa tecnologia</span>
                </div>
            </div>
            <div class="header-title" style="flex: 1; text-align: center; font-size: 28px; font-weight: 700; letter-spacing: 1px;">
                ORDEM DE ATENDIMENTO
            </div>
            <div class="header-number" style="background-color: #1f5fa1; padding: 12px 16px; border-radius: 6px; text-align: center; flex: 0 0 auto; min-width: 100px;">
                <span class="header-number-label" style="display: block; font-size: 11px; opacity: 0.9; margin-bottom: 4px;">NÚMERO</span>
                <span class="header-number-value" style="display: block; font-size: 18px; font-weight: 700;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main" style="padding: 24px;">
            <!-- INFO SECTION -->
            <table class="two-col" style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                <tr>
                    <td class="col-left" style="width: 55%; padding-right: 20px; vertical-align: top;">
                        <div class="client-box" style="background-color: #f2f6fb; border: 1px solid #d4e0ed; border-radius: 6px; padding: 16px; line-height: 1.8;">
                            <div class="client-box-row" style="margin: 8px 0;">
                                <span class="client-box-label" style="font-weight: 700; color: #2f88d8;">Cliente:</span>
                                <span>{{ $os->cliente->nome }}</span>
                            </div>
                            <div class="client-box-row" style="margin: 8px 0;">
                                <span class="client-box-label" style="font-weight: 700; color: #2f88d8;">Contato:</span>
                                <span>{{ $os->cliente->contato ?? '-' }}</span>
                            </div>
                            <div class="client-box-row" style="margin: 8px 0;">
                                <span class="client-box-label" style="font-weight: 700; color: #2f88d8;">Emissão:</span>
                                <span>{{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}</span>
                            </div>
                            <div class="client-box-row" style="margin: 8px 0;">
                                <span class="client-box-label" style="font-weight: 700; color: #2f88d8;">Consultor:</span>
                                <span>{{ $os->consultor->name }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="col-right" style="width: 45%; padding-left: 20px; vertical-align: top;">
                        <table class="times-table" style="width: 100%; border: 1px solid #d4e0ed; border-radius: 6px; overflow: hidden;">
                            <tr style="background-color: #f7f9fc; border-bottom: 2px solid #d4e0ed;">
                                <th style="width: 20%; padding: 12px 4px; text-align: center; font-weight: 700; color: #666; font-size: 11px; border-right: 1px solid #d4e0ed;">HORA<br>INICIO</th>
                                <th style="width: 20%; padding: 12px 4px; text-align: center; font-weight: 700; color: #666; font-size: 11px; border-right: 1px solid #d4e0ed;">HORA<br>FIM</th>
                                <th style="width: 20%; padding: 12px 4px; text-align: center; font-weight: 700; color: #666; font-size: 11px; border-right: 1px solid #d4e0ed;">DESCONTO</th>
                                <th style="width: 20%; padding: 12px 4px; text-align: center; font-weight: 700; color: #666; font-size: 11px; border-right: 1px solid #d4e0ed;">TRASLADO</th>
                                <th style="width: 20%; padding: 12px 4px; text-align: center; font-weight: 700; color: #666; font-size: 11px;">TOTAL<br>HORAS</th>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="width: 20%; padding: 14px 4px; text-align: center; font-weight: 700; font-size: 13px; border-right: 1px solid #eee;">--:--</td>
                                <td style="width: 20%; padding: 14px 4px; text-align: center; font-weight: 700; font-size: 13px; border-right: 1px solid #eee;">--:--</td>
                                <td style="width: 20%; padding: 14px 4px; text-align: center; font-weight: 700; font-size: 13px; border-right: 1px solid #eee;">0:00</td>
                                <td style="width: 20%; padding: 14px 4px; text-align: center; font-weight: 700; font-size: 13px; border-right: 1px solid #eee;">{{ number_format($totalizador['deslocamento'] ?? 0, 2, ',', '.') }}</td>
                                <td style="width: 20%; padding: 14px 4px; text-align: center; font-weight: 700; font-size: 13px;">{{ number_format($totalizador['horas'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- DETALHAMENTO -->
            <div style="margin-bottom: 24px;">
                <div class="section-title" style="background-color: #2f88d8; color: #ffffff; padding: 12px 16px; font-weight: 700; font-size: 14px; border-radius: 4px 4px 0 0;">DETALHAMENTO</div>
                <div class="detail-box" style="background-color: #fbfcfd; border: 1px solid #e8eef7; border-top: none; border-radius: 0 0 6px 6px; padding: 16px;">
                    <p style="margin: 8px 0;"><strong style="color: #111;">Assunto:</strong> {{ $os->assunto ?? '-' }}</p>
                    <p style="margin: 8px 0;"><strong style="color: #111;">Observações:</strong> {{ $os->observacao ?? 'Nenhuma observação adicionada' }}</p>
                </div>
            </div>

            <!-- RESUMO -->
            <div class="resumo-header" style="background-color: #2f88d8; color: #ffffff; padding: 12px 16px; font-weight: 700; font-size: 14px; border-radius: 4px; display: block; margin-bottom: 12px;">RESUMO</div>
            <table class="resumo-wrap" style="width: 100%; border: 1px solid #d4e0ed; border-radius: 6px; margin-bottom: 24px; border-collapse: collapse;">
                <tr>
                    <td class="resumo-table-wrap" style="width: 55%; padding: 16px; vertical-align: middle;">
                        <table class="resumo-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <tr>
                                <th style="background-color: #f7f9fc; border: 1px solid #d4e0ed; padding: 10px; font-weight: 700; text-align: center; color: #666; font-size: 12px;">Chamado Personalitec</th>
                                <th style="background-color: #f7f9fc; border: 1px solid #d4e0ed; padding: 10px; font-weight: 700; text-align: center; color: #666; font-size: 12px;">{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</th>
                                <th style="background-color: #f7f9fc; border: 1px solid #d4e0ed; padding: 10px; font-weight: 700; text-align: center; color: #666; font-size: 12px;">Previsão Retorno</th>
                                <th style="background-color: #f7f9fc; border: 1px solid #d4e0ed; padding: 10px; font-weight: 700; text-align: center; color: #666; font-size: 12px;">{{ \Carbon\Carbon::parse($os->data_emissao)->addDays(1)->format('d/m/Y') }}</th>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #d4e0ed; padding: 12px 10px; text-align: center; font-size: 12px;">KM</td>
                                <td style="border: 1px solid #d4e0ed; padding: 12px 10px; text-align: center; font-size: 12px;">{{ $os->km ?? '0' }}</td>
                                <td style="border: 1px solid #d4e0ed; padding: 12px 10px; text-align: center; font-weight: 700; color: #2f88d8; font-size: 14px;">TOTAL OS</td>
                                <td style="border: 1px solid #d4e0ed; padding: 12px 10px; text-align: center; font-weight: 700; color: #2f88d8; font-size: 14px;">R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="resumo-logo-wrap" style="width: 45%; padding: 16px; border-left: 1px solid #d4e0ed; text-align: center; vertical-align: middle;">
                        <div class="logo-company" style="font-weight: 700; color: #2f88d8; margin-bottom: 6px; font-size: 13px;">Personalitec</div>
                        <div class="contact-info" style="font-size: 12px; color: #2f88d8; margin-top: 8px;">atendimento@personalitec.com.br</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- FOOTER -->
        <div class="footer" style="padding: 16px; border-top: 1px solid #eee; text-align: center; font-size: 11px; color: #666; background-color: #fafbfc;">
            <p style="margin: 4px 0;">Este é um e-mail automático. Por favor, não responda.</p>
            <p style="margin: 4px 0;">© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
