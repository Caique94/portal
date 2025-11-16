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
        $osStatus = [
            'Aberta' => OrdemServico::where('status', 0)->count(),
            'Enviada para Aprovação' => OrdemServico::where('status', 1)->count(),
            'Finalizada' => OrdemServico::where('status', 2)->count(),
            'Contestada' => OrdemServico::where('status', 3)->count(),
            'Faturada' => OrdemServico::where('status', 4)->count(),
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
