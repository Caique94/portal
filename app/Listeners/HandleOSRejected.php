<?php

namespace App\Listeners;

use App\Events\OSRejected;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class HandleOSRejected
{
    public function handle(OSRejected $event): void
    {
        $os = $event->ordemServico;
        $reason = $event->reason;

        Log::info("OS #{$os->id} rejeitada. Enviando notificação ao consultor.");

        try {
            // Send rejection notification to consultant
            $notificationService = new NotificationService();
            $rejector = \App\Models\User::find(auth()->id());

            if ($rejector) {
                $notificationService->notifyOsRejected($os, $rejector, $reason);
            }

            Log::info("Notificação de rejeição enviada para OS #{$os->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar rejeição da OS #{$os->id}: " . $e->getMessage());
        }
    }
}
