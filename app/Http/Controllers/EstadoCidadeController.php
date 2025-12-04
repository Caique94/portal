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

    /**
     * Buscar endereço por CEP usando ViaCEP
     */
    public function buscarCEP(Request $request)
    {
        $cep = preg_replace('/\D/', '', $request->input('cep', ''));

        if (strlen($cep) !== 8) {
            return response()->json(['success' => false, 'message' => 'CEP inválido'], 400);
        }

        try {
            $response = \Http::get("https://viacep.com.br/ws/{$cep}/json/");
            $data = $response->json();

            if (isset($data['erro'])) {
                return response()->json(['success' => false, 'message' => 'CEP não encontrado'], 404);
            }

            // Buscar o estado pelo UF retornado
            $estado = Estado::where('sigla', $data['uf'])->first();

            if (!$estado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado não encontrado no banco de dados'
                ], 404);
            }

            // Buscar a cidade pelo nome e estado
            $cidade = Cidade::where('estado_id', $estado->id)
                ->where('nome', 'ilike', $data['localidade'])
                ->first();

            if (!$cidade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cidade não encontrada no banco de dados'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cep' => $cep,
                    'endereco' => $data['logradouro'],
                    'bairro' => $data['bairro'],
                    'cidade' => $cidade->nome,
                    'estado' => $estado->nome,
                    'estado_id' => $estado->id,
                    'cidade_id' => $cidade->id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar CEP: ' . $e->getMessage()
            ], 500);
        }
    }
}
