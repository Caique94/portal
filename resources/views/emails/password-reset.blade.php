<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .password-box { background: #f8f9fa; border: 2px dashed #667eea; border-radius: 8px; padding: 20px; text-align: center; margin: 25px 0; }
        .password-box .label { color: #666; font-size: 14px; margin-bottom: 10px; }
        .password-box .password { font-size: 24px; font-weight: bold; color: #667eea; letter-spacing: 2px; font-family: 'Courier New', monospace; }
        .info-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .info-box strong { color: #856404; }
        .button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Nova Senha Gerada</h1>
            <p style="margin: 5px 0 0 0;">Personalitec Soluções</p>
        </div>

        <div class="content">
            <p>Olá, <strong>{{ $user->name }}</strong>!</p>

            <p>Uma nova senha foi gerada para sua conta no Portal Personalitec.</p>

            <div class="password-box">
                <div class="label">Sua nova senha é:</div>
                <div class="password">{{ $newPassword }}</div>
            </div>

            <div class="info-box">
                <strong>⚠️ Importante:</strong> Por motivos de segurança, recomendamos que você altere esta senha assim que fizer login no sistema.
            </div>

            <p><strong>Dados para acesso:</strong></p>
            <ul>
                <li><strong>E-mail:</strong> {{ $user->email }}</li>
                <li><strong>Senha:</strong> {{ $newPassword }}</li>
            </ul>

            <p style="text-align: center;">
                <a href="{{ url('/login') }}" class="button">Acessar o Sistema</a>
            </p>

            <p><strong>Como alterar sua senha:</strong></p>
            <ol>
                <li>Faça login com as credenciais acima</li>
                <li>Clique no seu nome no canto superior direito</li>
                <li>Selecione "Alterar Senha"</li>
                <li>Digite sua senha atual e escolha uma nova senha</li>
            </ol>

            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 14px; color: #666;">
                Se você não solicitou esta alteração de senha, entre em contato com o administrador do sistema imediatamente.
            </p>
        </div>

        <div class="footer">
            <p><strong>Personalitec Soluções</strong></p>
            <p>Soluções personalizadas para o seu negócio</p>
            <p style="margin-top: 10px;">
                Este é um e-mail automático. Por favor, não responda.<br>
                Em caso de dúvidas, entre em contato com o suporte.
            </p>
            <p style="margin-top: 15px;">© {{ date('Y') }} Personalitec. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
