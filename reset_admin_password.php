<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========================================\n";
echo "Reset Admin Password\n";
echo "========================================\n\n";

// Nova senha
$newPassword = 'admin123';  // MUDE AQUI para a senha que você quer

$admin = App\Models\User::where('email', 'admin@example.com')->first();

if (!$admin) {
    echo "❌ Admin não encontrado!\n";
    exit(1);
}

$admin->password = bcrypt($newPassword);
$admin->save();

echo "✅ Senha do admin resetada com sucesso!\n\n";
echo "Credenciais:\n";
echo "Email: {$admin->email}\n";
echo "Senha: {$newPassword}\n";
echo "\n⚠️  IMPORTANTE: Mude a senha depois de fazer login!\n";
