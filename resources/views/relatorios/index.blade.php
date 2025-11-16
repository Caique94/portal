@extends('layout.master')

@section('title', '- Relatórios Gerenciais')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css')}}" rel="stylesheet">
<style>
    .card-relatorio {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .card-relatorio h5 {
        color: #0d6efd;
        margin-bottom: 15px;
        font-weight: 600;
    }
    .filtros-relatorio {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .resumo-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .resumo-card h3 {
        font-size: 2rem;
        margin: 0;
    }
    .resumo-card p {
        margin: 5px 0 0 0;
        opacity: 0.9;
    }
</style>
@endpush

@section('content')
<h4>RELAT&Oacute;RIOS GERENCIAIS</h4>

<!-- Filtros Globais -->
<div class="card-relatorio">
    <h5><i class="bi bi-funnel"></i> Filtros Globais</h5>
    <div class="row filtros-relatorio">
        <div class="col-md-3">
            <label class="form-label">Data Início</label>
            <input type="date" id="filtroDataInicio" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Data Fim</label>
            <input type="date" id="filtroDataFim" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Cliente (opcional)</label>
            <select id="filtroCliente" class="form-select">
                <option value="">Todos os clientes</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button id="btnAplicarFiltros" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Aplicar
            </button>
        </div>
    </div>
</div>

<!-- Resumo Geral -->
<div class="row" id="resumoGeral">
    <div class="col-md-3">
        <div class="resumo-card">
            <p>Total de Ordens</p>
            <h3 id="totalOrdens">0</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="resumo-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <p>Valor Total</p>
            <h3 id="valorTotal">R$ 0,00</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="resumo-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <p>Faturado</p>
            <h3 id="valorFaturado">R$ 0,00</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="resumo-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <p>Ticket Médio</p>
            <h3 id="ticketMedio">R$ 0,00</h3>
        </div>
    </div>
</div>

<!-- Tabs de Relatórios -->
<ul class="nav nav-tabs mt-4" id="relatorioTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-clientes" data-bs-toggle="tab" data-bs-target="#relatorio-clientes" type="button">
            <i class="bi bi-people"></i> Por Cliente
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-consultores" data-bs-toggle="tab" data-bs-target="#relatorio-consultores" type="button">
            <i class="bi bi-person-badge"></i> Por Consultor
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-status" data-bs-toggle="tab" data-bs-target="#relatorio-status" type="button">
            <i class="bi bi-bar-chart"></i> Por Status
        </button>
    </li>
</ul>

<div class="tab-content mt-3" id="relatorioTabContent">
    <!-- Relatório por Cliente -->
    <div class="tab-pane fade show active" id="relatorio-clientes">
        <div class="card-relatorio">
            <h5>Fechamento por Cliente</h5>
            <div class="table-responsive">
                <table id="tblRelatorioClientes" class="table table-striped w-100">
                    <thead class="table-primary">
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th class="text-center">Total de Ordens</th>
                            <th class="text-end">Valor Total</th>
                            <th class="text-end">Faturado</th>
                            <th class="text-end">Pendente</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Relatório por Consultor -->
    <div class="tab-pane fade" id="relatorio-consultores">
        <div class="card-relatorio">
            <h5>Fechamento por Consultor</h5>
            <div class="table-responsive">
                <table id="tblRelatorioConsultores" class="table table-striped w-100">
                    <thead class="table-primary">
                        <tr>
                            <th>Consultor</th>
                            <th class="text-center">Total de Ordens</th>
                            <th class="text-end">Valor Total</th>
                            <th class="text-end">Faturado</th>
                            <th class="text-end">Pendente</th>
                            <th class="text-end">Ticket Médio</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Relatório por Status -->
    <div class="tab-pane fade" id="relatorio-status">
        <div class="card-relatorio">
            <h5>Ordens por Status</h5>
            <div class="table-responsive">
                <table id="tblRelatorioStatus" class="table table-striped w-100">
                    <thead class="table-primary">
                        <tr>
                            <th>Status</th>
                            <th class="text-center">Quantidade</th>
                            <th class="text-end">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/relatorios.js') }}"></script>
@endpush
