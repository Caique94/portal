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
     * Nota: Não filtra por consultor, pois o foco é nos dados do cliente
     */
    public function indexCliente(Request $request)
    {
        $this->authorize('viewAny', RelatorioFechamento::class);

        $query = RelatorioFechamento::with(['cliente', 'consultor', 'aprovador'])
            ->where('tipo', 'cliente')
            ->orderByDesc('id');

        // Filtros (sem filtro de consultor - foco no cliente)
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

        return view('relatorio-fechamento.index-cliente', compact('relatorios'));
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
     * Dashboard de fechamento CLIENTE com métricas e indicadores
     */
    public function dashboardCliente(Request $request)
    {
        $this->authorize('viewAny', RelatorioFechamento::class);

        // Métricas gerais
        $totalFechamentos = RelatorioFechamento::where('tipo', 'cliente')->count();
        $totalAprovados = RelatorioFechamento::where('tipo', 'cliente')->where('status', 'aprovado')->count();
        $totalPendentes = RelatorioFechamento::where('tipo', 'cliente')->where('status', 'enviado')->count();
        $valorTotalMes = RelatorioFechamento::where('tipo', 'cliente')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('valor_total');

        // Últimos fechamentos
        $ultimosFechamentos = RelatorioFechamento::where('tipo', 'cliente')
            ->with(['cliente', 'consultor'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Fechamentos por status
        $porStatus = RelatorioFechamento::where('tipo', 'cliente')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        return view('relatorio-fechamento.dashboard-cliente', compact(
            'totalFechamentos',
            'totalAprovados',
            'totalPendentes',
            'valorTotalMes',
            'ultimosFechamentos',
            'porStatus'
        ));
    }

    /**
     * Dashboard de fechamento CONSULTOR com métricas e indicadores
     */
    public function dashboardConsultor(Request $request)
    {
        $this->authorize('viewAny', RelatorioFechamento::class);

        // Métricas gerais
        $totalFechamentos = RelatorioFechamento::where('tipo', 'consultor')->count();
        $totalAprovados = RelatorioFechamento::where('tipo', 'consultor')->where('status', 'aprovado')->count();
        $totalPendentes = RelatorioFechamento::where('tipo', 'consultor')->where('status', 'enviado')->count();
        $valorTotalMes = RelatorioFechamento::where('tipo', 'consultor')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('valor_total');

        // Últimos fechamentos
        $ultimosFechamentos = RelatorioFechamento::where('tipo', 'consultor')
            ->with('consultor')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Fechamentos por consultor (top 5)
        $porConsultor = RelatorioFechamento::where('tipo', 'consultor')
            ->selectRaw('consultor_id, count(*) as total, sum(valor_total) as valor')
            ->groupBy('consultor_id')
            ->orderByDesc('valor')
            ->limit(5)
            ->with('consultor')
            ->get();

        return view('relatorio-fechamento.dashboard-consultor', compact(
            'totalFechamentos',
            'totalAprovados',
            'totalPendentes',
            'valorTotalMes',
            'ultimosFechamentos',
            'porConsultor'
        ));
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

        // Log temporário para debug
        \Log::info('RelatorioFechamento::store - Request data:', [
            'all' => $request->all(),
            'consultor_id_raw' => $request->input('consultor_id'),
            'tipo' => $request->input('tipo'),
        ]);

        $validated = $request->validate([
            'consultor_id' => $request->tipo === 'cliente' ? 'nullable' : 'required',
            'cliente_id' => 'nullable',
            'tipo' => 'required|in:consultor,cliente',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        // Garantir que strings vazias sejam convertidas para null
        if (isset($validated['consultor_id']) && $validated['consultor_id'] === '') {
            $validated['consultor_id'] = null;
        }
        if (isset($validated['cliente_id']) && $validated['cliente_id'] === '') {
            $validated['cliente_id'] = null;
        }

        // Log após validação
        \Log::info('RelatorioFechamento::store - After validation:', [
            'validated' => $validated,
            'consultor_id_final' => $validated['consultor_id'] ?? 'NOT_SET',
        ]);

        // Validar consultor_id (pode ser 'todos' ou um ID válido) - apenas para consultor
        if (!empty($validated['consultor_id']) && $validated['consultor_id'] !== 'todos') {
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
                if (!empty($validated['consultor_id']) && $validated['consultor_id'] === 'todos') {
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
                            'cliente_id' => $cliente->id,
                            'tipo' => $validated['tipo'],
                            'data_inicio' => $validated['data_inicio'],
                            'data_fim' => $validated['data_fim'],
                            'valor_total' => $valorTotal,
                            'total_os' => $totalOs,
                            'status' => 'rascunho',
                        ]);

                        $relatoriosGerados[] = $relatorio;
                    }
                } elseif (!empty($validated['consultor_id'])) {
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
                        'cliente_id' => $cliente->id,
                        'tipo' => $validated['tipo'],
                        'data_inicio' => $validated['data_inicio'],
                        'data_fim' => $validated['data_fim'],
                        'valor_total' => $valorTotal,
                        'total_os' => $totalOs,
                        'status' => 'rascunho',
                    ]);

                    $relatoriosGerados[] = $relatorio;
                } else {
                    // Fechamento de cliente sem consultor específico (tipo=cliente)
                    $query = \App\Models\OrdemServico::with(['cliente', 'produtoTabela.produto'])
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

                    $valorTotal = $this->calcularValorCliente($ordemServicos, null);
                    $totalOs = $ordemServicos->count();

                    $relatorio = RelatorioFechamento::create([
                        'consultor_id' => null,
                        'cliente_id' => $cliente->id,
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

        // Se for "todos" consultores (apenas para tipo consultor), gerar relatórios individuais
        if (!empty($validated['consultor_id']) && $validated['consultor_id'] === 'todos') {
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
                    'cliente_id' => $validated['cliente_id'] ?? null,
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

        // Caso específico: um consultor ou fechamento de cliente
        $consultor = null;
        if (!empty($validated['consultor_id'])) {
            $consultor = User::findOrFail($validated['consultor_id']);
        }

        // Buscar todas as OS do período
        $query = \App\Models\OrdemServico::with(['cliente', 'produtoTabela.produto'])
            ->where('status', '<=', 5)
            ->whereBetween('created_at', [
                Carbon::parse($validated['data_inicio'])->startOfDay(),
                Carbon::parse($validated['data_fim'])->endOfDay(),
            ]);

        // Para fechamento de consultor, filtrar por consultor
        if ($validated['tipo'] === 'consultor' && !empty($validated['consultor_id'])) {
            $query->where('consultor_id', $validated['consultor_id']);
        }

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
            'consultor_id' => $validated['consultor_id'] ?? null,
            'cliente_id' => $validated['cliente_id'] ?? null,
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
     * Converter valor para float de forma segura, tratando vírgula e ponto
     */
    private function toFloat($value)
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        // Se já é numérico, retorna como float
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Se é string, remove espaços e trata vírgula/ponto
        $value = trim((string) $value);

        // Remove separadores de milhar (ponto no formato brasileiro, vírgula no americano)
        // e mantém apenas o separador decimal
        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            // Tem ambos: determinar qual é o separador decimal (último a aparecer)
            $posVirgula = strrpos($value, ',');
            $posPonto = strrpos($value, '.');

            if ($posVirgula > $posPonto) {
                // Vírgula é o decimal (formato BR: 1.234,56)
                $value = str_replace('.', '', $value); // Remove pontos de milhar
                $value = str_replace(',', '.', $value); // Troca vírgula por ponto
            } else {
                // Ponto é o decimal (formato US: 1,234.56)
                $value = str_replace(',', '', $value); // Remove vírgulas de milhar
            }
        } else if (strpos($value, ',') !== false) {
            // Só tem vírgula, assumir que é decimal (formato BR: 1234,56)
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    /**
     * Calcular valor total baseado nos valores do consultor
     */
    private function calcularValorConsultor($ordemServicos, $consultor)
    {
        $total = 0;

        foreach ($ordemServicos as $os) {
            // Valor Serviço = horas × valor_hora_consultor
            $horas = $this->toFloat($os->horas_trabalhadas ?? 0);
            $valorHoraConsultor = $this->toFloat($consultor->valor_hora ?? 0);
            $valorServico = $horas * $valorHoraConsultor;

            // Despesas
            $despesas = $this->toFloat($os->valor_despesa ?? 0);

            // KM e Deslocamento (apenas se presencial)
            $valorKM = 0;
            $valorDeslocamento = 0;

            if ($os->is_presencial) {
                $km = $this->toFloat($os->km ?? 0);
                $valorKmConsultor = $this->toFloat($consultor->valor_km ?? 0);
                $valorKM = $km * $valorKmConsultor;

                $horasDeslocamento = $this->toFloat($os->deslocamento ?? 0);
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
            // Totalizador Administrativo:
            // Usa o valor_total já calculado e salvo na OS
            $valorOS = $this->toFloat($os->valor_total ?? 0);
            $total += $valorOS;
        }

        return $total;
    }

    /**
     * Visualizar relatório
     */
    public function show(RelatorioFechamento $relatorioFechamento)
    {
        $this->authorize('view', $relatorioFechamento);

        // Carregar relacionamentos se ainda não estiverem loaded
        if (!$relatorioFechamento->relationLoaded('cliente')) {
            $relatorioFechamento->load('cliente');
        }
        if (!$relatorioFechamento->relationLoaded('consultor')) {
            $relatorioFechamento->load('consultor');
        }

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

        // Usar template específico baseado no tipo
        $viewTemplate = $relatorioFechamento->tipo === 'cliente'
            ? 'relatorio-fechamento.pdf-cliente'
            : 'relatorio-fechamento.pdf-consultor';

        return Pdf::loadView($viewTemplate, compact('relatorioFechamento', 'ordemServicos', 'consultor'))
            ->setPaper('a4', 'portrait')
            ->download('relatorio_fechamento_' . $relatorioFechamento->tipo . '_' . $relatorioFechamento->id . '.pdf');
    }

    /**
     * Aprovar relatório
     * Apenas ADMIN pode aprovar fechamentos para envio
     */
    public function aprovar(RelatorioFechamento $relatorioFechamento)
    {
        $this->authorize('aprovar', $relatorioFechamento);

        $relatorioFechamento->update([
            'status' => 'aprovado',
            'aprovado_por' => auth()->id(),
            'data_aprovacao' => now(),
        ]);

        return back()->with('success', 'Relatório aprovado com sucesso!');
    }

    /**
     * Rejeitar relatório
     * Apenas ADMIN pode rejeitar fechamentos
     */
    public function rejeitar(RelatorioFechamento $relatorioFechamento, Request $request)
    {
        $this->authorize('rejeitar', $relatorioFechamento);

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

        $tipo = $relatorioFechamento->tipo;
        $relatorioFechamento->delete();

        $route = $tipo === 'cliente'
            ? 'relatorio-fechamento-cliente.index'
            : 'relatorio-fechamento-consultor.index';

        return redirect()->route($route)
            ->with('success', 'Relatório removido com sucesso!');
    }
}
