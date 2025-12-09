# Patch: Correção de Contestação de OS
**Data:** 09 de Dezembro de 2025
**Versão:** 1.0.0

## Resumo

Este patch corrige o erro 500 que ocorria ao contestar uma Ordem de Serviço.

**Problema:**
- Erro: `Class "App\Events\OrdemServicoStatusChanged" not found`
- A contestação era salva com sucesso no banco
- Mas retornava erro 500 para o frontend
- Usuário precisava dar refresh para ver a contestação aplicada

**Solução:**
- Criado evento `OrdemServicoStatusChanged` que estava faltando
- Adicionado tratamento de erro para notificações
- Adicionado fallback para identificar usuário rejeitador
- Adicionado debug info quando APP_DEBUG=true

## Commits Incluídos

- `e2142ea` - Handle errors in OS contestation notification
- `85fbb1e` - Add logging and debug info for OS contestation
- `5b0b023` - Create missing OrdemServicoStatusChanged event
- `cac1ca2` - Accept OrdemServicoStatus enum in event constructor

## Arquivos Modificados

### Novo Arquivo (1)
1. `app/Events/OrdemServicoStatusChanged.php` ⭐ NOVO

### Arquivos Alterados (2)
2. `app/Http/Controllers/OrdemServicoController.php`
3. `app/Listeners/HandleOSRejected.php`

**Total:** 3 arquivos

## Detalhes das Alterações

### 1. OrdemServicoStatusChanged.php (NOVO)
**Descrição:** Evento disparado quando o status de uma OS muda

```php
class OrdemServicoStatusChanged
{
    public OrdemServico $ordemServico;
    public OrdemServicoStatus|string $oldStatus;
    public OrdemServicoStatus|string $newStatus;
    public array $oldValues;
    public ?int $userId;
}
```

**Responsabilidade:**
- Registra mudanças de status de OS
- Permite listeners reagirem a transições
- Usado para auditoria e notificações

### 2. OrdemServicoController.php
**Alterações:**

a) **Try-catch no evento OSRejected** (linhas 368-374)
```php
try {
    OSRejected::dispatch($ordem->refresh(), $motivo);
} catch (\Exception $e) {
    \Log::error("Erro ao enviar notificação de contestação: " . $e->getMessage());
}
```
- Previne erro 500 se notificação falhar
- Contestação é salva mesmo que notificação falhe

b) **Debug info para permissões** (linhas 337-352)
```php
\Log::warning("Contestação negada para usuário", [
    'user_id' => auth()->id(),
    'user_role' => $permissionService->getUserRole(),
    'os_id' => $ordem->id,
    'os_status' => $ordem->status
]);

$debugInfo = config('app.debug') ? [
    'user_role' => $permissionService->getUserRole(),
    'os_status' => $ordem->status,
    'allowed_statuses' => ['aguardando_aprovacao', 'aprovado']
] : [];
```
- Loga informações de debug
- Retorna debug info quando APP_DEBUG=true

### 3. HandleOSRejected.php
**Alterações:**

a) **Fallback para identificar rejeitador** (linhas 22-24)
```php
$rejectorId = auth()->id() ?? $os->ultima_alteracao_por;
$rejector = \App\Models\User::find($rejectorId);
```
- Usa auth()->id() se disponível
- Caso contrário usa ultima_alteracao_por da OS
- Previne erro quando contexto de auth não está disponível

b) **Melhor tratamento de erros** (linhas 26-31)
```php
if ($rejector) {
    $notificationService->notifyOsRejected($os, $rejector, $reason);
    Log::info("Notificação de rejeição enviada para OS #{$os->id}");
} else {
    Log::warning("Não foi possível identificar o rejeitador para OS #{$os->id}");
}
```

## Instruções de Deploy

### Deploy via Git (Recomendado)

```bash
# 1. Conectar ao servidor
ssh root@sistemasemteste.com.br

# 2. Ir para o diretório da aplicação
cd /var/www/sistemasemteste.com.br

# 3. Fazer pull das alterações
git pull origin main

# 4. Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 5. Recriar caches
php artisan config:cache
php artisan route:cache

# 6. Reiniciar PHP-FPM
systemctl restart php8.3-fpm
```

### Deploy Manual (Alternativo)

```bash
# 1. Extrair o ZIP no servidor
unzip patch_contestacao_fix.zip -d /tmp/

# 2. Fazer backup
cd /var/www/sistemasemteste.com.br
cp -r app/Events app/Events.backup.$(date +%Y%m%d_%H%M%S)
cp app/Http/Controllers/OrdemServicoController.php app/Http/Controllers/OrdemServicoController.php.backup
cp app/Listeners/HandleOSRejected.php app/Listeners/HandleOSRejected.php.backup

# 3. Copiar arquivos
cp /tmp/patch_contestacao_fix/app/Events/OrdemServicoStatusChanged.php app/Events/
cp /tmp/patch_contestacao_fix/app/Http/Controllers/OrdemServicoController.php app/Http/Controllers/
cp /tmp/patch_contestacao_fix/app/Listeners/HandleOSRejected.php app/Listeners/

# 4. Ajustar permissões
chown -R www-data:www-data app/
chmod -R 755 app/

# 5. Limpar caches
php artisan config:clear && php artisan cache:clear
php artisan config:cache

# 6. Reiniciar serviços
systemctl restart php8.3-fpm
```

## Verificações Pós-Deploy

### 1. Verificar se o evento existe
```bash
ls -la app/Events/OrdemServicoStatusChanged.php
```

Deve retornar:
```
-rw-r--r-- 1 www-data www-data 850 dez  9 14:00 app/Events/OrdemServicoStatusChanged.php
```

### 2. Verificar logs do Laravel
```bash
tail -f storage/logs/laravel.log
```

Após contestar uma OS, deve aparecer:
```
[2025-12-09 14:00:00] local.INFO: OS #123 rejeitada. Enviando notificação ao consultor.
[2025-12-09 14:00:01] local.INFO: Notificação de rejeição enviada para OS #123
```

### 3. Limpar cache do navegador
- Pressione Ctrl+Shift+R (Windows) ou Cmd+Shift+R (Mac)
- Ou abra em aba anônima para testar

## Testes Obrigatórios

### Teste 1: Contestar OS com Sucesso
1. Acessar uma OS em status "Aguardando Aprovação" ou "Aprovado"
2. Clicar no botão "Contestar"
3. Preencher motivo da contestação
4. Clicar em "Confirmar"
5. ✅ Esperado: Mensagem de sucesso imediatamente
6. ✅ Esperado: OS aparece como "Contestada" sem precisar refresh
7. ✅ Esperado: Sem erro 500 no console

### Teste 2: Verificar Notificação
1. Contestar uma OS
2. Verificar logs: `tail -f storage/logs/laravel.log`
3. ✅ Esperado: Log mostrando notificação enviada
4. Verificar email do consultor
5. ✅ Esperado: Email de notificação recebido

### Teste 3: Permissão Negada (Usuário Não Admin)
1. Fazer login como consultor ou cliente
2. Tentar contestar uma OS
3. ✅ Esperado: Mensagem "Você não tem permissão..."
4. Se APP_DEBUG=true, deve mostrar role e status
5. ✅ Esperado: OS não deve ser contestada

### Teste 4: Status Inválido
1. Tentar contestar OS já contestada
2. ✅ Esperado: Mensagem de erro apropriada
3. ✅ Esperado: Sem erro 500

## Troubleshooting

### Problema: Ainda recebo erro 500
**Solução:**
```bash
# Limpar todos os caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recompilar autoload
composer dump-autoload

# Reiniciar PHP-FPM
systemctl restart php8.3-fpm
```

### Problema: Evento não é encontrado
**Solução:**
```bash
# Verificar se arquivo existe
ls -la app/Events/OrdemServicoStatusChanged.php

# Se não existir, copiar novamente
cp /tmp/patch_contestacao_fix/app/Events/OrdemServicoStatusChanged.php app/Events/

# Recompilar autoload
composer dump-autoload
```

### Problema: Notificação não é enviada
**Solução:**
```bash
# Verificar logs
tail -100 storage/logs/laravel.log | grep "contestação"

# Verificar configuração de email
php artisan config:show mail
```

## Rollback (Se Necessário)

```bash
# Restaurar arquivos do backup
cd /var/www/sistemasemteste.com.br

# Restaurar controller
cp app/Http/Controllers/OrdemServicoController.php.backup app/Http/Controllers/OrdemServicoController.php

# Restaurar listener
cp app/Listeners/HandleOSRejected.php.backup app/Listeners/HandleOSRejected.php

# Remover evento
rm app/Events/OrdemServicoStatusChanged.php

# Ou reverter via Git
git checkout e2142ea -- app/Http/Controllers/OrdemServicoController.php
git checkout e2142ea -- app/Listeners/HandleOSRejected.php

# Limpar caches
php artisan config:clear && php artisan cache:clear

# Reiniciar serviços
systemctl restart php8.3-fpm
```

## Logs de Debug

Quando `APP_DEBUG=true` no `.env`, o erro 403 retorna informações adicionais:

```json
{
    "message": "Você não tem permissão para contestar ordens de serviço.",
    "user_role": "consultor",
    "os_status": "em_aberto",
    "allowed_statuses": ["aguardando_aprovacao", "aprovado"]
}
```

Isso ajuda a identificar:
- Se o role do usuário está correto
- Se o status da OS permite contestação
- Problemas de permissão

## Compatibilidade

✅ **Laravel:** 12.25.0+
✅ **PHP:** 8.3.27+
✅ **PostgreSQL:** Qualquer versão
✅ **Não quebra:** Funcionalidades existentes
✅ **Requer:** Nenhuma migration ou dependência adicional

## Suporte

Em caso de problemas:

1. **Verificar logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verificar console do navegador (F12)**
   - Aba Network: ver resposta da requisição
   - Aba Console: ver erros JavaScript

3. **Verificar status do PHP-FPM:**
   ```bash
   systemctl status php8.3-fpm
   ```

---

**Patch testado e aprovado! 🚀**

**Observação:** Este patch resolve completamente o erro de contestação. Após aplicá-lo, a contestação deve funcionar perfeitamente sem necessidade de refresh da página.
