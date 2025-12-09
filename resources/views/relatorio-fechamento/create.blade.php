@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Novo Relatório de Fechamento</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ isset($tipo) && $tipo === 'cliente' ? route('relatorio-fechamento-cliente.store') : route('relatorio-fechamento-consultor.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="consultor_id" class="form-label">Consultor <span class="text-danger">*</span></label>
                            <select name="consultor_id" id="consultor_id" class="form-select @error('consultor_id') is-invalid @enderror" required>
                                <option value="">Selecione um consultor...</option>
                                <option value="todos" @selected(old('consultor_id') == 'todos')>Todos os Consultores</option>
                                @foreach($consultores as $consultor)
                                    <option value="{{ $consultor->id }}" @selected(old('consultor_id') == $consultor->id)>
                                        {{ $consultor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('consultor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(isset($tipo))
                            <input type="hidden" name="tipo" value="{{ $tipo }}">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i>
                                <strong>Tipo de Fechamento:</strong>
                                @if($tipo === 'cliente')
                                    Fechamento de Cliente - Usa valores da tabela de preços do cliente (preco_produto)
                                @else
                                    Fechamento de Consultor - Usa valores cadastrados no consultor (valor_hora_consultor)
                                @endif
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo de Fechamento <span class="text-danger">*</span></label>
                                <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                    <option value="">Selecione o tipo...</option>
                                    <option value="consultor" @selected(old('tipo') == 'consultor')>
                                        Fechamento de Consultor (valores do consultor)
                                    </option>
                                    <option value="cliente" @selected(old('tipo') == 'cliente')>
                                        Fechamento de Cliente (valores administrativos)
                                    </option>
                                </select>
                                <div class="form-text">
                                    <strong>Consultor:</strong> Usa valores cadastrados no consultor (valor/hora, valor/km do consultor).<br>
                                    <strong>Cliente:</strong> Usa valores da tabela de preços do cliente (valor/hora da tabela, mas valor/km do consultor).
                                </div>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="cliente_id" class="form-label">Filtrar por Cliente (Opcional)</label>
                            <select name="cliente_id" id="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror">
                                <option value="">Todos os Clientes</option>
                                <option value="todos" @selected(old('cliente_id') == 'todos')>Gerar relatórios individuais para todos os clientes</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                        {{ $cliente->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Deixe vazio para incluir todos os clientes ou selecione um cliente específico.
                            </div>
                            @error('cliente_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="data_inicio" class="form-label">Data Início <span class="text-danger">*</span></label>
                                    <input type="date" name="data_inicio" id="data_inicio"
                                           class="form-control @error('data_inicio') is-invalid @enderror"
                                           value="{{ old('data_inicio', $dataInicio ?? '') }}" required>
                                    @error('data_inicio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="data_fim" class="form-label">Data Fim <span class="text-danger">*</span></label>
                                    <input type="date" name="data_fim" id="data_fim"
                                           class="form-control @error('data_fim') is-invalid @enderror"
                                           value="{{ old('data_fim', $dataFim ?? '') }}" required>
                                    @error('data_fim')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informações:</strong> O sistema irá gerar um relatório contendo todas as Ordens de Serviço
                            criadas no período especificado para o consultor selecionado.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Gerar Relatório
                            </button>
                            <a href="{{ isset($tipo) && $tipo === 'cliente' ? route('relatorio-fechamento-cliente.index') : route('relatorio-fechamento-consultor.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Dicas</h5>
                </div>
                <div class="card-body">
                    <p><strong>Como funciona?</strong></p>
                    <ul class="ps-3 mb-0">
                        <li>Selecione um consultor na lista</li>
                        <li>Defina o período desejado (data início e fim)</li>
                        <li>O sistema irá buscar todas as OS criadas neste período</li>
                        <li>Um rascunho do relatório será criado</li>
                        <li>Você poderá visualizar, gerar PDF ou enviar para aprovação</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Status dos Relatórios</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge badge-secondary">Rascunho</span>
                        <small class="d-block text-muted mt-1">Relatório criado, ainda não foi enviado</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-info">Enviado</span>
                        <small class="d-block text-muted mt-1">Aguardando aprovação do financeiro</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-success">Aprovado</span>
                        <small class="d-block text-muted mt-1">Aprovado e pronto para envio ao consultor</small>
                    </div>
                    <div>
                        <span class="badge badge-danger">Rejeitado</span>
                        <small class="d-block text-muted mt-1">Rejeitado com observações</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
