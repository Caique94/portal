<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get total orders count for the current period (last 30 days)
     */
    public function getTotalOrdersThisMonth(): int
    {
        return OrdemServico::where('created_at', '>=', now('America/Sao_Paulo')->subDays(30))->count();
    }

    /**
     * Get total revenue for billed orders
     */
    public function getTotalRevenue(): float
    {
        $orders = OrdemServico::whereIn('status', [5, 6, 7])
            ->pluck('valor_total');

        $total = $orders->sum(fn($value) => (float) $value);

        return (float) $total;
    }

    /**
     * Get average revenue per client
     */
    public function getAverageRevenuePerClient(): float
    {
        $totalRevenue = $this->getTotalRevenue();
        $clientCount = Cliente::count() ?? 1;

        return $clientCount > 0 ? $totalRevenue / $clientCount : 0;
    }

    /**
     * Get total number of clients
     */
    public function getTotalClients(): int
    {
        return Cliente::count();
    }

    /**
     * Get revenue breakdown by day for the last 30 days
     */
    public function getRevenueByDay(): array
    {
        $data = OrdemServico::select('created_at', 'valor_total')
            ->whereIn('status', [5, 6, 7])
            ->where('created_at', '>=', now('America/Sao_Paulo')->subDays(30))
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

    /**
     * Get status distribution of all orders
     */
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

    /**
     * Get top 5 clients by revenue
     */
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

    /**
     * Get the last 10 orders for the table
     */
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

    /**
     * Get statistics by consultant (for admins)
     */
    public function getConsultantStats(): array
    {
        $data = OrdemServico::select('consultor_id', 'valor_total')
            ->whereIn('status', [5, 6, 7])
            ->with('consultor')
            ->get()
            ->groupBy('consultor_id')
            ->map(function ($items) {
                $total = $items->sum(fn($item) => (float) $item->valor_total);
                return [
                    'consultant' => $items[0]->consultor->name ?? 'Unknown',
                    'total_orders' => count($items),
                    'total_revenue' => $total,
                ];
            })
            ->values()
            ->toArray();

        return $data;
    }

    /**
     * Get all dashboard data at once
     */
    public function getAllDashboardData(): array
    {
        return [
            'kpis' => [
                'total_orders_month' => $this->getTotalOrdersThisMonth(),
                'total_revenue' => $this->getTotalRevenue(),
                'average_per_client' => $this->getAverageRevenuePerClient(),
                'total_clients' => $this->getTotalClients(),
            ],
            'charts' => [
                'revenue_by_day' => $this->getRevenueByDay(),
                'orders_by_status' => $this->getOrdersByStatus(),
                'top_clients' => $this->getTopClients(),
            ],
            'recent_orders' => $this->getRecentOrders(),
            'consultant_stats' => $this->getConsultantStats(),
        ];
    }
}
