<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSequentialCode;

class Produto extends Model
{

    use HasFactory, HasSequentialCode;

    protected $table = 'produto';

    protected $fillable = [
        'codigo',
        'descricao',
        'narrativa',
        'is_presencial',
        'ativo'
    ];

    protected $casts = [
        'is_presencial' => 'boolean',
        'ativo' => 'boolean'
    ];

}