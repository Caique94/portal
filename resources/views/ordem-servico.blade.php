<?php
use Illuminate\Support\Facades\Auth;
?>

@extends('layout.master')

@section('title', '- Ordem de Serviço')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css')}}" rel="stylesheet">
<link href="{{ asset('plugins/select2/select2.min.css')}}" rel="stylesheet">
<link href="{{ asset('plugins/select2/select2-bootstrap-5-theme.min.css')}}" rel="stylesheet">
@endpush

@section('content')

    <h4>ORDEM DE SERVI&Ccedil;O</h4>

    <!-- incluir cards com graficos -->

    <div class="mt-3">
        <div class="table-responsive">
            <table id="tblOrdensServico" class="table w-100"></table>
        </div>
    </div>

    <script>
        const papel = '{{ auth()->user()->papel }}';
        const user_id = '{{ $user->id }}';
        const user_name = '{{ $user->name }}';
    </script>

@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/select2/i18n/pt-BR.js') }}"></script>
<script src="{{ asset('js/ordem-servico.js') }}"></script>
<script src="{{ asset('js/projetos.js') }}"></script>
@endpush

@section('modal')

    <div class="modal fade" id="modalOrdemServico" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalOrdemServicoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalOrdemServicoLabel">Ordem de Servi&ccedil;o</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formOrdemServico" action="#" method="post">
                        <input type="hidden" name="txtOrdemPrecoProduto" id="txtOrdemPrecoProduto" class="calculo-valor-total" />
                        <input type="hidden" name="txtOrdemValorTotal" id="txtOrdemValorTotal" />
                        <div class="row">
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtOrdemId" id="txtOrdemId" class="form-control" placeholder="C&oacute;digo" readonly />
                                <label for="txtOrdemId">C&oacute;digo</label>
                            </div>
                            <div class="form-floating mb-3 col-md-10">
                                <input type="hidden" name="txtOrdemConsultorId" id="txtOrdemConsultorId" />
                                <input type="text" id="txtOrdemConsultor" class="form-control" placeholder="Consultor" required readonly />
                                <label for="txtOrdemConsultor">Consultor</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-10">
                                <select name="slcOrdemClienteId" id="slcOrdemClienteId" class="form-select" required></select>
                                <label for="slcOrdemClienteId">Cliente</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="date" name="txtOrdemDataEmissao" id="txtOrdemDataEmissao" class="form-control" placeholder="Data Emiss&atilde;o" />
                                <label for="txtOrdemDataEmissao">Data Emiss&atilde;o</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-group mb-3 col-md-12">
                                <div class="form-floating">
                                    <select name="slcOrdemTipoDespesa" id="slcOrdemTipoDespesa" class="form-select" placeholder="Tipo Despesa">
                                        <option value="">Selecionar ...</option>
                                        <option value="refeicao">Refei&ccedil;&atilde;o</option>
                                        <option value="outros">Outros</option>
                                    </select>
                                    <label for="slcOrdemTipoDespesa">Tipo Despesa</label>
                                </div>
                                <div class="form-floating">
                                    <input type="text" name="txtOrdemDespesas" id="txtOrdemDespesas" class="form-control money calculo-valor-total" placeholder="Despesas" />
                                    <label for="txtOrdemDespesas">Despesas</label>
                                </div>
                                <div class="form-floating f-g-4">
                                    <input type="text" name="txtOrdemDespesasDetalhamento" id="txtOrdemDespesasDetalhamento" class="form-control" placeholder="Detalhamento" readonly />
                                    <label for="txtOrdemDespesasDetalhamento">Detalhamento</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-12">
                                <select name="slcProdutoOrdemId" id="slcProdutoOrdemId" class="form-select" required></select>
                                <label for="slcProdutoOrdemId">Produto</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-3">
                                <input type="time" name="txtProdutoOrdemHoraInicio" id="txtProdutoOrdemHoraInicio" class="form-control calculo-horas" placeholder="Hora In&iacute;cio" />
                                <label for="txtProdutoOrdemHoraInicio">Hora In&iacute;cio</label>
                            </div>
                            <div class="form-floating mb-3 col-md-3">
                                <input type="time" name="txtProdutoOrdemHoraFinal" id="txtProdutoOrdemHoraFinal" class="form-control calculo-horas" placeholder="Hora Final" />
                                <label for="txtProdutoOrdemHoraFinal">Hora Final</label>
                            </div>
                            <div class="form-floating mb-3 col-md-3">
                                <input type="time" name="txtProdutoOrdemHoraDesconto" id="txtProdutoOrdemHoraDesconto" class="form-control calculo-horas" placeholder="Hora Desconto" />
                                <label for="txtProdutoOrdemHoraDesconto">Hora Desconto</label>
                            </div>
                            <div class="form-floating mb-3 col-md-3">
                                <input type="text" name="txtProdutoOrdemQtdeTotal" id="txtProdutoOrdemQtdeTotal" class="form-control calculo-valor-total" placeholder="Qtde Total" readonly />
                                <label for="txtProdutoOrdemQtdeTotal">Qtde Total</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-5">
                                <input type="text" name="txtOrdemAssunto" id="txtOrdemAssunto" class="form-control" placeholder="Assunto" />
                                <label for="txtOrdemAssunto">Assunto</label>
                            </div>
                            <div class="form-floating mb-3 col-md-4">
                                <select name="projeto_id" id="slcOrdemProjetoId" class="form-select" placeholder="Selecione um projeto">
                                    <option value="">Selecione um projeto</option>
                                </select>
                                <label for="slcOrdemProjetoId">Projeto</label>
                            </div>
                            <div class="form-floating mb-3 col-md-3">
                                <input type="text" name="txtOrdemNrAtendimento" id="txtOrdemNrAtendimento" class="form-control" placeholder="Nr. Atendimento" />
                                <label for="txtOrdemNrAtendimento">Nr. Atendimento</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-12">
                                <textarea name="txtProdutoOrdemDetalhamento" id="txtProdutoOrdemDetalhamento" class="form-control" style="height: 100px;"></textarea>
                                <label for="txtProdutoOrdemDetalhamento">Detalhamento</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><label for="chkOrdemPresencial">Presencial</label></span>
                                    <div class="input-group-text"><input class="form-check-input calculo-valor-total" type="checkbox" name="chkOrdemPresencial" id="chkOrdemPresencial" value="1"></div>
                                    <div class="form-floating">
                                        <input type="text" id="txtOrdemKM" class="form-control money calculo-valor-total" placeholder="KM" disabled />
                                        <label for="txtOrdemKM">KM</label>
                                    </div>
                                    <div class="form-floating">
                                        <input type="text" name="txtOrdemDeslocamento" id="txtOrdemDeslocamento" class="form-control calculo-valor-total" placeholder="HH:MM" disabled />
                                        <label for="txtOrdemDeslocamento">Deslocamento (HH:MM)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-floating mb-3 col-md-2" style="display: none;">
                                <input type="text" name="txtOrdemTotal" id="txtOrdemTotal" class="form-control money" placecholder="Total OS" />
                                <label>Total OS</label>
                            </div>
                        </div>
                        <div class="row">
                            <div>
                                <div class="table-responsive">
                                    <table id="tblProdutosOrdem" class="table w-100"></table>
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->papel === 'admin')
                        <div id="divTotalizadorAdmin" class="row mt-4" style="display: none;">
                            <div class="col-md-12">
                                <div class="card bg-light border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bi bi-calculator"></i> Totalizador - Administração</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Valor Hora Consultor:</strong></td>
                                                            <td class="text-end" id="valorHoraConsultor">R$ 0,00</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Valor KM Consultor:</strong></td>
                                                            <td class="text-end" id="valorKMConsultor">R$ 0,00</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Valor do Serviço:</strong></td>
                                                            <td class="text-end" id="totalValorServico">R$ 0,00</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Despesas:</strong></td>
                                                            <td class="text-end" id="totalDespesas">R$ 0,00</td>
                                                        </tr>
                                                        <tr id="linhaKM" style="display: none;">
                                                            <td><strong>KM:</strong></td>
                                                            <td class="text-end" id="totalKM">R$ 0,00</td>
                                                        </tr>
                                                        <tr id="linhaDeslocamento" style="display: none;">
                                                            <td><strong>Deslocamento:</strong></td>
                                                            <td class="text-end" id="totalDeslocamento">R$ 0,00</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="alert alert-success mb-0">
                                                    <h5 class="mb-1">TOTAL GERAL</h5>
                                                    <h3 class="mb-0" id="totalGeral">R$ 0,00</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-salvar-ordem-servico">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

@endsection
