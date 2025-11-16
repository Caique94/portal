<?php

namespace App\Enums;

enum OrdemServicoStatus: string
{
    // Status states in workflow order
    case EM_ABERTO = 'em_aberto';
    case AGUARDANDO_APROVACAO = 'aguardando_aprovacao';
    case APROVADO = 'aprovado';
    case CONTESTAR = 'contestar';
    case AGUARDANDO_FATURAMENTO = 'aguardando_faturamento';
    case FATURADO = 'faturado';
    case AGUARDANDO_RPS = 'aguardando_rps';
    case RPS_EMITIDA = 'rps_emitida';

    /**
     * Get user-friendly label for status
     */
    public function label(): string
    {
        return match($this) {
            self::EM_ABERTO => 'Em Aberto',
            self::AGUARDANDO_APROVACAO => 'Aguardando Aprovação',
            self::APROVADO => 'Aprovado',
            self::CONTESTAR => 'Contestar',
            self::AGUARDANDO_FATURAMENTO => 'Aguardando Faturamento',
            self::FATURADO => 'Faturado',
            self::AGUARDANDO_RPS => 'Aguardando RPS',
            self::RPS_EMITIDA => 'RPS Emitida',
        };
    }

    /**
     * Get Bootstrap badge color for status
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::EM_ABERTO => 'info',           // Light blue
            self::AGUARDANDO_APROVACAO => 'warning',  // Orange
            self::APROVADO => 'success',         // Green
            self::CONTESTAR => 'danger',         // Red
            self::AGUARDANDO_FATURAMENTO => 'secondary', // Gray
            self::FATURADO => 'success',         // Green
            self::AGUARDANDO_RPS => 'warning',   // Orange
            self::RPS_EMITIDA => 'success',      // Green
        };
    }

    /**
     * Get valid transitions from current status
     */
    public function validTransitions(): array
    {
        return match($this) {
            self::EM_ABERTO => [
                self::AGUARDANDO_APROVACAO,
                self::CONTESTAR,
            ],
            self::AGUARDANDO_APROVACAO => [
                self::APROVADO,
                self::CONTESTAR,
                self::EM_ABERTO,
            ],
            self::APROVADO => [
                self::AGUARDANDO_FATURAMENTO,
                self::CONTESTAR,
            ],
            self::CONTESTAR => [
                self::EM_ABERTO,
                self::AGUARDANDO_APROVACAO,
            ],
            self::AGUARDANDO_FATURAMENTO => [
                self::FATURADO,
            ],
            self::FATURADO => [
                self::AGUARDANDO_RPS,
            ],
            self::AGUARDANDO_RPS => [
                self::RPS_EMITIDA,
            ],
            self::RPS_EMITIDA => [
                // Final state - no transitions
            ],
        };
    }

    /**
     * Check if this status allows editing
     */
    public function isEditable(): bool
    {
        return in_array($this, [
            self::EM_ABERTO,
            self::CONTESTAR,
        ]);
    }

    /**
     * Check if this status allows faturamento (billing)
     */
    public function canBeBilled(): bool
    {
        return $this === self::APROVADO;
    }

    /**
     * Check if this status can be linked to RPS
     */
    public function canLinkToRps(): bool
    {
        return $this === self::AGUARDANDO_RPS;
    }
}
