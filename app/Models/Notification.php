<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'ordem_servico_id',
        'title',
        'message',
        'type',
        'action_url',
        'data',
        'read_at',
        'email_sent',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array',
        'email_sent' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Ordem de Serviço associated with the notification
     */
    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Get unread notifications count for a user
     */
    public static function getUnreadCount($userId): int
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get unread notifications for a user
     */
    public static function getUnreadNotifications($userId, $limit = 10)
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get notification type icon
     */
    public function getTypeIcon(): string
    {
        return match($this->type) {
            'aprovada' => 'bi-check-circle-fill text-success',
            'rejeitada' => 'bi-x-circle-fill text-danger',
            'faturada' => 'bi-file-earmark-check text-info',
            'nova_os' => 'bi-plus-circle text-primary',
            'comentario' => 'bi-chat-left-text text-secondary',
            'rps_emitida' => 'bi-file-text text-warning',
            default => 'bi-bell text-muted',
        };
    }

    /**
     * Get notification type color
     */
    public function getTypeColor(): string
    {
        return match($this->type) {
            'aprovada' => 'success',
            'rejeitada' => 'danger',
            'faturada' => 'info',
            'nova_os' => 'primary',
            'comentario' => 'secondary',
            'rps_emitida' => 'warning',
            default => 'light',
        };
    }
}
