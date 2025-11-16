<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    // VIEW principal de relatórios
    public function index()
    {
        return view('relatorios.index');
    }

    // RELATÓRIO: Fechamento por Cliente
    public function fechamentoCliente(Request $request)
    {
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $clienteId = $request->get('cliente_id');

        $query = DB::table('ordem_servico as os')
            ->join('cliente as c', 'os.cliente_id', '=', 'c.id')
            ->select(
                'c.codigo',
                'c.nome as cliente_nome',
                DB::raw('COUNT(os.id) as total_ordens'),
                DB::raw('SUM(CAST(os.valor_total AS NUMERIC)) as valor_total'),
                DB::raw('SUM(CASE WHEN os.status = 5 THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_faturado'),
                DB::raw('SUM(CASE WHEN os.status < 5 THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_pendente')
            )
            ->groupBy('c.id', 'c.codigo', 'c.nome');

        if ($dataInicio) {
            $query->where('os.data_emissao', '>=', $dataInicio);
        }
        if ($dataFim) {
            $query->where('os.data_emissao', '<=', $dataFim);
        }
        if ($clienteId) {
            $query->where('os.cliente_id', $clienteId);
        }

        $resultado = $query->get();

        return response()->json($resultado);
    }

    // RELATÓRIO: Fechamento por Consultor
    public function fechamentoConsultor(Request $request)
    {
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $consultorId = $request->get('consultor_id');

        $query = DB::table('ordem_servico as os')
            ->join('users as u', 'os.consultor_id', '=', 'u.id')
            ->select(
                'u.name as consultor_nome',
                DB::raw('COUNT(os.id) as total_ordens'),
                DB::raw('SUM(CAST(os.valor_total AS NUMERIC)) as valor_total'),
                DB::raw('SUM(CASE WHEN os.status = 5 THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_faturado'),
                DB::raw('SUM(CASE WHEN os.status < 5 THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_pendente'),
                DB::raw('AVG(CAST(os.valor_total AS NUMERIC)) as ticket_medio')
            )
            ->groupBy('u.id', 'u.name');

        if ($dataInicio) {
            $query->where('os.data_emissao', '>=', $dataInicio);
        }
        if ($dataFim) {
            $query->where('os.data_emissao', '<=', $dataFim);
        }
        if ($consultorId) {
            $query->where('os.consultor_id', $consultorId);
        }

        $resultado = $query->get();

        return response()->json($resultado);
    }

    // RELATÓRIO: Fechamento Geral (Resumo)
    public function fechamentoGeral(Request $request)
    {
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');

        $query = DB::table('ordem_servico as os')
            ->select(
                DB::raw('COUNT(os.id) as total_ordens'),
                DB::raw('SUM(CAST(os.valor_total AS NUMERIC)) as valor_total'),
                DB::raw('SUM(CASE WHEN os.status = 5 THEN 1 ELSE 0 END) as ordens_faturadas'),
                DB::raw('SUM(CASE WHEN os.status = 5 THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_faturado'),
                DB::raw('SUM(CASE WHEN os.status < 5 THEN 1 ELSE 0 END) as ordens_pendentes'),
                DB::raw('SUM(CASE WHEN os.status < 5 THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_pendente'),
                DB::raw('AVG(CAST(os.valor_total AS NUMERIC)) as ticket_medio')
            );

        if ($dataInicio) {
            $query->where('os.data_emissao', '>=', $dataInicio);
        }
        if ($dataFim) {
            $query->where('os.data_emissao', '<=', $dataFim);
        }

        $resultado = $query->first();

        return response()->json($resultado);
    }

    // RELATÓRIO: Ordens por Status
    public function ordemPorStatus(Request $request)
    {
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');

        $query = DB::table('ordem_servico as os')
            ->select(
                'os.status',
                DB::raw('COUNT(os.id) as total'),
                DB::raw('SUM(CAST(os.valor_total AS NUMERIC)) as valor_total')
            )
            ->groupBy('os.status')
            ->orderBy('os.status');

        if ($dataInicio) {
            $query->where('os.data_emissao', '>=', $dataInicio);
        }
        if ($dataFim) {
            $query->where('os.data_emissao', '<=', $dataFim);
        }

        $resultado = $query->get();

        return response()->json($resultado);
    }
}
