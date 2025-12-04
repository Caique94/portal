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
        try {
            $estados = Estado::orderBy('nome', 'asc')->get();
            return response()->json($estados);
        } catch (\Exception $e) {
            \Log::error('Erro ao listar estados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar cidades de um estado específico
     */
    public function listarCidades($estadoId)
    {
        try {
            $cidades = Cidade::where('estado_id', $estadoId)
                ->orderBy('nome', 'asc')
                ->get();
            return response()->json($cidades);
        } catch (\Exception $e) {
            \Log::error('Erro ao listar cidades: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar cidades'
            ], 500);
        }
    }

    /**
     * Buscar cidades por nome (autocomplete)
     */
    public function buscarCidades(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar cidades: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar cidades'
            ], 500);
        }
    }

    /**
     * Buscar estado por sigla ou nome
     */
    public function buscarEstado(Request $request)
    {
        try {
            $search = $request->input('q', '');

            $estado = Estado::where('sigla', 'ilike', $search)
                ->orWhere('nome', 'ilike', '%' . $search . '%')
                ->first();

            if ($estado) {
                return response()->json(['success' => true, 'data' => $estado]);
            }

            return response()->json(['success' => false], 404);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estado'
            ], 500);
        }
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
            $response = \Http::withoutVerifying()->get("https://viacep.com.br/ws/{$cep}/json/");

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao consultar ViaCEP: ' . $response->status()
                ], 500);
            }

            $data = $response->json();

            if (isset($data['erro'])) {
                return response()->json(['success' => false, 'message' => 'CEP não encontrado'], 404);
            }

            if (!isset($data['uf']) || !isset($data['localidade']) || !isset($data['logradouro'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resposta inválida da API de CEP'
                ], 400);
            }

            // Buscar o estado pelo UF retornado
            $estado = Estado::where('sigla', strtoupper($data['uf']))->first();

            if (!$estado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado não encontrado no banco de dados: ' . $data['uf']
                ], 404);
            }

            // Buscar a cidade pelo nome e estado (com busca flexível)
            $nomeCidade = trim($data['localidade']);
            $cidade = Cidade::where('estado_id', $estado->id)
                ->where('nome', 'ilike', $nomeCidade)
                ->first();

            // Se não encontrar exatamente, tentar buscar com LIKE (menos rigoroso)
            if (!$cidade) {
                $cidade = Cidade::where('estado_id', $estado->id)
                    ->where('nome', 'like', '%' . addslashes($nomeCidade) . '%')
                    ->first();
            }

            // Se ainda não encontrar, retornar a resposta com o nome da ViaCEP mesmo assim
            // (o usuário pode estar fora de uma cidade cadastrada ou a API retorna nome genérico)
            if (!$cidade) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'cep' => preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $cep),
                        'endereco' => trim($data['logradouro']),
                        'bairro' => trim($data['bairro']),
                        'cidade' => $nomeCidade,  // Usar nome da ViaCEP
                        'estado' => $estado->nome,
                        'estado_id' => $estado->id,
                        'cidade_id' => null  // Sem ID porque não encontrou no banco
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cep' => preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $cep),
                    'endereco' => trim($data['logradouro']),
                    'bairro' => trim($data['bairro']),
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
