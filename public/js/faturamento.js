$(document).ready(function() {

    let userRole = 'admin'; // Default role
    let papel = 'admin'; // Get user role

    // Get user role from page data
    if (typeof window.userRole !== 'undefined') {
        papel = window.userRole;
    }

    // Carregar condições de pagamento do banco de dados
    carregarCondicoesPagamento();

    function carregarCondicoesPagamento() {
        $.ajax({
            url: '/todas-condicoes-pagamento',
            type: 'GET',
            success: function(response) {
                var select = $('#slcEmissaoRPSCondPagto');
                select.empty();
                select.append('<option value="">Selecione uma condição...</option>');

                response.forEach(function(condicao) {
                    var optionText = condicao.descricao;
                    // Usar ID como value para manter compatibilidade com backend
                    select.append('<option value="' + condicao.id + '" data-numero-parcelas="' + condicao.numero_parcelas + '" data-intervalo-dias="' + condicao.intervalo_dias + '">' + optionText + '</option>');
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Erro ao carregar condições de pagamento:', errorThrown);
                $('#slcEmissaoRPSCondPagto').empty();
                $('#slcEmissaoRPSCondPagto').append('<option value="">Erro ao carregar</option>');
            }
        });
    }

    // ===== NOVO: Carregar clientes disponíveis para RPS =====
    function carregarClientesParaRPS() {
        $.ajax({
            url: '/clientes-com-ordens-rps',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var lista = $('#listaClientesRPS');
                lista.empty();

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(i, cliente) {
                        var html = `
                            <button type="button" class="list-group-item list-group-item-action btn-selecionar-cliente-rps"
                                    data-cliente-id="${cliente.id}"
                                    data-cliente-nome="${cliente.nome}"
                                    data-cliente-codigo="${cliente.codigo}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${cliente.nome}</h6>
                                    <small class="text-muted">${cliente.codigo}</small>
                                </div>
                                <p class="mb-0 text-muted"><small>${cliente.numero_ordens} ordem(s) aguardando RPS</small></p>
                            </button>
                        `;
                        lista.append(html);
                    });
                } else {
                    lista.html('<div class="list-group-item text-muted text-center"><small>Nenhum cliente com ordens aguardando RPS</small></div>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Erro ao carregar clientes:', errorThrown);
                $('#listaClientesRPS').html('<div class="list-group-item text-danger"><small>Erro ao carregar clientes</small></div>');
            }
        });
    }

    // ===== NOVO: Filtrar lista de clientes durante busca =====
    $('#inputBuscaCliente').on('keyup', function() {
        var termo = $(this).val().toLowerCase();
        $('#listaClientesRPS .btn-selecionar-cliente-rps').each(function() {
            var nome = $(this).data('cliente-nome').toLowerCase();
            var codigo = $(this).data('cliente-codigo').toLowerCase();

            if (nome.includes(termo) || codigo.includes(termo)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // ===== NOVO: Ao selecionar cliente, filtrar tabela e abrir modal de RPS =====
    $(document).on('click', '.btn-selecionar-cliente-rps', function() {
        var cliente_id = $(this).data('cliente-id');
        var cliente_nome = $(this).data('cliente-nome');

        console.log('Cliente selecionado:', cliente_id, cliente_nome);

        // Fechar modal de seleção
        var modalSelecionarCliente = bootstrap.Modal.getInstance(document.getElementById('modalSelecionarCliente'));
        if (modalSelecionarCliente) {
            modalSelecionarCliente.hide();
        }

        // Filtrar tabela para mostrar apenas ordens deste cliente com status = 6
        filtrarTabelaPorClienteRPS(cliente_id, cliente_nome);
    });

    // ===== NOVO: Filtrar tabela por cliente e abrir seleção de RPS =====
    function filtrarTabelaPorClienteRPS(cliente_id, cliente_nome) {
        var ordem_arr = [];
        var valor_total = 0;

        // Limpar filtros anteriores
        tblFaturamento.search('').draw();

        // Buscar todas as ordens do cliente com status = 6 (AGUARDANDO_RPS)
        $('#tblFaturamento tbody tr').each(function() {
            var rowData = tblFaturamento.row($(this)).data();

            if (rowData && rowData.status == 6 && rowData.cliente_id == cliente_id) {
                ordem_arr.push({
                    id: rowData.id,
                    numero: ('00000000' + rowData.id).slice(-8),
                    valor: parseFloat(rowData.valor_total || 0)
                });
                valor_total += parseFloat(rowData.valor_total || 0);
            }
        });

        console.log('Ordens encontradas:', ordem_arr, 'Valor total:', valor_total);

        if (ordem_arr.length > 0) {
            // Mostrar checkbox para seleção múltipla
            abrirModalSelecaoRPS(cliente_id, cliente_nome, ordem_arr, valor_total);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Sem ordens disponíveis',
                text: `Nenhuma ordem aguardando RPS para o cliente ${cliente_nome}`
            });
        }
    }

    // ===== NOVO: Modal para seleção de múltiplas RPS =====
    function abrirModalSelecaoRPS(cliente_id, cliente_nome, ordem_arr, valor_total) {
        var checkboxesHTML = `
            <div class="mb-3">
                <p><strong>Selecione quais ordens deseja agrupar para este RPS:</strong></p>
                <p class="text-muted"><small>Cliente: <strong>${cliente_nome}</strong></small></p>
            </div>
            <div style="max-height: 300px; overflow-y: auto;">
        `;

        $.each(ordem_arr, function(i, ordem) {
            checkboxesHTML += `
                <div class="form-check" style="margin-bottom: 10px;">
                    <input class="form-check-input rps-checkbox-novo" type="checkbox"
                           id="rps_novo_${ordem.id}" value="${ordem.id}" checked>
                    <label class="form-check-label" for="rps_novo_${ordem.id}">
                        OS ${ordem.numero} - R$ ${parseFloat(ordem.valor).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'})}
                    </label>
                </div>
            `;
        });

        checkboxesHTML += '</div>';

        Swal.fire({
            title: 'Selecionar Ordens para Agrupar',
            html: checkboxesHTML,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Confirmar Seleção',
            cancelButtonText: 'Voltar',
            backdrop: true,
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            },
            didOpen: (modal) => {
                // Evento para atualizar total quando checkbox muda
                modal.querySelectorAll('.rps-checkbox-novo').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        atualizarValorTotalModal(ordem_arr);
                    });
                });
                // Mostrar valor inicial
                atualizarValorTotalModal(ordem_arr);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Coletar ordens selecionadas
                var ordem_arr_final = [];
                var valor_total_final = 0;

                document.querySelectorAll('.rps-checkbox-novo:checked').forEach(checkbox => {
                    var id = parseInt(checkbox.value);
                    var ordem = ordem_arr.find(o => o.id == id);
                    if (ordem) {
                        ordem_arr_final.push(ordem.id);
                        valor_total_final += ordem.valor;
                    }
                });

                // Se nada foi selecionado, usar todas
                if (ordem_arr_final.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selecione pelo menos uma ordem'
                    });
                    return;
                }

                // Abrir modal de emissão (já existente)
                abrirModalEmissaoRPS(cliente_id, cliente_nome, ordem_arr_final, valor_total_final);
            }
        });
    }

    // ===== NOVO: Atualizar total no modal de seleção =====
    function atualizarValorTotalModal(ordem_arr) {
        var valor_total = 0;
        var checked = [];

        document.querySelectorAll('.rps-checkbox-novo:checked').forEach(checkbox => {
            var id = parseInt(checkbox.value);
            var ordem = ordem_arr.find(o => o.id == id);
            if (ordem) {
                valor_total += ordem.valor;
                checked.push(ordem.numero);
            }
        });

        var totalFormatado = valor_total.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        var swalContent = document.querySelector('.swal2-html-container');
        if (swalContent) {
            var msgAnterior = swalContent.querySelector('.total-selecionado-modal');
            if (msgAnterior) {
                msgAnterior.remove();
            }

            if (checked.length > 0) {
                var msgExtra = `
                    <div class="total-selecionado-modal mt-3 p-3 bg-light border rounded">
                        <p class="mb-2"><strong>${checked.length} ordem(s) selecionada(s)</strong></p>
                        <p class="mb-0"><strong>Total:</strong> <span class="text-success">${totalFormatado}</span></p>
                    </div>
                `;
                swalContent.insertAdjacentHTML('beforeend', msgExtra);
            }
        }
    }

    let tblFaturamento = $('#tblFaturamento').DataTable({
        ajax: {
            url: '/listar-ordens-faturamento',
            dataSrc: 'data',
            complete: function(jqXHR) {
                // Capture user role from response
                if (jqXHR.responseJSON && jqXHR.responseJSON.user_role) {
                    userRole = jqXHR.responseJSON.user_role;
                    papel = userRole;
                }
            }
        },
        columnDefs: [{
            targets: [3, 5],
            visible: false
        }, {
            // Hide consultor column for consultores
            targets: [5],
            visible: papel != 'consultor' ? true : false
        }],
        columns: [{
            title: '',
            data: 'id',
            className: 'dt-center noVis',
            orderable: false,
            width: '50px',
            render: function(data, type, row) {
                var html = '<input type="checkbox" class="form-check-input check-faturamento-row" value="1" />';
                return html;
            }
        },{
            title: 'C&oacute;digo',
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
            title: 'C&oacute;digo Cliente',
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
            render: function(data, type, row) {
                var html = data ? parseFloat(data).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }) : '0,00';

                return html;
            }
        },{
            title: 'Status',
            data: 'status',
            className: 'dt-center',
            width: '150px',
            render: function(data, type, row) {
                var html = '';

                switch(data) {
                    case 1:
                        html = '<span class="status-1-text">Aberta</span>';
                        break;
                    case 2:
                        html = '<span class="status-2-text">Aguardando Aprova&ccedil;&atilde;o</span>';
                        break;
                    case 3:
                        html = '<span class="status-3-text">Contestada</span>';
                        break;
                    case 4:
                        html = '<span class="status-4-text">Aguardando Faturamento</span>';
                        break;
                    case 5:
                        html = '<span class="status-5-text">Faturada</span>';
                        break;
                    case 6:
                        html = '<span class="status-6-text">Aguardando RPS</span>';
                        break;
                    case 7:
                        html = '<span class="status-7-text">RPS Emitida</span>';
                        break;
                    default:
                        html = '<span class="status-unknown">Desconhecido</span>';
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
                html += '<li><a class="dropdown-item" href="javascript:void(0);">Visualizar</a></li>';
                html += '</ul>';
                html += '</div>';

                return html;
            }
        }],
        order: [[1, 'desc']],
        buttons: {
            name: 'primary',
            buttons: [{
                text: 'Faturar',
                className: 'btn-primary',
                visible: papel == 'financeiro' || papel == 'admin',
                action: function (e, dt, node, config) {
                    var ordem_arr = [];
                    var cliente = '';
                    var ordens = '';
                    $('#tblFaturamento').find('.check-faturamento-row:checked').each(function() {
                        var row = $(this).closest('tr');
                        var rowData = tblFaturamento.row(row).data();

                        if (rowData.status == 4) {
                            cliente = rowData.cliente_nome;
                            ordens += ordens != '' ? (', ' + ('00000000' + rowData.id).slice(-8)) : ('00000000' + rowData.id).slice(-8);
                            ordem_arr.push(rowData.id);
                        }
                    });

                    if (ordem_arr.length > 0) {
                        var msg = ordem_arr.length == 1 ? ('Faturar a ordem <b>' + ordens + '</b> do cliente <b>' + cliente + '</b>.') : ('Faturar as ordens <b>' + ordens + '</b> do cliente <b>' + cliente + '</b>.');

                        Swal.fire({
                            html: msg,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Faturar',
                            cancelButtonText: 'Cancelar',
                            backdrop: true,
                            customClass: {
                                confirmButton: 'btn btn-success',
                                cancelButton: 'btn btn-secondary'
                            },
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var data = {
                                    id_list: ordem_arr
                                }

                                $.ajax({
                                    url: '/faturar-ordens-servico',
                                    type: 'POST',
                                    data: data,
                                    success: function(response) {
                                        tblFaturamento.ajax.reload();

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
                    } else {
                        Swal.fire({
                            title: 'Nenhuma OS válida para faturar selecionada',
                            icon: 'warning'
                        });
                    }
                }
            },{
                text: 'Emitir RPS',
                className: 'btn-primary',
                visible: papel == 'financeiro' || papel == 'admin',
                action: function(e, dt, node, config) {
                    // Abrir modal de seleção de clientes
                    carregarClientesParaRPS();
                    var modalSelecionarCliente = new bootstrap.Modal(
                        document.getElementById('modalSelecionarCliente'),
                        { backdrop: 'static', keyboard: false }
                    );
                    modalSelecionarCliente.show();
                }
            },{
                extend: 'colvis',
                columns: ':not(.noVis)',
                text: 'Colunas'
            }]
        },
        initComplete: function (settings, json) {
            initializeTooltips();

            $('#tblFaturamento tbody').on('click', '.check-faturamento-row', function() {
                var row = $(this).closest('tr');
                var rowData = tblFaturamento.row(row).data();

                $('#tblFaturamento tbody .check-faturamento-row:checked').each(function() {
                    var _row = $(this).closest('tr');
                    var _rowData = tblFaturamento.row(_row).data();

                    if (rowData.cliente_id !== _rowData.cliente_id) {
                        $(this).prop('checked', false);
                    }
                });
            });
        }
    });

    // Função para atualizar valor total selecionado nos checkboxes
    function atualizarValorTotalSelecionado(ordem_arr, outrasRPS) {
        var valor_total = 0;
        var outrasCheckados = [];

        // Somar valor das ordens selecionadas originalmente
        $.each(ordem_arr, function(i, id) {
            $('#tblFaturamento tbody tr').each(function() {
                var rowData = tblFaturamento.row($(this)).data();
                if (rowData && rowData.id == id) {
                    valor_total += parseFloat(rowData.valor_total || 0);
                    return false;
                }
            });
        });

        // Somar valor das outras RPS que foram marcadas
        $.each(outrasRPS, function(i, rps) {
            var checkbox = document.getElementById('rps_' + rps.id);
            if (checkbox && checkbox.checked) {
                valor_total += parseFloat(rps.valor || 0);
                outrasCheckados.push(rps.numero);
            }
        });

        // Atualizar a mensagem do modal com o total selecionado
        var totalFormatado = valor_total.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        // Se há outras RPS selecionadas, mostrar aviso com o total
        if (outrasCheckados.length > 0) {
            var msgExtra = '<p style="color: #28a745; margin-top: 15px; font-weight: bold;">Total a agrupar: <strong>' + totalFormatado + '</strong></p>';
            var swalContent = document.querySelector('.swal2-html-container');
            if (swalContent) {
                // Remover mensagem anterior se existir
                var msgAnterior = swalContent.querySelector('.total-selecionado');
                if (msgAnterior) {
                    msgAnterior.remove();
                }
                // Adicionar nova mensagem
                swalContent.insertAdjacentHTML('beforeend', '<div class="total-selecionado">' + msgExtra + '</div>');
            }
        }
    }

    // Função auxiliar para abrir modal de emissão de RPS
    function abrirModalEmissaoRPS(cliente_id, cliente, ordem_arr, valor_total) {
        var ordens = '';
        $.each(ordem_arr, function(i, id) {
            ordens += ordens != '' ? (', ' + ('00000000' + id).slice(-8)) : ('00000000' + id).slice(-8);
        });

        var msg = ordem_arr.length == 1 ? ('Emitir RPS para a ordem <b>' + ordens + '</b> do cliente <b>' + cliente + '</b>.') : ('Emitir RPS para as ordens <b>' + ordens + '</b> do cliente <b>' + cliente + '</b>.');

        $('#txtEmissaoRPSClienteId').val(cliente_id);
        $('#txtEmissaoRPSOrdens').val(ordem_arr);
        $('#modalEmissaoRPSLabel').html(msg);
        $('#txtEmissaoRPSValor').val(valor_total);
        $('#txtEmissaoRPSValorMascarado').val(valor_total.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }));

        $('#modalEmissaoRPS').modal('show');
        $('#txtEmissaoRPSNumero').focus();
    }

    // Controlar exibição de parcelas conforme condição de pagamento
    $('#slcEmissaoRPSCondPagto').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var numeroParcelas = parseInt(selectedOption.data('numero-parcelas')) || 1;

        if (numeroParcelas > 1) {
            // Atualizar intervalo de dias com o valor padrão da condição
            var intervaloDias = selectedOption.data('intervalo-dias') || 30;
            $('#txtIntervaloDiasParcelas').val(intervaloDias);

            $('#divConfiguracaoParcelas').show();
            $('#txtDataPrimeiraParcela').val(new Date().toISOString().split('T')[0]);

            // Atualizar preview automaticamente
            gerarPreviewParcelasRPS();
        } else {
            $('#divConfiguracaoParcelas').hide();
            $('#previewParcelasRPS').hide();
        }
    });

    // Gerar preview de parcelas
    function gerarPreviewParcelasRPS() {
        var selectedOption = $('#slcEmissaoRPSCondPagto').find('option:selected');
        var dataPrimeira = $('#txtDataPrimeiraParcela').val();
        var intervaloDias = parseInt($('#txtIntervaloDiasParcelas').val()) || 30;
        var valorTotal = parseFloat($('#txtEmissaoRPSValor').val()) || 0;
        var consolidada = $('#chkConsolidaRPS').is(':checked');
        var numeroParcelas = parseInt(selectedOption.data('numero-parcelas')) || 1;

        if (numeroParcelas <= 1 || !dataPrimeira || valorTotal <= 0) {
            $('#previewParcelasRPS').hide();
            return;
        }

        // Usar número de parcelas da condição (ou 1 se consolidada)
        var totalParcelas = consolidada ? 1 : numeroParcelas;
        var valorParcela = valorTotal / totalParcelas;

        var lista = $('#listaPreviewParcelasRPS');
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

            if (consolidada) {
                lista.append('<li><strong>Pagamento Único</strong> - Venc: ' + dataFormatada + ' - Valor: ' + valor + '</li>');
            } else {
                lista.append('<li>Parcela ' + i + '/' + totalParcelas + ' - Venc: ' + dataFormatada + ' - Valor: ' + valor + '</li>');
            }
        }

        $('#previewParcelasRPS').show();
    }

    // Atualizar preview ao alterar data, intervalo ou consolidação
    $('#txtDataPrimeiraParcela, #txtIntervaloDiasParcelas, #chkConsolidaRPS').on('change input', function() {
        gerarPreviewParcelasRPS();
    });

    /*
    let lista_cond_pagto = $('#slcEmissaoRPSCondPagto').select2({
        dropdownParent: $('#modalEmissaoRPS'),
        language: 'pt-BR',
        placeholder: 'Selecione ...',
        theme: 'bootstrap-5'
    });
    */

    $('.btn-salvar-rps').on('click', function() {
        const form = $('#formEmissaoRPS');

        if (validateFormRequired(form)) {
            var formData = form.serialize();
            var selectedOption = $('#slcEmissaoRPSCondPagto').find('option:selected');
            var numeroParcelas = parseInt(selectedOption.data('numero-parcelas')) || 1;

            // Se é parcelado (mais de 1 parcela), adicionar dados de parcelas ao formData
            if (numeroParcelas > 1) {
                var dataPrimeira = $('#txtDataPrimeiraParcela').val();
                var intervaloDias = parseInt($('#txtIntervaloDiasParcelas').val()) || 30;
                var consolidada = $('#chkConsolidaRPS').is(':checked');
                var totalParcelas = consolidada ? 1 : numeroParcelas;

                // Adicionar parâmetros de parcelamento ao form data
                formData += '&data_primeira_parcela=' + encodeURIComponent(dataPrimeira);
                formData += '&intervalo_dias=' + intervaloDias;
                formData += '&total_parcelas=' + totalParcelas;
                formData += '&consolidada=' + (consolidada ? 1 : 0);
            }

            $.ajax({
                url: '/salvar_rps',
                type: 'POST',
                data: formData,
                success: function(response) {
                    tblFaturamento.ajax.reload();
                    $('#modalEmissaoRPS').modal('hide');
                    $('#formEmissaoRPS')[0].reset();
                    $('#divConfiguracaoParcelas').hide();

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
