$(document).ready(function() {

    let reciboAtual = null;

    // Configuração comum para todas as DataTables
    const dataTableConfig = function(status = 'todos') {
        return {
            ajax: {
                url: '/listar-recibos-provisorios?status=' + status,
                dataSrc: function(json) {
                    return json;
                }
            },
            columns: [{
                title: 'N&uacute;mero',
                data: 'numero',
                className: 'dt-center',
                width: '120px',
            },{
                title: 'S&eacute;rie',
                data: 'serie',
                className: 'dt-center',
                width: '80px',
                orderable: false
            },{
                title: 'Data Emiss&atilde;o',
                data: 'data_emissao',
                className: 'dt-center',
                width: '120px',
                render: function(data, type, row) {
                    var emissao = new Date(data + 'T00:00:00');
                    var html = ('0' + emissao.getDate()).slice(-2) + '/' + ('0' + (emissao.getMonth() + 1)).slice(-2) + '/' + emissao.getFullYear();

                    return html;
                }
            },{
                title: 'Cliente',
                data: 'cliente.nome'
            },{
                title: 'Cond. Pagto',
                data: 'cond_pagto',
                width: '150px',
                orderable: false
            },{
                title: 'Valor',
                data: 'valor',
                className: 'dt-head-center dt-body-right',
                width: '150px',
                render: function(data, type, row) {
                    var html = data ? parseFloat(data).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    }) : '0,00';

                    return html;
                }
            },{
                title: 'Status',
                data: 'payment_status',
                className: 'dt-center',
                width: '120px',
                orderable: false,
                render: function(data, type, row) {
                    var badge = '';
                    switch(data) {
                        case 'quitado':
                            badge = '<span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Quitado</span>';
                            break;
                        case 'aberto':
                            badge = '<span class="badge bg-primary"><i class="bi bi-hourglass-split"></i> Em Aberto</span>';
                            break;
                        case 'atraso':
                            badge = '<span class="badge bg-danger"><i class="bi bi-exclamation-circle-fill"></i> Em Atraso</span>';
                            break;
                        default:
                            badge = '<span class="badge bg-secondary">Desconhecido</span>';
                    }
                    return badge;
                }
            },{
                title: 'A&ccedil;&otilde;es',
                data: null,
                className: 'dt-center',
                orderable: false,
                width: '100px',
                render: function(data, type, row) {
                    return '<button class="btn btn-sm btn-primary btn-gerenciar-parcelas" title="Gerenciar Parcelas"><i class="bi bi-cash-stack"></i></button>';
                }
            }],
            buttons: {
                name: 'primary',
                buttons: []
            },
            initComplete: function(settings, json) {
                initializeTooltips();
            }
        };
    };

    // Inicializar DataTables para cada status
    let tblReciboProvisorio = null;
    let tblReciboQuitado = null;
    let tblReciboAberto = null;
    let tblReciboAtraso = null;

    function initializeDataTables() {
        // Destruir DataTables existentes se já foram inicializadas
        if (tblReciboProvisorio) tblReciboProvisorio.destroy();
        if (tblReciboQuitado) tblReciboQuitado.destroy();
        if (tblReciboAberto) tblReciboAberto.destroy();
        if (tblReciboAtraso) tblReciboAtraso.destroy();

        // Inicializar DataTable para "Todos"
        tblReciboProvisorio = $('#tblReciboProvisorio').DataTable(dataTableConfig('todos'));

        // Bind de clique para botão gerenciar parcelas (Todos)
        bindGerenciarParcelas(tblReciboProvisorio);

        // Inicializar DataTable para "Quitado"
        tblReciboQuitado = $('#tblReciboQuitado').DataTable(dataTableConfig('quitado'));
        bindGerenciarParcelas(tblReciboQuitado);

        // Inicializar DataTable para "Em Aberto"
        tblReciboAberto = $('#tblReciboAberto').DataTable(dataTableConfig('aberto'));
        bindGerenciarParcelas(tblReciboAberto);

        // Inicializar DataTable para "Em Atraso"
        tblReciboAtraso = $('#tblReciboAtraso').DataTable(dataTableConfig('atraso'));
        bindGerenciarParcelas(tblReciboAtraso);
    }

    function bindGerenciarParcelas(dataTable) {
        dataTable.off('click', '.btn-gerenciar-parcelas').on('click', '.btn-gerenciar-parcelas', function() {
            var row = $(this).closest('tr');
            var rowData = dataTable.row(row).data();
            reciboAtual = rowData;

            $('#spanNumeroRPS').text(rowData.numero + '/' + rowData.serie);
            $('#txtParcelaReciboId').val(rowData.id);

            carregarParcelas(rowData.id);
            $('#modalGerenciarParcelas').modal('show');
        });
    }

    // Inicializar na primeira vez
    initializeDataTables();

    // Recarregar DataTables quando abas são clicadas
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).data('bs-target');
        if (target === '#content-todos' && tblReciboProvisorio) {
            tblReciboProvisorio.ajax.reload();
        } else if (target === '#content-quitado' && tblReciboQuitado) {
            tblReciboQuitado.ajax.reload();
        } else if (target === '#content-aberto' && tblReciboAberto) {
            tblReciboAberto.ajax.reload();
        } else if (target === '#content-atraso' && tblReciboAtraso) {
            tblReciboAtraso.ajax.reload();
        }
    });

    // Carregar parcelas de um RPS
    function carregarParcelas(reciboId) {
        $.ajax({
            url: '/listar-parcelas',
            type: 'GET',
            data: { recibo_provisorio_id: reciboId },
            success: function(response) {
                renderizarParcelas(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Toast.fire({
                    icon: 'error',
                    title: 'Erro ao carregar parcelas: ' + errorThrown
                });
            }
        });
    }

    // Renderizar parcelas na tabela
    function renderizarParcelas(parcelas) {
        var tbody = $('#tblParcelas tbody');
        tbody.empty();

        if (parcelas.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center">Nenhuma parcela cadastrada</td></tr>');
            return;
        }

        parcelas.forEach(function(parcela) {
            var statusBadge = '';
            switch(parcela.status) {
                case 'pendente':
                    statusBadge = '<span class="badge bg-warning text-dark">Pendente</span>';
                    break;
                case 'paga':
                    statusBadge = '<span class="badge bg-success">Paga</span>';
                    break;
                case 'atrasada':
                    statusBadge = '<span class="badge bg-danger">Atrasada</span>';
                    break;
            }

            // Formatar data de vencimento
            var dataVencimentoFormatada = '-';
            if (parcela.data_vencimento) {
                // Remove qualquer caractere não-numérico ou hífen no início/fim
                var dataLimpa = parcela.data_vencimento.trim().replace(/[^0-9-]/g, '');
                var partsVenc = dataLimpa.split('-');
                if (partsVenc.length === 3 && partsVenc[0].length === 4) {
                    dataVencimentoFormatada = ('0' + partsVenc[2]).slice(-2) + '/' +
                                             ('0' + partsVenc[1]).slice(-2) + '/' +
                                             partsVenc[0];
                }
            }

            // Formatar data de pagamento
            var dataPagamento = '-';
            if (parcela.data_pagamento) {
                // Remove qualquer caractere não-numérico ou hífen no início/fim
                var dataLimpaPg = parcela.data_pagamento.trim().replace(/[^0-9-]/g, '');
                var partsPg = dataLimpaPg.split('-');
                if (partsPg.length === 3 && partsPg[0].length === 4) {
                    dataPagamento = ('0' + partsPg[2]).slice(-2) + '/' +
                                   ('0' + partsPg[1]).slice(-2) + '/' +
                                   partsPg[0];
                }
            }

            var valor = parseFloat(parcela.valor).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });

            var acoes = '';
            if (parcela.status !== 'paga') {
                acoes += '<button class="btn btn-sm btn-success btn-marcar-paga me-1" data-id="' + parcela.id + '" title="Marcar como Paga"><i class="bi bi-check-circle"></i></button>';
            }
            acoes += '<button class="btn btn-sm btn-primary btn-editar-parcela me-1" data-id="' + parcela.id + '" title="Editar"><i class="bi bi-pencil"></i></button>';
            acoes += '<button class="btn btn-sm btn-danger btn-deletar-parcela" data-id="' + parcela.id + '" title="Deletar"><i class="bi bi-trash"></i></button>';

            var tr = '<tr>' +
                '<td class="text-center">' + parcela.numero_parcela + '/' + parcela.total_parcelas + '</td>' +
                '<td class="text-center">' + dataVencimentoFormatada + '</td>' +
                '<td class="text-end">' + valor + '</td>' +
                '<td class="text-center">' + statusBadge + '</td>' +
                '<td class="text-center">' + dataPagamento + '</td>' +
                '<td class="text-center">' + acoes + '</td>' +
                '</tr>';

            tbody.append(tr);
        });

        initializeTooltips();
    }

    // Abrir modal criar parcelas
    $('#btnAbrirModalCriarParcelas').on('click', function() {
        $('#txtValorTotalParcelas').val(reciboAtual.valor);
        $('#txtDataPrimeiraParcela').val(new Date().toISOString().split('T')[0]);
        $('#modalCriarParcelas').modal('show');
    });

    // Preview de parcelas
    $('#txtTotalParcelas, #txtValorTotalParcelas, #txtDataPrimeiraParcela, #txtIntervaloDias').on('input change', function() {
        var totalParcelas = parseInt($('#txtTotalParcelas').val());
        var valorTotal = parseFloat($('#txtValorTotalParcelas').val().replace(/\./g, '').replace(/,/g, '.')) || 0;
        var dataPrimeira = $('#txtDataPrimeiraParcela').val();
        var intervaloDias = parseInt($('#txtIntervaloDias').val()) || 30;

        if (totalParcelas && valorTotal && dataPrimeira) {
            var valorParcela = valorTotal / totalParcelas;
            var lista = $('#listaPreviewParcelas');
            lista.empty();

            var data = new Date(dataPrimeira + 'T00:00:00');

            for (var i = 1; i <= totalParcelas; i++) {
                var dataVenc = new Date(data);
                dataVenc.setDate(dataVenc.getDate() + ((i - 1) * intervaloDias));

                var dataFormatada = ('0' + dataVenc.getDate()).slice(-2) + '/' +
                                   ('0' + (dataVenc.getMonth() + 1)).slice(-2) + '/' +
                                   dataVenc.getFullYear();

                var valor = valorParcela.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });

                lista.append('<li>Parcela ' + i + '/' + totalParcelas + ' - Venc: ' + dataFormatada + ' - Valor: ' + valor + '</li>');
            }

            $('#previewParcelas').show();
        } else {
            $('#previewParcelas').hide();
        }
    });

    // Criar parcelas
    $('.btn-criar-parcelas').on('click', function() {
        var formData = {
            recibo_provisorio_id: $('#txtParcelaReciboId').val(),
            total_parcelas: parseInt($('#txtTotalParcelas').val()),
            valor_total: parseFloat($('#txtValorTotalParcelas').val().replace(/\./g, '').replace(/,/g, '.')),
            data_primeira_parcela: $('#txtDataPrimeiraParcela').val(),
            intervalo_dias: parseInt($('#txtIntervaloDias').val())
        };

        $.ajax({
            url: '/criar-parcelas',
            type: 'POST',
            data: formData,
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });

                $('#modalCriarParcelas').modal('hide');
                $('#formCriarParcelas')[0].reset();
                $('#previewParcelas').hide();

                // Recarregar lista de parcelas
                carregarParcelas(formData.recibo_provisorio_id);
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
                    Toast.fire({
                        icon: 'error',
                        title: errorThrown
                    });
                }
            }
        });
    });

    // Marcar parcela como paga
    $(document).on('click', '.btn-marcar-paga', function() {
        var parcelaId = $(this).data('id');

        Swal.fire({
            title: 'Marcar como Paga?',
            text: 'A data de pagamento será definida como hoje.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, marcar',
            cancelButtonText: 'Cancelar',
            backdrop: false,
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Obter a data de hoje em formato YYYY-MM-DD
                var hoje = new Date().toISOString().split('T')[0];

                $.ajax({
                    url: '/marcar-parcela-paga/' + parcelaId,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        'data_pagamento': hoje
                    },
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });

                        carregarParcelas(reciboAtual.id);
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

    // Editar parcela
    $(document).on('click', '.btn-editar-parcela', function() {
        var parcelaId = $(this).data('id');

        $.ajax({
            url: '/listar-parcelas',
            type: 'GET',
            data: { recibo_provisorio_id: reciboAtual.id },
            success: function(response) {
                var parcela = response.find(p => p.id == parcelaId);

                if (parcela) {
                    $('#txtEditarParcelaId').val(parcela.id);
                    $('#txtEditarDataVencimento').val(parcela.data_vencimento);
                    $('#txtEditarValor').val(parseFloat(parcela.valor).toFixed(2).replace('.', ','));
                    $('#slcEditarStatus').val(parcela.status);
                    $('#txtEditarDataPagamento').val(parcela.data_pagamento || '');
                    $('#txtEditarObservacao').val(parcela.observacao || '');

                    // Exibir alertas de validação
                    atualizarValidacaoParcelas(response, parcelaId);

                    $('#modalEditarParcela').modal('show');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Toast.fire({
                    icon: 'error',
                    title: 'Erro ao carregar parcela'
                });
            }
        });
    });

    // Função para atualizar validação de parcelas
    function atualizarValidacaoParcelas(parcelas, parcelaIdEditando) {
        var valorRps = parseFloat(reciboAtual.valor) || 0;
        var totalParcelas = 0;

        // Calcular total das parcelas
        parcelas.forEach(function(p) {
            if (p.id == parcelaIdEditando) {
                // Usar o valor sendo editado
                var valorEditado = parseFloat($('#txtEditarValor').val().replace(/\./g, '').replace(/,/g, '.')) || 0;
                totalParcelas += valorEditado;
            } else {
                totalParcelas += parseFloat(p.valor) || 0;
            }
        });

        var diferenca = valorRps - totalParcelas;
        var diferencaFormatada = Math.abs(diferenca).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        var valorRpsFormatado = valorRps.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        var totalParcelasFormatado = totalParcelas.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        $('#spanValorRps').text(valorRpsFormatado);
        $('#spanTotalParcelas').text(totalParcelasFormatado);
        $('#spanDiferenca').text((diferenca >= 0 ? '+' : '-') + ' ' + diferencaFormatada);

        $('#alertValidacaoPagamento').show();

        // Mostrar avisos se necessário
        $('#alertAvisoValidacao').hide();
        $('#textoAvisoValidacao').html('');

        if (totalParcelas > valorRps) {
            $('#alertAvisoValidacao').show();
            $('#textoAvisoValidacao').html('<strong>⚠️ Atenção:</strong> O total das parcelas (' + totalParcelasFormatado + ') é maior que o valor da RPS (' + valorRpsFormatado + ')! Você não poderá salvar neste estado.');
            $('#alertAvisoValidacao').removeClass('alert-warning').addClass('alert-danger');
        } else if (totalParcelas < valorRps) {
            $('#alertAvisoValidacao').show();
            $('#textoAvisoValidacao').html('<strong>⚠️ Aviso:</strong> O total das parcelas (' + totalParcelasFormatado + ') é menor que o valor da RPS (' + valorRpsFormatado + '). Faltam ' + diferencaFormatada + ' para fechar a RPS.');
            $('#alertAvisoValidacao').removeClass('alert-danger').addClass('alert-warning');
        }
    }

    // Atualizar validação quando valor é alterado
    $(document).on('input', '#txtEditarValor', function() {
        $.ajax({
            url: '/listar-parcelas',
            type: 'GET',
            data: { recibo_provisorio_id: reciboAtual.id },
            success: function(response) {
                var parcelaId = $('#txtEditarParcelaId').val();
                atualizarValidacaoParcelas(response, parcelaId);
            }
        });
    });

    // Salvar edição da parcela
    $('.btn-salvar-edicao-parcela').on('click', function() {
        var parcelaId = $('#txtEditarParcelaId').val();
        var valorRps = parseFloat(reciboAtual.valor) || 0;
        var totalParcelas = 0;

        // Verificar validação antes de salvar
        $.ajax({
            url: '/listar-parcelas',
            type: 'GET',
            data: { recibo_provisorio_id: reciboAtual.id },
            success: function(response) {
                // Calcular total das parcelas com o novo valor
                response.forEach(function(p) {
                    if (p.id == parcelaId) {
                        var valorEditado = parseFloat($('#txtEditarValor').val().replace(/\./g, '').replace(/,/g, '.')) || 0;
                        totalParcelas += valorEditado;
                    } else {
                        totalParcelas += parseFloat(p.valor) || 0;
                    }
                });

                // Não permitir salvar se o total exceder o valor da RPS
                if (totalParcelas > valorRps) {
                    var totalParcelasFormatado = totalParcelas.toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    });
                    var valorRpsFormatado = valorRps.toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    });

                    Toast.fire({
                        icon: 'error',
                        title: 'Erro na Validação',
                        html: 'O total das parcelas (' + totalParcelasFormatado + ') não pode ser maior que o valor da RPS (' + valorRpsFormatado + ')!'
                    });
                    return;
                }

                // Se passou na validação, salvar
                var dados = {
                    data_vencimento: $('#txtEditarDataVencimento').val(),
                    valor: parseFloat($('#txtEditarValor').val().replace(/\./g, '').replace(/,/g, '.')),
                    status: $('#slcEditarStatus').val(),
                    data_pagamento: $('#txtEditarDataPagamento').val() || null,
                    observacao: $('#txtEditarObservacao').val()
                };

                $.ajax({
                    url: '/atualizar-parcela/' + parcelaId,
                    type: 'PUT',
                    data: dados,
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });

                        $('#modalEditarParcela').modal('hide');
                        carregarParcelas(reciboAtual.id);
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

    // Deletar parcela
    $(document).on('click', '.btn-deletar-parcela', function() {
        var parcelaId = $(this).data('id');

        Swal.fire({
            title: 'Confirmar exclusão?',
            text: 'Esta ação não poderá ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, deletar',
            cancelButtonText: 'Cancelar',
            backdrop: false,
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/deletar-parcela/' + parcelaId,
                    type: 'DELETE',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });

                        carregarParcelas(reciboAtual.id);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Toast.fire({
                            icon: 'error',
                            title: errorThrown
                        });
                    }
                });
            }
        });
    });

});