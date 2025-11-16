$(document).ready(function() {

    let tblCondicoesPagamento = $('#tblCondicoesPagamento').DataTable({
        ajax: {
            url: '/todas-condicoes-pagamento',
            dataSrc: function(json) {
                return json;
            }
        },
        columns: [{
            title: 'Descrição',
            data: 'descricao',
        },{
            title: 'Parcelas',
            data: 'numero_parcelas',
            className: 'dt-center',
            width: '100px'
        },{
            title: 'Intervalo (dias)',
            data: 'intervalo_dias',
            className: 'dt-center',
            width: '120px'
        },{
            title: 'Criado Em',
            data: 'created_at',
            className: 'dt-center',
            width: '120px',
            render: function(data, type, row) {
                var criado_em = new Date(data);
                var html = ('0' + criado_em.getDate()).slice(-2) + '/' + ('0' + (criado_em.getMonth() + 1)).slice(-2) + '/' + criado_em.getFullYear();
                return html;
            }
        },{
            title: 'Ativo',
            className: 'dt-center',
            orderable: false,
            width: '80px',
            render: function(data, type, row) {
                var html = '';
                html += '<div class="d-flex align-items-center justify-content-center">';
                html += '<div class="form-check form-switch d-flex align-items-center justify-content-center">';
                html += '<input type="checkbox" role="switch" id="chkCondicaoAtivo' + row.id + '" class="form-check-input toggle-condicao-pagamento" value="1" ' + (row.ativo == '1' || row.ativo == true ? 'checked' : '') + ' />';
                html += '</div>';
                html += '</div>';
                return html;
            }
        },{
            title: '',
            data: null,
            className: 'dt-center',
            orderable: false,
            width: '50px',
            render: function(data, type, row) {
                var html = '';
                html += '<button class="btn btn-sm btn-outline-warning border-0 editar-condicao-pagamento" data-bs-toggle="tooltip" data-bs-title="Editar" data-bs-trigger="hover" data-bs-placement="left">';
                html += '<i class="bi bi-pencil-fill"></i>';
                html += '</button>';
                return html;
            }
        },{
            title: '',
            data: null,
            className: 'dt-center',
            orderable: false,
            width: '50px',
            render: function(data, type, row) {
                var html = '';
                html += '<button class="btn btn-sm btn-outline-danger border-0 deletar-condicao-pagamento" data-bs-toggle="tooltip" data-bs-title="Deletar" data-bs-trigger="hover" data-bs-placement="left">';
                html += '<i class="bi bi-trash-fill"></i>';
                html += '</button>';
                return html;
            }
        }],
        buttons: {
            name: 'primary',
            buttons: [{
                text: 'Adicionar',
                className: 'btn-primary',
                action: function (e, dt, node, config) {
                    $('#modalCondicaoPagamentoLabel').text('Adicionar Condição de Pagamento');
                    $('#formCondicaoPagamento')[0].reset();
                    $('#formCondicaoPagamento').attr('data-id', '');
                    $('#modalCondicaoPagamento').modal('show');
                }
            }]
        },
        initComplete: function (settings, json) {
            initializeTooltips();

            // Toggle Ativo
            $('#tblCondicoesPagamento tbody').on('click', '.toggle-condicao-pagamento', function() {
                var row = $(this).closest('tr');
                var rowData = tblCondicoesPagamento.row(row).data();
                var condicao_id = rowData.id;

                var ativo = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: '/atualizar-condicao-pagamento/' + condicao_id,
                    type: 'PUT',
                    data: {
                        ativo: ativo
                    },
                    success: function(response) {
                        tblCondicoesPagamento.ajax.reload();

                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 422) {
                            var errors = jqXHR.responseJSON.errors;
                            var errorsHtml = '';
                            $.each(errors, function(key, value) {
                                $.each(value, function(index, error) {
                                    errorsHtml += errorsHtml == '' ? error : ('<br>' + error);
                                });
                            });

                            Toast.fire({
                                icon: 'error',
                                title: errorsHtml
                            });
                        } else {
                            console.error('Error: ' + textStatus + ' - ' + errorThrown);

                            Toast.fire({
                                icon: 'error',
                                title: errorThrown
                            });
                        }
                    }
                });
            });

            // Editar
            $('#tblCondicoesPagamento tbody').on('click', '.editar-condicao-pagamento', function() {
                var row = $(this).closest('tr');
                var rowData = tblCondicoesPagamento.row(row).data();

                $('#modalCondicaoPagamentoLabel').text('Editar Condição de Pagamento');
                $('#formCondicaoPagamento').attr('data-id', rowData.id);
                $('#txtCondicaoDescricao').val(rowData.descricao);
                $('#txtCondicaoNumeroParcelas').val(rowData.numero_parcelas);
                $('#txtCondicaoIntervaloDias').val(rowData.intervalo_dias);
                $('#chkCondicaoAtivo').prop('checked', rowData.ativo == '1' || rowData.ativo == true);

                $('#modalCondicaoPagamento').modal('show');
            });

            // Deletar
            $('#tblCondicoesPagamento tbody').on('click', '.deletar-condicao-pagamento', function() {
                var row = $(this).closest('tr');
                var rowData = tblCondicoesPagamento.row(row).data();

                Swal.fire({
                    title: 'Tem certeza?',
                    text: 'Esta ação não pode ser desfeita!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Deletar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/deletar-condicao-pagamento/' + rowData.id,
                            type: 'DELETE',
                            success: function(response) {
                                tblCondicoesPagamento.ajax.reload();

                                Toast.fire({
                                    icon: 'success',
                                    title: response.message
                                });
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                if (jqXHR.status === 422) {
                                    var errors = jqXHR.responseJSON.errors;
                                    var errorsHtml = '';
                                    $.each(errors, function(key, value) {
                                        $.each(value, function(index, error) {
                                            errorsHtml += errorsHtml == '' ? error : ('<br>' + error);
                                        });
                                    });

                                    Toast.fire({
                                        icon: 'error',
                                        title: errorsHtml
                                    });
                                } else {
                                    console.error('Error: ' + textStatus + ' - ' + errorThrown);

                                    Toast.fire({
                                        icon: 'error',
                                        title: errorThrown
                                    });
                                }
                            }
                        });
                    }
                });
            });
        }
    });

    $('#modalCondicaoPagamento').on('hidden.bs.modal', function() {
        $('#formCondicaoPagamento')[0].reset();
        $('#formCondicaoPagamento').attr('data-id', '');
    });

    $('.btn-salvar-condicao-pagamento').on('click', function() {
        const form = $('#formCondicaoPagamento');

        if (validateFormRequired(form)) {
            var condicao_id = form.attr('data-id');
            var url = condicao_id ? '/atualizar-condicao-pagamento/' + condicao_id : '/salvar-condicao-pagamento';
            var method = condicao_id ? 'PUT' : 'POST';

            var formData = {
                descricao: $('#txtCondicaoDescricao').val(),
                numero_parcelas: $('#txtCondicaoNumeroParcelas').val(),
                intervalo_dias: $('#txtCondicaoIntervaloDias').val(),
                ativo: $('#chkCondicaoAtivo').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(response) {
                    tblCondicoesPagamento.ajax.reload();
                    $('#modalCondicaoPagamento').modal('hide');

                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status === 422) {
                        var errors = jqXHR.responseJSON.errors;
                        var errorsHtml = '';
                        $.each(errors, function(key, value) {
                            $.each(value, function(index, error) {
                                errorsHtml += errorsHtml == '' ? error : ('<br>' + error);
                            });
                        });

                        Toast.fire({
                            icon: 'error',
                            title: errorsHtml
                        });
                    } else {
                        console.error('Error: ' + textStatus + ' - ' + errorThrown);

                        Toast.fire({
                            icon: 'error',
                            title: errorThrown
                        });
                    }
                }
            });
        }
    });

});
