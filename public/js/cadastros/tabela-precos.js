$(document).ready(function() {

    let tblTabelasPrecos = $('#tblTabelasPrecos').DataTable({
        ajax: {
            url: '/listar-tabelas-precos',
            dataSrc: function(json) {
                return json;
            }
        },
        columns: [{
            title: 'Descri&ccedil;&atilde;o',
            data: 'descricao',
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
                html += '<input type="checkbox" role="switch" id="chkTabelaPrecoAtivo' + row.id + '" class="form-check-input toggle-tabela-precos" value="1" ' + (row.ativo == '1' ? 'checked' : '') + ' />';
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
                html += '<button class="btn btn-sm btn-outline-success border-0 adicionar-produto-tabela" data-bs-toggle="tooltip" data-bs-title="Adicionar Produto" data-bs-trigger="hover" data-bs-placement="left">';
                html += '<i class="bi bi-bag-plus-fill"></i>';
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
                html += '<button class="btn btn-sm btn-outline-primary border-0 exibir-produtos-tabela" data-bs-toggle="tooltip" data-bs-title="Exibir Produtos" data-bs-trigger="hover" data-bs-placement="top">';
                html += '<i class="bi bi-bag-fill"></i>';
                html += '</button>';

                return html;
            }
        }],
        buttons: [],
        initComplete: function (settings, json) {
            initializeTooltips();

            $('#tblTabelasPrecos tbody').on('click', '.toggle-tabela-precos', function() {
                var row = $(this).closest('tr');
                var rowData = tblTabelasPrecos.row(row).data();
                var url = '/toggle-tabela-precos/' + rowData.id;

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        tblTabelasPrecos.ajax.reload();

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
        }
    });

    $('.btn-adicionar-tabela-precos').on('click', function() {
        $('#modalTabelaPrecos').modal('show');
    });

    $('#modalTabelaPrecos').on('hidden.bs.modal', function() {
        $('#modalTabelaPrecos input[type="text"]').val('');
    });

    $('.btn-salvar-tabela-precos').on('click', function() {
        const form = $('#formTabelaPrecos');

        if (validateFormRequired(form)) {
            var formData = form.serialize();

            $.ajax({
                url: '/salvar-tabela-precos',
                type: 'POST',
                data: formData,
                success: function(response) {
                    tblTabelasPrecos.ajax.reload();
                    $('#modalTabelaPrecos').modal('hide');

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

    $(document).on('click', '.exibir-produtos-tabela', function() {
        var row = $(this).closest('tr');
        var rowData = tblTabelasPrecos.row(row).data();

        $('#modalProdutosTabelaLabel').text(rowData.descricao + ' - Produtos');
        tblProdutosTabela.ajax.url('/listar-produtos-tabela?id=' + rowData.id).load();

        $('#modalProdutosTabela').modal('show');
    });

    let tblProdutosTabela = $('#tblProdutosTabela').DataTable({
        ajax: {
            url: '',
            dataSrc: function(json) {
                return json;
            }
        },
        columns: [{
            title: 'Produto',
            data: 'id',
            render: function(data, type, row) {
                var html = '';
                html += '(' + row.produto.codigo + ') ' + row.produto.descricao;

                return html;
            }
        },{
            title: 'Pre&ccedil;o',
            data: 'preco',
            className: 'dt-head-center dt-body-right',
            width: '120px'
        },{
            title: 'Ativo',
            className: 'dt-center',
            orderable: false,
            width: '80px',
            render: function(data, type, row) {
                var html = '';
                html += '<div class="d-flex align-items-center justify-content-center">';
                html += '<div class="form-check form-switch d-flex align-items-center justify-content-center">';
                html += '<input type="checkbox" role="switch" id="chkProdutoTabelaAtivo' + row.id + '" class="form-check-input toggle-produto-tabela" value="1" ' + (row.ativo == '1' ? 'checked' : '') + ' />';
                html += '</div>';
                html += '</div>';

                return html;
            }
        }],
        searching: false,
        info: false,
        paging: false,
        ordering: false,
        processing: true,
        buttons: [],
        initComplete: function (settings, json) {
            initializeTooltips();

            $('#tblProdutosTabela tbody').on('click', '.toggle-produto-tabela', function() {
                var row = $(this).closest('tr');
                var rowData = tblProdutosTabela.row(row).data();
                var url = '/toggle-produto-tabela/' + rowData.id;

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        tblProdutosTabela.ajax.reload();

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
        }
    });

    $(document).on('click', '.adicionar-produto-tabela', function (e) {
        var row = $(this).closest('tr');
        var rowData = tblTabelasPrecos.row(row).data();

        $('#modalProdutoTabelaLabel').text(rowData.descricao + ' - Adicionar Produto');
        $('#txtProdutoTabelaTabelaPrecoId').val(rowData.id)

        $('#modalProdutoTabela').modal('show');
    });

    $('.btn-salvar-produto-tabela').on('click', function() {
        const form = $('#formProdutoTabela');

        if (validateFormRequired(form)) {
            var formData = form.serialize();
            
            $.ajax({
                url: '/salvar-produto-tabela',
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#modalProdutoTabela').modal('hide');

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

    $('#modalProdutoTabela').on('hidden.bs.modal', function() {
        $('#modalProdutoTabela input[type="text"]').val('');
        $('#modalProdutoTabela select').val('').trigger('change');
    });

    let lista_produtos = $('#slcProdutoTabelaProdutoId').select2({
        dropdownParent: $('#modalProdutoTabela'),
        language: 'pt-BR',
        placeholder: 'Selecione ...',
        theme: 'bootstrap-5'
    });

    $.ajax({
        url: '/listar-produtos-ativos',
        type: 'GET'
    }).then(function (data) {
        var option = new Option('', '', true, true);
        lista_produtos.append(option).trigger('change');
        for (var i = 0; i < data.length; i++) {
            var option = new Option('(' + data[i].codigo + ') ' + data[i].nome, data[i].id, false, false);
            lista_produtos.append(option).trigger('change');
        }
    });

    $('#slcProdutoTabelaProdutoId').parent('div').children('span').children('span').children('span').css('height', ' calc(3.5rem + 2px)');
    $('#slcProdutoTabelaProdutoId').parent('div').children('span').children('span').children('span').children('span').css('margin-top', '18px');
    $('#slcProdutoTabelaProdutoId').parent('div').find('label').css('z-index', '1');

});
