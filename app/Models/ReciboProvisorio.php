<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReciboProvisorio extends Model
{

        use HasFactory;

        protected $table = 'recibo_provisorio';

        protected $fillable = [
            'cliente_id',
            'numero',
            'serie',
            'data_emissao',
            'cond_pagto',
            'valor',
            'consolidada',
            'ordens_consolidadas'
        ];

        public function cliente()
        {
            return $this->belongsTo(Cliente::class);
        }

        public function parcelas()
        {
            return $this->hasMany(PagamentoParcela::class);
        }

}