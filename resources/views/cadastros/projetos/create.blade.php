@extends('layout.master')

@section('title', '- Criar Projeto')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Novo Projeto</h4>
                <a href="{{ route('projetos.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Erro ao validar formulário:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('projetos.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cliente_id" class="form-label">Cliente *</label>
                                    <select class="form-control @error('cliente_id') is-invalid @enderror"
                                        id="cliente_id" name="cliente_id" required>
                                        <option value="">Selecione um cliente</option>
                                        @foreach ($clientes as $cliente)
                                            <option value="{{ $cliente->id }}"
                                                {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                                {{ $cliente->nome_fantasia ?? $cliente->razao_social }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('cliente_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome do Projeto *</label>
                                    <input type="text" class="form-control @error('nome') is-invalid @enderror"
                                        id="nome" name="nome" placeholder="Ex: Implementação de Sistema"
                                        value="{{ old('nome') }}" required>
                                    @error('nome')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="numero_atendimento" class="form-label">Número do Atendimento</label>
                                    <input type="text" class="form-control @error('numero_atendimento') is-invalid @enderror"
                                        id="numero_atendimento" name="numero_atendimento" placeholder="Ex: ATD-001-2024 ou 001/002/003"
                                        value="{{ old('numero_atendimento') }}">
                                    <small class="text-muted d-block mt-2">Você pode informar um ou vários números de atendimento separados por vírgula ou barra</small>
                                    @error('numero_atendimento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="descricao" class="form-label">Descrição</label>
                                    <textarea class="form-control @error('descricao') is-invalid @enderror"
                                        id="descricao" name="descricao" rows="4"
                                        placeholder="Descreva o projeto...">{{ old('descricao') }}</textarea>
                                    @error('descricao')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-control @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                        <option value="ativo" {{ old('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                        <option value="pausado" {{ old('status') == 'pausado' ? 'selected' : '' }}>Pausado</option>
                                        <option value="concluido" {{ old('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>
                                        <option value="cancelado" {{ old('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="horas_alocadas" class="form-label">Horas Alocadas</label>
                                    <input type="number" step="0.01" class="form-control @error('horas_alocadas') is-invalid @enderror"
                                        id="horas_alocadas" name="horas_alocadas" placeholder="Ex: 100.00"
                                        value="{{ old('horas_alocadas') }}" min="0">
                                    @error('horas_alocadas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="data_inicio" class="form-label">Data Início</label>
                                    <input type="date" class="form-control @error('data_inicio') is-invalid @enderror"
                                        id="data_inicio" name="data_inicio" value="{{ old('data_inicio') }}">
                                    @error('data_inicio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="data_fim" class="form-label">Data Fim</label>
                                    <input type="date" class="form-control @error('data_fim') is-invalid @enderror"
                                        id="data_fim" name="data_fim" value="{{ old('data_fim') }}">
                                    @error('data_fim')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Salvar Projeto
                            </button>
                            <a href="{{ route('projetos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
