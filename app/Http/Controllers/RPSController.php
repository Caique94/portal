<?php

namespace App\Http\Controllers;

use App\Models\RPS;
use App\Models\OrdemServico;
use App\Enums\OrdemServicoStatus;
use App\Services\PermissionService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RPSController extends Controller
{
    /**
     * List all RPS documents
     */
    public function index(Request $request)
    {
        $permissionService = new PermissionService();
        if (!$permissionService->canViewRPS(new RPS())) {
            return response()->json([
                'message' => 'Você não tem permissão para visualizar RPS.'
            ], 403);
        }

        $rps = RPS::with('cliente')
            ->orderByDesc('rps.created_at')
            ->get()
            ->map(function($r) {
                return [
                    'id' => $r->id,
                    'cliente_id' => $r->cliente_id,
                    'cliente_nome' => $r->cliente?->nome,
                    'cliente_codigo' => $r->cliente?->codigo,
                    'numero_rps' => $r->numero_rps,
                    'data_emissao' => $r->data_emissao,
                    'valor_total' => $r->valor_total,
                    'status' => $r->status,
                    'created_at' => $r->created_at,
                ];
            });

        return response()->json([
            'data' => $rps
        ], 200);
    }

    /**
     * Get RPS by ID with related OS
     */
    public function show($id)
    {
        $rps = RPS::with('ordensServico', 'cliente', 'criadoPor')->findOrFail($id);

        $permissionService = new PermissionService();
        if (!$permissionService->canViewRPS($rps)) {
            return response()->json([
                'message' => 'Você não tem permissão para visualizar esta RPS.'
            ], 403);
        }

        return response()->json([
            'data' => $rps
        ], 200);
    }

    /**
     * Create new RPS and link multiple OS to it
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|numeric|exists:cliente,id',
            'numero_rps' => 'required|string|unique:rps,numero_rps',
            'data_emissao' => 'required|date',
            'data_vencimento' => 'nullable|date|after_or_equal:data_emissao',
            'valor_total' => 'required|numeric|min:0.01',
            'valor_servicos' => 'nullable|numeric|min:0',
            'valor_deducoes' => 'nullable|numeric|min:0',
            'valor_impostos' => 'nullable|numeric|min:0',
            'observacoes' => 'nullable|string',
            'ordem_servico_ids' => 'required|array|min:1',
            'ordem_servico_ids.*' => 'numeric|exists:ordem_servico,id',
        ]);

        $permissionService = new PermissionService();
        if (!$permissionService->canCreateRPS()) {
            return response()->json([
                'message' => 'Você não tem permissão para criar RPS.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Verify all OS exist, belong to same client, and are in AGUARDANDO_RPS status
            $ordemServicoIds = $request->input('ordem_servico_ids');
            $ordensServico = OrdemServico::whereIn('id', $ordemServicoIds)
                ->where('cliente_id', $request->input('cliente_id'))
                ->byStatus(OrdemServicoStatus::AGUARDANDO_RPS)
                ->get();

            if ($ordensServico->count() !== count($ordemServicoIds)) {
                return response()->json([
                    'message' => 'Uma ou mais Ordens de Serviço não estão no status "Aguardando RPS" ou pertencem a clientes diferentes.'
                ], 422);
            }

            // Create RPS
            $rps = RPS::create([
                'cliente_id' => $request->input('cliente_id'),
                'numero_rps' => $request->input('numero_rps'),
                'data_emissao' => $request->input('data_emissao'),
                'data_vencimento' => $request->input('data_vencimento'),
                'valor_total' => $request->input('valor_total'),
                'valor_servicos' => $request->input('valor_servicos') ?? 0,
                'valor_deducoes' => $request->input('valor_deducoes') ?? 0,
                'valor_impostos' => $request->input('valor_impostos') ?? 0,
                'status' => 'emitida',
                'observacoes' => $request->input('observacoes'),
                'criado_por' => Auth::id(),
            ]);

            // Link OS to RPS
            if ($rps->linkOrdensServico($ordemServicoIds)) {
                // Record audit for each linked OS
                foreach ($ordemServicoIds as $osId) {
                    $os = OrdemServico::find($osId);
                    $auditService = new AuditService($os);
                    $auditService->recordRpsLinking($rps->id);
                }

                DB::commit();

                return response()->json([
                    'message' => 'RPS criada e Ordens de Serviço vinculadas com sucesso',
                    'data' => $rps->load('ordensServico')
                ], 201);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Erro ao vincular Ordens de Serviço à RPS.'
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar RPS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Link additional OS to existing RPS
     */
    public function linkOrdensServico(Request $request, $id)
    {
        $request->validate([
            'ordem_servico_ids' => 'required|array|min:1',
            'ordem_servico_ids.*' => 'numeric|exists:ordem_servico,id',
        ]);

        $rps = RPS::findOrFail($id);

        $permissionService = new PermissionService();
        if (!$permissionService->canCreateRPS()) {
            return response()->json([
                'message' => 'Você não tem permissão para modificar RPS.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $ordemServicoIds = $request->input('ordem_servico_ids');

            // Verify all OS exist, belong to same client, and are in AGUARDANDO_RPS status
            $ordensServico = OrdemServico::whereIn('id', $ordemServicoIds)
                ->where('cliente_id', $rps->cliente_id)
                ->byStatus(OrdemServicoStatus::AGUARDANDO_RPS)
                ->get();

            if ($ordensServico->count() !== count($ordemServicoIds)) {
                return response()->json([
                    'message' => 'Uma ou mais Ordens de Serviço não estão no status "Aguardando RPS" ou pertencem a cliente diferente.'
                ], 422);
            }

            // Link OS to RPS
            if ($rps->linkOrdensServico($ordemServicoIds)) {
                foreach ($ordemServicoIds as $osId) {
                    $os = OrdemServico::find($osId);
                    $auditService = new AuditService($os);
                    $auditService->recordRpsLinking($rps->id);
                }

                DB::commit();

                return response()->json([
                    'message' => 'Ordens de Serviço vinculadas com sucesso',
                    'data' => $rps->load('ordensServico')
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Erro ao vincular Ordens de Serviço.'
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel RPS and revert linked OS status
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        $rps = RPS::findOrFail($id);

        $permissionService = new PermissionService();
        if (!$permissionService->canCancelRPS($rps)) {
            return response()->json([
                'message' => 'Você não tem permissão para cancelar RPS ou esta RPS não está em status "Emitida".'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Unlink all OS
            if ($rps->unlinkOrdensServico()) {
                // Cancel RPS
                if ($rps->cancel(Auth::id(), $request->input('motivo'))) {
                    // Record audit for each unlinked OS
                    foreach ($rps->ordensServico as $os) {
                        $auditService = new AuditService($os);
                        $auditService->recordRpsCancellation();
                    }

                    DB::commit();

                    return response()->json([
                        'message' => 'RPS cancelada e Ordens de Serviço revertidas para "Aguardando RPS"',
                        'data' => $rps->refresh()
                    ], 200);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Erro ao cancelar RPS.'
                    ], 500);
                }
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Erro ao desvinc ular Ordens de Serviço.'
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao cancelar RPS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revert cancelled RPS
     */
    public function revert(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        $rps = RPS::findOrFail($id);

        $permissionService = new PermissionService();
        if (!$permissionService->canRevertRPS($rps)) {
            return response()->json([
                'message' => 'Você não tem permissão para reverter RPS ou esta RPS não está em status "Cancelada".'
            ], 403);
        }

        try {
            // Revert RPS status
            if ($rps->revert(Auth::id(), $request->input('motivo'))) {
                return response()->json([
                    'message' => 'RPS revertida com sucesso',
                    'data' => $rps->refresh()
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Erro ao reverter RPS.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao reverter RPS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List RPS by client
     */
    public function listByClient($clienteId)
    {
        $permissionService = new PermissionService();
        if (!$permissionService->canViewRPS(new RPS())) {
            return response()->json([
                'message' => 'Você não tem permissão para visualizar RPS.'
            ], 403);
        }

        $rps = RPS::byCliente($clienteId)
            ->with('ordensServico', 'cliente')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $rps->toArray()
        ], 200);
    }

    /**
     * List OS ready for RPS linking (AGUARDANDO_RPS status)
     */
    public function listOrdensReadyForRps($clienteId)
    {
        $ordensServico = OrdemServico::where('cliente_id', $clienteId)
            ->byStatus(OrdemServicoStatus::AGUARDANDO_RPS)
            ->with('consultor', 'cliente')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $ordensServico->toArray()
        ], 200);
    }

    /**
     * Get RPS audit trail
     */
    public function getAuditTrail($id)
    {
        $rps = RPS::findOrFail($id);

        $permissionService = new PermissionService();
        if (!$permissionService->canViewRPS($rps)) {
            return response()->json([
                'message' => 'Você não tem permissão para visualizar histórico desta RPS.'
            ], 403);
        }

        $audits = $rps->audits()
            ->with('user')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($audit) => [
                'timestamp' => $audit->created_at,
                'user' => $audit->user?->name ?? 'Sistema',
                'event' => $audit->event,
                'description' => $audit->description,
            ]);

        return response()->json([
            'data' => $audits
        ], 200);
    }

    /**
     * Export RPS as PDF (stub for future implementation)
     */
    public function exportPdf($id)
    {
        $rps = RPS::findOrFail($id);

        $permissionService = new PermissionService();
        if (!$permissionService->canViewRPS($rps)) {
            return response()->json([
                'message' => 'Você não tem permissão para exportar esta RPS.'
            ], 403);
        }

        // TODO: Implement PDF export logic
        return response()->json([
            'message' => 'Exportação em PDF será implementada em breve.'
        ], 501);
    }
}
