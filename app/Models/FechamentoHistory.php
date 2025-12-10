<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FechamentoHistory extends Model
{
    protected $table = 'fechamento_history';

    protected $fillable = [
        'fechamento_job_id',
        'data',
        'tag',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(FechamentoJob::class, 'fechamento_job_id');
    }

    public function scopeByTag($query, string $tag)
    {
        return $query->where('tag', $tag);
    }
}
