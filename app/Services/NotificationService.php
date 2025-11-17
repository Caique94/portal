<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\OrdemServico;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Create a notification
     */
    public function create(
        $userId,
        string $title,
        string $message,
        string $type,
        ?int $ordemServicoId = null,
        ?string $actionUrl = null,
        ?array $data = null,
        bool $sendEmail = false
    ): Notification {
        \Log::info("NotificationService::create called", [
            'user_id' => $userId,
            'title' => $title,
            'type' => $type,
            'send_email' => $sendEmail,
        ]);

        $notification = Notification::create([
            'user_id' => $userId,
            'ordem_servico_id' => $ordemServicoId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'data' => $data,
            'email_sent' => false,
        ]);

        \Log::info("Notification created successfully", [
            'notification_id' => $notification->id,
            'user_id' => $userId,
        ]);

        // Send email if requested
        if ($sendEmail) {
            \Log::info("Sending email notification for notification ID: {$notification->id}");
            $this->sendEmailNotification($notification);
        }

        return $notification;
    }

    /**
     * Notify OS approval
     */
    public function notifyOsApproved(OrdemServico $os, User $approver): Notification
    {
        \Log::info("notifyOsApproved called", [
            'os_id' => $os->id,
            'approver_id' => $approver->id,
            'consultor_id' => $os->consultor_id,
        ]);

        $consultorId = $os->consultor_id;
        $client = $os->cliente->nome ?? 'Cliente';

        \Log::info("Creating approval notification", [
            'consultor_id' => $consultorId,
            'client_name' => $client,
            'os_id' => $os->id,
        ]);

        return $this->create(
            userId: $consultorId,
            title: '✅ Ordem de Serviço Aprovada',
            message: "Sua OS #{$os->id} do cliente {$client} foi aprovada por {$approver->name}",
            type: 'aprovada',
            ordemServicoId: $os->id,
            actionUrl: route('ordem-servico.show', $os->id),
            data: [
                'cliente' => $client,
                'valor' => $os->valor_total,
                'status' => $os->status,
            ],
            sendEmail: true
        );
    }

    /**
     * Notify OS rejection
     */
    public function notifyOsRejected(OrdemServico $os, User $rejector, ?string $reason = null): Notification
    {
        $consultorId = $os->consultor_id;
        $client = $os->cliente->nome ?? 'Cliente';

        return $this->create(
            userId: $consultorId,
            title: '❌ Ordem de Serviço Rejeitada',
            message: "Sua OS #{$os->id} do cliente {$client} foi rejeitada. Motivo: " . ($reason ?? 'Verifique os detalhes'),
            type: 'rejeitada',
            ordemServicoId: $os->id,
            actionUrl: route('ordem-servico.show', $os->id),
            data: [
                'cliente' => $client,
                'motivo' => $reason,
                'rejector' => $rejector->name,
            ],
            sendEmail: true
        );
    }

    /**
     * Notify OS billed
     */
    public function notifyOsBilled(OrdemServico $os): Notification
    {
        \Log::info("notifyOsBilled called", [
            'os_id' => $os->id,
            'consultor_id' => $os->consultor_id,
        ]);

        $consultorId = $os->consultor_id;
        $client = $os->cliente->nome ?? 'Cliente';

        \Log::info("Creating billing notification", [
            'consultor_id' => $consultorId,
            'client_name' => $client,
            'os_id' => $os->id,
            'valor_total' => $os->valor_total,
        ]);

        return $this->create(
            userId: $consultorId,
            title: '💰 Ordem de Serviço Faturada',
            message: "Sua OS #{$os->id} do cliente {$client} foi faturada com sucesso. Valor: R$ " . number_format($os->valor_total, 2, ',', '.'),
            type: 'faturada',
            ordemServicoId: $os->id,
            actionUrl: route('ordem-servico.show', $os->id),
            data: [
                'cliente' => $client,
                'valor' => $os->valor_total,
            ],
            sendEmail: true
        );
    }

    /**
     * Notify new OS to admin
     */
    public function notifyNewOsToAdmin(OrdemServico $os): void
    {
        $admins = User::where('papel', 'admin')->get();
        $client = $os->cliente->nome ?? 'Cliente';
        $consultant = $os->consultor->name ?? 'Consultor';

        foreach ($admins as $admin) {
            $this->create(
                userId: $admin->id,
                title: '🆕 Nova Ordem de Serviço',
                message: "Nova OS #{$os->id} criada por {$consultant} para o cliente {$client}",
                type: 'nova_os',
                ordemServicoId: $os->id,
                actionUrl: route('ordem-servico.show', $os->id),
                data: [
                    'cliente' => $client,
                    'consultor' => $consultant,
                    'valor' => $os->valor_total,
                ],
                sendEmail: false
            );
        }
    }

    /**
     * Notify RPS emission
     */
    public function notifyRpsEmitted(OrdemServico $os): Notification
    {
        $consultorId = $os->consultor_id;

        return $this->create(
            userId: $consultorId,
            title: '📄 RPS Emitido',
            message: "RPS para a OS #{$os->id} foi emitido com sucesso",
            type: 'rps_emitida',
            ordemServicoId: $os->id,
            actionUrl: route('ordem-servico.show', $os->id),
            sendEmail: true
        );
    }

    /**
     * Notify comment mention
     */
    public function notifyMention(User $mentionedUser, User $mentioner, OrdemServico $os, string $comment): Notification
    {
        return $this->create(
            userId: $mentionedUser->id,
            title: '💬 Você foi mencionado',
            message: "{$mentioner->name} mencionou você na OS #{$os->id}",
            type: 'comentario',
            ordemServicoId: $os->id,
            actionUrl: route('ordem-servico.show', $os->id),
            data: [
                'mentioner' => $mentioner->name,
                'comment' => $comment,
            ],
            sendEmail: true
        );
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(Notification $notification): void
    {
        try {
            $user = $notification->user;

            // Skip if user has no email
            if (!$user || !$user->email) {
                \Log::warning('User has no email, skipping email notification', [
                    'notification_id' => $notification->id,
                    'user_id' => $notification->user_id,
                ]);
                return;
            }

            // Send email using Mailable
            Mail::to($user->email)->send(new NotificationMail($notification));

            // Mark as sent
            $notification->update(['email_sent' => true]);

            \Log::info('Notificação enviada por email com sucesso', [
                'notification_id' => $notification->id,
                'user_email' => $user->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar notificação por email', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications($userId, $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->with('ordemServico', 'user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for a user
     */
    public function getAllNotifications($userId, $limit = 50)
    {
        return Notification::where('user_id', $userId)
            ->with('ordemServico', 'user')
            ->latest()
            ->paginate($limit);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId): Notification
    {
        $notification = Notification::findOrFail($notificationId);
        $notification->markAsRead();
        return $notification;
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread($notificationId): Notification
    {
        $notification = Notification::findOrFail($notificationId);
        $notification->markAsUnread();
        return $notification;
    }

    /**
     * Mark all as read for user
     */
    public function markAllAsRead($userId): void
    {
        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Delete notification
     */
    public function delete($notificationId): bool
    {
        return Notification::destroy($notificationId) > 0;
    }

    /**
     * Delete old notifications (older than 30 days)
     */
    public function deleteOldNotifications($days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Get notification count by type
     */
    public function getCountByType($userId): array
    {
        return Notification::where('user_id', $userId)
            ->groupBy('type')
            ->selectRaw('type, count(*) as count')
            ->pluck('count', 'type')
            ->toArray();
    }
}
