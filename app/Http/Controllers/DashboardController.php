<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $data = $this->dashboardService->getAllDashboardData();

        return view('dashboard', [
            'user' => $user,
            'kpis' => $data['kpis'],
            'chartData' => $data['charts'],
            'recentOrders' => $data['recent_orders'],
            'consultantStats' => $data['consultant_stats'],
        ]);
    }

    /**
     * Get dashboard data as JSON (for API calls)
     */
    public function getData()
    {
        return response()->json($this->dashboardService->getAllDashboardData());
    }

    /**
     * Get KPI data
     */
    public function getKPIs()
    {
        return response()->json($this->dashboardService->getAllDashboardData()['kpis']);
    }

    /**
     * Get chart data
     */
    public function getCharts()
    {
        return response()->json($this->dashboardService->getAllDashboardData()['charts']);
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders()
    {
        return response()->json($this->dashboardService->getRecentOrders());
    }

    /**
     * Get consultant statistics
     */
    public function getConsultantStats()
    {
        return response()->json($this->dashboardService->getConsultantStats());
    }
}
