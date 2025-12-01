<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ordem de Serviço #{{ $ordemServico->id }}</title>
  <style type="text/css">
    body { margin: 0; padding: 20px; font-family: Arial, sans-serif; background-color: #f5f5f5; }
    .container { max-width: 980px; margin: 0 auto; background-color: #ffffff; border-collapse: collapse; }
    .header { background: linear-gradient(135deg, #2E7DA8 0%, #5B9FBF 100%); padding: 20px; color: white; text-align: left; }
    .header-content { display: flex; justify-content: space-between; align-items: center; }
    .logo-section { flex: 1; }
    .logo-section img { height: 50px; margin-right: 15px; }
    .logo-text { display: inline-block; font-size: 13px; font-weight: bold; }
    .logo-text small { display: block; font-size: 10px; opacity: 0.95; }
    .title { flex: 2; text-align: center; font-size: 28px; font-weight: bold; letter-spacing: 1px; }
    .numero-box { background-color: #0A5FA6; padding: 12px 18px; border-radius: 6px; min-width: 100px; text-align: center; }
    .numero-label { font-size: 10px; font-weight: bold; letter-spacing: 0.5px; opacity: 0.9; }
    .numero-valor { font-size: 18px; font-weight: bold; margin-top: 5px; }

    .main-content { padding: 25px 20px; }
    .row-2col { display: flex; gap: 20px; margin-bottom: 25px; }
    .col { flex: 1; }

    .client-box { background-color: #F5F8FA; padding: 16px; border: 1px solid #E0E8F0; border-radius: 6px; }
    .client-row { display: flex; margin: 10px 0; font-size: 13px; line-height: 1.5; }
    .client-label { font-weight: bold; color: #0A5FA6; min-width: 100px; }
    .client-value { color: #1F3A56; flex: 1; }

    .times-box { background-color: #F5F8FA; border: 1px solid #E0E8F0; border-radius: 6px; overflow: hidden; }
    .times-header { background-color: #2E7DA8; color: white; display: flex; text-align: center; font-weight: bold; font-size: 11px; }
    .times-header div { flex: 1; padding: 12px 5px; border-right: 1px solid #5B9FBF; }
    .times-header div:last-child { border-right: none; }
    .times-body { display: flex; text-align: center; padding: 12px 5px; font-weight: 600; font-size: 13px; color: #1F3A56; }
    .times-body div { flex: 1; border-right: 1px solid #E0E8F0; padding: 5px; }
    .times-body div:last-child { border-right: none; }

    .section-title { background: linear-gradient(90deg, #2E7DA8 0%, #5B9FBF 100%); color: white; padding: 12px 16px; border-radius: 6px; font-weight: bold; font-size: 13px; margin: 20px 0 10px 0; text-align: center; letter-spacing: 0.5px; }
    .detail-box { background-color: #F5F8FA; padding: 16px; border: 1px solid #E0E8F0; border-radius: 6px; line-height: 1.6; color: #1F3A56; font-size: 13px; text-align: justify; }

    .summary-box { background-color: #F5F8FA; border: 1px solid #E0E8F0; border-radius: 6px; margin-top: 20px; overflow: hidden; }
    .summary-title { background: linear-gradient(90deg, #2E7DA8 0%, #5B9FBF 100%); color: white; padding: 12px 16px; font-weight: bold; text-align: center; font-size: 13px; letter-spacing: 0.5px; }
    .summary-content { display: flex; padding: 16px; gap: 20px; }
    .summary-table { flex: 1; }
    .summary-table table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .summary-table th { background-color: white; color: #2E7DA8; padding: 10px 8px; border: 1px solid #E0E8F0; font-weight: bold; text-align: center; }
    .summary-table td { padding: 10px 8px; border: 1px solid #E0E8F0; text-align: center; color: #1F3A56; }
    .summary-table .big-value { font-weight: 800; color: #0A5FA6; font-size: 14px; }
    .summary-logo { width: 200px; text-align: center; display: flex; align-items: center; justify-content: center; }
    .summary-logo img { max-width: 180px; height: auto; }

    @media (max-width: 768px) {
      .header-content { flex-wrap: wrap; }
      .title { font-size: 20px; }
      .row-2col { flex-direction: column; gap: 16px; }
      .summary-content { flex-direction: column; }
      .summary-logo { width: 100%; margin-top: 10px; }
    }

    @media (max-width: 480px) {
      .logo-text { font-size: 11px; }
      .title { font-size: 16px; }
      .numero-box { padding: 10px 14px; min-width: 80px; }
      .times-header, .times-body { font-size: 11px; }
      .main-content { padding: 15px 12px; }
    }
  </style>
</head>
<body>
<table class="container" width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td>
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: linear-gradient(135deg, #2E7DA8 0%, #5B9FBF 100%); color: white; padding: 20px;">
        <tr>
          <td style="width: 60px;">
            <img src="https://static.wixstatic.com/media/c4d4c1_6fa078f57383404faf7ceb1d9533f4fb~mv2.png/v1/fill/w_472,h_228,al_c,lg_1,q_85,enc_avif,quality_auto/Logo-Personalitec-Site.png" alt="Personalitec" style="height: 50px; width: auto; vertical-align: middle;">
          </td>
          <td style="flex: 1; text-align: center;">
            <h2 style="font-size: 28px; font-weight: bold; margin: 0; letter-spacing: 1px;">ORDEM DE ATENDIMENTO</h2>
          </td>
          <td style="width: 150px; text-align: right;">
            <table cellpadding="0" cellspacing="0" border="0" style="background-color: #0A5FA6; border-radius: 6px; padding: 12px 18px; margin-left: auto; min-width: 100px;">
              <tr>
                <td style="text-align: center; font-size: 10px; font-weight: bold; letter-spacing: 0.5px; opacity: 0.9; display: block;">NUMERO</td>
              </tr>
              <tr>
                <td style="text-align: center; font-size: 18px; font-weight: bold; margin-top: 5px;">{{ $ordemServico->id }}</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td style="padding: 25px 20px;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="50%" style="padding-right: 10px; vertical-align: top;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F5F8FA; border: 1px solid #E0E8F0; border-radius: 6px; padding: 16px;">
              <tr>
                <td style="font-weight: bold; color: #0A5FA6; width: 100px; padding: 8px 0;">Cliente:</td>
                <td style="color: #1F3A56; padding: 8px 0;">
                  @if($ordemServico->cliente)
                    {{ $ordemServico->cliente->name ?? 'N/A' }}
                  @else
                    N/A
                  @endif
                </td>
              </tr>
              <tr>
                <td style="font-weight: bold; color: #0A5FA6; width: 100px; padding: 8px 0;">Contato:</td>
                <td style="color: #1F3A56; padding: 8px 0;">
                  @if($ordemServico->cliente)
                    {{ $ordemServico->cliente->email ?? $ordemServico->cliente->contato ?? 'N/A' }}
                  @else
                    N/A
                  @endif
                </td>
              </tr>
              <tr>
                <td style="font-weight: bold; color: #0A5FA6; width: 100px; padding: 8px 0;">Emissão:</td>
                <td style="color: #1F3A56; padding: 8px 0;">{{ \Carbon\Carbon::parse($ordemServico->data_emissao)->format('d/m/Y') }}</td>
              </tr>
              <tr>
                <td style="font-weight: bold; color: #0A5FA6; width: 100px; padding: 8px 0;">Consultor:</td>
                <td style="color: #1F3A56; padding: 8px 0;">{{ $ordemServico->consultor->name ?? 'N/A' }}</td>
              </tr>
            </table>
          </td>

          <td width="50%" style="padding-left: 10px; vertical-align: top;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F5F8FA; border: 1px solid #E0E8F0; border-radius: 6px; overflow: hidden;">
              <tr style="background-color: #2E7DA8; color: white; font-weight: bold; font-size: 11px;">
                <td style="padding: 12px 5px; border-right: 1px solid #5B9FBF; text-align: center;">HORA INICIO</td>
                <td style="padding: 12px 5px; border-right: 1px solid #5B9FBF; text-align: center;">HORA FIM</td>
                <td style="padding: 12px 5px; border-right: 1px solid #5B9FBF; text-align: center;">DESPESA</td>
                <td style="padding: 12px 5px; border-right: 1px solid #5B9FBF; text-align: center;">TRASLADO</td>
                <td style="padding: 12px 5px; text-align: center;">TOTAL HORAS</td>
              </tr>
              <tr style="font-weight: 600; font-size: 13px; color: #1F3A56;">
                <td style="padding: 10px 5px; border-right: 1px solid #E0E8F0; text-align: center;">{{ $ordemServico->hora_inicio ?? '00:00' }}</td>
                <td style="padding: 10px 5px; border-right: 1px solid #E0E8F0; text-align: center;">{{ $ordemServico->hora_final ?? '00:00' }}</td>
                <td style="padding: 10px 5px; border-right: 1px solid #E0E8F0; text-align: center;">{{ $ordemServico->valor_despesa ? 'R$ ' . number_format($ordemServico->valor_despesa, 2, ',', '.') : '--' }}</td>
                <td style="padding: 10px 5px; border-right: 1px solid #E0E8F0; text-align: center;">{{ $ordemServico->deslocamento ? 'R$ ' . number_format($ordemServico->deslocamento, 2, ',', '.') : '--' }}</td>
                <td style="padding: 10px 5px; text-align: center;">
                  @php
                    // Calcula total de horas: (hora_fim - hora_inicio - hora_desconto)
                    $total_horas = 0;
                    if ($ordemServico->hora_inicio && $ordemServico->hora_final) {
                      $inicio = \Carbon\Carbon::createFromFormat('H:i', $ordemServico->hora_inicio);
                      $fim = \Carbon\Carbon::createFromFormat('H:i', $ordemServico->hora_final);
                      $total_minutos = $fim->diffInMinutes($inicio);

                      // Subtrair desconto se existir
                      if ($ordemServico->hora_desconto) {
                        list($desc_h, $desc_m) = explode(':', $ordemServico->hora_desconto);
                        $desconto_minutos = intval($desc_h) * 60 + intval($desc_m);
                        $total_minutos -= $desconto_minutos;
                      }

                      // Converter para horas decimais (máximo 0 se resultado negativo)
                      $total_horas = max(0, round($total_minutos / 60, 2));
                    }
                  @endphp
                  {{ number_format($total_horas, 2, '.', '') }}
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td style="padding: 0 20px;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: linear-gradient(90deg, #2E7DA8 0%, #5B9FBF 100%); color: white; padding: 12px 16px; border-radius: 6px; font-weight: bold; font-size: 13px; margin: 20px 0 10px 0; text-align: center; letter-spacing: 0.5px;">
        <tr>
          <td>DETALHAMENTO</td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td style="padding: 0 20px 20px 20px;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F5F8FA; padding: 16px; border: 1px solid #E0E8F0; border-radius: 6px; line-height: 1.6; color: #1F3A56; font-size: 13px;">
        <tr>
          <td>{!! nl2br($ordemServico->detalhamento ?? 'Nenhum detalhamento fornecido.') !!}</td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td style="padding: 0 20px;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F5F8FA; border: 1px solid #E0E8F0; border-radius: 6px; margin-top: 20px; overflow: hidden;">
        <tr>
          <td style="background: linear-gradient(90deg, #2E7DA8 0%, #5B9FBF 100%); color: white; padding: 12px 16px; font-weight: bold; text-align: center; font-size: 13px; letter-spacing: 0.5px;">RESUMO</td>
        </tr>
        <tr>
          <td style="padding: 16px;">
            <!-- 4-Column Summary Table - 2 rows -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 15px; background-color: #F2F2F2;">
              <!-- Row 1 -->
              <tr>
                <!-- Column 1: Chamado Personalitec (2-line label) -->
                <td style="padding: 16px 12px; border: 1px solid #DEDEDE; text-align: center; color: #555; font-size: 11px; font-weight: 500; width: 25%;">
                  Chamado<br>Personalitec
                </td>
                <!-- Column 2: Chamado Value -->
                <td style="padding: 16px 12px; border: 1px solid #DEDEDE; text-align: center; color: #1F3A56; font-size: 15px; font-weight: 600; width: 25%;">
                  {{ $ordemServico->nr_atendimento ?? $ordemServico->id }}
                </td>
                <!-- Column 3: Previsão Retorno (2-line label) -->
                <td style="padding: 16px 12px; border: 1px solid #DEDEDE; text-align: center; color: #555; font-size: 11px; font-weight: 500; width: 25%;">
                  Previsão<br>Retorno
                </td>
                <!-- Column 4: Date Value -->
                <td style="padding: 16px 12px; border: 1px solid #DEDEDE; text-align: center; color: #1F3A56; font-size: 14px; width: 25%;">
                  {{ $ordemServico->updated_at ? \Carbon\Carbon::parse($ordemServico->updated_at)->addDay()->format('d/m/Y') : '--' }}
                </td>
              </tr>
              <!-- Row 2 -->
              <tr>
                <!-- Column 1: KM Label -->
                <td style="padding: 16px 12px; border: 1px solid #DEDEDE; text-align: center; color: #555; font-size: 11px; font-weight: 500; width: 25%;">
                  KM
                </td>
                <!-- Column 2: KM Value -->
                <td style="padding: 16px 12px; border: 1px solid #DEDEDE; text-align: center; color: #1F3A56; font-size: 14px; width: 25%;">
                  {{ $ordemServico->km ?? '--' }}
                </td>
                <!-- Column 3: TOTAL OS Label -->
                <td style="padding: 16px 12px; border: 1px solid #DEDEDE; text-align: center; color: #555; font-size: 11px; font-weight: 700; width: 25%;">
                  TOTAL OS
                </td>
                <!-- Column 4: TOTAL OS Value -->
                <td style="padding: 16px 12px; border: 1px solid #DEDEDE; text-align: center; color: #0A5FA6; font-size: 14px; font-weight: 700;">
                  {{ $ordemServico->valor_total ? 'R$ ' . number_format($ordemServico->valor_total, 2, ',', '.') : '--' }}
                </td>
              </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: linear-gradient(135deg, #2E7DA8 0%, #5B9FBF 100%);">
              <tr>
                <td width="70%" style="vertical-align: middle;"></td>
                <td width="30%" style="text-align: center; padding: 20px;">
                  <img src="https://static.wixstatic.com/media/c4d4c1_6fa078f57383404faf7ceb1d9533f4fb~mv2.png/v1/fill/w_472,h_228,al_c,lg_1,q_85,enc_avif,quality_auto/Logo-Personalitec-Site.png" alt="Personalitec" style="max-width: 180px; height: auto;">
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td style="padding: 20px; text-align: center; color: #666; font-size: 11px;">
      <p style="margin: 0;">© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
    </td>
  </tr>
</table>
</body>
</html>
