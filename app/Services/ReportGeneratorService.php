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
            'qtde_total' => $os->qtde_total,
        ];

        return $data;
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
        $timestamp = now()->format('Ymd_His');
        $osNumber = str_pad($os->id, 8, '0', STR_PAD_LEFT);

        return match ($type) {
            'os_consultor' => "OS_{$osNumber}_Consultor_{$timestamp}.pdf",
            'os_cliente' => "OS_{$osNumber}_Cliente_{$timestamp}.pdf",
            default => "OS_{$osNumber}_{$type}_{$timestamp}.pdf",
        };
    }
}
