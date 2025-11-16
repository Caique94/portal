<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoTabela extends Model
{

    use HasFactory;

    protected $table = 'produto_tabela';

    protected $fillable = [
        'tabela_preco_id',
        'produto_id',
        'preco',
        'ativo'
    ];

    public function tabela()
    {
        return $this->belongsTo(TabelaPreco::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

}