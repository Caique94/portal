# Ordem de Serviço (OS) & RPS Workflow Documentation

## Overview

This document describes the complete Ordem de Serviço (OS) and Recibo de Prestação de Serviço (RPS) workflow implementation. The system uses a **Finite State Machine (FSM)** pattern to enforce strict business logic and state transitions.

## 8 Status States

The workflow defines exactly **8 status states** that an OS can have:

| Status | Value | Label | Description |
|--------|-------|-------|-------------|
| 1 | `em_aberto` | Em Aberto | Initial state - OS is open and editable |
| 2 | `aguardando_aprovacao` | Aguardando Aprovação | OS submitted for approval, not yet approved |
| 3 | `aprovado` | Aprovado | OS approved and ready for billing |
| 4 | `contestar` | Contestar | OS rejected/contested, can be reopened |
| 5 | `aguardando_faturamento` | Aguardando Faturamento | (Intermediate state during billing process) |
| 6 | `faturado` | Faturado | OS has been billed |
| 7 | `aguardando_rps` | Aguardando RPS | OS ready to be linked to RPS |
| 8 | `rps_emitida` | RPS Emitida | RPS issued and OS linked |

## State Transition Diagram

```
EM_ABERTO
├── → AGUARDANDO_APROVACAO
│   ├── → APROVADO
│   │   ├── → AGUARDANDO_FATURAMENTO
│   │   │   └── → FATURADO
│   │   │       └── → AGUARDANDO_RPS
│   │   │           └── → RPS_EMITIDA
│   │   └── → CONTESTAR
│   │       └── → EM_ABERTO
│   │       └── → AGUARDANDO_APROVACAO
│   └── → CONTESTAR
│       └── → EM_ABERTO
│       └── → AGUARDANDO_APROVACAO
└── → CONTESTAR
    └── → EM_ABERTO
    └── → AGUARDANDO_APROVACAO
```

## Key Implementation Files

### Enums

**File:** `app/Enums/OrdemServicoStatus.php`

Defines the 8 status states with helper methods:
- `label()` - Get user-friendly label
- `badgeColor()` - Get Bootstrap badge color
- `validTransitions()` - Get array of valid next states
- `isEditable()` - Check if OS can be edited (only EM_ABERTO and CONTESTAR)
- `canBeBilled()` - Check if OS can be billed (APROVADO only)
- `canLinkToRps()` - Check if OS can be linked to RPS (AGUARDANDO_RPS only)

### Services

#### StateMachine Service
**File:** `app/Services/StateMachine.php`

Implements the Finite State Machine logic:
- `getCurrentStatus()` - Get current OS status
- `canTransition($targetStatus)` - Check if transition is valid
- `getValidTransitions()` - Get array of valid next states from current
- `transition($targetStatus, $additionalData, $userId)` - Perform state transition with audit logging

**Usage:**
```php
$stateMachine = new StateMachine($ordemServico);

// Check if transition is valid
if ($stateMachine->canTransition(OrdemServicoStatus::APROVADO)) {
    // Perform transition
    $stateMachine->transition(OrdemServicoStatus::APROVADO, [], Auth::id());
}
```

#### OSValidation Service
**File:** `app/Services/OSValidation.php`

Validates OS before state transitions:
- `validateForBilling()` - Comprehensive validation before faturamento
  - Check cliente exists and has fiscal data (CPF/CNPJ, email)
  - Check consultor exists
  - Check valor_total is valid
  - Check OS has items/produtos
  - Check for duplicate RPS
  - Check data_emissao is valid
- `validateForRPS()` - Validation before linking to RPS
- `canEdit()` - Check if OS can be edited
- `hasValidItems()` - Check if OS has products
- `validateTransition($targetStatus)` - Get all validation errors for target status
- `validateRequiredFields()` - Validate all required fields are filled

**Usage:**
```php
$validationService = new OSValidation($ordemServico);
$errors = $validationService->validateForBilling();

if (!empty($errors)) {
    // Handle validation errors
}
```

#### AuditService
**File:** `app/Services/AuditService.php`

Records all changes to OS in audit trail:
- `recordCreation($attributes)` - Record OS creation
- `recordUpdate($oldValues, $newValues)` - Record OS update
- `recordStatusTransition($from, $to, $data)` - Record status change
- `recordApproval()` - Record approval
- `recordContestacao($motivo)` - Record rejection
- `recordBilling($billingData)` - Record billing
- `recordRpsLinking($rpsId)` - Record RPS linkage
- `recordDeletion($reason)` - Record deletion
- `getHistory($limit)` - Get audit history
- `getStatusHistory()` - Get status change history
- `getTimeline()` - Get formatted timeline

**Usage:**
```php
$auditService = new AuditService($ordemServico);
$auditService->recordStatusTransition('em_aberto', 'aguardando_aprovacao');
```

#### PermissionService
**File:** `app/Services/PermissionService.php`

Implements role-based access control with 5 roles:
- **superadmin** - Full access to everything
- **admin** - Administrative access (can approve, contest, bill)
- **consultor** - Can create/edit own OS only
- **financeiro** - Can view all OS, perform billing
- **fiscal** - Can create/manage RPS, linking

**Key Methods:**
- `canViewOS($os)` - Check view permission
- `canCreateOS()` - Check creation permission
- `canEditOS($os)` - Check edit permission (+ must be editable status)
- `canDeleteOS($os)` - Check delete permission (+ must be EM_ABERTO)
- `canRequestApproval($os)` - Check approval request permission
- `canApproveOS($os)` - Check approval permission
- `canContestOS($os)` - Check contest permission
- `canBillOS($os)` - Check billing permission
- `canLinkToRPS($os)` - Check RPS linking permission
- `canViewRPS($rps)` - Check RPS view permission
- `canCreateRPS()` - Check RPS creation permission
- `canCancelRPS($rps)` - Check RPS cancellation permission
- `canRevertRPS($rps)` - Check RPS reversion permission
- `getAllowedStatusTransitions($os)` - Get filtered transitions based on permissions
- `getPermissionSummary($os)` - Get complete permission overview

**Usage:**
```php
$permissionService = new PermissionService();

if (!$permissionService->canApproveOS($os)) {
    return response()->json(['message' => 'Permission denied'], 403);
}
```

### Models

#### OrdemServico Model
**File:** `app/Models/OrdemServico.php`

Enhanced with:
- Status cast to `OrdemServicoStatus` enum
- `audits()` relationship - hasMany OSAudit
- `rpsDocuments()` relationship - belongsToMany RPS
- Scope methods: `byStatus()`, `emAberto()`, `aguardandoAprovacao()`, etc.
- Helper methods: `getStatus()`, `canBeEdited()`, `canBeBilled()`

#### RPS Model
**File:** `app/Models/RPS.php`

Represents RPS document with:
- `ordensServico()` relationship - belongsToMany OrdemServico
- `cliente()` relationship - belongsTo Cliente
- `criadoPor()`, `canceladoPor()`, `revertidoPor()` - belongsTo User
- `audits()` relationship - hasMany RPSAudit
- Methods:
  - `linkOrdensServico($ids)` - Link multiple OS to RPS
  - `unlinkOrdensServico()` - Unlink all OS from RPS
  - `cancel($userId, $motivo)` - Cancel RPS
  - `revert($userId, $motivo)` - Revert cancelled RPS
  - `canBeCancelled()` - Check if RPS can be cancelled
  - `canBeReverted()` - Check if RPS can be reverted

#### OSAudit Model
**File:** `app/Models/OSAudit.php`

Records all changes to OS:
- Fields: event, action, old_values, new_values, changed_fields, status_from, status_to, description, ip_address, user_agent
- Relationships: `ordemServico()`, `user()`
- Scope methods: `forOrdemServico()`, `byEvent()`, `byAction()`, `statusChanges()`, `recentFirst()`
- Methods: `getEventLabel()`, `getActionLabel()`, `getChangeSummary()`

### Controllers

#### OrdemServicoController
**File:** `app/Http/Controllers/OrdemServicoController.php`

**New Endpoints:**
- `POST /os/{id}/solicitar-aprovacao` - Request approval (EM_ABERTO → AGUARDANDO_APROVACAO)
- `POST /os/{id}/aprovar` - Approve OS (AGUARDANDO_APROVACAO → APROVADO)
- `POST /os/{id}/contestar` - Contest OS (ANY → CONTESTAR)
- `POST /os/{id}/faturar` - Bill OS (APROVADO → FATURADO) with concurrency control
- `GET /os/aguardando-rps` - List OS ready for RPS linking
- `GET /os/{id}/auditoria` - Get audit trail
- `GET /os/{id}/permissoes` - Get permission summary

**Concurrency Control:**
The billing endpoint uses pessimistic locking to prevent race conditions:
```php
DB::beginTransaction();
$ordem = OrdemServico::lockForUpdate()->find($id);
// ... perform billing
DB::commit();
```

#### RPSController
**File:** `app/Http/Controllers/RPSController.php`

**Endpoints:**
- `GET /rps` - List all RPS
- `POST /rps` - Create RPS and link OS
- `GET /rps/{id}` - Get RPS details
- `POST /rps/{id}/vincular-ordens` - Link additional OS to RPS
- `POST /rps/{id}/cancelar` - Cancel RPS
- `POST /rps/{id}/reverter` - Revert cancelled RPS
- `GET /rps/cliente/{clienteId}` - List RPS by client
- `GET /rps/cliente/{clienteId}/ordens-aguardando` - List OS ready for linking
- `GET /rps/{id}/auditoria` - Get RPS audit trail
- `GET /rps/{id}/exportar-pdf` - Export RPS as PDF

### Migrations

**Files:**
- `2025_11_15_032659_create_ordem_servico_audits_table.php` - Audit trail table
- `2025_11_15_033008_create_rps_table.php` - RPS document table
- `2025_11_15_033058_create_ordem_servico_rps_table.php` - OS-RPS pivot table

**Audit Table Structure:**
```sql
- id
- ordem_servico_id (FK)
- event (created, updated, status_changed, deleted)
- user_id (FK - nullable)
- action (create, edit, transition, delete, approve, contest, bill, link_rps)
- old_values (JSON)
- new_values (JSON)
- changed_fields (JSON)
- status_from
- status_to
- description
- ip_address
- user_agent
- timestamps
```

**RPS Table Structure:**
```sql
- id
- cliente_id (FK)
- numero_rps (unique)
- data_emissao
- data_vencimento (nullable)
- valor_total
- valor_servicos
- valor_deducoes
- valor_impostos
- status (emitida, cancelada, revertida)
- observacoes
- criado_por (FK)
- cancelado_em
- cancelado_por (FK - nullable)
- motivo_cancelamento
- revertido_em
- revertido_por (FK - nullable)
- motivo_reversao
- timestamps
```

## Business Rules

### 1. Status Editing

**Only editable in these statuses:**
- EM_ABERTO (open)
- CONTESTAR (contested)

**Cannot edit in:**
- AGUARDANDO_APROVACAO
- APROVADO
- FATURADO
- AGUARDANDO_RPS
- RPS_EMITIDA

### 2. Faturamento (Billing) Validation

Before OS can transition to FATURADO, ALL of these must be true:

✅ Client exists and has:
  - CPF/CNPJ defined
  - Email defined

✅ Consultant exists

✅ valor_total is set and > 0

✅ OS has products/items defined

✅ No duplicate RPS exists for same client in same month

✅ data_emissao is defined

### 3. RPS Linking Rules

Multiple OS can be linked to single RPS if:
- All OS are in AGUARDANDO_RPS status
- All OS belong to SAME client
- RPS is in "emitida" status

When OS are linked to RPS:
- OS status changes from AGUARDANDO_RPS → RPS_EMITIDA
- OS-RPS relationship is recorded in pivot table
- Audit trail is created for each OS

### 4. RPS Cancellation

When RPS is cancelled:
1. All linked OS are unlinked
2. OS status reverts from RPS_EMITIDA → AGUARDANDO_RPS
3. RPS status becomes "cancelada"
4. Audit trail recorded for RPS and all linked OS
5. Cancel reason must be provided

### 5. Permission-Based Transitions

Not all users can perform all transitions:

| Action | superadmin | admin | consultor | financeiro | fiscal |
|--------|-----------|-------|-----------|------------|--------|
| Create OS | ✅ | ✅ | ✅ | ❌ | ❌ |
| Edit OS | ✅ | ✅ | own only | ❌ | ❌ |
| Delete OS | ✅ | ✅ | own only | ❌ | ❌ |
| Request Approval | ✅ | ✅ | own only | ❌ | ❌ |
| Approve OS | ✅ | ✅ | ❌ | ❌ | ❌ |
| Contest OS | ✅ | ✅ | ❌ | ❌ | ❌ |
| Bill OS | ✅ | ✅ | ❌ | ✅ | ❌ |
| Create RPS | ✅ | ✅ | ❌ | ❌ | ✅ |
| Link OS to RPS | ✅ | ✅ | ❌ | ❌ | ✅ |
| Cancel RPS | ✅ | ✅ | ❌ | ❌ | ✅ |

### 6. Concurrency Control

Billing operation uses **pessimistic locking** (SELECT ... FOR UPDATE) to prevent:
- Race conditions during faturamento
- Multiple users billing same OS simultaneously
- Data corruption

Implementation:
```php
DB::transaction(function () use ($id) {
    $ordem = OrdemServico::lockForUpdate()->find($id);
    // Safe to modify
    $ordem->update(['status' => 'faturado']);
});
```

## API Usage Examples

### 1. Create and Approve OS

```bash
# Create OS in EM_ABERTO status
POST /salvar-ordem-servico
{
  "txtOrdemConsultorId": 1,
  "slcOrdemClienteId": 5,
  "txtOrdemDataEmissao": "2025-11-15",
  "slcProdutoOrdemId": 3,
  "txtOrdemValorTotal": "1500.00"
}

# Request approval
POST /os/1/solicitar-aprovacao

# Approve OS (admin only)
POST /os/1/aprovar
```

### 2. Bill OS

```bash
# Bill OS (financeiro or admin)
POST /os/1/faturar

# Check if can bill
GET /os/1/permissoes
```

### 3. Create RPS and Link OS

```bash
# Create RPS with linked OS
POST /rps
{
  "cliente_id": 5,
  "numero_rps": "RPS2025001",
  "data_emissao": "2025-11-15",
  "valor_total": "5000.00",
  "ordem_servico_ids": [1, 2, 3]
}

# Link additional OS
POST /rps/1/vincular-ordens
{
  "ordem_servico_ids": [4, 5]
}

# Cancel RPS
POST /rps/1/cancelar
{
  "motivo": "Erro na emissão"
}
```

### 4. View Audit Trail

```bash
# Get OS audit history
GET /os/1/auditoria

# Response:
{
  "data": [
    {
      "timestamp": "2025-11-15T10:30:00Z",
      "user": "João Admin",
      "action": "Aprovação",
      "event": "Status Alterado",
      "summary": "Status alterado de aguardando_aprovacao para aprovado"
    },
    ...
  ]
}
```

## Database Transactions

Critical operations use database transactions for data integrity:

```php
DB::transaction(function () {
    // RPS creation with OS linking
    $rps = RPS::create([...]);
    $rps->linkOrdensServico($ordemServicoIds);

    // Audit all linked OS
    foreach ($ordemServicoIds as $osId) {
        $os = OrdemServico::find($osId);
        (new AuditService($os))->recordRpsLinking($rps->id);
    }
});
```

## Error Handling

All endpoints return appropriate HTTP status codes:

- **200** - Success (GET, POST with successful transition)
- **201** - Created (POST new resource)
- **400** - Bad request (validation error)
- **403** - Forbidden (permission denied)
- **404** - Not found
- **422** - Unprocessable (business logic error, invalid transition)
- **500** - Server error

Example error response:
```json
{
  "message": "Não foi possível faturar a OS. Verifique os erros:",
  "errors": [
    "Cliente não possui CPF/CNPJ cadastrado.",
    "Valor total não definido ou inválido."
  ]
}
```

## Testing

### Manual Testing Steps

1. **Create OS**: consultor user creates OS (status: EM_ABERTO)
2. **Request Approval**: consultor requests approval (status: AGUARDANDO_APROVACAO)
3. **Approve**: admin approves OS (status: APROVADO)
4. **Bill**: financeiro bills OS (status: FATURADO)
5. **Ready for RPS**: System auto-transitions to AGUARDANDO_RPS
6. **Create RPS**: fiscal creates RPS linking multiple OS
7. **Verify RPS**: Check RPS links multiple OS (status: RPS_EMITIDA)
8. **Cancel RPS**: Cancel RPS, verify OS reverted to AGUARDANDO_RPS
9. **View Audit**: View complete audit trail

### Unit Tests (To be implemented)

```php
// Test state machine transitions
public function test_valid_transition()
{
    $os = OrdemServico::create([...]);
    $stateMachine = new StateMachine($os);

    $this->assertTrue($stateMachine->canTransition(OrdemServicoStatus::AGUARDANDO_APROVACAO));
    $stateMachine->transition(OrdemServicoStatus::AGUARDANDO_APROVACAO);
    $this->assertEquals('aguardando_aprovacao', $os->refresh()->status);
}

// Test permissions
public function test_only_admin_can_approve()
{
    $os = OrdemServico::create(['status' => 'aguardando_aprovacao']);
    $consultor = User::where('papel', 'consultor')->first();

    $permissionService = new PermissionService($consultor);
    $this->assertFalse($permissionService->canApproveOS($os));
}

// Test audit trail
public function test_audit_trail_recorded()
{
    $os = OrdemServico::create([...]);
    $auditService = new AuditService($os);

    $auditService->recordStatusTransition('em_aberto', 'aguardando_aprovacao');

    $this->assertCount(1, $os->audits);
    $this->assertEquals('status_changed', $os->audits->first()->event);
}
```

## Migration Checklist

Before deploying to production:

- [ ] Run migrations: `php artisan migrate`
- [ ] Update `orden_servico` table structure if needed
- [ ] Clear all cached data: `php artisan cache:clear`
- [ ] Verify all users have appropriate roles assigned
- [ ] Test all 8 status transitions
- [ ] Verify permission checks working correctly
- [ ] Test concurrent billing operations
- [ ] Verify audit trail being recorded
- [ ] Check RPS linking with multiple OS
- [ ] Test RPS cancellation and reversion
- [ ] Monitor performance with large datasets

## Future Enhancements

1. **Notifications**: Email alerts for status changes
2. **Webhooks**: External system integration
3. **PDF Export**: Generate official RPS PDF
4. **Fiscal Integration**: Connect to fiscal authority API
5. **Dashboard**: Status distribution charts
6. **Advanced Filtering**: Filter by date range, amount, consultant
7. **Bulk Operations**: Process multiple OS at once
8. **Reporting**: Excel export with audit trail
