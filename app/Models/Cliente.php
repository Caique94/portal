<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSequentialCode;

class Cliente extends Model
{

    use HasFactory, HasSequentialCode;

    protected $table = 'cliente';

    protected $fillable = [
        'codigo',
        'loja',
        'nome',
        'nome_fantasia',
        'tipo',
        'cgc',
        'contato',
        'endereco',
        'municipio',
        'estado',
        'km',
        'deslocamento',
        'tabela_preco_id',
        'valor_hora'
    ];

    public function contatos()
    {
        return $this->hasMany(Contato::class);
    }

    /**
     * Get all order services for this client
     */
    public function ordemServicos()
    {
        return $this->hasMany(OrdemServico::class);
    }

    /**
     * Get all projetos for this client
     */
    public function projetos()
    {
        return $this->hasMany(Projeto::class);
    }

    /**
     * Retorna o nome do cliente como string
     */
    public function __toString()
    {
        return $this->razao_social ?? $this->nome ?? '';
    }

}