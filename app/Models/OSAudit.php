<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OSAudit extends Model
{
    protected $table = 'ordem_servico_audits';

    protected $fillable = [
        'ordem_servico_id',
        'event',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'changed_fields',
        'status_from',
        'status_to',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scopes
     */
    public function scopeForOrdemServico($query, $ordemServicoId)
    {
        return $query->where('ordem_servico_id', $ordemServicoId);
    }

    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeStatusChanges($query)
    {
        return $query->where('event', 'status_changed');
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * Get human-readable event name
     */
    public function getEventLabel(): string
    {
        return match($this->event) {
            'created' => 'Criado',
            'updated' => 'Atualizado',
            'status_changed' => 'Status Alterado',
            'deleted' => 'Deletado',
            default => $this->event,
        };
    }

    /**
     * Get human-readable action name
     */
    public function getActionLabel(): string
    {
        return match($this->action) {
            'create' => 'Criação',
            'edit' => 'Edição',
            'transition' => 'Transição de Status',
            'delete' => 'Exclusão',
            'approve' => 'Aprovação',
            'contest' => 'Contestação',
            'bill' => 'Faturamento',
            'link_rps' => 'Vinculação RPS',
            default => $this->action,
        };
    }

    /**
     * Get summary of what changed
     */
    public function getChangeSummary(): string
    {
        if ($this->status_from && $this->status_to) {
            return "Status alterado de {$this->status_from} para {$this->status_to}";
        }

        if ($this->changed_fields && is_array($this->changed_fields)) {
            $fields = implode(', ', array_keys($this->changed_fields));
            return "Campos alterados: {$fields}";
        }

        return $this->description ?? 'Sem descrição';
    }
}
