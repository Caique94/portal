<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========================================\n";
echo "Admin Users:\n";
echo "========================================\n\n";

$admins = App\Models\User::where('papel', 'admin')->get(['id', 'name', 'email']);

foreach ($admins as $admin) {
    echo "ID: {$admin->id}\n";
    echo "Nome: {$admin->name}\n";
    echo "Email: {$admin->email}\n";
    echo "---\n";
}

echo "\n";
echo "Para resetar a senha do admin, você pode:\n";
echo "1. Usar: php artisan tinker\n";
echo "2. Executar: \$user = App\\Models\\User::find(ID_DO_ADMIN); \$user->password = bcrypt('nova_senha'); \$user->save();\n";
echo "\nOu vou criar um script para você fazer isso...\n";
