<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\FechamentoJob;
use App\Models\FechamentoAudit;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotifyUserJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 2;
    public $timeout = 60;

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
        $job = FechamentoJob::with('requestedBy')->find($this->jobId);

        if (!$job) {
            Log::error("FechamentoJob {$this->jobId} not found for notification");
            return;
        }

        try {
            $user = $job->requestedBy;

            if (!$user->email) {
                Log::warning("User {$user->id} has no email address");
                return;
            }

            $tipo = $job->type === 'client' ? 'Cliente' : 'Consultor';
            $periodo = "{$job->period_start->format('d/m/Y')} - {$job->period_end->format('d/m/Y')}";

            Mail::send('emails.fechamento-ready', [
                'user' => $user,
                'job' => $job,
                'tipo' => $tipo,
                'periodo' => $periodo,
                'downloadUrl' => route('api.fechamentos.download', $job->id),
            ], function ($message) use ($user, $tipo) {
                $message->to($user->email)
                    ->subject("Relatório de Fechamento {$tipo} Pronto");
            });

            FechamentoAudit::log('notificar', $job->id, $user->id, [
                'email' => $user->email,
                'tipo' => 'email',
            ]);

            Log::info("Notification sent for FechamentoJob {$this->jobId} to {$user->email}");

        } catch (\Exception $e) {
            Log::error("Error sending notification for FechamentoJob {$this->jobId}: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
