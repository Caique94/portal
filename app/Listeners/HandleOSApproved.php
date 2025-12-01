<?php

namespace App\Listeners;

use App\Events\OSApproved;
use App\Jobs\GenerateReportJob;
use App\Models\Report;
use App\Services\NotificationService;
use App\Services\OrdemServicoEmailService;
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

            // Enviar Ordem de Serviço por Email para Consultor e Cliente
            try {
                $emailService = new OrdemServicoEmailService();
                $consultorEnviado = $emailService->enviarParaConsultor($os);
                $clienteEnviado = $emailService->enviarParaCliente($os);

                if ($consultorEnviado || $clienteEnviado) {
                    Log::info("Emails de OS #{$os->id} enviados após aprovação", [
                        'consultor' => $consultorEnviado,
                        'cliente' => $clienteEnviado
                    ]);
                }
            } catch (\Exception $emailError) {
                Log::warning("Erro ao enviar emails de OS #{$os->id} após aprovação: " . $emailError->getMessage());
                // Não falha o processo principal se email falhar
            }

            Log::info("Relatórios criados, notificações enviadas e jobs despachados para OS #{$os->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar aprovação da OS #{$os->id}: " . $e->getMessage());
        }
    }
}
