<?php

namespace App\Actions;

use App\Models\OrdemServico;
use App\Models\Report;
use App\Services\ReportEmailService;
use Illuminate\Support\Facades\DB;

class ResendReportEmailAction
{
    protected ReportEmailService $reportEmailService;

    public function __construct(ReportEmailService $reportEmailService)
    {
        $this->reportEmailService = $reportEmailService;
    }

    /**
     * Resend report emails for an order
     *
     * @param OrdemServico $os
     * @param string $recipient 'consultor', 'cliente', or 'ambos'
     * @return array Result with success count and error messages
     */
    public function execute(OrdemServico $os, string $recipient = 'ambos'): array
    {
        $result = [
            'success' => true,
            'sent_count' => 0,
            'failed_count' => 0,
            'messages' => [],
            'details' => [],
        ];

        // Validar status
        if (!in_array($os->status, [5, 6, 7])) { // FATURADO, AGUARDANDO_RPS, RPS_EMITIDA
            $result['success'] = false;
            $result['messages'][] = "Ordem de Serviço deve estar em status 'Faturado', 'Aguardando RPS' ou 'RPS Emitida' para reenviar emails";
            return $result;
        }

        // Determinar quais relatórios enviar
        $reportTypes = $this->getReportTypes($recipient);

        foreach ($reportTypes as $type) {
            try {
                // Buscar ou criar relatório
                $report = $this->getOrCreateReport($os, $type);

                // Enviar email
                $this->reportEmailService->send($report);

                $result['sent_count']++;
                $recipientType = $type === 'os_consultor' ? 'Consultor' : 'Cliente';
                $result['messages'][] = "Email para {$recipientType} reenviado com sucesso";
                $result['details'][] = [
                    'type' => $type,
                    'status' => 'sent',
                    'recipient' => $recipientType,
                ];

            } catch (\Exception $e) {
                $result['failed_count']++;
                $recipientType = $type === 'os_consultor' ? 'Consultor' : 'Cliente';
                $result['messages'][] = "Erro ao reenviar para {$recipientType}: " . $e->getMessage();
                $result['details'][] = [
                    'type' => $type,
                    'status' => 'failed',
                    'recipient' => $recipientType,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $result['success'] = $result['failed_count'] === 0;

        return $result;
    }

    /**
     * Determine which report types to send based on recipient selection
     */
    protected function getReportTypes(string $recipient): array
    {
        return match ($recipient) {
            'consultor' => ['os_consultor'],
            'cliente' => ['os_cliente'],
            'ambos' => ['os_consultor', 'os_cliente'],
            default => ['os_consultor', 'os_cliente'],
        };
    }

    /**
     * Get existing report or create a new one
     */
    protected function getOrCreateReport(OrdemServico $os, string $type): Report
    {
        // Try to find existing report
        $report = Report::where('ordem_servico_id', $os->id)
            ->where('type', $type)
            ->latest('created_at')
            ->first();

        // If no report exists, create a new one
        if (!$report) {
            // If there's a PDF from a previous report, use it
            $previousReport = Report::where('ordem_servico_id', $os->id)
                ->where('type', $type)
                ->latest('created_at')
                ->first();

            $path = $previousReport?->path ?? null;

            $report = Report::create([
                'ordem_servico_id' => $os->id,
                'type' => $type,
                'status' => 'pending',
                'path' => $path,
                'created_by' => auth()->user()->id ?? null,
            ]);
        }

        return $report;
    }
}
