// ADICIONAR ESTE CÓDIGO NO ARQUIVO public/js/ordem-servico.js
// APÓS A INICIALIZAÇÃO DO DATATABLE

// ========================================
// FILTROS DE ORDEM DE SERVIÇO
// ========================================

$(document).ready(function() {
    // Carregar consultores no filtro (apenas para admin e financeiro)
    if (papel === 'admin' || papel === 'financeiro') {
        $.ajax({
            url: '/listar-consultores-filtro',
            method: 'GET',
            success: function(consultores) {
                consultores.forEach(function(consultor) {
                    $('#filtroConsultor').append(
                        $('<option>').val(consultor.id).text(consultor.name)
                    );
                });
            }
        });
    }

    // Carregar clientes no filtro
    $.ajax({
        url: '/listar-clientes-filtro',
        method: 'GET',
        success: function(clientes) {
            clientes.forEach(function(cliente) {
                const texto = `${cliente.codigo}-${cliente.loja} - ${cliente.nome}`;
                $('#filtroCliente').append(
                    $('<option>').val(cliente.id).text(texto)
                );
            });
        }
    });

    // Aplicar filtros
    $('#btnAplicarFiltros').on('click', function() {
        const status = $('#filtroStatus').val();
        const consultor = $('#filtroConsultor').val();
        const cliente = $('#filtroCliente').val();
        const mes = $('#filtroMes').val();
        const ano = $('#filtroAno').val();

        // Construir query string
        let params = [];
        if (status) params.push('status=' + status);
        if (consultor) params.push('consultor_id=' + consultor);
        if (cliente) params.push('cliente_id=' + cliente);
        if (mes) params.push('mes=' + mes);
        if (ano) params.push('ano=' + ano);

        const queryString = params.length > 0 ? '?' + params.join('&') : '';

        // Recarregar DataTable com filtros
        const table = $('#tblOrdensServico').DataTable();
        table.ajax.url('/listar-ordens-servico' + queryString).load();
    });

    // Limpar filtros
    $('#btnLimparFiltros').on('click', function() {
        $('#filtroStatus').val('');
        $('#filtroConsultor').val('');
        $('#filtroCliente').val('');
        $('#filtroMes').val('');
        $('#filtroAno').val(new Date().getFullYear());

        // Recarregar DataTable sem filtros
        const table = $('#tblOrdensServico').DataTable();
        table.ajax.url('/listar-ordens-servico').load();
    });
});
