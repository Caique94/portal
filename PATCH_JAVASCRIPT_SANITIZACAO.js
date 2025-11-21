/**
 * PATCH: Sanitização de Dados no Formulário de Usuários
 *
 * Arquivo: public/js/cadastros/usuarios.js
 * Linha: ~225 (seção "Salvar do modal")
 *
 * O que faz:
 * 1. Remove máscara do CNPJ (65.465.465/4564 → 654654654564)
 * 2. Valida e converte user_id para inteiro ou null
 * 3. Limpa CPF/CNPJ do titular de pagamento
 *
 * ANTES:
 */

// ❌ CÓDIGO ANTIGO (COM ERRO):
$('.btn-salvar-usuario').on('click', function () {
  const $f = $('#formUsuario');

  if (!validateFormRequired($f)) {
    return;
  }

  const formData = new FormData($f[0]);
  const jsonData = {};
  formData.forEach((value, key) => {
    jsonData[key] = value;  // ❌ Não sanitiza CNPJ com máscara
  });

  console.log('Enviando dados:', jsonData);

  $.ajax({
    url: '/salvar-usuario',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(jsonData),  // ❌ Envia CNPJ mascarado: "65.465.465/4564"
    // ... resto do código
  });
});

/**
 * ═════════════════════════════════════════════════════════════════
 * DEPOIS:
 */

// ✅ CÓDIGO NOVO (SEM ERRO):
$('.btn-salvar-usuario').on('click', function () {
  const $f = $('#formUsuario');

  if (!validateFormRequired($f)) {
    return;
  }

  // Coletar dados do formulário
  const formData = new FormData($f[0]);
  const jsonData = {};

  formData.forEach((value, key) => {
    // ✅ SANITIZAR CNPJ: remover máscara (deixar só números)
    if (key === 'txtPJCNPJ' && value) {
      jsonData[key] = value.replace(/\D/g, '');
      console.log(`CNPJ sanitizado: ${value} → ${jsonData[key]}`);
    }
    // ✅ VALIDAR CPF/CNPJ DO TITULAR: remover máscara
    else if (key === 'txtPagCpfCnpjTitular' && value) {
      jsonData[key] = value.replace(/\D/g, '');
      console.log(`CPF/CNPJ Titular sanitizado: ${value} → ${jsonData[key]}`);
    }
    // ✅ VALIDAR user_id: converter para inteiro ou null
    else if (key === 'id') {
      const id = parseInt(value);
      jsonData[key] = !isNaN(id) && id > 0 ? id : null;
      console.log(`user_id convertido: ${value} → ${jsonData[key]}`);
    }
    // ✅ VALIDAR CEP: remover máscara
    else if (key === 'txtPJCEP' && value) {
      jsonData[key] = value.replace(/\D/g, '');
    }
    // Resto dos campos - deixar como estão
    else {
      jsonData[key] = value;
    }
  });

  console.log('Dados sanitizados prontos para envio:', jsonData);

  $.ajax({
    url: '/salvar-usuario',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(jsonData),  // ✅ Agora envia CNPJ limpo: "654654654564"
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    dataType: 'json',
    timeout: 30000,

    success: function (response) {
      console.log('✅ Sucesso:', response);
      Toast.fire({
        icon: 'success',
        title: response.message || 'Usuário salvo com sucesso!'
      });
      $('#modalUsuario').modal('hide');
      tblUsuarios.ajax.reload(null, false);
    },

    error: function (jqXHR, textStatus, errorThrown) {
      console.error('❌ Erro completo:', {
        status: jqXHR.status,
        statusText: jqXHR.statusText,
        textStatus: textStatus,
        errorThrown: errorThrown,
        responseText: jqXHR.responseText.substring(0, 500),
        responseJSON: jqXHR.responseJSON
      });

      let errorMsg = 'Erro ao salvar usuário';
      let errorDetails = '';

      // Tratamento detalhado por status HTTP
      if (jqXHR.status === 0) {
        errorMsg = 'Erro de conexão com o servidor';
        errorDetails = 'Verifique se o servidor está rodando';
      } else if (jqXHR.status === 422) {
        // Erro de validação
        errorMsg = 'Erro de validação dos dados';
        const errors = jqXHR.responseJSON?.errors || {};
        let errorText = '';
        for (const field in errors) {
          const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
          errorText += messages.join(', ') + ' • ';
        }
        if (errorText) {
          errorDetails = errorText.slice(0, -2);
        }
      } else if (jqXHR.status === 401) {
        errorMsg = 'Sessão expirada';
        errorDetails = 'Faça login novamente';
      } else if (jqXHR.status === 403) {
        errorMsg = 'Acesso negado';
        errorDetails = 'Você não tem permissão para esta ação';
      } else if (jqXHR.status === 500) {
        errorMsg = 'Erro no servidor';
        errorDetails = 'Verifique os logs em storage/logs/laravel.log';
      } else if (textStatus === 'timeout') {
        errorMsg = 'Requisição expirou';
        errorDetails = 'Tente novamente em alguns segundos';
      } else if (textStatus === 'parsererror') {
        errorMsg = 'Erro ao processar resposta';
        errorDetails = 'A resposta do servidor não é JSON válido';
      } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
        errorMsg = jqXHR.responseJSON.message;
      }

      // Mostrar erro
      if (errorDetails) {
        Toast.fire({
          icon: 'error',
          title: errorMsg,
          text: errorDetails
        });
      } else {
        Toast.fire({
          icon: 'error',
          title: errorMsg
        });
      }
    }
  });
});

/**
 * ═════════════════════════════════════════════════════════════════
 *
 * EXEMPLOS DE SANITIZAÇÃO:
 *
 * Input                              | Output
 * ──────────────────────────────────────────────────────────────
 * txtPJCNPJ:      "65.465.465/4564" → "654654654564"
 * txtPJCNPJ:      "654654654564"    → "654654654564"
 * txtPJCEP:       "12345-678"       → "12345678"
 * txtPJCEP:       "12345678"        → "12345678"
 * id:             "5"               → 5
 * id:             ""                → null
 * id:             "abc"             → null
 * txtPagCpfCnpjTitular: "123.456.789-09" → "12345678909"
 *
 * ═════════════════════════════════════════════════════════════════
 */
