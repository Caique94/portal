<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\RPS;
use App\Models\User;
use App\Enums\OrdemServicoStatus;

class PermissionService
{
    protected User $user;

    /**
     * Role definitions in the system
     */
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_CONSULTOR = 'consultor';
    const ROLE_FINANCEIRO = 'financeiro';
    const ROLE_FISCAL = 'fiscal';

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    /**
     * Get user's role
     */
    public function getUserRole(): string
    {
        return $this->user?->papel ?? 'guest';
    }

    /**
     * Check if user can view OS
     */
    public function canViewOS(OrdemServico $os): bool
    {
        $role = $this->getUserRole();

        return match($role) {
            self::ROLE_SUPERADMIN => true,
            self::ROLE_ADMIN => true,
            self::ROLE_CONSULTOR => $os->consultor_id === $this->user->id,
            self::ROLE_FINANCEIRO => true,
            self::ROLE_FISCAL => true,
            default => false,
        };
    }

    /**
     * Check if user can create OS
     */
    public function canCreateOS(): bool
    {
        $role = $this->getUserRole();

        return in_array($role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_CONSULTOR,
        ]);
    }

    /**
     * Check if user can edit OS
     */
    public function canEditOS(OrdemServico $os): bool
    {
        $role = $this->getUserRole();

        // Must be editable status
        if (!$os->canBeEdited()) {
            return false;
        }

        return match($role) {
            self::ROLE_SUPERADMIN => true,
            self::ROLE_ADMIN => true,
            self::ROLE_CONSULTOR => $os->consultor_id === $this->user->id,
            default => false,
        };
    }

    /**
     * Check if user can delete OS
     */
    public function canDeleteOS(OrdemServico $os): bool
    {
        $role = $this->getUserRole();

        // Can only delete OS in "Em Aberto" status
        if ($os->getStatus() !== OrdemServicoStatus::EM_ABERTO) {
            return false;
        }

        return match($role) {
            self::ROLE_SUPERADMIN => true,
            self::ROLE_ADMIN => true,
            self::ROLE_CONSULTOR => $os->consultor_id === $this->user->id,
            default => false,
        };
    }

    /**
     * Check if user can request approval for OS
     */
    public function canRequestApproval(OrdemServico $os): bool
    {
        $role = $this->getUserRole();
        $status = $os->getStatus();

        if (!in_array($status, [OrdemServicoStatus::EM_ABERTO, OrdemServicoStatus::CONTESTAR])) {
            return false;
        }

        return match($role) {
            self::ROLE_SUPERADMIN => true,
            self::ROLE_ADMIN => true,
            self::ROLE_CONSULTOR => $os->consultor_id === $this->user->id,
            default => false,
        };
    }

    /**
     * Check if user can approve OS
     */
    public function canApproveOS(OrdemServico $os): bool
    {
        $role = $this->getUserRole();
        $status = $os->getStatus();

        if ($status !== OrdemServicoStatus::AGUARDANDO_APROVACAO) {
            return false;
        }

        return in_array($role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
        ]);
    }

    /**
     * Check if user can contest OS
     */
    public function canContestOS(OrdemServico $os): bool
    {
        $role = $this->getUserRole();
        $status = $os->getStatus();

        if (!in_array($status, [OrdemServicoStatus::AGUARDANDO_APROVACAO, OrdemServicoStatus::APROVADO])) {
            return false;
        }

        return in_array($role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
        ]);
    }

    /**
     * Check if user can bill OS (transition to Faturado)
     */
    public function canBillOS(OrdemServico $os): bool
    {
        $role = $this->getUserRole();
        $status = $os->getStatus();

        if ($status !== OrdemServicoStatus::APROVADO) {
            return false;
        }

        return in_array($role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_FINANCEIRO,
        ]);
    }

    /**
     * Check if user can link OS to RPS
     */
    public function canLinkToRPS(OrdemServico $os): bool
    {
        $role = $this->getUserRole();
        $status = $os->getStatus();

        if ($status !== OrdemServicoStatus::AGUARDANDO_RPS) {
            return false;
        }

        return in_array($role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_FISCAL,
        ]);
    }

    /**
     * Check if user can view RPS
     */
    public function canViewRPS(RPS $rps): bool
    {
        $role = $this->getUserRole();

        return match($role) {
            self::ROLE_SUPERADMIN => true,
            self::ROLE_ADMIN => true,
            self::ROLE_FINANCEIRO => true,
            self::ROLE_FISCAL => true,
            self::ROLE_CONSULTOR => true, // Consultores can view their OS linked to RPS
            default => false,
        };
    }

    /**
     * Check if user can create RPS
     */
    public function canCreateRPS(): bool
    {
        $role = $this->getUserRole();

        return in_array($role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_FISCAL,
        ]);
    }

    /**
     * Check if user can cancel RPS
     */
    public function canCancelRPS(RPS $rps): bool
    {
        $role = $this->getUserRole();

        if (!$rps->canBeCancelled()) {
            return false;
        }

        return in_array($role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_FISCAL,
        ]);
    }

    /**
     * Check if user can revert RPS
     */
    public function canRevertRPS(RPS $rps): bool
    {
        $role = $this->getUserRole();

        if (!$rps->canBeReverted()) {
            return false;
        }

        return in_array($role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_FISCAL,
        ]);
    }

    /**
     * Check if user can view audit trail
     */
    public function canViewAudit(OrdemServico $os): bool
    {
        $role = $this->getUserRole();

        return match($role) {
            self::ROLE_SUPERADMIN => true,
            self::ROLE_ADMIN => true,
            self::ROLE_CONSULTOR => $os->consultor_id === $this->user->id,
            self::ROLE_FINANCEIRO => true,
            self::ROLE_FISCAL => true,
            default => false,
        };
    }

    /**
     * Get all allowed statuses user can transition OS to
     */
    public function getAllowedStatusTransitions(OrdemServico $os): array
    {
        $role = $this->getUserRole();
        $currentStatus = $os->getStatus();
        $validTransitions = $currentStatus->validTransitions();

        // Filter based on permissions
        return array_filter($validTransitions, function(OrdemServicoStatus $status) use ($role, $os) {
            return match($status) {
                OrdemServicoStatus::AGUARDANDO_APROVACAO => $this->canRequestApproval($os),
                OrdemServicoStatus::APROVADO => $this->canApproveOS($os),
                OrdemServicoStatus::CONTESTAR => $this->canContestOS($os),
                OrdemServicoStatus::FATURADO => $this->canBillOS($os),
                OrdemServicoStatus::RPS_EMITIDA => $this->canLinkToRPS($os),
                default => false,
            };
        });
    }

    /**
     * Get permission summary for user
     */
    public function getPermissionSummary(OrdemServico $os): array
    {
        return [
            'can_view' => $this->canViewOS($os),
            'can_edit' => $this->canEditOS($os),
            'can_delete' => $this->canDeleteOS($os),
            'can_request_approval' => $this->canRequestApproval($os),
            'can_approve' => $this->canApproveOS($os),
            'can_contest' => $this->canContestOS($os),
            'can_bill' => $this->canBillOS($os),
            'can_link_rps' => $this->canLinkToRPS($os),
            'can_view_audit' => $this->canViewAudit($os),
            'allowed_transitions' => $this->getAllowedStatusTransitions($os),
        ];
    }
}
