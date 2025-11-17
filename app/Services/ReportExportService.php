<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class ReportExportService
{
    protected $spreadsheet;

    /**
     * Build report data based on filters
     */
    public function getFilteredData(array $filters = []): array
    {
        $query = OrdemServico::query()
            ->with(['cliente', 'consultor', 'projeto']);

        // Apply filters
        if (!empty($filters['data_inicio'])) {
            $query->where('created_at', '>=', $filters['data_inicio']);
        }
        if (!empty($filters['data_fim'])) {
            $query->where('created_at', '<=', $filters['data_fim'] . ' 23:59:59');
        }
        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }
        if (!empty($filters['consultor_id'])) {
            $query->where('consultor_id', $filters['consultor_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $orders = $query->orderByDesc('created_at')->get();

        return $this->formatOrders($orders);
    }

    /**
     * Get summary report based on filters
     */
    public function getSummaryReport(array $filters = []): array
    {
        $query = OrdemServico::query();

        // Apply filters
        if (!empty($filters['data_inicio'])) {
            $query->where('created_at', '>=', $filters['data_inicio']);
        }
        if (!empty($filters['data_fim'])) {
            $query->where('created_at', '<=', $filters['data_fim'] . ' 23:59:59');
        }
        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }
        if (!empty($filters['consultor_id'])) {
            $query->where('consultor_id', $filters['consultor_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $summary = [
            'total_ordens' => $query->count(),
            'valor_total' => (float) $this->sumValues($query->clone()->pluck('valor_total')),
            'total_ordens_faturadas' => $query->clone()->whereIn('status', [5, 6, 7, 8])->count(),
            'valor_faturado' => (float) $this->sumValues($query->clone()->whereIn('status', [5, 6, 7, 8])->pluck('valor_total')),
            'total_ordens_pendentes' => $query->clone()->whereIn('status', [1, 2, 3, 4])->count(),
            'valor_pendente' => (float) $this->sumValues($query->clone()->whereIn('status', [1, 2, 3, 4])->pluck('valor_total')),
        ];

        return $summary;
    }

    /**
     * Export to Excel
     */
    public function exportToExcel(array $filters = [], string $viewType = 'summary'): string
    {
        $this->spreadsheet = new Spreadsheet();

        if ($viewType === 'analytical') {
            return $this->exportToExcelAnalytical($filters);
        } else {
            return $this->exportToExcelSummary($filters);
        }
    }

    /**
     * Export summary view to Excel
     */
    private function exportToExcelSummary(array $filters = []): string
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle('Relatório Resumido');

        // Header
        $sheet->setCellValue('A1', 'PORTAL - RELATÓRIO DE ORDENS DE SERVIÇO');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A2', 'Data do Relatório: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:H2');

        // Filtros aplicados
        $row = 4;
        if (!empty($filters)) {
            $sheet->setCellValue('A' . $row, 'FILTROS APLICADOS:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            if (!empty($filters['data_inicio'])) {
                $sheet->setCellValue('A' . $row, 'Data Início: ' . $filters['data_inicio']);
                $row++;
            }
            if (!empty($filters['data_fim'])) {
                $sheet->setCellValue('A' . $row, 'Data Fim: ' . $filters['data_fim']);
                $row++;
            }
            if (!empty($filters['cliente_id'])) {
                $cliente = Cliente::find($filters['cliente_id']);
                $sheet->setCellValue('A' . $row, 'Cliente: ' . ($cliente->nome ?? 'N/A'));
                $row++;
            }
            if (!empty($filters['consultor_id'])) {
                $consultor = User::find($filters['consultor_id']);
                $sheet->setCellValue('A' . $row, 'Consultor: ' . ($consultor->name ?? 'N/A'));
                $row++;
            }
            if (!empty($filters['status'])) {
                $statusNames = $this->getStatusNames();
                $sheet->setCellValue('A' . $row, 'Status: ' . ($statusNames[$filters['status']] ?? 'N/A'));
                $row++;
            }
            $row++;
        }

        // Resumo
        $summary = $this->getSummaryReport($filters);
        $sheet->setCellValue('A' . $row, 'RESUMO:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total de Ordens:');
        $sheet->setCellValue('B' . $row, $summary['total_ordens']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Valor Total:');
        $sheet->setCellValue('B' . $row, 'R$ ' . number_format($summary['valor_total'], 2, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Ordens Faturadas:');
        $sheet->setCellValue('B' . $row, $summary['total_ordens_faturadas']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Valor Faturado:');
        $sheet->setCellValue('B' . $row, 'R$ ' . number_format($summary['valor_faturado'], 2, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Ordens Pendentes:');
        $sheet->setCellValue('B' . $row, $summary['total_ordens_pendentes']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Valor Pendente:');
        $sheet->setCellValue('B' . $row, 'R$ ' . number_format($summary['valor_pendente'], 2, ',', '.'));
        $row += 2;

        // Dados detalhados
        $sheet->setCellValue('A' . $row, 'ORDENS DE SERVIÇO DETALHADAS');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        // Headers da tabela
        $headers = ['ID', 'Cliente', 'Consultor', 'Data', 'Valor', 'Status', 'Faturado', 'Pendente'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $style = $sheet->getStyle($col . $row);
            $style->getFont()->setBold(true);
            $style->getFont()->getColor()->setRGB('FFFFFF');
            $style->getFill()->setFillType('solid');
            $style->getFill()->getStartColor()->setRGB('366092');
            $col++;
        }
        $row++;

        // Dados
        $orders = $this->getFilteredData($filters);
        $statusNames = $this->getStatusNames();

        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $order['id']);
            $sheet->setCellValue('B' . $row, $order['client']);
            $sheet->setCellValue('C' . $row, $order['consultant']);
            $sheet->setCellValue('D' . $row, $order['created_at']);
            $sheet->setCellValue('E' . $row, 'R$ ' . number_format($order['total'], 2, ',', '.'));
            $sheet->setCellValue('F' . $row, $order['status']);
            $sheet->setCellValue('G' . $row, in_array($order['status'], ['Faturada', 'RPS Emitida', 'Aguardando RPS']) ? 'Sim' : 'Não');
            $sheet->setCellValue('H' . $row, in_array($order['status'], ['Aberta', 'Aguardando Aprovação', 'Aprovado', 'Contestada']) ? 'Sim' : 'Não');
            $row++;
        }

        // Auto fit columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save
        $filename = 'relatorio_' . now()->format('Y-m-d_His') . '.xlsx';
        $filepath = storage_path('app/exports/' . $filename);

        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Export analytical view to Excel
     */
    private function exportToExcelAnalytical(array $filters = []): string
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle('Relatório Analítico');

        // Header
        $sheet->setCellValue('A1', 'PORTAL - RELATÓRIO ANALÍTICO');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A2', 'Data do Relatório: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:H2');

        $row = 4;

        // Filter summary
        if (!empty($filters)) {
            $sheet->setCellValue('A' . $row, 'FILTROS APLICADOS:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            if (!empty($filters['data_inicio'])) {
                $sheet->setCellValue('A' . $row, 'Data Início: ' . $filters['data_inicio']);
                $row++;
            }
            if (!empty($filters['data_fim'])) {
                $sheet->setCellValue('A' . $row, 'Data Fim: ' . $filters['data_fim']);
                $row++;
            }
            if (!empty($filters['cliente_id'])) {
                $cliente = Cliente::find($filters['cliente_id']);
                $sheet->setCellValue('A' . $row, 'Cliente: ' . ($cliente->nome ?? 'N/A'));
                $row++;
            }
            if (!empty($filters['consultor_id'])) {
                $consultor = User::find($filters['consultor_id']);
                $sheet->setCellValue('A' . $row, 'Consultor: ' . ($consultor->name ?? 'N/A'));
                $row++;
            }
            if (!empty($filters['status'])) {
                $statusNames = $this->getStatusNames();
                $sheet->setCellValue('A' . $row, 'Status: ' . ($statusNames[$filters['status']] ?? 'N/A'));
                $row++;
            }
            $row += 2;
        }

        // Overall metrics
        $summary = $this->getSummaryReport($filters);
        $sheet->setCellValue('A' . $row, 'MÉTRICAS GERAIS:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total de Ordens:');
        $sheet->setCellValue('B' . $row, $summary['total_ordens']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Valor Total:');
        $sheet->setCellValue('B' . $row, 'R$ ' . number_format($summary['valor_total'], 2, ',', '.'));
        $row++;

        $avgTicket = $summary['total_ordens'] > 0 ? $summary['valor_total'] / $summary['total_ordens'] : 0;
        $sheet->setCellValue('A' . $row, 'Ticket Médio:');
        $sheet->setCellValue('B' . $row, 'R$ ' . number_format($avgTicket, 2, ',', '.'));
        $row++;

        $conversionRate = $summary['total_ordens'] > 0 ? ($summary['total_ordens_faturadas'] / $summary['total_ordens'] * 100) : 0;
        $sheet->setCellValue('A' . $row, 'Taxa de Faturamento:');
        $sheet->setCellValue('B' . $row, number_format($conversionRate, 1) . '%');
        $row += 2;

        // Analysis by Client
        $sheet->setCellValue('A' . $row, 'ANÁLISE POR CLIENTE:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $headers = ['Cliente', 'Ordens', 'Valor Total', 'Ticket Médio'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $style = $sheet->getStyle($col . $row);
            $style->getFont()->setBold(true);
            $style->getFont()->getColor()->setRGB('FFFFFF');
            $style->getFill()->setFillType('solid');
            $style->getFill()->getStartColor()->setRGB('4472C4');
            $col++;
        }
        $row++;

        $clientData = $this->getAnalysisByClient($filters);
        foreach ($clientData as $client) {
            $sheet->setCellValue('A' . $row, $client['name']);
            $sheet->setCellValue('B' . $row, $client['orders']);
            $sheet->setCellValue('C' . $row, 'R$ ' . number_format($client['total'], 2, ',', '.'));
            $sheet->setCellValue('D' . $row, 'R$ ' . number_format($client['avg'], 2, ',', '.'));
            $row++;
        }
        $row++;

        // Analysis by Consultant
        $sheet->setCellValue('A' . $row, 'ANÁLISE POR CONSULTOR:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $headers = ['Consultor', 'Ordens', 'Valor Total', 'Ticket Médio'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $style = $sheet->getStyle($col . $row);
            $style->getFont()->setBold(true);
            $style->getFont()->getColor()->setRGB('FFFFFF');
            $style->getFill()->setFillType('solid');
            $style->getFill()->getStartColor()->setRGB('70AD47');
            $col++;
        }
        $row++;

        $consultantData = $this->getAnalysisByConsultant($filters);
        foreach ($consultantData as $consultant) {
            $sheet->setCellValue('A' . $row, $consultant['name']);
            $sheet->setCellValue('B' . $row, $consultant['orders']);
            $sheet->setCellValue('C' . $row, 'R$ ' . number_format($consultant['total'], 2, ',', '.'));
            $sheet->setCellValue('D' . $row, 'R$ ' . number_format($consultant['avg'], 2, ',', '.'));
            $row++;
        }
        $row += 2;

        // Analysis by Project
        $sheet->setCellValue('A' . $row, 'ANÁLISE POR PROJETO:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $orders = $this->getFilteredData($filters);
        $projects = [];
        foreach ($orders as $order) {
            $project = $order['projeto_nome'] ?? 'Sem Projeto';
            if (!isset($projects[$project])) {
                $projects[$project] = ['orders' => 0, 'total' => 0];
            }
            $projects[$project]['orders']++;
            $projects[$project]['total'] += $order['valor_total'];
        }

        foreach ($projects as $project => $data) {
            $sheet->setCellValue('A' . $row, $project);
            $sheet->setCellValue('B' . $row, $data['orders'] . ' OS');
            $sheet->setCellValue('C' . $row, 'R$ ' . number_format($data['total'], 2, ',', '.'));
            $row++;
        }
        $row += 2;

        // Duration Analysis
        $sheet->setCellValue('A' . $row, 'ANÁLISE DE DURAÇÃO E DESLOCAMENTO:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $totalHours = 0;
        $totalKm = 0;
        foreach ($orders as $order) {
            $totalHours += $order['horas'] ?? 0;
            $totalKm += $order['km'] ?? 0;
        }

        $sheet->setCellValue('A' . $row, 'Total de Horas:');
        $sheet->setCellValue('B' . $row, number_format($totalHours, 2, ',', '.') . 'h');
        $row++;

        $sheet->setCellValue('A' . $row, 'Média de Horas/OS:');
        $sheet->setCellValue('B' . $row, number_format(count($orders) > 0 ? $totalHours / count($orders) : 0, 2, ',', '.') . 'h');
        $row++;

        $sheet->setCellValue('A' . $row, 'Total de KM:');
        $sheet->setCellValue('B' . $row, number_format($totalKm, 2, ',', '.') . ' km');
        $row++;

        $sheet->setCellValue('A' . $row, 'Média de KM/OS:');
        $sheet->setCellValue('B' . $row, number_format(count($orders) > 0 ? $totalKm / count($orders) : 0, 2, ',', '.') . ' km');
        $row += 2;

        // Detailed Activities
        $sheet->setCellValue('A' . $row, 'DETALHES DE ATIVIDADES:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $headers = ['ID', 'Cliente', 'Consultor', 'Projeto', 'Assunto', 'Descrição', 'Horas', 'KM', 'Data', 'Valor', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $style = $sheet->getStyle($col . $row);
            $style->getFont()->setBold(true);
            $style->getFont()->getColor()->setRGB('FFFFFF');
            $style->getFill()->setFillType('solid');
            $style->getFill()->getStartColor()->setRGB('70AD47');
            $col++;
        }
        $row++;

        $statusNames = $this->getStatusNames();
        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $order['id']);
            $sheet->setCellValue('B' . $row, $order['cliente_nome']);
            $sheet->setCellValue('C' . $row, $order['consultor_nome']);
            $sheet->setCellValue('D' . $row, $order['projeto_nome']);
            $sheet->setCellValue('E' . $row, $order['assunto']);
            $sheet->setCellValue('F' . $row, substr($order['descricao'] ?: $order['detalhamento'], 0, 50));
            $sheet->setCellValue('G' . $row, $order['horas'] > 0 ? $order['horas'] . 'h' : '-');
            $sheet->setCellValue('H' . $row, $order['km'] > 0 ? $order['km'] . 'km' : '-');
            $sheet->setCellValue('I' . $row, $order['created_at_formatted']);
            $sheet->setCellValue('J' . $row, 'R$ ' . number_format($order['valor_total'], 2, ',', '.'));
            $sheet->setCellValue('K' . $row, $order['status_name']);
            $row++;
        }

        // Auto fit columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save
        $filename = 'relatorio_analitico_' . now()->format('Y-m-d_His') . '.xlsx';
        $filepath = storage_path('app/exports/' . $filename);

        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Get analysis data by client
     */
    private function getAnalysisByClient(array $filters = []): array
    {
        $query = OrdemServico::with('cliente');

        // Apply filters
        if (!empty($filters['data_inicio'])) {
            $query->where('created_at', '>=', $filters['data_inicio']);
        }
        if (!empty($filters['data_fim'])) {
            $query->where('created_at', '<=', $filters['data_fim'] . ' 23:59:59');
        }
        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }
        if (!empty($filters['consultor_id'])) {
            $query->where('consultor_id', $filters['consultor_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $results = $query->get()
            ->groupBy('cliente_id')
            ->map(function ($group) {
                $cliente = $group->first()->cliente;
                $total = $group->sum('valor_total');
                return [
                    'name' => $cliente->nome ?? 'Unknown',
                    'orders' => $group->count(),
                    'total' => (float) $total,
                    'avg' => $group->count() > 0 ? $total / $group->count() : 0
                ];
            })
            ->values()
            ->toArray();

        return $results;
    }

    /**
     * Get analysis data by consultant
     */
    private function getAnalysisByConsultant(array $filters = []): array
    {
        $query = OrdemServico::with('consultor');

        // Apply filters
        if (!empty($filters['data_inicio'])) {
            $query->where('created_at', '>=', $filters['data_inicio']);
        }
        if (!empty($filters['data_fim'])) {
            $query->where('created_at', '<=', $filters['data_fim'] . ' 23:59:59');
        }
        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }
        if (!empty($filters['consultor_id'])) {
            $query->where('consultor_id', $filters['consultor_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $results = $query->get()
            ->groupBy('consultor_id')
            ->map(function ($group) {
                $consultor = $group->first()->consultor;
                $total = $group->sum('valor_total');
                return [
                    'name' => $consultor->name ?? 'Unknown',
                    'orders' => $group->count(),
                    'total' => (float) $total,
                    'avg' => $group->count() > 0 ? $total / $group->count() : 0
                ];
            })
            ->values()
            ->toArray();

        return $results;
    }

    /**
     * Export to PDF
     */
    public function exportToPdf(array $filters = [], string $viewType = 'summary'): string
    {
        if ($viewType === 'analytical') {
            return $this->exportToPdfAnalytical($filters);
        } else {
            return $this->exportToPdfSummary($filters);
        }
    }

    /**
     * Export summary view to PDF
     */
    private function exportToPdfSummary(array $filters = []): string
    {
        $summary = $this->getSummaryReport($filters);
        $orders = $this->getFilteredData($filters);
        $statusNames = $this->getStatusNames();

        $html = $this->generatePdfHtml($filters, $summary, $orders, $statusNames);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'relatorio_' . now()->format('Y-m-d_His') . '.pdf';
        $filepath = storage_path('app/exports/' . $filename);

        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        file_put_contents($filepath, $dompdf->output());

        return $filepath;
    }

    /**
     * Export analytical view to PDF
     */
    private function exportToPdfAnalytical(array $filters = []): string
    {
        $summary = $this->getSummaryReport($filters);
        $clientData = $this->getAnalysisByClient($filters);
        $consultantData = $this->getAnalysisByConsultant($filters);
        $orders = $this->getFilteredData($filters);
        $statusNames = $this->getStatusNames();

        $html = $this->generatePdfHtmlAnalytical($filters, $summary, $clientData, $consultantData, $orders, $statusNames);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'relatorio_analitico_' . now()->format('Y-m-d_His') . '.pdf';
        $filepath = storage_path('app/exports/' . $filename);

        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        file_put_contents($filepath, $dompdf->output());

        return $filepath;
    }

    /**
     * Generate PDF HTML
     */
    private function generatePdfHtml(array $filters, array $summary, array $orders, array $statusNames): string
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { margin: 5px 0; }
                .header p { margin: 2px 0; color: #666; }
                .filters { margin-bottom: 20px; padding: 10px; background: #f5f5f5; border-radius: 5px; }
                .summary { margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
                .summary-box { background: #e8f4f8; padding: 15px; border-radius: 5px; }
                .summary-box h4 { margin: 0 0 10px 0; }
                .summary-box p { margin: 0; font-size: 18px; font-weight: bold; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                table th { background: #366092; color: white; padding: 10px; text-align: left; }
                table td { padding: 8px; border-bottom: 1px solid #ddd; }
                table tr:nth-child(even) { background: #f9f9f9; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>PORTAL - RELATÓRIO DE ORDENS DE SERVIÇO</h1>
                <p>Data do Relatório: ' . now()->format('d/m/Y H:i:s') . '</p>
            </div>';

        if (!empty($filters)) {
            $html .= '<div class="filters"><strong>Filtros Aplicados:</strong><br>';
            if (!empty($filters['data_inicio'])) {
                $html .= 'Data Início: ' . $filters['data_inicio'] . '<br>';
            }
            if (!empty($filters['data_fim'])) {
                $html .= 'Data Fim: ' . $filters['data_fim'] . '<br>';
            }
            if (!empty($filters['cliente_id'])) {
                $cliente = Cliente::find($filters['cliente_id']);
                $html .= 'Cliente: ' . ($cliente->nome ?? 'N/A') . '<br>';
            }
            if (!empty($filters['consultor_id'])) {
                $consultor = User::find($filters['consultor_id']);
                $html .= 'Consultor: ' . ($consultor->name ?? 'N/A') . '<br>';
            }
            if (!empty($filters['status'])) {
                $html .= 'Status: ' . ($statusNames[$filters['status']] ?? 'N/A') . '<br>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="summary">
            <div class="summary-box">
                <h4>Total de Ordens</h4>
                <p>' . $summary['total_ordens'] . '</p>
            </div>
            <div class="summary-box">
                <h4>Valor Total</h4>
                <p>R$ ' . number_format($summary['valor_total'], 2, ',', '.') . '</p>
            </div>
            <div class="summary-box">
                <h4>Valor Faturado</h4>
                <p>R$ ' . number_format($summary['valor_faturado'], 2, ',', '.') . '</p>
            </div>
            <div class="summary-box">
                <h4>Valor Pendente</h4>
                <p>R$ ' . number_format($summary['valor_pendente'], 2, ',', '.') . '</p>
            </div>
            <div class="summary-box">
                <h4>Ordens Faturadas</h4>
                <p>' . $summary['total_ordens_faturadas'] . '</p>
            </div>
            <div class="summary-box">
                <h4>Ordens Pendentes</h4>
                <p>' . $summary['total_ordens_pendentes'] . '</p>
            </div>
        </div>';

        $html .= '<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Consultor</th>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Faturado</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($orders as $order) {
            $isBilled = in_array($order['status'], ['Faturada', 'RPS Emitida', 'Aguardando RPS']);
            $html .= '<tr>
                <td>' . $order['id'] . '</td>
                <td>' . $order['client'] . '</td>
                <td>' . $order['consultant'] . '</td>
                <td>' . $order['created_at'] . '</td>
                <td>R$ ' . number_format($order['total'], 2, ',', '.') . '</td>
                <td>' . $order['status'] . '</td>
                <td>' . ($isBilled ? 'Sim' : 'Não') . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return $html;
    }

    /**
     * Generate analytical PDF HTML
     */
    private function generatePdfHtmlAnalytical(array $filters, array $summary, array $clientData, array $consultantData, array $orders, array $statusNames): string
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { margin: 5px 0; color: #333; }
                .header p { margin: 2px 0; color: #666; }
                .section-title { background: #4472C4; color: white; padding: 10px; margin-top: 20px; margin-bottom: 10px; font-weight: bold; }
                .metrics { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px; }
                .metric-box { background: #e8f4f8; padding: 12px; border-radius: 5px; text-align: center; }
                .metric-box h4 { margin: 0 0 8px 0; font-size: 12px; color: #666; }
                .metric-box p { margin: 0; font-size: 16px; font-weight: bold; color: #333; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                table th { background: #4472C4; color: white; padding: 10px; text-align: left; font-size: 12px; }
                table td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 12px; }
                table tr:nth-child(even) { background: #f9f9f9; }
                .filters { margin-bottom: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>PORTAL - RELATÓRIO ANALÍTICO</h1>
                <p>Data do Relatório: ' . now()->format('d/m/Y H:i:s') . '</p>
            </div>';

        if (!empty($filters)) {
            $html .= '<div class="filters"><strong>Filtros Aplicados:</strong><br>';
            if (!empty($filters['data_inicio'])) {
                $html .= 'Data Início: ' . $filters['data_inicio'] . ' | ';
            }
            if (!empty($filters['data_fim'])) {
                $html .= 'Data Fim: ' . $filters['data_fim'] . ' | ';
            }
            if (!empty($filters['cliente_id'])) {
                $cliente = Cliente::find($filters['cliente_id']);
                $html .= 'Cliente: ' . ($cliente->nome ?? 'N/A') . ' | ';
            }
            if (!empty($filters['consultor_id'])) {
                $consultor = User::find($filters['consultor_id']);
                $html .= 'Consultor: ' . ($consultor->name ?? 'N/A') . ' | ';
            }
            if (!empty($filters['status'])) {
                $html .= 'Status: ' . ($statusNames[$filters['status']] ?? 'N/A');
            }
            $html .= '</div>';
        }

        // Overall metrics
        $avgTicket = $summary['total_ordens'] > 0 ? $summary['valor_total'] / $summary['total_ordens'] : 0;
        $conversionRate = $summary['total_ordens'] > 0 ? ($summary['total_ordens_faturadas'] / $summary['total_ordens'] * 100) : 0;

        $html .= '<div class="metrics">
            <div class="metric-box">
                <h4>Total de Ordens</h4>
                <p>' . $summary['total_ordens'] . '</p>
            </div>
            <div class="metric-box">
                <h4>Valor Total</h4>
                <p>R$ ' . number_format($summary['valor_total'], 2, ',', '.') . '</p>
            </div>
            <div class="metric-box">
                <h4>Ticket Médio</h4>
                <p>R$ ' . number_format($avgTicket, 2, ',', '.') . '</p>
            </div>
            <div class="metric-box">
                <h4>Taxa Faturamento</h4>
                <p>' . number_format($conversionRate, 1) . '%</p>
            </div>
        </div>';

        // Client Analysis
        $html .= '<div class="section-title">Análise por Cliente</div>';
        $html .= '<table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Ordens</th>
                    <th>Valor Total</th>
                    <th>Ticket Médio</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($clientData as $client) {
            $html .= '<tr>
                <td>' . $client['name'] . '</td>
                <td>' . $client['orders'] . '</td>
                <td>R$ ' . number_format($client['total'], 2, ',', '.') . '</td>
                <td>R$ ' . number_format($client['avg'], 2, ',', '.') . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';

        // Consultant Analysis
        $html .= '<div class="section-title">Análise por Consultor</div>';
        $html .= '<table>
            <thead>
                <tr>
                    <th>Consultor</th>
                    <th>Ordens</th>
                    <th>Valor Total</th>
                    <th>Ticket Médio</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($consultantData as $consultant) {
            $html .= '<tr>
                <td>' . $consultant['name'] . '</td>
                <td>' . $consultant['orders'] . '</td>
                <td>R$ ' . number_format($consultant['total'], 2, ',', '.') . '</td>
                <td>R$ ' . number_format($consultant['avg'], 2, ',', '.') . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';

        // Project Analysis
        $html .= '<div class="section-title">Análise por Projeto</div>';
        $html .= '<table>
            <thead>
                <tr>
                    <th>Projeto</th>
                    <th>Ordens</th>
                    <th>Valor Total</th>
                </tr>
            </thead>
            <tbody>';

        $projects = [];
        foreach ($orders as $order) {
            $project = $order['projeto_nome'] ?? 'Sem Projeto';
            if (!isset($projects[$project])) {
                $projects[$project] = ['orders' => 0, 'total' => 0];
            }
            $projects[$project]['orders']++;
            $projects[$project]['total'] += $order['valor_total'];
        }

        foreach ($projects as $project => $data) {
            $html .= '<tr>
                <td>' . $project . '</td>
                <td>' . $data['orders'] . '</td>
                <td>R$ ' . number_format($data['total'], 2, ',', '.') . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';

        // Duration Analysis
        $html .= '<div class="section-title">Duração e Deslocamento</div>';
        $totalHours = 0;
        $totalKm = 0;
        foreach ($orders as $order) {
            $totalHours += $order['horas'] ?? 0;
            $totalKm += $order['km'] ?? 0;
        }

        $html .= '<table>
            <thead>
                <tr>
                    <th>Métrica</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Total de Horas</strong></td>
                    <td>' . number_format($totalHours, 2, ',', '.') . ' h</td>
                </tr>
                <tr>
                    <td><strong>Média de Horas/OS</strong></td>
                    <td>' . number_format(count($orders) > 0 ? $totalHours / count($orders) : 0, 2, ',', '.') . ' h</td>
                </tr>
                <tr>
                    <td><strong>Total de KM</strong></td>
                    <td>' . number_format($totalKm, 2, ',', '.') . ' km</td>
                </tr>
                <tr>
                    <td><strong>Média de KM/OS</strong></td>
                    <td>' . number_format(count($orders) > 0 ? $totalKm / count($orders) : 0, 2, ',', '.') . ' km</td>
                </tr>
            </tbody>
        </table>';

        // Detailed Activities Table
        $html .= '<div class="section-title">Detalhes de Atividades</div>';
        $html .= '<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Consultor</th>
                    <th>Projeto</th>
                    <th>Assunto</th>
                    <th>Descrição</th>
                    <th>Horas</th>
                    <th>KM</th>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($orders as $order) {
            $html .= '<tr>
                <td>' . $order['id'] . '</td>
                <td>' . $order['cliente_nome'] . '</td>
                <td>' . $order['consultor_nome'] . '</td>
                <td>' . ($order['projeto_nome'] ?? '-') . '</td>
                <td>' . substr($order['assunto'] ?? '-', 0, 30) . '</td>
                <td>' . substr($order['descricao'] ?: $order['detalhamento'] ?? '-', 0, 50) . '</td>
                <td>' . ($order['horas'] > 0 ? $order['horas'] . 'h' : '-') . '</td>
                <td>' . ($order['km'] > 0 ? $order['km'] . 'km' : '-') . '</td>
                <td>' . $order['created_at_formatted'] . '</td>
                <td>R$ ' . number_format($order['valor_total'], 2, ',', '.') . '</td>
                <td>' . $order['status_name'] . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return $html;
    }

    /**
     * Helper: Sum values from collection
     */
    private function sumValues($collection): float
    {
        return (float) $collection->sum(fn($value) => (float) $value);
    }

    /**
     * Helper: Format orders
     */
    private function formatOrders($orders): array
    {
        $statusNames = $this->getStatusNames();

        return $orders->map(function ($order) use ($statusNames) {
            return [
                'id' => $order->id,
                'client' => $order->cliente->nome ?? 'Unknown',
                'cliente_nome' => $order->cliente->nome ?? 'Unknown',
                'consultant' => $order->consultor->name ?? 'Unknown',
                'consultor_nome' => $order->consultor->name ?? 'Unknown',
                'total' => (float) $order->valor_total,
                'valor_total' => $order->valor_total,
                'status' => (string) $order->status,  // Keep as numeric string for JS mapping
                'status_name' => $statusNames[$order->status] ?? 'Unknown',
                'created_at' => $order->created_at->toIso8601String(),
                'created_at_formatted' => $order->created_at->format('d/m/Y'),
                // New detailed fields
                'projeto_nome' => $order->projeto->nome ?? 'Unknown',
                'projeto_id' => $order->projeto_id,
                'assunto' => $order->assunto ?? '',
                'descricao' => $order->descricao ?? '',
                'detalhamento' => $order->detalhamento ?? '',
                'horas' => (float) ($order->hora_inicio && $order->hora_final
                    ? $this->calculateHours($order->hora_inicio, $order->hora_final, $order->hora_desconto)
                    : 0),
                'km' => (float) ($order->km ?? 0),
                'deslocamento' => $order->deslocamento ?? '',
                'observacao' => $order->observacao ?? '',
            ];
        })->toArray();
    }

    /**
     * Helper: Calculate hours from time range
     */
    private function calculateHours($inicio, $final, $desconto = 0): float
    {
        if (!$inicio || !$final) {
            return 0;
        }

        $inicio_time = \Carbon\Carbon::parse($inicio);
        $final_time = \Carbon\Carbon::parse($final);

        $diff_minutes = $final_time->diffInMinutes($inicio_time);
        $hours = $diff_minutes / 60;

        // Subtract discount hours if provided
        if ($desconto) {
            $hours -= $desconto;
        }

        return max(0, $hours);
    }

    /**
     * Helper: Get status names
     */
    private function getStatusNames(): array
    {
        return [
            1 => 'Aberta',
            2 => 'Aguardando Aprovação',
            3 => 'Aprovado',
            4 => 'Contestada',
            5 => 'Aguardando Faturamento',
            6 => 'Faturada',
            7 => 'Aguardando RPS',
            8 => 'RPS Emitida',
        ];
    }
}
