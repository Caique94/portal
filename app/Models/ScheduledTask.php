<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cron\CronExpression;

class ScheduledTask extends Model
{
    protected $fillable = [
        'type',
        'cron_expr',
        'filters',
        'last_run',
        'next_run',
        'enabled',
        'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'last_run' => 'datetime',
        'next_run' => 'datetime',
        'enabled' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeDue($query)
    {
        return $query->enabled()
            ->where('next_run', '<=', now())
            ->orWhereNull('next_run');
    }

    public function calculateNextRun(): void
    {
        try {
            $cron = new CronExpression($this->cron_expr);
            $this->next_run = $cron->getNextRunDate();
            $this->save();
        } catch (\Exception $e) {
            \Log::error("Invalid cron expression for task {$this->id}: {$this->cron_expr}");
        }
    }

    public function markAsRun(): void
    {
        $this->last_run = now();
        $this->calculateNextRun();
    }

    public function isDue(): bool
    {
        return $this->enabled &&
               ($this->next_run === null || $this->next_run <= now());
    }
}
