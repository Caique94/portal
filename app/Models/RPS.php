<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RPS extends Model
{
    use HasFactory;

    protected $table = 'rps';

    protected $fillable = [
        'cliente_id',
        'numero_rps',
        'data_emissao',
        'data_vencimento',
        'valor_total',
        'valor_servicos',
        'valor_deducoes',
        'valor_impostos',
        'status',
        'observacoes',
        'criado_por',
        'cancelado_em',
        'cancelado_por',
        'motivo_cancelamento',
        'revertido_em',
        'revertido_por',
        'motivo_reversao',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
        'cancelado_em' => 'datetime',
        'revertido_em' => 'datetime',
        'valor_total' => 'decimal:2',
        'valor_servicos' => 'decimal:2',
        'valor_deducoes' => 'decimal:2',
        'valor_impostos' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(User::class, 'cancelado_por');
    }

    public function revertidoPor()
    {
        return $this->belongsTo(User::class, 'revertido_por');
    }

    /**
     * Many-to-many relationship with OrdemServico
     */
    public function ordensServico()
    {
        return $this->belongsToMany(OrdemServico::class, 'ordem_servico_rps', 'rps_id', 'ordem_servico_id')
            ->withTimestamps();
    }

    /**
     * Audits relationship
     */
    public function audits()
    {
        return $this->hasMany(RPSAudit::class, 'rps_id');
    }

    /**
     * Scopes
     */
    public function scopeEmitidas($query)
    {
        return $query->where('status', 'emitida');
    }

    public function scopeCanceladas($query)
    {
        return $query->where('status', 'cancelada');
    }

    public function scopeRevertidas($query)
    {
        return $query->where('status', 'revertida');
    }

    public function scopeByCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('data_emissao', [$startDate, $endDate]);
    }

    /**
     * Methods
     */

    /**
     * Check if RPS can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'emitida';
    }

    /**
     * Check if RPS can be reverted
     */
    public function canBeReverted(): bool
    {
        return $this->status === 'cancelada';
    }

    /**
     * Get RPS status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'emitida' => 'Emitida',
            'cancelada' => 'Cancelada',
            'revertida' => 'Revertida',
            default => 'Desconhecida',
        };
    }

    /**
     * Link multiple OS to this RPS
     */
    public function linkOrdensServico(array $ordemServicoIds): bool
    {
        try {
            // Verify all OS exist and are in the correct status
            $ordensServico = OrdemServico::whereIn('id', $ordemServicoIds)
                ->byStatus(\App\Enums\OrdemServicoStatus::AGUARDANDO_RPS)
                ->where('cliente_id', $this->cliente_id)
                ->get();

            if ($ordensServico->count() !== count($ordemServicoIds)) {
                return false;
            }

            // Attach all OS to this RPS
            $this->ordensServico()->attach($ordemServicoIds);

            // Update OS status to RPS_EMITIDA (numeric: 7)
            OrdemServico::whereIn('id', $ordemServicoIds)->update([
                'status' => 7,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Unlink OS from this RPS (on cancellation/reversion)
     */
    public function unlinkOrdensServico(): bool
    {
        try {
            $ordemIds = $this->ordensServico()->pluck('ordem_servico_id')->toArray();

            // Detach all OS from this RPS
            $this->ordensServico()->detach();

            // Revert OS status back to AGUARDANDO_RPS (numeric: 6)
            OrdemServico::whereIn('id', $ordemIds)->update([
                'status' => 6,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cancel RPS
     */
    public function cancel(int $userId, string $motivo): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        try {
            $this->update([
                'status' => 'cancelada',
                'cancelado_em' => now(),
                'cancelado_por' => $userId,
                'motivo_cancelamento' => $motivo,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Revert RPS (from cancelled state)
     */
    public function revert(int $userId, string $motivo): bool
    {
        if (!$this->canBeReverted()) {
            return false;
        }

        try {
            $this->update([
                'status' => 'revertida',
                'revertido_em' => now(),
                'revertido_por' => $userId,
                'motivo_reversao' => $motivo,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
