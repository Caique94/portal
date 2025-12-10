<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\FechamentoJob;
use App\Models\FechamentoHistory;
use App\Models\FechamentoAudit;
use App\Models\RelatorioFechamento;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessFechamentoJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 600;

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
        $job = FechamentoJob::find($this->jobId);

        if (!$job) {
            Log::error("FechamentoJob {$this->jobId} not found");
            return;
        }

        try {
            $job->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            FechamentoAudit::log('processar', $job->id, $job->requested_by, [
                'type' => $job->type,
                'period' => "{$job->period_start} - {$job->period_end}",
            ]);

            $data = $this->collectData($job);

            FechamentoHistory::create([
                'fechamento_job_id' => $job->id,
                'data' => $data,
                'tag' => $job->type === 'client' ? 'agregacao_cliente' : 'agregacao_consultor',
            ]);

            $job->update([
                'status' => 'success',
                'finished_at' => now(),
            ]);

            GeneratePdfJob::dispatch($job->id);

        } catch (\Exception $e) {
            Log::error("Error processing FechamentoJob {$this->jobId}: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            $job->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            FechamentoAudit::log('erro', $job->id, $job->requested_by, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function collectData(FechamentoJob $job): array
    {
        $query = OrdemServico::whereBetween('data_inicio', [$job->period_start, $job->period_end]);

        if ($job->filters) {
            foreach ($job->filters as $key => $value) {
                if ($value !== null) {
                    $query->where($key, $value);
                }
            }
        }

        if ($job->type === 'client') {
            return $this->collectClientData($query);
        } else {
            return $this->collectConsultantData($query);
        }
    }

    private function collectClientData($query): array
    {
        $ordens = $query->with(['cliente', 'produto', 'consultor'])->get();

        $grouped = $ordens->groupBy('cliente_id');

        return $grouped->map(function ($clienteOrdens) {
            $cliente = $clienteOrdens->first()->cliente;

            return [
                'cliente_id' => $cliente->id,
                'cliente_nome' => $cliente->name,
                'total_ordens' => $clienteOrdens->count(),
                'total_horas' => $clienteOrdens->sum('quantidade_horas'),
                'valor_total' => $clienteOrdens->sum(function ($ordem) {
                    return $ordem->quantidade_horas * $ordem->preco_produto;
                }),
                'ordens' => $clienteOrdens->map(function ($ordem) {
                    return [
                        'id' => $ordem->id,
                        'produto' => $ordem->produto->name,
                        'consultor' => $ordem->consultor->name,
                        'horas' => $ordem->quantidade_horas,
                        'preco_unitario' => $ordem->preco_produto,
                        'total' => $ordem->quantidade_horas * $ordem->preco_produto,
                    ];
                })->toArray(),
            ];
        })->values()->toArray();
    }

    private function collectConsultantData($query): array
    {
        $ordens = $query->with(['cliente', 'produto', 'consultor'])->get();

        $grouped = $ordens->groupBy('consultor_id');

        return $grouped->map(function ($consultorOrdens) {
            $consultor = $consultorOrdens->first()->consultor;

            return [
                'consultor_id' => $consultor->id,
                'consultor_nome' => $consultor->name,
                'total_ordens' => $consultorOrdens->count(),
                'total_horas' => $consultorOrdens->sum('quantidade_horas'),
                'valor_total' => $consultorOrdens->sum(function ($ordem) {
                    return $ordem->quantidade_horas * $ordem->valor_hora_consultor;
                }),
                'ordens' => $consultorOrdens->map(function ($ordem) {
                    return [
                        'id' => $ordem->id,
                        'cliente' => $ordem->cliente->name,
                        'produto' => $ordem->produto->name,
                        'horas' => $ordem->quantidade_horas,
                        'valor_hora' => $ordem->valor_hora_consultor,
                        'total' => $ordem->quantidade_horas * $ordem->valor_hora_consultor,
                    ];
                })->toArray(),
            ];
        })->values()->toArray();
    }
}
