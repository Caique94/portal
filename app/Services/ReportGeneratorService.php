<?php

namespace App\Services;

use App\Models\Report;
use App\Models\OrdemServico;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportGeneratorService
{
    public function generate(Report $report): string
    {
        $os = OrdemServico::with(['consultor', 'cliente', 'produtoTabela.produto'])
            ->findOrFail($report->ordem_servico_id);

        $data = $this->prepareData($os, $report->type);

        $view = $this->getViewName($report->type);

        // Gerar PDF usando DomPDF
        $pdf = Pdf::loadView($view, $data);

        // Definir nome do arquivo
        $filename = $this->generateFilename($os, $report->type);

        // Salvar no storage
        $path = "reports/{$filename}";
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    protected function prepareData(OrdemServico $os, string $type): array
    {
        // Calculate totalizador data based on report type
        $totalizadorData = $this->calculateTotalizadorData($os, $type);

        $data = [
            'os' => $os,
            'numero_os' => str_pad($os->id, 8, '0', STR_PAD_LEFT),
            'cliente' => $os->cliente,
            'consultor' => $os->consultor,
            'produto' => $os->produtoTabela->produto ?? null,
            'data_emissao' => \Carbon\Carbon::parse($os->data_emissao)->format('d/m/Y'),
            'data_aprovacao' => $os->approved_at ? \Carbon\Carbon::parse($os->approved_at)->format('d/m/Y H:i') : null,
            'valor_total' => $os->valor_total,
            'assunto' => $os->assunto,
            'projeto' => $os->projeto,
            'detalhamento' => $os->detalhamento,
            'hora_inicio' => $os->hora_inicio,
            'hora_final' => $os->hora_final,
            'qtde_total' => $os->horas_trabalhadas,
            'totalizador' => $totalizadorData,
        ];

        return $data;
    }

    /**
     * Calculate totalizador data based on report type
     * For client reports: shows admin perspective (what they pay)
     * For consultant reports: shows consultant perspective (what they receive)
     */
    protected function calculateTotalizadorData(OrdemServico $os, string $type): array
    {
        $consultor = $os->consultor;
        $cliente = $os->cliente;

        $horas = floatval($os->horas_trabalhadas ?? 0);
        $km = floatval($os->km ?? 0);
        $deslocamento = floatval($os->deslocamento ?? 0);
        $despesas = floatval($os->valor_despesa ?? 0);
        $is_presencial = $os->is_presencial ?? false;

        // Get valores_km
        $valor_km_consultor = floatval($consultor->valor_km ?? 0);

        if ($type === 'os_cliente') {
            // Client PDF shows ADMIN perspective (what they pay)
            $valor_hora = floatval($cliente->valor_hora ?? 0);

            $valor_horas = $horas * $valor_hora;
            $valor_km_total = $is_presencial ? ($km * floatval($cliente->valor_hora ?? 0)) : 0;
            $valor_deslocamento = $is_presencial ? ($deslocamento * floatval($cliente->valor_hora ?? 0)) : 0;

            return [
                'tipo' => 'admin',
                'valor_hora_label' => 'Valor Hora Cliente',
                'valor_km_label' => 'Valor KM Cliente',
                'valor_hora' => $valor_hora,
                'valor_km' => floatval($cliente->valor_hora ?? 0),
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
        } else {
            // Consultant PDF shows CONSULTANT perspective (what they receive)
            $valor_hora = floatval($consultor->valor_hora ?? 0);

            $valor_horas = $horas * $valor_hora;
            $valor_km_total = $is_presencial ? ($km * $valor_km_consultor) : 0;
            $valor_deslocamento = $is_presencial ? ($deslocamento * floatval($consultor->valor_hora ?? 0)) : 0;

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

    protected function getViewName(string $type): string
    {
        return match ($type) {
            'os_consultor' => 'pdfs.reports.os_consultor',
            'os_cliente' => 'pdfs.reports.os_cliente',
            default => throw new \Exception("Tipo de relatório desconhecido: {$type}"),
        };
    }

    protected function generateFilename(OrdemServico $os, string $type): string
    {
        $timestamp = now('America/Sao_Paulo')->format('Ymd_His');
        $osNumber = str_pad($os->id, 8, '0', STR_PAD_LEFT);

        return match ($type) {
            'os_consultor' => "OS_{$osNumber}_Consultor_{$timestamp}.pdf",
            'os_cliente' => "OS_{$osNumber}_Cliente_{$timestamp}.pdf",
            default => "OS_{$osNumber}_{$type}_{$timestamp}.pdf",
        };
    }
}
