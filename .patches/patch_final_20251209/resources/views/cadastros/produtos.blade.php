@extends('layout.master')

@section('title', '- Produtos')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css')}}" rel="stylesheet">
@endpush

@section('content')

    <h4>PRODUTOS</h4>

    <div class="mt-3">
        <div class="table-responsive">
            <table id="tblProdutos" class="table w-100"></table>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/cadastros/produtos.js') }}"></script>
@endpush

@section('modal')

    <div class="modal fade" id="modalProduto" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalProdutoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalProdutoLabel">Adicionar Produto</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formProduto" action="#" method="post">
                        <!-- id p/ edição -->
                        <input type="hidden" name="id" id="produto_id">

                        <div class="row">
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="codigo" id="txtProdutoCodigo" class="form-control" placeholder="C&oacute;digo" readonly />
                                <label for="txtProdutoCodigo">C&oacute;digo</label>
                            </div>
                            <div class="form-floating mb-3 col-md-6">
                                <input type="text" name="nome" id="txtProdutoDescricao" class="form-control" placeholder="Descri&ccedil;&atilde;o" required />
                                <label for="txtProdutoDescricao">Descri&ccedil;&atilde;o</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2 d-flex align-items-center justify-content-center">
                                <div class="form-check form-switch form-check-reverse">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_presencial" id="chkProdutoPresencial" value="1">
                                    <label class="form-check-label" for="chkProdutoPresencial">Presencial</label>
                                </div>
                            </div>
                            <div class="form-floating mb-3 col-md-2 d-flex align-items-center justify-content-end">
                                <div class="form-check form-switch form-check-reverse">
                                    <input class="form-check-input" type="checkbox" role="switch" name="ativo" id="chkProdutoAtivo" value="1" checked>
                                    <label class="form-check-label" for="chkProdutoAtivo">Ativo</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-12">
                                <textarea name="narrativa" id="txtProdutoNarrativa" class="form-control" style="height: 100px;"></textarea>
                                <label for="txtProdutoNarrativa">Narrativa</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-salvar-produto">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

@endsection
