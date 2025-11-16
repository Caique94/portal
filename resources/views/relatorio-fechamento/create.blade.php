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
                    <form method="POST" action="{{ route('relatorio-fechamento.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="consultor_id" class="form-label">Consultor <span class="text-danger">*</span></label>
                            <select name="consultor_id" id="consultor_id" class="form-select @error('consultor_id') is-invalid @enderror" required>
                                <option value="">Selecione um consultor...</option>
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
                            <a href="{{ route('relatorio-fechamento.index') }}" class="btn btn-outline-secondary">
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
