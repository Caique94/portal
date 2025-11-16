<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ProdutoTabela;
use Illuminate\Http\Request;

class ProdutoTabelaController extends Controller
{

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'txtProdutoTabelaTabelaPrecoId' => 'required|numeric|min:0',
            'slcProdutoTabelaProdutoId'     => 'required|numeric|min:0',
            'txtProdutoTabelaPreco'         => 'required|string|max:255'
        ]);

        $mappedData = [
            'tabela_preco_id'   => $validatedData['txtProdutoTabelaTabelaPrecoId'],
            'produto_id'        => $validatedData['slcProdutoTabelaProdutoId'],
            'preco'             => $validatedData['txtProdutoTabelaPreco']
        ];

        $produto_tabela = ProdutoTabela::create($mappedData);

        return response()->json([
            'message'   => 'Produto vinculado com sucesso',
            'data'      => $produto_tabela
        ], 201);
    }

    public function list(Request $request)
    {
        $id = $request->input('id');

        $data = ProdutoTabela::with('produto')->where('tabela_preco_id',$id)->get();
        return response()->json($data);
    }

    public function toggle(Request $request, string $id)
    {
        $produto = ProdutoTabela::find($id);
        $produto->ativo = !$produto->ativo;
        $produto->save();

        return response()->json([
            'message'   => 'Produto da tabela atualizado com sucesso',
            'data'      => $produto
        ], 201);
    }

    public function list_by_client(Request $request, string $client_id)
    {
        $cliente = Cliente::find($client_id);
        
        $data = ProdutoTabela::with('produto')->where('tabela_preco_id',$cliente->tabela_preco_id)->where('ativo', true)->get();
        return response()->json($data);
    }

}