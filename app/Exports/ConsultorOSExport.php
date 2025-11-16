<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsultorOSExport implements FromCollection, WithHeadings, WithStyles
{
    private $uid;
    private $statusMap;
    private $dataInicio;
    private $dataFim;
    private $clienteId;

    public function __construct($uid, $statusMap, $dataInicio = null, $dataFim = null, $clienteId = null)
    {
        $this->uid = $uid;
        $this->statusMap = $statusMap;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->clienteId = $clienteId;
    }

    public function collection()
    {
        // Status final para consultores (até Faturada - status 5)
        $statusFinal = 5;

        $query = DB::table('ordem_servico as os')
            ->leftJoin('cliente as c', 'c.id', '=', 'os.cliente_id')
            ->where('os.consultor_id', $this->uid)
            ->where('os.status', '<=', $statusFinal);

        // Aplicar filtros
        if ($this->dataInicio) {
            $query->whereDate('os.created_at', '>=', $this->dataInicio);
        }
        if ($this->dataFim) {
            $query->whereDate('os.created_at', '<=', $this->dataFim);
        }
        if ($this->clienteId) {
            $query->where('os.cliente_id', $this->clienteId);
        }

        $dados = $query->orderByDesc('os.id')
            ->get([
                'os.id',
                'os.created_at as data',
                'os.status',
                DB::raw("COALESCE(NULLIF(os.valor_total,'')::numeric,0) as valor_total"),
                DB::raw("COALESCE(c.nome, c.nome_fantasia) as cliente"),
            ])
            ->map(function ($row) {
                return [
                    'ID' => $row->id,
                    'Data' => \Carbon\Carbon::parse($row->data)->format('d/m/Y'),
                    'Cliente' => $row->cliente ?? '-',
                    'Status' => $this->statusMap[$row->status] ?? 'Desconhecido',
                    'Valor' => 'R$ ' . number_format($row->valor_total ?? 0, 2, ',', '.'),
                ];
            });

        return $dados;
    }

    public function headings(): array
    {
        return ['ID', 'Data', 'Cliente', 'Status', 'Valor'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F6FEB']],
            ],
        ];
    }
}
