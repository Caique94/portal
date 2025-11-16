<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabelaPreco extends Model
{

    use HasFactory;

    protected $table = 'tabela_preco';

    protected $fillable = [
        'descricao',
        'ativo'
    ];

    public function produtos()
    {
        return $this->hasMany(ProdutoTabela::class);
    }

}