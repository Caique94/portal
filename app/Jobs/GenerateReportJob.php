<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    protected Report $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function handle(ReportGeneratorService $generator): void
    {
        try {
            Log::info("Gerando relatório #{$this->report->id} do tipo {$this->report->type}");

            $this->report->update(['status' => 'processing']);

            // Gerar PDF
            $pdfPath = $generator->generate($this->report);

            $this->report->update([
                'status' => 'completed',
                'path' => $pdfPath,
            ]);

            Log::info("Relatório #{$this->report->id} gerado com sucesso: {$pdfPath}");

            // Despachar job para enviar e-mail
            SendReportEmailJob::dispatch($this->report);

        } catch (\Exception $e) {
            Log::error("Erro ao gerar relatório #{$this->report->id}: " . $e->getMessage());

            $this->report->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job falhou definitivamente para relatório #{$this->report->id}: " . $exception->getMessage());

        $this->report->update([
            'status' => 'failed',
            'error' => $exception->getMessage(),
        ]);
    }
}
