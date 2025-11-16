<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\OSAudit;
use Illuminate\Support\Facades\Request;

class AuditService
{
    protected OrdemServico $ordemServico;
    protected ?int $userId;

    public function __construct(OrdemServico $ordemServico, ?int $userId = null)
    {
        $this->ordemServico = $ordemServico;
        $this->userId = $userId ?? auth()->id();
    }

    /**
     * Record creation of OS
     */
    public function recordCreation(array $attributes): OSAudit
    {
        return $this->log(
            event: 'created',
            action: 'create',
            newValues: $attributes,
            description: 'Ordem de Serviço criada'
        );
    }

    /**
     * Record update of OS
     */
    public function recordUpdate(array $oldValues, array $newValues): OSAudit
    {
        $changedFields = $this->getChangedFields($oldValues, $newValues);

        return $this->log(
            event: 'updated',
            action: 'edit',
            oldValues: $oldValues,
            newValues: $newValues,
            changedFields: $changedFields,
            description: "Ordem de Serviço atualizada. Campos alterados: " . implode(', ', array_keys($changedFields))
        );
    }

    /**
     * Record status transition
     */
    public function recordStatusTransition(string $statusFrom, string $statusTo, array $additionalData = []): OSAudit
    {
        $oldValues = ['status' => $statusFrom];
        $newValues = ['status' => $statusTo, ...$additionalData];

        return $this->log(
            event: 'status_changed',
            action: 'transition',
            oldValues: $oldValues,
            newValues: $newValues,
            statusFrom: $statusFrom,
            statusTo: $statusTo,
            description: "Status alterado de {$statusFrom} para {$statusTo}"
        );
    }

    /**
     * Record approval
     */
    public function recordApproval(): OSAudit
    {
        return $this->log(
            event: 'status_changed',
            action: 'approve',
            statusFrom: 'aguardando_aprovacao',
            statusTo: 'aprovado',
            description: 'Ordem de Serviço aprovada'
        );
    }

    /**
     * Record rejection/contestation
     */
    public function recordContestacao(string $motivo): OSAudit
    {
        return $this->log(
            event: 'status_changed',
            action: 'contest',
            statusFrom: $this->ordemServico->status,
            statusTo: 'contestar',
            newValues: ['motivo_contestacao' => $motivo],
            description: "Ordem de Serviço contestada. Motivo: {$motivo}"
        );
    }

    /**
     * Record faturamento (billing)
     */
    public function recordBilling(array $billingData): OSAudit
    {
        return $this->log(
            event: 'status_changed',
            action: 'bill',
            statusFrom: 'aprovado',
            statusTo: 'faturado',
            newValues: $billingData,
            description: 'Ordem de Serviço faturada'
        );
    }

    /**
     * Record RPS linking
     */
    public function recordRpsLinking(int $rpsId): OSAudit
    {
        return $this->log(
            event: 'status_changed',
            action: 'link_rps',
            statusFrom: 'aguardando_rps',
            statusTo: 'rps_emitida',
            newValues: ['rps_id' => $rpsId],
            description: "Ordem de Serviço vinculada à RPS #{$rpsId}"
        );
    }

    /**
     * Record deletion (soft delete)
     */
    public function recordDeletion(string $reason = null): OSAudit
    {
        return $this->log(
            event: 'deleted',
            action: 'delete',
            oldValues: $this->ordemServico->getAttributes(),
            description: $reason ? "Ordem de Serviço deletada. Motivo: {$reason}" : 'Ordem de Serviço deletada'
        );
    }

    /**
     * Record RPS cancellation reversal on OS
     */
    public function recordRpsCancellation(): OSAudit
    {
        return $this->log(
            event: 'status_changed',
            action: 'link_rps',
            statusFrom: 'rps_emitida',
            statusTo: 'aguardando_rps',
            description: 'Vinculação com RPS removida (RPS cancelada)'
        );
    }

    /**
     * Base log method
     */
    protected function log(
        string $event,
        string $action,
        array $oldValues = [],
        array $newValues = [],
        array $changedFields = [],
        string $statusFrom = null,
        string $statusTo = null,
        string $description = null
    ): OSAudit {
        $audit = new OSAudit([
            'ordem_servico_id' => $this->ordemServico->id,
            'event' => $event,
            'action' => $action,
            'user_id' => $this->userId,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'changed_fields' => !empty($changedFields) ? $changedFields : null,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        $audit->save();
        return $audit;
    }

    /**
     * Get fields that changed between old and new values
     */
    protected function getChangedFields(array $oldValues, array $newValues): array
    {
        $changed = [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changed[$key] = [
                    'from' => $oldValue,
                    'to' => $newValue,
                ];
            }
        }

        return $changed;
    }

    /**
     * Get audit history for OS
     */
    public function getHistory($limit = 50)
    {
        return $this->ordemServico->audits()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get status change history
     */
    public function getStatusHistory()
    {
        return $this->ordemServico->audits()
            ->statusChanges()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get who made the last change
     */
    public function getLastModifiedBy()
    {
        return $this->ordemServico->audits()
            ->orderByDesc('created_at')
            ->first()?->user;
    }

    /**
     * Get timeline of changes
     */
    public function getTimeline()
    {
        return $this->ordemServico->audits()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($audit) => [
                'timestamp' => $audit->created_at,
                'user' => $audit->user?->name ?? 'Sistema',
                'action' => $audit->getActionLabel(),
                'event' => $audit->getEventLabel(),
                'summary' => $audit->getChangeSummary(),
            ]);
    }
}
