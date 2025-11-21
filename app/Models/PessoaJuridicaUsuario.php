<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PessoaJuridicaUsuario extends Model
{
    protected $table = 'pessoa_juridica_usuario';

    protected $fillable = [
        'user_id',
        'cnpj',
        'razao_social',
        'nome_fantasia',
        'inscricao_estadual',
        'inscricao_municipal',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'telefone',
        'email',
        'site',
        'ramo_atividade',
        'data_constituicao',
        'ativo',
    ];

    protected $casts = [
        'data_constituicao' => 'date',
        'ativo' => 'boolean',
    ];

    /**
     * Get the user that owns this legal person information.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
