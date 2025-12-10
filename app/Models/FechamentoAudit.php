<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FechamentoAudit extends Model
{
    protected $table = 'fechamento_audit';

    public $timestamps = false;

    protected $fillable = [
        'job_id',
        'action',
        'user_id',
        'details',
        'timestamp',
    ];

    protected $casts = [
        'details' => 'array',
        'timestamp' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(FechamentoJob::class, 'job_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public static function log(string $action, ?string $jobId, int $userId, ?array $details = null): void
    {
        self::create([
            'job_id' => $jobId,
            'action' => $action,
            'user_id' => $userId,
            'details' => $details,
            'timestamp' => now(),
        ]);
    }
}
