<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ProjetoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projetos = Projeto::with('cliente')->get();
        return view('cadastros.projetos.index', compact('projetos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::all();
        return view('cadastros.projetos.create', compact('clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'status' => 'required|in:ativo,pausado,concluido,cancelado',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date',
            'horas_alocadas' => 'nullable|numeric|min:0',
        ]);

        // Validação: não permitir status "concluído" se houver horas restantes
        if ($request->status === 'concluido') {
            $horasAlocadas = $request->horas_alocadas ?? 0;
            $horasRestantes = $horasAlocadas - 0; // Na criação, horas consumidas é 0

            if ($horasRestantes > 0) {
                return back()->withErrors([
                    'status' => 'Não é possível marcar o projeto como concluído enquanto houver horas alocadas não consumidas.'
                ])->withInput();
            }
        }

        Projeto::create($request->all());

        return redirect()->route('projetos.index')->with('success', 'Projeto criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $projeto = Projeto::with('cliente', 'ordemServicos')->findOrFail($id);
        return view('cadastros.projetos.show', compact('projeto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $projeto = Projeto::findOrFail($id);
        $clientes = Cliente::all();
        return view('cadastros.projetos.edit', compact('projeto', 'clientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $projeto = Projeto::findOrFail($id);

        // Se for apenas status (requisição JSON)
        if ($request->isJson() && $request->has('status') && count($request->all()) === 1) {
            $request->validate([
                'status' => 'required|in:ativo,pausado,concluido,cancelado',
            ]);

            // Validação: não permitir status "concluído" se houver horas restantes
            if ($request->status === 'concluido') {
                $horasAlocadas = $projeto->horas_alocadas ?? 0;
                $horasConsumidas = $projeto->horas_consumidas ?? 0;
                $horasRestantes = $horasAlocadas - $horasConsumidas;

                if ($horasRestantes > 0) {
                    return response()->json([
                        'message' => 'Não é possível marcar o projeto como concluído enquanto houver horas alocadas não consumidas (' . number_format($horasRestantes, 2, ',', '.') . 'h restantes).'
                    ], 422);
                }
            }

            $projeto->update(['status' => $request->status]);
            return response()->json(['message' => 'Projeto atualizado com sucesso!']);
        }

        // Atualização completa do formulário
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'status' => 'required|in:ativo,pausado,concluido,cancelado',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date',
            'horas_alocadas' => 'nullable|numeric|min:0',
        ]);

        // Validação: não permitir status "concluído" se houver horas restantes
        if ($request->status === 'concluido') {
            $horasAlocadas = $request->horas_alocadas ?? $projeto->horas_alocadas ?? 0;
            $horasConsumidas = $projeto->horas_consumidas ?? 0;
            $horasRestantes = $horasAlocadas - $horasConsumidas;

            if ($horasRestantes > 0) {
                return back()->withErrors([
                    'status' => 'Não é possível marcar o projeto como concluído enquanto houver horas alocadas não consumidas (' . number_format($horasRestantes, 2, ',', '.') . 'h restantes).'
                ])->withInput();
            }
        }

        $projeto->update($request->all());

        return redirect()->route('projetos.index')->with('success', 'Projeto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $projeto = Projeto::findOrFail($id);
        $projeto->delete();

        return redirect()->route('projetos.index')->with('success', 'Projeto deletado com sucesso!');
    }

    /**
     * Get projetos for a specific cliente (AJAX)
     */
    public function getClienteProjetos($clienteId)
    {
        $projetos = Projeto::where('cliente_id', $clienteId)
            ->where('status', '!=', 'cancelado')
            ->select('id', 'nome', 'codigo')
            ->get();

        return response()->json($projetos);
    }
}
