@extends('layout.master')

@section('title', '- Admin Dashboard')

@push('styles')
<style>
  .kpi-card {
    border: 0;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,.06);
    transition: transform 0.2s, box-shadow 0.2s;
    background-color: #FFFFFF !important;
  }

  .kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,.12);
  }

  .kpi-label {
    font-size: .85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .kpi-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
    margin: 10px 0;
  }

  .kpi-icon {
    font-size: 2rem;
    opacity: 0.6;
  }

  .section-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #333;
    margin-top: 30px;
    margin-bottom: 20px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
  }

  .pending-report {
    border-left: 4px solid #ff6b6b;
    padding: 15px;
    background: #fff5f5;
    border-radius: 8px;
    margin-bottom: 12px;
  }

  .pending-report:hover {
    background: #ffe0e0;
    cursor: pointer;
  }

  .badge-pending {
    background-color: #ff6b6b;
    color: white;
  }

  .badge-approved {
    background-color: #51cf66;
    color: white;
  }

  .open-os {
    border-left: 4px solid #ffd93d;
    padding: 12px;
    background: #fff8e1;
    border-radius: 6px;
    margin-bottom: 10px;
    font-size: 0.9rem;
  }

  .consultant-card {
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .consultant-card:hover {
    background: #f8f9fa;
  }

  .chart-container {
    position: relative;
    height: 300px;
    margin-bottom: 20px;
  }

  .empty-state {
    text-align: center;
    padding: 30px;
    color: #999;
  }

  .empty-state i {
    font-size: 3rem;
    margin-bottom: 10px;
    opacity: 0.5;
  }

  .scrollable-section {
    max-height: 500px;
    overflow-y: auto;
  }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center mb-4">
  <h4 class="mb-0">
    <i class="bi bi-speedometer2 me-2"></i>
    Dashboard Administrativo
  </h4>
  <span class="badge bg-primary ms-auto">{{ Auth::user()->name }}</span>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="kpi-label">Total de OS</p>
            <p class="kpi-value">{{ number_format($totalOS, 0, ',', '.') }}</p>
          </div>
          <i class="bi bi-files kpi-icon text-primary"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="kpi-label">Consultores Ativos</p>
            <p class="kpi-value">{{ number_format($totalConsultores, 0, ',', '.') }}</p>
          </div>
          <i class="bi bi-people kpi-icon text-success"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="kpi-label">Total de Clientes</p>
            <p class="kpi-value">{{ number_format($totalClientes, 0, ',', '.') }}</p>
          </div>
          <i class="bi bi-building kpi-icon text-info"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="kpi-label">Faturamento Aprovado</p>
            <p class="kpi-value">R$ {{ number_format($totalFaturamento, 0, ',', '.') }}</p>
          </div>
          <i class="bi bi-cash-coin kpi-icon text-warning"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Status de OS -->
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header bg-light">
        <h5 class="mb-0">Status das Ordens de Serviço</h5>
      </div>
      <div class="card-body">
        @if(count($osStatus) > 0)
          @foreach($osStatus as $status => $count)
            <div class="mb-3">
              <div class="d-flex justify-content-between mb-2">
                <span>{{ $status }}</span>
                <span class="badge bg-secondary">{{ $count }}</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar" role="progressbar"
                     style="width: {{ $count > 0 ? ($count / $totalOS * 100) : 0 }}%;"
                     aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ $totalOS }}"></div>
              </div>
            </div>
          @endforeach
        @else
          <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Nenhuma ordem de serviço registrada</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header bg-light">
        <h5 class="mb-0">Atalhos Rápidos</h5>
      </div>
      <div class="card-body">
        <div class="row g-2">
          <div class="col-6">
            <a href="{{ url('/cadastros/usuarios') }}" class="btn btn-outline-primary btn-sm w-100">
              <i class="bi bi-person-plus"></i> Usuários
            </a>
          </div>
          <div class="col-6">
            <a href="{{ url('/cadastros/clientes') }}" class="btn btn-outline-success btn-sm w-100">
              <i class="bi bi-building"></i> Clientes
            </a>
          </div>
          <div class="col-6">
            <a href="{{ url('/cadastros/produtos') }}" class="btn btn-outline-info btn-sm w-100">
              <i class="bi bi-box"></i> Produtos
            </a>
          </div>
          <div class="col-6">
            <a href="{{ url('/ordem-servico') }}" class="btn btn-outline-warning btn-sm w-100">
              <i class="bi bi-file-earmark"></i> Ordens
            </a>
          </div>
          <div class="col-6">
            <a href="{{ url('/relatorios') }}" class="btn btn-outline-danger btn-sm w-100">
              <i class="bi bi-graph-up"></i> Relatórios
            </a>
          </div>
          <div class="col-6">
            <a href="{{ url('/relatorio-fechamento') }}" class="btn btn-outline-dark btn-sm w-100">
              <i class="bi bi-check-circle"></i> Aprovações
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Pendências de Aprovação -->
<h5 class="section-title">
  <i class="bi bi-exclamation-circle me-2"></i>
  Relatórios Pendentes de Aprovação
  @if($relatoriosPendentes->count() > 0)
    <span class="badge badge-pending ms-2">{{ $relatoriosPendentes->count() }}</span>
  @endif
</h5>

@if($relatoriosPendentes->count() > 0)
  <div class="row g-3 mb-4">
    @foreach($relatoriosPendentes as $relatorio)
      <div class="col-12 col-lg-6">
        <div class="pending-report" onclick="window.location.href='{{ url('/relatorio-fechamento/' . $relatorio->id) }}'">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <h6 class="mb-1">
                <strong>{{ $relatorio->consultor->name }}</strong>
              </h6>
              <small class="text-muted">
                {{ $relatorio->created_at->format('d/m/Y H:i') }}
              </small>
            </div>
            <span class="badge badge-pending">PENDENTE</span>
          </div>
          <div class="row g-2 mt-2">
            <div class="col-6">
              <small>
                <strong>Período:</strong>
                {{ \Carbon\Carbon::parse($relatorio->data_inicio)->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($relatorio->data_fim)->format('d/m/Y') }}
              </small>
            </div>
            <div class="col-6">
              <small>
                <strong>Valor:</strong>
                R$ {{ number_format($relatorio->valor_total, 2, ',', '.') }}
              </small>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="alert alert-success mb-4" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    Nenhum relatório pendente de aprovação. Parabéns!
  </div>
@endif

<!-- Ordens de Serviço Abertas e Pendentes de Aprovação -->
<h5 class="section-title">
  <i class="bi bi-file-earmark-open me-2"></i>
  Ordens Abertas e Aguardando Aprovação
  @if($osAbertas->count() > 0)
    <span class="badge bg-warning text-dark ms-2">{{ $osAbertas->count() }}</span>
  @endif
</h5>

@if($osAbertas->count() > 0)
  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body scrollable-section">
          @foreach($osAbertas as $os)
            <div class="open-os">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h6 class="mb-1">
                    <strong>OS #{{ $os->id }}</strong> - {{ $os->assunto }}
                  </h6>
                  <small class="text-muted">
                    Cliente: <strong>{{ $os->cliente->nome_fantasia ?? $os->cliente->razao_social }}</strong> |
                    Consultor: <strong>{{ $os->consultor->name }}</strong>
                  </small>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-success" onclick="aprovarOS({{ $os->id }})">
                    <i class="bi bi-check-circle"></i> Aprovar
                  </button>
                  <button type="button" class="btn btn-danger" onclick="contestarOS({{ $os->id }})">
                    <i class="bi bi-x-circle"></i> Contestar
                  </button>
                  <a href="{{ url('/ordem-servico') }}" class="btn btn-outline-primary">
                    <i class="bi bi-eye"></i> Ver
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@else
  <div class="alert alert-info mb-4" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    Nenhuma ordem de serviço aberta no momento.
  </div>
@endif

<!-- Performance dos Consultores -->
<h5 class="section-title">
  <i class="bi bi-graph-up me-2"></i>
  Top 5 Consultores (Últimos 30 dias)
</h5>

@if($consultoresPerformance->count() > 0)
  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          @foreach($consultoresPerformance as $index => $consultor)
            <div class="consultant-card">
              <div>
                <h6 class="mb-1">
                  <span class="badge bg-primary">{{ $index + 1 }}º</span>
                  {{ $consultor->name }}
                </h6>
                <small class="text-muted">{{ $consultor->email }}</small>
              </div>
              <span class="badge bg-info" title="Ordens de Serviço">
                {{ $consultor->ordem_servicos_count }} OS
              </span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@else
  <div class="alert alert-info mb-4" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    Nenhum consultor ativo registrado.
  </div>
@endif

<!-- Últimos Relatórios Aprovados -->
<h5 class="section-title">
  <i class="bi bi-check-circle me-2"></i>
  Últimos 5 Relatórios Aprovados
</h5>

@if($ultimosRelatorios->count() > 0)
  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Consultor</th>
                  <th>Período</th>
                  <th>Valor</th>
                  <th>Aprovado em</th>
                </tr>
              </thead>
              <tbody>
                @foreach($ultimosRelatorios as $relatorio)
                  <tr>
                    <td>#{{ $relatorio->id }}</td>
                    <td>{{ $relatorio->consultor->name }}</td>
                    <td>
                      {{ \Carbon\Carbon::parse($relatorio->data_inicio)->format('d/m/Y') }} -
                      {{ \Carbon\Carbon::parse($relatorio->data_fim)->format('d/m/Y') }}
                    </td>
                    <td>
                      <strong>R$ {{ number_format($relatorio->valor_total, 2, ',', '.') }}</strong>
                    </td>
                    <td>
                      <small class="text-muted">{{ $relatorio->updated_at->format('d/m/Y H:i') }}</small>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@else
  <div class="alert alert-info mb-4" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    Nenhum relatório aprovado ainda.
  </div>
@endif

<!-- Clientes Inativos -->
@if($clientesInativos->count() > 0)
  <h5 class="section-title">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Clientes Sem Atividade (Últimos 30 dias)
  </h5>

  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th>Contato</th>
                  <th>Telefone</th>
                </tr>
              </thead>
              <tbody>
                @foreach($clientesInativos as $cliente)
                  <tr>
                    <td>{{ $cliente->nome_fantasia ?? $cliente->razao_social }}</td>
                    <td>{{ $cliente->contatos->first()?->nome ?? '-' }}</td>
                    <td>{{ $cliente->contatos->first()?->telefone ?? '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

<!-- Modal de Contestação -->
<div class="modal fade" id="modalContestar" tabindex="-1" aria-labelledby="modalContestarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalContestarLabel">Contestar Ordem de Serviço</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formContestar">
          <input type="hidden" id="osIdContestar" value="">
          <div class="mb-3">
            <label for="motivoContestacao" class="form-label">Motivo da Contestação</label>
            <textarea class="form-control" id="motivoContestacao" rows="4" placeholder="Descreva o motivo da contestação..." required></textarea>
          </div>
          <small class="text-muted">A ordem será marcada como contestada e o consultor será notificado.</small>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" onclick="enviarContestacao()">Contestar</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
   function aprovarOS(osId) {
    if (!confirm('Tem certeza que deseja aprovar esta Ordem de Serviço?')) return;

    $.ajax({
      url: '/toggle-ordem-servico/' + osId + '/4',
      method: 'POST',
      contentType: 'application/json',            // << garante o header Content-Type
      data: JSON.stringify({}),                   // << corpo vazio válido para o backend
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'              // força resposta JSON (útil pra erros)
      },
      success: function(response) {
        alert('Ordem de Serviço aprovada com sucesso!');
        location.reload();
      },
      error: function(xhr) {
        console.error('Erro aprovarOS', xhr.status, xhr.responseText);
        // tenta mostrar mensagem amigável do backend
        try {
          const json = xhr.responseJSON || JSON.parse(xhr.responseText);
          alert(json.message || JSON.stringify(json));
        } catch (e) {
          alert('Erro ao aprovar a Ordem de Serviço. Veja console.');
        }
      }
    });
  }

  function contestarOS(osId) {
    document.getElementById('osIdContestar').value = osId;
    const modal = new bootstrap.Modal(document.getElementById('modalContestar'));
    modal.show();
  }

  function enviarContestacao() {
    const osId = document.getElementById('osIdContestar').value;
    const motivo = document.getElementById('motivoContestacao').value;

    if (!motivo.trim()) {
      alert('Por favor, descreva o motivo da contestação.');
      return;
    }

    $.ajax({
      url: '/contestar-ordem-servico',
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: {
        id: osId,
        motivo: motivo
      },
      success: function(response) {
        alert('Ordem de Serviço contestada com sucesso!');
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalContestar'));
        modal.hide();
        location.reload();
      },
      error: function(error) {
        console.error(error);
        alert('Erro ao contestar a Ordem de Serviço.');
      }
    });
  }
</script>
@endpush

@endsection
