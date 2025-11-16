<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contato extends Model
{

    use HasFactory;

    protected $table = 'contato';

    protected $fillable = [
        'cliente_id',
        'nome',
        'email',
        'telefone',
        'recebe_email_os',
        'aniversario'
    ];

    protected $casts = [
        'recebe_email_os' => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

}