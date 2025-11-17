<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\ReciboProvisorio;
use App\Models\RPS;
use App\Enums\OrdemServicoStatus;
use App\Services\StateMachine;
use App\Services\OSValidation;
use App\Services\AuditService;
use App\Services\PermissionService;
use App\Events\OSApproved;
use App\Events\OSRejected;
use App\Events\OSBilled;
use App\Events\RPSEmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdemServicoController extends Controller
{

    public function view()
    {
        $user = Auth::user();
        return view('ordem-servico', compact('user'));
    }

    /**
     * Store (create or update) an OS
     */
    public function store(Request $request)
    {
        $id = $request->input('txtOrdemId');

        $validatedData = $request->validate([
            'txtOrdemConsultorId'           => 'required|numeric|min:0',
            'slcOrdemClienteId'             => 'required|numeric|min:0',
            'txtOrdemDataEmissao'           => 'required|string|max:255',
            'slcOrdemTipoDespesa'           => 'max:255',
            'txtOrdemDespesas'              => 'max:255',
            'txtOrdemDespesasDetalhamento'  => 'max:255',
            'slcProdutoOrdemId'             => 'required|numeric|min:0',
            'txtProdutoOrdemHoraInicio'     => 'max:255',
            'txtProdutoOrdemHoraFinal'      => 'max:255',
            'txtProdutoOrdemHoraDesconto'   => 'max:255',
            'txtProdutoOrdemQtdeTotal'      => 'max:255',
            'txtProdutoOrdemDetalhamento'   => 'max:65535',
            'txtOrdemAssunto'               => 'max:255',
            'projeto_id'                    => 'nullable|numeric|min:0',
            'txtOrdemNrAtendimento'         => 'max:255',
            'txtOrdemPrecoProduto'          => 'max:255',
            'txtOrdemValorTotal'            => 'max:255'
        ]);

        $ordem = OrdemServico::find($id);
        $isUpdate = $ordem !== null;

        // Check permissions
        if ($isUpdate) {
            $permissionService = new PermissionService();
            if (!$permissionService->canEditOS($ordem)) {
                return response()->json([
                    'message' => 'Você não tem permissão para editar esta Ordem de Serviço.'
                ], 403);
            }
        } else {
            $permissionService = new PermissionService();
            if (!$permissionService->canCreateOS()) {
                return response()->json([
                    'message' => 'Você não tem permissão para criar Ordens de Serviço.'
                ], 403);
            }
        }

        // Buscar consultor e cliente para calcular o deslocamento
        $consultor = \App\Models\User::find($validatedData['txtOrdemConsultorId']);
        $cliente = \App\Models\Cliente::find($validatedData['slcOrdemClienteId']);

        // Calcular valor do deslocamento: km do cliente * valor_km do consultor
        $valor_deslocamento = 0;
        if ($cliente && $cliente->km && $consultor && $consultor->valor_km) {
            $valor_deslocamento = floatval($cliente->km) * floatval($consultor->valor_km);
        }

        // Atualizar o campo deslocamento do cliente
        if ($cliente && $valor_deslocamento > 0) {
            $cliente->deslocamento = $valor_deslocamento;
            $cliente->save();
        }

        $mappedData = [
            'consultor_id'          => $validatedData['txtOrdemConsultorId'],
            'cliente_id'            => $validatedData['slcOrdemClienteId'],
            'data_emissao'          => $validatedData['txtOrdemDataEmissao'],
            'tipo_despesa'          => $validatedData['slcOrdemTipoDespesa'],
            'valor_despesa'         => $validatedData['txtOrdemDespesas'],
            'detalhamento_despesa'  => isset($validatedData['txtOrdemDespesasDetalhamento']) ? $validatedData['txtOrdemDespesasDetalhamento'] : '',
            'status'                => 1, // Legacy: 1 = Em Aberto
            'produto_tabela_id'     => $validatedData['slcProdutoOrdemId'],
            'hora_inicio'           => $validatedData['txtProdutoOrdemHoraInicio'],
            'hora_final'            => $validatedData['txtProdutoOrdemHoraFinal'],
            'hora_desconto'         => $validatedData['txtProdutoOrdemHoraDesconto'],
            'qtde_total'            => isset($validatedData['txtProdutoOrdemQtdeTotal']) ? $validatedData['txtProdutoOrdemQtdeTotal'] : '',
            'detalhamento'          => $validatedData['txtProdutoOrdemDetalhamento'],
            'assunto'               => $validatedData['txtOrdemAssunto'],
            'projeto_id'            => isset($validatedData['projeto_id']) ? $validatedData['projeto_id'] : null,
            'nr_atendimento'        => $validatedData['txtOrdemNrAtendimento'],
            'preco_produto'         => $validatedData['txtOrdemPrecoProduto'],
            'valor_total'           => $validatedData['txtOrdemValorTotal']
        ];

        if ($ordem) {
            // Record old values for audit
            $oldValues = $ordem->getAttributes();

            // Update existing
            $ordem->update($mappedData);

            // Record audit
            $auditService = new AuditService($ordem);
            $auditService->recordUpdate($oldValues, $mappedData);

            return response()->json([
                'message' => 'Ordem de Serviço atualizada com sucesso',
                'data' => $ordem->refresh(),
            ], 200);
        } else {
            // Create new
            $ordem = OrdemServico::create($mappedData);

            // Record audit
            $auditService = new AuditService($ordem);
            $auditService->recordCreation($mappedData);

            return response()->json([
                'message' => 'Ordem de Serviço criada com sucesso',
                'data' => $ordem,
            ], 201);
        }
    }

    /**
     * List OS based on user role
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        $papel = $user->papel;
        $consultor_id = $user->id;
        $data = null;

        switch ($papel) {
            case 'consultor':
                // Consultores veem apenas suas próprias OS
                $data = OrdemServico::join('cliente', 'ordem_servico.cliente_id', '=', 'cliente.id')
                    ->join('users', 'ordem_servico.consultor_id', '=', 'users.id')
                    ->select('ordem_servico.*', 'cliente.codigo as cliente_codigo', 'cliente.nome as cliente_nome', 'users.name as consultor_nome')
                    ->where('ordem_servico.consultor_id', $consultor_id)
                    ->orderByDesc('ordem_servico.created_at')
                    ->get();
                break;
            case 'financeiro':
                // Financeiro vê todas as OS em status de faturamento em diante
                $data = OrdemServico::join('cliente', 'ordem_servico.cliente_id', '=', 'cliente.id')
                    ->join('users', 'ordem_servico.consultor_id', '=', 'users.id')
                    ->select('ordem_servico.*', 'cliente.codigo as cliente_codigo', 'cliente.nome as cliente_nome', 'users.name as consultor_nome')
                    ->whereIn('ordem_servico.status', [4, 5, 6, 7]) // Aguardando Faturamento em diante
                    ->orderByDesc('ordem_servico.created_at')
                    ->get();
                break;
            case 'admin':
                // Admin vê todas as OS
                $data = OrdemServico::join('cliente','ordem_servico.cliente_id', '=', 'cliente.id')
                    ->join('users', 'ordem_servico.consultor_id', '=', 'users.id')
                    ->select('ordem_servico.*', 'cliente.codigo as cliente_codigo', 'cliente.nome as cliente_nome', 'users.name as consultor_nome')
                    ->orderByDesc('ordem_servico.created_at')
                    ->get();
                break;
        }

        // Return in DataTables format with user role info
        return response()->json([
            'data' => $data ?? [],
            'user_role' => $papel
        ]);
    }

    /**
     * Request approval for OS (EM_ABERTO -> AGUARDANDO_APROVACAO)
     */
    public function requestApproval(Request $request, $id)
    {
        $ordem = OrdemServico::findOrFail($id);

        // Check permissions
        $permissionService = new PermissionService();
        if (!$permissionService->canRequestApproval($ordem)) {
            return response()->json([
                'message' => 'Você não tem permissão para solicitar aprovação desta OS.'
            ], 403);
        }

        // Validate transition
        $stateMachine = new StateMachine($ordem);
        if (!$stateMachine->canTransition(OrdemServicoStatus::AGUARDANDO_APROVACAO)) {
            return response()->json([
                'message' => 'Transição de status não permitida.',
                'current_status' => $ordem->getStatus()->label(),
                'valid_transitions' => array_map(fn($s) => $s->label(), $ordem->getStatus()->validTransitions())
            ], 422);
        }

        // Perform transition
        $stateMachine->transition(OrdemServicoStatus::AGUARDANDO_APROVACAO);

        // Record audit
        $auditService = new AuditService($ordem);
        $auditService->recordStatusTransition('em_aberto', 'aguardando_aprovacao');

        return response()->json([
            'message' => 'Ordem de Serviço enviada para aprovação',
            'data' => $ordem->refresh()
        ], 200);
    }

    /**
     * Approve OS (AGUARDANDO_APROVACAO -> APROVADO)
     */
    public function approve(Request $request, $id)
    {
        $ordem = OrdemServico::findOrFail($id);

        // Check permissions
        $permissionService = new PermissionService();
        if (!$permissionService->canApproveOS($ordem)) {
            return response()->json([
                'message' => 'Você não tem permissão para aprovar ordens de serviço.'
            ], 403);
        }

        // Validate transition
        $stateMachine = new StateMachine($ordem);
        if (!$stateMachine->canTransition(OrdemServicoStatus::APROVADO)) {
            return response()->json([
                'message' => 'Transição de status não permitida.'
            ], 422);
        }

        // Update status and approval fields
        $ordem->status = 4; // APROVADO

        if ($ordem->approval_status !== 'approved') {
            $ordem->approval_status = 'approved';
            $ordem->approved_at = now();
            $ordem->approved_by = Auth::id();
        }

        $ordem->save();

        // Record audit
        $auditService = new AuditService($ordem);
        $auditService->recordApproval();

        // Dispatch OSApproved event to trigger PDF generation and email sending
        // Only dispatch if approval_status was changed (wasChanged checks before save)
        if ($ordem->wasChanged('approval_status')) {
            OSApproved::dispatch($ordem);
        }

        return response()->json([
            'message' => 'Ordem de Serviço aprovada com sucesso',
            'data' => $ordem
        ], 200);
    }


    /**
     * Contest OS (ANY -> CONTESTAR)
     */
    public function contest(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric',
            'motivo' => 'required|string|max:500',
        ]);

        $id = $request->input('id');
        $motivo = $request->input('motivo');
        $ordem = OrdemServico::findOrFail($id);

        // Check permissions
        $permissionService = new PermissionService();
        if (!$permissionService->canContestOS($ordem)) {
            return response()->json([
                'message' => 'Você não tem permissão para contestar ordens de serviço.'
            ], 403);
        }

        // Validate transition
        $stateMachine = new StateMachine($ordem);
        if (!$stateMachine->canTransition(OrdemServicoStatus::CONTESTAR)) {
            return response()->json([
                'message' => 'Transição de status não permitida.'
            ], 422);
        }

        // Perform transition
        $stateMachine->transition(
            OrdemServicoStatus::CONTESTAR,
            ['motivo_contestacao' => $motivo],
            Auth::id()
        );

        // Record audit
        $auditService = new AuditService($ordem);
        $auditService->recordContestacao($motivo);

        // Dispatch OSRejected event to send notification
        OSRejected::dispatch($ordem->refresh(), $motivo);

        return response()->json([
            'message' => 'Ordem de Serviço contestada com sucesso',
            'data' => $ordem->refresh()
        ], 200);
    }

    /**
     * Bill OS (APROVADO -> FATURADO)
     */
    public function bill(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric',
        ]);

        $id = $request->input('id');
        $ordem = OrdemServico::findOrFail($id);

        // Check permissions
        $permissionService = new PermissionService();
        if (!$permissionService->canBillOS($ordem)) {
            return response()->json([
                'message' => 'Você não tem permissão para faturar ordens de serviço.'
            ], 403);
        }

        // Validate billing requirements
        $validationService = new OSValidation($ordem);
        $errors = $validationService->validateForBilling();

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Não foi possível faturar a OS. Verifique os erros:',
                'errors' => $errors
            ], 422);
        }

        // Check concurrency - pessimistic locking
        try {
            DB::beginTransaction();

            // Lock the row for update
            $ordem = OrdemServico::lockForUpdate()->find($id);

            // Validate transition again (safety check)
            $stateMachine = new StateMachine($ordem);
            if (!$stateMachine->canTransition(OrdemServicoStatus::FATURADO)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Transição de status não permitida.'
                ], 422);
            }

            // Perform transition
            $stateMachine->transition(OrdemServicoStatus::FATURADO, [], Auth::id());

            // Record audit
            $auditService = new AuditService($ordem);
            $auditService->recordBilling([
                'valor_total' => $ordem->valor_total,
                'faturado_em' => now(),
                'faturado_por' => Auth::id(),
            ]);

            DB::commit();

            // Dispatch OSBilled event to send notification
            OSBilled::dispatch($ordem->refresh());

            return response()->json([
                'message' => 'Ordem de Serviço faturada com sucesso',
                'data' => $ordem->refresh()
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao faturar a Ordem de Serviço: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List OS ready for RPS linking
     */
    public function listForRps(Request $request)
    {
        $data = OrdemServico::join('cliente', 'ordem_servico.cliente_id', '=', 'cliente.id')
            ->join('users', 'ordem_servico.consultor_id', '=', 'users.id')
            ->select('ordem_servico.*', 'cliente.codigo as cliente_codigo', 'cliente.nome as cliente_nome', 'users.name as consultor_nome')
            ->byStatus(OrdemServicoStatus::AGUARDANDO_RPS)
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Delete OS (only if EM_ABERTO)
     */
    public function destroy($id)
    {
        $ordem = OrdemServico::findOrFail($id);

        // Check permissions
        $permissionService = new PermissionService();
        if (!$permissionService->canDeleteOS($ordem)) {
            return response()->json([
                'message' => 'Você não tem permissão para deletar esta Ordem de Serviço ou ela não está em status "Em Aberto".'
            ], 403);
        }

        // Record audit before deletion
        $auditService = new AuditService($ordem);
        $auditService->recordDeletion('Deletado pelo usuário');

        // Soft delete
        $ordem->delete();

        return response()->json([
            'message' => 'Ordem de Serviço deletada com sucesso!'
        ], 200);
    }

    /**
     * Get audit history for OS
     */
    public function getAuditTrail($id)
    {
        $ordem = OrdemServico::findOrFail($id);

        // Check permissions
        $permissionService = new PermissionService();
        if (!$permissionService->canViewAudit($ordem)) {
            return response()->json([
                'message' => 'Você não tem permissão para visualizar o histórico desta OS.'
            ], 403);
        }

        $auditService = new AuditService($ordem);
        $timeline = $auditService->getTimeline();

        return response()->json([
            'data' => $timeline
        ], 200);
    }

    /**
     * Get permission summary for current user
     */
    public function getPermissions($id)
    {
        $ordem = OrdemServico::findOrFail($id);
        $permissionService = new PermissionService();

        return response()->json([
            'data' => $permissionService->getPermissionSummary($ordem)
        ], 200);
    }

    // Legacy methods for backward compatibility
    public function toggle_status(Request $request, string $id, int $status)
    {
        $ordem = OrdemServico::find($id);
        $ordem->status = $status;

        if ($status == 4 && $ordem->approval_status !== 'approved') {
            $ordem->approval_status = 'approved';
            $ordem->approved_at = now();
            $ordem->approved_by = Auth::id();
        }

        $ordem->save();

        if ($status == 4 && $ordem->wasChanged('approval_status')) {
            event(new \App\Events\OSApproved($ordem));
        }

        return response()->json([
            'message'   => 'Ordem de Serviço atualizada com sucesso',
            'data'      => $ordem
        ], 201);
    }

    public function list_invoice()
    {
        $user = Auth::user();
        $papel = $user->papel;

        $query = OrdemServico::join('cliente','ordem_servico.cliente_id', '=', 'cliente.id')
            ->join('users', 'ordem_servico.consultor_id', '=', 'users.id')
            ->select('ordem_servico.*', 'cliente.codigo as cliente_codigo', 'cliente.nome as cliente_nome', 'users.name as consultor_nome')
            ->whereIn('ordem_servico.status', [4, 5, 6, 7]); // Aguardando Faturamento, Faturada, Aguardando RPS, RPS Emitida

        // Filtrar por papel
        switch ($papel) {
            case 'consultor':
                // Consultores veem apenas suas próprias OS em status de faturamento
                $query->where('ordem_servico.consultor_id', $user->id);
                break;
            case 'financeiro':
                // Financeiro vê todas as OS em status de faturamento (sem filtro adicional)
                break;
            case 'admin':
            default:
                // Admin vê todas as OS (sem filtro adicional)
                break;
        }

        $data = $query->orderByDesc('ordem_servico.created_at')->get();

        return response()->json([
            'data' => $data,
            'user_role' => $papel
        ]);
    }

    public function invoice_orders(Request $request)
    {
        $ordens = $request->input('id_list');
        OrdemServico::whereIn('id', $ordens)->update(['status' => 6]);

        return response()->json([
            'message'   => 'Ordens de Serviço atualizadas com sucesso',
            'data'      => $ordens
        ], 201);
    }

    public function rps_orders(Request $request)
    {
        $cliente_id     = $request->input('txtEmissaoRPSClienteId');
        $ordens_id      = $request->input('txtEmissaoRPSOrdens');
        $numero         = $request->input('txtEmissaoRPSNumero');
        $serie          = $request->input('txtEmissaoRPSSerie');
        $data_emissao   = $request->input('txtEmissaoRPSEmissao');
        $cond_pagto_id  = $request->input('slcEmissaoRPSCondPagto');
        $valor          = $request->input('txtEmissaoRPSValor');

        $data_primeira_parcela = $request->input('data_primeira_parcela');
        $intervalo_dias = $request->input('intervalo_dias');
        $total_parcelas = $request->input('total_parcelas');
        $consolidada = $request->input('consolidada', 0);

        $condicaoPagamento = \App\Models\CondicaoPagamento::find($cond_pagto_id);
        $cond_pagto_descricao = $condicaoPagamento ? $condicaoPagamento->descricao : $cond_pagto_id;

        $ordemArr = explode(',', $ordens_id);

        $mappedData = [
            'cliente_id'    => $cliente_id,
            'numero'        => $numero,
            'serie'         => $serie,
            'data_emissao'  => $data_emissao,
            'cond_pagto'    => $cond_pagto_descricao,
            'valor'         => $valor,
            'consolidada'   => ($consolidada == 1 || count($ordemArr) > 1) ? true : false,
            'ordens_consolidadas' => json_encode($ordemArr)
        ];

        $recibo_provisorio = ReciboProvisorio::create($mappedData);

        $ordens = OrdemServico::whereIn('id', $ordemArr)->update([
            'nr_rps' => $recibo_provisorio->id,
            'status' => 5
        ]);

        if ($condicaoPagamento && $condicaoPagamento->numero_parcelas > 1) {
            $totalParcelas = $total_parcelas ?: $condicaoPagamento->numero_parcelas;
            $interval = $intervalo_dias ?: $condicaoPagamento->intervalo_dias;

            $this->criarParcelasRPS(
                $recibo_provisorio->id,
                $totalParcelas,
                $valor,
                $data_primeira_parcela,
                $interval
            );
        }

        return response()->json([
            'message'   => 'RPS emitida com sucesso! Parcelas criadas automaticamente.',
            'data'      => $ordens
        ], 201);
    }

    private function criarParcelasRPS($reciboId, $totalParcelas, $valorTotal, $dataPrimeira, $intervaloDias)
    {
        $valorParcela = $valorTotal / $totalParcelas;
        $dataPrimeira = \Carbon\Carbon::parse($dataPrimeira);

        for ($i = 1; $i <= $totalParcelas; $i++) {
            $dataVencimento = $dataPrimeira->copy()->addDays(($i - 1) * $intervaloDias);

            \App\Models\PagamentoParcela::create([
                'recibo_provisorio_id' => $reciboId,
                'numero_parcela' => $i,
                'total_parcelas' => $totalParcelas,
                'valor' => round($valorParcela, 2),
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'status' => 'pendente'
            ]);
        }
    }

}
