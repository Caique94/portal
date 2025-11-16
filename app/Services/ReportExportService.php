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
            ->with(['cliente', 'consultor']);

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
    public function exportToExcel(array $filters = [], string $type = 'completo'): string
    {
        $this->spreadsheet = new Spreadsheet();
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle('Relatório');

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
            $sheet->getStyle($col . $row)->getFont()->setBold(true)->setColor('FFFFFF');
            $sheet->getStyle($col . $row)->getFill()->setFillType('solid')->getStartColor()->setRGB('366092');
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
     * Export to PDF
     */
    public function exportToPdf(array $filters = []): string
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
            ];
        })->toArray();
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
