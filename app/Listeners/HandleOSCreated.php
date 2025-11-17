<?php

namespace App\Listeners;

use App\Events\OSCreated;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class HandleOSCreated
{
    public function handle(OSCreated $event): void
    {
        $os = $event->ordemServico;

        Log::info("OS #{$os->id} criada. Notificando administradores.");

        try {
            // Notify admins about new OS
            $notificationService = new NotificationService();
            Log::info("HandleOSCreated: Enviando notificação para administradores");
            $notificationService->notifyNewOsToAdmin($os);
            Log::info("HandleOSCreated: notifyNewOsToAdmin chamado com sucesso para OS #{$os->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar criação da OS #{$os->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
