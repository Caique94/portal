@extends('layout.master')

@section('title', '- Condições de Pagamento')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css')}}" rel="stylesheet">
@endpush

@section('content')

    <h4>CONDIÇÕES DE PAGAMENTO</h4>

    <div class="mt-3">
        <div class="table-responsive">
            <table id="tblCondicoesPagamento" class="table w-100"></table>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/cadastros/condicoes-pagamento.js') }}"></script>
@endpush

@section('modal')

    <div class="modal fade" id="modalCondicaoPagamento" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalCondicaoPagamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalCondicaoPagamentoLabel">Adicionar Condição de Pagamento</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCondicaoPagamento" action="#" method="post">
                        <div class="form-floating mb-3">
                            <input type="text" name="txtCondicaoDescricao" id="txtCondicaoDescricao" class="form-control" placeholder="Descrição" required />
                            <label for="txtCondicaoDescricao">Descrição</label>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-6">
                                <input type="number" name="txtCondicaoNumeroParcelas" id="txtCondicaoNumeroParcelas" class="form-control" placeholder="Número de Parcelas" min="1" max="12" required />
                                <label for="txtCondicaoNumeroParcelas">Número de Parcelas</label>
                            </div>
                            <div class="form-floating mb-3 col-md-6">
                                <input type="number" name="txtCondicaoIntervaloDias" id="txtCondicaoIntervaloDias" class="form-control" placeholder="Intervalo (dias)" min="0" required />
                                <label for="txtCondicaoIntervaloDias">Intervalo (dias)</label>
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input type="checkbox" name="chkCondicaoAtivo" id="chkCondicaoAtivo" class="form-check-input" checked />
                            <label class="form-check-label" for="chkCondicaoAtivo">
                                Ativo
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-salvar-condicao-pagamento">Salvar</button>
                </div>
            </div>
        </div>
    </div>

@endsection
