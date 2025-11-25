<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ordem de Atendimento</title>
  <style>
    :root{
      --blue:#2f88d8;--blue-dark:#1f5fa1;--bg:#f5f7f9;--card:#ffffff;--muted:#666;--text:#111;--radius:8px;
    }
    *{box-sizing:border-box}body{margin:0;font-family:'Segoe UI',Roboto,Arial,sans-serif;background:var(--bg);color:var(--text);padding:20px}
    .paper{max-width:900px;margin:0 auto;background:var(--card);border-radius:var(--radius);overflow:hidden}
    .header{display:flex;align-items:center;gap:12px;padding:14px 18px;background:linear-gradient(90deg,var(--blue),#4fa1e6);color:#fff}
    .logo-brand{display:flex;align-items:center;gap:8px;flex:0}
    .logo-img{height:48px;width:auto}
    .tag{font-size:12px;line-height:1.3;font-weight:600}
    .tag-sub{font-size:10px;opacity:0.9}
    .title{flex:1;text-align:center;font-size:32px;font-weight:700;letter-spacing:1px}
    .num-box{background:var(--blue-dark);padding:8px 14px;border-radius:6px;text-align:center}
    .num-box small{display:block;font-size:11px;opacity:0.9}
    .num-box strong{display:block;font-size:18px;font-weight:700}
    .main{padding:16px}
    .info-section{display:flex;gap:20px}
    .col-left{flex:1}
    .col-right{width:45%;min-width:380px}
    .client-box{background:#f2f6fb;border:1px solid #d4e0ed;border-radius:6px;padding:12px;line-height:1.8}
    .client-box .row{display:flex;gap:12px;margin:6px 0}
    .client-box .label{font-weight:700;min-width:100px}
    .times-table{background:#fff;border:1px solid #d4e0ed;border-radius:6px;overflow:hidden}
    .times-table .th-row{display:grid;grid-template-columns:repeat(5,1fr);background:#f7f9fc;font-weight:700;color:var(--muted);padding:10px;border-bottom:2px solid #d4e0ed;text-align:center}
    .times-table .td-row{display:grid;grid-template-columns:repeat(5,1fr);padding:12px;text-align:center;font-weight:700;border-bottom:1px solid #eee}
    .section-title{background:var(--blue);color:#fff;padding:10px 16px;font-weight:700;font-size:14px;margin-top:16px;border-radius:4px}
    .detail-box{background:#fbfcfd;border:1px solid #e8eef7;border-radius:6px;padding:12px;margin-top:8px;line-height:1.6}
    .detail-box p{margin:6px 0}
    .detail-box strong{color:var(--text)}
    .resumo-section{margin-top:16px}
    .resumo-wrap{background:#fff;border:1px solid #d4e0ed;border-radius:6px;overflow:hidden}
    .resumo-table{width:100%;border-collapse:collapse;font-size:13px}
    .resumo-table th{background:#f7f9fc;border:1px solid #d4e0ed;padding:10px;font-weight:700;text-align:center;color:var(--muted)}
    .resumo-table td{border:1px solid #d4e0ed;padding:10px;text-align:center}
    .resumo-table .big{font-weight:700;color:var(--blue);font-size:15px}
    .resumo-logo{display:flex;align-items:center;justify-content:center;gap:16px;padding:16px;border-top:1px solid #d4e0ed}
    .logo-wrap{text-align:center}
    .logo-wrap img{height:50px;width:auto}
    .contact-info{font-size:12px;margin-top:8px}
    .contact-info a{color:var(--blue);text-decoration:none;font-weight:700}
    .footer{padding:12px 18px;border-top:1px solid #eee;text-align:center;font-size:11px;color:var(--muted)}
    @media(max-width:800px){
      .info-section{flex-direction:column}.col-right{width:100%;min-width:auto}.title{font-size:24px}.times-table .th-row,.times-table .td-row{grid-template-columns:repeat(3,1fr)}
      .resumo-logo{flex-direction:column}.times-table .th-row div:nth-child(n+4),.times-table .td-row div:nth-child(n+4){display:none}
    }
  </style>
</head>
<body>
  <article class="paper">
    <header class="header">
      <div class="logo-brand">
        <div class="tag">Personalitec<br><span class="tag-sub">Sua visão, nossa tecnologia</span></div>
      </div>
      <div class="title">ORDEM DE ATENDIMENTO</div>
      <div class="num-box"><small>NÚMERO</small><strong>{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
    </header>

    <main class="main">
      <div class="info-section">
        <div class="col-left">
          <div class="client-box">
            <div class="row"><span class="label">Cliente:</span><span>{{ $os->cliente->nome }}</span></div>
            <div class="row"><span class="label">Contato:</span><span>{{ $os->cliente->contato ?? '-' }}</span></div>
            <div class="row"><span class="label">Emissão:</span><span>{{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}</span></div>
            <div class="row"><span class="label">Consultor:</span><span>{{ $os->consultor->name }}</span></div>
          </div>
        </div>

        <div class="col-right">
          <div class="times-table">
            <div class="th-row">
              <div>HORA INICIO</div>
              <div>HORA FIM</div>
              <div>DESCONTO</div>
              <div>TRASLADO</div>
              <div>TOTAL HORAS</div>
            </div>
            <div class="td-row">
              <div>--:--</div>
              <div>--:--</div>
              <div>0:00</div>
              <div>{{ number_format($totalizador['deslocamento'] ?? 0, 2, ',', '.') }}</div>
              <div>{{ number_format($totalizador['horas'] ?? 0, 2, ',', '.') }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="section-title">DETALHAMENTO</div>
      <div class="detail-box">
        <p><strong>Assunto:</strong> {{ $os->assunto ?? '-' }}</p>
        <p><strong>Observações:</strong> {{ $os->observacao ?? 'Nenhuma observação adicionada' }}</p>
      </div>

      <div class="resumo-section">
        <div class="section-title">RESUMO</div>
        <div class="resumo-wrap">
          <div style="display:flex;align-items:stretch">
            <div style="flex:1">
              <table class="resumo-table">
                <tr>
                  <th>Chamado Personalitec</th>
                  <th>{{ str_pad($os->id, 6, '0', STR_PAD_LEFT) }}</th>
                  <th>Previsão Retorno</th>
                  <th>{{ \Carbon\Carbon::parse($os->data_emissao)->addDays(1)->format('d/m/Y') }}</th>
                </tr>
                <tr>
                  <td>KM</td>
                  <td>{{ $os->km ?? '0' }}</td>
                  <td class="big">TOTAL OS</td>
                  <td class="big">R$ {{ number_format($totalizador['total_geral'] ?? 0, 2, ',', '.') }}</td>
                </tr>
              </table>
            </div>
            <div class="resumo-logo">
              <div class="logo-wrap">
                <div style="font-weight:700;color:var(--blue);margin-bottom:6px">Personalitec</div>
                <div class="contact-info">atendimento@personalitec.com.br</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <footer class="footer">
      <p>Este é um e-mail automático. Por favor, não responda.<br>© {{ date('Y') }} Personalitec Soluções. Todos os direitos reservados.</p>
    </footer>
  </article>
</body>
</html>
