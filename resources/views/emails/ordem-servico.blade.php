<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ordem de Serviço #{{ $ordemServico->id }}</title>
  <style>
    :root{
      --blue:#50B5DF; --blue-dark:#252ED9; --secondary:#009EFB; --light:#F2F7F8; --bg:#f5f7f9; --card:#ffffff; --muted:#556777; --text:#111827;
      --radius:10px; --gap:16px; --max-w:980px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter, 'Segoe UI', Roboto, Arial, sans-serif;background:var(--bg);color:var(--text);padding:20px}

    .paper{max-width:var(--max-w);margin:18px auto;background:var(--card);border-radius:12px;border:1px solid #E5E7EB;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1)}

    .header{display:flex;align-items:center;gap:12px;padding:20px 18px;background:linear-gradient(90deg,var(--blue),var(--secondary));color:#fff}
    .header .brand{display:flex;align-items:center;gap:10px}
    .brand img{height:54px;width:auto}
    .brand .tag{font-size:13px;line-height:1.3;font-weight:600;color:#fff}
    .title{flex:1;text-align:center;font-size:34px;font-weight:700;letter-spacing:-0.5px}
    .num{background:var(--blue-dark);padding:8px 14px;border-radius:8px;text-align:center;box-shadow:0 2px 4px rgba(37,46,217,0.3)}
    .num small{display:block;font-size:11px;letter-spacing:0.5px;opacity:0.9}
    .num strong{display:block;font-size:20px;font-weight:700;margin-top:2px}

    .main{padding:20px}
    .top{display:flex;gap:20px;align-items:flex-start}
    .col-left{flex:1;min-width:240px}
    .col-right{width:520px;max-width:52%}

    .client{background:var(--light);padding:14px;border-radius:8px;border:1px solid #D1D5DB;box-shadow:0 1px 2px rgba(0,0,0,0.05)}
    .client .row{margin:8px 0;color:var(--text);font-size:14px;line-height:1.5}
    .client .label{font-weight:700;display:inline-block;width:120px;color:var(--blue-dark)}

    .times{background:#fff;border-radius:8px;border:2px solid var(--light);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05)}
    .times .head,.times .body{display:grid;grid-template-columns:repeat(5,1fr);text-align:center}
    .times .head{background:var(--light);padding:12px;font-weight:700;color:var(--blue-dark);font-size:12px;letter-spacing:0.3px}
    .times .body{padding:14px;font-weight:600;color:var(--text);font-size:13px}

    .section-title{display:block;width:100%;text-align:center;background:linear-gradient(90deg,var(--blue),var(--secondary));color:#fff;padding:12px 16px;border-radius:8px;font-weight:700;margin-top:20px;letter-spacing:0.5px;font-size:14px;box-shadow:0 2px 4px rgba(80,181,223,0.2)}
    .detail{background:var(--light);padding:16px;border-radius:8px;border:1px solid #D1D5DB;margin-top:12px;line-height:1.6;text-align:justify;color:var(--text);font-size:13px}

    .summary-wrap{margin-top:20px}
    .summary-title{display:block;background:linear-gradient(90deg,var(--blue),var(--secondary));color:#fff;padding:12px 16px;border-radius:8px;font-weight:700;margin-top:20px;text-align:center;width:calc(100% - 260px - 18px);letter-spacing:0.5px;font-size:14px;box-shadow:0 2px 4px rgba(80,181,223,0.2)}
    .summary{margin-top:14px;background:#fff;border:1px solid #E5E7EB;border-radius:8px;padding:14px;display:flex;gap:20px;align-items:center;box-shadow:0 1px 3px rgba(0,0,0,0.05)}
    .summary .table-wrap{flex:1}
    .summary table{width:100%;border-collapse:collapse;font-size:13px}
    .summary th,.summary td{padding:14px;border:1px solid #F3F4F6;text-align:center;color:var(--text)}
    .summary th{background:var(--light);color:var(--blue-dark);font-weight:700;letter-spacing:0.3px}
    .summary .big{font-weight:800;color:var(--blue-dark);font-size:15px}
    .summary .logo-wrap{width:260px;text-align:center}
    .summary .logo-wrap img{max-width:220px;height:auto}

    @media (max-width:900px){
      .title{font-size:22px;text-align:left}
      .header{flex-direction:column;align-items:flex-start;gap:10px}
      .top{flex-direction:column}
      .col-right{width:100%}
      .summary{flex-direction:column;align-items:stretch}
      .summary .logo-wrap{order:2;text-align:left}
      .summary .table-wrap{order:1}
      .brand img{height:48px}
    }

    @media (max-width:480px){
      .brand img{height:40px}
      .num strong{font-size:16px}
      .times .head,.times .body{grid-template-columns:repeat(2,1fr);grid-auto-rows:auto}
      .times .head div:nth-child(n+3),.times .body div:nth-child(n+3){display:block;padding:8px 0;border-top:1px solid #f0f6fb}
    }
  </style>
</head>
<body>
  <article class="paper" role="document" aria-label="Ordem de Serviço">
    <header class="header">
      <div class="brand">
        <img src="https://static.wixstatic.com/media/c4d4c1_6fa078f57383404faf7ceb1d9533f4fb~mv2.png/v1/fill/w_472,h_228,al_c,lg_1,q_85,enc_avif,quality_auto/Logo-Personalitec-Site.png" alt="Personalitec logo">
        <div class="tag">Personalitec<br><small style="opacity:0.95">Sua visão, nossa tecnologia</small></div>
      </div>

      <div class="title">ORDEM DE ATENDIMENTO</div>

      <div class="num"><small>NUMERO</small><strong>{{ $ordemServico->id }}</strong></div>
    </header>

    <main class="main">
      <div class="top">
        <div class="col-left">
          <div class="client">
            <div class="row"><span class="label">Cliente:</span> {{ $ordemServico->cliente->name ?? 'N/A' }}</div>
            <div class="row"><span class="label">Contato:</span> {{ $ordemServico->cliente->pessoaJuridica->email ?? $ordemServico->cliente->email ?? 'N/A' }}</div>
            <div class="row"><span class="label">Emissão:</span> {{ \Carbon\Carbon::parse($ordemServico->data_emissao)->format('d/m/Y') }}</div>
            <div class="row"><span class="label">Consultor:</span> {{ $ordemServico->consultor->name ?? 'N/A' }}</div>
          </div>
        </div>

        <aside class="col-right">
          <div class="times">
            <div class="head">
              <div>HORA INICIO</div>
              <div>HORA FIM</div>
              <div>DESCONTO</div>
              <div>TRASLADO</div>
              <div>TOTAL HORAS</div>
            </div>
            <div class="body">
              <div>{{ $ordemServico->hora_inicio ?? '--:--' }}</div>
              <div>{{ $ordemServico->hora_final ?? '--:--' }}</div>
              <div>{{ $ordemServico->hora_desconto ?? '--' }}</div>
              <div>{{ $ordemServico->deslocamento ? 'R$ ' . number_format($ordemServico->deslocamento, 2, ',', '.') : '--' }}</div>
              <div>{{ $ordemServico->horas_trabalhadas ?? '--' }}</div>
            </div>
          </div>
        </aside>
      </div>

      <div style="width:100%;margin-top:18px">
        <div class="section-title">DETALHAMENTO</div>
        <div class="detail">
          {!! nl2br($ordemServico->detalhamento ?? 'Nenhum detalhamento fornecido.') !!}
        </div>
      </div>

      <section class="summary-wrap">
        <div class="summary-title">RESUMO</div>
        <div class="summary">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Chamado Personalitec</th>
                  <th>{{ $ordemServico->nr_atendimento ?? $ordemServico->id }}</th>
                  <th>Previsão Retorno</th>
                  <th>{{ $ordemServico->updated_at ? \Carbon\Carbon::parse($ordemServico->updated_at)->addDay()->format('d/m/Y') : '--' }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>KM</td>
                  <td>{{ $ordemServico->km ?? '--' }}</td>
                  <td>Valor Total</td>
                  <td class="big">{{ $ordemServico->valor_total ? 'R$ ' . number_format($ordemServico->valor_total, 2, ',', '.') : '--' }}</td>
                </tr>
                <tr>
                  <td>Horas Trabalhadas</td>
                  <td>{{ $ordemServico->horas_trabalhadas ?? '--' }}</td>
                  <td>Status</td>
                  <td>@if($ordemServico->status == 1) Pendente @elseif($ordemServico->status == 2) Aprovado @elseif($ordemServico->status == 3) Finalizado @else Desconhecido @endif</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="logo-wrap">
            <img src="https://static.wixstatic.com/media/c4d4c1_6fa078f57383404faf7ceb1d9533f4fb~mv2.png/v1/fill/w_472,h_228,al_c,lg_1,q_85,enc_avif,quality_auto/Logo-Personalitec-Site.png" alt="Personalitec">
          </div>
        </div>
      </section>
    </main>
  </article>
</body>
</html>
