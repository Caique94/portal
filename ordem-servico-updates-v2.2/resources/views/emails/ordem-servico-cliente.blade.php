<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ordem de Serviço #{{ $ordemServico->id }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">
  <!--[if mso]>
    <style type="text/css">
      body, table, td { font-family: Arial, Helvetica, sans-serif !important; }
    </style>
  <![endif]-->

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5;padding:20px 0;">
    <tr>
      <td align="center">

        <!-- container -->
        <table width="980" cellpadding="0" cellspacing="0" border="0" style="max-width:980px;width:100%;background-color:#ffffff;">
          <!-- HEADER -->
          <tr>
            <td style="padding:0;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#1565C0;">
                <tr>
                  <td width="80" style="padding:18px;vertical-align:middle;">
                    <img src="https://static.wixstatic.com/media/c4d4c1_6fa078f57383404faf7ceb1d9533f4fb~mv2.png/v1/fill/w_472,h_228,al_c,lg_1,q_85,enc_avif,quality_auto/Logo-Personalitec-Site.png"
                         alt="Personalitec" width="70" style="display:block;border:0;outline:none;text-decoration:none;">
                  </td>
                  <td align="center" style="padding:10px 0;">
                    <h1 style="margin:0;color:#ffffff;font-size:22px;letter-spacing:1px;font-weight:700;">ORDEM DE ATENDIMENTO</h1>
                  </td>
                  <td width="150" align="right" style="padding:12px;">
                    <table cellpadding="0" cellspacing="0" border="0" style="background-color:#0A5FA6;border-radius:6px;">
                      <tr>
                        <td style="padding:10px 12px;text-align:center;color:#ffffff;font-size:11px;font-weight:700;">NUMERO</td>
                      </tr>
                      <tr>
                        <td style="padding:8px 12px;text-align:center;color:#ffffff;font-size:16px;font-weight:800;">{{ $ordemServico->id }}</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- MAIN CONTENT -->
          <tr>
            <td style="padding:20px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <!-- Left column -->
                  <td width="50%" valign="top" style="padding-right:10px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F5F8FA;border:1px solid #E0E8F0;border-radius:6px;padding:12px;">
                      <tr>
                        <td style="font-weight:700;color:#0A5FA6;padding:6px;width:110px;">Cliente:</td>
                        <td style="color:#1F3A56;padding:6px;">
                          @if($ordemServico->cliente)
                            {{ $ordemServico->cliente->nome ?? $ordemServico->cliente->nome_fantasia ?? 'N/A' }}
                          @else
                            N/A
                          @endif
                        </td>
                      </tr>
                      <tr>
                        <td style="font-weight:700;color:#0A5FA6;padding:6px;">Contato:</td>
                        <td style="color:#1F3A56;padding:6px;">
                          @if($ordemServico->cliente)
                            {{ $ordemServico->cliente->email ?? $ordemServico->cliente->contato ?? 'N/A' }}
                          @else
                            N/A
                          @endif
                        </td>
                      </tr>
                      <tr>
                        <td style="font-weight:700;color:#0A5FA6;padding:6px;">Emissão:</td>
                        <td style="color:#1F3A56;padding:6px;">{{ \Carbon\Carbon::parse($ordemServico->data_emissao)->format('d/m/Y') }}</td>
                      </tr>
                      <tr>
                        <td style="font-weight:700;color:#0A5FA6;padding:6px;">Consultor:</td>
                        <td style="color:#1F3A56;padding:6px;">{{ $ordemServico->consultor->name ?? 'N/A' }}</td>
                      </tr>
                    </table>
                  </td>

                  <!-- Right column -->
                  <td width="50%" valign="top" style="padding-left:10px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F5F8FA;border:1px solid #E0E8F0;border-radius:6px;overflow:hidden;">
                      <tr style="background-color:#0A5FA6;color:#ffffff;font-weight:700;font-size:12px;">
                        <td style="padding:12px;text-align:center;border-right:1px solid #1966A8;">HORA INICIO</td>
                        <td style="padding:12px;text-align:center;border-right:1px solid #1966A8;">HORA FIM</td>
                        <td style="padding:12px;text-align:center;border-right:1px solid #1966A8;">HORA DESCONTO</td>
                        <td style="padding:12px;text-align:center;border-right:1px solid #1966A8;">DESPESA</td>
                        <td style="padding:12px;text-align:center;border-right:1px solid #1966A8;">TRANSLADO</td>
                        <td style="padding:12px;text-align:center;">TOTAL HORAS</td>
                      </tr>
                      <tr style="font-weight:600;font-size:13px;color:#1F3A56;">
                        <td style="padding:10px;text-align:center;border-right:1px solid #E0E8F0;">{{ $ordemServico->hora_inicio ?? '00:00' }}</td>
                        <td style="padding:10px;text-align:center;border-right:1px solid #E0E8F0;">{{ $ordemServico->hora_final ?? '00:00' }}</td>
                        <td style="padding:10px;text-align:center;border-right:1px solid #E0E8F0;">{{ $ordemServico->hora_desconto ? $ordemServico->hora_desconto : '00:00' }}</td>
                        <td style="padding:10px;text-align:center;border-right:1px solid #E0E8F0;">
                          {{ $ordemServico->valor_despesa ? 'R$ ' . number_format($ordemServico->valor_despesa, 2, ',', '.') : '--' }}
                        </td>
                        <td style="padding:10px;text-align:center;border-right:1px solid #E0E8F0;">
                          @php
                            if ($ordemServico->deslocamento) {
                              $deslocamento = floatval($ordemServico->deslocamento);
                              $horas = intval($deslocamento);
                              $minutos = intval(($deslocamento - $horas) * 60);
                              echo sprintf('%02d:%02d', $horas, $minutos);
                            } else {
                              echo '--';
                            }
                          @endphp
                        </td>
                        <td style="padding:10px;text-align:center;">{{ $ordemServico->qtde_total ? number_format(floatval($ordemServico->qtde_total), 2, '.', '') : '--' }}</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- DETALHAMENTO TITLE -->
          <tr>
            <td style="padding:0 20px 10px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0A5FA6;color:#ffffff;padding:10px;border-radius:6px;">
                <tr>
                  <td align="center" style="font-weight:700;">DETALHAMENTO</td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- DETALHAMENTO CONTENT -->
          <tr>
            <td style="padding:0 20px 20px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F5F8FA;border:1px solid #E0E8F0;border-radius:6px;padding:12px;">
                <tr>
                  <td style="color:#1F3A56;font-size:13px;line-height:1.6;text-align:left;">
                    {!! nl2br(e($ordemServico->detalhamento ?? 'Nenhum detalhamento fornecido.')) !!}
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- RESUMO (Faixa Azul + 4 colunas × 2 linhas + logo à direita) -->
          <tr>
            <td style="padding:0 20px 20px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                <tr>
                  <!-- resumo block (60%) -->
                  <td style="padding:0 10px 0 0;width:60%;vertical-align:top;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F5F8FA;border:1px solid #DDE6EE;border-radius:8px;overflow:hidden;">
                      <!-- faixa -->
                      <tr>
                        <td style="background:#0A5FA6;color:#ffffff;font-size:14px;font-weight:700;text-align:center;padding:8px 10px;border-bottom:1px solid #DDE6EE;border-top-left-radius:8px;border-top-right-radius:8px;">
                          RESUMO
                        </td>
                      </tr>

                      <!-- inner table -->
                      <tr>
                        <td style="padding:0;">
                          <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                            <!-- linha 1 -->
                            <tr style="background:#F7F7F7;text-align:center;color:#0A5FA6;font-size:13px;font-weight:600;">
                              <td style="padding:12px 8px;border-right:1px solid #E2E6EA;">Chamado<br><span style="font-weight:700;color:#0A5FA6;">Personalitec</span></td>
                              <td style="padding:12px 8px;border-right:1px solid #E2E6EA;font-weight:700;color:#0A5FA6;">{{ $ordemServico->nr_atendimento ?? $ordemServico->id }}</td>
                              <td style="padding:12px 8px;border-right:1px solid #E2E6EA;">Data<br>de Emissão</td>
                              <td style="padding:12px 8px;">{{ $ordemServico->previsao_retorno ?? ($ordemServico->data_emissao ? \Carbon\Carbon::parse($ordemServico->data_emissao)->format('d/m/Y') : '--') }}</td>
                            </tr>

                            <!-- linha 2 -->
                            <tr style="background:#FFFFFF;text-align:center;color:#0A5FA6;font-size:13px;font-weight:600;">
                              <td style="padding:12px 8px;border-top:1px solid #E2E6EA;border-right:1px solid #E2E6EA;">KM</td>
                              <td style="padding:12px 8px;border-top:1px solid #E2E6EA;border-right:1px solid #E2E6EA;font-weight:700;color:#0A5FA6;">{{ $ordemServico->km ?? '--' }}</td>
                              <td style="padding:12px 8px;border-top:1px solid #E2E6EA;border-right:1px solid #E2E6EA;font-weight:700;color:#0A5FA6;">TOTAL OS</td>
                              <td style="padding:12px 8px;border-top:1px solid #E2E6EA;font-weight:800;color:#0A5FA6;font-size:14px;">
                                {{ $ordemServico->valor_total ? 'R$ ' . number_format($ordemServico->valor_total, 2, ',', '.') : (isset($total_ganho) ? 'R$ ' . number_format($total_ganho,2,',','.') : '--') }}
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </td>

                  <!-- logo column (40%) -->
                  <td style="padding:0 0 0 10px;width:40%;text-align:center;vertical-align:middle;">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="height:100%;vertical-align:middle;">
                      <tr>
                        <td style="padding:10px;text-align:center;vertical-align:middle;">
                          <img src="https://static.wixstatic.com/media/c4d4c1_6fa078f57383404faf7ceb1d9533f4fb~mv2.png/v1/fill/w_472,h_228,al_c,lg_1,q_85,enc_avif,quality_auto/Logo-Personalitec-Site.png"
                               alt="Personalitec" width="110" style="display:block;margin:0 auto 8px;">
                          <div style="font-size:14px;font-weight:800;color:#1A1A1A;">Personalitec</div>
                          <div style="font-size:11px;color:#606060;margin-top:2px;">Sua visão, nossa tecnologia</div>
                          <div style="font-size:12px;color:#0A5FA6;margin-top:8px;">atendimento@personalitec.com.br</div>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="padding:20px;text-align:center;color:#666;font-size:11px;">
              <p style="margin:0;">© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
            </td>
          </tr>

        </table>
        <!-- /container -->

      </td>
    </tr>
  </table>
</body>
</html>
