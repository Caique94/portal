<?php

namespace App\Services;

use App\Enums\OrdemServicoStatus;
use App\Models\OrdemServico;
use Illuminate\Database\Eloquent\Model;

class StateMachine
{
    protected Model $model;
    protected string $statusField;

    public function __construct(Model $model, string $statusField = 'status')
    {
        $this->model = $model;
        $this->statusField = $statusField;
    }

    /**
     * Get current status
     */
    public function getCurrentStatus(): OrdemServicoStatus
    {
        $status = $this->model->{$this->statusField};

        if ($status instanceof OrdemServicoStatus) {
            return $status;
        }

        // Try to convert string to enum
        return OrdemServicoStatus::tryFrom($status) ?? OrdemServicoStatus::EM_ABERTO;
    }

    /**
     * Check if transition is valid
     */
    public function canTransition(OrdemServicoStatus $targetStatus): bool
    {
        $current = $this->getCurrentStatus();
        return in_array($targetStatus, $current->validTransitions());
    }

    /**
     * Get valid next statuses
     */
    public function getValidTransitions(): array
    {
        return $this->getCurrentStatus()->validTransitions();
    }

    /**
     * Transition to new status with optional data update
     */
    public function transition(
        OrdemServicoStatus $targetStatus,
        array $additionalData = [],
        ?int $userId = null
    ): bool {
        if (!$this->canTransition($targetStatus)) {
            return false;
        }

        // Store old values for audit
        $oldValues = $this->model->getAttributes();

        // Map enum to numeric status for database compatibility
        $statusMap = [
            OrdemServicoStatus::EM_ABERTO->value => 1,
            OrdemServicoStatus::AGUARDANDO_APROVACAO->value => 2,
            OrdemServicoStatus::CONTESTAR->value => 3,
            OrdemServicoStatus::APROVADO->value => 4,
            OrdemServicoStatus::AGUARDANDO_FATURAMENTO->value => 4,
            OrdemServicoStatus::FATURADO->value => 5,
            OrdemServicoStatus::AGUARDANDO_RPS->value => 6,
            OrdemServicoStatus::RPS_EMITIDA->value => 7,
        ];

        // Prepare update data with numeric status
        $updateData = array_merge(
            [$this->statusField => $statusMap[$targetStatus->value] ?? 1],
            $additionalData
        );

        // Add timestamp
        $updateData['updated_at'] = now();

        // If transitioning due to approval/rejection, track who did it
        if ($userId && $this->needsApprovalTracking($targetStatus)) {
            $updateData['approved_by'] = $userId;
            $updateData['approved_at'] = now();
        }

        // Perform update
        $this->model->update($updateData);

        // Record in audit trail if needed
        if (method_exists($this->model, 'audits')) {
            event(new \App\Events\OrdemServicoStatusChanged(
                $this->model,
                $this->getCurrentStatus(),
                $targetStatus,
                $oldValues,
                $userId
            ));
        }

        return true;
    }

    /**
     * Check if this transition needs approval tracking
     */
    private function needsApprovalTracking(OrdemServicoStatus $targetStatus): bool
    {
        return in_array($targetStatus, [
            OrdemServicoStatus::APROVADO,
            OrdemServicoStatus::CONTESTAR,
        ]);
    }

    /**
     * Get all available statuses
     */
    public static function getAllStatuses(): array
    {
        return OrdemServicoStatus::cases();
    }

    /**
     * Get status as options for select/dropdown
     */
    public static function getStatusOptions(): array
    {
        $options = [];
        foreach (OrdemServicoStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }
        return $options;
    }
}
