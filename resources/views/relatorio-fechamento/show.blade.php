@extends('layout.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Relatório #{{ $relatorioFechamento->id }}</h1>
                <a href="{{ $relatorioFechamento->tipo === 'cliente' ? route('relatorio-fechamento-cliente.index') : route('relatorio-fechamento-consultor.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Consultor</label>
                                <p class="mb-0 h6">{{ $consultor->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Tipo de Fechamento</label>
                                <p class="mb-0">
                                    @if($relatorioFechamento->tipo === 'consultor')
                                        <span class="badge badge-primary h6">Consultor</span>
                                        <small class="d-block text-muted mt-1">Baseado nos valores do consultor</small>
                                    @else
                                        <span class="badge badge-warning h6">Cliente</span>
                                        <small class="d-block text-muted mt-1">Baseado nos valores administrativos</small>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Período</label>
                                <p class="mb-0 h6">
                                    {{ $relatorioFechamento->data_inicio->format('d/m/Y') }} até
                                    {{ $relatorioFechamento->data_fim->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Status</label>
                                <p class="mb-0">
                                    @php
                                        $statusBadges = [
                                            'rascunho' => 'badge-secondary',
                                            'enviado' => 'badge-info',
                                            'aprovado' => 'badge-success',
                                            'rejeitado' => 'badge-danger',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusBadges[$relatorioFechamento->status] ?? 'badge-secondary' }} h6">
                                        {{ ucfirst($relatorioFechamento->status) }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Criado em</label>
                                <p class="mb-0 h6">{{ $relatorioFechamento->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary">{{ $relatorioFechamento->total_os }}</h3>
                                <p class="text-muted small mb-0">Ordens de Serviço</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success">R$ {{ number_format($relatorioFechamento->valor_total, 2, ',', '.') }}</h3>
                                <p class="text-muted small mb-0">Valor Total</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info">R$ {{ number_format($relatorioFechamento->valor_total / max($relatorioFechamento->total_os, 1), 2, ',', '.') }}</h3>
                                <p class="text-muted small mb-0">Valor Médio</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-secondary">{{ $relatorioFechamento->total_os > 0 ? floor(($relatorioFechamento->valor_total / $relatorioFechamento->total_os) * 100) / 100 : 0 }}</h3>
                                <p class="text-muted small mb-0">Por OS</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Ordens de Serviço no Período</h5>

                    @if($ordemServicos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <th>Cliente</th>
                                        <th>Status</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ordemServicos as $os)
                                        <tr>
                                            <td><strong>#{{ $os->id }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($os->created_at)->format('d/m/Y') }}</td>
                                            <td>{{ $os->cliente ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $statusMap = [
                                                        0 => 'Aberta',
                                                        1 => 'Enviada para Aprovação',
                                                        2 => 'Finalizada',
                                                        3 => 'Contestada',
                                                        4 => 'Faturada',
                                                        5 => 'Faturada',
                                                        6 => 'Faturada',
                                                    ];
                                                    $statusColors = [
                                                        0 => 'warning',
                                                        1 => 'warning',
                                                        2 => 'success',
                                                        3 => 'danger',
                                                        4 => 'primary',
                                                        5 => 'primary',
                                                        6 => 'primary',
                                                    ];
                                                @endphp
                                                <span class="badge badge-{{ $statusColors[$os->status] ?? 'secondary' }}">
                                                    {{ $statusMap[$os->status] ?? 'Desconhecido' }}
                                                </span>
                                            </td>
                                            <td class="text-end">R$ {{ number_format((float)($os->valor_total ?? 0), 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total</strong></td>
                                        <td class="text-end"><strong>R$ {{ number_format($relatorioFechamento->valor_total, 2, ',', '.') }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i> Nenhuma ordem de serviço encontrada no período especificado.
                        </div>
                    @endif

                    @if($relatorioFechamento->observacoes)
                        <hr>
                        <div class="alert alert-warning" role="alert">
                            <strong>Motivo da Rejeição:</strong>
                            <p class="mb-0 mt-2">{{ $relatorioFechamento->observacoes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Ações</h5>
                </div>
                <div class="card-body">
                    @can('view', $relatorioFechamento)
                        <a href="{{ route($relatorioFechamento->tipo === 'cliente' ? 'relatorio-fechamento-cliente.pdf' : 'relatorio-fechamento-consultor.pdf', $relatorioFechamento) }}"
                           class="btn btn-outline-danger w-100 mb-2" target="_blank">
                            <i class="fas fa-file-pdf"></i> Baixar PDF
                        </a>
                    @endcan

                    @if($relatorioFechamento->status === 'rascunho' || $relatorioFechamento->status === 'rejeitado')
                        @can('update', $relatorioFechamento)
                            <form method="POST" action="{{ route($relatorioFechamento->tipo === 'cliente' ? 'relatorio-fechamento-cliente.destroy' : 'relatorio-fechamento-consultor.destroy', $relatorioFechamento) }}"
                                  onsubmit="return confirm('Tem certeza que deseja remover este relatório?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-trash"></i> Remover
                                </button>
                            </form>
                        @endcan
                    @endif

                    @if($relatorioFechamento->status === 'enviado')
                        @can('update', $relatorioFechamento)
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success"
                                        data-bs-toggle="modal"
                                        data-bs-target="#aprovarModal">
                                    <i class="fas fa-check"></i> Aprovar
                                </button>
                                <button type="button" class="btn btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejeitarModal">
                                    <i class="fas fa-times"></i> Rejeitar
                                </button>
                            </div>
                        @endcan
                    @endif

                    @if($relatorioFechamento->status === 'aprovado')
                        @can('update', $relatorioFechamento)
                            <form method="POST" action="{{ route($relatorioFechamento->tipo === 'cliente' ? 'relatorio-fechamento-cliente.enviar-email' : 'relatorio-fechamento-consultor.enviar-email', $relatorioFechamento) }}"
                                  class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-primary"
                                        @if($relatorioFechamento->data_envio_email) disabled @endif>
                                    <i class="fas fa-envelope"></i>
                                    @if($relatorioFechamento->data_envio_email)
                                        Enviado em {{ $relatorioFechamento->data_envio_email->format('d/m/Y H:i') }}
                                    @else
                                        Enviar Email para Consultor
                                    @endif
                                </button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informações</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Data de Criação</label>
                        <p class="mb-0">{{ $relatorioFechamento->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    @if($relatorioFechamento->data_aprovacao)
                        <div class="mb-3">
                            <label class="form-label text-muted">Data de Aprovação</label>
                            <p class="mb-0">{{ $relatorioFechamento->data_aprovacao->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Aprovado por</label>
                            <p class="mb-0">{{ $relatorioFechamento->aprovador->name ?? '-' }}</p>
                        </div>
                    @endif

                    @if($relatorioFechamento->data_envio_email)
                        <div class="mb-3">
                            <label class="form-label text-muted">Data de Envio</label>
                            <p class="mb-0">{{ $relatorioFechamento->data_envio_email->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aprovar -->
<div class="modal fade" id="aprovarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aprovar Relatório</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route($relatorioFechamento->tipo === 'cliente' ? 'relatorio-fechamento-cliente.aprovar' : 'relatorio-fechamento-consultor.aprovar', $relatorioFechamento) }}">
                @csrf
                <div class="modal-body">
                    <p>Tem certeza que deseja <strong>aprovar</strong> este relatório?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Aprovar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rejeitar -->
<div class="modal fade" id="rejeitarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeitar Relatório</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route($relatorioFechamento->tipo === 'cliente' ? 'relatorio-fechamento-cliente.rejeitar' : 'relatorio-fechamento-consultor.rejeitar', $relatorioFechamento) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Motivo da Rejeição</label>
                        <textarea name="observacoes" id="observacoes" class="form-control" rows="4" required
                                  minlength="10" placeholder="Descreva o motivo da rejeição..."></textarea>
                        <small class="text-muted">Mínimo de 10 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Rejeitar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
