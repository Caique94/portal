@extends('layout.master')

@section('title', '- Dashboard Analítico')

@push('styles')
<style>
  .kpi-card {
    border: 0;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, .12);
  }

  .kpi-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
  }

  .kpi-value {
    font-size: 1.8rem;
    font-weight: 700;
    margin-top: 8px;
  }

  .kpi-icon {
    font-size: 1.5rem;
    opacity: 0.5;
  }

  .chart-container {
    position: relative;
    height: 300px;
    margin-top: 15px;
  }

  .stats-table {
    font-size: 0.95rem;
  }

  .stats-table tbody tr:hover {
    background-color: #f8f9fa;
  }

  .badge-status {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
  }

  .status-aberta {
    background-color: #e7f3ff;
    color: #0056b3;
  }

  .status-aprovado {
    background-color: #e6f5e6;
    color: #006600;
  }

  .status-contestada {
    background-color: #ffe6e6;
    color: #cc0000;
  }

  .status-faturada {
    background-color: #e6f0ff;
    color: #003d99;
  }

  .status-rps {
    background-color: #fff4e6;
    color: #b38600;
  }

  .status-emitida {
    background-color: #e6ffe6;
    color: #00b300;
  }

  .alert-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 0;
    border-radius: 10px;
  }

  .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
  }

  .tabs-content {
    margin-top: 25px;
  }
</style>
@endpush

@section('content')

<div class="dashboard-header">
  <div>
    <h3 class="mb-1">Dashboard Analítico</h3>
    <p class="text-muted mb-0">
      <i class="bi bi-clock-history"></i>
      Atualizado em tempo real
    </p>
  </div>
  <div>
    <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
      <i class="bi bi-arrow-clockwise"></i> Atualizar
    </button>
  </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">Total de OS (30 dias)</span>
          <i class="bi bi-collection kpi-icon"></i>
        </div>
        <div class="kpi-value" id="total-orders">
          {{ number_format($kpis['total_orders_month'], 0, ',', '.') }}
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">Receita Total</span>
          <i class="bi bi-cash-coin kpi-icon"></i>
        </div>
        <div class="kpi-value text-success" id="total-revenue">
          R$ {{ number_format($kpis['total_revenue'], 2, ',', '.') }}
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">Média por Cliente</span>
          <i class="bi bi-person-check kpi-icon"></i>
        </div>
        <div class="kpi-value text-info" id="average-per-client">
          R$ {{ number_format($kpis['average_per_client'], 2, ',', '.') }}
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">Total de Clientes</span>
          <i class="bi bi-people kpi-icon"></i>
        </div>
        <div class="kpi-value text-primary" id="total-clients">
          {{ number_format($kpis['total_clients'], 0, ',', '.') }}
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Tabs Navigation --}}
<ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="charts-tab" data-bs-toggle="tab" data-bs-target="#charts-panel"
      type="button" role="tab" aria-controls="charts-panel" aria-selected="true">
      <i class="bi bi-graph-up"></i> Gráficos
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-panel" type="button"
      role="tab" aria-controls="orders-panel" aria-selected="false">
      <i class="bi bi-table"></i> Últimas OS
    </button>
  </li>
  @if (Auth::user()->papel === 'admin')
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="consultants-tab" data-bs-toggle="tab" data-bs-target="#consultants-panel"
        type="button" role="tab" aria-controls="consultants-panel" aria-selected="false">
        <i class="bi bi-person-badge"></i> Por Consultor
      </button>
    </li>
  @endif
</ul>

<div class="tab-content tabs-content" id="dashboardTabsContent">
  {{-- Charts Panel --}}
  <div class="tab-pane fade show active" id="charts-panel" role="tabpanel" aria-labelledby="charts-tab">
    <div class="row g-4">
      {{-- Revenue by Day Chart --}}
      <div class="col-12 col-lg-8">
        <div class="card h-100">
          <div class="card-header">
            <strong>Receita por Dia (Últimos 30 dias)</strong>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="revenueByDayChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- Status Distribution Chart --}}
      <div class="col-12 col-lg-4">
        <div class="card h-100">
          <div class="card-header">
            <strong>Status das OS</strong>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 250px;">
              <canvas id="statusChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- Top Clients Chart --}}
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <strong>Top 5 Clientes (Receita)</strong>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 250px;">
              <canvas id="topClientsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Recent Orders Panel --}}
  <div class="tab-pane fade" id="orders-panel" role="tabpanel" aria-labelledby="orders-tab">
    <div class="card">
      <div class="card-header">
        <strong>10 Últimas Ordens de Serviço</strong>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover stats-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Consultor</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody id="ordersTableBody">
              @forelse($recentOrders as $order)
                <tr>
                  <td>{{ $order['id'] }}</td>
                  <td>{{ $order['client'] }}</td>
                  <td>{{ $order['consultant'] }}</td>
                  <td>R$ {{ number_format($order['total'], 2, ',', '.') }}</td>
                  <td>
                    <span class="badge-status status-{{ strtolower(str_replace(' ', '-', $order['status'])) }}">
                      {{ $order['status'] }}
                    </span>
                  </td>
                  <td>{{ $order['created_at'] }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted">Nenhuma ordem de serviço encontrada</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Consultant Stats Panel (Admin only) --}}
  @if (Auth::user()->papel === 'admin')
    <div class="tab-pane fade" id="consultants-panel" role="tabpanel" aria-labelledby="consultants-tab">
      <div class="card">
        <div class="card-header">
          <strong>Estatísticas por Consultor</strong>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover stats-table">
              <thead>
                <tr>
                  <th>Consultor</th>
                  <th>Total de OS</th>
                  <th>Receita Total</th>
                  <th>Ticket Médio</th>
                </tr>
              </thead>
              <tbody id="consultantsTableBody">
                @forelse($consultantStats as $stat)
                  <tr>
                    <td><strong>{{ $stat['consultant'] }}</strong></td>
                    <td>{{ $stat['total_orders'] }}</td>
                    <td>R$ {{ number_format($stat['total_revenue'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($stat['total_revenue'] / max($stat['total_orders'], 1), 2, ',', '.') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted">Sem dados de consultores</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
  // Convert PHP data to JavaScript
  const chartData = {!! json_encode($chartData) !!};

  // Initialize charts
  function initCharts() {
    // 1. Revenue by Day Chart (Line Chart)
    const revenueCtx = document.getElementById('revenueByDayChart').getContext('2d');
    const revenueLabels = chartData.revenue_by_day.map(item => {
      const date = new Date(item.date);
      return date.toLocaleDateString('pt-BR', { month: 'short', day: '2-digit' });
    });
    const revenueValues = chartData.revenue_by_day.map(item => item.total);

    new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels: revenueLabels,
        datasets: [{
          label: 'Receita (R$)',
          data: revenueValues,
          borderColor: '#667eea',
          backgroundColor: 'rgba(102, 126, 234, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointBackgroundColor: '#667eea',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointHoverRadius: 6,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          filler: {
            propagate: true
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
              }
            }
          }
        }
      }
    });

    // 2. Status Distribution Chart (Pie Chart)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = chartData.orders_by_status.map(item => item.status);
    const statusValues = chartData.orders_by_status.map(item => item.count);
    const colors = [
      '#667eea', '#764ba2', '#f093fb', '#4facfe',
      '#43e97b', '#fa709a', '#30cfd0', '#a8edea'
    ];

    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: statusLabels,
        datasets: [{
          data: statusValues,
          backgroundColor: colors.slice(0, statusLabels.length),
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              font: { size: 11 },
              padding: 10
            }
          }
        }
      }
    });

    // 3. Top Clients Chart (Horizontal Bar Chart)
    const clientsCtx = document.getElementById('topClientsChart').getContext('2d');
    const clientLabels = chartData.top_clients.map(item => item.client_name);
    const clientValues = chartData.top_clients.map(item => item.total);

    new Chart(clientsCtx, {
      type: 'bar',
      data: {
        labels: clientLabels,
        datasets: [{
          label: 'Receita (R$)',
          data: clientValues,
          backgroundColor: '#43e97b',
          borderColor: '#2dd450',
          borderWidth: 1,
          borderRadius: 5
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
              }
            }
          }
        }
      }
    });
  }

  // Initialize charts when page loads
  document.addEventListener('DOMContentLoaded', initCharts);
</script>
@endpush
