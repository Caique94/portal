<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use App\Models\Cidade;
use Illuminate\Http\Request;

class EstadoCidadeController extends Controller
{
    /**
     * Listar todos os estados brasileiros
     */
    public function listarEstados()
    {
        $estados = Estado::orderBy('nome', 'asc')->get();
        return response()->json($estados);
    }

    /**
     * Listar cidades de um estado específico
     */
    public function listarCidades($estadoId)
    {
        $cidades = Cidade::where('estado_id', $estadoId)
            ->orderBy('nome', 'asc')
            ->get();
        return response()->json($cidades);
    }

    /**
     * Buscar cidades por nome (autocomplete)
     */
    public function buscarCidades(Request $request)
    {
        $search = $request->input('q', '');
        $estadoId = $request->input('estado_id');

        $query = Cidade::query();

        if ($estadoId) {
            $query->where('estado_id', $estadoId);
        }

        if ($search) {
            $query->where('nome', 'ilike', '%' . $search . '%');
        }

        $cidades = $query->orderBy('nome', 'asc')->limit(20)->get();
        return response()->json($cidades);
    }

    /**
     * Buscar estado por sigla ou nome
     */
    public function buscarEstado(Request $request)
    {
        $search = $request->input('q', '');

        $estado = Estado::where('sigla', 'ilike', $search)
            ->orWhere('nome', 'ilike', '%' . $search . '%')
            ->first();

        if ($estado) {
            return response()->json(['success' => true, 'data' => $estado]);
        }

        return response()->json(['success' => false], 404);
    }
}
