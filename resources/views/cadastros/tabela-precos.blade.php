@extends('layout.master')

@section('title', '- Tabela de Preços')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css')}}" rel="stylesheet">
<link href="{{ asset('plugins/select2/select2.min.css')}}" rel="stylesheet">
<link href="{{ asset('plugins/select2/select2-bootstrap-5-theme.min.css')}}" rel="stylesheet">
@endpush

@section('content')

    <h4>TABELA DE PRE&Ccedil;OS</h4>

    <div class="mt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span></span>
            <button type="button" class="btn btn-primary btn-adicionar-tabela-precos">
                <i class="bi bi-plus-circle"></i> Nova Tabela de Preços
            </button>
        </div>
        <div class="table-responsive">
            <table id="tblTabelasPrecos" class="table w-100"></table>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/select2/i18n/pt-BR.js') }}"></script>
<script src="{{ asset('js/cadastros/tabela-precos.js') }}"></script>
@endpush

@section('modal')

    <div class="modal fade" id="modalTabelaPrecos" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalTabelaPrecosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalTabelaPrecosLabel">Adicionar Tabela de Pre&ccedil;os</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTabelaPrecos" action="#" method="post">
                        <div class="row">
                            <div class="form-floating mb-3 col-md-12">
                                <input type="text" name="txtTabelaPrecoDescricao" id="txtTabelaPrecoDescricao" class="form-control" placeholder="Descri&ccedil;&atilde;o" required />
                                <label for="txtTabelaPrecoDescricao">Descri&ccedil;&atilde;o</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-salvar-tabela-precos">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Listar Produtos -->
    <div class="modal fade" id="modalProdutosTabela" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalProdutosTabelaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalProdutosTabelaLabel">Produtos</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="table-responsive">
                        <table id="tblProdutosTabela" class="table w-100"></table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-target="#modalProdutoTabela" data-bs-toggle="modal">Adicionar Produto</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Adicionar Produto -->
    <div class="modal fade" id="modalProdutoTabela" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalProdutoTabelaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalProdutoTabelaLabel">Produto</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formProdutoTabela" action="#" method="post">
                        <input type="hidden" name="txtProdutoTabelaTabelaPrecoId" id="txtProdutoTabelaTabelaPrecoId" />
                        <div class="row">
                            <div class="form-floating mb-3 col-md-9">
                                <select name="slcProdutoTabelaProdutoId" id="slcProdutoTabelaProdutoId" class="form-select" required></select>
                                <label for="slcProdutoTabelaProdutoId">Produto</label>
                            </div>
                            <div class="form-floating mb-3 col-md-3">
                                <input type="text" name="txtProdutoTabelaPreco" id="txtProdutoTabelaPreco" class="form-control money" placeholder="Pre&ccedil;o" required />
                                <label for="txtProdutoTabelaPreco">Pre&ccedil;o</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-salvar-produto-tabela">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

@endsection
