@extends('layout.master')

@section('title', '- Clientes')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css')}}" rel="stylesheet">
<link href="{{ asset('plugins/select2/select2.min.css')}}" rel="stylesheet">
<link href="{{ asset('plugins/select2/select2-bootstrap-5-theme.min.css')}}" rel="stylesheet">
@endpush

@section('content')

    <h4>CLIENTES</h4>

    <div class="mt-3">
        <div class="table-responsive">
            <table id="tblClientes" class="table w-100"></table>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/select2/i18n/pt-BR.js') }}"></script>
<script src="{{ asset('js/cadastros/clientes.js') }}"></script>
@endpush

@section('modal')

    <div class="modal fade" id="modalCliente" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalClienteLabel">Adicionar Cliente</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCliente" action="#" method="post">
                        <div class="row">
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtClienteCodigo" id="txtClienteCodigo" class="form-control" placeholder="C&oacute;digo" readonly />
                                <label for="txtClienteCodigo">C&oacute;digo</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtClienteLoja" id="txtClienteLoja" class="form-control" placeholder="Loja" required />
                                <label for="txtClienteLoja">Loja</label>
                            </div>
                            <div class="form-floating mb-3 col-md-8">
                                <input type="text" name="txtClienteNome" id="txtClienteNome" class="form-control" placeholder="Nome" required />
                                <label for="txtClienteNome">Nome</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-6">
                                <input type="text" name="txtClienteNomeFantasia" id="txtClienteNomeFantasia" class="form-control" placeholder="Nome Fantasia" />
                                <label for="txtClienteNomeFantasia">Nome Fantasia</label>
                            </div>
                            <div class="form-floating mb-3 col-md-6">
                                <input type="text" name="txtClienteTipo" id="txtClienteTipo" class="form-control" placeholder="Tipo" />
                                <label for="txtClienteTipo">Tipo</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-4">
                                <input type="text" name="txtClienteCGC" id="txtClienteCGC" class="form-control cpf-cnpj" placeholder="CPF/CNPJ" />
                                <label for="txtClienteCGC">CPF/CNPJ</label>
                            </div>
                            <div class="form-floating mb-3 col-md-5">
                                <select name="txtClienteContato" id="txtClienteContato" class="form-select"></select>
                                <label for="txtClienteContato">Contato Principal</label>
                            </div>
                            <div class="col-md-2 d-flex align-items-end mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnAdicionarContatoRapido" data-bs-toggle="modal" data-bs-target="#modalContato">
                                    <i class="bi bi-person-plus"></i> Adicionar
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end mb-3 justify-content-end">
                                <span class="badge bg-info" id="badgeContatoCount" style="display:none; height:fit-content;">0 contatos</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-3">
                                <input type="text" name="txtClienteCEP" id="txtClienteCEP" class="form-control mask-cep" placeholder="CEP" data-bs-toggle="tooltip" data-bs-title="Digite o CEP para preencher automaticamente os dados de endere&ccedil;o" data-bs-trigger="hover" data-bs-placement="top" />
                                <label for="txtClienteCEP">CEP</label>
                            </div>
                            <div class="form-floating mb-3 col-md-9">
                                <input type="text" name="txtClienteEndereco" id="txtClienteEndereco" class="form-control" placeholder="Endere&ccedil;o" />
                                <label for="txtClienteEndereco">Endere&ccedil;o</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-6">
                                <input type="text" name="txtClienteEstado" id="txtClienteEstado" class="form-control" placeholder="Estado" />
                                <label for="txtClienteEstado">Estado</label>
                            </div>
                            <div class="form-floating mb-3 col-md-6">
                                <input type="text" name="txtClienteCidade" id="txtClienteCidade" class="form-control" placeholder="Munic&iacute;pio" />
                                <label for="txtClienteCidade">Munic&iacute;pio</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-6">
                                <select name="slcClienteTabelaPrecos" id="slcClienteTabelaPrecos" class="form-select" required></select>
                                <label for="slcClienteTabelaPrecos">Tabela de Pre&ccedil;os</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtClienteKm" id="txtClienteKm" class="form-control mask-km" placeholder="KM" data-bs-toggle="tooltip" data-bs-title="Quantidade de KMs de deslocamento at&eacute; o cliente" data-bs-trigger="hover" data-bs-placement="top" />
                                <label for="txtClienteKm">KM</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtClienteDeslocamento" id="txtClienteDeslocamento" class="form-control mask-deslocamento" placeholder="Deslocamento" data-bs-toggle="tooltip" data-bs-title="Quantidade de KMs de deslocamento at&eacute; o cliente" data-bs-trigger="hover" data-bs-placement="top" />
                                <label for="txtClienteDeslocamento">Deslocamento</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-salvar-cliente">Salvar</button>
                    <button type="button" class="btn btn-secondary btn-fechar-e-concluir" style="display:none;">Fechar e Concluir</button>
                    <button type="button" class="btn btn-secondary btn-fechar-modal" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Listar Contatos -->
    <div class="modal fade" id="modalContatos" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalContatosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalContatosLabel">Contatos</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="table-responsive">
                        <table id="tblContatos" class="table w-100"></table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-target="#modalContato" data-bs-toggle="modal">Adicionar Contato</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Adicionar Contato -->
    <div class="modal fade" id="modalContato" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalContatoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalContatoLabel">Contato Adicional</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formContato" action="#" method="post">
                        <input type="hidden" name="txtContatoClienteId" id="txtContatoClienteId" />
                        <div class="row">
                            <div class="form-floating mb-3 col-md-12">
                                <input type="text" name="txtContatoNome" id="txtContatoNome" class="form-control" placeholder="Contato" required />
                                <label for="txtContatoNome">Contato</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-6">
                                <input type="email" name="txtContatoEmail" id="txtContatoEmail" class="form-control" placeholder="Email" />
                                <label for="txtContatoEmail">Email</label>
                            </div>
                            <div class="form-floating mb-3 col-md-4">
                                <input type="text" name="txtContatoTelefone" id="txtContatoTelefone" class="form-control" placeholder="Telefone" />
                                <label for="txtContatoTelefone">Telefone</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtContatoAniversario" id="txtContatoAniversario" class="form-control mask-aniversario" placeholder="Anivers&aacute;rio" />
                                <label for="txtContatoAniversario">Anivers&aacute;rio</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="chkContatoRecebeEmailOS" name="chkContatoRecebeEmailOS" checked>
                                    <label class="form-check-label" for="chkContatoRecebeEmailOS">
                                        Recebe e-mail de Ordem de Servi&ccedil;o
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-salvar-contato">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

@endsection
