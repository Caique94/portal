<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagamentoUsuario extends Model
{
    protected $table = 'pagamento_usuario';

    protected $fillable = [
        'user_id',
        'titular_conta',
        'cpf_cnpj_titular',
        'banco',
        'agencia',
        'conta',
        'tipo_conta',
        'pix_key',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Get the user that owns this payment information.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
