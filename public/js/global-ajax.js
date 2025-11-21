/**
 * Global AJAX Configuration
 * Configuração centralizada de CSRF, Accept headers, timeout e error handling
 *
 * Deve ser carregado PRIMEIRO, antes de outros scripts
 *
 * Uso:
 *   <script src="/js/global-ajax.js"></script>
 *   <script src="/js/app.js"></script>
 *   <script src="/js/pages/usuarios.js"></script>
 */

$(document).ready(function() {

    // ========== CONFIGURAÇÃO GLOBAL DE AJAX ==========
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'  // Crucial: avisa que esperamos JSON
        },
        xhrFields: {
            withCredentials: true  // Envia cookies com requests (Sanctum/Session)
        },
        timeout: 30000,  // 30 segundos

        // Error handler global para TODAS as requisições AJAX
        error: function(xhr, status, error) {
            console.error('🔴 AJAX Error Global:', {
                status: xhr.status,
                statusText: xhr.statusText,
                statusError: error,
                responsePreview: xhr.responseText.substring(0, 150)
            });

            // Tratamento específico por status
            if (xhr.status === 401) {
                console.error('❌ 401: Não autenticado - considere redirecionar para login');
            } else if (xhr.status === 419) {
                console.error('❌ 419: Sessão expirada ou CSRF token inválido');
            } else if (xhr.status === 422) {
                console.log('⚠️ 422: Validação falhou - handler específico deve tratar');
            } else if (xhr.status === 500) {
                console.error('❌ 500: Erro no servidor - verificar logs');
            } else if (status === 'timeout') {
                console.error('❌ Timeout: Requisição levou mais de 30s');
            }
        }
    });

    console.log('✅ Global AJAX Setup carregado');
    console.log('📝 CSRF Token:', $('meta[name="csrf-token"]').attr('content')?.substring(0, 20) + '...');

});

// ========== FUNÇÕES UTILITÁRIAS GLOBAIS ==========

/**
 * Parse seguro de JSON
 * Se resposta não for JSON válida, retorna objeto com _raw
 *
 * Uso:
 *   var data = safeParseJson(xhr.responseText);
 *   if (data._error) console.log('HTML recebido:', data._raw);
 */
window.safeParseJson = function(responseText) {
    try {
        return JSON.parse(responseText);
    } catch (e) {
        console.error('⚠️ Resposta não-JSON recebida. Primeiros 200 chars:', responseText.substring(0, 200));
        return {
            _error: true,
            _raw: responseText,
            message: 'Resposta inválida do servidor (HTML ao invés de JSON?)'
        };
    }
};

/**
 * Fazer requisição AJAX com JSON estruturado
 * Automaticamente adiciona headers necessários e serializa JSON
 *
 * Uso:
 *   ajaxJson({
 *       url: '/api/usuarios',
 *       type: 'POST',
 *       data: { name: 'João', email: 'joao@test.com' },
 *       success: function(response) { ... },
 *       error: function(xhr) { ... }
 *   });
 */
window.ajaxJson = function(options) {
    const defaults = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        dataType: 'json',
        contentType: 'application/json',
        processData: false,  // Importante: não processar FormData
        xhrFields: {
            withCredentials: true
        }
    };

    // Se data é objeto, converter para JSON string
    if (options.data && typeof options.data === 'object') {
        options.data = JSON.stringify(options.data);
    }

    const config = $.extend({}, defaults, options);

    return $.ajax(config);
};

/**
 * Fazer requisição AJAX com form-encoded (fallback para legacy)
 * Para formulários simples
 *
 * Uso:
 *   ajaxForm({
 *       url: '/api/usuarios',
 *       type: 'POST',
 *       data: $('#form-usuario').serialize(),
 *       success: function(response) { ... }
 *   });
 */
window.ajaxForm = function(options) {
    const defaults = {
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        dataType: 'json',
        processData: true,
        xhrFields: {
            withCredentials: true
        }
    };

    return $.ajax($.extend({}, defaults, options));
};

/**
 * Fazer requisição com tratamento automático de erros comuns
 * Ideal para operações repetitivas
 *
 * Uso:
 *   ajaxWithErrorHandling({
 *       url: '/api/usuarios',
 *       type: 'POST',
 *       data: { ... },
 *       onSuccess: function(data) { console.log('Sucesso:', data); },
 *       onValidationError: function(errors) { showErrors(errors); },
 *       onUnauthorized: function() { redirectToLogin(); },
 *       onServerError: function(msg) { showErrorToast(msg); }
 *   });
 */
window.ajaxWithErrorHandling = function(options) {
    const defaults = {
        onSuccess: function() {},
        onValidationError: function() {},
        onUnauthorized: function() {},
        onSessionExpired: function() {},
        onServerError: function() {},
        onError: function() {}
    };

    const config = $.extend({}, defaults, options);

    // Construir callbacks
    const successHandler = config.success || function(response) {
        config.onSuccess(response);
    };

    const errorHandler = config.error || function(xhr) {
        const response = safeParseJson(xhr.responseText);

        switch (xhr.status) {
            case 422:  // Validação falhou
                config.onValidationError(response.errors || {});
                break;
            case 419:  // Token expirado
                config.onSessionExpired();
                break;
            case 401:  // Não autenticado
                config.onUnauthorized();
                break;
            case 500:  // Erro no servidor
            default:
                config.onServerError(response.message || 'Erro desconhecido');
                config.onError(xhr, response);
        }
    };

    // Remover callbacks da config para não conflitar com $.ajax
    delete config.onSuccess;
    delete config.onValidationError;
    delete config.onUnauthorized;
    delete config.onSessionExpired;
    delete config.onServerError;
    delete config.onError;

    config.success = successHandler;
    config.error = errorHandler;

    return ajaxJson(config);
};

/**
 * Função auxiliar para FormData
 * Envia arquivo + dados em multipart/form-data
 *
 * Uso:
 *   ajaxFormData({
 *       url: '/api/upload',
 *       type: 'POST',
 *       data: formData,  // FormData object
 *       success: function(response) { ... }
 *   });
 */
window.ajaxFormData = function(options) {
    const defaults = {
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        dataType: 'json',
        processData: false,      // Não processar (deixar FormData)
        contentType: false,      // Deixar browser definir content-type
        xhrFields: {
            withCredentials: true
        }
    };

    return $.ajax($.extend({}, defaults, options));
};

console.log('✅ Funções auxiliares carregadas: ajaxJson, ajaxForm, ajaxWithErrorHandling, ajaxFormData, safeParseJson');
