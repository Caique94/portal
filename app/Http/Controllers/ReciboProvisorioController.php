<?php

namespace App\Http\Controllers;

use App\Models\ReciboProvisorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReciboProvisorioController extends Controller
{

    public function view()
    {
        $user = Auth::user();
        return view('recibo-provisorio', compact('user'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'txtEmissaoRPSClienteId' => 'required|exists:cliente,id',
            'txtEmissaoRPSNumero' => 'required|numeric',
            'txtEmissaoRPSSerie' => 'required|numeric',
            'txtEmissaoRPSEmissao' => 'required|date',
            'slcEmissaoRPSCondPagto' => 'required|in:a_vista,parcelado_x3,parcelado_x4,parcelado_x5,parcelado_x6',
            'txtEmissaoRPSValor' => 'required|numeric|min:0',
            'data_primeira_parcela' => 'nullable|date',
            'intervalo_dias' => 'nullable|integer|min:1',
            'total_parcelas' => 'nullable|integer|min:1|max:12'
        ]);

        $recibo = ReciboProvisorio::create([
            'cliente_id' => $validatedData['txtEmissaoRPSClienteId'],
            'numero' => $validatedData['txtEmissaoRPSNumero'],
            'serie' => $validatedData['txtEmissaoRPSSerie'],
            'data_emissao' => $validatedData['txtEmissaoRPSEmissao'],
            'cond_pagto' => $validatedData['slcEmissaoRPSCondPagto'],
            'valor' => $validatedData['txtEmissaoRPSValor']
        ]);

        // Se é parcelado, criar as parcelas automaticamente
        if (str_contains($validatedData['slcEmissaoRPSCondPagto'], 'parcelado_')) {
            $this->criarParcelasRPS(
                $recibo->id,
                $validatedData['total_parcelas'],
                $validatedData['txtEmissaoRPSValor'],
                $validatedData['data_primeira_parcela'],
                $validatedData['intervalo_dias']
            );
        }

        return response()->json([
            'message' => 'RPS emitida com sucesso!',
            'data' => $recibo
        ], 201);
    }

    private function criarParcelasRPS($reciboId, $totalParcelas, $valorTotal, $dataPrimeira, $intervaloDias)
    {
        $valorParcela = $valorTotal / $totalParcelas;
        $dataPrimeira = \Carbon\Carbon::parse($dataPrimeira);

        for ($i = 1; $i <= $totalParcelas; $i++) {
            $dataVencimento = $dataPrimeira->copy()->addDays(($i - 1) * $intervaloDias);

            \App\Models\PagamentoParcela::create([
                'recibo_provisorio_id' => $reciboId,
                'numero_parcela' => $i,
                'total_parcelas' => $totalParcelas,
                'valor' => round($valorParcela, 2),
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'status' => 'pendente'
            ]);
        }
    }

    public function list(Request $request)
    {
        $status = $request->input('status', 'todos');
        $data = ReciboProvisorio::with('cliente')->get();

        // Adicionar status a cada recibo
        $data = $data->map(function ($recibo) {
            $recibo->payment_status = $this->getStatus($recibo->id);
            return $recibo;
        });

        // Filtrar por status se solicitado
        if ($status !== 'todos') {
            $data = $data->filter(function ($recibo) use ($status) {
                return $recibo->payment_status === $status;
            })->values();
        }

        return response()->json($data);
    }

    private function getStatus($reciboId)
    {
        $recibo = ReciboProvisorio::find($reciboId);
        if (!$recibo) {
            return 'desconhecido';
        }

        $parcelas = \App\Models\PagamentoParcela::where('recibo_provisorio_id', $reciboId)->get();

        if ($parcelas->isEmpty()) {
            return 'quitado'; // Se não tem parcelas, foi pago à vista
        }

        // Verificar se tem parcelas em atraso
        $hoje = \Carbon\Carbon::now()->toDateString();
        $parcelasAtrasadas = $parcelas->filter(function ($p) use ($hoje) {
            return $p->status !== 'paga' && $p->data_vencimento < $hoje;
        });

        if ($parcelasAtrasadas->count() > 0) {
            return 'atraso';
        }

        // Verificar se todas as parcelas estão pagas
        $todasPagas = $parcelas->every(function ($p) {
            return $p->status === 'paga';
        });

        if ($todasPagas) {
            return 'quitado';
        }

        // Se tem parcelas pendentes/atrasadas mas nenhuma vencida
        return 'aberto';
    }

}