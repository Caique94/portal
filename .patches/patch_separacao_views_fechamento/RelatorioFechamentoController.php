<?php

namespace App\Http\Controllers;

use App\Models\RelatorioFechamento;
use App\Models\User;
use App\Mail\RelatorioFechamentoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioFechamentoController extends Controller
{
    /**
     * Listar relatórios de fechamento CLIENTE (usa totalizador administrativo)
     */
    public function indexCliente(Request $request)
    {
        $this->authorize('viewAny', RelatorioFechamento::class);

        $query = RelatorioFechamento::with('consultor', 'aprovador')
            ->where('tipo', 'cliente')
            ->orderByDesc('id');

        // Filtros
        if ($request->has('consultor_id') && $request->consultor_id) {
            $query->where('consultor_id', $request->consultor_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('data_inicio', '>=', $request->data_inicio);
        }

        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('data_fim', '<=', $request->data_fim);
        }

        $relatorios = $query->paginate(15);
        $consultores = User::where('papel', 'consultor')->orderBy('name')->get();

        return view('relatorio-fechamento.index-cliente', compact('relatorios', 'consultores'));
    }

    /**
     * Listar relatórios de fechamento CONSULTOR (usa totalizador consultor)
     */
    public function indexConsultor(Request $request)
    {
        $this->authorize('viewAny', RelatorioFechamento::class);

        $query = RelatorioFechamento::with('consultor', 'aprovador')
            ->where('tipo', 'consultor')
            ->orderByDesc('id');

        // Filtros
        if ($request->has('consultor_id') && $request->consultor_id) {
            $query->where('consultor_id', $request->consultor_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('data_inicio', '>=', $request->data_inicio);
        }

        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('data_fim', '<=', $request->data_fim);
        }

        $relatorios = $query->paginate(15);
        $consultores = User::where('papel', 'consultor')->orderBy('name')->get();

        return view('relatorio-fechamento.index-consultor', compact('relatorios', 'consultores'));
    }

    /**
     * Formulário para criar novo relatório CLIENTE
     */
    public function createCliente(Request $request)
    {
        $this->authorize('create', RelatorioFechamento::class);

        $consultores = User::where('papel', 'consultor')->orderBy('name')->get();
        $clientes = \App\Models\Cliente::orderBy('nome')->get();

        // Se veio com período da URL, usa eles
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $tipo = 'cliente';

        return view('relatorio-fechamento.create', compact('consultores', 'clientes', 'dataInicio', 'dataFim', 'tipo'));
    }

    /**
     * Formulário para criar novo relatório CONSULTOR
     */
    public function createConsultor(Request $request)
    {
        $this->authorize('create', RelatorioFechamento::class);

        $consultores = User::where('papel', 'consultor')->orderBy('name')->get();
        $clientes = \App\Models\Cliente::orderBy('nome')->get();

        // Se veio com período da URL, usa eles
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $tipo = 'consultor';

        return view('relatorio-fechamento.create', compact('consultores', 'clientes', 'dataInicio', 'dataFim', 'tipo'));
    }

    /**
     * Gerar e salvar novo relatório CLIENTE
     */
    public function storeCliente(Request $request)
    {
        $request->merge(['tipo' => 'cliente']);
        return $this->store($request);
    }

    /**
     * Gerar e salvar novo relatório CONSULTOR
     */
    public function storeConsultor(Request $request)
    {
        $request->merge(['tipo' => 'consultor']);
        return $this->store($request);
    }

    /**
     * Visualizar relatório CLIENTE
     */
    public function showCliente(RelatorioFechamento $relatorioFechamento)
    {
        if ($relatorioFechamento->tipo !== 'cliente') {
            abort(404);
        }
        return $this->show($relatorioFechamento);
    }

    /**
     * Visualizar relatório CONSULTOR
     */
    public function showConsultor(RelatorioFechamento $relatorioFechamento)
    {
        if ($relatorioFechamento->tipo !== 'consultor') {
            abort(404);
        }
        return $this->show($relatorioFechamento);
    }

    /**
     * Gerar e salvar novo relatório
     */
    public function store(Request $request)
    {
        $this->authorize('create', RelatorioFechamento::class);

        $validated = $request->validate([
            'consultor_id' => 'required',
            'cliente_id' => 'nullable',
            'tipo' => 'required|in:consultor,cliente',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        // Validar consultor_id (pode ser 'todos' ou um ID válido)
        if ($validated['consultor_id'] !== 'todos') {
            $request->validate([
                'consultor_id' => 'exists:users,id',
            ]);
        }

        // Validar cliente_id (pode ser 'todos', vazio ou um ID válido)
        if (!empty($validated['cliente_id']) && $validated['cliente_id'] !== 'todos') {
            $request->validate([
                'cliente_id' => 'exists:cliente,id',
            ]);
        }

        // Se cliente_id for 'todos', gerar relatórios separados por cliente
        if (!empty($validated['cliente_id']) && $validated['cliente_id'] === 'todos') {
            $clientes = \App\Models\Cliente::orderBy('nome')->get();
            $relatoriosGerados = [];

            foreach ($clientes as $cliente) {
                // Se consultor_id for 'todos', gerar para todos os consultores deste cliente
                if ($validated['consultor_id'] === 'todos') {
                    $consultores = User::where('papel', 'consultor')->orderBy('name')->get();

                    foreach ($consultores as $consultor) {
                        $query = \App\Models\OrdemServico::with(['cliente', 'produtoTabela.produto'])
                            ->where('consultor_id', $consultor->id)
                            ->where('cliente_id', $cliente->id)
                            ->where('status', '<=', 5)
                            ->whereBetween('created_at', [
                                Carbon::parse($validated['data_inicio'])->startOfDay(),
                                Carbon::parse($validated['data_fim'])->endOfDay(),
                            ]);

                        $ordemServicos = $query->get();

                        if ($ordemServicos->isEmpty()) {
                            continue;
                        }

                        if ($validated['tipo'] === 'consultor') {
                            $valorTotal = $this->calcularValorConsultor($ordemServicos, $consultor);
                        } else {
                            $valorTotal = $this->calcularValorCliente($ordemServicos, $consultor);
                        }

                        $totalOs = $ordemServicos->count();

                        $relatorio = RelatorioFechamento::create([
                            'consultor_id' => $consultor->id,
                            'tipo' => $validated['tipo'],
                            'data_inicio' => $validated['data_inicio'],
                            'data_fim' => $validated['data_fim'],
                            'valor_total' => $valorTotal,
                            'total_os' => $totalOs,
                            'status' => 'rascunho',
                        ]);

                        $relatoriosGerados[] = $relatorio;
                    }
                } else {
                    // Consultor específico, cliente atual do loop
                    $consultor = User::findOrFail($validated['consultor_id']);

                    $query = \App\Models\OrdemServico::with(['cliente', 'produtoTabela.produto'])
                        ->where('consultor_id', $consultor->id)
                        ->where('cliente_id', $cliente->id)
                        ->where('status', '<=', 5)
                        ->whereBetween('created_at', [
                            Carbon::parse($validated['data_inicio'])->startOfDay(),
                            Carbon::parse($validated['data_fim'])->endOfDay(),
                        ]);

                    $ordemServicos = $query->get();

                    if ($ordemServicos->isEmpty()) {
                        continue;
                    }

                    if ($validated['tipo'] === 'consultor') {
                        $valorTotal = $this->calcularValorConsultor($ordemServicos, $consultor);
                    } else {
                        $valorTotal = $this->calcularValorCliente($ordemServicos, $consultor);
                    }

                    $totalOs = $ordemServicos->count();

                    $relatorio = RelatorioFechamento::create([
                        'consultor_id' => $consultor->id,
                        'tipo' => $validated['tipo'],
                        'data_inicio' => $validated['data_inicio'],
                        'data_fim' => $validated['data_fim'],
                        'valor_total' => $valorTotal,
                        'total_os' => $totalOs,
                        'status' => 'rascunho',
                    ]);

                    $relatoriosGerados[] = $relatorio;
                }
            }

            $routeName = $validated['tipo'] === 'cliente' ? 'relatorio-fechamento-cliente.index' : 'relatorio-fechamento-consultor.index';
            return redirect()->route($routeName)
                ->with('success', count($relatoriosGerados) . ' relatórios gerados com sucesso!');
        }

        // Se for "todos", gerar relatórios individuais para cada consultor
        if ($validated['consultor_id'] === 'todos') {
            $consultores = User::where('papel', 'consultor')->orderBy('name')->get();
            $relatoriosGerados = [];

            foreach ($consultores as $consultor) {
                // Buscar todas as OS do período para o consultor
                $query = \App\Models\OrdemServico::with(['cliente', 'produtoTabela.produto'])
                    ->where('consultor_id', $consultor->id)
                    ->where('status', '<=', 5)
                    ->whereBetween('created_at', [
                        Carbon::parse($validated['data_inicio'])->startOfDay(),
                        Carbon::parse($validated['data_fim'])->endOfDay(),
                    ]);

                // Filtrar por cliente se especificado
                if (!empty($validated['cliente_id']) && $validated['cliente_id'] !== 'todos') {
                    $query->where('cliente_id', $validated['cliente_id']);
                }

                $ordemServicos = $query->get();

                // Pular se não houver OS para este consultor
                if ($ordemServicos->isEmpty()) {
                    continue;
                }

                // Calcular totais baseado no tipo
                if ($validated['tipo'] === 'consultor') {
                    $valorTotal = $this->calcularValorConsultor($ordemServicos, $consultor);
                } else {
                    $valorTotal = $this->calcularValorCliente($ordemServicos, $consultor);
                }

                $totalOs = $ordemServicos->count();

                // Salvar relatório
                $relatorio = RelatorioFechamento::create([
                    'consultor_id' => $consultor->id,
                    'tipo' => $validated['tipo'],
                    'data_inicio' => $validated['data_inicio'],
                    'data_fim' => $validated['data_fim'],
                    'valor_total' => $valorTotal,
                    'total_os' => $totalOs,
                    'status' => 'rascunho',
                ]);

                $relatoriosGerados[] = $relatorio;
            }

            $routeName = $validated['tipo'] === 'cliente' ? 'relatorio-fechamento-cliente.index' : 'relatorio-fechamento-consultor.index';
            return redirect()->route($routeName)
                ->with('success', count($relatoriosGerados) . ' relatórios gerados com sucesso!');
        }

        // Buscar consultor específico
        $consultor = User::findOrFail($validated['consultor_id']);

        // Buscar todas as OS do período para o consultor
        $query = \App\Models\OrdemServico::with(['cliente', 'produtoTabela.produto'])
            ->where('consultor_id', $validated['consultor_id'])
            ->where('status', '<=', 5)
            ->whereBetween('created_at', [
                Carbon::parse($validated['data_inicio'])->startOfDay(),
                Carbon::parse($validated['data_fim'])->endOfDay(),
            ]);

        // Filtrar por cliente se especificado
        if (!empty($validated['cliente_id']) && $validated['cliente_id'] !== 'todos') {
            $query->where('cliente_id', $validated['cliente_id']);
        }

        $ordemServicos = $query->get();

        // Calcular totais baseado no tipo
        if ($validated['tipo'] === 'consultor') {
            $valorTotal = $this->calcularValorConsultor($ordemServicos, $consultor);
        } else {
            $valorTotal = $this->calcularValorCliente($ordemServicos, $consultor);
        }

        $totalOs = $ordemServicos->count();

        // Salvar relatório
        $relatorio = RelatorioFechamento::create([
            'consultor_id' => $validated['consultor_id'],
            'tipo' => $validated['tipo'],
            'data_inicio' => $validated['data_inicio'],
            'data_fim' => $validated['data_fim'],
            'valor_total' => $valorTotal,
            'total_os' => $totalOs,
            'status' => 'rascunho',
        ]);

        $routeName = $validated['tipo'] === 'cliente' ? 'relatorio-fechamento-cliente.show' : 'relatorio-fechamento-consultor.show';
        return redirect()->route($routeName, $relatorio)
            ->with('success', 'Relatório gerado com sucesso!');
    }

    /**
     * Calcular valor total baseado nos valores do consultor
     */
    private function calcularValorConsultor($ordemServicos, $consultor)
    {
        $total = 0;

        foreach ($ordemServicos as $os) {
            // Valor Serviço = horas × valor_hora_consultor
            $horas = floatval($os->horas_trabalhadas ?? 0);
            $valorHoraConsultor = floatval($consultor->valor_hora ?? 0);
            $valorServico = $horas * $valorHoraConsultor;

            // Despesas
            $despesas = floatval($os->valor_despesa ?? 0);

            // KM e Deslocamento (apenas se presencial)
            $valorKM = 0;
            $valorDeslocamento = 0;

            if ($os->is_presencial) {
                $km = floatval($os->km ?? 0);
                $valorKmConsultor = floatval($consultor->valor_km ?? 0);
                $valorKM = $km * $valorKmConsultor;

                $horasDeslocamento = floatval($os->deslocamento ?? 0);
                $valorDeslocamento = $horasDeslocamento * $valorHoraConsultor;
            }

            $total += $valorServico + $despesas + $valorKM + $valorDeslocamento;
        }

        return $total;
    }

    /**
     * Calcular valor total baseado nos valores do cliente (administrativo)
     */
    private function calcularValorCliente($ordemServicos, $consultor)
    {
        $total = 0;

        foreach ($ordemServicos as $os) {
            // Valor Serviço = horas × preco_produto (tabela de preços do cliente)
            $horas = floatval($os->horas_trabalhadas ?? 0);
            $precoProduto = floatval($os->preco_produto ?? 0);
            $valorServico = $horas * $precoProduto;

            // Despesas
            $despesas = floatval($os->valor_despesa ?? 0);

            // KM e Deslocamento (usa valores do consultor mesmo no admin)
            $valorKM = 0;
            $valorDeslocamento = 0;

            if ($os->is_presencial) {
                $km = floatval($os->km ?? 0);
                $valorKmConsultor = floatval($consultor->valor_km ?? 0);
                $valorKM = $km * $valorKmConsultor;

                $horasDeslocamento = floatval($os->deslocamento ?? 0);
                $valorHoraConsultor = floatval($consultor->valor_hora ?? 0);
                $valorDeslocamento = $horasDeslocamento * $valorHoraConsultor;
            }

            $total += $valorServico + $despesas + $valorKM + $valorDeslocamento;
        }

        return $total;
    }

    /**
     * Visualizar relatório
     */
    public function show(RelatorioFechamento $relatorioFechamento)
    {
        $this->authorize('view', $relatorioFechamento);

        $ordemServicos = $relatorioFechamento->ordemServicos();
        $consultor = $relatorioFechamento->consultor;

        return view('relatorio-fechamento.show', compact('relatorioFechamento', 'ordemServicos', 'consultor'));
    }

    /**
     * Enviar relatório para aprovação
     */
    public function enviarAprovacao(RelatorioFechamento $relatorioFechamento)
    {
        $this->authorize('update', $relatorioFechamento);

        if ($relatorioFechamento->status !== 'rascunho' && $relatorioFechamento->status !== 'rejeitado') {
            return back()->with('error', 'Apenas relatórios em rascunho ou rejeitados podem ser enviados!');
        }

        $relatorioFechamento->update([
            'status' => 'enviado',
        ]);

        return back()->with('success', 'Relatório enviado para aprovação com sucesso!');
    }

    /**
     * Gerar PDF do relatório
     */
    public function pdf(RelatorioFechamento $relatorioFechamento)
    {
        $this->authorize('view', $relatorioFechamento);

        $ordemServicos = $relatorioFechamento->ordemServicos();
        $consultor = $relatorioFechamento->consultor;

        return Pdf::loadView('relatorio-fechamento.pdf', compact('relatorioFechamento', 'ordemServicos', 'consultor'))
            ->download('relatorio_fechamento_' . $relatorioFechamento->id . '.pdf');
    }

    /**
     * Aprovar relatório
     */
    public function aprovar(RelatorioFechamento $relatorioFechamento)
    {
        $this->authorize('update', $relatorioFechamento);

        $relatorioFechamento->update([
            'status' => 'aprovado',
            'aprovado_por' => auth()->id(),
            'data_aprovacao' => now(),
        ]);

        return back()->with('success', 'Relatório aprovado com sucesso!');
    }

    /**
     * Rejeitar relatório
     */
    public function rejeitar(RelatorioFechamento $relatorioFechamento, Request $request)
    {
        $this->authorize('update', $relatorioFechamento);

        $validated = $request->validate([
            'observacoes' => 'required|string|min:10',
        ]);

        $relatorioFechamento->update([
            'status' => 'rejeitado',
            'observacoes' => $validated['observacoes'],
            'aprovado_por' => auth()->id(),
            'data_aprovacao' => now(),
        ]);

        return back()->with('success', 'Relatório rejeitado!');
    }

    /**
     * Enviar relatório por email para o consultor
     */
    public function enviarEmail(RelatorioFechamento $relatorioFechamento)
    {
        $this->authorize('update', $relatorioFechamento);

        if ($relatorioFechamento->status !== 'aprovado') {
            return back()->with('error', 'Apenas relatórios aprovados podem ser enviados!');
        }

        try {
            // Verificar se já foi enviado
            if ($relatorioFechamento->data_envio_email) {
                return back()->with('info', 'Este relatório já foi enviado em ' . $relatorioFechamento->data_envio_email->format('d/m/Y H:i'));
            }

            // Enviar email para o consultor
            Mail::to($relatorioFechamento->consultor->email)
                ->send(new RelatorioFechamentoMail($relatorioFechamento));

            // Registrar data de envio
            $relatorioFechamento->update([
                'data_envio_email' => now(),
            ]);

            return back()->with('success', 'Relatório enviado por email para o consultor com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao enviar email: ' . $e->getMessage());
        }
    }

    /**
     * Remover relatório
     */
    public function destroy(RelatorioFechamento $relatorioFechamento)
    {
        $this->authorize('delete', $relatorioFechamento);

        $relatorioFechamento->delete();

        return redirect()->route('relatorio-fechamento.index')
            ->with('success', 'Relatório removido com sucesso!');
    }
}
