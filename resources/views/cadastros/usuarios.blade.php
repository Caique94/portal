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
<script src="{{ asset('js/validators/cpf-validator.js') }}"></script>
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

                        <!-- Nav tabs para diferentes seções do formulário -->
                        <ul class="nav nav-tabs mb-3" id="tabsUsuario" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-dados-pessoais" data-bs-toggle="tab" data-bs-target="#pane-dados-pessoais" type="button" role="tab" aria-controls="pane-dados-pessoais" aria-selected="true">
                                    <i class="bi bi-person"></i> Dados Pessoais
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-pessoa-juridica" data-bs-toggle="tab" data-bs-target="#pane-pessoa-juridica" type="button" role="tab" aria-controls="pane-pessoa-juridica" aria-selected="false">
                                    <i class="bi bi-building"></i> Pessoa Jurídica
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-dados-pagamento" data-bs-toggle="tab" data-bs-target="#pane-dados-pagamento" type="button" role="tab" aria-controls="pane-dados-pagamento" aria-selected="false">
                                    <i class="bi bi-credit-card"></i> Dados de Pagamento
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="tabsUsuarioContent">
                            <!-- TAB 1: DADOS PESSOAIS -->
                            <div class="tab-pane fade show active" id="pane-dados-pessoais" role="tabpanel" aria-labelledby="tab-dados-pessoais">
                        <div class="row">
                            <div class="form-floating mb-3 col-md-10">
                                <input type="text" name="txtUsuarioNome" id="txtUsuarioNome" class="form-control" placeholder="Nome" required />
                                <label for="txtUsuarioNome">Nome</label>
                            </div>
                            <div class="form-floating mb-3 col-md-2">
                                <input type="date" name="txtUsuarioDataNasc" id="txtUsuarioDataNasc" class="form-control" placeholder="Data Nasc." />
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
                                <input type="text" name="txtUsuarioCPF" id="txtUsuarioCPF" class="form-control cpf" placeholder="CPF" />
                                <label for="txtUsuarioCPF">CPF</label>
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
                            </div>

                            <!-- TAB 2: PESSOA JURÍDICA -->
                            <div class="tab-pane fade" id="pane-pessoa-juridica" role="tabpanel" aria-labelledby="tab-pessoa-juridica">
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-6">
                                        <input type="text" name="txtPJCNPJ" id="txtPJCNPJ" class="form-control cnpj" placeholder="CNPJ" />
                                        <label for="txtPJCNPJ">CNPJ</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-6">
                                        <input type="text" name="txtPJRazaoSocial" id="txtPJRazaoSocial" class="form-control" placeholder="Razão Social" />
                                        <label for="txtPJRazaoSocial">Razão Social</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-6">
                                        <input type="text" name="txtPJNomeFantasia" id="txtPJNomeFantasia" class="form-control" placeholder="Nome Fantasia" />
                                        <label for="txtPJNomeFantasia">Nome Fantasia</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="text" name="txtPJInscricaoEstadual" id="txtPJInscricaoEstadual" class="form-control" placeholder="Inscrição Estadual" />
                                        <label for="txtPJInscricaoEstadual">Inscrição Estadual</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="text" name="txtPJInscricaoMunicipal" id="txtPJInscricaoMunicipal" class="form-control" placeholder="Inscrição Municipal" />
                                        <label for="txtPJInscricaoMunicipal">Inscrição Municipal</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="text" name="txtPJCEP" id="txtPJCEP" class="form-control mask-cep" placeholder="CEP" />
                                        <label for="txtPJCEP">CEP</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-6">
                                        <input type="text" name="txtPJEndereco" id="txtPJEndereco" class="form-control" placeholder="Endereço" />
                                        <label for="txtPJEndereco">Endereço</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="text" name="txtPJNumero" id="txtPJNumero" class="form-control" placeholder="Número" />
                                        <label for="txtPJNumero">Número</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-12">
                                        <input type="text" name="txtPJComplemento" id="txtPJComplemento" class="form-control" placeholder="Complemento" />
                                        <label for="txtPJComplemento">Complemento</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-4">
                                        <input type="text" name="txtPJBairro" id="txtPJBairro" class="form-control" placeholder="Bairro" />
                                        <label for="txtPJBairro">Bairro</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-5">
                                        <input type="text" name="txtPJCidade" id="txtPJCidade" class="form-control" placeholder="Cidade/Município" maxlength="255" />
                                        <label for="txtPJCidade">Cidade/Município</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="text" name="txtPJEstado" id="txtPJEstado" class="form-control" placeholder="Estado" maxlength="255" />
                                        <label for="txtPJEstado">Estado</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="text" name="txtPJTelefone" id="txtPJTelefone" class="form-control phone" placeholder="Telefone" />
                                        <label for="txtPJTelefone">Telefone</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="email" name="txtPJEmail" id="txtPJEmail" class="form-control" placeholder="Email" />
                                        <label for="txtPJEmail">Email</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="text" name="txtPJSite" id="txtPJSite" class="form-control" placeholder="Site" />
                                        <label for="txtPJSite">Site</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-6">
                                        <input type="text" name="txtPJRamoAtividade" id="txtPJRamoAtividade" class="form-control" placeholder="Ramo de Atividade" />
                                        <label for="txtPJRamoAtividade">Ramo de Atividade</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-6">
                                        <input type="date" name="txtPJDataConstituicao" id="txtPJDataConstituicao" class="form-control" />
                                        <label for="txtPJDataConstituicao">Data de Constituição</label>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: DADOS DE PAGAMENTO -->
                            <div class="tab-pane fade" id="pane-dados-pagamento" role="tabpanel" aria-labelledby="tab-dados-pagamento">
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-8">
                                        <input type="text" name="txtPagTitularConta" id="txtPagTitularConta" class="form-control" placeholder="Titular da Conta" />
                                        <label for="txtPagTitularConta">Titular da Conta</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-4">
                                        <input type="text" name="txtPagCpfCnpjTitular" id="txtPagCpfCnpjTitular" class="form-control cpf-cnpj" placeholder="CPF/CNPJ do Titular" />
                                        <label for="txtPagCpfCnpjTitular">CPF/CNPJ</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-4">
                                        <input type="text" name="txtPagBanco" id="txtPagBanco" class="form-control" placeholder="Banco" required />
                                        <label for="txtPagBanco">Banco</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="text" name="txtPagAgencia" id="txtPagAgencia" class="form-control" placeholder="Agência" required />
                                        <label for="txtPagAgencia">Agência</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-3">
                                        <input type="text" name="txtPagConta" id="txtPagConta" class="form-control" placeholder="Conta" required />
                                        <label for="txtPagConta">Conta</label>
                                    </div>
                                    <div class="form-floating mb-3 col-md-2">
                                        <select name="slcPagTipoConta" id="slcPagTipoConta" class="form-select" required>
                                            <option value="">Tipo</option>
                                            <option value="corrente">Corrente</option>
                                            <option value="poupanca">Poupança</option>
                                        </select>
                                        <label for="slcPagTipoConta">Tipo Conta</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating mb-3 col-md-12">
                                        <input type="text" name="txtPagPixKey" id="txtPagPixKey" class="form-control" placeholder="Chave PIX (opcional)" />
                                        <label for="txtPagPixKey">Chave PIX (opcional)</label>
                                    </div>
                                </div>
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
