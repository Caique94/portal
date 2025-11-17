<?php

namespace App\Listeners;

use App\Events\RPSEmitted;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class HandleRPSEmitted
{
    public function handle(RPSEmitted $event): void
    {
        $os = $event->ordemServico;

        Log::info("RPS emitido para OS #{$os->id}. Enviando notificação ao consultor.");

        try {
            // Send RPS notification to consultant
            $notificationService = new NotificationService();
            $notificationService->notifyRpsEmitted($os);

            Log::info("Notificação de RPS enviada para OS #{$os->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar emissão de RPS para OS #{$os->id}: " . $e->getMessage());
        }
    }
}
