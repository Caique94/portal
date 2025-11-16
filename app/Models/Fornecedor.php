<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSequentialCode;

class Fornecedor extends Model
{

    use HasFactory, HasSequentialCode;

    protected $table = 'fornecedor';

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
        'estado'
    ];

}