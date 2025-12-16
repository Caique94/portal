<?php

namespace App\Models;

use App\Enums\OrdemServicoStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{

    use HasFactory;

    protected $table = 'ordem_servico';

    protected $fillable = [
        'consultor_id',
        'cliente_id',
        'projeto_id',
        'data_emissao',
        'tipo_despesa',
        'valor_despesa',
        'detalhamento_despesa',
        'status',
        'motivo_contestacao',
        'assunto',
        'nr_atendimento',
        'preco_produto',
        'valor_total',

        'produto_tabela_id',
        'hora_inicio',
        'hora_final',
        'hora_desconto',
        'qtde_total',
        'detalhamento',

        'nr_rps',
        'cond_pagto',
        'valor_rps',

        // Campos de deslocamento
        'km',
        'deslocamento',
        'is_presencial',
        'observacao',
        'descricao',

        // Campos de aprovação
        'approval_status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'data_emissao' => 'date',
        // Note: status is handled manually to support both legacy numeric and new enum string values
    ];

    /**
     * Serialize data_emissao as YYYY-MM-DD string for JSON responses
     */
    protected function getDataEmissaoAttribute($value)
    {
        if ($value) {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        }
        return null;
    }

    // Relacionamentos
    public function consultor()
    {
        return $this->belongsTo(User::class, 'consultor_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function produtoTabela()
    {
        return $this->belongsTo(ProdutoTabela::class, 'produto_tabela_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class, 'projeto_id');
    }

    // New relationships for RPS workflow
    public function audits()
    {
        return $this->hasMany(OSAudit::class, 'ordem_servico_id');
    }

    public function rpsDocuments()
    {
        return $this->belongsToMany(RPS::class, 'ordem_servico_rps', 'ordem_servico_id', 'rps_id')
            ->withTimestamps();
    }

    // Scope methods for status filtering
    public function scopeByStatus($query, OrdemServicoStatus $status)
    {
        return $query->where('status', $status->value);
    }

    public function scopeEmAberto($query)
    {
        return $query->byStatus(OrdemServicoStatus::EM_ABERTO);
    }

    public function scopeAguardandoAprovacao($query)
    {
        return $query->byStatus(OrdemServicoStatus::AGUARDANDO_APROVACAO);
    }

    public function scopeAprovado($query)
    {
        return $query->byStatus(OrdemServicoStatus::APROVADO);
    }

    public function scopeFaturado($query)
    {
        return $query->byStatus(OrdemServicoStatus::FATURADO);
    }

    public function scopeAguardandoRps($query)
    {
        return $query->byStatus(OrdemServicoStatus::AGUARDANDO_RPS);
    }

    public function scopeRpsEmitida($query)
    {
        return $query->byStatus(OrdemServicoStatus::RPS_EMITIDA);
    }

    /**
     * Get the status enum value (supports legacy numeric status)
     */
    public function getStatus(): OrdemServicoStatus
    {
        if ($this->status instanceof OrdemServicoStatus) {
            return $this->status;
        }

        // Try to convert from enum string value
        $enumStatus = OrdemServicoStatus::tryFrom($this->status);
        if ($enumStatus) {
            return $enumStatus;
        }

        // Support legacy numeric status values (map to new enum strings)
        $legacyStatusMap = [
            1 => OrdemServicoStatus::EM_ABERTO,
            2 => OrdemServicoStatus::AGUARDANDO_APROVACAO,
            3 => OrdemServicoStatus::CONTESTAR,
            4 => OrdemServicoStatus::APROVADO, // Legacy "Aguardando Faturamento" → Aprovado
            5 => OrdemServicoStatus::FATURADO,
            6 => OrdemServicoStatus::AGUARDANDO_RPS,
        ];

        $numeric = (int)$this->status;
        return $legacyStatusMap[$numeric] ?? OrdemServicoStatus::EM_ABERTO;
    }

    /**
     * Check if OS can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->getStatus()->isEditable();
    }

    /**
     * Check if OS can be billed
     */
    public function canBeBilled(): bool
    {
        return $this->getStatus()->canBeBilled();
    }

    /**
     * Calcular horas corretamente (qtde_total ou baseado em hora_inicio/hora_final/hora_desconto)
     */
    public function getHoras(): float
    {
        // Se qtde_total está preenchido, usar ele
        if (!empty($this->qtde_total)) {
            return (float) $this->qtde_total;
        }

        // Se tem hora_inicio e hora_final, calcular a diferença
        if ($this->hora_inicio && $this->hora_final) {
            try {
                $inicio = \Carbon\Carbon::createFromFormat('H:i:s', $this->hora_inicio);
                $final = \Carbon\Carbon::createFromFormat('H:i:s', $this->hora_final);

                if ($final->greaterThanOrEqualTo($inicio)) {
                    $minutos = $final->diffInMinutes($inicio);
                    $horas = $minutos / 60;

                    // Subtrair desconto se houver
                    if (!empty($this->hora_desconto)) {
                        $horas -= (float) $this->hora_desconto;
                    }

                    return max(0, $horas);
                }
            } catch (\Exception $e) {
                // Se falhar, retornar qtde_total ou 0
                return (float) ($this->qtde_total ?? 0);
            }
        }

        return 0;
    }

    /**
     * Accessor para horas (compatível com views que usam $os->horas)
     */
    public function getHorasAttribute()
    {
        return $this->getHoras();
    }

}