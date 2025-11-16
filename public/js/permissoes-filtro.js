/**
 * Filtro de Permissões Baseado em Papéis
 *
 * Sistema centralizado para gerenciar visibilidade de elementos, botões e ações
 * conforme o papel (role) do usuário autenticado.
 *
 * Papéis disponíveis:
 * - admin: Administrador - acesso total
 * - consultor: Consultor - acesso limitado a suas próprias ordens
 * - financeiro: Financeiro - acesso apenas a ordens para faturamento
 */

const PermissoesFiltro = {

    /**
     * Definições de permissões por papel e recurso
     */
    permissoes: {
        admin: {
            ordem_servico: {
                view: true,
                create: true,
                edit: true,
                approve: true,
                contest: true,
                delete: true,
                view_values: true,
                manage_all: true
            },
            faturamento: {
                view: true,
                invoice: true,
                emit_rps: true,
                manage_all: true
            },
            relatorios: {
                view: true,
                export: true,
                manage_all: true
            }
        },
        consultor: {
            ordem_servico: {
                view: true,
                create: true,
                edit: true,        // Apenas suas próprias ordens
                approve: false,
                contest: false,
                delete: true,       // Apenas suas próprias ordens
                view_values: false, // Não vê valores
                manage_all: false
            },
            faturamento: {
                view: true,         // Apenas suas próprias ordens
                invoice: false,
                emit_rps: false,
                manage_all: false
            },
            relatorios: {
                view: false,
                export: false,
                manage_all: false
            }
        },
        financeiro: {
            ordem_servico: {
                view: false,
                create: false,
                edit: false,
                approve: false,
                contest: false,
                delete: false,
                view_values: true,
                manage_all: false
            },
            faturamento: {
                view: true,
                invoice: true,
                emit_rps: true,
                manage_all: false
            },
            relatorios: {
                view: false,
                export: false,
                manage_all: false
            }
        }
    },

    /**
     * Verifica se o usuário tem permissão para uma ação
     * @param {string} papel - Papel do usuário (admin, consultor, financeiro)
     * @param {string} recurso - Recurso/módulo (ordem_servico, faturamento, etc)
     * @param {string} acao - Ação a ser verificada (view, create, edit, etc)
     * @returns {boolean}
     */
    temPermissao: function(papel, recurso, acao) {
        if (!this.permissoes[papel]) {
            console.warn(`Papel '${papel}' não encontrado`);
            return false;
        }

        if (!this.permissoes[papel][recurso]) {
            console.warn(`Recurso '${recurso}' não encontrado para papel '${papel}'`);
            return false;
        }

        return this.permissoes[papel][recurso][acao] || false;
    },

    /**
     * Esconde elemento HTML se usuário não tem permissão
     * @param {string} papel - Papel do usuário
     * @param {string} selector - Seletor CSS
     * @param {string} recurso - Recurso
     * @param {string} acao - Ação
     */
    esconderSemPermissao: function(papel, selector, recurso, acao) {
        if (!this.temPermissao(papel, recurso, acao)) {
            $(selector).hide();
        }
    },

    /**
     * Desabilita elemento HTML se usuário não tem permissão
     * @param {string} papel - Papel do usuário
     * @param {string} selector - Seletor CSS
     * @param {string} recurso - Recurso
     * @param {string} acao - Ação
     */
    desabilitarSemPermissao: function(papel, selector, recurso, acao) {
        if (!this.temPermissao(papel, recurso, acao)) {
            $(selector).prop('disabled', true).addClass('disabled');
        }
    },

    /**
     * Mostra elemento HTML apenas se usuário tem permissão
     * @param {string} papel - Papel do usuário
     * @param {string} selector - Seletor CSS
     * @param {string} recurso - Recurso
     * @param {string} acao - Ação
     */
    mostrarComPermissao: function(papel, selector, recurso, acao) {
        if (this.temPermissao(papel, recurso, acao)) {
            $(selector).show();
        } else {
            $(selector).hide();
        }
    },

    /**
     * Retorna classe CSS condicional baseada em permissão
     * @param {string} papel - Papel do usuário
     * @param {string} recurso - Recurso
     * @param {string} acao - Ação
     * @returns {string} Classe CSS ou string vazia
     */
    classe: function(papel, recurso, acao) {
        return this.temPermissao(papel, recurso, acao) ? '' : 'd-none';
    },

    /**
     * Retorna visibilidade booleana para DataTable buttons
     * @param {string} papel - Papel do usuário
     * @param {string} recurso - Recurso
     * @param {string} acao - Ação
     * @returns {boolean}
     */
    visivel: function(papel, recurso, acao) {
        return this.temPermissao(papel, recurso, acao);
    },

    /**
     * Define configurações de permissão customizadas
     * @param {string} papel - Papel do usuário
     * @param {object} permissoes - Objeto com permissões a mesclar
     */
    definirPermissoes: function(papel, permissoes) {
        if (!this.permissoes[papel]) {
            this.permissoes[papel] = {};
        }
        $.extend(true, this.permissoes[papel], permissoes);
    },

    /**
     * Obtém todas as permissões de um papel
     * @param {string} papel - Papel do usuário
     * @returns {object}
     */
    obterPermissoes: function(papel) {
        return this.permissoes[papel] || {};
    }
};

// Alias para facilitar uso
const Perms = PermissoesFiltro;
