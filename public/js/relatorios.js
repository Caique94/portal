$(document).ready(function() {
    let tblClientes, tblConsultores, tblStatus;

    // Inicializar DataTables
    function initTables() {
        // Tabela de Clientes
        tblClientes = $('#tblRelatorioClientes').DataTable({
            data: [],
            columns: [
                { data: 'codigo', className: 'dt-center', width: '100px' },
                { data: 'cliente_nome' },
                { data: 'total_ordens', className: 'dt-center', width: '120px' },
                {
                    data: 'valor_total',
                    className: 'dt-body-right',
                    width: '150px',
                    render: (data) => formatarMoeda(data)
                },
                {
                    data: 'valor_faturado',
                    className: 'dt-body-right',
                    width: '150px',
                    render: (data) => formatarMoeda(data)
                },
                {
                    data: 'valor_pendente',
                    className: 'dt-body-right',
                    width: '150px',
                    render: (data) => formatarMoeda(data)
                }
            ],
            order: [[3, 'desc']], // ordenar por valor total
            language: { url: '/plugins/datatables/i18n/pt-BR.json' }
        });

        // Tabela de Consultores
        tblConsultores = $('#tblRelatorioConsultores').DataTable({
            data: [],
            columns: [
                { data: 'consultor_nome' },
                { data: 'total_ordens', className: 'dt-center', width: '120px' },
                {
                    data: 'valor_total',
                    className: 'dt-body-right',
                    width: '150px',
                    render: (data) => formatarMoeda(data)
                },
                {
                    data: 'valor_faturado',
                    className: 'dt-body-right',
                    width: '150px',
                    render: (data) => formatarMoeda(data)
                },
                {
                    data: 'valor_pendente',
                    className: 'dt-body-right',
                    width: '150px',
                    render: (data) => formatarMoeda(data)
                },
                {
                    data: 'ticket_medio',
                    className: 'dt-body-right',
                    width: '150px',
                    render: (data) => formatarMoeda(data)
                }
            ],
            order: [[2, 'desc']], // ordenar por valor total
            language: { url: '/plugins/datatables/i18n/pt-BR.json' }
        });

        // Tabela de Status
        tblStatus = $('#tblRelatorioStatus').DataTable({
            data: [],
            columns: [
                {
                    data: 'status',
                    render: (data) => {
                        const statusMap = {
                            1: 'Aberta',
                            2: 'Aguardando Aprovação',
                            3: 'Contestada',
                            4: 'Aguardando Faturamento',
                            5: 'Faturada',
                            6: 'Aguardando RPS'
                        };
                        return statusMap[data] || 'Desconhecido';
                    }
                },
                { data: 'total', className: 'dt-center', width: '150px' },
                {
                    data: 'valor_total',
                    className: 'dt-body-right',
                    width: '200px',
                    render: (data) => formatarMoeda(data)
                }
            ],
            order: [[1, 'desc']], // ordenar por quantidade
            language: { url: '/plugins/datatables/i18n/pt-BR.json' }
        });
    }

    // Formatar valor em moeda
    function formatarMoeda(valor) {
        if (!valor) return 'R$ 0,00';
        return parseFloat(valor).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    // Carregar lista de clientes para o filtro
    function carregarClientes() {
        $.get('/listar-clientes', function(data) {
            const $select = $('#filtroCliente');
            $select.empty().append('<option value="">Todos os clientes</option>');
            data.forEach(cliente => {
                $select.append(`<option value="${cliente.id}">${cliente.codigo} - ${cliente.nome}</option>`);
            });
        });
    }

    // Aplicar filtros e carregar relatórios
    function aplicarFiltros() {
        const dataInicio = $('#filtroDataInicio').val();
        const dataFim = $('#filtroDataFim').val();
        const clienteId = $('#filtroCliente').val();

        const params = {
            data_inicio: dataInicio,
            data_fim: dataFim,
            cliente_id: clienteId
        };

        // Carregar resumo geral
        $.get('/relatorio-fechamento-geral', params, function(data) {
            $('#totalOrdens').text(data.total_ordens || 0);
            $('#valorTotal').text(formatarMoeda(data.valor_total));
            $('#valorFaturado').text(formatarMoeda(data.valor_faturado));
            $('#ticketMedio').text(formatarMoeda(data.ticket_medio));
        }).fail(function() {
            Toast.fire({ icon: 'error', title: 'Erro ao carregar resumo geral' });
        });

        // Carregar relatório por cliente
        $.get('/relatorio-fechamento-cliente', params, function(data) {
            tblClientes.clear();
            tblClientes.rows.add(data);
            tblClientes.draw();
        }).fail(function() {
            Toast.fire({ icon: 'error', title: 'Erro ao carregar relatório por cliente' });
        });

        // Carregar relatório por consultor
        $.get('/relatorio-fechamento-consultor', params, function(data) {
            tblConsultores.clear();
            tblConsultores.rows.add(data);
            tblConsultores.draw();
        }).fail(function() {
            Toast.fire({ icon: 'error', title: 'Erro ao carregar relatório por consultor' });
        });

        // Carregar relatório por status
        $.get('/relatorio-ordem-por-status', params, function(data) {
            tblStatus.clear();
            tblStatus.rows.add(data);
            tblStatus.draw();
        }).fail(function() {
            Toast.fire({ icon: 'error', title: 'Erro ao carregar relatório por status' });
        });
    }

    // Definir datas padrão (mês atual)
    function setDatasPadrao() {
        const hoje = new Date();
        const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
        const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);

        $('#filtroDataInicio').val(primeiroDia.toISOString().split('T')[0]);
        $('#filtroDataFim').val(ultimoDia.toISOString().split('T')[0]);
    }

    // Event listeners
    $('#btnAplicarFiltros').on('click', aplicarFiltros);

    // Aplicar filtros quando mudar de aba
    $('#relatorioTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
        aplicarFiltros();
    });

    // Inicialização
    initTables();
    carregarClientes();
    setDatasPadrao();
    aplicarFiltros();
});
