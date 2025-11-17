@extends('layout.master')

@section('title', '- Detalhes do Projeto')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>{{ $projeto->nome }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('projetos.edit', $projeto->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="{{ route('projetos.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Código</label>
                                <p class="form-control-plaintext">{{ $projeto->codigo }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Cliente</label>
                                <p class="form-control-plaintext">
                                    {{ $projeto->cliente->nome_fantasia ?? $projeto->cliente->razao_social }}
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p class="form-control-plaintext">
                                    @php
                                        $statusClass = match($projeto->status) {
                                            'ativo' => 'success',
                                            'pausado' => 'warning',
                                            'concluido' => 'info',
                                            'cancelado' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ ucfirst($projeto->status) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Data Início</label>
                                <p class="form-control-plaintext">
                                    {{ $projeto->data_inicio ? $projeto->data_inicio->format('d/m/Y') : '-' }}
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Data Fim</label>
                                <p class="form-control-plaintext">
                                    {{ $projeto->data_fim ? $projeto->data_fim->format('d/m/Y') : '-' }}
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Criado em</label>
                                <p class="form-control-plaintext">
                                    {{ $projeto->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <label class="form-label fw-bold small text-muted">Horas Alocadas</label>
                            <h3 class="mb-0 text-primary">{{ number_format($projeto->horas_alocadas ?? 0, 2, ',', '.') }}h</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <label class="form-label fw-bold small text-muted">Horas Consumidas</label>
                            <h3 class="mb-0 text-warning">{{ number_format($projeto->horas_consumidas ?? 0, 2, ',', '.') }}h</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <label class="form-label fw-bold small text-muted">Horas Restantes</label>
                            @php
                                $restantes = $projeto->horas_restantes ?? 0;
                                $restanteClass = $restantes < 10 ? 'text-danger' : ($restantes < 20 ? 'text-warning' : 'text-success');
                            @endphp
                            <h3 class="mb-0 {{ $restanteClass }}">{{ number_format($restantes, 2, ',', '.') }}h</h3>
                        </div>
                    </div>
                </div>
            </div>

            @if ($projeto->descricao)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Descrição</h5>
                        <p>{{ $projeto->descricao }}</p>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        Ordens de Serviço Vinculadas
                        @if($projeto->ordemServicos->count() > 0)
                            <span class="badge bg-primary">{{ $projeto->ordemServicos->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if ($projeto->ordemServicos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Código OS</th>
                                        <th>Consultor</th>
                                        <th>Status</th>
                                        <th>Data Emissão</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($projeto->ordemServicos as $os)
                                        <tr>
                                            <td>#{{ $os->id }}</td>
                                            <td>{{ $os->consultor->name ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $osStatusClass = match($os->status) {
                                                        1 => 'info',
                                                        2 => 'warning',
                                                        3 => 'danger',
                                                        4 => 'secondary',
                                                        5 => 'success',
                                                        6 => 'info',
                                                        7 => 'success',
                                                        default => 'secondary'
                                                    };
                                                    $osStatusText = match($os->status) {
                                                        1 => 'Em Aberto',
                                                        2 => 'Aguardando Aprovação',
                                                        3 => 'Contestada',
                                                        4 => 'Aguardando Faturamento',
                                                        5 => 'Faturada',
                                                        6 => 'Aguardando RPS',
                                                        7 => 'RPS Emitida',
                                                        default => 'Desconhecido'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $osStatusClass }}">{{ $osStatusText }}</span>
                                            </td>
                                            <td>{{ $os->data_emissao ? $os->data_emissao->format('d/m/Y') : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Nenhuma ordem de serviço vinculada a este projeto.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
