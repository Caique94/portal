@extends("layout.master")

@section('title', '- Recibo Provisório')

@section('content')

<div class="container-fluid">
    <h4>RECIBO PROVISÓRIO</h4>

    <!-- Tabs for different statuses -->
    <ul class="nav nav-tabs" id="reciboTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-todos" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab">
                Todos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-quitado" data-bs-toggle="tab" data-bs-target="#quitado" type="button" role="tab">
                Quitados
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-aberto" data-bs-toggle="tab" data-bs-target="#aberto" type="button" role="tab">
                Abertos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-atraso" data-bs-toggle="tab" data-bs-target="#atraso" type="button" role="tab">
                Em Atraso
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="reciboTabContent">
        <!-- Todos os Recibos -->
        <div class="tab-pane fade show active" id="todos" role="tabpanel" aria-labelledby="tab-todos">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tblReciboProvisorio" class="table table-striped mb-0 w-100"></table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recibos Quitados -->
        <div class="tab-pane fade" id="quitado" role="tabpanel" aria-labelledby="tab-quitado">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tblReciboQuitado" class="table table-striped mb-0 w-100"></table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recibos Abertos -->
        <div class="tab-pane fade" id="aberto" role="tabpanel" aria-labelledby="tab-aberto">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tblReciboAberto" class="table table-striped mb-0 w-100"></table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recibos em Atraso -->
        <div class="tab-pane fade" id="atraso" role="tabpanel" aria-labelledby="tab-atraso">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tblReciboAtraso" class="table table-striped mb-0 w-100"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('modal')

<!-- Modal Gerenciar Parcelas -->
<div class="modal fade" id="modalGerenciarParcelas" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalGerenciarParcelasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGerenciarParcelasLabel">Gerenciar Parcelas - RPS <span id="spanNumeroRPS"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tblParcelas" class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Parcela</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                                <th>Pagamento</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="txtParcelaReciboId" />

@endsection

@push('scripts')
<script src="{{ asset('js/recibo-provisorio.js') }}"></script>
@endpush
