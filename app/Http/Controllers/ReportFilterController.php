<?php

namespace App\Http\Controllers;

use App\Services\ReportExportService;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;

class ReportFilterController extends Controller
{
    protected $exportService;

    public function __construct(ReportExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Get filter options (clientes, consultores, status)
     */
    public function getFilterOptions()
    {
        $clientes = Cliente::select('id', 'nome')->orderBy('nome')->get();
        $consultores = User::where('papel', 'consultor')
            ->where('ativo', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $statusOptions = [
            ['id' => 1, 'name' => 'Aberta'],
            ['id' => 2, 'name' => 'Aguardando Aprovação'],
            ['id' => 3, 'name' => 'Aprovado'],
            ['id' => 4, 'name' => 'Contestada'],
            ['id' => 5, 'name' => 'Aguardando Faturamento'],
            ['id' => 6, 'name' => 'Faturada'],
            ['id' => 7, 'name' => 'Aguardando RPS'],
            ['id' => 8, 'name' => 'RPS Emitida'],
        ];

        return response()->json([
            'clientes' => $clientes,
            'consultores' => $consultores,
            'status' => $statusOptions,
        ]);
    }

    /**
     * Get filtered data
     */
    public function getFiltered(Request $request)
    {
        $filters = $request->all();
        $data = $this->exportService->getFilteredData($filters);
        $summary = $this->exportService->getSummaryReport($filters);

        return response()->json([
            'data' => $data,
            'summary' => $summary,
        ]);
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $filters = $request->all();
            $filepath = $this->exportService->exportToExcel($filters);

            return response()->download($filepath, 'relatorio_' . now()->format('Y-m-d_His') . '.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao gerar Excel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $filters = $request->all();
            $filepath = $this->exportService->exportToPdf($filters);

            return response()->download($filepath, 'relatorio_' . now()->format('Y-m-d_His') . '.pdf')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao gerar PDF: ' . $e->getMessage()], 500);
        }
    }
}
