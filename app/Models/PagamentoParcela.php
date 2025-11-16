<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagamentoParcela extends Model
{
    use HasFactory;

    protected $table = 'pagamento_parcelas';

    protected $fillable = [
        'recibo_provisorio_id',
        'numero_parcela',
        'total_parcelas',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'status',
        'observacao'
    ];

    protected $casts = [
        'valor' => 'decimal:2'
    ];

    protected $dates = [
        'data_vencimento',
        'data_pagamento'
    ];

    // Relacionamento com ReciboProvisorio
    public function reciboProvisorio()
    {
        return $this->belongsTo(ReciboProvisorio::class, 'recibo_provisorio_id');
    }

    // Accessor para formatar data_vencimento como string ISO (Y-m-d)
    public function getDataVencimentoAttribute($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }
        return $value;
    }

    // Accessor para formatar data_pagamento como string ISO (Y-m-d)
    public function getDataPagamentoAttribute($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }
        return $value;
    }
}
