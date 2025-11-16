@extends('layout.master')

@section('title', '- Usuários')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css')}}" rel="stylesheet">
@endpush

@section('content')

    <h4>USUÁRIOS</h4>

    <div class="mt-3">
        <div class="table-responsive">
            <table id="tblUsuarios" class="table w-100"></table>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/cadastros/usuarios.js') }}"></script>
@endpush

@section('modal')

    <div class="modal fade" id="modalUsuario" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalUsuarioLabel">Adicionar Usuário</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formUsuario" action="#" method="post">
                        @csrf
                        <div class="row">
                            <div class="form-floating mb-3 col-md-10">
                                <input type="text" name="txtUsuarioNome" id="txtUsuarioNome" class="form-control" placeholder="Nome" required />
                                <label for="txtUsuarioNome">Nome</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="date" name="txtUsuarioDataNasc" id="txtUsuarioDataNasc" class="form-control" placeholder="Data Nasc." required />
                                <label for="txtUsuarioDataNasc">Data Nasc.</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-6">
                                <input type="text" name="txtUsuarioEmail" id="txtUsuarioEmail" class="form-control" placeholder="Email" required />
                                <label for="txtUsuarioEmail">Email</label>
                            </div>
                            <div class="form-floating mb-3 col-md-3">
                                <input type="text" name="txtUsuarioCelular" id="txtUsuarioCelular" class="form-control phone" placeholder="Celular" />
                                <label for="txtUsuarioCelular">Celular</label>
                            </div>
                            <div class="form-floating mb-3 col-md-3">
                                <select name="slcUsuarioPapel" id="slcUsuarioPapel" class="form-select" required>
                                    <option value="">Selecione ...</option>
                                    <option value="consultor">Consultor</option>
                                    <option value="financeiro">Financeiro</option>
                                    <option value="admin">Administrador</option>
                                </select>
                                <label for="slcUsuarioPapel">Papel</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-floating mb-3 col-md-3">
                                <input type="text" name="txtUsuarioCGC" id="txtUsuarioCGC" class="form-control cpf-cnpj" placeholder="CPF/CNPJ" />
                                <label for="txtUsuarioCGC">CPF/CNPJ</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtUsuarioValorHora" id="txtUsuarioValorHora" class="form-control money" placeholder="Valor Hora" />
                                <label for="txtUsuarioValorHora">Valor Hora</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtUsuarioValorDesloc" id="txtUsuarioValorDesloc" class="form-control money" placeholder="Valor Desloc." />
                                <label for="txtUsuarioValorDesloc">Valor Desloc.</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtUsuarioValorKM" id="txtUsuarioValorKM" class="form-control money" placeholder="Valor KM" />
                                <label for="txtUsuarioValorKM">Valor KM</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="text" name="txtUsuarioSalarioBase" id="txtUsuarioSalarioBase" class="form-control money" placeholder="Salário Base" />
                                <label for="txtUsuarioSalarioBase">Salário Base</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-salvar-usuario">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para seleção de colunas ao copiar -->
    <div class="modal fade" id="modalSelecionarColunas" tabindex="-1" aria-labelledby="modalSelecionarColunasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalSelecionarColunasLabel">Selecionar Colunas para Copiar</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkColunaCopiar1" name="chkColunaCopiar" value="nome" checked>
                        <label class="form-check-label" for="chkColunaCopiar1">Nome</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkColunaCopiar2" name="chkColunaCopiar" value="email" checked>
                        <label class="form-check-label" for="chkColunaCopiar2">Email</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkColunaCopiar3" name="chkColunaCopiar" value="celular">
                        <label class="form-check-label" for="chkColunaCopiar3">Celular</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkColunaCopiar4" name="chkColunaCopiar" value="cpf_cnpj">
                        <label class="form-check-label" for="chkColunaCopiar4">CPF/CNPJ</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkColunaCopiar5" name="chkColunaCopiar" value="ativo">
                        <label class="form-check-label" for="chkColunaCopiar5">Ativo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-copiar-selecionado">Copiar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

@endsection
