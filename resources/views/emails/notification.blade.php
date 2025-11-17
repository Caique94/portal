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
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            margin: -20px -20px 20px -20px;
        }
        h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 15px;
            margin: 20px -20px -20px -20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 5px 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $notification->title }}</h1>
        </div>

        <div class="content">
            <p>{{ $notification->message }}</p>

            @if($notification->data && isset($notification->data['cliente']))
            <div class="info">
                <span class="label">Cliente:</span> {{ $notification->data['cliente'] }}
            </div>
            @endif

            @if($notification->data && isset($notification->data['valor']))
            <div class="info">
                <span class="label">Valor:</span> R$ {{ number_format($notification->data['valor'], 2, ',', '.') }}
            </div>
            @endif

            @if($notification->data && isset($notification->data['motivo']))
            <div class="info">
                <span class="label">Motivo:</span> {{ $notification->data['motivo'] }}
            </div>
            @endif

            @if($notification->action_url)
            <div style="margin: 20px 0; text-align: center;">
                <a href="{{ $notification->action_url }}" class="button">Ver Detalhes</a>
            </div>
            @endif
        </div>

        <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 20px; font-size: 12px; color: #666;">
            <p>
                <strong>Tipo de Notificação:</strong> {{ ucfirst(str_replace('_', ' ', $notification->type)) }}<br>
                <strong>Data:</strong> {{ $notification->created_at->format('d/m/Y H:i') }}
            </p>
        </div>

        <div class="footer">
            <p>© Portal Personalitec Soluções</p>
            <p>Esta é uma notificação automática do sistema</p>
        </div>
    </div>
</body>
</html>
