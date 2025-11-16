<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; background: #0d6efd; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .info-box { background: white; padding: 15px; border-left: 4px solid #0d6efd; margin: 15px 0; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatório de Ordem de Serviço</h1>
            <p>OS Nº {{ $numero_os }}</p>
        </div>

        <div class="content">
            <p>Olá, <strong>{{ $os->consultor->name }}</strong>!</p>

            <p>Segue em anexo o relatório da Ordem de Serviço <strong>#{{ $numero_os }}</strong> que foi aprovada.</p>

            <div class="info-box">
                <strong>Informações da OS:</strong><br>
                <strong>Cliente:</strong> {{ $os->cliente->nome }}<br>
                <strong>Assunto:</strong> {{ $os->assunto ?? '-' }}<br>
                <strong>Data de Emissão:</strong> {{ \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y') }}<br>
                <strong>Valor Total:</strong> R$ {{ number_format((float)$os->valor_total, 2, ',', '.') }}
            </div>

            <p>O relatório detalhado está em anexo neste e-mail.</p>

            <p style="margin-top: 30px;">
                <small>Este é um e-mail automático. Por favor, não responda.</small>
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Personalitec Soluções</p>
        </div>
    </div>
</body>
</html>
