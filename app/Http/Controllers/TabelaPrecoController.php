<?php

namespace App\Http\Controllers;

use App\Models\TabelaPreco;
use Illuminate\Http\Request;

class TabelaPrecoController extends Controller
{

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'txtTabelaPrecoDescricao'   => 'required|string|max:255',
            'txtTabelaPrecoDataInicio'  => 'required|date',
            'txtTabelaPrecoDataVencimento' => 'required|date|after:txtTabelaPrecoDataInicio'
        ]);

        $mappedData = [
            'descricao' => $validatedData['txtTabelaPrecoDescricao'],
            'data_inicio' => $validatedData['txtTabelaPrecoDataInicio'],
            'data_vencimento' => $validatedData['txtTabelaPrecoDataVencimento']
        ];

        $tabela = TabelaPreco::create($mappedData);

        return response()->json([
            'message'   => 'Tabela de preços criada com sucesso',
            'data'      => $tabela
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'txtTabelaPrecoDescricao'   => 'required|string|max:255',
            'txtTabelaPrecoDataInicio'  => 'required|date',
            'txtTabelaPrecoDataVencimento' => 'required|date|after:txtTabelaPrecoDataInicio'
        ]);

        $tabela = TabelaPreco::find($id);

        if (!$tabela) {
            return response()->json([
                'message'   => 'Tabela de preços não encontrada'
            ], 404);
        }

        $tabela->update([
            'descricao' => $validatedData['txtTabelaPrecoDescricao'],
            'data_inicio' => $validatedData['txtTabelaPrecoDataInicio'],
            'data_vencimento' => $validatedData['txtTabelaPrecoDataVencimento']
        ]);

        return response()->json([
            'message'   => 'Tabela de preços atualizada com sucesso',
            'data'      => $tabela
        ], 200);
    }

    public function list(Request $request)
    {
        $data = TabelaPreco::all();
        return response()->json([
            'data' => $data
        ]);
    }

    public function toggle(Request $request, string $id)
    {
        $tabela = TabelaPreco::find($id);
        $tabela->ativo = !$tabela->ativo;
        $tabela->save();

        return response()->json([
            'message'   => 'Tabela de preços atualizada com sucesso',
            'data'      => $tabela
        ], 201);
    }

    public function active_list(Request $request)
    {
        $hoje = now()->format('Y-m-d');
        $data = TabelaPreco::where('ativo', true)
            ->where('data_inicio', '<=', $hoje)
            ->where('data_vencimento', '>=', $hoje)
            ->orderBy('descricao', 'asc')
            ->get();
        return response()->json($data);
    }

}