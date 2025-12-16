<?php

namespace App\Services;

use App\Models\OrdemServico;

class ConsultorTotalizadorService
{
    /**
     * Calculate totalizador value for consultant perspective
     * Returns what the consultant receives (not what the client pays)
     */
    public static function calculateConsultantTotal(OrdemServico $os): float
    {
        $consultor = $os->consultor;
        $cliente = $os->cliente;

        $horas = floatval($os->horas_trabalhadas ?? 0);
        $km = floatval($os->km ?? 0);
        $deslocamento = floatval($os->deslocamento ?? 0);
        $despesas = floatval($os->valor_despesa ?? 0);
        $is_presencial = $os->is_presencial ?? false;

        // Get consultant's hourly rate
        $valor_hora = floatval($consultor->valor_hora ?? 0);
        $valor_km_consultor = floatval($consultor->valor_km ?? 0);

        // Calculate based on consultant's rates
        $valor_horas = $horas * $valor_hora;
        $valor_km_total = $is_presencial ? ($km * $valor_km_consultor) : 0;
        $valor_deslocamento = $is_presencial ? ($deslocamento * $valor_hora) : 0;

        // Total: hours + km + displacement + expenses
        $total = $valor_horas + $valor_km_total + $valor_deslocamento + $despesas;

        return $total;
    }

    /**
     * Calculate totalizador array for consultant perspective
     * Returns complete breakdown (same as used in emails/PDFs)
     */
    public static function calculateConsultantTotalizador(OrdemServico $os): array
    {
        $consultor = $os->consultor;

        $horas = floatval($os->horas_trabalhadas ?? 0);
        $km = floatval($os->km ?? 0);
        $deslocamento = floatval($os->deslocamento ?? 0);
        $despesas = floatval($os->valor_despesa ?? 0);
        $is_presencial = $os->is_presencial ?? false;

        // Get consultant's rates
        $valor_hora = floatval($consultor->valor_hora ?? 0);
        $valor_km_consultor = floatval($consultor->valor_km ?? 0);

        // Calculate values
        $valor_horas = $horas * $valor_hora;
        $valor_km_total = $is_presencial ? ($km * $valor_km_consultor) : 0;
        $valor_deslocamento = $is_presencial ? ($deslocamento * $valor_hora) : 0;

        return [
            'tipo' => 'consultor',
            'valor_hora_label' => 'Valor Hora Consultor',
            'valor_km_label' => 'Valor KM Consultor',
            'valor_hora' => $valor_hora,
            'valor_km' => $valor_km_consultor,
            'horas' => $horas,
            'km' => $km,
            'deslocamento' => $deslocamento,
            'despesas' => $despesas,
            'is_presencial' => $is_presencial,
            'valor_horas' => $valor_horas,
            'valor_km_total' => $valor_km_total,
            'valor_deslocamento' => $valor_deslocamento,
            'total_servico' => $valor_horas + $valor_km_total + $valor_deslocamento,
            'total_geral' => $valor_horas + $valor_km_total + $valor_deslocamento + $despesas,
        ];
    }
}
