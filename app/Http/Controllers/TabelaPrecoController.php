<?php

namespace App\Http\Controllers;

use App\Models\TabelaPreco;
use Illuminate\Http\Request;

class TabelaPrecoController extends Controller
{

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'txtTabelaPrecoDescricao'   => 'required|string|max:255'
        ]);

        $mappedData = [
            'descricao' => $validatedData['txtTabelaPrecoDescricao']
        ];

        $tabela = TabelaPreco::create($mappedData);

        return response()->json([
            'message'   => 'Tabela de preços criada com sucesso',
            'data'      => $tabela
        ], 201);
    }

    public function list(Request $request)
    {
        $data = TabelaPreco::all();
        return response()->json($data);
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
        $data = TabelaPreco::where('ativo', true)->orderBy('descricao', 'asc')->get();
        return response()->json($data);
    }

}