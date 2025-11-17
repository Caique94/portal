<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\User;
use App\Models\Cliente;
use App\Models\RelatorioFechamento;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminHomeController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Verificar se é admin
        if (Auth::user()->papel !== 'admin') {
            return redirect('/');
        }

        // KPIs
        $totalOS = OrdemServico::count();
        $totalConsultores = User::where('papel', 'consultor')->where('ativo', true)->count();
        $totalClientes = Cliente::count();
        $totalFaturamento = RelatorioFechamento::where('status', 'aprovado')
            ->sum('valor_total');

        // OS por status
        // Note: Status 6 and 7 display as 5 (Faturada) for consultores
        // So we count all of them together under "Faturada"
        $osStatus = [
            'Aberta' => OrdemServico::where(function($q) {
                $q->where('status', 1)->orWhere('status', 'em_aberto');
            })->count(),
            'Aguardando Aprovação' => OrdemServico::where(function($q) {
                $q->where('status', 2)->orWhere('status', 'aguardando_aprovacao');
            })->count(),
            'Contestada' => OrdemServico::where(function($q) {
                $q->where('status', 3)->orWhere('status', 'contestar');
            })->count(),
            'Aguardando Faturamento' => OrdemServico::where(function($q) {
                $q->where('status', 4)->orWhere('status', 'aprovado');
            })->count(),
            // Faturada includes status 5, 6, 7 (6 and 7 display as 5 for consultores)
            'Faturada' => OrdemServico::where(function($q) {
                $q->where('status', 5)->orWhere('status', 'faturado')
                  ->orWhere('status', 6)->orWhere('status', 'aguardando_rps')
                  ->orWhere('status', 7)->orWhere('status', 'rps_emitida');
            })->count(),
        ];

        // Relatórios pendentes de aprovação
        $relatoriosPendentes = RelatorioFechamento::where('status', 'enviado')
            ->with('consultor')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Ordens de serviço abertas ou enviadas para aprovação (últimas 10)
        $osAbertas = OrdemServico::whereIn('status', [0, 1])
            ->with(['consultor', 'cliente'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Performance por consultor (últimos 30 dias)
        $consultoresPerformance = User::where('papel', 'consultor')
            ->where('ativo', true)
            ->withCount([
                'ordemServicos' => function ($query) {
                    $query->whereDate('created_at', '>=', Carbon::now()->subDays(30));
                }
            ])
            ->orderByDesc('ordem_servicos_count')
            ->limit(5)
            ->get();

        // Últimos relatórios aprovados
        $ultimosRelatorios = RelatorioFechamento::where('status', 'aprovado')
            ->with('consultor')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Clientes sem pedidos nos últimos 30 dias
        $clientesInativos = Cliente::whereDoesntHave('ordemServicos', function ($query) {
            $query->whereDate('created_at', '>=', Carbon::now()->subDays(30));
        })
        ->limit(5)
        ->get();

        return view('admin.home', compact(
            'totalOS',
            'totalConsultores',
            'totalClientes',
            'totalFaturamento',
            'osStatus',
            'relatoriosPendentes',
            'osAbertas',
            'consultoresPerformance',
            'ultimosRelatorios',
            'clientesInativos'
        ));
    }
}
