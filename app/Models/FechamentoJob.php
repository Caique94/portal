<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FechamentoJob extends Model
{
    use HasUuids;

    protected $table = 'fechamento_jobs';

    protected $fillable = [
        'type',
        'period_start',
        'period_end',
        'filters',
        'requested_by',
        'status',
        'error_message',
        'pdf_url',
        'pdf_checksum',
        'version',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(FechamentoHistory::class, 'fechamento_job_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(FechamentoAudit::class, 'job_id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['queued', 'processing']);
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['success', 'failed']);
    }
}
