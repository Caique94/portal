<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Fechamento</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .greeting strong {
            color: #667eea;
        }

        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }

        .stat-card {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
            text-align: center;
        }

        .cta-button:hover {
            opacity: 0.9;
        }

        .message {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #2e7d32;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #e0e0e0;
        }

        .footer p {
            margin: 5px 0;
        }

        a {
            color: #667eea;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table th {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
            font-weight: bold;
            color: #2c3e50;
        }

        .table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .table tr:hover {
            background-color: #f9f9f9;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Relatório de Fechamento Aprovado</h1>
            <p>Relatório #{{ $relatorio->id }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Olá <strong>{{ $consultor->name }}</strong>,
            </div>

            <p>
                Seu relatório de fechamento foi <strong>aprovado pelo departamento de financeiro</strong>.
                Segue abaixo um resumo das informações:
            </p>

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-label">Período do Relatório</div>
                <div class="info-value">{{ $periodo_inicio }} a {{ $periodo_fim }}</div>
            </div>

            <!-- Statistics -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value">{{ $total_os }}</div>
                    <div class="stat-label">Ordens de Serviço</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">R$ {{ $valor_total }}</div>
                    <div class="stat-label">Valor Total</div>
                </div>
            </div>

            <!-- Success Message -->
            <div class="message">
                <strong>✓ Aprovado com Sucesso!</strong><br>
                Seu relatório foi revisado e aprovado. Você pode acessar os detalhes completos clicando no botão abaixo.
            </div>

            <!-- CTA Button -->
            <div class="text-center">
                <a href="{{ route('relatorio-fechamento.show', $relatorio) }}" class="cta-button">
                    Ver Detalhes Completos
                </a>
            </div>

            <!-- Footer Message -->
            <p style="margin-top: 30px; color: #666;">
                Se você tiver alguma dúvida sobre este relatório, favor entrar em contato com o departamento de financeiro.
            </p>

            <p style="color: #999; font-size: 12px;">
                <strong>Informações do Relatório:</strong><br>
                Data de Criação: {{ $relatorio->created_at->format('d/m/Y H:i') }}<br>
                Data de Aprovação: {{ $relatorio->data_aprovacao->format('d/m/Y H:i') }}<br>
                Aprovado por: {{ $relatorio->aprovador->name ?? '-' }}
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Portal Personalitec</strong></p>
            <p>Este é um email automático, favor não responder.</p>
            <p>&copy; {{ date('Y') }} Personalitec. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
