<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\FechamentoJob;
use App\Models\FechamentoAudit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GeneratePdfJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 2;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $jobId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = FechamentoJob::with(['history', 'requestedBy'])->find($this->jobId);

        if (!$job) {
            Log::error("FechamentoJob {$this->jobId} not found for PDF generation");
            return;
        }

        try {
            $history = $job->history()->latest()->first();

            if (!$history) {
                throw new \Exception("No history data found for job {$this->jobId}");
            }

            $view = $job->type === 'client'
                ? 'relatorio-fechamento.pdf-cliente'
                : 'relatorio-fechamento.pdf-consultor';

            $pdf = Pdf::loadView($view, [
                'job' => $job,
                'data' => $history->data,
                'metadata' => [
                    'periodo' => "{$job->period_start->format('d/m/Y')} - {$job->period_end->format('d/m/Y')}",
                    'tipo' => $job->type === 'client' ? 'Cliente' : 'Consultor',
                    'gerado_por' => $job->requestedBy->name,
                    'gerado_em' => now()->format('d/m/Y H:i:s'),
                    'versao' => $job->version,
                ],
            ]);

            $pdf->setPaper('A4', 'portrait');

            $filename = sprintf(
                'fechamento_%s_%s_%s_v%d.pdf',
                $job->type,
                $job->period_start->format('Y-m'),
                $job->id,
                $job->version
            );

            $pdfContent = $pdf->output();
            $checksum = hash('sha256', $pdfContent);

            Storage::disk('public')->put("fechamentos/{$filename}", $pdfContent);

            $job->update([
                'pdf_url' => "fechamentos/{$filename}",
                'pdf_checksum' => $checksum,
            ]);

            FechamentoAudit::log('gerar_pdf', $job->id, $job->requested_by, [
                'filename' => $filename,
                'checksum' => $checksum,
                'size' => strlen($pdfContent),
            ]);

            NotifyUserJob::dispatch($job->id);

        } catch (\Exception $e) {
            Log::error("Error generating PDF for FechamentoJob {$this->jobId}: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            FechamentoAudit::log('erro_pdf', $job->id, $job->requested_by, [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
