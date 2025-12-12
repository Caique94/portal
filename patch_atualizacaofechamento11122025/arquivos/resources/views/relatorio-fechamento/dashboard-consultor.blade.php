@extends('layout.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-chart-line text-primary"></i> Dashboard Fechamento Consultor
                </h1>
                <div>
                    <a href="{{ route('relatorio-fechamento-consultor.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-list"></i> Ver Todos
                    </a>
                    @can('create', App\Models\RelatorioFechamento::class)
                        <a href="{{ route('relatorio-fechamento-consultor.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Novo Fechamento Consultor
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Métricas Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total de Fechamentos</p>
                            <h3 class="mb-0">{{ $totalFechamentos }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-file-invoice fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Aprovados</p>
                            <h3 class="mb-0 text-success">{{ $totalAprovados }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Pendentes</p>
                            <h3 class="mb-0 text-warning">{{ $totalPendentes }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Valor Total Mês</p>
                            <h3 class="mb-0 text-primary">R$ {{ number_format($valorTotalMes, 2, ',', '.') }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top 5 Consultores -->
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy text-warning"></i> Top 5 Consultores por Valor
                    </h5>
                </div>
                <div class="card-body">
                    @if($porConsultor->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Consultor</th>
                                        <th class="text-center">Fechamentos</th>
                                        <th class="text-end">Valor Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($porConsultor as $index => $item)
                                        <tr>
                                            <td>
                                                @if($index === 0)
                                                    <i class="fas fa-medal text-warning me-1"></i>
                                                @elseif($index === 1)
                                                    <i class="fas fa-medal text-secondary me-1"></i>
                                                @elseif($index === 2)
                                                    <i class="fas fa-medal text-danger me-1"></i>
                                                @endif
                                                {{ $item->consultor->nome ?? 'N/A' }}
                                            </td>
                                            <td class="text-center"><strong>{{ $item->total }}</strong></td>
                                            <td class="text-end">
                                                <strong>R$ {{ number_format($item->valor, 2, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">Nenhum fechamento cadastrado</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Últimos Fechamentos -->
        <div class="col-md-7 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-primary"></i> Últimos Fechamentos
                    </h5>
                </div>
                <div class="card-body">
                    @if($ultimosFechamentos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Consultor</th>
                                        <th>Período</th>
                                        <th class="text-end">Valor</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ultimosFechamentos as $fechamento)
                                        <tr>
                                            <td>#{{ $fechamento->id }}</td>
                                            <td>
                                                <small>{{ $fechamento->consultor->nome ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ \Carbon\Carbon::parse($fechamento->data_inicio)->format('d/m/Y') }}
                                                    a
                                                    {{ \Carbon\Carbon::parse($fechamento->data_fim)->format('d/m/Y') }}
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <strong>R$ {{ number_format($fechamento->valor_total, 2, ',', '.') }}</strong>
                                            </td>
                                            <td>
                                                @if($fechamento->status === 'rascunho')
                                                    <span class="badge bg-secondary">Rascunho</span>
                                                @elseif($fechamento->status === 'enviado')
                                                    <span class="badge bg-warning">Enviado</span>
                                                @elseif($fechamento->status === 'aprovado')
                                                    <span class="badge bg-success">Aprovado</span>
                                                @elseif($fechamento->status === 'rejeitado')
                                                    <span class="badge bg-danger">Rejeitado</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('relatorio-fechamento-consultor.show', $fechamento) }}"
                                                   class="btn btn-sm btn-outline-primary" title="Ver Detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">Nenhum fechamento recente</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
