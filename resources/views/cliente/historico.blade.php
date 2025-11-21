@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">📄 Histórico do Cliente</h1>
            <p class="text-muted">{{ $cliente->nome }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="/clientes" class="btn btn-secondary">← Voltar</a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total de Ordens</h6>
                    <h2 class="mb-0">{{ $overview['total_orders'] }}</h2>
                    <small class="text-success">{{ $overview['completed_orders'] }} concluídas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Gasto</h6>
                    <h2 class="mb-0">R$ {{ number_format($overview['total_spent'], 2, ',', '.') }}</h2>
                    <small class="text-info">Desde {{ $overview['active_since'] }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Valor Médio</h6>
                    <h2 class="mb-0">R$ {{ number_format($overview['average_order_value'], 2, ',', '.') }}</h2>
                    <small class="text-secondary">Por ordem</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Frequência</h6>
                    <h2 class="mb-0">{{ $overview['avg_days_between_orders'] }}d</h2>
                    <small class="text-secondary">Média entre ordens</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Suggestions -->
    @if(count($suggestions) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">💡 Sugestões</h5>
            <div class="row">
                @foreach($suggestions as $suggestion)
                <div class="col-md-6 mb-3">
                    <div class="alert alert-{{ $suggestion['priority'] === 'high' ? 'warning' : ($suggestion['priority'] === 'medium' ? 'info' : 'secondary') }} mb-0">
                        <h6 class="alert-heading mb-2">{{ $suggestion['title'] }}</h6>
                        <p class="mb-0">{{ $suggestion['message'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Tabs for different sections -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab">
                📅 Timeline de Serviços
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="spending-tab" data-bs-toggle="tab" data-bs-target="#spending" type="button" role="tab">
                💰 Gastos por Período
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="patterns-tab" data-bs-toggle="tab" data-bs-target="#patterns" type="button" role="tab">
                📊 Padrões de Serviço
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Timeline Tab -->
        <div class="tab-pane fade show active" id="timeline" role="tabpanel">
            <div class="timeline">
                @forelse($timeline as $item)
                <div class="timeline-item mb-4">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <small class="d-block text-muted">{{ $item['created_at_formatted'] }}</small>
                                    <strong class="d-block">{{ $item['date_service'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-2">{{ $item['title'] }}</h6>
                                            <p class="text-muted small mb-0">{{ $item['description'] }}</p>
                                            <small class="text-secondary">Consultor: {{ $item['consultant'] }}</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <h5 class="mb-2">R$ {{ number_format($item['value'], 2, ',', '.') }}</h5>
                                            <span class="badge bg-{{ $item['status'] >= 4 ? 'success' : 'warning' }}">
                                                {{ $item['status_name'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="alert alert-info">Nenhum serviço registrado para este cliente.</div>
                @endforelse
            </div>
        </div>

        <!-- Spending Tab -->
        <div class="tab-pane fade" id="spending" role="tabpanel">
            <div class="row">
                @foreach($totalByPeriod as $period => $data)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">{{ $data['period'] }}</h6>
                            <h4 class="mb-2">R$ {{ number_format($data['total'], 2, ',', '.') }}</h4>
                            <small class="text-muted">
                                {{ $data['count'] }} ordem(ns)
                                @if($data['count'] > 0)
                                    | Média: R$ {{ number_format($data['average'], 2, ',', '.') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Spending Chart -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Gráfico de Gastos</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="spendingChart" height="60"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patterns Tab -->
        <div class="tab-pane fade" id="patterns" role="tabpanel">
            @if(count($servicePatterns) > 0)
            <div class="row">
                @foreach($servicePatterns as $pattern)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">{{ $pattern['service'] }}</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" role="progressbar"
                                     style="width: {{ $pattern['percentage'] }}%;"
                                     aria-valuenow="{{ $pattern['percentage'] }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $pattern['count'] }} solicitações ({{ $pattern['percentage'] }}%)
                            </small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Service Pattern Chart -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Distribuição de Serviços</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="patternsChart" height="40"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info">Sem dados de padrões disponíveis.</div>
            @endif
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Spending Chart
    const spendingData = {!! json_encode($totalByPeriod) !!};
    const spendingLabels = Object.values(spendingData).map(p => p.period);
    const spendingValues = Object.values(spendingData).map(p => p.total);

    const spendingCtx = document.getElementById('spendingChart');
    if (spendingCtx) {
        new Chart(spendingCtx, {
            type: 'bar',
            data: {
                labels: spendingLabels,
                datasets: [{
                    label: 'Gastos (R$)',
                    data: spendingValues,
                    backgroundColor: '#0dcaf0',
                    borderColor: '#0dcaf0',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });
    }

    // Patterns Chart
    const patternsData = {!! json_encode($servicePatterns) !!};
    const patternsLabels = patternsData.map(p => p.service);
    const patternsValues = patternsData.map(p => p.count);
    const colors = ['#0d6efd', '#0dcaf0', '#198754', '#ffc107', '#dc3545'];

    const patternsCtx = document.getElementById('patternsChart');
    if (patternsCtx) {
        new Chart(patternsCtx, {
            type: 'doughnut',
            data: {
                labels: patternsLabels,
                datasets: [{
                    data: patternsValues,
                    backgroundColor: colors.slice(0, patternsLabels.length),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>
@endsection

@endsection
