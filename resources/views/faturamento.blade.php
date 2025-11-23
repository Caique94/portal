<?php
use Illuminate\Support\Facades\Auth;
?>

@extends('layout.master')

@section('title', '- Faturamento')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css')}}" rel="stylesheet">
@endpush

<script>
    // Pass user role to JavaScript
    window.userRole = '{{ $user->papel ?? "admin" }}';
</script>

@section('content')

    <h4>FATURAMENTO</h4>

    <div class="mt-3">
        <div class="table-responsive">
            <table id="tblFaturamento" class="table w-100"></table>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/faturamento.js') }}"></script>
@endpush

@section('modal')

    <div class="modal fade" id="modalEmissaoRPS" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalEmissaoRPSLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalEmissaoRPSLabel">Emitir RPS</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEmissaoRPS" action="#" method="POST">
                        <input type="hidden" name="txtEmissaoRPSClienteId" id="txtEmissaoRPSClienteId" />
                        <input type="hidden" name="txtEmissaoRPSOrdens" id="txtEmissaoRPSOrdens" />
                        <input type="hidden" name="txtEmissaoRPSValor" id="txtEmissaoRPSValor" />

                        <div class="d-none" id="divMsgOrdens">
                            <span class="" id="spanMsgOrdens"></span>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <div class="input-group">
                                    <div class="form-floating f-g-4">
                                        <input type="number" name="txtEmissaoRPSNumero" id="txtEmissaoRPSNumero" class="form-control" placeholder="N&uacute;mero" required />
                                        <label for="txtEmissaoRPSNumero">N&uacute;mero</label>
                                    </div>
                                    <div class="form-floating">
                                        <input type="number" name="txtEmissaoRPSSerie" id="txtEmissaoRPSSerie" class="form-control" placeholder="S&eacute;rie" required />
                                        <label for="txtEmissaoRPSSerie">S&eacute;rie</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="date" name="txtEmissaoRPSEmissao" id="txtEmissaoRPSEmissao" class="form-control" placeholder="Data Emiss&atilde;o" required />
                                <label for="txtEmissaoRPSEmissao">Data Emiss&atilde;o</label>
                            </div>
                            <div class="form-floating mb-3 col-md-3">
                                <select name="slcEmissaoRPSCondPagto" id="slcEmissaoRPSCondPagto" class="form-select" required>
                                    <option value="">Carregando...</option>
                                </select>
                                <label for="slcEmissaoRPSCondPagto">Condi&ccedil;&atilde;o de Pagamento</label>
                            </div>
                            <div class="form-floating mb-3 col-md-3">
                                <input type="text" id="txtEmissaoRPSValorMascarado" class="form-control text-right" placeholder="Valor" readonly required />
                                <label for="txtEmissaoRPSValorMascarado">Valor</label>
                            </div>
                        </div>

                        <!-- Seção de Parcelas (visível apenas para parcelado) -->
                        <div id="divConfiguracaoParcelas" style="display: none;" class="border-top pt-3 mt-3">
                            <h6 class="mb-3"><i class="bi bi-cash-stack"></i> Configuração de Parcelas</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="date" name="txtDataPrimeiraParcela" id="txtDataPrimeiraParcela" class="form-control" />
                                        <label>Data da 1ª Parcela</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="number" name="txtIntervaloDiasParcelas" id="txtIntervaloDiasParcelas" class="form-control" value="30" min="1" />
                                        <label>Intervalo entre Parcelas (dias)</label>
                                    </div>
                                </div>
                            </div>

                            <div id="previewParcelasRPS" class="alert alert-info mb-3" style="display: none;">
                                <h6 class="mb-2">Preview das Parcelas:</h6>
                                <ul id="listaPreviewParcelasRPS" class="mb-0"></ul>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="chkConsolidaRPS" />
                                <label class="form-check-label" for="chkConsolidaRPS">
                                    <strong>Consolidar em único pagamento</strong> - Gera uma única parcela com o valor total das ordens
                                </label>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-salvar-rps">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== NOVO MODAL: Seleção de Clientes ===== -->
    <div class="modal fade" id="modalSelecionarCliente" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selecionar Cliente para Emissão de RPS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <!-- Campo de busca -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="inputBuscaCliente" class="form-control" placeholder="Buscar cliente por nome ou código...">
                        </div>
                    </div>

                    <!-- Lista de clientes -->
                    <div class="list-group" id="listaClientesRPS" style="max-height: 400px; overflow-y: auto;">
                        <div class="list-group-item text-muted">
                            <small>Carregando clientes...</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== FIM NOVO MODAL ===== -->

    <!-- ===== NOVO MODAL: Seleção de Clientes para Faturamento ===== -->
    <div class="modal fade" id="modalSelecionarClienteFaturamento" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selecionar Cliente para Faturamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="inputBuscaClienteFaturamento" class="form-control" placeholder="Buscar cliente por nome ou código...">
                        </div>
                    </div>
                    <div class="list-group" id="listaClientesFaturamento" style="max-height: 400px; overflow-y: auto;">
                        <div class="list-group-item text-muted">
                            <small>Carregando clientes...</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== FIM MODAL FATURAMENTO ===== -->

    <!-- ===== MODAL: Reenviar Email de OS ===== -->
    <div class="modal fade" id="modalReenviarEmailOS" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalReenviarEmailOSLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReenviarEmailOSLabel">
                        <i class="bi bi-envelope"></i> Reenviar Email de OS
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="txtOSIdReenvio" />

                    <p class="text-muted mb-4">
                        Selecione para quem você deseja reenviar o email da Ordem de Serviço:
                    </p>

                    <div class="btn-group-vertical w-100" role="group">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="opcaoReenvio" id="opcaoConsultor" value="consultor" />
                            <label class="form-check-label w-100" for="opcaoConsultor">
                                <strong><i class="bi bi-person"></i> Apenas para o Consultor</strong>
                                <small class="d-block text-muted">Envia apenas para o consultor responsável pela OS</small>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="opcaoReenvio" id="opcaoCliente" value="cliente" />
                            <label class="form-check-label w-100" for="opcaoCliente">
                                <strong><i class="bi bi-building"></i> Apenas para o Cliente</strong>
                                <small class="d-block text-muted">Envia apenas para o cliente contato da OS</small>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="opcaoReenvio" id="opcaoAmbos" value="ambos" checked />
                            <label class="form-check-label w-100" for="opcaoAmbos">
                                <strong><i class="bi bi-people"></i> Para Consultor e Cliente</strong>
                                <small class="d-block text-muted">Envia para ambos (consultor e cliente)</small>
                            </label>
                        </div>
                    </div>

                    <div id="divAlertaReenvio" class="alert alert-info mt-4 d-none" role="alert">
                        <small id="textoAlertaReenvio"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnReenviarEmail">
                        <i class="bi bi-send"></i> Reenviar Email
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== FIM MODAL REENVIAR EMAIL ===== -->

@endsection
