<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\OrdemServico;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ClientHistoryService
{
    protected Cliente $cliente;

    public function __construct(Cliente $cliente)
    {
        $this->cliente = $cliente;
    }

    /**
     * Get timeline of all services for the client
     */
    public function getTimeline($limit = 20): Collection
    {
        return OrdemServico::where('cliente_id', $this->cliente->id)
            ->orderBy('created_at', 'desc')
            ->with('consultor')
            ->limit($limit)
            ->get()
            ->map(function ($os) {
                return [
                    'id' => $os->id,
                    'title' => "OS #{$os->id}",
                    'description' => $os->descricao ?? 'Sem descrição',
                    'value' => $os->valor_total,
                    'status' => $os->status,
                    'status_name' => $os->statusLabel(),
                    'consultant' => $os->consultor->name ?? 'N/A',
                    'created_at' => $os->created_at,
                    'created_at_formatted' => $os->created_at->format('d/m/Y H:i'),
                    'date_service' => $os->data_servico ? $os->data_servico->format('d/m/Y') : 'N/A',
                ];
            });
    }

    /**
     * Get total spent by period
     */
    public function getTotalByPeriod(): array
    {
        $now = Carbon::now();

        return [
            'current_month' => $this->getTotalForPeriod(
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'Este mês'
            ),
            'last_month' => $this->getTotalForPeriod(
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
                'Mês passado'
            ),
            'current_quarter' => $this->getTotalForPeriod(
                $now->copy()->startOfQuarter(),
                $now->copy()->endOfQuarter(),
                'Este trimestre'
            ),
            'current_year' => $this->getTotalForPeriod(
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'Este ano'
            ),
            'last_year' => $this->getTotalForPeriod(
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
                'Ano passado'
            ),
            'all_time' => [
                'period' => 'Todos os tempos',
                'total' => $this->getAllTimeTotal(),
                'count' => $this->getTotalCountForClient(),
                'average' => $this->getAverageValue(),
            ],
        ];
    }

    /**
     * Get service patterns
     */
    public function getServicePatterns(): array
    {
        $orders = OrdemServico::where('cliente_id', $this->cliente->id)
            ->where('status', '>=', 4) // Only approved/completed services
            ->pluck('descricao')
            ->filter()
            ->countBy()
            ->sort()
            ->reverse()
            ->take(5);

        return $orders->map(function ($count, $service) {
            return [
                'service' => $service,
                'count' => $count,
                'percentage' => round(($count / $orders->sum()) * 100, 1),
            ];
        })->values()->all();
    }

    /**
     * Get automatic suggestions
     */
    public function getSuggestions(): array
    {
        $suggestions = [];

        // Check for inactive clients
        $lastOrder = OrdemServico::where('cliente_id', $this->cliente->id)
            ->where('status', '>=', 4)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastOrder) {
            $daysSinceLastOrder = $lastOrder->created_at->diffInDays(now());
            $averageDaysBetweenOrders = $this->getAverageDaysBetweenOrders();

            // Suggest if inactive beyond pattern
            if ($averageDaysBetweenOrders > 0 && $daysSinceLastOrder > ($averageDaysBetweenOrders * 1.5)) {
                $suggestions[] = [
                    'type' => 'inactive',
                    'title' => '⚠️ Cliente Inativo',
                    'message' => "Último serviço há {$daysSinceLastOrder} dias. Padrão: a cada {$averageDaysBetweenOrders} dias.",
                    'priority' => 'high',
                ];
            }
        }

        // Suggest based on most common service
        $topService = $this->getTopService();
        if ($topService) {
            $suggestions[] = [
                'type' => 'recurring_service',
                'title' => '💡 Serviço Recorrente',
                'message' => "Serviço mais comum: {$topService['service']} ({$topService['count']}x)",
                'priority' => 'medium',
            ];
        }

        // Check pending orders
        $pendingCount = OrdemServico::where('cliente_id', $this->cliente->id)
            ->where('status', '<', 4)
            ->count();

        if ($pendingCount > 0) {
            $suggestions[] = [
                'type' => 'pending_orders',
                'title' => '📋 Ordens Pendentes',
                'message' => "Há {$pendingCount} ordem(ns) aguardando aprovação ou processamento.",
                'priority' => 'high',
            ];
        }

        // High-value client suggestion
        if ($this->getAllTimeTotal() > 10000) {
            $suggestions[] = [
                'type' => 'high_value_client',
                'title' => '⭐ Cliente VIP',
                'message' => 'Cliente de alto valor: ' . $this->formatMoney($this->getAllTimeTotal()) . ' gastos.',
                'priority' => 'low',
            ];
        }

        return $suggestions;
    }

    /**
     * Get client overview statistics
     */
    public function getOverview(): array
    {
        return [
            'total_orders' => $this->getTotalCountForClient(),
            'total_spent' => $this->getAllTimeTotal(),
            'average_order_value' => $this->getAverageValue(),
            'completed_orders' => $this->getCompletedOrdersCount(),
            'pending_orders' => $this->getPendingOrdersCount(),
            'active_since' => $this->getClientActiveSince(),
            'avg_days_between_orders' => round($this->getAverageDaysBetweenOrders(), 1),
        ];
    }

    /**
     * Helper: Get total for a specific period
     */
    private function getTotalForPeriod($startDate, $endDate, $label): array
    {
        $total = OrdemServico::where('cliente_id', $this->cliente->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '>=', 4) // Only approved/completed
            ->sum('valor_total');

        $count = OrdemServico::where('cliente_id', $this->cliente->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '>=', 4)
            ->count();

        return [
            'period' => $label,
            'total' => $total,
            'count' => $count,
            'average' => $count > 0 ? $total / $count : 0,
        ];
    }

    /**
     * Helper: Get all-time total
     */
    private function getAllTimeTotal(): float
    {
        return (float) OrdemServico::where('cliente_id', $this->cliente->id)
            ->where('status', '>=', 4)
            ->sum('valor_total');
    }

    /**
     * Helper: Get total count for client
     */
    private function getTotalCountForClient(): int
    {
        return OrdemServico::where('cliente_id', $this->cliente->id)->count();
    }

    /**
     * Helper: Get average value
     */
    private function getAverageValue(): float
    {
        $count = $this->getTotalCountForClient();
        if ($count === 0) {
            return 0;
        }
        return $this->getAllTimeTotal() / $count;
    }

    /**
     * Helper: Get completed orders count
     */
    private function getCompletedOrdersCount(): int
    {
        return OrdemServico::where('cliente_id', $this->cliente->id)
            ->where('status', '>=', 4)
            ->count();
    }

    /**
     * Helper: Get pending orders count
     */
    private function getPendingOrdersCount(): int
    {
        return OrdemServico::where('cliente_id', $this->cliente->id)
            ->where('status', '<', 4)
            ->count();
    }

    /**
     * Helper: Get when client started using service
     */
    private function getClientActiveSince(): ?string
    {
        $firstOrder = OrdemServico::where('cliente_id', $this->cliente->id)
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$firstOrder) {
            return null;
        }

        return $firstOrder->created_at->format('d/m/Y');
    }

    /**
     * Helper: Get average days between orders
     */
    private function getAverageDaysBetweenOrders(): float
    {
        $orders = OrdemServico::where('cliente_id', $this->cliente->id)
            ->where('status', '>=', 4)
            ->orderBy('created_at', 'asc')
            ->pluck('created_at')
            ->toArray();

        if (count($orders) < 2) {
            return 0;
        }

        $totalDays = 0;
        $count = 0;

        for ($i = 1; $i < count($orders); $i++) {
            $date1 = new Carbon($orders[$i - 1]);
            $date2 = new Carbon($orders[$i]);
            $totalDays += $date1->diffInDays($date2);
            $count++;
        }

        return $count > 0 ? $totalDays / $count : 0;
    }

    /**
     * Helper: Get top service
     */
    private function getTopService(): ?array
    {
        $patterns = $this->getServicePatterns();
        return count($patterns) > 0 ? $patterns[0] : null;
    }

    /**
     * Helper: Format money
     */
    private function formatMoney($value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}
