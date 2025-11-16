<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço - Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .header img { max-width: 200px; height: auto; margin-bottom: 10px; }
        .header h1 { color: #667eea; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .section { margin-bottom: 20px; }
        .section-title { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px; font-weight: bold; margin-bottom: 10px; }
        .info-row { display: table; width: 100%; margin-bottom: 5px; }
        .info-label { display: table-cell; width: 30%; font-weight: bold; color: #333; }
        .info-value { display: table-cell; width: 70%; color: #555; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .highlight-box { background: #f0f4ff; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ORDEM DE SERVIÇO</h1>
        <p><strong>Personalitec Soluções</strong></p>
        <p>OS Nº {{ $numero_os }}</p>
    </div>

    <div class="section">
        <div class="section-title">Informações da Ordem de Serviço</div>
        <div class="info-row">
            <div class="info-label">Número da OS:</div>
            <div class="info-value">{{ $numero_os }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Data de Emissão:</div>
            <div class="info-value">{{ $data_emissao }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Data de Aprovação:</div>
            <div class="info-value">{{ $data_aprovacao ?? 'Pendente' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Assunto:</div>
            <div class="info-value">{{ $assunto ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Projeto:</div>
            <div class="info-value">{{ $projeto ?? '-' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Cliente</div>
        <div class="info-row">
            <div class="info-label">Código:</div>
            <div class="info-value">{{ $cliente->codigo ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nome:</div>
            <div class="info-value">{{ $cliente->nome ?? '-' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Serviço Prestado</div>
        <div class="info-row">
            <div class="info-label">Descrição:</div>
            <div class="info-value">{{ $produto->descricao ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Quantidade (horas):</div>
            <div class="info-value">{{ $qtde_total ?? '-' }}</div>
        </div>
        @if($cliente->km)
        <div class="info-row">
            <div class="info-label">Quilometragem:</div>
            <div class="info-value">{{ $cliente->km }} km</div>
        </div>
        @endif
        @if($cliente->deslocamento)
        <div class="info-row">
            <div class="info-label">Deslocamento:</div>
            <div class="info-value">R$ {{ number_format((float)$cliente->deslocamento, 2, ',', '.') }}</div>
        </div>
        @endif
    </div>

    @if($detalhamento)
    <div class="section">
        <div class="section-title">Descrição dos Serviços</div>
        <p style="padding: 10px; background: #f9f9f9;">{{ $detalhamento }}</p>
    </div>
    @endif

    <div class="highlight-box">
        <div class="info-row">
            <div class="info-label" style="font-size: 16px;">VALOR TOTAL:</div>
            <div class="info-value" style="font-size: 18px; font-weight: bold; color: #667eea;">
                R$ {{ number_format((float)$valor_total, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Documento gerado automaticamente em {{ now()->format('d/m/Y H:i') }}</p>
        <p>© {{ date('Y') }} Personalitec Soluções</p>
        <p>Qualquer dúvida, entre em contato conosco.</p>
    </div>
</body>
</html>
