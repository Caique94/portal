@extends('layout.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Fechamento Cliente</h1>
                @can('create', App\Models\RelatorioFechamento::class)
                    <a href="{{ route('relatorio-fechamento-cliente.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Fechamento Cliente
                    </a>
                @endcan
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

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('relatorio-fechamento-cliente.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="rascunho" @selected(request('status') == 'rascunho')>Rascunho</option>
                        <option value="enviado" @selected(request('status') == 'enviado')>Enviado</option>
                        <option value="aprovado" @selected(request('status') == 'aprovado')>Aprovado</option>
                        <option value="rejeitado" @selected(request('status') == 'rejeitado')>Rejeitado</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                           value="{{ request('data_inicio') }}">
                </div>

                <div class="col-md-3">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control"
                           value="{{ request('data_fim') }}">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>

            @if($relatorios->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 8%;">ID</th>
                                <th style="width: 15%;">Consultor</th>
                                <th style="width: 20%;">Período</th>
                                <th style="width: 10%;">Total OS</th>
                                <th style="width: 15%;">Valor Total</th>
                                <th style="width: 12%;">Status</th>
                                <th style="width: 15%;">Aprovado Por</th>
                                <th style="width: 5%; text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.95rem; line-height: 1.8;">
                            @foreach($relatorios as $relatorio)
                                <tr>
                                    <td><strong>#{{ $relatorio->id }}</strong></td>
                                    <td>{{ $relatorio->consultor->name }}</td>
                                    <td>
                                        {{ $relatorio->data_inicio->format('d/m/Y') }} até
                                        {{ $relatorio->data_fim->format('d/m/Y') }}
                                    </td>
                                    <td>{{ $relatorio->total_os }}</td>
                                    <td>R$ {{ number_format($relatorio->valor_total, 2, ',', '.') }}</td>
                                    <td>
                                        @php
                                            $statusBadges = [
                                                'rascunho' => 'badge-secondary',
                                                'enviado' => 'badge-info',
                                                'aprovado' => 'badge-success',
                                                'rejeitado' => 'badge-danger',
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusBadges[$relatorio->status] ?? 'badge-secondary' }}">
                                            {{ ucfirst($relatorio->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($relatorio->aprovador)
                                            {{ $relatorio->aprovador->name }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i> Ações
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @can('view', $relatorio)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('relatorio-fechamento-cliente.show', $relatorio) }}">
                                                            <i class="fas fa-eye"></i> Visualizar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('relatorio-fechamento-cliente.pdf', $relatorio) }}" target="_blank">
                                                            <i class="fas fa-file-pdf"></i> Baixar PDF
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('update', $relatorio)
                                                    @if($relatorio->status === 'rascunho' || $relatorio->status === 'rejeitado')
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="dropdown-item text-primary" type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#enviarAprovacaoModal"
                                                                    data-relatorio="{{ $relatorio->id }}">
                                                                <i class="fas fa-paper-plane"></i> Enviar para Aprovação
                                                            </button>
                                                        </li>
                                                    @endif

                                                    @if($relatorio->status === 'enviado')
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="dropdown-item text-success" type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#aprovarModal"
                                                                    data-relatorio="{{ $relatorio->id }}">
                                                                <i class="fas fa-check"></i> Aprovar
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item text-warning" type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#rejeitarModal"
                                                                    data-relatorio="{{ $relatorio->id }}">
                                                                <i class="fas fa-times"></i> Rejeitar
                                                            </button>
                                                        </li>
                                                    @endif

                                                    @if($relatorio->status === 'aprovado')
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form method="POST" action="{{ route('relatorio-fechamento-cliente.enviar-email', $relatorio) }}" style="display: contents;">
                                                                @csrf
                                                                <button type="submit"
                                                                        class="dropdown-item text-primary"
                                                                        @if($relatorio->data_envio_email) disabled @endif>
                                                                    <i class="fas fa-envelope"></i>
                                                                    @if($relatorio->data_envio_email)
                                                                        Enviado
                                                                    @else
                                                                        Enviar por Email
                                                                    @endif
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif

                                                    @if($relatorio->status === 'rascunho' || $relatorio->status === 'rejeitado')
                                                        <li class="mt-2">
                                                            <form method="POST" action="{{ route('relatorio-fechamento-cliente.destroy', $relatorio) }}" style="display: contents;"
                                                                  onsubmit="return confirm('Tem certeza que deseja remover este relatório?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="fas fa-trash"></i> Remover
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Page navigation">
                    {{ $relatorios->links() }}
                </nav>
            @else
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> Nenhum relatório encontrado.
                </div>
            @endif
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
            <form id="formAprovar" method="POST">
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

<!-- Modal Enviar para Aprovação -->
<div class="modal fade" id="enviarAprovacaoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enviar para Aprovação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEnviarAprovacao" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Tem certeza que deseja enviar este relatório <strong>para aprovação</strong>?</p>
                    <p class="text-muted small mb-0">O relatório será enviado para revisão do departamento de financeiro.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar para Aprovação</button>
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
            <form id="formRejeitar" method="POST">
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

<script>
const baseRoute = '/relatorio-fechamento-cliente';

document.getElementById('enviarAprovacaoModal').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const relatorioid = button.getAttribute('data-relatorio');
    const form = document.getElementById('formEnviarAprovacao');
    form.action = `${baseRoute}/${relatorioid}/enviar-aprovacao`;
});

document.getElementById('aprovarModal').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const relatorioid = button.getAttribute('data-relatorio');
    const form = document.getElementById('formAprovar');
    form.action = `${baseRoute}/${relatorioid}/aprovar`;
});

document.getElementById('rejeitarModal').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const relatorioid = button.getAttribute('data-relatorio');
    const form = document.getElementById('formRejeitar');
    form.action = `${baseRoute}/${relatorioid}/rejeitar`;
});
</script>
@endsection
