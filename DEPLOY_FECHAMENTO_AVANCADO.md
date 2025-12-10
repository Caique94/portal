# Deploy do Sistema Avançado de Fechamento

**Servidor:** sistemasemteste.com.br
**Data:** 10/12/2025

---

## Passo 1: Conectar no Servidor

```bash
ssh root@sistemasemteste.com.br
# Senha: (sua senha)
```

---

## Passo 2: Navegar para o Diretório e Pull

```bash
cd /var/www/sistemasemteste
git pull origin main
```

**Commits incluídos:**
- feat: Implement advanced fechamento system with job processing and scheduling
- docs: Add comprehensive documentation for advanced fechamento system

---

## Passo 3: Executar Migrations

```bash
php artisan migrate
```

**Tabelas criadas:**
- `fechamento_jobs` (UUID, status, PDF, version)
- `fechamento_history` (JSONB data storage)
- `fechamento_audit` (audit trail)
- `scheduled_tasks` (cron scheduling)

**Verificar se as migrations rodaram:**
```bash
php artisan tinker
>>> DB::table('migrations')->where('migration', 'like', '%fechamento%')->get();
>>> exit
```

---

## Passo 4: Instalar Dependências (se necessário)

### Verificar se DomPDF está instalado:
```bash
composer show barryvdh/laravel-dompdf
```

**Se não estiver instalado:**
```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### Verificar se dragonmantank/cron-expression está instalado:
```bash
composer show dragonmantank/cron-expression
```

**Se não estiver instalado:**
```bash
composer require dragonmantank/cron-expression
```

---

## Passo 5: Criar Diretórios e Ajustar Permissões

```bash
# Criar diretório para PDFs
mkdir -p storage/app/public/fechamentos

# Criar symlink (se ainda não existir)
php artisan storage:link

# Ajustar permissões
chown -R www-data:www-data storage/ bootstrap/cache/
chmod -R 775 storage/ bootstrap/cache/

# Verificar permissões
ls -la storage/app/public/
```

---

## Passo 6: Configurar Queue Worker com Supervisor

### 6.1 Verificar se Supervisor está instalado:
```bash
supervisorctl status
```

**Se não estiver instalado:**
```bash
apt-get update
apt-get install supervisor
systemctl enable supervisor
systemctl start supervisor
```

### 6.2 Criar arquivo de configuração:
```bash
nano /etc/supervisor/conf.d/sistemasemteste-queue.conf
```

**Conteúdo do arquivo:**
```ini
[program:sistemasemteste-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sistemasemteste/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sistemasemteste/storage/logs/queue-worker.log
stopwaitsecs=3600
```

### 6.3 Ativar configuração:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start sistemasemteste-queue:*
```

### 6.4 Verificar status:
```bash
supervisorctl status sistemasemteste-queue:*
```

**Output esperado:**
```
sistemasemteste-queue:sistemasemteste-queue_00   RUNNING   pid 12345, uptime 0:00:05
sistemasemteste-queue:sistemasemteste-queue_01   RUNNING   pid 12346, uptime 0:00:05
```

---

## Passo 7: Configurar Cron para Scheduler

### 7.1 Editar crontab:
```bash
crontab -e -u www-data
```

### 7.2 Adicionar linha:
```cron
* * * * * cd /var/www/sistemasemteste && php artisan schedule:run >> /dev/null 2>&1
```

### 7.3 Verificar crontab:
```bash
crontab -l -u www-data
```

### 7.4 Testar comando de agendamento:
```bash
sudo -u www-data php artisan fechamento:execute-scheduled --dry-run
```

**Output esperado:**
```
Checking for due scheduled tasks...
No tasks are due for execution.
```

---

## Passo 8: Configurar Email (se ainda não configurado)

```bash
nano .env
```

**Verificar/adicionar configurações de email:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@sistemasemteste.com.br
MAIL_FROM_NAME="${APP_NAME}"
```

**Testar envio de email:**
```bash
php artisan tinker
>>> Mail::raw('Teste do sistema de fechamento', function($msg) {
...   $msg->to('seu-email@teste.com')->subject('Teste');
... });
>>> exit
```

---

## Passo 9: Limpar Caches

```bash
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize
```

---

## Passo 10: Verificar Instalação

### 10.1 Verificar tabelas criadas:
```bash
php artisan tinker
>>> \Schema::hasTable('fechamento_jobs');
>>> \Schema::hasTable('fechamento_history');
>>> \Schema::hasTable('fechamento_audit');
>>> \Schema::hasTable('scheduled_tasks');
>>> exit
```

**Todas devem retornar `true`**

### 10.2 Verificar rotas da API:
```bash
php artisan route:list --path=api/fechamentos
```

**Output esperado:**
```
GET|HEAD   api/fechamentos ................ api.fechamentos.index
POST       api/fechamentos ................ api.fechamentos.store
GET|HEAD   api/fechamentos/{id} ........... api.fechamentos.show
POST       api/fechamentos/{id}/reprocess . api.fechamentos.reprocess
GET|HEAD   api/fechamentos/{id}/download .. api.fechamentos.download
DELETE     api/fechamentos/{id} ........... api.fechamentos.destroy
```

### 10.3 Verificar Queue Worker:
```bash
supervisorctl status sistemasemteste-queue:*
tail -f storage/logs/queue-worker.log
```

### 10.4 Verificar comandos artisan:
```bash
php artisan list | grep fechamento
```

**Output esperado:**
```
fechamento:execute-scheduled  Execute scheduled fechamento tasks that are due
```

---

## Passo 11: Teste Funcional Completo

### 11.1 Criar um job via API (teste):
```bash
php artisan tinker
```

```php
use App\Models\FechamentoJob;
use App\Jobs\ProcessFechamentoJob;
use Illuminate\Support\Str;

$job = FechamentoJob::create([
    'id' => (string) Str::uuid(),
    'type' => 'client',
    'period_start' => '2025-11-01',
    'period_end' => '2025-11-30',
    'filters' => null,
    'requested_by' => 1, // ID do seu usuário admin
    'status' => 'queued',
]);

echo "Job criado: {$job->id}\n";

ProcessFechamentoJob::dispatch($job->id);

echo "Job despachado para processamento!\n";
exit
```

### 11.2 Monitorar processamento:
```bash
# Em um terminal, monitorar o log
tail -f storage/logs/laravel.log

# Em outro terminal, verificar status do job
php artisan tinker
>>> $job = \App\Models\FechamentoJob::latest()->first();
>>> echo $job->status;
>>> exit
```

**Status esperado:**
- Inicial: `queued`
- Processando: `processing`
- Final: `success` (ou `failed` se houver erro)

### 11.3 Verificar PDF gerado:
```bash
ls -lh storage/app/public/fechamentos/
```

### 11.4 Verificar auditoria:
```bash
php artisan tinker
>>> \App\Models\FechamentoAudit::latest()->get(['action', 'user_id', 'timestamp']);
>>> exit
```

**Ações esperadas:**
- `gerar`
- `processar`
- `gerar_pdf`
- `notificar`

---

## Passo 12: Criar Tarefa Agendada (Opcional)

Se quiser que os fechamentos sejam gerados automaticamente todo dia 1 do mês:

```bash
php artisan tinker
```

```php
use App\Models\ScheduledTask;

$task = ScheduledTask::create([
    'type' => 'client',
    'cron_expr' => '0 0 1 * *', // Todo dia 1 às 00:00
    'filters' => null,
    'enabled' => true,
    'created_by' => 1,
]);

$task->calculateNextRun();

echo "Tarefa criada! Próxima execução: {$task->next_run}\n";
exit
```

**Testar execução da tarefa:**
```bash
php artisan fechamento:execute-scheduled --dry-run
```

---

## Troubleshooting

### Erro: "Queue connection does not exist"
```bash
# Verificar configuração da fila no .env
nano .env
```

Adicionar/verificar:
```env
QUEUE_CONNECTION=database
```

Criar tabela de jobs:
```bash
php artisan queue:table
php artisan migrate
```

### Erro: "Class 'Barryvdh\DomPDF\Facade\Pdf' not found"
```bash
composer require barryvdh/laravel-dompdf
php artisan optimize:clear
```

### Erro: "Class 'Cron\CronExpression' not found"
```bash
composer require dragonmantank/cron-expression
```

### Queue worker não processa jobs
```bash
# Verificar se está rodando
supervisorctl status sistemasemteste-queue:*

# Reiniciar
supervisorctl restart sistemasemteste-queue:*

# Ver logs
tail -f storage/logs/queue-worker.log
```

### Scheduler não executa tarefas
```bash
# Verificar cron
crontab -l -u www-data

# Testar manualmente
sudo -u www-data php artisan schedule:run

# Ver logs
tail -f storage/logs/laravel.log
```

### Permissões de arquivo
```bash
# Corrigir permissões
chown -R www-data:www-data storage/ bootstrap/cache/
chmod -R 775 storage/ bootstrap/cache/

# Verificar
sudo -u www-data touch storage/test.txt
rm storage/test.txt
```

---

## Monitoramento Pós-Deploy

### Logs importantes:
```bash
# Laravel
tail -f storage/logs/laravel.log

# Queue Worker
tail -f storage/logs/queue-worker.log

# Nginx
tail -f /var/log/nginx/error.log

# PHP-FPM
tail -f /var/log/php8.3-fpm.log
```

### Verificações periódicas:
```bash
# Status do queue worker
supervisorctl status

# Jobs pendentes
php artisan tinker
>>> \App\Models\FechamentoJob::whereIn('status', ['queued', 'processing'])->count();

# Jobs com erro
>>> \App\Models\FechamentoJob::where('status', 'failed')->latest()->get(['id', 'error_message', 'created_at']);

# Espaço em disco (PDFs)
du -sh storage/app/public/fechamentos/
```

---

## Checklist Final

- [ ] Git pull executado com sucesso
- [ ] Migrations rodaram sem erro
- [ ] Dependências instaladas (dompdf, cron-expression)
- [ ] Diretório `storage/app/public/fechamentos/` criado
- [ ] Permissões ajustadas (www-data)
- [ ] Supervisor configurado e rodando
- [ ] Cron configurado
- [ ] Email configurado (opcional)
- [ ] Caches limpos
- [ ] Rotas API funcionando
- [ ] Teste funcional completo realizado
- [ ] Job de teste processou com sucesso
- [ ] PDF foi gerado
- [ ] Auditoria registrando ações

---

## Comandos Úteis

```bash
# Ver status geral
supervisorctl status
crontab -l -u www-data
php artisan queue:work --once  # Processar um job

# Estatísticas
php artisan tinker
>>> DB::table('fechamento_jobs')->select('status', DB::raw('count(*) as total'))->groupBy('status')->get();
>>> DB::table('fechamento_audit')->select('action', DB::raw('count(*) as total'))->groupBy('action')->get();

# Limpar jobs antigos (cuidado!)
>>> \App\Models\FechamentoJob::where('created_at', '<', now()->subMonths(3))->delete();
```

---

## Rollback (se necessário)

```bash
# Parar queue worker
supervisorctl stop sistemasemteste-queue:*

# Reverter migrations
php artisan migrate:rollback --step=4

# Voltar código
git revert HEAD~2..HEAD
git push

# Limpar caches
php artisan optimize:clear
```

---

**Pronto para Deploy! 🚀**
