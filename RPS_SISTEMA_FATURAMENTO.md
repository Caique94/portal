# RPS - Sistema de Faturamento
## Documentação Completa da Solução de Emissão de Recibos de Serviços Prestados

---

## 📋 Índice

1. [Visão Geral do Sistema](#visão-geral-do-sistema)
2. [Arquitetura e Componentes](#arquitetura-e-componentes)
3. [Fluxo de Faturamento](#fluxo-de-faturamento)
4. [Modelos de Dados](#modelos-de-dados)
5. [API Endpoints](#api-endpoints)
6. [Implementação de Novas Funcionalidades](#implementação-de-novas-funcionalidades)
7. [Tratamento de Erros](#tratamento-de-erros)
8. [Customizações Futuras](#customizações-futuras)
9. [Exemplos de Uso](#exemplos-de-uso)

---

## 🎯 Visão Geral do Sistema

### O que é RPS (Recibo de Serviços Prestados)?

RPS é um documento fiscal que comprueba a prestação de serviços. No Brasil, é regulado pela Lei Complementar nº 116/2003 e pode ser emitido antes ou junto com a Nota Fiscal de Serviço (NFS-e).

### Funcionalidade no Portal

O sistema de RPS no portal permite:

- ✅ **Emitir RPS** a partir de Ordens de Serviço (OS) em status "Aguardando RPS"
- ✅ **Agrupar múltiplas OS** em uma única RPS (mesmo cliente)
- ✅ **Configurar condições de pagamento** com parcelas
- ✅ **Cancelar RPS** com motivo registrado
- ✅ **Reverter cancelamentos** de RPS
- ✅ **Auditoria completa** de todas as operações
- ✅ **Rastreamento de status** em tempo real

### Status de Uma RPS

Uma RPS passa pelos seguintes estados:

```
┌─────────────────────────────────────────────────────┐
│                 EMITIDA (inicial)                   │
│        RPS criada e aguardando processamento         │
└────────────┬────────────────────────────────────────┘
             │
             ├──────────────────┬─────────────────────┐
             │                  │                     │
             ▼                  ▼                     ▼
        CANCELADA          REVERTIDA          (Processada)
      (cancelada)      (reverter cancelamento)
      com motivo           com motivo
```

**Estados possíveis:**
- `emitida`: RPS criada e válida para faturamento
- `cancelada`: RPS foi cancelada, com motivo registrado
- `revertida`: Cancelamento foi revertido, voltando ao status original

---

## 🏗️ Arquitetura e Componentes

### Estrutura de Camadas

```
┌─────────────────────────────────────────────────────┐
│            Interface Usuário (Frontend)             │
│          resources/views/faturamento.blade.php      │
│          public/js/faturamento.js                   │
└────────────┬────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────┐
│         Controllers (HTTP Handlers)                 │
│  RPSController.php, FaturamentoController.php       │
└────────────┬────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────┐
│   Models (Database Layer) & Services                │
│  RPS.php, OrdemServico.php, PermissionService.php   │
│  AuditService.php, NotificationService.php          │
└────────────┬────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────┐
│  Events (Event System) & Listeners                  │
│  RPSEmitted.php, HandleRPSEmitted.php               │
└────────────┬────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────┐
│    Database (PostgreSQL)                            │
│  Tables: rps, ordem_servico_rps, rps_audit, ...     │
└─────────────────────────────────────────────────────┘
```

### Componentes Principais

#### 1. **Model RPS** (`app/Models/RPS.php`)
Representa uma RPS no banco de dados com campos como:
- `numero_rps`: Número único da RPS
- `valor_total`, `valor_servicos`, `valor_deducoes`, `valor_impostos`: Valores
- `status`: Estado atual ('emitida', 'cancelada', 'revertida')
- `criado_por`, `cancelado_por`, `revertido_por`: Auditoria de quem fez cada ação
- Relacionamentos com `Cliente`, `OrdemServico`, `User`, `RPSAudit`

#### 2. **Controller RPS** (`app/Http/Controllers/RPSController.php`)
Endpoints para operações com RPS:
- `index()`: Listar RPS
- `show($id)`: Detalhes de uma RPS
- `store()`: Criar RPS
- `linkOrdensServico()`: Vincular OS à RPS
- `cancel()`: Cancelar RPS
- `revert()`: Reverter cancelamento
- `getAuditTrail()`: Histórico de alterações
- `exportPdf()`: Exportar em PDF (futuro)

#### 3. **Services (Serviços)**
- `PermissionService`: Verifica permissões de operações
- `AuditService`: Registra todas as alterações
- `NotificationService`: Envia notificações aos consultores

#### 4. **Frontend** (`public/js/faturamento.js`)
- DataTable com OS em status "Aguardando Faturamento" ou "Aguardando RPS"
- Modal para emissão de RPS
- Seleção de OS para agrupar em uma RPS
- Configuração de condições de pagamento e parcelas

---

## 📊 Fluxo de Faturamento

### Ciclo Completo: Da OS à RPS Emitida

```
1. ORDEM DE SERVIÇO CRIADA
   ↓
   Status: "Aberta" (status = 1)
   ├─ Data: data_emissao
   ├─ Cliente: cliente_id
   ├─ Valor: valor_total
   └─ Consultor: consultor_id

2. APROVAÇÃO DA OS
   ↓
   Status: "Aguardando Faturamento" (status = 4)

3. FATURAMENTO
   ↓
   Status: "Aguardando RPS" (status = 6)
   └─ Indica que agora pode emitir RPS

4. EMISSÃO DE RPS
   ↓
   ✅ Criar nova RPS
   ├─ numero_rps: número único
   ├─ data_emissao: data de emissão
   ├─ status: 'emitida'
   ├─ criado_por: usuário logado
   └─ Vincular OS à RPS via tabela pivot

   📢 Disparar evento RPSEmitted
   ↓
   ✉️ Enviar notificação ao consultor

5. RPS EMITIDA
   ↓
   Status: "RPS Emitida" (status = 7)
   └─ Pronto para processamento fiscal

6. OPERAÇÕES POSTERIORES
   ├─ Cancelar RPS (se necessário)
   │  └─ Status: 'cancelada' com motivo
   │
   └─ Reverter Cancelamento
      └─ Status: 'revertida' com motivo
```

### Exemplo de Sequência no Sistema

```
Usuário ADMIN/FINANCEIRO seleciona 3 OS
  └─ Todas do mesmo cliente
  └─ Todas em status "Aguardando RPS"
  └─ Valores: R$ 1.000 + R$ 2.000 + R$ 500

Clica em "Emitir RPS"
  ├─ Sistema detecta outras OS do mesmo cliente aguardando RPS
  └─ Oferece opção de agrupar mais OS

Seleciona quais OS agrupar
  └─ Total: R$ 3.500

Configura:
  ├─ Número RPS: 2025-001
  ├─ Data de Emissão: 2025-11-19
  ├─ Condição de Pagamento: À Vista
  └─ Observações: "Serviços prestados em nov/2025"

Clica "Confirmar"
  ├─ Sistema cria registro em RPS
  ├─ Vincula 3 OS à RPS
  ├─ Atualiza status das OS para "RPS Emitida"
  ├─ Registra auditoria (criado_por = admin)
  ├─ Dispara evento RPSEmitted
  ├─ Notifica consultant (via evento listener)
  └─ Retorna sucesso ao usuário
```

---

## 💾 Modelos de Dados

### Tabela: `rps`

```sql
CREATE TABLE rps (
    id                      BIGINT PRIMARY KEY,
    cliente_id              BIGINT NOT NULL,
    numero_rps              VARCHAR UNIQUE NOT NULL,
    data_emissao            DATE NOT NULL,
    data_vencimento         DATE,
    valor_total             DECIMAL(12, 2) NOT NULL,
    valor_servicos          DECIMAL(12, 2) DEFAULT 0,
    valor_deducoes          DECIMAL(12, 2) DEFAULT 0,
    valor_impostos          DECIMAL(12, 2) DEFAULT 0,
    status                  VARCHAR DEFAULT 'emitida',
    observacoes             TEXT,
    criado_por              BIGINT NOT NULL,
    cancelado_em            TIMESTAMP,
    cancelado_por           BIGINT,
    motivo_cancelamento     TEXT,
    revertido_em            TIMESTAMP,
    revertido_por           BIGINT,
    motivo_reversao         TEXT,
    created_at              TIMESTAMP,
    updated_at              TIMESTAMP,

    FOREIGN KEY (cliente_id) REFERENCES cliente(id),
    FOREIGN KEY (criado_por) REFERENCES users(id),
    FOREIGN KEY (cancelado_por) REFERENCES users(id),
    FOREIGN KEY (revertido_por) REFERENCES users(id),

    INDEX (cliente_id),
    INDEX (numero_rps),
    INDEX (data_emissao),
    INDEX (status)
);
```

### Tabela: `ordem_servico_rps` (Pivot/Junction)

```sql
CREATE TABLE ordem_servico_rps (
    id                  BIGINT PRIMARY KEY,
    ordem_servico_id    BIGINT NOT NULL,
    rps_id              BIGINT NOT NULL,
    created_at          TIMESTAMP,
    updated_at          TIMESTAMP,

    FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (rps_id) REFERENCES rps(id) ON DELETE CASCADE,

    UNIQUE (ordem_servico_id, rps_id),
    INDEX (ordem_servico_id),
    INDEX (rps_id)
);
```

### Tabela: `rps_audit` (Auditoria)

```sql
CREATE TABLE rps_audit (
    id              BIGINT PRIMARY KEY,
    rps_id          BIGINT NOT NULL,
    user_id         BIGINT,
    event           VARCHAR,
    description     TEXT,
    old_values      JSON,
    new_values      JSON,
    created_at      TIMESTAMP,

    FOREIGN KEY (rps_id) REFERENCES rps(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### Relacionamentos em Eloquent

#### RPS Model
```php
// Um RPS pertence a um Cliente
public function cliente() {
    return $this->belongsTo(Cliente::class);
}

// Um RPS foi criado por um User
public function criadoPor() {
    return $this->belongsTo(User::class, 'criado_por');
}

// Um RPS pode ser cancelado por um User
public function canceladoPor() {
    return $this->belongsTo(User::class, 'cancelado_por');
}

// Um RPS pode ser revertido por um User
public function revertidoPor() {
    return $this->belongsTo(User::class, 'revertido_por');
}

// Uma RPS está associada a múltiplas Ordens de Serviço
public function ordensServico() {
    return $this->belongsToMany(OrdemServico::class, 'ordem_servico_rps');
}

// Histórico de auditoria
public function audits() {
    return $this->hasMany(RPSAudit::class);
}
```

---

## 🔌 API Endpoints

### Base URL
```
/api/rps/  (ou rotas diretas)
```

### 1. Listar RPS

**GET** `/rps`

Retorna todas as RPS com paginação.

**Query Parameters:**
- `page`: Número da página (padrão: 1)
- `per_page`: Itens por página (padrão: 15)
- `cliente_id`: Filtrar por cliente (opcional)
- `status`: Filtrar por status (opcional)
- `data_inicio`: Filtrar por data início (opcional)
- `data_fim`: Filtrar por data fim (opcional)

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "numero_rps": "2025-001",
      "cliente_id": 5,
      "cliente": { "id": 5, "nome": "Empresa ABC" },
      "data_emissao": "2025-11-19",
      "data_vencimento": null,
      "valor_total": 3500.00,
      "valor_servicos": 3500.00,
      "valor_deducoes": 0,
      "valor_impostos": 0,
      "status": "emitida",
      "criado_por": 3,
      "created_at": "2025-11-19T10:30:00Z"
    }
  ],
  "pagination": { "current_page": 1, "total": 5, "per_page": 15 }
}
```

**Errors:**
- `403 Forbidden`: Usuário sem permissão de visualizar RPS

---

### 2. Detalhes de Uma RPS

**GET** `/rps/{id}`

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "numero_rps": "2025-001",
    "cliente_id": 5,
    "cliente": {
      "id": 5,
      "nome": "Empresa ABC",
      "cnpj": "12.345.678/0001-90"
    },
    "data_emissao": "2025-11-19",
    "data_vencimento": null,
    "valor_total": 3500.00,
    "valor_servicos": 3500.00,
    "valor_deducoes": 0,
    "valor_impostos": 0,
    "status": "emitida",
    "observacoes": "Serviços prestados em nov/2025",
    "criado_por": 3,
    "criadoPor": { "id": 3, "name": "Admin User" },
    "cancelado_em": null,
    "cancelado_por": null,
    "motivo_cancelamento": null,
    "revertido_em": null,
    "revertido_por": null,
    "motivo_reversao": null,
    "ordensServico": [
      { "id": 101, "numero": "00000101", "valor_total": 1000.00 },
      { "id": 102, "numero": "00000102", "valor_total": 2000.00 },
      { "id": 103, "numero": "00000103", "valor_total": 500.00 }
    ],
    "created_at": "2025-11-19T10:30:00Z",
    "updated_at": "2025-11-19T10:30:00Z"
  }
}
```

**Errors:**
- `404 Not Found`: RPS não existe
- `403 Forbidden`: Sem permissão de visualizar

---

### 3. Criar RPS

**POST** `/rps`

Cria uma nova RPS.

**Request Body:**
```json
{
  "cliente_id": 5,
  "numero_rps": "2025-001",
  "data_emissao": "2025-11-19",
  "data_vencimento": null,
  "valor_total": 3500.00,
  "valor_servicos": 3500.00,
  "valor_deducoes": 0,
  "valor_impostos": 0,
  "observacoes": "Serviços prestados em nov/2025"
}
```

**Response (201 Created):**
```json
{
  "message": "RPS criada com sucesso",
  "data": {
    "id": 1,
    "numero_rps": "2025-001",
    "cliente_id": 5,
    "status": "emitida",
    "criado_por": 3,
    "created_at": "2025-11-19T10:30:00Z"
  }
}
```

**Errors:**
- `422 Unprocessable Entity`: Validação falhou
  ```json
  {
    "message": "Erro na validação",
    "errors": {
      "numero_rps": ["RPS com este número já existe"],
      "valor_total": ["Valor deve ser maior que 0"]
    }
  }
  ```
- `403 Forbidden`: Sem permissão de criar RPS
- `500 Internal Server Error`: Erro ao criar

---

### 4. Vincular Ordens de Serviço

**POST** `/rps/{id}/vincular-ordens`

Vincula uma ou múltiplas Ordens de Serviço à RPS.

**Request Body:**
```json
{
  "ordem_servico_ids": [101, 102, 103]
}
```

**Response (200 OK):**
```json
{
  "message": "Ordens de serviço vinculadas com sucesso",
  "data": {
    "id": 1,
    "numero_rps": "2025-001",
    "ordensServico": [
      { "id": 101, "numero": "00000101" },
      { "id": 102, "numero": "00000102" },
      { "id": 103, "numero": "00000103" }
    ]
  }
}
```

**Validações:**
- Todas as OS devem estar em status `AGUARDANDO_RPS`
- Uma OS não pode ser vinculada a múltiplas RPS
- RPS deve estar em status `emitida`

**Errors:**
- `422 Unprocessable Entity`: Validação falhou
- `403 Forbidden`: Sem permissão
- `404 Not Found`: RPS ou OS não existe
- `500 Internal Server Error`: Erro ao vincular

---

### 5. Cancelar RPS

**POST** `/rps/{id}/cancelar`

Cancela uma RPS em status `emitida`.

**Request Body:**
```json
{
  "motivo": "RPS emitida por engano - usar nova numeração"
}
```

**Response (200 OK):**
```json
{
  "message": "RPS cancelada com sucesso",
  "data": {
    "id": 1,
    "numero_rps": "2025-001",
    "status": "cancelada",
    "cancelado_em": "2025-11-19T11:15:00Z",
    "cancelado_por": 3,
    "motivo_cancelamento": "RPS emitida por engano - usar nova numeração"
  }
}
```

**Validações:**
- RPS deve estar em status `emitida`
- Motivo é obrigatório (máx 500 caracteres)
- Usuário deve ter permissão de cancelar RPS

**Errors:**
- `422 Unprocessable Entity`: RPS não está em status emitida ou motivo inválido
- `403 Forbidden`: Sem permissão de cancelar
- `404 Not Found`: RPS não existe

---

### 6. Reverter Cancelamento

**POST** `/rps/{id}/reverter`

Reverte o cancelamento de uma RPS em status `cancelada`.

**Request Body:**
```json
{
  "motivo": "Cancelamento foi feito por engano - RPS é válida"
}
```

**Response (200 OK):**
```json
{
  "message": "RPS revertida com sucesso",
  "data": {
    "id": 1,
    "numero_rps": "2025-001",
    "status": "revertida",
    "revertido_em": "2025-11-19T11:20:00Z",
    "revertido_por": 3,
    "motivo_reversao": "Cancelamento foi feito por engano - RPS é válida"
  }
}
```

**Validações:**
- RPS deve estar em status `cancelada`
- Motivo é obrigatório (máx 500 caracteres)
- Usuário deve ter permissão de reverter

**Errors:**
- `422 Unprocessable Entity`: RPS não está em status cancelada
- `403 Forbidden`: Sem permissão de reverter
- `404 Not Found`: RPS não existe

---

### 7. Listar RPS por Cliente

**GET** `/rps/cliente/{clienteId}`

Lista todas as RPS de um cliente específico.

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "numero_rps": "2025-001",
      "cliente_id": 5,
      "valor_total": 3500.00,
      "status": "emitida",
      "data_emissao": "2025-11-19"
    }
  ]
}
```

---

### 8. Listar Ordens Aguardando RPS

**GET** `/rps/cliente/{clienteId}/ordens-aguardando`

Lista Ordens de Serviço prontas para serem vinculadas a uma RPS.

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 101,
      "numero": "00000101",
      "cliente_id": 5,
      "valor_total": 1000.00,
      "status": 6,
      "statusNome": "AGUARDANDO_RPS",
      "consultor": { "id": 2, "name": "Consultor ABC" },
      "cliente": { "id": 5, "nome": "Empresa ABC" }
    }
  ]
}
```

---

### 9. Obter Auditoria

**GET** `/rps/{id}/auditoria`

Retorna o histórico de todas as operações realizadas em uma RPS.

**Response (200 OK):**
```json
{
  "data": [
    {
      "timestamp": "2025-11-19T10:30:00Z",
      "user": "Admin User",
      "event": "created",
      "description": "RPS criada com número 2025-001"
    },
    {
      "timestamp": "2025-11-19T10:35:00Z",
      "user": "Admin User",
      "event": "ordens_vinculadas",
      "description": "3 ordens de serviço vinculadas"
    },
    {
      "timestamp": "2025-11-19T11:15:00Z",
      "user": "Admin User",
      "event": "cancelada",
      "description": "RPS cancelada: RPS emitida por engano"
    }
  ]
}
```

---

### 10. Exportar PDF (Stub)

**GET** `/rps/{id}/exportar-pdf`

Retorna a RPS em formato PDF (implementação futura).

**Response (501 Not Implemented):**
```json
{
  "message": "Exportação em PDF será implementada em breve."
}
```

---

## 🔐 Permissões e Autorização

### Verificação de Permissões

O sistema usa `PermissionService` para verificar quem pode fazer cada operação:

```php
// Verificar se pode visualizar RPS
$permissionService->canViewRPS($rps)

// Verificar se pode criar RPS
$permissionService->canCreateRPS()

// Verificar se pode cancelar RPS
$permissionService->canCancelRPS($rps)

// Verificar se pode reverter RPS
$permissionService->canRevertRPS($rps)
```

### Papéis com Permissão

Geralmente, as seguintes permissões se aplicam:

| Operação | Admin | Financeiro | Consultor | Outros |
|----------|-------|-----------|-----------|--------|
| Visualizar RPS | ✅ | ✅ | ✅ (próprias) | ❌ |
| Criar RPS | ✅ | ✅ | ❌ | ❌ |
| Cancelar RPS | ✅ | ✅ | ❌ | ❌ |
| Reverter RPS | ✅ | ✅ | ❌ | ❌ |
| Ver Auditoria | ✅ | ✅ | ❌ | ❌ |

---

## 📝 Implementação de Novas Funcionalidades

### Cenário 1: Adicionar Novo Campo à RPS

**Objetivo:** Adicionar campo `numero_nota_fiscal` para rastrear a NFS-e gerada

#### Passo 1: Criar Migration

```bash
php artisan make:migration add_numero_nota_fiscal_to_rps
```

**Arquivo:** `database/migrations/YYYY_MM_DD_add_numero_nota_fiscal_to_rps.php`

```php
public function up(): void
{
    Schema::table('rps', function (Blueprint $table) {
        $table->string('numero_nota_fiscal')->nullable()->after('numero_rps');
        $table->index('numero_nota_fiscal');
    });
}

public function down(): void
{
    Schema::table('rps', function (Blueprint $table) {
        $table->dropIndex(['numero_nota_fiscal']);
        $table->dropColumn('numero_nota_fiscal');
    });
}
```

#### Passo 2: Atualizar Model

```php
// app/Models/RPS.php

public $fillable = [
    'cliente_id',
    'numero_rps',
    'numero_nota_fiscal',  // ← Adicionar
    'data_emissao',
    // ... outros campos
];

// Adicionar validação (opcional)
public static function rules($id = null)
{
    return [
        'numero_nota_fiscal' => 'nullable|string|unique:rps,numero_nota_fiscal',
    ];
}
```

#### Passo 3: Atualizar Controller

```php
// app/Http/Controllers/RPSController.php

public function store(Request $request)
{
    $validated = $request->validate([
        'numero_rps' => 'required|string|unique:rps',
        'numero_nota_fiscal' => 'nullable|string|unique:rps',  // ← Adicionar
        // ... outras validações
    ]);

    // Criar RPS
    $rps = RPS::create($validated);

    // Registrar na auditoria
    AuditService::recordEvent($rps, 'created', 'RPS criada com NFS-e: ' . $validated['numero_nota_fiscal']);

    return response()->json([
        'message' => 'RPS criada com sucesso',
        'data' => $rps->refresh()
    ], 201);
}
```

#### Passo 4: Executar Migration

```bash
php artisan migrate
```

#### Passo 5: Atualizar Frontend (opcional)

```javascript
// public/js/faturamento.js

// Adicionar campo ao modal
$('#modalEmissaoRPS').append(`
    <div class="mb-3">
        <label for="txtNotaFiscal" class="form-label">Número NFS-e</label>
        <input type="text" id="txtNotaFiscal" name="numero_nota_fiscal"
               class="form-control" placeholder="Opcional">
    </div>
`);

// Enviar ao salvar
var jsonData = {
    numero_rps: $('#txtEmissaoRPSNumero').val(),
    numero_nota_fiscal: $('#txtNotaFiscal').val(),  // ← Incluir
    // ... outros dados
};
```

---

### Cenário 2: Adicionar Cálculo Automático de Impostos

**Objetivo:** Calcular automaticamente ISS (5%) sobre o valor de serviços

#### Passo 1: Adicionar Método ao Model

```php
// app/Models/RPS.php

public static function boot()
{
    parent::boot();

    // Evento 'creating' - antes de inserir
    static::creating(function ($rps) {
        // Se valor_servicos foi definido, calcular ISS
        if ($rps->valor_servicos && !$rps->valor_impostos) {
            $aliquota_iss = 0.05; // 5%
            $rps->valor_impostos = $rps->valor_servicos * $aliquota_iss;
        }

        // Recalcular valor_total
        $rps->valor_total =
            ($rps->valor_servicos ?? 0) +
            ($rps->valor_impostos ?? 0) -
            ($rps->valor_deducoes ?? 0);
    });
}
```

#### Passo 2: Usar no Controller

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'valor_servicos' => 'required|numeric|min:0.01',
        // ... outras validações
    ]);

    // Model automaticamente calcula impostos no evento 'creating'
    $rps = RPS::create($validated);

    return response()->json([
        'message' => 'RPS criada com sucesso (impostos calculados automaticamente)',
        'data' => $rps->refresh()
    ], 201);
}
```

---

### Cenário 3: Implementar Aprovação de RPS (Workflow)

**Objetivo:** Requer aprovação do financeiro antes de uma RPS ser "emitida"

#### Passo 1: Adicionar Campo à Tabela

```php
// Nova migration
public function up(): void
{
    Schema::table('rps', function (Blueprint $table) {
        $table->string('status')->change(); // Já existe
        // Adicionar novo estado: 'pendente_aprovacao'

        $table->timestamp('aprovada_em')->nullable();
        $table->unsignedBigInteger('aprovada_por')->nullable();

        $table->foreign('aprovada_por')->references('id')->on('users')->onDelete('set null');
    });
}
```

#### Passo 2: Atualizar Model

```php
// app/Models/RPS.php

public function aprovadaPor()
{
    return $this->belongsTo(User::class, 'aprovada_por');
}

// Método para verificar se pode ser aprovada
public function canBeApproved()
{
    return $this->status === 'pendente_aprovacao';
}

// Método para aprovar
public function approve(int $userId, string $comentario = null)
{
    if (!$this->canBeApproved()) {
        return false;
    }

    $this->update([
        'status' => 'emitida',
        'aprovada_em' => now(),
        'aprovada_por' => $userId,
    ]);

    AuditService::recordEvent($this, 'aprovada', 'RPS aprovada por ' . Auth::user()->name);

    // Disparar evento
    event(new RPSApproved($this));

    return true;
}
```

#### Passo 3: Adicionar Endpoint

```php
// app/Http/Controllers/RPSController.php

public function approve(Request $request, $id)
{
    $rps = RPS::findOrFail($id);

    if (!$rps->canBeApproved()) {
        return response()->json([
            'message' => 'RPS não está em status pendente de aprovação'
        ], 422);
    }

    $rps->approve(Auth::id());

    return response()->json([
        'message' => 'RPS aprovada com sucesso',
        'data' => $rps->refresh()
    ], 200);
}
```

#### Passo 4: Adicionar Botão no Frontend

```javascript
// public/js/faturamento.js

// Ao criar RPS, status inicial é 'pendente_aprovacao'
$.ajax({
    url: '/rps',
    type: 'POST',
    data: JSON.stringify({
        status: 'pendente_aprovacao',  // ← Novo
        // ... outros dados
    }),
    // ...
});

// Botão para aprovar (financeiro apenas)
$('#btn-aprovar-rps').on('click', function() {
    var rpsId = $(this).data('rps-id');

    $.ajax({
        url: '/rps/' + rpsId + '/aprovar',
        type: 'POST',
        success: function() {
            Toast.fire({
                icon: 'success',
                title: 'RPS aprovada com sucesso'
            });
            // Recarregar tabela
        }
    });
});
```

---

### Cenário 4: Integração com NFS-e (Nota Fiscal de Serviço)

**Objetivo:** Sincronizar RPS com sistema de NFS-e automático

#### Passo 1: Criar Event

```php
// app/Events/RPSEmittedForNFSe.php

namespace App\Events;

use App\Models\RPS;

class RPSEmittedForNFSe
{
    public RPS $rps;

    public function __construct(RPS $rps)
    {
        $this->rps = $rps;
    }
}
```

#### Passo 2: Criar Listener

```php
// app/Listeners/SyncWithNFSeSystem.php

namespace App\Listeners;

use App\Events\RPSEmittedForNFSe;

class SyncWithNFSeSystem
{
    public function handle(RPSEmittedForNFSe $event): void
    {
        $rps = $event->rps;

        try {
            // Chamar API externa de NFS-e
            $nfseNumber = $this->callNFSeAPI($rps);

            // Atualizar RPS com número da NFS-e
            $rps->update([
                'numero_nota_fiscal' => $nfseNumber
            ]);

            \Log::info("NFS-e sincronizada: RPS #{$rps->id} → NFS-e #{$nfseNumber}");
        } catch (\Exception $e) {
            \Log::error("Erro ao sincronizar NFS-e para RPS #{$rps->id}: " . $e->getMessage());

            // Notificar financeiro sobre falha
            \Mail::to('financeiro@empresa.com')->send(
                new \App\Mail\NFSeIntegrationFailed($rps, $e)
            );
        }
    }

    private function callNFSeAPI($rps)
    {
        // Implementar chamada à API de NFS-e
        // (RPS Fiscal, Nota Fiscal Eletrônica, etc.)

        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.nfse.com/v1/emitir', [
            'json' => [
                'numero_rps' => $rps->numero_rps,
                'valor' => $rps->valor_total,
                'cliente_id' => $rps->cliente_id,
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.nfse.token'),
            ]
        ]);

        return json_decode($response->getBody())->numero_nfs;
    }
}
```

#### Passo 3: Registrar Listener

```php
// app/Providers/EventServiceProvider.php

protected $listen = [
    // ... outros listeners
    'App\Events\RPSEmitted' => [
        'App\Listeners\HandleRPSEmitted',
    ],
    'App\Events\RPSEmittedForNFSe' => [
        'App\Listeners\SyncWithNFSeSystem',  // ← Novo
    ],
];
```

---

## ⚠️ Tratamento de Erros

### Erros Comuns e Soluções

#### 1. "RPS com este número já existe"

```
Erro: 422 Unprocessable Entity
Causa: Número RPS é duplicado
Solução: Usar número único, ex: RPS-2025-001, RPS-2025-002
```

#### 2. "Ordem de Serviço não está em status AGUARDANDO_RPS"

```
Erro: 422 Unprocessable Entity
Causa: Tentar vincular OS em status inválido
Solução: Verificar status da OS antes de vincular
         - Status 1: Aberta
         - Status 4: Aguardando Faturamento
         - Status 6: Aguardando RPS ← Correto
         - Status 7: RPS Emitida
```

#### 3. "RPS não está em status emitida para cancelamento"

```
Erro: 422 Unprocessable Entity
Causa: Tentar cancelar RPS já cancelada
Solução: Reverter primeira (se necessário), depois cancelar novamente
```

#### 4. "Você não tem permissão para..."

```
Erro: 403 Forbidden
Causa: Usuário sem role necessário
Solução: Verificar permissões no PermissionService
         - Admin: Acesso total
         - Financeiro: Criação, cancelamento, reversão
         - Consultor: Apenas visualização
```

#### 5. "Erro ao processar emissão de RPS"

```
Erro: 500 Internal Server Error
Causa: Exceção não tratada no listener ou evento
Solução: Verificar logs em storage/logs/laravel.log

Log típico:
[2025-11-19 10:30:45] local.ERROR: Error processing RPS emission: ...
```

### Tratamento Defensivo em Customizações

```php
// ✅ CORRETO: Com tratamento de erro

try {
    $rps = RPS::create($validated);

    // Vincular OS
    $rps->ordensServico()->attach($osIds);

    // Registrar auditoria
    AuditService::recordEvent($rps, 'created', 'RPS criada');

    // Disparar evento (sem bloquear se falhar)
    event(new RPSEmitted($rps));

    return response()->json([
        'message' => 'RPS criada com sucesso',
        'data' => $rps
    ], 201);

} catch (\Illuminate\Validation\ValidationException $e) {
    return response()->json([
        'message' => 'Erro na validação',
        'errors' => $e->errors()
    ], 422);

} catch (\Exception $e) {
    \Log::error('Erro ao criar RPS: ' . $e->getMessage());

    return response()->json([
        'message' => 'Erro ao criar RPS: ' . $e->getMessage()
    ], 500);
}
```

```php
// ❌ INCORRETO: Sem tratamento

$rps = RPS::create($request->all());  // Pode gerar exception
$rps->ordensServico()->attach($osIds);  // Pode falhar silenciosamente
event(new RPSEmitted($rps));  // Pode travar tudo
return response()->json(['data' => $rps]);  // Sem status correto
```

---

## 🚀 Customizações Futuras

### Roadmap Recomendado

#### Curto Prazo (1-2 semanas)

- ✅ Implementar exportação em PDF
- ✅ Adicionar filtros avançados (data, valor, cliente)
- ✅ Dashboard com resumo de RPS por status
- ✅ Email automático quando RPS é emitida

#### Médio Prazo (1-2 meses)

- ✅ Integração com NFS-e
- ✅ Aprovação workflow (pendente → aprovada)
- ✅ Cálculo automático de impostos
- ✅ Relatórios e analytics

#### Longo Prazo (3+ meses)

- ✅ Integração com sistema bancário (boleto, PIX)
- ✅ Assinatura digital de RPS
- ✅ Portal do cliente (visualizar RPS)
- ✅ Mobile app para consultores

### Padrões para Novas Funcionalidades

Ao adicionar novas features, siga este padrão:

```
1. Migration
   └─ Adicionar campos/tabelas necessários

2. Model
   └─ Adicionar propriedades, relacionamentos, métodos

3. Controller
   └─ Adicionar endpoints

4. Service
   └─ Lógica complexa (se necessário)

5. Event/Listener
   └─ Se precisa disparar ações assíncronas

6. Frontend
   └─ UI para nova funcionalidade

7. Testes
   └─ Unit tests + Feature tests

8. Documentação
   └─ Atualizar este README
```

---

## 💡 Exemplos de Uso

### Exemplo 1: Criar RPS via Terminal

```bash
php artisan tinker

# Criar RPS
$rps = App\Models\RPS::create([
    'cliente_id' => 5,
    'numero_rps' => '2025-999',
    'data_emissao' => now()->date,
    'valor_total' => 5000,
    'valor_servicos' => 5000,
    'criado_por' => 1,
]);

# Vincular OS
$rps->ordensServico()->attach([101, 102, 103]);

# Verificar auditoria
$rps->audits;
```

### Exemplo 2: Cancelar RPS Programaticamente

```php
// No Controller ou Job
$rps = RPS::find(1);

if ($rps->canBeCancelled()) {
    $rps->cancel(
        Auth::id(),
        'Cancelamento automático por duplicação'
    );

    \Log::info("RPS #{$rps->id} cancelada automaticamente");
}
```

### Exemplo 3: Relatório de RPS Emitidas no Mês

```php
// Controller
public function relatorioMes()
{
    $mes = now()->month;
    $ano = now()->year;

    $rps = RPS::where('status', 'emitida')
        ->whereYear('data_emissao', $ano)
        ->whereMonth('data_emissao', $mes)
        ->with('cliente', 'criadoPor')
        ->get();

    $total = $rps->sum('valor_total');
    $quantidade = $rps->count();

    return view('relatorio.rps-mes', compact('rps', 'total', 'quantidade'));
}

// Blade Template
<table>
    <tr>
        <th>RPS</th>
        <th>Cliente</th>
        <th>Valor</th>
        <th>Data</th>
        <th>Criado por</th>
    </tr>
    @foreach($rps as $r)
    <tr>
        <td>{{ $r->numero_rps }}</td>
        <td>{{ $r->cliente->nome }}</td>
        <td>R$ {{ number_format($r->valor_total, 2, ',', '.') }}</td>
        <td>{{ $r->data_emissao->format('d/m/Y') }}</td>
        <td>{{ $r->criadoPor->name }}</td>
    </tr>
    @endforeach
    <tr>
        <td colspan="2"><strong>Total</strong></td>
        <td><strong>R$ {{ number_format($total, 2, ',', '.') }}</strong></td>
        <td colspan="2">{{ $quantidade }} RPS</td>
    </tr>
</table>
```

### Exemplo 4: Buscar RPS com Auditoria

```php
$rps = RPS::with('audits', 'audits.user')
    ->find(1);

foreach ($rps->audits as $audit) {
    echo $audit->created_at . " - " . $audit->user->name . ": " . $audit->event . "\n";
}

// Output:
// 2025-11-19 10:30:00 - Admin: created: RPS criada com número 2025-001
// 2025-11-19 10:35:00 - Admin: ordens_vinculadas: 3 ordens de serviço vinculadas
// 2025-11-19 11:15:00 - Admin: cancelada: RPS cancelada
```

---

## 📞 Suporte e Dúvidas

### Checklist de Implementação

- [ ] RPS criada com sucesso
- [ ] OS vinculadas corretamente
- [ ] Status atualizado para "RPS Emitida"
- [ ] Auditoria registrada
- [ ] Notificação enviada ao consultor
- [ ] Cancelamento funciona (se necessário)
- [ ] Reversão funciona (se necessário)
- [ ] Relatórios mostram dados corretos

### Próximos Passos

1. **Testar em Desenvolvimento**
   ```bash
   php artisan serve
   php artisan queue:listen  # Se usar jobs
   ```

2. **Validar em Staging**
   - Criar RPS com dados reais
   - Testar fluxo completo
   - Verificar auditoria

3. **Deploy em Produção**
   ```bash
   php artisan migrate --force
   php artisan cache:clear
   ```

4. **Monitorar**
   - Verificar logs: `tail -f storage/logs/laravel.log`
   - Monitorar performance das queries
   - Acompanhar feedback de usuários

---

**Última atualização:** 19 de Novembro de 2025
**Status:** Documentação Completa ✅
