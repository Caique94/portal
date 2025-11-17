<?php

namespace App\Http\Controllers;

use App\Services\ManagerialDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerialDashboardController extends Controller
{
    protected ManagerialDashboardService $service;

    public function __construct(ManagerialDashboardService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the managerial dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check for period filters
        $dataInicio = $request->query('data_inicio');
        $dataFim = $request->query('data_fim');

        $data = $this->service->getAllDashboardData($dataInicio, $dataFim);

        return view('managerial-dashboard', [
            'user' => $user,
            'kpis' => $data['kpis'],
            'chartData' => $data['charts'],
            'recentOrders' => $data['recent_orders'],
            'reports' => $data['reports'],
        ]);
    }

    /**
     * Get all data as JSON
     */
    public function getData()
    {
        return response()->json($this->service->getAllDashboardData());
    }

    /**
     * Get KPIs
     */
    public function getKPIs()
    {
        return response()->json($this->service->getKPIs());
    }

    /**
     * Get charts
     */
    public function getCharts()
    {
        return response()->json([
            'revenue_by_day' => $this->service->getRevenueByDay(),
            'orders_by_status' => $this->service->getOrdersByStatus(),
            'top_clients' => $this->service->getTopClients(),
            'consultant_performance' => $this->service->getConsultantPerformance(),
        ]);
    }

    /**
     * Get reports
     */
    public function getReports()
    {
        return response()->json($this->service->getReports());
    }

    /**
     * Get geral report
     */
    public function getRelatorioGeral()
    {
        return response()->json($this->service->getRelatórioGeral());
    }

    /**
     * Get clientes report
     */
    public function getRelatorioClientes()
    {
        return response()->json($this->service->getRelatórioClientes());
    }

    /**
     * Get consultores report
     */
    public function getRelatorioConsultores()
    {
        return response()->json($this->service->getRelatórioConsultores());
    }
}
