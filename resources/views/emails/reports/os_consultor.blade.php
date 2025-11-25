<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ordem de Atendimento</title>
  <style>
    :root{
      --blue:#2f98db; --blue-dark:#1f76b1; --bg:#f5f7f9; --card:#ffffff; --muted:#58656f; --text:#111;
      --radius:10px; --gap:16px; --max-w:980px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter, 'Segoe UI', Roboto, Arial, sans-serif;background:var(--bg);color:var(--text);padding:20px}

    /* Container */
    .paper{max-width:var(--max-w);margin:18px auto;background:var(--card);border-radius:12px;border:1px solid rgba(17,33,50,0.04);overflow:hidden}

    /* Header */
    .header{display:flex;align-items:center;gap:12px;padding:14px 18px;background:linear-gradient(90deg,var(--blue),#5fb0ea);color:#fff}
    .header .brand{display:flex;align-items:center;gap:10px}
    .brand img{height:54px;width:auto}
    .brand .tag{font-size:12px;line-height:1}
    .title{flex:1;text-align:center;font-size:34px;font-weight:700}
    .num{background:var(--blue-dark);padding:6px 12px;border-radius:8px;text-align:center}
    .num small{display:block;font-size:12px}
    .num strong{display:block;font-size:18px}

    /* Main */
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

    /* Detalhamento */
    .section-title{display:inline-block;background:var(--blue);color:#fff;padding:8px 12px;border-radius:6px;font-weight:700;margin-top:18px}
    .detail{background:#fbfdff;padding:14px;border-radius:8px;border:1px solid rgba(17,33,50,0.03);margin-top:10px;line-height:1.45}

    /* Resumo (full width under detail) */
    .summary-wrap{margin-top:18px}
    .summary-title{display:block;background:var(--blue);color:#fff;padding:8px 12px;border-radius:8px;font-weight:700;margin-top:18px;text-align:center;width:100%}
    .summary{margin-top:12px;background:#fff;border:1px solid rgba(17,33,50,0.03);border-radius:8px;padding:12px;display:flex;gap:18px;align-items:center}
    .summary .table-wrap{flex:1}
    .summary table{width:100%;border-collapse:collapse}
    .summary th,.summary td{padding:12px;border:1px solid #eef6fb;text-align:center}
    .summary th{background:#f6fafc;color:var(--muted);font-weight:700}
    .summary .big{font-weight:800}
    .summary .logo-wrap{width:260px;text-align:center}
    .summary .logo-wrap img{max-width:220px;height:auto}

    /* Footer */
    .footer{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-top:1px solid rgba(17,33,50,0.03)}
    .footer .email{color:var(--muted)}

    /* Small screen stack */
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

    /* Very small screens */
    @media (max-width:480px){
      .brand img{height:40px}
      .num strong{font-size:16px}
      .times .head,.times .body{grid-template-columns:repeat(2,1fr);grid-auto-rows:auto}
      .times .head div:nth-child(n+3),.times .body div:nth-child(n+3){display:block;padding:8px 0;border-top:1px solid #f0f6fb}
    }
  </style>
</head>
<body>
  <article class="paper" role="document" aria-label="Ordem de Atendimento">
    <header class="header">
      <div class="brand">
        <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec logo">
        <div class="tag">Personalitec<br><small style="opacity:0.95">Sua visão, nossa tecnologia</small></div>
      </div>

      <div class="title">ORDEM DE ATENDIMENTO</div>

      <div class="num" aria-hidden="false"><small>NUMERO</small><strong>{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
    </header>

    <main class="main">
      <div class="top">
        <div class="col-left">
          <div class="client" aria-label="Dados do Cliente">
            <div class="row"><span class="label">Cliente:</span> {{ $os->cliente->nome }}</div>
            <div class="row"><span class="label">Contato:</span> {{ $os->cliente->contato ?? '-' }}</div>
            <div class="row"><span class="label">Emissão:</span> {{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}</div>
            <div class="row"><span class="label">Consultor:</span> {{ $os->consultor->name }}</div>
          </div>
        </div>

        <aside class="col-right">
          <div class="times" aria-label="Horários">
            <div class="head">
              <div>HORA INICIO</div>
              <div>HORA FIM</div>
              <div>DESCONTO</div>
              <div>TRASLADO</div>
              <div>TOTAL HORAS</div>
            </div>
            <div class="body">
              <div>--:--</div>
              <div>--:--</div>
              <div>0:00</div>
              <div>{{ number_format($totalizador['deslocamento'] ?? 0, 2, ',', '.') }}</div>
              <div>{{ number_format($totalizador['horas'] ?? 0, 2, ',', '.') }}</div>
            </div>
          </div>
        </aside>
      </div>

      <div style="width:100%;margin-top:18px">
        <div class="section-title" style="display:block;width:100%;text-align:center;border-radius:8px">DETALHAMENTO</div>
        <div class="detail" style="margin-top:10px;text-align:justify;width:100%">
          <p style="margin:0 0 8px 0"><strong>Assunto:</strong> {{ $os->assunto ?? '-' }}</p>
          <p style="margin:0"><strong>Observações:</strong> {{ $os->observacao ?? 'Nenhuma observação adicionada' }}</p>
        </div>
      </div>

      <section class="summary-wrap">
        <div class="summary-title">RESUMO</div>
        <div class="summary" role="region" aria-label="Resumo da OS">
          <div class="table-wrap">
            <table role="table" aria-label="Resumo tabela">
              <thead>
                <tr>
                  <th>Chamado Personalitec</th>
                  <th>{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</th>
                  <th>Previsão Retorno</th>
                  <th>{{ \Carbon\Carbon::parse($os->data_emissao)->addDays(1)->format('d/m/Y') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>KM</td>
                  <td>{{ $os->km ?? '0' }}</td>
                  <td class="big">TOTAL OS</td>
                  <td class="big">R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="logo-wrap">
            <img src="{{ asset('images/logo-personalitec.png') }}" alt="Personalitec logo">
            <div style="margin-top:8px;color:var(--muted)">atendimento@personalitec.com.br</div>
          </div>
        </div>
      </section>

    </main>

  </article>
</body>
</html>
