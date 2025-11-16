<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReportEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    protected Report $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function handle(ReportEmailService $emailService): void
    {
        try {
            Log::info("Enviando e-mail para relatório #{$this->report->id}");

            $emailService->send($this->report);

            $this->report->update(['sent_at' => now()]);

            Log::info("E-mail enviado com sucesso para relatório #{$this->report->id}");

        } catch (\Exception $e) {
            Log::error("Erro ao enviar e-mail do relatório #{$this->report->id}: " . $e->getMessage());

            $this->report->update([
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job de envio falhou definitivamente para relatório #{$this->report->id}: " . $exception->getMessage());
    }
}
