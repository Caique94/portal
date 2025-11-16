<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicaoPagamento extends Model
{
    protected $table = 'condicoes_pagamento';

    protected $fillable = [
        'descricao',
        'numero_parcelas',
        'intervalo_dias',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean'
    ];
}
