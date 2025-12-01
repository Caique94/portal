<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ordem de Serviço #{{ $ordemServico->id }}</title>
  <style>
    :root{
      --blue:#2f98db; --blue-dark:#1f76b1; --bg:#f5f7f9; --card:#ffffff; --muted:#58656f; --text:#111;
      --radius:10px; --gap:16px; --max-w:980px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter, 'Segoe UI', Roboto, Arial, sans-serif;background:var(--bg);color:var(--text);padding:20px}

    .paper{max-width:var(--max-w);margin:18px auto;background:var(--card);border-radius:12px;border:1px solid rgba(17,33,50,0.04);overflow:hidden}

    .header{display:flex;align-items:center;gap:12px;padding:14px 18px;background:linear-gradient(90deg,var(--blue),#5fb0ea);color:#fff}
    .header .brand{display:flex;align-items:center;gap:10px}
    .brand img{height:54px;width:auto}
    .brand .tag{font-size:12px;line-height:1}
    .title{flex:1;text-align:center;font-size:34px;font-weight:700}
    .num{background:var(--blue-dark);padding:6px 12px;border-radius:8px;text-align:center}
    .num small{display:block;font-size:12px}
    .num strong{display:block;font-size:18px}

    .main{padding:18px}
    .top{display:flex;gap:18px;align-items:flex-start}
    .col-left{flex:1;min-width:240px}
    .col-right{width:520px;max-width:52%}

    .client{background:#f2f6fb;padding:12px;border-radius:8px;border:1px solid rgba(17,33,50,0.03)}
    .client .row{margin:6px 0}
    .client .label{font-weight:700;display:inline-block;width:120px}

    .times{background:#fff;border-radius:8px;border:1px solid rgba(17,33,50,0.03);overflow:hidden}
    .times .head,.times .body{display:grid;grid-template-columns:repeat(5,1fr);text-align:center}
    .times .head{background:#f7fbff;padding:10px;font-weight:700;color:var(--muted)}
    .times .body{padding:12px;font-weight:700}

    .section-title{display:block;width:100%;text-align:center;background:var(--blue);color:#fff;padding:8px 12px;border-radius:8px;font-weight:700;margin-top:18px}
    .detail{background:#fbfdff;padding:14px;border-radius:8px;border:1px solid rgba(17,33,50,0.03);margin-top:10px;line-height:1.45;text-align:justify}

    .summary-wrap{margin-top:18px}
    .summary-title{display:block;background:var(--blue);color:#fff;padding:8px 12px;border-radius:8px;font-weight:700;margin-top:18px;text-align:center;width:calc(100% - 260px - 18px)}
    .summary{margin-top:12px;background:#fff;border:1px solid rgba(17,33,50,0.03);border-radius:8px;padding:12px;display:flex;gap:18px;align-items:center}
    .summary .table-wrap{flex:1}
    .summary table{width:100%;border-collapse:collapse}
    .summary th,.summary td{padding:12px;border:1px solid #eef6fb;text-align:center}
    .summary th{background:#f6fafc;color:var(--muted);font-weight:700}
    .summary .big{font-weight:800}
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
