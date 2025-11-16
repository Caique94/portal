<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasSequentialCode;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasSequentialCode;

    protected $fillable = [
        'codigo','name','email','password','data_nasc','papel','cgc','celular',
        'valor_hora','valor_desloc','valor_km','salario_base','ativo',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'data_nasc'     => 'date',
        'ativo'         => 'boolean',
    ];

    /**
     * Get all ordem servicos created by this consultant
     */
    public function ordemServicos()
    {
        return $this->hasMany(OrdemServico::class, 'consultor_id');
    }

    /**
     * Get all relatorios de fechamento for this consultant
     */
    public function relatoriosFechamento()
    {
        return $this->hasMany(RelatorioFechamento::class, 'consultor_id');
    }

    /**
     * Get all relatorios de fechamento that this user approved
     */
    public function relatoriosAprovados()
    {
        return $this->hasMany(RelatorioFechamento::class, 'aprovado_por');
    }
}
