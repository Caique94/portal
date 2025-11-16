<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'ordem_servico_id',
        'user_id',
        'content',
        'mentions',
    ];

    protected $casts = [
        'mentions' => 'array',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
