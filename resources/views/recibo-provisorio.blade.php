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

<!-- Modal Editar Parcela -->
<div class="modal fade" id="modalEditarParcela" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalEditarParcelaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarParcelaLabel">Editar Parcela</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarParcela">
                    <input type="hidden" id="txtEditarParcelaId" />

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="txtEditarValor" class="form-label">Valor da Parcela *</label>
                            <input type="text" class="form-control" id="txtEditarValor" placeholder="0,00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="slcEditarStatus" class="form-label">Status *</label>
                            <select class="form-select" id="slcEditarStatus" required>
                                <option value="pendente">Pendente</option>
                                <option value="paga">Paga</option>
                                <option value="atrasada">Atrasada</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="txtEditarDataVencimento" class="form-label">Data de Vencimento *</label>
                            <input type="date" class="form-control" id="txtEditarDataVencimento" required>
                        </div>
                        <div class="col-md-6">
                            <label for="txtEditarDataPagamento" class="form-label">Data de Pagamento</label>
                            <input type="date" class="form-control" id="txtEditarDataPagamento">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="txtEditarObservacao" class="form-label">Observação</label>
                        <textarea class="form-control" id="txtEditarObservacao" rows="3"></textarea>
                    </div>

                    <!-- Validação de totais -->
                    <div id="alertValidacaoPagamento" class="alert alert-info d-none mb-3">
                        <strong>Resumo de Valores:</strong><br>
                        Valor da RPS: <span id="spanValorRps">R$ 0,00</span><br>
                        Total das Parcelas: <span id="spanTotalParcelas">R$ 0,00</span><br>
                        Diferença: <span id="spanDiferenca">R$ 0,00</span>
                    </div>

                    <div id="alertAvisoValidacao" class="alert alert-warning d-none mb-3">
                        <span id="textoAvisoValidacao"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarParcela">Salvar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/recibo-provisorio.js') }}"></script>
@endpush
