<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px 0;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatório de Fechamento Pronto</h1>
        </div>

        <div class="content">
            <p>Olá {{ $user->name }},</p>

            <p>Seu relatório de <strong>Fechamento {{ $tipo }}</strong> foi gerado com sucesso!</p>

            <p><strong>Detalhes:</strong></p>
            <ul>
                <li><strong>Tipo:</strong> {{ $tipo }}</li>
                <li><strong>Período:</strong> {{ $periodo }}</li>
                <li><strong>Status:</strong> {{ ucfirst($job->status) }}</li>
                <li><strong>Versão:</strong> {{ $job->version }}</li>
            </ul>

            <p>Você pode fazer o download do relatório clicando no botão abaixo:</p>

            <div style="text-align: center;">
                <a href="{{ $downloadUrl }}" class="button">Baixar Relatório (PDF)</a>
            </div>

            <p>Ou acesse o sistema para visualizar todos os detalhes.</p>
        </div>

        <div class="footer">
            <p>Este é um email automático, por favor não responda.</p>
            <p>&copy; {{ date('Y') }} Portal - Sistema de Gestão</p>
        </div>
    </div>
</body>
</html>
