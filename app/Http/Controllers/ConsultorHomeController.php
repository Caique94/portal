<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ConsultorOSExport;
use Maatwebsite\Excel\Facades\Excel;

class ConsultorHomeController extends Controller
{
    // Mapeamento de status (DEPRECATED - use statusDisplayMap instead)
    private $statusMap = [
        1 => 'Em Aberto',
        2 => 'Aguardando Aprovação',
        3 => 'Contestada',
        4 => 'Aguardando Faturamento',
        5 => 'Faturada',
        6 => 'Aguardando RPS',
        7 => 'RPS Emitida',
    ];

    // Status finais para consultores (vê até status 7, mas exibe 6,7 como 5)
    private $statusFinal = 7;

    // Mapeamento de status para EXIBIÇÃO (Status 6 e 7 aparecem como Faturada para o consultor)
    private $statusDisplayMap = [
        1 => 'Em Aberto',
        2 => 'Aguardando Aprovação',
        3 => 'Contestada',
        4 => 'Aguardando Faturamento',
        5 => 'Faturada',
        6 => 'Faturada',        // Aguardando RPS aparece como Faturada
        7 => 'Faturada',        // RPS Emitida aparece como Faturada
    ];

    public function index(Request $request)
    {
        $uid = $request->user()->id;

        // Contar apenas as OS do consultor (até status Faturada)
        $total_meu = DB::table('ordem_servico')
            ->where('consultor_id', $uid)
            ->where('status', '<=', $this->statusFinal)
            ->count();

        // Contar as abertas/em andamento (status 1, 2 = Em Aberto, Aguardando Aprovação)
        $abertas_meu = DB::table('ordem_servico')
            ->where('consultor_id', $uid)
            ->whereIn('status', [1, 2]) // Em Aberto, Aguardando Aprovação
            ->count();

        // Período do mês
        $inicioMes = now()->startOfMonth()->format('Y-m-d 00:00:00');
        $fimMes    = now()->endOfMonth()->format('Y-m-d 23:59:59');

        // Valor faturado no mês (apenas do consultor)
        $valor_faturado_mes = DB::table('ordem_servico')
            ->where('consultor_id', $uid)
            ->where('status', 5)
            ->whereBetween('created_at', [$inicioMes, $fimMes])
            ->sum(DB::raw("COALESCE(NULLIF(valor_total,'')::numeric, 0)"));

        // Últimas 10 OS do consultor (apenas até Faturada)
        $ultimas = DB::table('ordem_servico as os')
            ->leftJoin('cliente as c', 'c.id', '=', 'os.cliente_id')
            ->where('os.consultor_id', $uid)
            ->where('os.status', '<=', $this->statusFinal)
            ->orderByDesc('os.id')
            ->limit(10)
            ->get([
                'os.id',
                'os.created_at as data',
                'os.status',
                DB::raw("COALESCE(NULLIF(os.valor_total,'')::numeric,0) as valor_total"),
                DB::raw("COALESCE(c.nome, c.nome_fantasia) as cliente"),
            ])
            ->map(function ($r) {
                $r->status_txt = $this->getDisplayStatus($r->status);
                return $r;
            });

        // Resumo por status (apenas até Faturada)
        // Get OS count by status, consolidating 5, 6, 7 as "Faturada"
        $por_status = DB::table('ordem_servico')
            ->select('status', DB::raw('COUNT(*) as qtd'))
            ->where('consultor_id', $uid)
            ->where('status', '<=', $this->statusFinal)
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(function ($r) {
                $r->status_txt = $this->getDisplayStatus($r->status);
                return $r;
            })
            ->groupBy('status_txt')
            ->map(function ($group) {
                // Consolidate status 5, 6, 7 into one "Faturada" entry by summing qtd
                $total_qtd = $group->sum('qtd');
                return (object)[
                    'status_txt' => $group->first()->status_txt,
                    'qtd' => $total_qtd
                ];
            })
            ->values();

        // Buscar clientes para o filtro
        $clientes = DB::table('cliente')
            ->orderBy('nome')
            ->get(['id', 'nome', 'nome_fantasia']);

        return view('consultor.home', compact(
            'total_meu','abertas_meu','valor_faturado_mes','ultimas','por_status','clientes'
        ));
    }

    /**
     * Exportar relatório de OS para Excel com filtros
     */
    public function exportExcel(Request $request)
    {
        try {
            $uid = $request->user()->id;
            $dataInicio = $request->input('data_inicio');
            $dataFim = $request->input('data_fim');
            $clienteId = $request->input('cliente_id');

            // Query base
            $query = DB::table('ordem_servico as os')
                ->leftJoin('cliente as c', 'c.id', '=', 'os.cliente_id')
                ->where('os.consultor_id', $uid)
                ->where('os.status', '<=', $this->statusFinal);

            // Aplicar filtros
            if ($dataInicio) {
                $query->whereDate('os.created_at', '>=', $dataInicio);
            }
            if ($dataFim) {
                $query->whereDate('os.created_at', '<=', $dataFim);
            }
            if ($clienteId) {
                $query->where('os.cliente_id', $clienteId);
            }

            $dados = $query->orderByDesc('os.id')
                ->get([
                    'os.id',
                    'os.created_at as data',
                    'os.status',
                    DB::raw("COALESCE(NULLIF(os.valor_total,'')::numeric,0) as valor_total"),
                    DB::raw("COALESCE(c.nome, c.nome_fantasia) as cliente"),
                ]);

            // Gerar Excel com separador de ponto-e-vírgula (compatível com Excel em PT-BR)
            $csv = "ID;Data;Cliente;Status;Valor\n";
            $totalValor = 0;

            foreach ($dados as $item) {
                $valor = (float)($item->valor_total ?? 0);
                $totalValor += $valor;
                $data = \Carbon\Carbon::parse($item->data)->format('d/m/Y');
                $status = $this->getDisplayStatus($item->status);
                $cliente = $item->cliente ?? '-';
                $valorFormatado = 'R$ ' . number_format($valor, 2, ',', '.');

                // Escapar valores com ponto-e-vírgula
                $cliente = str_replace(';', ',', $cliente);
                $status = str_replace(';', ',', $status);

                $csv .= "{$item->id};{$data};\"{$cliente}\";{$status};{$valorFormatado}\n";
            }

            // Adicionar linha de total
            $totalFormatado = 'R$ ' . number_format($totalValor, 2, ',', '.');
            $csv .= "TOTAL;;;{$totalFormatado}\n";

            // Gerar download como Excel (CSV com separador ;)
            $filename = 'relatorio_os_' . now()->format('Y-m-d_His') . '.csv';
            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao gerar Excel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exportar relatório de OS para PDF com filtros
     */
    public function exportPDF(Request $request)
    {
        try {
            $uid = $request->user()->id;
            $dataInicio = $request->input('data_inicio');
            $dataFim = $request->input('data_fim');
            $clienteId = $request->input('cliente_id');

            $query = DB::table('ordem_servico as os')
                ->leftJoin('cliente as c', 'c.id', '=', 'os.cliente_id')
                ->where('os.consultor_id', $uid)
                ->where('os.status', '<=', $this->statusFinal);

            // Aplicar filtros
            if ($dataInicio) {
                $query->whereDate('os.created_at', '>=', $dataInicio);
            }
            if ($dataFim) {
                $query->whereDate('os.created_at', '<=', $dataFim);
            }
            if ($clienteId) {
                $query->where('os.cliente_id', $clienteId);
            }

            $dados = $query->orderByDesc('os.id')
                ->get([
                    'os.id',
                    'os.created_at as data',
                    'os.status',
                    DB::raw("COALESCE(NULLIF(os.valor_total,'')::numeric,0) as valor_total"),
                    DB::raw("COALESCE(c.nome, c.nome_fantasia) as cliente"),
                ])
                ->map(function ($r) {
                    $r->status_txt = $this->getDisplayStatus($r->status);
                    return $r;
                });

            $pdf = \PDF::loadView('consultor.relatorio_pdf', [
                'dados' => $dados,
                'consultor' => $request->user()->name,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
            ]);

            return $pdf->download('relatorio_os_' . now()->format('Y-m-d_His') . '.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao gerar PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obter o status de exibição para o consultor
     * (Aguardando RPS aparece como Faturada para o consultor)
     */
    private function getDisplayStatus($status)
    {
        return $this->statusDisplayMap[$status] ?? 'Desconhecido';
    }
}
