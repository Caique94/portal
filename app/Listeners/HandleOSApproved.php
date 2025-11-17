<?php

namespace App\Listeners;

use App\Events\OSApproved;
use App\Jobs\GenerateReportJob;
use App\Models\Report;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class HandleOSApproved
{
    public function handle(OSApproved $event): void
    {
        $os = $event->ordemServico;

        Log::info("OS #{$os->id} aprovada. Iniciando geração de relatórios.");

        try {
            // Send notification to consultant
            $notificationService = new NotificationService();
            Log::info("HandleOSApproved: approved_by value = " . $os->approved_by);
            $approver = \App\Models\User::find($os->approved_by);
            if ($approver) {
                Log::info("HandleOSApproved: Approver found: {$approver->id} - {$approver->name} - {$approver->email}");
                $notificationService->notifyOsApproved($os, $approver);
                Log::info("HandleOSApproved: notifyOsApproved called successfully");
            } else {
                Log::warning("HandleOSApproved: Approver with ID {$os->approved_by} not found");
            }

            // Criar relatório para o consultor
            $reportConsultor = Report::create([
                'type' => 'os_consultor',
                'status' => 'pending',
                'ordem_servico_id' => $os->id,
                'filters' => json_encode([
                    'os_id' => $os->id,
                    'consultor_id' => $os->consultor_id,
                ]),
                'created_by' => $os->approved_by,
            ]);

            // Criar relatório para o cliente
            $reportCliente = Report::create([
                'type' => 'os_cliente',
                'status' => 'pending',
                'ordem_servico_id' => $os->id,
                'filters' => json_encode([
                    'os_id' => $os->id,
                    'cliente_id' => $os->cliente_id,
                ]),
                'created_by' => $os->approved_by,
            ]);

            // Despachar jobs para gerar e enviar os relatórios
            GenerateReportJob::dispatch($reportConsultor);
            GenerateReportJob::dispatch($reportCliente);

            Log::info("Relatórios criados, notificações enviadas e jobs despachados para OS #{$os->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar aprovação da OS #{$os->id}: " . $e->getMessage());
        }
    }
}
