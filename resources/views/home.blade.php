@extends('layout.master')

@section('title', '- Home do Consultor')

@push('styles')
<style>
  .kpi-card{border:0;border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06)}
  .kpi-label{font-size:.85rem;color:#6c757d}
  .kpi-value{font-size:1.6rem;font-weight:700}
  .kpi-icon{font-size:1.25rem;opacity:.6}
</style>
@endpush

@section('content')

  {{-- Aviso de primeira senha (opcional: seta via session no controller) --}}
  @if (session('force_password_change'))
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        Por segurança, redefina sua senha agora mesmo.
        <a href="javascript:void(0)" class="btn btn-sm btn-outline-dark ms-2 btn-redefinir-senha">Redefinir senha</a>
      </div>
    </div>
  @endif

  <div class="d-flex align-items-center mb-3">
    <h4 class="mb-0">Bem-vindo, {{ Auth::user()->name ?? 'Consultor' }}</h4>
    <a href="{{ url('/ordem-servico') }}" class="btn btn-primary btn-sm ms-auto">
      Ir para Ordens de Serviço
    </a>
  </div>

  {{-- KPIs --}}
  @php
    // Estrutura esperada do controller:
    // $stats = ['total'=>0,'abertas'=>0,'retrab'=>0,'finalizadas'=>0];
    $stats = $stats ?? ['total'=>0,'abertas'=>0,'retrab'=>0,'finalizadas'=>0];
  @endphp

  <div class="row g-3">
    <div class="col-12 col-md-3">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <span class="kpi-label">Total de OS</span>
            <i class="bi bi-collection kpi-icon"></i>
          </div>
          <div class="kpi-value">{{ number_format($stats['total'],0,',','.') }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <span class="kpi-label">Abertas</span>
            <i class="bi bi-folder2-open kpi-icon"></i>
          </div>
          <div class="kpi-value text-primary">{{ number_format($stats['abertas'],0,',','.') }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <span class="kpi-label">Em retrabalho</span>
            <i class="bi bi-arrow-repeat kpi-icon"></i>
          </div>
          <div class="kpi-value text-warning">{{ number_format($stats['retrab'],0,',','.') }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <span class="kpi-label">Finalizadas</span>
            <i class="bi bi-check2-circle kpi-icon"></i>
          </div>
          <div class="kpi-value text-success">{{ number_format($stats['finalizadas'],0,',','.') }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Últimas OS do consultor --}}
  @php
    // Estrutura esperada: coleção/array com: id, cliente, descricao, status_txt, dt_abertura
    $ultimas = $ultimas ?? [];
  @endphp

  <div class="card mt-4">
    <div class="card-header d-flex align-items-center">
      <strong>Últimas Ordens de Serviço</strong>
      <form class="ms-auto" method="GET" action="{{ url('/ordem-servico') }}">
        <button class="btn btn-sm btn-outline-primary">Ver todas</button>
      </form>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 90px;">#</th>
              <th>Cliente</th>
              <th>Descrição</th>
              <th style="width: 140px;">Status</th>
              <th style="width: 140px;">Abertura</th>
              <th style="width: 90px;">Ações</th>
            </tr>
          </thead>
          <tbody>
          @forelse ($ultimas as $os)
            <tr>
              <td>{{ $os->id }}</td>
              <td>{{ $os->cliente ?? '-' }}</td>
              <td class="text-truncate" style="max-width: 460px;">{{ $os->descricao ?? '-' }}</td>
              <td>
                @php
                  $badge = 'secondary';
                  $st = strtolower($os->status_txt ?? '');
                  if (str_contains($st, 'aberta')) $badge = 'primary';
                  if (str_contains($st, 'retrabalho')) $badge = 'warning';
                  if (str_contains($st, 'final')) $badge = 'success';
                @endphp
                <span class="badge bg-{{ $badge }}">{{ $os->status_txt ?? '-' }}</span>
              </td>
              <td>{{ \Carbon\Carbon::parse($os->dt_abertura ?? now())->format('d/m/Y') }}</td>
              <td>
                <a href="{{ url('/ordem-servico') }}" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Abrir OS">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted p-4">Nenhuma OS encontrada.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  // ativa tooltips do Bootstrap caso existam
  initializeTooltips && initializeTooltips();
</script>
@endpush
