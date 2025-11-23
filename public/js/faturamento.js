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

    // ===== NOVO: Modal para seleção de múltiplas RPS (melhorado) =====
    function abrirModalSelecaoRPS(cliente_id, cliente_nome, ordem_arr, valor_total) {
        var ordensHTML = '';

        $.each(ordem_arr, function(i, ordem) {
            ordensHTML += `
                <div class="rps-ordem-card">
                    <div class="rps-ordem-checkbox">
                        <input class="form-check-input rps-checkbox-novo" type="checkbox"
                               id="rps_novo_${ordem.id}" value="${ordem.id}" checked>
                    </div>
                    <div class="rps-ordem-info">
                        <div class="rps-ordem-numero">OS ${ordem.numero}</div>
                        <div class="rps-ordem-valor">${parseFloat(ordem.valor).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'})}</div>
                    </div>
                </div>
            `;
        });

        var checkboxesHTML = `
            <div class="rps-selecao-container">
                <div class="rps-header-selecao">
                    <div class="rps-cliente-info">
                        <i class="bi bi-building"></i>
                        <strong>${cliente_nome}</strong>
                    </div>
                    <div class="rps-total-header">
                        Total: <span id="totalHeaderSelecao">R$ 0,00</span>
                    </div>
                </div>

                <div class="rps-ordens-list">
                    ${ordensHTML}
                </div>

                <div class="rps-resumo-selecao">
                    <div class="rps-resumo-item">
                        <span>Ordens Selecionadas:</span>
                        <strong id="ordensCount">0</strong>
                    </div>
                    <div class="rps-resumo-total">
                        <span>Total a Emitir:</span>
                        <strong id="totalSelecao">R$ 0,00</strong>
                    </div>
                </div>
            </div>

            <style>
                .rps-selecao-container {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                }

                .rps-header-selecao {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 16px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }

                .rps-cliente-info {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 16px;
                }

                .rps-cliente-info i {
                    font-size: 24px;
                }

                .rps-total-header {
                    font-size: 14px;
                    opacity: 0.9;
                }

                .rps-ordens-list {
                    max-height: 350px;
                    overflow-y: auto;
                    margin-bottom: 20px;
                    padding: 10px;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    background: #f9f9f9;
                }

                .rps-ordem-card {
                    display: flex;
                    align-items: center;
                    padding: 14px;
                    margin-bottom: 10px;
                    background: white;
                    border: 2px solid #f0f0f0;
                    border-radius: 6px;
                    transition: all 0.3s ease;
                    cursor: pointer;
                }

                .rps-ordem-card:hover {
                    border-color: #667eea;
                    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
                }

                .rps-ordem-card input:checked ~ .rps-ordem-info {
                    color: #667eea;
                    font-weight: 500;
                }

                .rps-ordem-checkbox {
                    margin-right: 12px;
                    display: flex;
                    align-items: center;
                }

                .rps-ordem-checkbox input {
                    width: 20px;
                    height: 20px;
                    cursor: pointer;
                    accent-color: #667eea;
                }

                .rps-ordem-info {
                    flex: 1;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    transition: all 0.2s ease;
                }

                .rps-ordem-numero {
                    font-size: 15px;
                    font-weight: 500;
                    color: #333;
                }

                .rps-ordem-valor {
                    font-size: 16px;
                    font-weight: 600;
                    color: #667eea;
                }

                .rps-resumo-selecao {
                    padding: 16px;
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                    border-radius: 8px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .rps-resumo-item,
                .rps-resumo-total {
                    display: flex;
                    flex-direction: column;
                    gap: 8px;
                }

                .rps-resumo-item span,
                .rps-resumo-total span {
                    font-size: 13px;
                    color: #666;
                    font-weight: 500;
                }

                .rps-resumo-item strong,
                .rps-resumo-total strong {
                    font-size: 18px;
                    color: #333;
                }

                .rps-resumo-total strong {
                    color: #667eea;
                    font-size: 20px;
                }

                /* Scroll customizado */
                .rps-ordens-list::-webkit-scrollbar {
                    width: 8px;
                }

                .rps-ordens-list::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 4px;
                }

                .rps-ordens-list::-webkit-scrollbar-thumb {
                    background: #667eea;
                    border-radius: 4px;
                }

                .rps-ordens-list::-webkit-scrollbar-thumb:hover {
                    background: #5568d3;
                }
            </style>
        `;

        Swal.fire({
            title: 'Selecionar Ordens para Agrupar',
            html: checkboxesHTML,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '✓ Confirmar Seleção',
            cancelButtonText: '↶ Voltar',
            backdrop: true,
            width: '600px',
            customClass: {
                confirmButton: 'btn btn-success btn-lg',
                cancelButton: 'btn btn-secondary btn-lg',
                popup: 'swal-rps-popup'
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

        // Atualizar elementos do novo design melhorado
        var ordensCountEl = document.getElementById('ordensCount');
        var totalSelecaoEl = document.getElementById('totalSelecao');
        var totalHeaderEl = document.getElementById('totalHeaderSelecao');

        if (ordensCountEl) {
            ordensCountEl.textContent = checked.length;
        }

        if (totalSelecaoEl) {
            totalSelecaoEl.textContent = totalFormatado;
        }

        if (totalHeaderEl) {
            totalHeaderEl.textContent = totalFormatado;
        }
    }

    // ===== NOVO: Funções para Faturamento de Ordens de Serviço =====
    function carregarClientesParaFaturamento() {
        $.ajax({
            url: '/clientes-com-ordens-faturar',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var lista = $('#listaClientesFaturamento');
                lista.empty();

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(i, cliente) {
                        var html = `
                            <button type="button" class="list-group-item list-group-item-action btn-selecionar-cliente-faturamento"
                                    data-cliente-id="${cliente.id}"
                                    data-cliente-nome="${cliente.nome}"
                                    data-cliente-codigo="${cliente.codigo}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${cliente.nome}</h6>
                                    <small class="text-muted">${cliente.codigo}</small>
                                </div>
                                <p class="mb-0 text-muted"><small>${cliente.numero_ordens} ordem(s) aguardando faturamento</small></p>
                            </button>
                        `;
                        lista.append(html);
                    });
                } else {
                    lista.append('<div class="list-group-item text-muted"><small>Nenhum cliente com ordens para faturar</small></div>');
                }
            }
        });
    }

    // Busca em tempo real por cliente (faturamento)
    $('#inputBuscaClienteFaturamento').on('keyup', function() {
        var termo = $(this).val().toLowerCase();
        $('#listaClientesFaturamento .btn-selecionar-cliente-faturamento').each(function() {
            var nome = $(this).data('cliente-nome').toLowerCase();
            var codigo = $(this).data('cliente-codigo').toLowerCase();

            if (nome.includes(termo) || codigo.includes(termo)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Selecionar cliente para faturamento
    $(document).on('click', '.btn-selecionar-cliente-faturamento', function() {
        var cliente_id = $(this).data('cliente-id');
        var cliente_nome = $(this).data('cliente-nome');

        var modalSelecionarCliente = bootstrap.Modal.getInstance(document.getElementById('modalSelecionarClienteFaturamento'));
        if (modalSelecionarCliente) {
            modalSelecionarCliente.hide();
        }

        filtrarTabelaPorClienteFaturamento(cliente_id, cliente_nome);
    });

    // Filtrar tabela por cliente e abrir modal de seleção de ordens
    function filtrarTabelaPorClienteFaturamento(cliente_id, cliente_nome) {
        var ordem_arr = [];
        var valor_total = 0;

        $('#tblFaturamento tbody tr').each(function() {
            var row = $(this);
            var rowData = tblFaturamento.row(row).data();

            if (rowData.cliente_id == cliente_id && rowData.status == 4) {
                ordem_arr.push({
                    id: rowData.id,
                    numero: ('00000000' + rowData.id).slice(-8),
                    valor: parseFloat(rowData.valor_total),
                    cliente_nome: rowData.cliente_nome,
                    descricao: rowData.assunto
                });
                valor_total += parseFloat(rowData.valor_total);
            }
        });

        if (ordem_arr.length > 0) {
            abrirModalSelecaoOSFaturamento(cliente_id, cliente_nome, ordem_arr, valor_total);
        } else {
            Swal.fire({
                title: 'Nenhuma Ordem',
                text: 'Cliente selecionado não possui ordens para faturar.',
                icon: 'info'
            });
        }
    }

    // Modal de seleção de múltiplas ordens para faturamento
    function abrirModalSelecaoOSFaturamento(cliente_id, cliente_nome, ordem_arr, valor_total) {
        var ordensHTML = '';
        var totalFormatado = valor_total.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        ordem_arr.forEach(function(ordem) {
            var valorFormatado = ordem.valor.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
            ordensHTML += `
                <div class="rps-ordem-item">
                    <input type="checkbox" class="rps-checkbox-faturamento" value="${ordem.id}" checked>
                    <div class="rps-ordem-info">
                        <div class="rps-ordem-numero">OS ${ordem.numero}</div>
                        <div class="rps-ordem-assunto">${ordem.descricao}</div>
                        <div class="rps-ordem-valor">${valorFormatado}</div>
                    </div>
                </div>
            `;
        });

        var checkboxesHTML = `
            <div class="rps-selecao-container">
                <div class="rps-header-selecao">
                    <div class="rps-cliente-info">
                        <i class="bi bi-building"></i>
                        <strong>${cliente_nome}</strong>
                    </div>
                    <div class="rps-total-header">
                        Total: <span id="totalHeaderFaturamento">R$ 0,00</span>
                    </div>
                </div>

                <div class="rps-ordens-list">
                    ${ordensHTML}
                </div>

                <div class="rps-resumo-selecao">
                    <div class="rps-resumo-item">
                        <span>Ordens Selecionadas:</span>
                        <strong id="ordensCountFaturamento">0</strong>
                    </div>
                    <div class="rps-resumo-total">
                        <span>Total a Faturar:</span>
                        <strong id="totalFaturamento">R$ 0,00</strong>
                    </div>
                </div>
            </div>

            <style>
                .rps-selecao-container {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }

                .rps-header-selecao {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px;
                    border-radius: 8px 8px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 15px;
                }

                .rps-cliente-info {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 16px;
                    font-weight: 600;
                }

                .rps-cliente-info i {
                    font-size: 24px;
                }

                .rps-total-header {
                    font-size: 14px;
                    opacity: 0.95;
                }

                .rps-total-header span {
                    font-weight: 700;
                    font-size: 18px;
                }

                .rps-ordens-list {
                    max-height: 400px;
                    overflow-y: auto;
                    margin-bottom: 15px;
                    padding: 5px;
                }

                .rps-ordens-list::-webkit-scrollbar {
                    width: 6px;
                }

                .rps-ordens-list::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 3px;
                }

                .rps-ordens-list::-webkit-scrollbar-thumb {
                    background: #888;
                    border-radius: 3px;
                }

                .rps-ordens-list::-webkit-scrollbar-thumb:hover {
                    background: #555;
                }

                .rps-ordem-item {
                    display: flex;
                    gap: 12px;
                    padding: 12px;
                    border: 1px solid #e0e0e0;
                    border-radius: 6px;
                    margin-bottom: 8px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    background: white;
                }

                .rps-ordem-item:hover {
                    border-color: #667eea;
                    background: #f8f9ff;
                    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
                }

                .rps-ordem-item input[type="checkbox"] {
                    margin-top: 3px;
                    cursor: pointer;
                }

                .rps-ordem-info {
                    flex: 1;
                }

                .rps-ordem-numero {
                    font-weight: 600;
                    color: #333;
                    font-size: 14px;
                }

                .rps-ordem-assunto {
                    font-size: 12px;
                    color: #666;
                    margin: 4px 0;
                }

                .rps-ordem-valor {
                    font-weight: 600;
                    color: #667eea;
                    font-size: 13px;
                    text-align: right;
                }

                .rps-resumo-selecao {
                    background: #f5f5f5;
                    padding: 12px 15px;
                    border-radius: 6px;
                    display: flex;
                    justify-content: space-between;
                    border: 1px solid #e0e0e0;
                }

                .rps-resumo-item,
                .rps-resumo-total {
                    display: flex;
                    justify-content: space-between;
                    gap: 20px;
                    font-size: 14px;
                }

                .rps-resumo-total {
                    font-weight: 600;
                    color: #667eea;
                }
            </style>
        `;

        Swal.fire({
            title: 'Selecionar Ordens de Serviço',
            html: checkboxesHTML,
            icon: 'info',
            width: 600,
            showCancelButton: true,
            confirmButtonText: 'Confirmar Seleção',
            cancelButtonText: 'Voltar',
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            },
            didOpen: function() {
                atualizarValorTotalFaturamento(ordem_arr);

                document.querySelectorAll('.rps-checkbox-faturamento').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        atualizarValorTotalFaturamento(ordem_arr);
                    });
                });

                document.querySelectorAll('.rps-ordem-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        if (e.target.tagName !== 'INPUT') {
                            var checkbox = this.querySelector('.rps-checkbox-faturamento');
                            checkbox.checked = !checkbox.checked;
                            atualizarValorTotalFaturamento(ordem_arr);
                        }
                    });
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var ordem_ids = [];
                document.querySelectorAll('.rps-checkbox-faturamento:checked').forEach(checkbox => {
                    ordem_ids.push(parseInt(checkbox.value));
                });

                if (ordem_ids.length > 0) {
                    var data = {
                        id_list: ordem_ids
                    };

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
                            Toast.fire({
                                icon: 'error',
                                title: 'Erro ao faturar ordens'
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Atenção',
                        text: 'Selecione pelo menos uma ordem para faturar.',
                        icon: 'warning'
                    });
                }
            }
        });
    }

    function atualizarValorTotalFaturamento(ordem_arr) {
        var valor_total = 0;
        var checked = [];

        document.querySelectorAll('.rps-checkbox-faturamento:checked').forEach(checkbox => {
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

        var ordensCountEl = document.getElementById('ordensCountFaturamento');
        var totalSelecaoEl = document.getElementById('totalFaturamento');
        var totalHeaderEl = document.getElementById('totalHeaderFaturamento');

        if (ordensCountEl) {
            ordensCountEl.textContent = checked.length;
        }

        if (totalSelecaoEl) {
            totalSelecaoEl.textContent = totalFormatado;
        }

        if (totalHeaderEl) {
            totalHeaderEl.textContent = totalFormatado;
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
            width: '100px',
            render: function(data, type, row) {
                var html = '';

                html += '<div class="dropdown">';
                html += '<button class="btn btn-sm btn-outline-primary border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
                html += '<i class="bi bi-list"></i>';
                html += '</button>';
                html += '<ul class="dropdown-menu">';
                html += '<li><a class="dropdown-item" href="javascript:void(0);">Visualizar</a></li>';

                // Mostrar opção de Reenviar Email apenas para status 5, 6 e 7 (Faturado, Aguardando RPS, RPS Emitida)
                if (row.status >= 5) {
                    html += '<li><hr class="dropdown-divider"></li>';
                    html += '<li><a class="dropdown-item btn-reenviar-email" href="javascript:void(0);" data-os-id="' + row.id + '"><i class="bi bi-envelope-arrow-up"></i> Reenviar Email</a></li>';
                }

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
                action: function(e, dt, node, config) {
                    // Abrir modal de seleção de clientes para faturamento
                    carregarClientesParaFaturamento();
                    var modalSelecionarCliente = new bootstrap.Modal(
                        document.getElementById('modalSelecionarClienteFaturamento'),
                        { backdrop: 'static', keyboard: false }
                    );
                    modalSelecionarCliente.show();
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

    // ========== REENVIAR EMAIL DE OS ==========
    let acaoReenvio = {
        osId: null,
        modalElement: null,

        init: function() {
            this.modalElement = new bootstrap.Modal(document.getElementById('modalReenviarEmailOS'), {
                backdrop: 'static',
                keyboard: false
            });

            // Evento de clique no botão de reenvio
            $(document).on('click', '.btn-reenviar-email', function(e) {
                e.preventDefault();
                let osId = $(this).data('os-id');
                acaoReenvio.abrirModal(osId);
            });

            // Evento do botão Reenviar no modal
            $('#btnReenviarEmail').on('click', function() {
                acaoReenvio.reenviarEmail();
            });

            // Atualizar alerta conforme opção selecionada
            $('input[name="opcaoReenvio"]').on('change', function() {
                acaoReenvio.atualizarAlerta();
            });
        },

        abrirModal: function(osId) {
            this.osId = osId;
            $('#txtOSIdReenvio').val(osId);

            // Resetar seleção e alerta
            $('#opcaoAmbos').prop('checked', true);
            this.atualizarAlerta();

            this.modalElement.show();
        },

        atualizarAlerta: function() {
            let opcao = $('input[name="opcaoReenvio"]:checked').val();
            let divAlerta = $('#divAlertaReenvio');
            let textoAlerta = '';

            switch(opcao) {
                case 'consultor':
                    textoAlerta = 'O email será reenviado apenas para o <strong>Consultor</strong> responsável pela Ordem de Serviço.';
                    break;
                case 'cliente':
                    textoAlerta = 'O email será reenviado apenas para o <strong>Cliente</strong> (contato principal).';
                    break;
                case 'ambos':
                    textoAlerta = 'O email será reenviado para <strong>Consultor e Cliente</strong>.';
                    break;
            }

            $('#textoAlertaReenvio').html(textoAlerta);
            divAlerta.removeClass('d-none');
        },

        reenviarEmail: function() {
            let osId = $('#txtOSIdReenvio').val();
            let opcao = $('input[name="opcaoReenvio"]:checked').val();

            if (!osId || !opcao) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Por favor, selecione uma opção'
                });
                return;
            }

            // Desabilitar botão durante requisição
            let btnReenviar = $('#btnReenviarEmail');
            let textoBtnOriginal = btnReenviar.html();
            btnReenviar.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Enviando...');

            $.ajax({
                type: 'POST',
                url: `/os/${osId}/reenviar-email`,
                data: {
                    recipient: opcao,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });

                        // Mostrar detalhes do reenvio
                        if (response.result && response.result.details) {
                            let detalhes = response.result.details.map(d => {
                                return d.recipient + ': ' + (d.status === 'sent' ? '✓ Enviado' : '✗ Erro: ' + d.error);
                            }).join('\n');

                            console.log('Detalhes do reenvio:\n' + detalhes);
                        }

                        // Fechar modal
                        acaoReenvio.modalElement.hide();

                        // Recarregar tabela se existir
                        if ($.fn.DataTable.isDataTable('#tblFaturamento')) {
                            $('#tblFaturamento').DataTable().ajax.reload();
                        }
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message || 'Erro ao reenviar email'
                        });

                        // Mostrar detalhes de erros
                        if (response.result && response.result.messages) {
                            console.error('Erros ao reenviar:', response.result.messages);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'Erro ao processar reenvio';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            let errorsHtml = '';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                $.each(value, function(index, erro) {
                                    errorsHtml += errorsHtml == '' ? erro : ('<br>' + erro);
                                });
                            });
                            errorMsg = errorsHtml;
                        } else if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                    }

                    Toast.fire({
                        icon: 'error',
                        title: errorMsg
                    });

                    console.error('Erro AJAX:', {status, error, response: xhr.responseJSON});
                },
                complete: function() {
                    // Restaurar botão
                    btnReenviar.prop('disabled', false).html(textoBtnOriginal);
                }
            });
        }
    };

    // Inicializar quando o documento estiver pronto
    $(document).ready(function() {
        acaoReenvio.init();
    });

});
