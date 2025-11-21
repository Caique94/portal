$(document).ready(function() {

    let modal_stage = '';
    let produto_aux = '';
    let userRole = 'admin'; // Default role

    let tblOrdensServico = $('#tblOrdensServico').DataTable({
        ajax: {
            url: '/listar-ordens-servico',
            dataSrc: 'data',
            complete: function(jqXHR) {
                // Capture user role from response
                if (jqXHR.responseJSON && jqXHR.responseJSON.user_role) {
                    userRole = jqXHR.responseJSON.user_role;
                }
            }
        },
        columnDefs: [{
            targets: [2],
            visible: false
        }, {
            // Hide "Valor" column for non-admin users
            targets: [5],
            visible: papel == 'admin' ? true : false
        }],
        columns: [{
            title: 'Codigo',
            data: 'id',
            width: '120px',
            className: 'dt-center',
            render: function(data, type, row) {
                var html = ('00000000' + data).slice(-8);
                return html;
            }
        },{
            title: 'Emiss&atilde;o',
            data: 'data_emissao',
            className: 'dt-center',
            width: '120px',
            render: function(data, type, row) {
                if (!data) return '-';
                // Dividir a data no formato YYYY-MM-DD e criar a data sem problemas de timezone
                var parts = data.split('-');
                var year = parseInt(parts[0], 10);
                var month = parseInt(parts[1], 10);
                var day = parseInt(parts[2], 10);
                var html = ('0' + day).slice(-2) + '/' + ('0' + month).slice(-2) + '/' + year;

                return html;
            }
        },{
            title: 'C&oacute;digo',
            data: 'cliente_codigo'
        },{
            title: 'Cliente',
            data: 'cliente_nome'
        },{
            title: 'Consultor',
            data: 'consultor_nome'
        },{
            title: 'Assunto',
            data: 'assunto'
        },{
            title: 'Valor',
            data: 'valor_total',
            className: 'dt-head-center dt-body-right',
            width: '120px',
            visible: papel == 'admin' ? true : false
        },{
            title: 'Status',
            data: 'status',
            className: 'dt-center',
            width: '150px',
            render: function(data, type, row) {
                var html = '';
                var statusMap = {
                    'em_aberto': { text: 'Em Aberto', class: 'status-1-text' },
                    'aguardando_aprovacao': { text: 'Aguardando Aprovação', class: 'status-2-text' },
                    'contestar': { text: 'Contestada', class: 'status-3-text' },
                    'aprovado': { text: 'Aguardando Faturamento', class: 'status-4-text' },
                    'faturado': { text: 'Faturada', class: 'status-5-text' },
                    'aguardando_rps': { text: 'Aguardando RPS', class: 'status-6-text' },
                    'rps_emitida': { text: 'RPS Emitida', class: 'status-7-text' },
                    // Legacy numeric status support
                    '1': { text: 'Em Aberto', class: 'status-1-text' },
                    '2': { text: 'Aguardando Aprovação', class: 'status-2-text' },
                    '3': { text: 'Contestada', class: 'status-3-text' },
                    '4': { text: 'Aguardando Faturamento', class: 'status-4-text' },
                    '5': { text: 'Faturada', class: 'status-5-text' },
                    '6': { text: 'Aguardando RPS', class: 'status-6-text' },
                    '7': { text: 'RPS Emitida', class: 'status-7-text' }
                };

                // Use display_status if available (for consultores viewing OS they created)
                var statusToDisplay = row.display_status !== undefined ? row.display_status : data;
                var status = statusMap[statusToDisplay] || statusMap[String(statusToDisplay)];
                if (status) {
                    html = '<span class="' + status.class + '">' + status.text + '</span>';
                } else {
                    html = '<span class="status-unknown">' + statusToDisplay + '</span>';
                }

                return html;
            }
        },{
            title: 'A&ccedil;&otilde;es',
            data: null,
            className: 'dt-center noVis',
            orderable: false,
            width: '80px',
            render: function(data, type, row) {
                var html = '';

                html += '<div class="dropdown">';
                html += '<button class="btn btn-sm btn-outline-primary border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
                html += '<i class="bi bi-list"></i>';
                html += '</button>';
                html += '<ul class="dropdown-menu">';

                // Visualizar - visible to all roles
                html += '<li><a class="dropdown-item exibir-modal-visualizacao" href="javascript:void(0);"><i class="bi bi-eye"></i> Visualizar</a></li>';

                // Editar - only for consultor and admin on status 1 or 3
                if ((papel == 'consultor' || papel == 'admin') && (row.status == 1 || row.status == 3)) {
                    html += '<li><a class="dropdown-item exibir-modal-edicao" href="javascript:void(0);"><i class="bi bi-pencil"></i> Editar</a></li>';

                    // Motivo Contestação - only on status 3
                    if (row.status == 3) {
                        html += '<li><a class="dropdown-item exibir-motivo-contestacao" href="javascript:void(0);"><i class="bi bi-exclamation-triangle"></i> Motivo Contesta&ccedil;&atilde;o</a></li>';
                    }

                    html += '<li><hr class="dropdown-divider"></li>';
                    html += '<li><a class="dropdown-item toggle-status-ordem-servico" status-destino="2" href="javascript:void(0);"><i class="bi bi-send"></i> Enviar p/ Aprova&ccedil;&atilde;o</a></li>';

                    // Deletar - only on status 1
                    if (row.status == 1) {
                        html += '<li><a class="dropdown-item deletar-ordem-servico text-danger" href="javascript:void(0);"><i class="bi bi-trash"></i> Deletar</a></li>';
                    }
                }

                // Admin approval actions - only on status 2
                if (papel == 'admin' && (row.status == 2)) {
                    html += '<li><hr class="dropdown-divider"></li>';
                    html += '<li><a class="dropdown-item toggle-status-ordem-servico" status-destino="4" href="javascript:void(0);"><i class="bi bi-check-circle"></i> Aprovar</a></li>';
                    html += '<li><a class="dropdown-item contestar-ordem-servico" href="javascript:void(0);"><i class="bi bi-x-circle"></i> Contestar</a></li>';
                }

                html += '</ul>';
                html += '</div>';

                return html;
            }
        }],
        order: [[0, 'desc']],
        buttons: {
            name: 'primary',
            buttons: [{
                text: 'Adicionar',
                className: 'btn-primary',
                action: function (e, dt, node, config) {
                    modal_stage = 'adicionar';
                    $('#modalOrdemServico #txtOrdemId').val('0');
                    $('#modalOrdemServico #txtOrdemConsultorId').val(user_id);
                    $('#modalOrdemServico #txtOrdemConsultor').val(user_name);
                    $('#txtOrdemDataEmissao').val(today());
                    $('#modalOrdemServico').modal('show');
                }
            }]
        },
        createdRow: function(row, data, dataIndex) {
            $('td:eq(0)', row).addClass('first-column-border');

            // Use display_status if available (for consultores viewing OS they created)
            var statusForBorder = data.display_status !== undefined ? data.display_status : data.status;

            switch(statusForBorder) {
                case 1:
                    $('td:eq(0)', row).addClass('status-1-border');
                    break;
                case 2:
                    $('td:eq(0)', row).addClass('status-2-border');
                    break;
                case 3:
                    $('td:eq(0)', row).addClass('status-3-border');
                    break;
                case 4:
                    $('td:eq(0)', row).addClass('status-4-border');
                    break;
                case 5:
                    $('td:eq(0)', row).addClass('status-5-border');
                    break;
                case 6:
                    $('td:eq(0)', row).addClass('status-6-border');
                    break;
                case 7:
                    $('td:eq(0)', row).addClass('status-7-border');
                    break;
            }
        },
        initComplete: function (settings, json) {
            initializeTooltips();

            $('#tblOrdensServico tbody').on('click', '.toggle-status-ordem-servico', function() {
                var row = $(this).closest('tr');
                var rowData = tblOrdensServico.row(row).data();
                var status_destino = $(this).attr('status-destino');
                var url = '/toggle-status-ordem-servico/' + rowData.id + '/' + status_destino;

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        tblOrdensServico.ajax.reload();

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

            $('#tblOrdensServico tbody').on('click', '.contestar-ordem-servico', function() {
                var row = $(this).closest('tr');
                var rowData = tblOrdensServico.row(row).data();

                Swal.fire({
                    input: "textarea",
                    title: "Motivo Contestação",
                    showCancelButton: true,
                    confirmButtonText: 'Contestar',
                    cancelButtonText: 'Cancelar',
                    backdrop: false,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    didOpen: () => {
                        const inputElement = Swal.getPopup().querySelector('.swal2-input');
                        if (inputElement) {
                            inputElement.focus();
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (result.value == '') {
                            Toast.fire({
                                icon: 'error',
                                title: 'É necessário informar um motivo para a contestação.'
                            });
                        } else {
                            var data = {
                                id: rowData.id,
                                motivo: result.value
                            }

                            $.ajax({
                                url: '/contestar-ordem-servico',
                                type: 'POST',
                                data: data,
                                success: function(response) {
                                    tblOrdensServico.ajax.reload();

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
                    }
                });
            });

            $('#tblOrdensServico tbody').on('click', '.deletar-ordem-servico', function() {
                var row = $(this).closest('tr');
                var rowData = tblOrdensServico.row(row).data();

                Swal.fire({
                    title: 'Confirmar Exclusão',
                    text: 'Tem certeza que deseja deletar esta ordem de serviço? Esta ação não pode ser desfeita.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Deletar',
                    cancelButtonText: 'Cancelar',
                    backdrop: false,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/deletar-ordem-servico/' + rowData.id,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                tblOrdensServico.ajax.reload();

                                Toast.fire({
                                    icon: 'success',
                                    title: 'Ordem de Serviço deletada com sucesso!'
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
                                        title: 'Erro ao deletar ordem de serviço'
                                    });
                                }
                            }
                        });
                    }
                });
            });

            $('#tblOrdensServico tbody').on('click', '.exibir-modal-edicao', function() {
                var row = $(this).closest('tr');
                var rowData = tblOrdensServico.row(row).data();
                modal_stage = 'editar';
                produto_aux = rowData.produto_tabela_id;

                $('#txtOrdemId').val(rowData.id);
                $('#txtOrdemConsultorId').val(rowData.consultor_id);
                $('#txtOrdemConsultor').val(rowData.consultor_nome);
                $('#slcOrdemClienteId').val(rowData.cliente_id).trigger('change');
                $('#txtOrdemDataEmissao').val(rowData.data_emissao).trigger('change');
                $('#slcOrdemTipoDespesa').val(rowData.tipo_despesa).trigger('change');
                $('#txtOrdemDespesas').val(rowData.valor_despesa);
                $('#txtOrdemDespesasDetalhamento').val(rowData.detalhamento_despesa);
                $('#txtProdutoOrdemHoraInicio').val(rowData.hora_inicio).trigger('chamge');
                $('#txtProdutoOrdemHoraFinal').val(rowData.hora_final).trigger('chamge');
                $('#txtProdutoOrdemHoraDesconto').val(rowData.hora_desconto).trigger('chamge');
                $('#txtProdutoOrdemQtdeTotal').val(rowData.qtde_total);
                $('#txtOrdemAssunto').val(rowData.assunto);
                $('#txtOrdemProjeto').val(rowData.projeto);
                $('#txtOrdemNrAtendimento').val(rowData.nr_atendimento);
                $('#txtProdutoOrdemDetalhamento').val(rowData.detalhamento);

                // Trigger para calcular totalizador (para admin)
                setTimeout(function() {
                    $('#txtOrdemPrecoProduto').trigger('change');
                }, 500);

                $('#modalOrdemServico').modal('show');
            });

            $('#tblOrdensServico tbody').on('click', '.exibir-modal-visualizacao', function() {
                var row = $(this).closest('tr');
                var rowData = tblOrdensServico.row(row).data();
                modal_stage = 'visualizar';
                produto_aux = rowData.produto_tabela_id;

                $('#txtOrdemId').val(rowData.id);
                $('#txtOrdemConsultorId').val(rowData.consultor_id);
                $('#txtOrdemConsultor').val(rowData.consultor_nome);
                $('#slcOrdemClienteId').val(rowData.cliente_id).trigger('change');
                $('#txtOrdemDataEmissao').val(rowData.data_emissao).trigger('change');
                $('#slcOrdemTipoDespesa').val(rowData.tipo_despesa).trigger('change');
                $('#txtOrdemDespesas').val(rowData.valor_despesa);
                $('#txtOrdemDespesasDetalhamento').val(rowData.detalhamento_despesa);
                $('#txtProdutoOrdemHoraInicio').val(rowData.hora_inicio).trigger('chamge');
                $('#txtProdutoOrdemHoraFinal').val(rowData.hora_final).trigger('chamge');
                $('#txtProdutoOrdemHoraDesconto').val(rowData.hora_desconto).trigger('chamge');
                $('#txtProdutoOrdemQtdeTotal').val(rowData.qtde_total);
                $('#txtOrdemAssunto').val(rowData.assunto);
                $('#txtOrdemProjeto').val(rowData.projeto);
                $('#txtOrdemNrAtendimento').val(rowData.nr_atendimento);
                $('#txtProdutoOrdemDetalhamento').val(rowData.detalhamento);

                // Trigger para calcular totalizador (para admin)
                setTimeout(function() {
                    $('#txtOrdemPrecoProduto').trigger('change');
                }, 500);

                $('.btn-salvar-ordem-servico').addClass('d-none');
                $('#modalOrdemServico input, #modalOrdemServico select, #modalOrdemServico textarea').prop('disabled', true);
                $('#modalOrdemServico').modal('show');
            });

            $('#tblOrdensServico tbody').on('click', '.exibir-motivo-contestacao', function() {
                var row = $(this).closest('tr');
                var rowData = tblOrdensServico.row(row).data();

                Swal.fire({
                    title: "Motivo Contestação",
                    text: rowData.motivo_contestacao,
                    confirmButtonText: 'Fechar',
                    backdrop: false,
                    customClass: {
                        confirmButton: 'btn btn-secondary'
                    },
                });
            });
        }
    });

    $('#modalOrdemServico').on('hidden.bs.modal', function() {
        $('#modalOrdemServico input[type="text"]').val('');
        $('#modalOrdemServico input[type="time"]').val('').trigger('change');
        $('#modalOrdemServico select').val('').trigger('change');
        $('#modalOrdemServico textarea').val('');
        $('.btn-salvar-ordem-servico').removeClass('d-none');
        $('#modalOrdemServico input, #modalOrdemServico select, #modalOrdemServico textarea').prop('disabled', false);

        // Ocultar totalizador ao fechar modal
        if ($('#divTotalizadorAdmin').length > 0) {
            $('#divTotalizadorAdmin').hide();
        }

        modal_stage = '';
    });

    $('.btn-salvar-ordem-servico').on('click', function() {
        const form = $('#formOrdemServico');

        if (validateFormRequired(form)) {
            var formData = form.serialize();

            $.ajax({
                url: '/salvar-ordem-servico',
                type: 'POST',
                data: formData,
                success: function(response) {
                    tblOrdensServico.ajax.reload();
                    $('#modalOrdemServico').modal('hide');

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

    let lista_clientes = $('#slcOrdemClienteId').select2({
        dropdownParent: $('#modalOrdemServico'),
        language: 'pt-BR',
        placeholder: 'Selecione ...',
        theme: 'bootstrap-5'
    });
    
    let lista_produtos_tabela = $('#slcProdutoOrdemId').select2({
        dropdownParent: $('#modalOrdemServico'),
        language: 'pt-BR',
        placeholder: 'Selecione ...',
        theme: 'bootstrap-5'
    });

    $.ajax({
        url: '/listar-clientes',
        type: 'GET'
    }).then(function (data) {
        var option = new Option('', '', true, true);
        lista_clientes.append(option);
        for (var i = 0; i < data.length; i++) {
            var option = new Option(data[i].nome + ' (' + data[i].codigo + ')', data[i].id, false, false);
            lista_clientes.append(option);
        }
        lista_clientes.trigger('change');
    });

    $('#slcOrdemClienteId').on('change', function() {
        $('#slcProdutoOrdemId').prop('disabled', true);
        $('#slcProdutoOrdemId').empty();
        var selectedValue = $(this).val();

        if (selectedValue != '') {
            // Buscar dados do cliente (km e deslocamento)
            $.ajax({
                url: '/listar-clientes',
                type: 'GET'
            }).then(function (clientes) {
                var cliente = clientes.find(c => c.id == selectedValue);
                if (cliente) {
                    $('#txtOrdemKM').val(cliente.km || '');
                    $('#txtOrdemDeslocamento').val(cliente.deslocamento || '');
                    // Trigger calculation update
                    $('#chkOrdemPresencial').trigger('change');
                }
            });

            $.ajax({
                url: '/listar-produtos-por-cliente/' + selectedValue,
                type: 'GET'
            }).then(function (data) {
                var option = new Option('', '', true, true);
                lista_produtos_tabela.append(option);
                for (var i = 0; i < data.length; i++) {
                    var option = new Option('(' + data[i].produto.codigo + ') ' + data[i].produto.descricao, data[i].id, false, false);
                    $(option).attr('data-custom', data[i].preco);
                    lista_produtos_tabela.append(option);
                }
                lista_produtos_tabela.trigger('change');
                if (produto_aux != '') {
                    $('#slcProdutoOrdemId').val(produto_aux).trigger('change');
                    produto_aux = '';
                }
                if (modal_stage != 'visualizar') {
                    $('#slcProdutoOrdemId').prop('disabled', false);
                }
            });
        } else {
            // Limpar campos quando cliente não for selecionado
            $('#txtOrdemKM').val('');
            $('#txtOrdemDeslocamento').val('');
        }
    });

    $('#slcProdutoOrdemId').on('change', function() {
        var preco = $(this).find(':selected').data('custom');
        $('#txtOrdemPrecoProduto').val(preco).trigger('change');
    });

    $('#slcOrdemClienteId, #slcProdutoOrdemId').parent('div').children('span').children('span').children('span').css('height', ' calc(3.5rem + 2px)');
    $('#slcOrdemClienteId, #slcProdutoOrdemId').parent('div').children('span').children('span').children('span').children('span').css('margin-top', '18px');
    $('#slcOrdemClienteId, #slcProdutoOrdemId').parent('div').find('label').css('z-index', '1');

    $('.calculo-horas').on('change', function() {
        var qtde_horas = 0;

        var hora_inicial = moment($('#txtProdutoOrdemHoraInicio').val() + ':00', 'HH:mm:ss');
        var hora_final = moment($('#txtProdutoOrdemHoraFinal').val() + ':00', 'HH:mm:ss');
        var hora_desconto = parseInt($('#txtProdutoOrdemHoraDesconto').val().slice(-2)) + (parseInt($('#txtProdutoOrdemHoraDesconto').val().slice(0,2)) * 60);
        
        if (Number.isNaN(hora_desconto)) {
            hora_desconto = 0;
        }

        if (hora_final.isAfter(hora_inicial)) {
            qtde_horas = (hora_final.diff(hora_inicial, 'minutes') - hora_desconto) / 60;
        }

        $('#txtProdutoOrdemQtdeTotal').val(qtde_horas).trigger('change');
    });

    // INCLUIR LOGICA CALCULO VALOR TOTAL COM VALORES DO CONSULTOR
    $('.calculo-valor-total, #chkOrdemPresencial').on('change', async function() {
        var valor_total = 0;
        var preco = $('#txtOrdemPrecoProduto').val() != '' ? parseFloat($('#txtOrdemPrecoProduto').val().replace(/\./g, '').replace(/,/g, '.')) : 0;
        var horas = $('#txtProdutoOrdemQtdeTotal').val() != '' ? parseFloat($('#txtProdutoOrdemQtdeTotal').val()) : 0;
        var despesas = $('#txtOrdemDespesas').val() != '' ? parseFloat($('#txtOrdemDespesas').val().replace(/\./g, '').replace(/,/g, '.')) : 0;

        // Verificar se precisa deslocamento / km no valor da ordem
        var km = 0;
        var deslocamento = 0;
        var horasDeslocamento = 0;

        if ($('#chkOrdemPresencial').is(':checked')) {
            km = $('#txtOrdemKM').val() != '' ? parseFloat($('#txtOrdemKM').val().replace(/\./g, '').replace(/,/g, '.')) : 0;
            deslocamento = $('#txtOrdemDeslocamento').val() != '' ? parseFloat($('#txtOrdemDeslocamento').val().replace(/\./g, '').replace(/,/g, '.')) : 0;

            // Extrair horas de deslocamento do campo de deslocamento (formato HH:MM)
            var deslocamentoStr = $('#txtOrdemDeslocamento').val();
            if (deslocamentoStr && deslocamentoStr.includes(':')) {
                horasDeslocamento = calcularHorasDesdeTexto(deslocamentoStr);
            }
        }

        valor_total = (preco * horas) + despesas + km + deslocamento;

        $('#txtOrdemValorTotal').val(Number.isNaN(valor_total) ? 0 : valor_total.toFixed(2));

        // Buscar dados do consultor e atualizar totalizador
        var osId = $('#txtOrdemId').val();
        if (osId) {
            await atualizarTotalizadorComValoresConsultor(osId, preco, horas, despesas, km, horasDeslocamento);
        }
    });

    // Função auxiliar para calcular horas a partir de string HH:MM
    function calcularHorasDesdeTexto(texto) {
        if (!texto || !texto.includes(':')) return 0;
        var partes = texto.split(':');
        var horas = parseInt(partes[0]) || 0;
        var minutos = parseInt(partes[1]) || 0;
        return horas + (minutos / 60);
    }

    // Função para formatar valor em R$ com separadores brasileiros
    function formatarMoeda(valor) {
        return 'R$ ' + valor.toFixed(2).replace('.', ',');
    }

    // Função para buscar dados do consultor e atualizar totalizador
    async function atualizarTotalizadorComValoresConsultor(osId, precoProduto, horas, despesas, km, horasDeslocamento) {
        try {
            const response = await $.ajax({
                url: `/os/${osId}/totalizador-data`,
                type: 'GET',
                dataType: 'json'
            });

            if (response.success) {
                const dados = response.data;
                const userRole = dados.papel_user_atual;

                // Mostrar totalizador se usuário é admin, consultor ou superadmin
                if (['admin', 'consultor', 'superadmin'].includes(userRole) && $('#divTotalizadorAdmin').length > 0) {
                    $('#divTotalizadorAdmin').show();

                    let valorServico = 0;
                    let valorKM = 0;
                    let valorDeslocamento = 0;

                    // Admin: valor serviço = preco_produto × horas
                    if (userRole === 'admin') {
                        valorServico = precoProduto * horas;
                    }
                    // Consultor e Superadmin: valor serviço = horas × valor_hora_consultor
                    else if (['consultor', 'superadmin'].includes(userRole)) {
                        valorServico = horas * dados.valor_hora_consultor;
                    }

                    // KM = km × valor_km_consultor (ambos usam valor do consultor)
                    valorKM = km * dados.valor_km_consultor;

                    // Deslocamento = horas_deslocamento × valor_hora_consultor
                    valorDeslocamento = horasDeslocamento * dados.valor_hora_consultor;

                    // Atualizar exibição
                    $('#totalValorServico').text(formatarMoeda(valorServico));
                    $('#totalDespesas').text(formatarMoeda(despesas));

                    // Exibir valor/hora consultor
                    $('#valorHoraConsultor').text(formatarMoeda(dados.valor_hora_consultor));
                    $('#valorKMConsultor').text(formatarMoeda(dados.valor_km_consultor));

                    // Mostrar/ocultar linhas de KM e Deslocamento
                    if ($('#chkOrdemPresencial').is(':checked') && (km > 0 || horasDeslocamento > 0)) {
                        if (km > 0) {
                            $('#linhaKM').show();
                            $('#totalKM').text(formatarMoeda(valorKM));
                        } else {
                            $('#linhaKM').hide();
                        }

                        if (horasDeslocamento > 0) {
                            $('#linhaDeslocamento').show();
                            $('#totalDeslocamento').text(formatarMoeda(valorDeslocamento));
                        } else {
                            $('#linhaDeslocamento').hide();
                        }
                    } else {
                        $('#linhaKM').hide();
                        $('#linhaDeslocamento').hide();
                    }

                    // Calcular e exibir total geral
                    var totalGeral = valorServico + despesas + valorKM + valorDeslocamento;
                    $('#totalGeral').text(formatarMoeda(totalGeral));

                    // Se for Admin, mostrar também a visão do Consultor
                    if (userRole === 'admin' && $('#divTotalizadorConsultor').length > 0) {
                        $('#divTotalizadorConsultor').show();

                        // Cálculo da visão do Consultor
                        let valorServicoConsultor = horas * dados.valor_hora_consultor;
                        let valorKMConsultor = km * dados.valor_km_consultor;
                        let valorDeslocamentoConsultor = horasDeslocamento * dados.valor_hora_consultor;

                        // Atualizar exibição do totalizador do consultor
                        $('#totalValorServicoConsultor').text(formatarMoeda(valorServicoConsultor));
                        $('#totalDespesasConsultor').text(formatarMoeda(despesas));

                        // Exibir valor/hora consultor
                        $('#valorHoraConsultorConsultor').text(formatarMoeda(dados.valor_hora_consultor));
                        $('#valorKMConsultorConsultor').text(formatarMoeda(dados.valor_km_consultor));

                        // Mostrar/ocultar linhas de KM e Deslocamento
                        if ($('#chkOrdemPresencial').is(':checked') && (km > 0 || horasDeslocamento > 0)) {
                            if (km > 0) {
                                $('#linhaKMConsultor').show();
                                $('#totalKMConsultor').text(formatarMoeda(valorKMConsultor));
                            } else {
                                $('#linhaKMConsultor').hide();
                            }

                            if (horasDeslocamento > 0) {
                                $('#linhaDeslocamentoConsultor').show();
                                $('#totalDeslocamentoConsultor').text(formatarMoeda(valorDeslocamentoConsultor));
                            } else {
                                $('#linhaDeslocamentoConsultor').hide();
                            }
                        } else {
                            $('#linhaKMConsultor').hide();
                            $('#linhaDeslocamentoConsultor').hide();
                        }

                        // Calcular e exibir total geral do consultor
                        var totalGeralConsultor = valorServicoConsultor + despesas + valorKMConsultor + valorDeslocamentoConsultor;
                        $('#totalGeralConsultor').text(formatarMoeda(totalGeralConsultor));
                    }
                }
            }
        } catch (error) {
            console.error('Erro ao buscar dados do totalizador:', error);
        }
    }

    // Função compatível com código legado (mantida para referência)
    function atualizarTotalizadorAdmin(valorServico, despesas, km, deslocamento) {
        // Função mantida para compatibilidade com código legado
        // Agora o cálculo é feito em atualizarTotalizadorComValoresConsultor()
    }

    $('#slcOrdemTipoDespesa').on('change', function() {
        var readonly = true;

        if ($('#slcOrdemTipoDespesa').val() == 'outros') {
            readonly = false;
        } else {
            $('#txtOrdemDespesasDetalhamento').val('');
        }

        $('#txtOrdemDespesasDetalhamento').attr('readonly', readonly)
    });

    $('#slcOrdemTipoDespesa').trigger('change');

});
