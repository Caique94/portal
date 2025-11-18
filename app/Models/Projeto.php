<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projeto extends Model
{
    use \App\Traits\HasSequentialCode;

    protected $fillable = [
        'codigo',
        'numero_atendimento',
        'cliente_id',
        'nome',
        'descricao',
        'status',
        'data_inicio',
        'data_fim',
        'horas_alocadas',
        'horas_consumidas',
        'horas_restantes',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'horas_alocadas' => 'decimal:2',
        'horas_consumidas' => 'decimal:2',
        'horas_restantes' => 'decimal:2',
    ];

    /**
     * Get the cliente that owns the projeto.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Get the ordem servicos for the projeto.
     */
    public function ordemServicos()
    {
        return $this->hasMany(OrdemServico::class);
    }
}
