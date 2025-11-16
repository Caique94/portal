<?php

namespace App\Http\Controllers;

use App\Models\PagamentoParcela;
use App\Models\ReciboProvisorio;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PagamentoParcelaController extends Controller
{
    /**
     * Listar todas as parcelas ou filtrar por RPS
     */
    public function list(Request $request)
    {
        $query = PagamentoParcela::with(['reciboProvisorio.cliente']);

        // Filtrar por recibo_provisorio_id se fornecido
        if ($request->has('recibo_provisorio_id')) {
            $query->where('recibo_provisorio_id', $request->recibo_provisorio_id);
        }

        // Filtrar por status se fornecido
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Ordenar por data de vencimento
        $parcelas = $query->orderBy('data_vencimento', 'asc')->get();

        // Atualizar status de parcelas atrasadas
        foreach ($parcelas as $parcela) {
            if ($parcela->status === 'pendente' && $parcela->data_vencimento < now()) {
                $parcela->status = 'atrasada';
                $parcela->save();
            }
        }

        return response()->json($parcelas);
    }

    /**
     * Criar parcelas para um RPS
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'recibo_provisorio_id' => 'required|exists:recibo_provisorio,id',
            'total_parcelas' => 'required|integer|min:1|max:12',
            'valor_total' => 'required|numeric|min:0',
            'data_primeira_parcela' => 'required|date',
            'intervalo_dias' => 'required|integer|min:1' // Intervalo entre parcelas (ex: 30 dias)
        ]);

        $rps = ReciboProvisorio::find($validatedData['recibo_provisorio_id']);
        $totalParcelas = $validatedData['total_parcelas'];
        $valorParcela = $validatedData['valor_total'] / $totalParcelas;
        $dataPrimeira = Carbon::parse($validatedData['data_primeira_parcela']);
        $intervaloDias = $validatedData['intervalo_dias'];

        $parcelas = [];

        for ($i = 1; $i <= $totalParcelas; $i++) {
            $dataVencimento = $dataPrimeira->copy()->addDays(($i - 1) * $intervaloDias);

            $parcela = PagamentoParcela::create([
                'recibo_provisorio_id' => $rps->id,
                'numero_parcela' => $i,
                'total_parcelas' => $totalParcelas,
                'valor' => round($valorParcela, 2),
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'status' => 'pendente'
            ]);

            $parcelas[] = $parcela;
        }

        return response()->json([
            'message' => 'Parcelas criadas com sucesso',
            'data' => $parcelas
        ], 201);
    }

    /**
     * Marcar parcela como paga
     */
    public function marcarPaga(Request $request, $id)
    {
        $validatedData = $request->validate([
            'data_pagamento' => 'required|date',
            'observacao' => 'nullable|string|max:1000'
        ]);

        $parcela = PagamentoParcela::findOrFail($id);

        $parcela->status = 'paga';
        $parcela->data_pagamento = $validatedData['data_pagamento'];

        if (isset($validatedData['observacao'])) {
            $parcela->observacao = $validatedData['observacao'];
        }

        $parcela->save();

        return response()->json([
            'message' => 'Parcela marcada como paga',
            'data' => $parcela
        ]);
    }

    /**
     * Atualizar parcela
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'valor' => 'nullable|numeric|min:0',
            'data_vencimento' => 'nullable|date',
            'data_pagamento' => 'nullable|date',
            'status' => 'nullable|in:pendente,paga,atrasada',
            'observacao' => 'nullable|string|max:1000'
        ]);

        $parcela = PagamentoParcela::findOrFail($id);
        $parcela->update($validatedData);

        return response()->json([
            'message' => 'Parcela atualizada com sucesso',
            'data' => $parcela
        ]);
    }

    /**
     * Deletar parcela
     */
    public function delete($id)
    {
        $parcela = PagamentoParcela::findOrFail($id);
        $parcela->delete();

        return response()->json([
            'message' => 'Parcela removida com sucesso'
        ]);
    }

    /**
     * Dashboard de parcelas - estatísticas
     */
    public function dashboard()
    {
        $hoje = now();

        $stats = [
            'total_pendentes' => PagamentoParcela::where('status', 'pendente')->count(),
            'total_atrasadas' => PagamentoParcela::where('status', 'atrasada')->count(),
            'total_pagas' => PagamentoParcela::where('status', 'paga')->count(),
            'valor_pendente' => PagamentoParcela::where('status', 'pendente')->sum('valor'),
            'valor_atrasado' => PagamentoParcela::where('status', 'atrasada')->sum('valor'),
            'valor_pago' => PagamentoParcela::where('status', 'paga')->sum('valor'),
            'vencendo_mes' => PagamentoParcela::where('status', 'pendente')
                ->whereBetween('data_vencimento', [$hoje, $hoje->copy()->addDays(30)])
                ->count()
        ];

        return response()->json($stats);
    }
}
