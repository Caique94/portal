<?php

namespace App\Observers;

use App\Models\OrdemServico;

class OrdemServicoObserver
{
    /**
     * Handle the OrdemServico "created" event.
     * Atualiza horas consumidas do projeto
     */
    public function created(OrdemServico $ordemServico): void
    {
        $this->atualizarHorasDoProjet($ordemServico);
    }

    /**
     * Handle the OrdemServico "updated" event.
     * Atualiza horas consumidas do projeto
     */
    public function updated(OrdemServico $ordemServico): void
    {
        $this->atualizarHorasDoProjet($ordemServico);
    }

    /**
     * Handle the OrdemServico "deleted" event.
     * Remove horas consumidas do projeto
     */
    public function deleted(OrdemServico $ordemServico): void
    {
        $this->atualizarHorasDoProjet($ordemServico);
    }

    /**
     * Handle the OrdemServico "restored" event.
     */
    public function restored(OrdemServico $ordemServico): void
    {
        $this->atualizarHorasDoProjet($ordemServico);
    }

    /**
     * Handle the OrdemServico "force deleted" event.
     */
    public function forceDeleted(OrdemServico $ordemServico): void
    {
        $this->atualizarHorasDoProjet($ordemServico);
    }

    /**
     * Calcula e atualiza as horas consumidas do projeto
     * baseado no total de horas da ordem de serviço
     */
    private function atualizarHorasDoProjet(OrdemServico $ordemServico): void
    {
        // Se a OS não tem projeto vinculado, não faz nada
        if (!$ordemServico->projeto_id) {
            return;
        }

        $projeto = $ordemServico->projeto;

        // Se o projeto não existe na base de dados, não faz nada
        if (!$projeto) {
            return;
        }

        // Calcula total de horas consumidas de todas as OS do projeto
        // Converte a diferença de tempo para horas (em minutos / 60)
        // Como hora_inicio e hora_final são VARCHAR no formato HH:MM, precisamos calcular manualmente
        $result = \DB::selectOne(
            'SELECT COALESCE(SUM(
                (CAST(SUBSTRING(hora_final, 1, 2) AS INTEGER) - CAST(SUBSTRING(hora_inicio, 1, 2) AS INTEGER)) +
                (CAST(SUBSTRING(hora_final, 4, 2) AS INTEGER) - CAST(SUBSTRING(hora_inicio, 4, 2) AS INTEGER)) / 60.0
            ), 0) as total_horas
             FROM ordem_servico
             WHERE projeto_id = ? AND hora_final IS NOT NULL AND hora_inicio IS NOT NULL AND hora_final ~ \'\\d{2}:\\d{2}\' AND hora_inicio ~ \'\\d{2}:\\d{2}\'',
            [$projeto->id]
        );

        // Arredonda para 2 casas decimais
        $totalHoras = round($result->total_horas ?? 0, 2);

        // Atualiza as horas consumidas do projeto
        $projeto->horas_consumidas = $totalHoras;

        // Calcula horas restantes
        $projeto->horas_restantes = max(0, $projeto->horas_alocadas - $projeto->horas_consumidas);

        // Usa updateQuietly para evitar o bootHasSequentialCode quando o projeto já tem codigo
        $projeto->updateQuietly([
            'horas_consumidas' => $projeto->horas_consumidas,
            'horas_restantes' => $projeto->horas_restantes,
        ]);
    }
}
