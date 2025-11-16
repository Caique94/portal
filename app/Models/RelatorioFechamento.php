<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelatorioFechamento extends Model
{
    protected $table = 'relatorio_fechamento';

    protected $fillable = [
        'consultor_id',
        'data_inicio',
        'data_fim',
        'valor_total',
        'total_os',
        'status',
        'data_envio_email',
        'observacoes',
        'aprovado_por',
        'data_aprovacao',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'valor_total' => 'decimal:2',
        'data_envio_email' => 'datetime',
        'data_aprovacao' => 'datetime',
    ];

    /**
     * Consultor que gerou o relatório
     */
    public function consultor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultor_id');
    }

    /**
     * Usuário que aprovou o relatório
     */
    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    /**
     * Obter todas as OS do período
     */
    public function ordemServicos()
    {
        if (!$this->data_inicio || !$this->data_fim) {
            return collect();
        }

        return OrdemServico::with('cliente')
            ->where('consultor_id', $this->consultor_id)
            ->where('status', '<=', 5)
            ->whereBetween('created_at', [
                \Carbon\Carbon::parse($this->data_inicio)->startOfDay(),
                \Carbon\Carbon::parse($this->data_fim)->endOfDay(),
            ])
            ->orderByDesc('id')
            ->get();
    }
}
