# RPS - Guia de Customizações
## Como Estender e Personalizar o Sistema de RPS

---

## 📑 Índice

1. [Guia Rápido para Customizações](#guia-rápido-para-customizações)
2. [Estrutura de Pastas](#estrutura-de-pastas)
3. [Locais de Modificação](#locais-de-modificação)
4. [Customizações Comuns](#customizações-comuns)
5. [Teste de Customizações](#teste-de-customizações)
6. [Troubleshooting](#troubleshooting)

---

## 🚀 Guia Rápido para Customizações

### Quando Você Quer Fazer...

#### ✅ Adicionar Novo Campo à RPS

```
Passo 1: Criar migration
         └─ database/migrations/

Passo 2: Executar: php artisan migrate

Passo 3: Atualizar Model
         └─ app/Models/RPS.php
         └─ Adicionar ao $fillable

Passo 4: Atualizar Controller
         └─ app/Http/Controllers/RPSController.php
         └─ Adicionar validação em store()

Passo 5: Frontend (opcional)
         └─ public/js/faturamento.js
         └─ Adicionar input ao modal
```

**Tempo estimado:** 10-15 minutos

---

#### ✅ Alterar Regras de Validação

```
Local: app/Http/Controllers/RPSController.php
       └─ Método store()
       └─ Alterar $request->validate([...])

Exemplo:
- Antes: 'numero_rps' => 'required|string|unique:rps'
- Depois: 'numero_rps' => 'required|string|unique:rps|regex:/^RPS-\d{4}$/'
```

**Tempo estimado:** 2-5 minutos

---

#### ✅ Mudar Valor de Status Padrão

```
Local: app/Models/RPS.php
       └─ Nas migrations: ->default('emitida')
       └─ Ou no banco: ALTER TABLE rps SET status = 'novo_status'

Ou no Model:
```php
protected $attributes = [
    'status' => 'novo_status_padrao',
];
```
```

**Tempo estimado:** 5 minutos

---

#### ✅ Adicionar Ação Automática ao Criar RPS

```
Local: app/Models/RPS.php
       └─ Método boot()
       └─ Usar eventos: creating, created, updating, updated

Exemplo:
static::created(function ($rps) {
    // Enviar email, atualizar OS, etc.
    \Mail::to($rps->cliente->email)->send(
        new \App\Mail\RPSCreated($rps)
    );
});
```

**Tempo estimado:** 15-20 minutos

---

#### ✅ Criar Novo Endpoint (Rota)

```
Passo 1: Adicionar rota em routes/web.php
         └─ Route::post('/rps/{id}/minha-acao', [...])

Passo 2: Criar método no Controller
         └─ app/Http/Controllers/RPSController.php
         └─ public function minhaAcao($id) { ... }

Passo 3: Adicionar permissão (se necessário)
         └─ PermissionService::canMinhAcao($rps)

Passo 4: Atualizar Frontend
         └─ Adicionar botão/ação no faturamento.js
```

**Tempo estimado:** 20-30 minutos

---

#### ✅ Alterar Fluxo de Cancelamento

```
Local: app/Models/RPS.php
       └─ Método cancel()

Exemplo:
- Requer aprovação? Adicione novo status 'pendente_cancelamento'
- Enviar email? Adicione Mail::send() no método
- Atualizar OS? Adicione $rps->ordensServico()->update([...])
```

**Tempo estimado:** 20-25 minutos

---

## 📁 Estrutura de Pastas Relevantes

```
projeto/
├── app/
│   ├── Models/
│   │   ├── RPS.php                          ← Model principal
│   │   ├── OrdemServico.php                 ← Relacionamento
│   │   └── RPSAudit.php                     ← Auditoria
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── RPSController.php             ← Endpoints
│   │   │   └── FaturamentoController.php    ← View
│   │   │
│   │   └── Middleware/
│   │       └── Authenticate.php
│   │
│   ├── Events/
│   │   └── RPSEmitted.php                   ← Evento
│   │
│   ├── Listeners/
│   │   └── HandleRPSEmitted.php             ← Tratador
│   │
│   ├── Services/
│   │   ├── PermissionService.php            ← Permissões
│   │   ├── AuditService.php                 ← Auditoria
│   │   └── NotificationService.php          ← Notificações
│   │
│   └── Mail/
│       └── RPSCreated.php                   ← Email
│
├── database/
│   ├── migrations/
│   │   ├── 2025_11_15_033008_create_rps_table.php
│   │   └── 2025_11_15_033058_create_ordem_servico_rps_table.php
│   │
│   └── seeders/
│       └── RPSSeeder.php                    ← Dados de teste
│
├── routes/
│   └── web.php                              ← Rotas
│
├── public/
│   └── js/
│       └── faturamento.js                   ← Frontend
│
└── resources/
    └── views/
        └── faturamento.blade.php            ← Template
```

---

## 🎯 Locais de Modificação

### Por Tipo de Mudança

| Tipo de Mudança | Arquivo(s) | Método/Local |
|-----------------|-----------|-------------|
| **Novo Campo** | Migration, Model, Controller | `up()`, `$fillable`, `validate()` |
| **Nova Rota** | routes/web.php, Controller | `Route::...`, novo método |
| **Novo Status** | Migration, Model | `default()`, validação |
| **Validação** | Controller | `$request->validate([])` |
| **Evento** | Model, Controller | `events/`, `static::created()` |
| **Email** | Mail classes, Listener | `Mail::send()` |
| **Permissão** | PermissionService | `can...()` métodos |
| **UI** | faturamento.js, .blade.php | Novo elemento HTML/JS |
| **Cálculo** | Model boot() | `static::creating()` |
| **Query** | Model scopes | `public function scope...()` |

---

## 🛠️ Customizações Comuns

### 1. Adicionar Campo de Data de Pagamento

**Cenário:** Você quer rastrear quando a RPS foi efetivamente paga.

#### Passo 1: Migration

```bash
php artisan make:migration add_data_pagamento_to_rps
```

**Arquivo:** `database/migrations/YYYY_MM_DD_add_data_pagamento_to_rps.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rps', function (Blueprint $table) {
            $table->date('data_pagamento')->nullable()->after('data_vencimento');
            $table->index('data_pagamento');
        });
    }

    public function down(): void
    {
        Schema::table('rps', function (Blueprint $table) {
            $table->dropIndex(['data_pagamento']);
            $table->dropColumn('data_pagamento');
        });
    }
};
```

#### Passo 2: Executar Migration

```bash
php artisan migrate
```

#### Passo 3: Atualizar Model

```php
// app/Models/RPS.php

protected $dates = [
    'data_emissao',
    'data_vencimento',
    'data_pagamento',  // ← Novo
    'cancelado_em',
    'revertido_em',
];

protected $fillable = [
    'cliente_id',
    'numero_rps',
    'data_emissao',
    'data_vencimento',
    'data_pagamento',  // ← Novo
    // ... outros
];

// Método para registrar pagamento
public function marcarComoPaga(?\DateTime $dataPagamento = null)
{
    $this->update([
        'data_pagamento' => $dataPagamento ?? now(),
    ]);

    AuditService::recordEvent($this, 'pagamento_registrado', 'RPS marcada como paga em ' . $this->data_pagamento->format('d/m/Y'));

    event(new RPSPaid($this));

    return $this;
}
```

#### Passo 4: Adicionar Rota

```php
// routes/web.php

Route::post('/rps/{id}/marcar-como-paga', [RPSController::class, 'marcarComoPaga']);
```

#### Passo 5: Atualizar Controller

```php
// app/Http/Controllers/RPSController.php

public function marcarComoPaga(Request $request, $id)
{
    $rps = RPS::findOrFail($id);

    $permissionService = new PermissionService();
    if (!$permissionService->canEditRPS($rps)) {
        return response()->json(['message' => 'Sem permissão'], 403);
    }

    try {
        $dataPagamento = $request->input('data_pagamento') ?
            \Carbon\Carbon::parse($request->input('data_pagamento')) :
            now();

        $rps->marcarComoPaga($dataPagamento);

        return response()->json([
            'message' => 'RPS marcada como paga',
            'data' => $rps->refresh()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erro: ' . $e->getMessage()
        ], 500);
    }
}
```

#### Passo 6: Frontend

```javascript
// public/js/faturamento.js

// Adicionar botão na ação da RPS
html += '<li><a class="dropdown-item btn-marcar-paga" href="javascript:void(0);">';
html += '<i class="bi bi-check-circle"></i> Marcar como Paga</a></li>';

// Handler
$('#tblFaturamento').on('click', '.btn-marcar-paga', function() {
    var row = $(this).closest('tr');
    var rowData = tblFaturamento.row(row).data();

    Swal.fire({
        title: 'Marcar RPS como Paga',
        html: '<input type="date" id="dataPagamento" class="form-control">',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirmar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/rps/' + rowData.rps_id + '/marcar-como-paga',
                type: 'POST',
                data: {
                    data_pagamento: $('#dataPagamento').val()
                },
                success: function() {
                    Toast.fire({ icon: 'success', title: 'RPS marcada como paga' });
                    tblFaturamento.ajax.reload();
                }
            });
        }
    });
});
```

---

### 2. Gerar Número RPS Automático

**Cenário:** Em vez de digitar, gerar automaticamente com padrão ANO-SEQUENCIA.

#### Passo 1: Adicionar Helper

```php
// app/Helpers/RpsHelper.php

namespace App\Helpers;

use App\Models\RPS;

class RpsHelper
{
    public static function generateNextRpsNumber()
    {
        $ano = now()->year;

        // Buscar última RPS do ano
        $ultimaRps = RPS::where('numero_rps', 'like', $ano . '-%')
            ->orderByDesc('numero_rps')
            ->first();

        // Extrair sequência e incrementar
        if ($ultimaRps) {
            $partes = explode('-', $ultimaRps->numero_rps);
            $sequencia = (int) $partes[1] + 1;
        } else {
            $sequencia = 1;
        }

        return $ano . '-' . str_pad($sequencia, 4, '0', STR_PAD_LEFT);
    }
}
```

#### Passo 2: Usar no Controller

```php
// app/Http/Controllers/RPSController.php

use App\Helpers\RpsHelper;

public function store(Request $request)
{
    $validated = $request->validate([
        'cliente_id' => 'required|exists:cliente,id',
        'valor_total' => 'required|numeric|min:0.01',
        // numero_rps é agora opcional
        'numero_rps' => 'nullable|string|unique:rps',
    ]);

    // Gerar automaticamente se não fornecido
    if (!$validated['numero_rps']) {
        $validated['numero_rps'] = RpsHelper::generateNextRpsNumber();
    }

    $validated['criado_por'] = Auth::id();

    $rps = RPS::create($validated);

    return response()->json([
        'message' => 'RPS criada com sucesso',
        'data' => $rps->refresh(),
        'numero_gerado' => $rps->numero_rps  // ← Retornar número gerado
    ], 201);
}
```

#### Passo 3: Frontend

```javascript
// public/js/faturamento.js

// Ao abrir modal, deixar campo vazio e desabilitado
$('#txtEmissaoRPSNumero').prop('disabled', true);
$('#txtEmissaoRPSNumero').attr('placeholder', 'Será gerado automaticamente');

// Ou fazer requisição para gerar número antes de abrir modal
$.ajax({
    url: '/rps/proximo-numero',
    type: 'GET',
    success: function(response) {
        $('#txtEmissaoRPSNumero').val(response.numero);
    }
});
```

---

### 3. Notificar Cliente Automaticamente

**Cenário:** Enviar email ao cliente quando RPS é emitida.

#### Passo 1: Criar Mail Class

```bash
php artisan make:mail RPSEmittedNotification
```

```php
// app/Mail/RPSEmittedNotification.php

namespace App\Mail;

use App\Models\RPS;
use Illuminate\Mail\Mailable;

class RPSEmittedNotification extends Mailable
{
    public function __construct(public RPS $rps) {}

    public function envelope()
    {
        return new Envelope(
            subject: "RPS #{$this->rps->numero_rps} Emitida",
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.rps-emitted',
        );
    }

    public function attachments()
    {
        return [];
    }
}
```

#### Passo 2: Criar Template Email

```blade
<!-- resources/views/emails/rps-emitted.blade.php -->

<h2>RPS Emitida</h2>

<p>Prezado cliente,</p>

<p>Uma RPS foi emitida para você:</p>

<table border="1" cellpadding="10">
    <tr>
        <th>Número RPS</th>
        <td>{{ $rps->numero_rps }}</td>
    </tr>
    <tr>
        <th>Data de Emissão</th>
        <td>{{ $rps->data_emissao->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <th>Valor Total</th>
        <td>R$ {{ number_format($rps->valor_total, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <th>Data de Vencimento</th>
        <td>{{ $rps->data_vencimento?->format('d/m/Y') ?? 'À vista' }}</td>
    </tr>
</table>

<p>Para mais detalhes, acesse o portal: <a href="{{ route('faturamento') }}">{{ route('faturamento') }}</a></p>

<p>Atenciosamente,<br>Sua Empresa</p>
```

#### Passo 3: Criar Listener

```bash
php artisan make:listener SendRPSNotificationToClient
```

```php
// app/Listeners/SendRPSNotificationToClient.php

namespace App\Listeners;

use App\Events\RPSEmitted;
use App\Mail\RPSEmittedNotification;
use Illuminate\Support\Facades\Mail;

class SendRPSNotificationToClient
{
    public function handle(RPSEmitted $event): void
    {
        $rps = $event->ordemServico->rps;  // Ajustar conforme relacionamento

        try {
            Mail::to($rps->cliente->email)
                ->send(new RPSEmittedNotification($rps));

            \Log::info("Email de RPS emitida enviado para {$rps->cliente->email}");

        } catch (\Exception $e) {
            \Log::error("Erro ao enviar email de RPS: " . $e->getMessage());
        }
    }
}
```

#### Passo 4: Registrar Listener

```php
// app/Providers/EventServiceProvider.php

protected $listen = [
    'App\Events\RPSEmitted' => [
        'App\Listeners\HandleRPSEmitted',
        'App\Listeners\SendRPSNotificationToClient',  // ← Novo
    ],
];
```

---

### 4. Adicionar Relatório de RPS

**Cenário:** Gerar relatório filtrável de RPS por período.

#### Passo 1: Criar Controller

```php
// app/Http/Controllers/RelatorioRPSController.php

namespace App\Http\Controllers;

use App\Models\RPS;
use Illuminate\Http\Request;

class RelatorioRPSController extends Controller
{
    public function index()
    {
        return view('relatorios.rps');
    }

    public function gerar(Request $request)
    {
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $clienteId = $request->input('cliente_id');
        $status = $request->input('status');

        $query = RPS::with('cliente', 'criadoPor');

        if ($dataInicio) {
            $query->whereDate('data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->whereDate('data_emissao', '<=', $dataFim);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $rps = $query->orderByDesc('data_emissao')->get();

        $total = $rps->sum('valor_total');
        $por_status = $rps->groupBy('status')->map->sum('valor_total');

        return response()->json([
            'data' => $rps,
            'resumo' => [
                'total' => $total,
                'quantidade' => $rps->count(),
                'por_status' => $por_status,
            ]
        ]);
    }

    public function exportarPdf(Request $request)
    {
        // Usar mesma lógica de filtro acima
        // Gerar PDF com DOMPDF ou similar

        // return view('relatorios.rps-pdf', compact('rps', 'total'));
    }
}
```

#### Passo 2: Adicionar Rotas

```php
// routes/web.php

Route::get('/relatorios/rps', [RelatorioRPSController::class, 'index'])->name('relatorio.rps');
Route::get('/relatorios/rps/gerar', [RelatorioRPSController::class, 'gerar']);
Route::get('/relatorios/rps/exportar-pdf', [RelatorioRPSController::class, 'exportarPdf']);
```

---

### 5. Integração com Webhook (Para NFS-e)

**Cenário:** Receber confirmação de NFS-e de um sistema externo.

#### Passo 1: Criar Rota de Webhook

```php
// routes/web.php (sem autenticação)

Route::post('/webhooks/nfse/confirmacao', [WebhookController::class, 'nfseConfirmacao'])->withoutMiddleware('auth');
```

#### Passo 2: Criar Controller

```php
// app/Http/Controllers/WebhookController.php

namespace App\Http\Controllers;

use App\Models\RPS;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function nfseConfirmacao(Request $request)
    {
        // Verificar assinatura do webhook (segurança)
        $assinatura = $request->header('X-Webhook-Signature');
        $payload = $request->getContent();

        if (!$this->verificarAssinatura($payload, $assinatura)) {
            return response()->json(['error' => 'Assinatura inválida'], 401);
        }

        try {
            $dados = $request->json();

            // Buscar RPS
            $rps = RPS::where('numero_rps', $dados['numero_rps'])->first();

            if (!$rps) {
                return response()->json(['error' => 'RPS não encontrada'], 404);
            }

            // Atualizar com dados da NFS-e
            $rps->update([
                'numero_nota_fiscal' => $dados['numero_nfs'],
                'data_emissao_nfs' => $dados['data_emissao'],
            ]);

            \Log::info("Webhook recebido: RPS {$rps->numero_rps} → NFS-e {$dados['numero_nfs']}");

            event(new \App\Events\NFSeConfirmed($rps));

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            \Log::error("Erro ao processar webhook NFS-e: " . $e->getMessage());
            return response()->json(['error' => 'Erro ao processar'], 500);
        }
    }

    private function verificarAssinatura($payload, $assinatura)
    {
        $chave = config('services.nfse.webhook_secret');
        $hash = hash_hmac('sha256', $payload, $chave, false);
        return hash_equals($hash, $assinatura);
    }
}
```

---

## ✅ Teste de Customizações

### Teste Local

```bash
# 1. Limpar cache
php artisan cache:clear
php artisan config:clear

# 2. Executar migrations
php artisan migrate

# 3. Iniciar servidor
php artisan serve

# 4. Usar Tinker para testar
php artisan tinker

# Dentro do Tinker:
$rps = App\Models\RPS::first();
$rps->minhaMetodoCustomizado();
```

### Teste de Customizações em Blade

```php
// Dentro de uma view
@if (method_exists($rps, 'minhaMetodoCustomizado'))
    {{ $rps->minhaMetodoCustomizado() }}
@endif
```

### Teste de API com CURL

```bash
# Criar RPS
curl -X POST http://localhost:8000/rps \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(grep csrf resources/views/faturamento.blade.php | head -1)" \
  -d '{
    "cliente_id": 5,
    "numero_rps": "2025-999",
    "valor_total": 5000
  }'

# Marcar como paga
curl -X POST http://localhost:8000/rps/1/marcar-como-paga \
  -H "Content-Type: application/json" \
  -d '{"data_pagamento": "2025-11-20"}'
```

### Teste de Eventos

```php
// Testar se evento está sendo disparado
\Event::fake();

$rps = RPS::create([...]);

\Event::assertDispatched(\App\Events\RPSEmitted::class);
```

---

## 🐛 Troubleshooting

### Erro: "método não existe"

```
Erro: Call to undefined method App\Models\RPS::meuMetodo()
Solução: Verificar se adicionou o método na classe Model
         Executar: php artisan cache:clear
```

### Erro: "coluna não existe"

```
Erro: Column not found: 1054 Unknown column 'novo_campo' in 'on clause'
Solução: Executar migrations pendentes
         php artisan migrate
         Verificar se migration foi criada corretamente
```

### Erro: "Unauthorized" em chamada de API

```
Erro: 401 Unauthorized ao chamar /rps
Solução: 1. Verificar token CSRF
         2. Verificar autenticação do usuário
         3. Verificar middleware na rota
```

### Permissões não funcionam

```
Erro: 403 Forbidden mesmo com permissão correta
Solução: 1. Verificar método canCreate/canEdit/canDelete em PermissionService
         2. Verificar papel do usuário: Auth::user()->papel
         3. Adicionar verificação de permissão explícita se necessário
```

### Email não está sendo enviado

```
Erro: Listener dispara mas email não chega
Solução: 1. Verificar MAIL_DRIVER em .env
         2. Para teste: MAIL_DRIVER=log (vai para logs)
         3. Verificar: tail -f storage/logs/laravel.log
         4. Verificar endereço de email do cliente
```

### Webhook retorna 401

```
Erro: Assinatura inválida no webhook
Solução: 1. Verificar WEBHOOK_SECRET em config
         2. Verificar se payload está sendo assinado corretamente
         3. Adicionar Log para debug:
            Log::info('Assinatura esperada: ' . $hash);
            Log::info('Assinatura recebida: ' . $assinatura);
```

---

## 📚 Recursos Adicionais

### Links Importantes

- [Documentação RPS Sistema Completo](./RPS_SISTEMA_FATURAMENTO.md)
- [Laravel Documentation](https://laravel.com/docs)
- [Eloquent Relations](https://laravel.com/docs/11.x/eloquent-relationships)
- [Events and Listeners](https://laravel.com/docs/11.x/events)

### Arquivo de Teste (Seeder)

```php
// database/seeders/RPSSeeder.php

public function run(): void
{
    $cliente = \App\Models\Cliente::first();

    RPS::create([
        'cliente_id' => $cliente->id,
        'numero_rps' => '2025-001',
        'data_emissao' => now(),
        'valor_total' => 5000,
        'valor_servicos' => 5000,
        'status' => 'emitida',
        'criado_por' => 1,
    ]);
}
```

Executar: `php artisan db:seed --class=RPSSeeder`

---

**Última atualização:** 19 de Novembro de 2025
**Status:** Documentação de Customizações Completa ✅
