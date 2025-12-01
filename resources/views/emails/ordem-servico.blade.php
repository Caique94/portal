<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ordem de Serviço #{{ $ordemServico->id }}</title>
  <style>
    :root{
      --blue-primary:#2E7DA8; --blue-light:#5B9FBF; --blue-accent:#0A5FA6; --bg-light:#F5F8FA; --bg:#ffffff; --text-dark:#1F3A56; --text-light:#556B7E;
      --radius:6px; --gap:16px; --max-w:1000px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;background:#EFEFEF;color:var(--text-dark);padding:15px}

    .paper{max-width:var(--max-w);margin:0 auto;background:var(--bg);border-radius:0;border:none;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.12)}

    .header{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:16px 20px;background:linear-gradient(135deg,var(--blue-primary) 0%,var(--blue-light) 100%);color:#fff}
    .header .brand{display:flex;align-items:center;gap:12px;flex:1}
    .brand img{height:48px;width:auto}
    .brand .tag{font-size:12px;line-height:1.2;font-weight:600;color:#fff}
    .brand .tag small{display:block;font-size:10px;opacity:0.95}
    .title{flex:2;text-align:center;font-size:28px;font-weight:700;letter-spacing:0.5px}
    .num{background:var(--blue-accent);padding:10px 16px;border-radius:var(--radius);text-align:center;min-width:100px}
    .num small{display:block;font-size:10px;letter-spacing:0.5px;opacity:0.9;font-weight:600}
    .num strong{display:block;font-size:18px;font-weight:700;margin-top:3px}

    .main{padding:20px}
    .content-wrapper{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}

    .client-box{background:var(--bg-light);padding:16px;border-radius:var(--radius);border:1px solid #E0E8F0}
    .client-box .row{display:flex;gap:10px;margin:8px 0;font-size:13px;line-height:1.4}
    .client-box .label{font-weight:700;color:var(--blue-accent);min-width:90px;flex-shrink:0}
    .client-box .value{color:var(--text-dark);flex:1}

    .times-box{background:var(--bg-light);border:1px solid #E0E8F0;border-radius:var(--radius);overflow:hidden}
    .times-head{display:grid;grid-template-columns:repeat(5,1fr);background:var(--blue-primary);color:#fff;padding:10px;font-weight:700;font-size:11px;text-align:center;gap:2px}
    .times-head div{padding:4px 2px}
    .times-body{display:grid;grid-template-columns:repeat(5,1fr);padding:10px;font-weight:600;font-size:13px;text-align:center;color:var(--text-dark);gap:2px}
    .times-body div{padding:4px 2px}

    .section-title{background:linear-gradient(90deg,var(--blue-primary) 0%,var(--blue-light) 100%);color:#fff;padding:10px 16px;border-radius:var(--radius);font-weight:700;margin:18px 0 10px;text-align:center;font-size:13px;letter-spacing:0.3px}
    .detail{background:var(--bg-light);padding:14px;border-radius:var(--radius);border:1px solid #E0E8F0;line-height:1.6;text-align:justify;color:var(--text-dark);font-size:13px}

    .summary-section{margin-top:15px;background:var(--bg-light);border:1px solid #E0E8F0;border-radius:var(--radius);overflow:hidden}
    .summary-title{background:linear-gradient(90deg,var(--blue-primary) 0%,var(--blue-light) 100%);color:#fff;padding:10px 16px;font-weight:700;text-align:center;font-size:13px;letter-spacing:0.3px}
    .summary-content{display:flex;gap:20px;padding:16px}
    .summary-table{flex:1}
    .summary-table table{width:100%;border-collapse:collapse;font-size:12px}
    .summary-table th{background:white;color:var(--blue-primary);padding:8px;border:1px solid #E0E8F0;font-weight:700;text-align:center}
    .summary-table td{padding:8px;border:1px solid #E0E8F0;text-align:center;color:var(--text-dark)}
    .summary-table .value-big{font-weight:800;color:var(--blue-accent);font-size:14px}
    .summary-logo{width:200px;text-align:center;display:flex;align-items:center;justify-content:center}
    .summary-logo img{max-width:180px;height:auto}

    @media (max-width:768px){
      .title{font-size:20px}
      .header{flex-wrap:wrap;padding:12px 16px}
      .brand{flex:1}
      .num{min-width:80px}
      .content-wrapper{grid-template-columns:1fr;gap:16px}
      .summary-content{flex-direction:column}
      .summary-logo{width:100%;margin-top:10px}
      .brand img{height:42px}
    }

    @media (max-width:480px){
      .header{flex-direction:column;align-items:flex-start}
      .title{font-size:16px;text-align:left}
      .brand img{height:36px}
      .num{align-self:flex-end;margin-top:5px}
      .times-head,.times-body{grid-template-columns:repeat(2,1fr)}
      .times-head div:nth-child(n+3),.times-body div:nth-child(n+3){grid-column:auto}
      .main{padding:12px}
      .content-wrapper{gap:12px}
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
      <div class="content-wrapper">
        <div class="client-box">
          <div class="row"><span class="label">Cliente:</span> <span class="value">{{ $ordemServico->cliente->name ?? 'N/A' }}</span></div>
          <div class="row"><span class="label">Contato:</span> <span class="value">{{ $ordemServico->cliente->pessoaJuridica->email ?? $ordemServico->cliente->email ?? 'N/A' }}</span></div>
          <div class="row"><span class="label">Emissão:</span> <span class="value">{{ \Carbon\Carbon::parse($ordemServico->data_emissao)->format('d/m/Y') }}</span></div>
          <div class="row"><span class="label">Consultor:</span> <span class="value">{{ $ordemServico->consultor->name ?? 'N/A' }}</span></div>
        </div>

        <div class="times-box">
          <div class="times-head">
            <div>HORA INICIO</div>
            <div>HORA FIM</div>
            <div>DESCONTO</div>
            <div>TRASLADO</div>
            <div>TOTAL HORAS</div>
          </div>
          <div class="times-body">
            <div>{{ $ordemServico->hora_inicio ?? '--:--' }}</div>
            <div>{{ $ordemServico->hora_final ?? '--:--' }}</div>
            <div>{{ $ordemServico->hora_desconto ?? '--' }}</div>
            <div>{{ $ordemServico->deslocamento ? 'R$ ' . number_format($ordemServico->deslocamento, 2, ',', '.') : '--' }}</div>
            <div>{{ $ordemServico->horas_trabalhadas ?? '--' }}</div>
          </div>
        </div>
      </div>

      <div class="section-title">DETALHAMENTO</div>
      <div class="detail">
        {!! nl2br($ordemServico->detalhamento ?? 'Nenhum detalhamento fornecido.') !!}
      </div>

      <div class="summary-section">
        <div class="summary-title">RESUMO</div>
        <div class="summary-content">
          <div class="summary-table">
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
                  <td class="value-big">{{ $ordemServico->valor_total ? 'R$ ' . number_format($ordemServico->valor_total, 2, ',', '.') : '--' }}</td>
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
          <div class="summary-logo">
            <img src="https://static.wixstatic.com/media/c4d4c1_6fa078f57383404faf7ceb1d9533f4fb~mv2.png/v1/fill/w_472,h_228,al_c,lg_1,q_85,enc_avif,quality_auto/Logo-Personalitec-Site.png" alt="Personalitec">
          </div>
        </div>
      </div>
    </main>
  </article>
</body>
</html>
