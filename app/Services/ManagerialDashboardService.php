<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ManagerialDashboardService
{
    /**
     * Get all dashboard data (KPIs, charts, relatórios)
     */
    public function getAllDashboardData(): array
    {
        return [
            'kpis' => $this->getKPIs(),
            'charts' => $this->getCharts(),
            'recent_orders' => $this->getRecentOrders(),
            'reports' => $this->getReports(),
        ];
    }

    // ========== KPIs ==========
    public function getKPIs(): array
    {
        return [
            'total_orders_month' => $this->getTotalOrdersThisMonth(),
            'total_revenue' => $this->getTotalRevenue(),
            'average_per_client' => $this->getAverageRevenuePerClient(),
            'total_clients' => $this->getTotalClients(),
            'total_consultants' => $this->getTotalConsultants(),
            'orders_pending' => $this->getOrdersPending(),
            'orders_billed' => $this->getOrdersBilled(),
        ];
    }

    public function getTotalOrdersThisMonth(): int
    {
        return OrdemServico::where('created_at', '>=', now()->subDays(30))->count();
    }

    public function getTotalRevenue(): float
    {
        $orders = OrdemServico::whereIn('status', [5, 6, 7])->pluck('valor_total');
        return (float) $orders->sum(fn($value) => (float) $value);
    }

    public function getAverageRevenuePerClient(): float
    {
        $totalRevenue = $this->getTotalRevenue();
        $clientCount = Cliente::count() ?? 1;
        return $clientCount > 0 ? $totalRevenue / $clientCount : 0;
    }

    public function getTotalClients(): int
    {
        return Cliente::count();
    }

    public function getTotalConsultants(): int
    {
        return User::where('papel', 'consultor')->where('ativo', true)->count();
    }

    public function getOrdersPending(): int
    {
        return OrdemServico::whereIn('status', [1, 2, 3, 4])->count();
    }

    public function getOrdersBilled(): int
    {
        return OrdemServico::whereIn('status', [5, 6, 7, 8])->count();
    }

    // ========== CHARTS ==========
    public function getCharts(): array
    {
        return [
            'revenue_by_day' => $this->getRevenueByDay(),
            'orders_by_status' => $this->getOrdersByStatus(),
            'top_clients' => $this->getTopClients(),
            'consultant_performance' => $this->getConsultantPerformance(),
        ];
    }

    public function getRevenueByDay(): array
    {
        $data = OrdemServico::select('created_at', 'valor_total')
            ->whereIn('status', [5, 6, 7])
            ->where('created_at', '>=', now()->subDays(30))
            ->get()
            ->groupBy(fn($item) => $item->created_at->format('Y-m-d'));

        return $data->map(function ($items) {
            $total = $items->sum(fn($item) => (float) $item->valor_total);
            return [
                'date' => $items[0]->created_at->format('Y-m-d'),
                'total' => $total,
            ];
        })->sortBy('date')->values()->toArray();
    }

    public function getOrdersByStatus(): array
    {
        $statusNames = [
            1 => 'Aberta',
            2 => 'Aguardando Aprovação',
            3 => 'Aprovado',
            4 => 'Contestada',
            5 => 'Aguardando Faturamento',
            6 => 'Faturada',
            7 => 'Aguardando RPS',
            8 => 'RPS Emitida',
        ];

        $data = OrdemServico::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return $data->map(function ($item) use ($statusNames) {
            return [
                'status' => $statusNames[$item->status] ?? 'Unknown',
                'count' => (int) $item->count,
            ];
        })->toArray();
    }

    public function getTopClients(int $limit = 5): array
    {
        $data = OrdemServico::select('cliente_id', 'valor_total')
            ->whereIn('status', [5, 6, 7])
            ->with('cliente')
            ->get()
            ->groupBy('cliente_id')
            ->map(function ($items) {
                $total = $items->sum(fn($item) => (float) $item->valor_total);
                return [
                    'cliente_id' => $items[0]->cliente_id,
                    'client_name' => $items[0]->cliente->nome ?? 'Unknown',
                    'total' => $total,
                ];
            })
            ->sortByDesc('total')
            ->take($limit)
            ->values()
            ->toArray();

        return $data;
    }

    public function getConsultantPerformance(): array
    {
        $data = OrdemServico::select('consultor_id', 'valor_total')
            ->whereIn('status', [5, 6, 7])
            ->where('created_at', '>=', now()->subDays(30))
            ->with('consultor')
            ->get()
            ->groupBy('consultor_id')
            ->map(function ($items) {
                $total = $items->sum(fn($item) => (float) $item->valor_total);
                return [
                    'consultant' => $items[0]->consultor->name ?? 'Unknown',
                    'total_orders' => count($items),
                    'total_revenue' => $total,
                    'average_order' => count($items) > 0 ? $total / count($items) : 0,
                ];
            })
            ->sortByDesc('total_revenue')
            ->take(5)
            ->values()
            ->toArray();

        return $data;
    }

    // ========== RECENT ORDERS ==========
    public function getRecentOrders(int $limit = 10): array
    {
        $data = OrdemServico::with(['cliente', 'consultor'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $statusNames = [
            1 => 'Aberta',
            2 => 'Aguardando Aprovação',
            3 => 'Aprovado',
            4 => 'Contestada',
            5 => 'Aguardando Faturamento',
            6 => 'Faturada',
            7 => 'Aguardando RPS',
            8 => 'RPS Emitida',
        ];

        return $data->map(function ($order) use ($statusNames) {
            return [
                'id' => $order->id,
                'client' => $order->cliente->nome ?? 'Unknown',
                'consultant' => $order->consultor->name ?? 'Unknown',
                'total' => (float) $order->valor_total,
                'status' => $statusNames[$order->status] ?? 'Unknown',
                'created_at' => $order->created_at->format('d/m/Y H:i'),
            ];
        })->toArray();
    }

    // ========== RELATÓRIOS ==========
    public function getReports(): array
    {
        return [
            'geral' => $this->getRelatórioGeral(),
            'por_cliente' => $this->getRelatórioClientes(),
            'por_consultor' => $this->getRelatórioConsultores(),
        ];
    }

    public function getRelatórioGeral(): array
    {
        $resultado = DB::table('ordem_servico as os')
            ->select(
                DB::raw('COUNT(os.id) as total_ordens'),
                DB::raw('SUM(CAST(os.valor_total AS NUMERIC)) as valor_total'),
                DB::raw('SUM(CASE WHEN os.status IN (5,6,7,8) THEN 1 ELSE 0 END) as ordens_faturadas'),
                DB::raw('SUM(CASE WHEN os.status IN (5,6,7,8) THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_faturado'),
                DB::raw('SUM(CASE WHEN os.status < 5 THEN 1 ELSE 0 END) as ordens_pendentes'),
                DB::raw('SUM(CASE WHEN os.status < 5 THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_pendente'),
                DB::raw('AVG(CAST(os.valor_total AS NUMERIC)) as ticket_medio')
            )
            ->first();

        return [
            'total_ordens' => (int) ($resultado->total_ordens ?? 0),
            'valor_total' => (float) ($resultado->valor_total ?? 0),
            'ordens_faturadas' => (int) ($resultado->ordens_faturadas ?? 0),
            'valor_faturado' => (float) ($resultado->valor_faturado ?? 0),
            'ordens_pendentes' => (int) ($resultado->ordens_pendentes ?? 0),
            'valor_pendente' => (float) ($resultado->valor_pendente ?? 0),
            'ticket_medio' => (float) ($resultado->ticket_medio ?? 0),
        ];
    }

    public function getRelatórioClientes(): array
    {
        $resultado = DB::table('ordem_servico as os')
            ->join('cliente as c', 'os.cliente_id', '=', 'c.id')
            ->select(
                'c.id',
                'c.codigo',
                'c.nome as cliente_nome',
                DB::raw('COUNT(os.id) as total_ordens'),
                DB::raw('SUM(CAST(os.valor_total AS NUMERIC)) as valor_total'),
                DB::raw('SUM(CASE WHEN os.status IN (5,6,7,8) THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_faturado'),
                DB::raw('SUM(CASE WHEN os.status < 5 THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_pendente')
            )
            ->groupBy('c.id', 'c.codigo', 'c.nome')
            ->orderByDesc('valor_total')
            ->get();

        return $resultado->map(function ($item) {
            return [
                'id' => $item->id,
                'codigo' => $item->codigo,
                'cliente_nome' => $item->cliente_nome,
                'total_ordens' => (int) $item->total_ordens,
                'valor_total' => (float) $item->valor_total,
                'valor_faturado' => (float) ($item->valor_faturado ?? 0),
                'valor_pendente' => (float) ($item->valor_pendente ?? 0),
            ];
        })->toArray();
    }

    public function getRelatórioConsultores(): array
    {
        $resultado = DB::table('ordem_servico as os')
            ->join('users as u', 'os.consultor_id', '=', 'u.id')
            ->select(
                'u.id',
                'u.name as consultor_nome',
                DB::raw('COUNT(os.id) as total_ordens'),
                DB::raw('SUM(CAST(os.valor_total AS NUMERIC)) as valor_total'),
                DB::raw('SUM(CASE WHEN os.status IN (5,6,7,8) THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_faturado'),
                DB::raw('SUM(CASE WHEN os.status < 5 THEN CAST(os.valor_total AS NUMERIC) ELSE 0 END) as valor_pendente'),
                DB::raw('AVG(CAST(os.valor_total AS NUMERIC)) as ticket_medio')
            )
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('valor_total')
            ->get();

        return $resultado->map(function ($item) {
            return [
                'id' => $item->id,
                'consultor_nome' => $item->consultor_nome,
                'total_ordens' => (int) $item->total_ordens,
                'valor_total' => (float) $item->valor_total,
                'valor_faturado' => (float) ($item->valor_faturado ?? 0),
                'valor_pendente' => (float) ($item->valor_pendente ?? 0),
                'ticket_medio' => (float) ($item->ticket_medio ?? 0),
            ];
        })->toArray();
    }
}
