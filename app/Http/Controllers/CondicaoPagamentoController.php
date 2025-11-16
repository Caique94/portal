<?php

namespace App\Http\Controllers;

use App\Models\CondicaoPagamento;
use Illuminate\Http\Request;

class CondicaoPagamentoController extends Controller
{
    /**
     * Listar todas as condições de pagamento
     */
    public function list()
    {
        $condicoes = CondicaoPagamento::where('ativo', true)->orderBy('numero_parcelas')->get();
        return response()->json($condicoes);
    }

    /**
     * Salvar nova condição de pagamento
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'descricao' => 'required|string|max:100|unique:condicoes_pagamento,descricao',
            'numero_parcelas' => 'required|integer|min:1|max:12',
            'intervalo_dias' => 'required|integer|min:0',
            'ativo' => 'boolean'
        ]);

        $condicao = CondicaoPagamento::create($validatedData);

        return response()->json([
            'message' => 'Condição de pagamento criada com sucesso!',
            'data' => $condicao
        ], 201);
    }

    /**
     * Atualizar condição de pagamento
     */
    public function update(Request $request, $id)
    {
        $condicao = CondicaoPagamento::findOrFail($id);

        $validatedData = $request->validate([
            'descricao' => 'required|string|max:100|unique:condicoes_pagamento,descricao,' . $id,
            'numero_parcelas' => 'required|integer|min:1|max:12',
            'intervalo_dias' => 'required|integer|min:0',
            'ativo' => 'boolean'
        ]);

        $condicao->update($validatedData);

        return response()->json([
            'message' => 'Condição de pagamento atualizada com sucesso!',
            'data' => $condicao
        ]);
    }

    /**
     * Deletar condição de pagamento
     */
    public function delete($id)
    {
        $condicao = CondicaoPagamento::findOrFail($id);
        $condicao->delete();

        return response()->json([
            'message' => 'Condição de pagamento deletada com sucesso!'
        ]);
    }

    /**
     * Obter todas as condições (incluindo inativas) para admin
     */
    public function allCondicoes()
    {
        $condicoes = CondicaoPagamento::orderBy('numero_parcelas')->get();
        return response()->json($condicoes);
    }
}
