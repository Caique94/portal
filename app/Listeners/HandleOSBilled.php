<?php

namespace App\Listeners;

use App\Events\OSBilled;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class HandleOSBilled
{
    public function handle(OSBilled $event): void
    {
        $os = $event->ordemServico;

        Log::info("OS #{$os->id} faturada. Enviando notificação ao consultor.");

        try {
            // Send billing notification to consultant
            $notificationService = new NotificationService();
            $notificationService->notifyOsBilled($os);

            Log::info("Notificação de faturamento enviada para OS #{$os->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar faturamento da OS #{$os->id}: " . $e->getMessage());
        }
    }
}
