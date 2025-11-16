@extends('layout.master')

@section('title', '- Dashboard Gerencial')

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

  .report-table {
    font-size: 0.95rem;
  }

  .report-table tbody tr:hover {
    background-color: #f8f9fa;
  }

  .badge-status {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
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

  .metric-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
  }

  .metric-label {
    font-size: 0.9rem;
    opacity: 0.9;
  }

  .metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-top: 5px;
  }
</style>
@endpush

@section('content')

<div class="dashboard-header">
  <div>
    <h3 class="mb-1">Dashboard Gerencial</h3>
    <p class="text-muted mb-0">
      <i class="bi bi-chart-line"></i>
      Análise Completa do Negócio
    </p>
  </div>
  <div>
    <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
      <i class="bi bi-arrow-clockwise"></i> Atualizar
    </button>
  </div>
</div>

{{-- KPI Cards (7 principais) --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-sm-6 col-lg-4">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">Total de OS (30 dias)</span>
          <i class="bi bi-collection kpi-icon"></i>
        </div>
        <div class="kpi-value">{{ number_format($kpis['total_orders_month'], 0, ',', '.') }}</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-4">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">Receita Total</span>
          <i class="bi bi-cash-coin kpi-icon"></i>
        </div>
        <div class="kpi-value text-success">R$ {{ number_format($kpis['total_revenue'], 2, ',', '.') }}</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-4">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">OS Pendentes</span>
          <i class="bi bi-hourglass-split kpi-icon"></i>
        </div>
        <div class="kpi-value text-warning">{{ number_format($kpis['orders_pending'], 0, ',', '.') }}</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-4">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">OS Faturadas</span>
          <i class="bi bi-check-circle kpi-icon"></i>
        </div>
        <div class="kpi-value text-info">{{ number_format($kpis['orders_billed'], 0, ',', '.') }}</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-4">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">Total de Clientes</span>
          <i class="bi bi-people kpi-icon"></i>
        </div>
        <div class="kpi-value text-primary">{{ number_format($kpis['total_clients'], 0, ',', '.') }}</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-4">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="kpi-label">Total de Consultores</span>
          <i class="bi bi-person-badge kpi-icon"></i>
        </div>
        <div class="kpi-value text-secondary">{{ number_format($kpis['total_consultants'], 0, ',', '.') }}</div>
      </div>
    </div>
  </div>
</div>

{{-- Resumo Financeiro --}}
<div class="card mb-4">
  <div class="card-header">
    <strong>Resumo Financeiro Geral</strong>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-12 col-md-3">
        <div class="metric-box">
          <div class="metric-label">Valor Faturado</div>
          <div class="metric-value">R$ {{ number_format($reports['geral']['valor_faturado'] ?? 0, 2, ',', '.') }}</div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="metric-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
          <div class="metric-label">Valor Pendente</div>
          <div class="metric-value">R$ {{ number_format($reports['geral']['valor_pendente'] ?? 0, 2, ',', '.') }}</div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="metric-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
          <div class="metric-label">Ticket Médio</div>
          <div class="metric-value">R$ {{ number_format($reports['geral']['ticket_medio'] ?? 0, 2, ',', '.') }}</div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="metric-box" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
          <div class="metric-label">Total de Ordens</div>
          <div class="metric-value">{{ number_format($reports['geral']['total_ordens'] ?? 0, 0, ',', '.') }}</div>
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
    <button class="nav-link" id="filtros-tab" data-bs-toggle="tab" data-bs-target="#filtros-panel" type="button"
      role="tab" aria-controls="filtros-panel" aria-selected="false">
      <i class="bi bi-funnel"></i> Filtros & Relatórios
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="clientes-tab" data-bs-toggle="tab" data-bs-target="#clientes-panel" type="button"
      role="tab" aria-controls="clientes-panel" aria-selected="false">
      <i class="bi bi-people"></i> Por Cliente
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="consultores-tab" data-bs-toggle="tab" data-bs-target="#consultores-panel" type="button"
      role="tab" aria-controls="consultores-panel" aria-selected="false">
      <i class="bi bi-person-badge"></i> Por Consultor
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-panel" type="button"
      role="tab" aria-controls="orders-panel" aria-selected="false">
      <i class="bi bi-table"></i> Últimas OS
    </button>
  </li>
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
      <div class="col-12 col-lg-6">
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

      {{-- Consultant Performance Chart --}}
      <div class="col-12 col-lg-6">
        <div class="card">
          <div class="card-header">
            <strong>Performance dos Consultores (30 dias)</strong>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 250px;">
              <canvas id="consultantChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filtros & Relatórios Panel --}}
  <div class="tab-pane fade" id="filtros-panel" role="tabpanel" aria-labelledby="filtros-tab">
    <div class="card">
      <div class="card-header">
        <strong>Filtros Avançados & Exportação de Relatórios</strong>
      </div>
      <div class="card-body">
        <!-- Filtros -->
        <form id="filterForm" class="mb-4">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Data Início</label>
              <input type="date" class="form-control" name="data_inicio" id="data_inicio">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Data Fim</label>
              <input type="date" class="form-control" name="data_fim" id="data_fim">
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Cliente</label>
              <select class="form-control" name="cliente_id" id="cliente_id">
                <option value="">-- Selecione um cliente --</option>
              </select>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Consultor</label>
              <select class="form-control" name="consultor_id" id="consultor_id">
                <option value="">-- Selecione um consultor --</option>
              </select>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Status</label>
              <select class="form-control" name="status" id="status">
                <option value="">-- Selecione um status --</option>
              </select>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">&nbsp;</label>
              <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
                <i class="bi bi-search"></i> Aplicar Filtros
              </button>
            </div>
          </div>
        </form>

        <!-- Resumo dos Filtros -->
        <div id="filterSummary" style="display: none;" class="alert alert-info mb-4">
          <strong>Resumo do Relatório Filtrado:</strong><br>
          <div id="summaryContent"></div>
        </div>

        <!-- Tabela com Resultados -->
        <div id="filteredResults" style="display: none;">
          <div class="table-responsive mb-4">
            <table class="table table-hover report-table" id="filteredTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Cliente</th>
                  <th>Consultor</th>
                  <th>Data</th>
                  <th>Valor</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <!-- Preenchido por JavaScript -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- Botões de Exportação -->
        <div id="exportButtons" style="display: none;" class="mt-4">
          <button type="button" class="btn btn-success" onclick="exportToExcel()">
            <i class="bi bi-file-earmark-excel"></i> Exportar em Excel
          </button>
          <button type="button" class="btn btn-danger" onclick="exportToPdf()">
            <i class="bi bi-file-earmark-pdf"></i> Exportar em PDF
          </button>
          <button type="button" class="btn btn-secondary" onclick="clearFilters()">
            <i class="bi bi-arrow-clockwise"></i> Limpar Filtros
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Clientes Panel --}}
  <div class="tab-pane fade" id="clientes-panel" role="tabpanel" aria-labelledby="clientes-tab">
    <div class="card">
      <div class="card-header">
        <strong>Relatório Detalhado por Cliente</strong>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover report-table">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Total de OS</th>
                <th>Valor Total</th>
                <th>Valor Faturado</th>
                <th>Valor Pendente</th>
              </tr>
            </thead>
            <tbody>
              @forelse($reports['por_cliente'] as $cliente)
                <tr>
                  <td><strong>{{ $cliente['cliente_nome'] }}</strong></td>
                  <td>{{ $cliente['total_ordens'] }}</td>
                  <td>R$ {{ number_format($cliente['valor_total'], 2, ',', '.') }}</td>
                  <td><span class="badge bg-success">R$ {{ number_format($cliente['valor_faturado'], 2, ',', '.') }}</span></td>
                  <td><span class="badge bg-warning">R$ {{ number_format($cliente['valor_pendente'], 2, ',', '.') }}</span></td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">Nenhum cliente encontrado</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Consultores Panel --}}
  <div class="tab-pane fade" id="consultores-panel" role="tabpanel" aria-labelledby="consultores-tab">
    <div class="card">
      <div class="card-header">
        <strong>Relatório Detalhado por Consultor</strong>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover report-table">
            <thead>
              <tr>
                <th>Consultor</th>
                <th>Total de OS</th>
                <th>Valor Total</th>
                <th>Valor Faturado</th>
                <th>Valor Pendente</th>
                <th>Ticket Médio</th>
              </tr>
            </thead>
            <tbody>
              @forelse($reports['por_consultor'] as $consultor)
                <tr>
                  <td><strong>{{ $consultor['consultor_nome'] }}</strong></td>
                  <td>{{ $consultor['total_ordens'] }}</td>
                  <td>R$ {{ number_format($consultor['valor_total'], 2, ',', '.') }}</td>
                  <td><span class="badge bg-success">R$ {{ number_format($consultor['valor_faturado'], 2, ',', '.') }}</span></td>
                  <td><span class="badge bg-warning">R$ {{ number_format($consultor['valor_pendente'], 2, ',', '.') }}</span></td>
                  <td>R$ {{ number_format($consultor['ticket_medio'], 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted">Nenhum consultor encontrado</td>
                </tr>
              @endforelse
            </tbody>
          </table>
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
          <table class="table table-hover report-table">
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
            <tbody>
              @forelse($recentOrders as $order)
                <tr>
                  <td>{{ $order['id'] }}</td>
                  <td>{{ $order['client'] }}</td>
                  <td>{{ $order['consultant'] }}</td>
                  <td>R$ {{ number_format($order['total'], 2, ',', '.') }}</td>
                  <td>
                    <span class="badge bg-secondary">{{ $order['status'] }}</span>
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
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
  const chartData = {!! json_encode($chartData) !!};

  function initCharts() {
    // 1. Revenue by Day Chart
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
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
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

    // 2. Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = chartData.orders_by_status.map(item => item.status);
    const statusValues = chartData.orders_by_status.map(item => item.count);
    const colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#30cfd0', '#a8edea'];

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
            labels: { font: { size: 11 }, padding: 10 }
          }
        }
      }
    });

    // 3. Top Clients Chart
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
        plugins: { legend: { position: 'top' } },
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

    // 4. Consultant Performance Chart
    const consultantCtx = document.getElementById('consultantChart').getContext('2d');
    const consultantLabels = chartData.consultant_performance.map(item => item.consultant);
    const consultantValues = chartData.consultant_performance.map(item => item.total_revenue);

    new Chart(consultantCtx, {
      type: 'bar',
      data: {
        labels: consultantLabels,
        datasets: [{
          label: 'Receita (R$)',
          data: consultantValues,
          backgroundColor: '#764ba2',
          borderColor: '#5a3a85',
          borderWidth: 1,
          borderRadius: 5
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
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

  document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    loadFilterOptions();
  });

  // ===== FILTER FUNCTIONS =====

  /**
   * Load filter options (clientes, consultores, status)
   */
  function loadFilterOptions() {
    console.log('Iniciando loadFilterOptions...');
    fetch('/api/reports/filter-options', {
      credentials: 'same-origin'
    })
      .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
          console.error('HTTP Error:', response.status);
          if (response.status === 401) {
            throw new Error('Não autorizado. Faça login novamente.');
          } else if (response.status === 403) {
            throw new Error('Acesso negado. Apenas administradores podem acessar.');
          } else if (response.status === 404) {
            throw new Error('API não encontrada');
          } else {
            throw new Error(`Erro HTTP ${response.status}`);
          }
        }
        return response.json();
      })
      .then(data => {
        console.log('Filter options loaded:', data);

        // Populate clientes
        const clienteSelect = document.getElementById('cliente_id');
        if (data.clientes && Array.isArray(data.clientes)) {
          data.clientes.forEach(cliente => {
            const option = document.createElement('option');
            option.value = cliente.id;
            option.textContent = cliente.nome;
            clienteSelect.appendChild(option);
          });
          console.log(`Populado ${data.clientes.length} clientes`);
        }

        // Populate consultores
        const consultorSelect = document.getElementById('consultor_id');
        if (data.consultores && Array.isArray(data.consultores)) {
          data.consultores.forEach(consultor => {
            const option = document.createElement('option');
            option.value = consultor.id;
            option.textContent = consultor.name;
            consultorSelect.appendChild(option);
          });
          console.log(`Populado ${data.consultores.length} consultores`);
        }

        // Populate status
        const statusSelect = document.getElementById('status');
        if (data.status && Array.isArray(data.status)) {
          data.status.forEach(status => {
            const option = document.createElement('option');
            option.value = status.id;
            option.textContent = status.name;
            statusSelect.appendChild(option);
          });
          console.log(`Populado ${data.status.length} status`);
        }

        console.log('Filter options populated successfully');
      })
      .catch(error => {
        console.error('ERRO CRÍTICO ao carregar filtros:', error.message);
        console.error('Stack:', error.stack);
        // Show error in the UI
        const clienteSelect = document.getElementById('cliente_id');
        if (clienteSelect && clienteSelect.parentElement) {
          const errorDiv = document.createElement('div');
          errorDiv.className = 'alert alert-danger mt-2 mb-0';
          errorDiv.style.fontSize = '12px';
          errorDiv.textContent = '⚠️ Erro ao carregar filtros: ' + error.message;
          clienteSelect.parentElement.appendChild(errorDiv);
        }
      });
  }

  /**
   * Apply filters and display results
   */
  function applyFilters() {
    const filters = {
      data_inicio: document.getElementById('data_inicio').value,
      data_fim: document.getElementById('data_fim').value,
      cliente_id: document.getElementById('cliente_id').value,
      consultor_id: document.getElementById('consultor_id').value,
      status: document.getElementById('status').value
    };

    console.log('Applying filters:', filters);

    // Show loading state
    const summary = document.getElementById('filterSummary');
    const results = document.getElementById('filteredResults');
    const exports = document.getElementById('exportButtons');

    // Verificar se elementos existem (podem estar em outra aba)
    if (!summary || !results || !exports) {
      console.warn('⚠️ Elementos do filtro não encontrados. Certifique-se de que está na aba "Filtros & Relatórios"');
      alert('Por favor, clique na aba "Filtros & Relatórios" antes de aplicar filtros');
      return;
    }

    const summaryContent = document.getElementById('summaryContent');
    if (summaryContent) {
      summaryContent.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Carregando...</span></div> Carregando dados...';
    }
    summary.style.display = 'block';
    results.style.display = 'none';
    exports.style.display = 'none';

    // Build query string
    const queryString = new URLSearchParams(
      Object.fromEntries(Object.entries(filters).filter(([_, v]) => v !== ''))
    ).toString();

    const url = `/api/reports/filtered${queryString ? '?' + queryString : ''}`;
    console.log('Fetching from URL:', url);

    fetch(url, {
      credentials: 'same-origin'
    })
      .then(response => {
        console.log('Filter response status:', response.status);
        if (!response.ok) {
          console.error('HTTP Error:', response.status);
          if (response.status === 401) {
            throw new Error('Sessão expirada. Faça login novamente.');
          } else if (response.status === 403) {
            throw new Error('Acesso negado. Apenas administradores podem acessar.');
          } else if (response.status === 404) {
            throw new Error('API não encontrada');
          } else if (response.status === 500) {
            throw new Error('Erro no servidor. Verifique os logs.');
          } else {
            throw new Error(`Erro HTTP ${response.status}`);
          }
        }
        return response.json();
      })
      .then(data => {
        console.log('Filtered data received:', data);

        // Display summary
        const summaryContent = document.getElementById('summaryContent');
        const summary_data = data.summary;

        if (summaryContent) {
          summaryContent.innerHTML = `
            <div class="row g-3">
              <div class="col-12 col-md-3">
                <strong>Total de Ordens:</strong> ${summary_data.total_ordens || 0}
              </div>
              <div class="col-12 col-md-3">
                <strong>Valor Total:</strong> R$ ${(summary_data.valor_total || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </div>
              <div class="col-12 col-md-3">
                <strong>Valor Faturado:</strong> R$ ${(summary_data.valor_faturado || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </div>
              <div class="col-12 col-md-3">
                <strong>Valor Pendente:</strong> R$ ${(summary_data.valor_pendente || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </div>
            </div>
          `;
        }

        // Display results table
        const tbody = document.querySelector('#filteredTable tbody');
        if (tbody) {
          tbody.innerHTML = '';

          if (data.data && data.data.length > 0) {
            data.data.forEach((order, index) => {
              const row = document.createElement('tr');
              row.innerHTML = `
                <td>${index + 1}</td>
                <td>${order.cliente_nome || '-'}</td>
                <td>${order.consultor_nome || '-'}</td>
                <td>${new Date(order.created_at).toLocaleDateString('pt-BR')}</td>
                <td>R$ ${parseFloat(order.valor_total || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>
                  <span class="badge ${getStatusBadgeClass(order.status)}">
                    ${getStatusName(order.status)}
                  </span>
                </td>
              `;
              tbody.appendChild(row);
            });
          } else {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="6" class="text-center text-muted">Nenhuma ordem encontrada com estes filtros</td>';
            tbody.appendChild(row);
          }
        }

        // Show results and export buttons
        if (results) results.style.display = 'block';
        if (exports) exports.style.display = 'block';
        if (summary) {
          summary.classList.remove('alert-info');
          summary.classList.add('alert-success');
        }

        console.log('Filter display completed successfully');
      })
      .catch(error => {
        console.error('ERRO ao filtrar:', error.message);
        console.error('Stack:', error.stack);

        // Verificar se elementos existem antes de modificar
        if (summary) {
          summary.classList.remove('alert-info');
          summary.classList.add('alert-danger');
        }

        const summaryContent = document.getElementById('summaryContent');
        if (summaryContent) {
          summaryContent.innerHTML = `<div class="text-danger"><strong>⚠️ Erro ao carregar dados:</strong><br>${error.message}</div>`;
        }

        if (results) results.style.display = 'none';
        if (exports) exports.style.display = 'none';
      });
  }

  /**
   * Export filtered data to Excel
   */
  function exportToExcel() {
    const filters = {
      data_inicio: document.getElementById('data_inicio').value,
      data_fim: document.getElementById('data_fim').value,
      cliente_id: document.getElementById('cliente_id').value,
      consultor_id: document.getElementById('consultor_id').value,
      status: document.getElementById('status').value
    };

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/api/reports/export-excel';

    // Add CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = '_token';
    tokenInput.value = token;
    form.appendChild(tokenInput);

    // Add filter values
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== '') {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
      }
    });

    document.body.appendChild(form);
    console.log('Submitting Excel export form to:', form.action);
    form.submit();
    document.body.removeChild(form);
  }

  /**
   * Export filtered data to PDF
   */
  function exportToPdf() {
    const filters = {
      data_inicio: document.getElementById('data_inicio').value,
      data_fim: document.getElementById('data_fim').value,
      cliente_id: document.getElementById('cliente_id').value,
      consultor_id: document.getElementById('consultor_id').value,
      status: document.getElementById('status').value
    };

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/api/reports/export-pdf';

    // Add CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = '_token';
    tokenInput.value = token;
    form.appendChild(tokenInput);

    // Add filter values
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== '') {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
      }
    });

    document.body.appendChild(form);
    console.log('Submitting PDF export form to:', form.action);
    form.submit();
    document.body.removeChild(form);
  }

  /**
   * Clear filters and reset form
   */
  function clearFilters() {
    document.getElementById('filterForm').reset();
    document.getElementById('filterSummary').style.display = 'none';
    document.getElementById('filteredResults').style.display = 'none';
    document.getElementById('exportButtons').style.display = 'none';
  }

  /**
   * Get status display name
   */
  function getStatusName(status) {
    const statusMap = {
      '1': 'Aberta',
      '2': 'Aguardando Aprovação',
      '3': 'Aprovado',
      '4': 'Contestada',
      '5': 'Aguardando Faturamento',
      '6': 'Faturada',
      '7': 'Aguardando RPS',
      '8': 'RPS Emitida'
    };
    return statusMap[status] || status;
  }

  /**
   * Get status badge CSS class
   */
  function getStatusBadgeClass(status) {
    const classMap = {
      '1': 'bg-info',
      '2': 'bg-warning text-dark',
      '3': 'bg-success',
      '4': 'bg-danger',
      '5': 'bg-primary',
      '6': 'bg-success',
      '7': 'bg-warning text-dark',
      '8': 'bg-info'
    };
    return classMap[status] || 'bg-secondary';
  }
</script>
@endpush
