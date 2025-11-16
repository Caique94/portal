<?php
use Illuminate\Support\Facades\Auth;
?>

@extends('layout.master')

@section('title', '- Faturamento')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css')}}" rel="stylesheet">
@endpush

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

@endsection
