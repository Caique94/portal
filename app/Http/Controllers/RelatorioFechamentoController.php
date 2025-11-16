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
     * Listar relatórios de fechamento (apenas para Financeiro e Admin)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', RelatorioFechamento::class);

        $query = RelatorioFechamento::with('consultor', 'aprovador')
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

        return view('relatorio-fechamento.index', compact('relatorios', 'consultores'));
    }

    /**
     * Formulário para criar novo relatório
     */
    public function create(Request $request)
    {
        $this->authorize('create', RelatorioFechamento::class);

        $consultores = User::where('papel', 'consultor')->orderBy('name')->get();

        // Se veio com período da URL, usa eles
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        return view('relatorio-fechamento.create', compact('consultores', 'dataInicio', 'dataFim'));
    }

    /**
     * Gerar e salvar novo relatório
     */
    public function store(Request $request)
    {
        $this->authorize('create', RelatorioFechamento::class);

        $validated = $request->validate([
            'consultor_id' => 'required|exists:users,id',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        // Buscar todas as OS do período para o consultor
        $ordemServicos = DB::table('ordem_servico as os')
            ->leftJoin('cliente as c', 'c.id', '=', 'os.cliente_id')
            ->where('os.consultor_id', $validated['consultor_id'])
            ->where('os.status', '<=', 5)
            ->whereBetween('os.created_at', [
                Carbon::parse($validated['data_inicio'])->startOfDay(),
                Carbon::parse($validated['data_fim'])->endOfDay(),
            ])
            ->get([
                'os.id',
                'os.created_at',
                'os.status',
                DB::raw("COALESCE(NULLIF(os.valor_total,'')::numeric,0) as valor_total"),
                DB::raw("COALESCE(c.nome, c.nome_fantasia) as cliente"),
            ]);

        // Calcular totais
        $valorTotal = $ordemServicos->sum('valor_total');
        $totalOs = $ordemServicos->count();

        // Salvar relatório
        $relatorio = RelatorioFechamento::create([
            'consultor_id' => $validated['consultor_id'],
            'data_inicio' => $validated['data_inicio'],
            'data_fim' => $validated['data_fim'],
            'valor_total' => $valorTotal,
            'total_os' => $totalOs,
            'status' => 'rascunho',
        ]);

        return redirect()->route('relatorio-fechamento.show', $relatorio)
            ->with('success', 'Relatório gerado com sucesso!');
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
