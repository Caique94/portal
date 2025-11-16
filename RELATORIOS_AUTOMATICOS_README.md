# Sistema de Relatórios Automáticos de OS

## Descrição
Sistema completo de geração e envio automático de relatórios de Ordem de Serviço (OS) por e-mail, implementado em Laravel. Os relatórios são enviados **somente após a aprovação** da OS.

## Funcionalidades Implementadas

### ✅ Controle de Aprovação
- Campo `approval_status` com estados: `pending`, `approved`, `rejected`
- Campos `approved_at` e `approved_by` para rastreamento
- Aprovação automática ao mudar status para 4 (Aguardando Faturamento)

### ✅ Tipos de Relatórios
1. **OS Consultor**: Enviado ao consultor responsável
2. **OS Cliente**: Enviado ao cliente vinculado

### ✅ Geração de PDF
- Utiliza DomPDF para gerar relatórios em PDF
- Templates Blade personalizados para cada tipo
- PDFs salvos em 


### ✅ Envio por E-mail
- Sistema de filas (Jobs) para processamento em background
- Templates de e-mail personalizados
- PDF anexado ao e-mail
- Log completo de envios

### ✅ Sistema de Logs
- Tabela `reports`: status, path do PDF, erros
- Tabela `report_email_logs`: destinatário, status de envio, tentativas

## Estrutura Técnica

### Migrations
- `2025_01_11_000001_add_approval_fields_to_ordem_servico.php`
- `2025_01_11_000002_create_reports_table.php`
- `2025_01_11_000003_create_report_email_logs_table.php`

### Events & Listeners
- **Event**: `App\Events\OSApproved`
- **Listener**: `App\Listeners\HandleOSApproved`

### Jobs
- **GenerateReportJob**: Gera PDF e salva no storage
- **SendReportEmailJob**: Envia e-mail com PDF anexado

### Services
- **ReportGeneratorService**: Lógica de geração de PDFs
- **ReportEmailService**: Lógica de envio de e-mails

### Mailable
- **ReportMail**: Responsável pelo envio dos relatórios

### Models
- **Report**: Relatórios gerados
- **ReportEmailLog**: Logs de envio de e-mails
- **OrdemServico**: Atualizado com campos de aprovação e relacionamentos

### Templates Blade

#### PDFs
- `resources/views/pdfs/reports/os_consultor.blade.php`
- `resources/views/pdfs/reports/os_cliente.blade.php`

#### E-mails
- `resources/views/emails/reports/os_consultor.blade.php`
- `resources/views/emails/reports/os_cliente.blade.php`

## Fluxo de Execução

1. **Criação da OS**: OS criada com `approval_status = 'pending'`

2. **Envio para Aprovação**: Consultor envia OS para aprovação (status 2)

3. **Aprovação**: Admin aprova alterando status para 4
   - Campo `approval_status` muda para `'approved'`
   - Campos `approved_at` e `approved_by` são preenchidos
   - Event `OSApproved` é disparado

4. **Geração de Relatórios**: Listener `HandleOSApproved`
   - Cria registro `Report` tipo `os_consultor`
   - Cria registro `Report` tipo `os_cliente`
   - Despacha `GenerateReportJob` para cada um

5. **Job de Geração**: `GenerateReportJob`
   - Carrega dados da OS com relacionamentos
   - Gera PDF usando template Blade
   - Salva PDF em `storage/public/reports/`
   - Atualiza registro `Report` com caminho do PDF
   - Despacha `SendReportEmailJob`

6. **Job de Envio**: `SendReportEmailJob`
   - Cria `ReportEmailLog` com status `pending`
   - Identifica destinatário (consultor ou cliente)
   - Envia e-mail com PDF anexado
   - Atualiza logs com status `sent` ou `failed`

## Configuração

### 1. Configurar Fila (Queue)

Edite o arquivo `.env`:

```env
QUEUE_CONNECTION=database
```

Execute as migrations da fila:

```bash
php artisan queue:table
php artisan migrate
```

Inicie o worker:

```bash
php artisan queue:work
```

### 2. Configurar E-mail

Edite o arquivo `.env` com suas credenciais SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@example.com
MAIL_PASSWORD=sua-senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@personalitec.com
MAIL_FROM_NAME="Personalitec Soluções"
```

### 3. Criar Link Simbólico para Storage

```bash
php artisan storage:link
```

## Testando o Sistema

### 1. Criar uma OS de Teste

Crie uma OS normalmente pelo sistema.

### 2. Enviar para Aprovação

Altere o status para 2 (Aguardando Aprovação).

### 3. Aprovar a OS

Como admin, aprove a OS (status 4 - Aguardando Faturamento).

### 4. Verificar Logs

```bash
# Ver jobs na fila
php artisan queue:work --verbose

# Ver logs do Laravel
tail -f storage/logs/laravel.log

# Verificar PDFs gerados
ls -la storage/app/public/reports/

# Verificar banco de dados
php artisan tinker
>>> \App\Models\Report::all();
>>> \App\Models\ReportEmailLog::all();
```

## Monitoramento

### Verificar Status dos Relatórios

```php
// Relatórios gerados
$reports = \App\Models\Report::where('ordem_servico_id', $osId)->get();

// Logs de envio
$emailLogs = \App\Models\ReportEmailLog::whereHas('report', function($q) use ($osId) {
    $q->where('ordem_servico_id', $osId);
})->get();
```

### Re-enviar Relatório Falhado

```php
$report = \App\Models\Report::find($reportId);
\App\Jobs\SendReportEmailJob::dispatch($report);
```

## Manutenção

### Limpar Relatórios Antigos

Crie um comando Artisan para limpar relatórios com mais de X dias:

```php
// app/Console/Commands/CleanOldReports.php
$oldReports = Report::where('created_at', '<', now()->subDays(90))->get();

foreach ($oldReports as $report) {
    if ($report->path) {
        Storage::disk('public')->delete($report->path);
    }
    $report->delete();
}
```

## Troubleshooting

### Relatórios não são gerados

1. Verificar se o EventServiceProvider está registrado
2. Verificar se as migrations foram executadas
3. Verificar logs em `storage/logs/laravel.log`

### E-mails não são enviados

1. Verificar configuração SMTP no `.env`
2. Verificar se o queue worker está rodando
3. Verificar jobs falhados: `php artisan queue:failed`
4. Reprocessar jobs falhados: `php artisan queue:retry all`

### PDFs não são gerados

1. Verificar permissões do diretório storage
2. Verificar se o DomPDF está instalado
3. Verificar templates Blade

## Dependências

- **Laravel**: 11.x
- **barryvdh/laravel-dompdf**: ^3.1
- **PostgreSQL**: 18

## Autores

Sistema implementado por Claude Code (Anthropic).

## Data de Implementação

11 de Janeiro de 2025
