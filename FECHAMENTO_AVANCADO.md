# Sistema Avançado de Fechamento

**Data de Implementação:** 10/12/2025
**Versão:** 1.0.0

## Sumário

1. [Visão Geral](#visão-geral)
2. [Arquitetura](#arquitetura)
3. [Tabelas do Banco de Dados](#tabelas-do-banco-de-dados)
4. [Models e Relacionamentos](#models-e-relacionamentos)
5. [Jobs Assíncronos](#jobs-assíncronos)
6. [API REST](#api-rest)
7. [Sistema de Agendamento](#sistema-de-agendamento)
8. [Templates de PDF](#templates-de-pdf)
9. [Sistema de Auditoria](#sistema-de-auditoria)
10. [Como Usar](#como-usar)
11. [Configuração em Produção](#configuração-em-produção)

---

## Visão Geral

O Sistema Avançado de Fechamento é uma solução completa para geração de relatórios de fechamento mensal, com suporte para:

- ✅ **Processamento Assíncrono**: Jobs em background usando Laravel Queue
- ✅ **Rastreamento de Status**: queued → processing → success/failed
- ✅ **Idempotência**: Previne criação de jobs duplicados
- ✅ **Versionamento**: Controle de versão para reprocessamentos
- ✅ **Auditoria Completa**: Log de todas as ações (gerar, baixar, reprocessar, etc.)
- ✅ **Agendamento Automático**: Cron expressions para execução recorrente
- ✅ **PDF Profissional**: Templates A4 com metadata e checksum SHA-256
- ✅ **Notificações**: Email automático quando PDF está pronto
- ✅ **API REST**: Endpoints para integração externa

---

## Arquitetura

```
┌─────────────────────────────────────────────────────────────────┐
│                          Frontend/API                            │
│  (Usuário solicita geração de fechamento via web ou API)        │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│              FechamentoJobController (API)                       │
│  - Valida dados                                                  │
│  - Verifica duplicidade (idempotência)                           │
│  - Cria FechamentoJob com status 'queued'                        │
│  - Dispara ProcessFechamentoJob                                  │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                 ProcessFechamentoJob                             │
│  1. Atualiza status para 'processing'                            │
│  2. Coleta dados do banco (ordens de serviço)                    │
│  3. Agrupa por cliente ou consultor                              │
│  4. Salva em FechamentoHistory (JSONB)                           │
│  5. Atualiza status para 'success'                               │
│  6. Dispara GeneratePdfJob                                       │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                    GeneratePdfJob                                │
│  1. Carrega dados do FechamentoHistory                           │
│  2. Gera PDF usando template (cliente ou consultor)              │
│  3. Calcula checksum SHA-256                                     │
│  4. Salva PDF em storage/app/public/fechamentos/                 │
│  5. Atualiza job com pdf_url e pdf_checksum                      │
│  6. Dispara NotifyUserJob                                        │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                     NotifyUserJob                                │
│  - Envia email para o usuário com link de download               │
│  - Registra auditoria da notificação                             │
└─────────────────────────────────────────────────────────────────┘
```

---

## Tabelas do Banco de Dados

### 1. `fechamento_jobs`

Tabela principal que armazena os jobs de processamento.

```sql
CREATE TABLE fechamento_jobs (
    id UUID PRIMARY KEY,
    type ENUM('client', 'consultant'),
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    filters JSONB NULL,
    requested_by INTEGER REFERENCES users(id) ON DELETE CASCADE,
    status ENUM('queued', 'processing', 'success', 'failed') DEFAULT 'queued',
    error_message TEXT NULL,
    pdf_url VARCHAR(255) NULL,
    pdf_checksum VARCHAR(64) NULL,
    version INTEGER DEFAULT 1,
    started_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_type_status (type, status),
    INDEX idx_requested_by_created (requested_by, created_at)
);
```

**Campos importantes:**
- `id`: UUID para identificação única global
- `type`: 'client' ou 'consultant' define o tipo de fechamento
- `filters`: JSONB para armazenar filtros específicos (cliente_id, consultor_id, etc.)
- `status`: Estado atual do job
- `version`: Incrementado a cada reprocessamento
- `pdf_checksum`: Hash SHA-256 do PDF para integridade

### 2. `fechamento_history`

Armazena os dados históricos de cada execução.

```sql
CREATE TABLE fechamento_history (
    id BIGSERIAL PRIMARY KEY,
    fechamento_job_id UUID REFERENCES fechamento_jobs(id) ON DELETE CASCADE,
    data JSONB NOT NULL,
    tag VARCHAR(100) NULL COMMENT 'agregacao_cliente, agregacao_consultor',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX idx_job_id (fechamento_job_id),
    INDEX idx_tag (tag)
);
```

**Exemplo de dados JSONB:**
```json
[
  {
    "cliente_id": 1,
    "cliente_nome": "Empresa XYZ",
    "total_ordens": 15,
    "total_horas": 120.5,
    "valor_total": 18075.00,
    "ordens": [
      {
        "id": 101,
        "produto": "Desenvolvimento",
        "consultor": "João Silva",
        "horas": 40,
        "preco_unitario": 150.00,
        "total": 6000.00
      }
    ]
  }
]
```

### 3. `fechamento_audit`

Trilha de auditoria completa de todas as ações.

```sql
CREATE TABLE fechamento_audit (
    id BIGSERIAL PRIMARY KEY,
    job_id UUID NULL REFERENCES fechamento_jobs(id) ON DELETE SET NULL,
    action VARCHAR(50) NOT NULL COMMENT 'gerar, baixar, reprocessar, aprovar, rejeitar, etc',
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    details JSONB NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_job_action (job_id, action),
    INDEX idx_user_timestamp (user_id, timestamp),
    INDEX idx_action (action)
);
```

**Ações rastreadas:**
- `gerar`: Criação inicial do job
- `processar`: Início do processamento
- `gerar_pdf`: Geração do PDF
- `baixar`: Download do PDF
- `reprocessar`: Reprocessamento do job
- `aprovar`: Aprovação do fechamento
- `rejeitar`: Rejeição do fechamento
- `notificar`: Envio de notificação
- `deletar`: Exclusão do job
- `erro`: Erro durante processamento
- `erro_pdf`: Erro na geração do PDF

### 4. `scheduled_tasks`

Tarefas agendadas com cron expressions.

```sql
CREATE TABLE scheduled_tasks (
    id BIGSERIAL PRIMARY KEY,
    type ENUM('client', 'consultant'),
    cron_expr VARCHAR(50) NOT NULL COMMENT 'Ex: 0 0 1 * * (todo dia 1)',
    filters JSONB NULL,
    last_run TIMESTAMP NULL,
    next_run TIMESTAMP NULL,
    enabled BOOLEAN DEFAULT TRUE,
    created_by INTEGER REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX idx_type (type),
    INDEX idx_enabled (enabled),
    INDEX idx_next_run (next_run),
    INDEX idx_type_enabled (type, enabled),
    INDEX idx_enabled_next_run (enabled, next_run)
);
```

**Cron expressions comuns:**
- `0 0 1 * *`: Todo dia 1 do mês à meia-noite (fechamento mensal)
- `0 0 * * 1`: Toda segunda-feira à meia-noite (fechamento semanal)
- `0 0 * * *`: Todo dia à meia-noite (fechamento diário)

---

## Models e Relacionamentos

### FechamentoJob

```php
class FechamentoJob extends Model
{
    use HasUuids;  // UUID primary key

    // Relationships
    public function requestedBy(): BelongsTo
    public function history(): HasMany
    public function audits(): HasMany

    // Scopes
    public function scopeByType($query, string $type)
    public function scopeByStatus($query, string $status)
    public function scopePending($query)

    // Helpers
    public function isProcessing(): bool
    public function isCompleted(): bool
}
```

### FechamentoHistory

```php
class FechamentoHistory extends Model
{
    // Casts
    protected $casts = ['data' => 'array'];

    // Relationships
    public function job(): BelongsTo

    // Scopes
    public function scopeByTag($query, string $tag)
}
```

### FechamentoAudit

```php
class FechamentoAudit extends Model
{
    public $timestamps = false;  // Usa apenas 'timestamp'

    // Casts
    protected $casts = [
        'details' => 'array',
        'timestamp' => 'datetime',
    ];

    // Relationships
    public function job(): BelongsTo
    public function user(): BelongsTo

    // Scopes
    public function scopeByAction($query, string $action)
    public function scopeByUser($query, int $userId)

    // Static helper
    public static function log(string $action, ?string $jobId, int $userId, ?array $details = null): void
}
```

**Uso da auditoria:**
```php
FechamentoAudit::log('gerar', $job->id, auth()->id(), [
    'type' => 'client',
    'period' => '01/11/2025 - 30/11/2025',
]);
```

### ScheduledTask

```php
class ScheduledTask extends Model
{
    // Casts
    protected $casts = [
        'filters' => 'array',
        'last_run' => 'datetime',
        'next_run' => 'datetime',
        'enabled' => 'boolean',
    ];

    // Relationships
    public function creator(): BelongsTo

    // Scopes
    public function scopeEnabled($query)
    public function scopeDue($query)

    // Helpers
    public function calculateNextRun(): void
    public function markAsRun(): void
    public function isDue(): bool
}
```

---

## Jobs Assíncronos

### 1. ProcessFechamentoJob

**Responsabilidades:**
- Coletar dados do banco de dados
- Agregar por cliente ou consultor
- Salvar em FechamentoHistory
- Atualizar status do job

**Configuração:**
- `$tries = 3`: Tenta até 3 vezes em caso de falha
- `$timeout = 600`: Timeout de 10 minutos

**Fluxo:**
```php
1. Busca FechamentoJob pelo ID
2. Atualiza status para 'processing'
3. Registra auditoria 'processar'
4. Coleta dados usando collectData()
   - Se type == 'client': collectClientData()
   - Se type == 'consultant': collectConsultantData()
5. Salva dados em FechamentoHistory
6. Atualiza status para 'success'
7. Dispara GeneratePdfJob
8. Em caso de erro:
   - Atualiza status para 'failed'
   - Salva erro em error_message
   - Registra auditoria 'erro'
```

### 2. GeneratePdfJob

**Responsabilidades:**
- Gerar PDF a partir dos dados do history
- Calcular checksum SHA-256
- Salvar PDF em storage
- Atualizar job com URL e checksum

**Configuração:**
- `$tries = 2`: Tenta até 2 vezes
- `$timeout = 300`: Timeout de 5 minutos

**Fluxo:**
```php
1. Busca FechamentoJob com history e requestedBy
2. Carrega dados do último history
3. Seleciona template (pdf-cliente ou pdf-consultor)
4. Gera PDF com Barryvdh\DomPDF
5. Calcula SHA-256 do conteúdo
6. Salva em storage/app/public/fechamentos/
7. Atualiza job com pdf_url e pdf_checksum
8. Registra auditoria 'gerar_pdf'
9. Dispara NotifyUserJob
```

**Formato do filename:**
```
fechamento_{type}_{period_month}_{job_id}_v{version}.pdf
Exemplo: fechamento_client_2025-11_123e4567-e89b-12d3-a456-426614174000_v1.pdf
```

### 3. NotifyUserJob

**Responsabilidades:**
- Enviar email de notificação
- Registrar auditoria da notificação

**Configuração:**
- `$tries = 2`: Tenta até 2 vezes
- `$timeout = 60`: Timeout de 1 minuto

**Template de email:** `resources/views/emails/fechamento-ready.blade.php`

---

## API REST

Base URL: `/api/fechamentos`

### Endpoints

#### 1. GET /api/fechamentos
Lista todos os jobs com filtros opcionais.

**Query Parameters:**
- `type`: string (client|consultant)
- `status`: string (queued|processing|success|failed)
- `user_id`: integer
- `per_page`: integer (default: 15)

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": "123e4567-e89b-12d3-a456-426614174000",
      "type": "client",
      "period_start": "2025-11-01",
      "period_end": "2025-11-30",
      "status": "success",
      "pdf_url": "fechamentos/fechamento_client_2025-11_..._v1.pdf",
      "version": 1,
      "requested_by": {
        "id": 1,
        "name": "Admin"
      },
      "created_at": "2025-12-10T14:30:00.000000Z"
    }
  ],
  "total": 10,
  "per_page": 15
}
```

#### 2. POST /api/fechamentos
Cria um novo job de fechamento.

**Request Body:**
```json
{
  "type": "client",
  "period_start": "2025-11-01",
  "period_end": "2025-11-30",
  "filters": {
    "cliente_id": 5
  }
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Job criado com sucesso",
  "job": {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "type": "client",
    "status": "queued",
    ...
  }
}
```

**Response (409 Conflict) - Job duplicado:**
```json
{
  "success": false,
  "message": "Um job idêntico já está em processamento",
  "job_id": "existing-uuid"
}
```

#### 3. GET /api/fechamentos/{id}
Retorna detalhes de um job específico.

**Response:**
```json
{
  "success": true,
  "job": {
    "id": "...",
    "type": "client",
    "status": "success",
    "requested_by": {...},
    "history": [
      {
        "id": 1,
        "data": [{...}],
        "tag": "agregacao_cliente",
        "created_at": "..."
      }
    ],
    "audits": [
      {
        "id": 1,
        "action": "gerar",
        "user": {...},
        "details": {...},
        "timestamp": "..."
      }
    ]
  }
}
```

#### 4. POST /api/fechamentos/{id}/reprocess
Reprocessa um job (incrementa versão).

**Response:**
```json
{
  "success": true,
  "message": "Job reenfileirado para reprocessamento",
  "job": {
    "id": "...",
    "status": "queued",
    "version": 2
  }
}
```

#### 5. GET /api/fechamentos/{id}/download
Faz download do PDF e registra auditoria.

**Response:** Arquivo PDF (application/pdf)

#### 6. DELETE /api/fechamentos/{id}
Deleta o job e o arquivo PDF associado.

**Response:**
```json
{
  "success": true,
  "message": "Job deletado com sucesso"
}
```

---

## Sistema de Agendamento

### Command: fechamento:execute-scheduled

**Descrição:** Executa tarefas agendadas que estão vencidas.

**Uso:**
```bash
# Executar todas as tarefas vencidas
php artisan fechamento:execute-scheduled

# Executar tarefa específica
php artisan fechamento:execute-scheduled --task-id=1

# Modo dry-run (simular sem executar)
php artisan fechamento:execute-scheduled --dry-run
```

**Output exemplo:**
```
Checking for due scheduled tasks...
Found 2 task(s) to execute.
Processing task #1: client fechamento
✓ Created job 123e4567-... for task #1
  Period: 01/11/2025 - 30/11/2025
  Next run: 01/01/2026 00:00
Processing task #2: consultant fechamento
✓ Created job 987f6543-... for task #2
  Period: 01/11/2025 - 30/11/2025
  Next run: 01/01/2026 00:00
All due tasks have been processed.
```

### Agendamento no Laravel

**Arquivo:** `routes/console.php`

```php
Schedule::command('fechamento:execute-scheduled')->everyMinute();
```

### Configuração do Cron no Servidor

Adicionar ao crontab:

```cron
* * * * * cd /var/www/portal && php artisan schedule:run >> /dev/null 2>&1
```

### Criar Tarefa Agendada

```php
use App\Models\ScheduledTask;

$task = ScheduledTask::create([
    'type' => 'client',
    'cron_expr' => '0 0 1 * *',  // Todo dia 1 do mês
    'filters' => null,  // Sem filtros = todos os clientes
    'enabled' => true,
    'created_by' => auth()->id(),
]);

$task->calculateNextRun();
```

---

## Templates de PDF

### Template Cliente: `pdf-cliente.blade.php`

**Características:**
- Cor primária: Verde (#4CAF50)
- Agrupamento por cliente
- Colunas: ID, Produto, Consultor, Horas, Preço/Hora, Total
- Seções com totais por cliente
- Total geral no final

**Metadata incluído:**
- Período
- Tipo
- Gerado por
- Data/hora de geração
- Versão
- Hash SHA-256

### Template Consultor: `pdf-consultor.blade.php`

**Características:**
- Cor primária: Azul (#2196F3)
- Agrupamento por consultor
- Colunas: ID, Cliente, Produto, Horas, Valor/Hora, Total
- Seções com totais por consultor
- Total geral no final

**Formato A4:**
- Margens: 2cm (topo/baixo), 1.5cm (lados)
- Font: Arial, 10pt
- Rodapé com número de página
- Header com bordas e cores

---

## Sistema de Auditoria

### Logs Automáticos

O sistema registra automaticamente as seguintes ações:

| Ação | Quando | Detalhes |
|------|--------|----------|
| `gerar` | Criação do job | type, period |
| `processar` | Início do processamento | type, period |
| `gerar_pdf` | PDF gerado | filename, checksum, size |
| `baixar` | Download do PDF | filename |
| `reprocessar` | Reprocessamento | old_version, new_version |
| `aprovar` | Aprovação manual | - |
| `rejeitar` | Rejeição manual | motivo |
| `notificar` | Email enviado | email, tipo |
| `deletar` | Job deletado | - |
| `erro` | Erro no processamento | error message |
| `erro_pdf` | Erro na geração do PDF | error message |

### Consultar Auditoria

```php
// Todas as ações de um job
$audits = FechamentoAudit::where('job_id', $jobId)
    ->with('user')
    ->orderBy('timestamp', 'desc')
    ->get();

// Todas as ações de um usuário
$userAudits = FechamentoAudit::where('user_id', $userId)
    ->byAction('baixar')
    ->get();

// Downloads nos últimos 7 dias
$downloads = FechamentoAudit::byAction('baixar')
    ->where('timestamp', '>=', now()->subDays(7))
    ->count();
```

---

## Como Usar

### 1. Via Interface Web (Existente)

O sistema existente de fechamento (`/relatorio-fechamento-cliente` e `/relatorio-fechamento-consultor`) pode ser integrado para usar o novo sistema de jobs.

**Exemplo de integração no controller:**

```php
use App\Models\FechamentoJob;
use App\Jobs\ProcessFechamentoJob;
use Illuminate\Support\Str;

public function store(Request $request)
{
    $job = FechamentoJob::create([
        'id' => (string) Str::uuid(),
        'type' => $request->tipo === 'cliente' ? 'client' : 'consultant',
        'period_start' => $request->data_inicio,
        'period_end' => $request->data_fim,
        'filters' => [
            'cliente_id' => $request->cliente_id,
            'consultor_id' => $request->consultor_id,
        ],
        'requested_by' => auth()->id(),
        'status' => 'queued',
    ]);

    ProcessFechamentoJob::dispatch($job->id);

    return redirect()->route('fechamento-jobs.show', $job->id)
        ->with('success', 'Relatório em processamento. Você receberá um email quando estiver pronto.');
}
```

### 2. Via API REST

```bash
# Criar job
curl -X POST http://localhost/api/fechamentos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "type": "client",
    "period_start": "2025-11-01",
    "period_end": "2025-11-30"
  }'

# Verificar status
curl http://localhost/api/fechamentos/{job_id} \
  -H "Authorization: Bearer {token}"

# Download PDF
curl http://localhost/api/fechamentos/{job_id}/download \
  -H "Authorization: Bearer {token}" \
  -o relatorio.pdf
```

### 3. Via Command Line

```bash
# Criar job manualmente (em tinker)
php artisan tinker

>>> $job = \App\Models\FechamentoJob::create([
...   'id' => (string) \Illuminate\Support\Str::uuid(),
...   'type' => 'client',
...   'period_start' => '2025-11-01',
...   'period_end' => '2025-11-30',
...   'requested_by' => 1,
...   'status' => 'queued',
... ]);

>>> \App\Jobs\ProcessFechamentoJob::dispatch($job->id);
```

### 4. Agendamento Automático

```php
// Criar tarefa para executar todo dia 1 do mês
$task = ScheduledTask::create([
    'type' => 'client',
    'cron_expr' => '0 0 1 * *',
    'filters' => null,  // Todos os clientes
    'enabled' => true,
    'created_by' => auth()->id(),
]);

$task->calculateNextRun();
```

---

## Configuração em Produção

### 1. Queue Worker

**Iniciar queue worker:**
```bash
php artisan queue:work --tries=3 --timeout=900
```

**Supervisor config** (`/etc/supervisor/conf.d/portal-queue.conf`):
```ini
[program:portal-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/portal/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/portal/storage/logs/queue-worker.log
stopwaitsecs=3600
```

**Reiniciar supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start portal-queue:*
```

### 2. Scheduler (Cron)

**Adicionar ao crontab do servidor:**
```bash
sudo crontab -e -u www-data
```

```cron
* * * * * cd /var/www/portal && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Storage Permissions

```bash
# Criar diretório para PDFs
mkdir -p storage/app/public/fechamentos

# Criar symlink
php artisan storage:link

# Ajustar permissões
chown -R www-data:www-data storage/
chmod -R 775 storage/
```

### 4. Email Configuration

**Arquivo:** `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@portal.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Database Indexes

Certifique-se de que as migrations foram executadas:

```bash
php artisan migrate
```

### 6. Monitoramento

**Verificar status dos jobs:**
```bash
# Contagem por status
php artisan tinker
>>> DB::table('fechamento_jobs')->select('status', DB::raw('count(*) as total'))
...   ->groupBy('status')->get();
```

**Logs:**
```bash
# Queue worker
tail -f storage/logs/queue-worker.log

# Laravel
tail -f storage/logs/laravel.log

# Jobs com erro
php artisan tinker
>>> \App\Models\FechamentoJob::where('status', 'failed')->get();
```

---

## Troubleshooting

### Job fica preso em 'processing'

**Causa:** Queue worker travou ou foi interrompido.

**Solução:**
```bash
# Reiniciar queue worker
sudo supervisorctl restart portal-queue:*

# Ou reprocessar manualmente
php artisan tinker
>>> $job = \App\Models\FechamentoJob::find('uuid-here');
>>> $job->update(['status' => 'queued']);
>>> \App\Jobs\ProcessFechamentoJob::dispatch($job->id);
```

### PDF não é gerado

**Verificar:**
1. Permissões do diretório `storage/app/public/fechamentos/`
2. DomPDF está instalado: `composer require barryvdh/laravel-dompdf`
3. Logs em `storage/logs/laravel.log`

### Email não é enviado

**Verificar:**
1. Configuração SMTP no `.env`
2. Usuário tem email cadastrado
3. Logs de email: `tail -f storage/logs/laravel.log | grep -i mail`

### Tarefas agendadas não executam

**Verificar:**
1. Cron está configurado: `crontab -l -u www-data`
2. Scheduler está rodando: `php artisan schedule:list`
3. Next run está no passado: `ScheduledTask::due()->get()`

---

## Próximos Passos / Melhorias Futuras

- [ ] Dashboard com estatísticas de jobs
- [ ] Webhooks para notificação externa
- [ ] Suporte para múltiplos formatos (Excel, CSV)
- [ ] Compressão de PDFs antigos
- [ ] Retenção automática (deletar jobs antigos)
- [ ] API de webhook para integração com outros sistemas
- [ ] Interface web para gerenciar scheduled_tasks
- [ ] Notificações in-app (além de email)
- [ ] Rate limiting na API
- [ ] Autenticação via API token

---

## Suporte

Para dúvidas ou problemas:
1. Consulte os logs: `storage/logs/laravel.log`
2. Verifique a auditoria: `FechamentoAudit::latest()->get()`
3. Consulte este documento

---

**Desenvolvido com Claude Code**
Data: 10/12/2025
