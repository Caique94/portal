<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ordem de Atendimento</title>
  <style>
    :root{
      --blue:#2f98db;
      --blue-dark:#1f76b1;
      --bg:#f5f7f9;
      --card:#ffffff;
      --muted:#58656f;
      --text:#111;
      --radius:10px;
      --gap:16px;
      --max-w:980px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter,'Segoe UI',Roboto,Arial,sans-serif;background:var(--bg);color:var(--text);padding:20px}
    .paper{max-width:var(--max-w);margin:18px auto;background:var(--card);border-radius:12px;border:1px solid rgba(17,33,50,0.04);overflow:hidden}
    .header{display:flex;align-items:center;gap:12px;padding:14px 18px;background:linear-gradient(90deg,var(--blue),#5fb0ea);color:#fff}
    .header .brand{display:flex;align-items:center;gap:10px}
    .brand .tag{font-size:12px;line-height:1.2}
    .brand .tag-main{font-weight:700;display:block}
    .brand .tag-sub{font-size:11px;opacity:0.95;display:block}
    .title{flex:1;text-align:center;font-size:34px;font-weight:700}
    .num{background:var(--blue-dark);padding:6px 12px;border-radius:8px;text-align:center}
    .num small{display:block;font-size:12px}
    .num strong{display:block;font-size:18px;font-weight:700}
    .main{padding:18px}
    .top{display:flex;gap:18px;align-items:flex-start}
    .col-left{flex:1;min-width:240px}
    .col-right{width:520px;max-width:52%}
    .client{background:#f2f6fb;padding:12px;border-radius:8px;border:1px solid rgba(17,33,50,0.03)}
    .client .row{margin:6px 0}
    .client .label{font-weight:700;display:inline-block;width:120px}
    .times{background:#fff;border-radius:8px;border:1px solid rgba(17,33,50,0.03);overflow:hidden}
    .times .head,.times .body{display:grid;grid-template-columns:repeat(5,1fr);text-align:center;padding:10px}
    .times .head{background:#f7fbff;font-weight:700;color:var(--muted)}
    .times .body{padding:12px;font-weight:700;border-top:1px solid rgba(17,33,50,0.03)}
    .times .item{padding:8px 4px}
    .section-title{display:block;background:var(--blue);color:#fff;padding:8px 12px;border-radius:6px;font-weight:700;margin-top:18px;text-align:center;width:100%}
    .detail{background:#fbfdff;padding:14px;border-radius:8px;border:1px solid rgba(17,33,50,0.03);margin-top:10px;line-height:1.45}
    .summary-wrap{margin-top:18px}
    .summary-title{display:block;background:var(--blue);color:#fff;padding:8px 12px;border-radius:8px;font-weight:700;margin-bottom:12px;text-align:center}
    .summary{margin-top:0;background:#fff;border:1px solid rgba(17,33,50,0.03);border-radius:8px;padding:12px;display:flex;gap:18px;align-items:center}
    .summary .table-wrap{flex:1}
    .summary table{width:100%;border-collapse:collapse;font-size:14px}
    .summary th,.summary td{padding:12px;border:1px solid #eef6fb;text-align:center;font-weight:700}
    .summary th{background:#f6fafc;color:var(--muted)}
    .summary .big{font-weight:800;font-size:16px;color:var(--blue)}
    .summary .logo-wrap{width:260px;text-align:center;padding:12px;border-left:1px solid #eef6fb}
    .summary .logo-wrap img{max-width:180px;height:auto}
    .summary .contact{margin-top:8px;font-size:12px;color:var(--muted)}
    .footer{padding:14px 18px;border-top:1px solid rgba(17,33,50,0.03);text-align:center;font-size:11px;color:var(--muted)}
    @media (max-width:900px){
      .title{font-size:22px;text-align:left}
      .header{flex-direction:column;align-items:flex-start;gap:10px}
      .top{flex-direction:column}
      .col-right{width:100%;max-width:100%}
      .summary{flex-direction:column;align-items:stretch}
      .summary .logo-wrap{order:2;text-align:left;width:100%;border-left:0;border-top:1px solid #eef6fb;padding-top:12px;margin-top:12px}
      .summary .table-wrap{order:1}
    }
    @media (max-width:480px){
      .title{font-size:18px}
      .num strong{font-size:16px}
      .times .head,.times .body{grid-template-columns:repeat(2,1fr)}
      .times .item:nth-child(n+3){display:none}
    }
  </style>
</head>
<body>
  <article class="paper" role="document" aria-label="Ordem de Atendimento">
    <header class="header">
      <div class="brand">
        <div class="tag">
          <span class="tag-main">Personalitec</span>
          <span class="tag-sub">Sua visão, nossa tecnologia</span>
        </div>
      </div>
      <div class="title">ORDEM DE ATENDIMENTO</div>
      <div class="num" aria-hidden="false">
        <small>NÚMERO</small>
        <strong>{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</strong>
      </div>
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
          <div class="times" aria-label="Resumo de Horas">
            <div class="head">
              <div class="item">Valor/Hora</div>
              <div class="item">Horas</div>
              <div class="item">Valor</div>
              <div class="item">KM</div>
              <div class="item">Total</div>
            </div>
            <div class="body">
              <div class="item">R$ {{ number_format($totalizador['valor_hora'] ?? 0, 2, ',', '.') }}</div>
              <div class="item">{{ number_format($totalizador['horas'] ?? 0, 2, ',', '.') }}</div>
              <div class="item">R$ {{ number_format($totalizador['valor_horas'] ?? 0, 2, ',', '.') }}</div>
              <div class="item">{{ $totalizador['km'] ?? 0 }}</div>
              <div class="item"><strong>R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</strong></div>
            </div>
          </div>
        </aside>
      </div>

      <div style="width:100%;margin-top:18px">
        <div class="section-title">DETALHAMENTO</div>
        <div class="detail">
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
                  <th>Chamado</th>
                  <th>{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</th>
                  <th>KM</th>
                  <th>{{ $os->km ?? 0 }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="2">&nbsp;</td>
                  <td class="big">TOTAL OS</td>
                  <td class="big">R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="logo-wrap">
            <div style="font-weight:700;color:var(--blue);margin-bottom:8px">Personalitec</div>
            <div class="contact">Sua visão, nossa tecnologia</div>
            <div class="contact" style="margin-top:8px;font-weight:700">
              <a href="mailto:atendimento@personalitec.com.br" style="color:var(--blue);text-decoration:none">atendimento@personalitec.com.br</a>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="footer">
      <p>Este é um e-mail automático. Por favor, não responda.<br>© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
    </footer>
  </article>
</body>
</html>
