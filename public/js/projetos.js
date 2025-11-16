/**
 * Projetos - JavaScript para funcionalidades de projeto
 */

$(document).ready(function() {
    // Carregar projetos quando cliente é selecionado no formulário de Ordem de Serviço
    $(document).on('change', '#slcOrdemClienteId', function() {
        const clienteId = $(this).val();
        const selectProjeto = $('#slcOrdemProjetoId');

        // Limpar select de projetos
        selectProjeto.html('<option value="">Selecione um projeto</option>');

        if (clienteId) {
            $.ajax({
                url: '/cliente/' + clienteId + '/projetos',
                method: 'GET',
                dataType: 'json',
                success: function(projetos) {
                    if (projetos.length > 0) {
                        projetos.forEach(function(projeto) {
                            selectProjeto.append(
                                '<option value="' + projeto.id + '">' +
                                projeto.codigo + ' - ' + projeto.nome +
                                '</option>'
                            );
                        });
                    } else {
                        selectProjeto.html('<option value="">Nenhum projeto ativo para este cliente</option>');
                    }
                },
                error: function(error) {
                    console.error('Erro ao carregar projetos:', error);
                    selectProjeto.html('<option value="">Erro ao carregar projetos</option>');
                }
            });
        }
    });
});
