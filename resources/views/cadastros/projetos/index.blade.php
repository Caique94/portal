@extends('layout.master')

@section('title', '- Projetos')

@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Projetos</h4>
        <a href="{{ route('projetos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Novo Projeto
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tblProjetos" class="table table-striped mb-0 w-100">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Cliente</th>
                            <th>Status</th>
                            <th>Horas Alocadas</th>
                            <th>Horas Consumidas</th>
                            <th>Horas Restantes</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projetos as $projeto)
                            <tr>
                                <td>{{ $projeto->codigo }}</td>
                                <td>{{ $projeto->nome }}</td>
                                <td>{{ $projeto->cliente->nome_fantasia ?? $projeto->cliente->razao_social }}</td>
                                <td>
                                    @php
                                        $statusClass = match($projeto->status) {
                                            'ativo' => 'success',
                                            'pausado' => 'warning',
                                            'concluido' => 'info',
                                            'cancelado' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($projeto->status) }}
                                    </span>
                                </td>
                                <td>{{ number_format($projeto->horas_alocadas ?? 0, 2, ',', '.') }}h</td>
                                <td>{{ number_format($projeto->horas_consumidas ?? 0, 2, ',', '.') }}h</td>
                                <td>
                                    @php
                                        $restantes = $projeto->horas_restantes ?? 0;
                                        $restanteClass = $restantes < 10 ? 'danger' : ($restantes < 20 ? 'warning' : 'success');
                                    @endphp
                                    <span class="badge bg-{{ $restanteClass }}">
                                        {{ number_format($restantes, 2, ',', '.') }}h
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('projetos.show', $projeto->id) }}" class="btn btn-sm btn-outline-secondary" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('projetos.edit', $projeto->id) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if ($projeto->status !== 'concluido')
                                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Concluir Projeto"
                                                onclick="concluirProjeto({{ $projeto->id }})">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Deletar"
                                            onclick="deletarProjeto({{ $projeto->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#tblProjetos').DataTable({
            language: {
                sEmptyTable: "Nenhum dado disponível na tabela",
                sInfo: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                sInfoEmpty: "Mostrando 0 a 0 de 0 entradas",
                sInfoFiltered: "(filtrado de _MAX_ entradas no total)",
                sInfoPostFix: "",
                sInfoThousands: ".",
                sLengthMenu: "Mostrar _MENU_ entradas",
                sLoadingRecords: "Carregando...",
                sProcessing: "Processando...",
                sSearch: "Pesquisar:",
                sSearchPlaceholder: "",
                sUrl: "",
                sZeroRecords: "Nenhum resultado encontrado",
                oPaginate: {
                    sFirst: "Primeira",
                    sLast: "Última",
                    sNext: "Próxima",
                    sPrevious: "Anterior"
                },
                oAria: {
                    sSortAscending: ": ativar para ordenar coluna em crescente",
                    sSortDescending: ": ativar para ordenar coluna em decrescente"
                }
            },
            pageLength: 10,
        });
    });

    function concluirProjeto(id) {
        if (confirm('Tem certeza que deseja marcar este projeto como concluído?')) {
            $.ajax({
                url: '/projetos/' + id,
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({ status: 'concluido' }),
                success: function() {
                    alert('Projeto marcado como concluído com sucesso!');
                    location.reload();
                },
                error: function(error) {
                    if (error.responseJSON && error.responseJSON.message) {
                        alert('Erro: ' + error.responseJSON.message);
                    } else {
                        alert('Erro ao concluir o projeto');
                    }
                    console.error(error);
                }
            });
        }
    }

    function deletarProjeto(id) {
        if (confirm('Tem certeza que deseja deletar este projeto?')) {
            $.ajax({
                url: '/projetos/' + id,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    location.reload();
                },
                error: function(error) {
                    alert('Erro ao deletar o projeto');
                    console.error(error);
                }
            });
        }
    }
</script>
@endpush
