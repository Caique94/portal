@extends('layout.master')

@section('title', '- Minha Home')

@push('styles')
<style>
  /* Design System */
  :root {
    --primary: #1f6feb;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --dark: #0f172a;
    --light: #f8fafc;
    --border: #e5e7eb;
    --text-muted: #6b7280;
  }

  body { background: var(--light); }

  /* Header Section */
  .header-section {
    padding: 24px;
    background: white;
    border-bottom: 1px solid var(--border);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
  }

  .header-title h1 {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
    color: var(--dark);
  }

  .header-title p {
    color: var(--text-muted);
    margin: 4px 0 0;
    font-size: 14px;
  }

  .header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
  }

  .btn {
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
  }

  .btn-primary {
    background: var(--primary);
    color: white;
  }

  .btn-primary:hover {
    background: #1a5bc5;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(31, 111, 235, 0.3);
  }

  .btn-success {
    background: var(--success);
    color: white;
  }

  .btn-success:hover {
    background: #059669;
    transform: translateY(-2px);
  }

  .btn-danger {
    background: var(--danger);
    color: white;
  }

  .btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
  }

  .btn-outline {
    background: white;
    border: 1px solid var(--border);
    color: var(--dark);
  }

  .btn-outline:hover {
    background: var(--light);
  }

  /* KPI Cards */
  .kpi-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
  }

  @media (max-width: 1400px) {
    .kpi-container {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  @media (max-width: 1024px) {
    .kpi-container {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 768px) {
    .kpi-container {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .kpi-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
  }

  .kpi-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-color: var(--primary);
  }

  .kpi-label {
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    margin: 0 0 8px;
  }

  .kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark);
    margin: 0;
  }

  .kpi-icon {
    float: right;
    font-size: 32px;
    opacity: 0.1;
  }

  /* Content Grid */
  .content-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 24px;
    margin-bottom: 24px;
  }

  /* Card */
  .card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
  }

  .card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .card-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    color: var(--dark);
  }

  .card-body {
    padding: 16px 20px;
  }

  /* Table */
  .table-wrapper {
    overflow-x: auto;
    scrollbar-width: auto;
    scrollbar-color: #999999 #F5F5F5;
  }

  .table-wrapper::-webkit-scrollbar {
    height: 8px;
  }

  .table-wrapper::-webkit-scrollbar-track {
    background: #F5F5F5;
    border-radius: 4px;
  }

  .table-wrapper::-webkit-scrollbar-thumb {
    background: #999999;
    border-radius: 4px;
  }

  .table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #666666;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }

  thead th {
    background: #f9fafb;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    color: var(--text-muted);
    border-bottom: 2px solid var(--border);
  }

  tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
  }

  tbody tr {
    transition: background 0.2s ease;
  }

  tbody tr:hover {
    background: #f9fafb;
  }

  /* Status Badge */
  .badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
  }

  .badge-warning {
    background: rgba(245, 158, 11, 0.15);
    color: #b45309;
  }

  .badge-success {
    background: rgba(16, 185, 129, 0.15);
    color: #047857;
  }

  .badge-danger {
    background: rgba(239, 68, 68, 0.15);
    color: #7f1d1d;
  }

  .badge-primary {
    background: rgba(31, 111, 235, 0.15);
    color: #1e40af;
  }

  .badge-info {
    background: rgba(59, 130, 246, 0.15);
    color: #1e3a8a;
  }

  /* Sidebar */
  .sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .sidebar-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
  }

  .stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
  }

  .stat-row:last-child {
    border-bottom: none;
  }

  .stat-label {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
  }

  .stat-value {
    font-weight: 700;
    color: var(--dark);
  }

  /* Modal */
  .modal-header {
    border-bottom: 1px solid var(--border);
    padding: 16px 20px;
  }

  .modal-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
  }

  .modal-body {
    padding: 20px;
  }

  .form-group {
    margin-bottom: 16px;
  }

  .form-group label {
    display: block;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 6px;
    font-size: 13px;
  }

  .form-group input,
  .form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
  }

  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(31, 111, 235, 0.1);
  }

  /* Loading */
  .spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    border-top-color: var(--primary);
    animation: spin 0.6s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  /* Responsive */
  @media (max-width: 768px) {
    .header-section {
      flex-direction: column;
      align-items: flex-start;
    }

    .header-actions {
      width: 100%;
    }

    .content-grid {
      grid-template-columns: 1fr;
    }

    .kpi-container {
      grid-template-columns: repeat(2, 1fr);
    }
  }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="header-section">
  <div class="header-title">
    <h1>Olá, {{ Auth::user()->name }}</h1>
    <p>Resumo das suas ordens de serviço</p>
  </div>
  <div class="header-actions">
    <a href="{{ route('ordem-servico') }}" class="btn btn-primary">
      <i class="bi bi-list"></i> Gerenciar Ordens
    </a>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalExportarRelatorio">
      <i class="bi bi-download"></i> Exportar Relatório
    </button>
  </div>
</div>

<!-- KPI Cards -->
<div class="kpi-container">
  <div class="kpi-card">
    <div style="float: right; font-size: 32px; opacity: 0.1;">
      <i class="bi bi-file-text"></i>
    </div>
    <p class="kpi-label">Total de OS</p>
    <p class="kpi-value">{{ number_format($total_meu ?? 0, 0, ',', '.') }}</p>
  </div>

  <div class="kpi-card">
    <div style="float: right; font-size: 32px; opacity: 0.1;">
      <i class="bi bi-clock"></i>
    </div>
    <p class="kpi-label">Abertas</p>
    <p class="kpi-value">{{ number_format($abertas_meu ?? 0, 0, ',', '.') }}</p>
  </div>

  <div class="kpi-card">
    <div style="float: right; font-size: 32px; opacity: 0.1;">
      <i class="bi bi-check-circle"></i>
    </div>
    <p class="kpi-label">Faturado (Mês)</p>
    <p class="kpi-value">R$ {{ number_format($valor_faturado_mes ?? 0, 2, ',', '.') }}</p>
  </div>

  <div class="kpi-card">
    <div style="float: right; font-size: 32px; opacity: 0.1;">
      <i class="bi bi-graph-up"></i>
    </div>
    <p class="kpi-label">Taxa Conclusão</p>
    @php
      $totalOS = $total_meu ?? 0;
      $abertasOS = $abertas_meu ?? 0;
      $taxaConc = $totalOS > 0 ? round((($totalOS - $abertasOS) / $totalOS) * 100) : 0;
    @endphp
    <p class="kpi-value">{{ $taxaConc }}%</p>
  </div>
</div>

<!-- Content -->
<div class="content-grid">
  <!-- Main Table -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Minhas Últimas OS</h2>
      <a href="{{ route('ordem-servico') }}" class="btn btn-outline" style="font-size: 12px; padding: 6px 12px;">
        Ver todas
      </a>
    </div>
    <div class="card-body">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th style="width: 60px;">#</th>
              <th>Data</th>
              <th>Cliente</th>
              <th>Status</th>
              <th style="text-align: right;">Valor</th>
              <th style="width: 80px; text-align: center;">Ação</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($ultimas ?? []) as $os)
              @php
                $statusTxt = strtolower($os->status_txt ?? '');
                $badgeClass = match($statusTxt) {
                  'aberta' => 'badge-warning',
                  'enviada para aprovação' => 'badge-warning',
                  'finalizada' => 'badge-success',
                  'contestada' => 'badge-danger',
                  'faturada' => 'badge-primary',
                  'aguardando rps' => 'badge-info',
                  default => 'badge-secondary'
                };
              @endphp
              <tr>
                <td><strong>#{{ $os->id }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($os->data)->format('d/m/Y') }}</td>
                <td>{{ $os->cliente ?? '-' }}</td>
                <td>
                  <span class="badge {{ $badgeClass }}">
                    {{ ucfirst($os->status_txt ?? '—') }}
                  </span>
                </td>
                <td style="text-align: right; font-weight: 600;">
                  R$ {{ number_format($os->valor_total ?? 0, 2, ',', '.') }}
                </td>
                <td style="text-align: center;">
                  <button class="btn btn-outline" style="padding: 6px 10px; font-size: 12px;"
                    data-bs-toggle="modal" data-bs-target="#modalDetalhesOS" data-os-id="{{ $os->id }}">
                    <i class="bi bi-eye"></i> Ver
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                  Nenhuma ordem de serviço encontrada
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Status Summary -->
    <div class="sidebar-card">
      <h3 class="card-title" style="margin-bottom: 12px;">Status</h3>
      @forelse(($por_status ?? []) as $row)
        @php
          $statusKey = strtolower($row->status_txt ?? '');
          $color = match($statusKey) {
            'aberta' => 'var(--warning)',
            'enviada para aprovação' => 'var(--warning)',
            'finalizada' => 'var(--success)',
            'contestada' => 'var(--danger)',
            'faturada' => 'var(--primary)',
            'aguardando rps' => 'var(--info)',
            default => 'var(--text-muted)'
          };
        @endphp
        <div class="stat-row">
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $color }};"></span>
            <span class="stat-label">{{ $row->status_txt ?? 'Desconhecido' }}</span>
          </div>
          <span class="stat-value" style="color: {{ $color }};">{{ $row->qtd }}</span>
        </div>
      @empty
        <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Sem dados disponíveis</p>
      @endforelse
    </div>

    <!-- Quick Actions -->
    <div class="sidebar-card">
      <h3 class="card-title" style="margin-bottom: 12px;">Ações Rápidas</h3>
      <button class="btn btn-primary" style="width: 100%; margin-bottom: 8px;" data-bs-toggle="modal" data-bs-target="#modalExportarRelatorio">
        <i class="bi bi-file-earmark-excel"></i> Excel
      </button>
      <button class="btn btn-danger" style="width: 100%;" data-bs-toggle="modal" data-bs-target="#modalExportarRelatorio">
        <i class="bi bi-file-earmark-pdf"></i> PDF
      </button>
    </div>
  </div>
</div>

<!-- Modal Detalhes OS -->
<div class="modal fade" id="modalDetalhesOS" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-file-text"></i> Detalhes da Ordem de Serviço
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="osLoading" style="text-align: center; padding: 32px;">
          <div class="spinner"></div>
          <p style="margin-top: 12px; color: var(--text-muted);">Carregando...</p>
        </div>
        <div id="osContent" style="display: none;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div>
              <p style="color: var(--text-muted); font-size: 12px; margin: 0;">ID</p>
              <p style="font-weight: 700; font-size: 16px; margin: 4px 0 0;" id="osId"></p>
            </div>
            <div>
              <p style="color: var(--text-muted); font-size: 12px; margin: 0;">Status</p>
              <p id="osStatus" style="margin: 4px 0 0;"></p>
            </div>
            <div>
              <p style="color: var(--text-muted); font-size: 12px; margin: 0;">Data Emissão</p>
              <p style="font-weight: 700; margin: 4px 0 0;" id="osData"></p>
            </div>
            <div>
              <p style="color: var(--text-muted); font-size: 12px; margin: 0;">Valor Total</p>
              <p style="font-weight: 700; font-size: 16px; margin: 4px 0 0;" id="osValor"></p>
            </div>
            <div style="grid-column: 1 / -1;">
              <p style="color: var(--text-muted); font-size: 12px; margin: 0;">Cliente</p>
              <p style="font-weight: 700; margin: 4px 0 0;" id="osCliente"></p>
            </div>
          </div>
          <hr style="margin: 16px 0;">
          <div>
            <p style="color: var(--text-muted); font-size: 12px; margin: 0;">Descrição</p>
            <p style="margin: 4px 0 0;" id="osDescricao"></p>
          </div>
        </div>
        <div id="osError" style="display: none; padding: 20px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; color: var(--danger);">
          Erro ao carregar detalhes. Tente novamente.
        </div>
      </div>
      <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; gap: 8px; justify-content: flex-end;">
        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Fechar</button>
        <a href="{{ route('ordem-servico') }}" class="btn btn-primary" id="btnEditarOS">
          <i class="bi bi-pencil"></i> Gerenciar
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Modal Exportar Relatório -->
<div class="modal fade" id="modalExportarRelatorio" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-download"></i> Exportar Relatório de Ordens de Serviço
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="padding: 24px;">
        <form id="formExportar">
          <div style="margin-bottom: 20px;">
            <h6 style="color: var(--dark); font-weight: 600; margin-bottom: 12px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
              <i class="bi bi-funnel"></i> Filtros
            </h6>

            <div class="form-group" style="margin-bottom: 16px;">
              <label for="dataInicio" style="font-weight: 500; font-size: 13px; margin-bottom: 6px; display: block; color: var(--dark);">Data Inicial</label>
              <input type="date" id="dataInicio" name="data_inicio" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px;">
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
              <label for="dataFim" style="font-weight: 500; font-size: 13px; margin-bottom: 6px; display: block; color: var(--dark);">Data Final</label>
              <input type="date" id="dataFim" name="data_fim" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px;">
            </div>

            <div class="form-group">
              <label for="clienteFiltro" style="font-weight: 500; font-size: 13px; margin-bottom: 6px; display: block; color: var(--dark);">Cliente (Opcional)</label>
              <select id="clienteFiltro" name="cliente_id" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px;">
                <option value="">Todos os clientes</option>
                @foreach($clientes ?? [] as $cliente)
                  <option value="{{ $cliente->id }}">{{ $cliente->nome ?? $cliente->nome_fantasia }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <!-- Botões de Ação -->
          <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
            <h6 style="color: var(--dark); font-weight: 600; margin-bottom: 12px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
              <i class="bi bi-download"></i> Formato de Exportação
            </h6>

            <div style="display: flex; flex-direction: column; gap: 8px;">
              <button type="submit" name="format" value="excel" class="btn btn-success" style="width: 100%; padding: 12px; font-weight: 500; border-radius: 6px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px;">
                <i class="bi bi-file-earmark-excel"></i> Exportar Excel
              </button>
              <button type="submit" name="format" value="pdf" class="btn btn-danger" style="width: 100%; padding: 12px; font-weight: 500; border-radius: 6px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px;">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
              </button>
            </div>
          </div>
        </form>
      </div>

      <div style="padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 12px; justify-content: flex-end;">
        <button type="button" class="btn btn-outline" data-bs-dismiss="modal" style="padding: 10px 20px;">
          Cancelar
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
  // Carregar detalhes da OS
  $('#modalDetalhesOS').on('show.bs.modal', function(e) {
    const osId = $(e.relatedTarget).data('os-id');
    carregarDetalhesOS(osId);
  });

  function carregarDetalhesOS(osId) {
    $('#osLoading').show();
    $('#osContent').hide();
    $('#osError').hide();

    $.ajax({
      url: '/listar-ordens-servico',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        const os = response.data.find(o => o.id == osId);

        if (os) {
          $('#osId').text('#' + os.id);
          $('#osData').text(new Date(os.created_at).toLocaleDateString('pt-BR'));
          $('#osCliente').text(os.cliente_nome || '-');
          $('#osValor').text('R$ ' + parseFloat(os.valor_total || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}));
          $('#osDescricao').text(os.descricao || 'Sem descrição');

          const statusMap = {
            0: { text: 'Aberta', class: 'badge badge-warning' },
            1: { text: 'Enviada para Aprovação', class: 'badge badge-warning' },
            2: { text: 'Finalizada', class: 'badge badge-success' },
            3: { text: 'Contestada', class: 'badge badge-danger' },
            4: { text: 'Aguardando RPS', class: 'badge badge-info' },
            5: { text: 'Faturada', class: 'badge badge-primary' }
          };

          const status = statusMap[os.status] || { text: 'Desconhecido', class: 'badge' };
          $('#osStatus').html('<span class="' + status.class + '">' + status.text + '</span>');
          $('#btnEditarOS').attr('href', "{{ route('ordem-servico') }}?id=" + osId);

          $('#osLoading').hide();
          $('#osContent').show();
        } else {
          $('#osLoading').hide();
          $('#osError').show();
        }
      },
      error: function() {
        $('#osLoading').hide();
        $('#osError').show();
      }
    });
  }

  // Exportar relatório
  $('#formExportar').on('submit', function(e) {
    e.preventDefault();
    const format = $(e.originalEvent.submitter).val();
    const dataInicio = $('#dataInicio').val();
    const dataFim = $('#dataFim').val();
    const clienteId = $('#clienteFiltro').val();

    let url = format === 'excel' ? '{{ route("consultor.exportExcel") }}' : '{{ route("consultor.exportPDF") }}';

    if (dataInicio) url += '?data_inicio=' + dataInicio;
    if (dataFim) url += (url.includes('?') ? '&' : '?') + 'data_fim=' + dataFim;
    if (clienteId) url += (url.includes('?') ? '&' : '?') + 'cliente_id=' + clienteId;

    window.location.href = url;
    $('#modalExportarRelatorio').modal('hide');
  });
});
</script>
@endpush
