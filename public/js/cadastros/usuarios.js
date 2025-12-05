$(function () {

  // ===== CEP PESSOA JURÍDICA =====
  // Função para buscar endereço por CEP
  function buscarCEPPJ(cep) {
    const cepLimpo = cep.replace(/\D/g, '');

    if (cepLimpo.length !== 8) {
      Toast.fire({ icon: 'warning', title: 'CEP deve conter 8 dígitos' });
      return;
    }

    console.log('Enviando requisição para /buscar-cep com CEP:', cepLimpo);

    $.ajax({
      url: '/buscar-cep',
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: { cep: cepLimpo },
      success: function (resp) {
        console.log('Resposta da API CEP:', resp);
        if (resp.success) {
          // Preencher os campos com os dados retornados da API
          const cepFormatado = resp.data.cep || cepLimpo.replace(/^(\d{5})(\d{3})$/, '$1-$2');
          console.log('Preenchendo campos PJ:', {
            cep: cepFormatado,
            endereco: resp.data.endereco,
            estado: resp.data.estado,
            cidade: resp.data.cidade
          });

          $('#txtPJCEP').val(cepFormatado);
          $('#txtPJEndereco').val(resp.data.endereco || '');
          $('#txtPJEstado').val(resp.data.estado || '');

          // Importante: Usar setTimeout para garantir que o valor seja setado após o DOM atualizar
          setTimeout(() => {
            $('#txtPJCidade').val(resp.data.cidade || '');
            console.log('Cidade PJ preenchida:', resp.data.cidade);
          }, 50);

          Toast.fire({
            icon: 'success',
            title: 'CEP encontrado com sucesso!'
          });
        } else {
          console.error('Erro na resposta da API:', resp.message);
          Toast.fire({
            icon: 'error',
            title: resp.message || 'CEP não encontrado'
          });
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        const message = jqXHR.responseJSON?.message || 'Erro ao consultar CEP';
        console.error('Erro AJAX ao buscar CEP:', { status: jqXHR.status, message, errorThrown });
        Toast.fire({ icon: 'error', title: message });
      }
    });
  }

  // Event listener para blur no campo CEP PJ
  $(document).on('blur', '#txtPJCEP', function () {
    const cep = $(this).val();
    console.log('Blur no CEP PJ, valor:', cep);
    if (cep && cep.replace(/\D/g, '').length === 8) {
      console.log('Iniciando busca por CEP:', cep);
      buscarCEPPJ(cep);
    } else {
      console.log('CEP inválido ou vazio');
    }
  });

  const $tbl = $('#tblUsuarios');

  const tblUsuarios = $tbl.DataTable({
    ajax: {
      url: '/listar-usuarios',
      type: 'GET',
      dataSrc: 'data', // { data: [...] }
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      // Tratamento detalhado de erros
      error: function(xhr, status, error) {
        console.error('DataTables AJAX Error:', {
          status: xhr.status,
          statusText: xhr.statusText,
          responseText: xhr.responseText.substring(0, 200),
          error: error
        });

        let errorMsg = 'Erro ao carregar dados';

        if (xhr.status === 401) {
          errorMsg = 'Sessão expirada. Faça login novamente.';
          console.error('401 Unauthorized - Precisa fazer login novamente');
        } else if (xhr.status === 403) {
          errorMsg = 'Você não tem permissão para acessar este recurso';
        } else if (xhr.status === 404) {
          errorMsg = 'Rota não encontrada';
        } else if (xhr.status === 500) {
          errorMsg = 'Erro no servidor';
        }

        Toast.fire({
          icon: 'error',
          title: errorMsg
        });
      }
    },
    columns: [
      { title: 'Nome',      data: 'name',    defaultContent: '' },
      { title: 'Email',     data: 'email',   defaultContent: '' },
      { title: 'Celular',   data: 'celular', className: 'dt-center', orderable: false, width: '150px', defaultContent: '' },
      { title: 'CPF',       data: 'cgc',     className: 'dt-center', orderable: false, width: '150px', defaultContent: '' },
      {
        title: 'Ativo',
        data: 'ativo',
        className: 'dt-center',
        orderable: false,
        width: '80px',
        render: (_, __, row) => `
          <div class="d-flex align-items-center justify-content-center">
            <div class="form-check form-switch">
              <input type="checkbox" role="switch"
                     id="chkUsuarioAtivo${row.id}"
                     class="form-check-input toggle-usuario"
                     ${row.ativo ? 'checked' : ''}>
            </div>
          </div>`
      },
      {
        title: 'Ações',
        data: null,
        className: 'dt-center',
        orderable: false,
        width: '100px',
        render: (_, __, row) => `
          <div class="dropdown">
            <button class="btn btn-sm btn-primary border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item btn-visualizar" href="javascript:void(0);"><i class="bi bi-eye"></i> Visualizar</a></li>
              <li><a class="dropdown-item btn-editar" href="javascript:void(0);"><i class="bi bi-pencil"></i> Editar</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item btn-enviar-senha" href="javascript:void(0);"><i class="bi bi-envelope"></i> Enviar senha por e-mail</a></li>
            </ul>
          </div>`
      }
    ],
    dom: "<'row'<'col-sm-6'B><'col-sm-6 text-end'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [
      {
        text: '<i class="bi bi-plus-circle"></i> Adicionar',
        className: 'btn btn-primary dt-button-styled',
        action: function () {
          const $f = $('#formUsuario');
          if ($f.length) {
            // cria hidden id se não existir (para usar no update)
            if (!$f.find('input[name="id"]').length) {
              $f.append('<input type="hidden" name="id" id="usuario_id">');
            }
            $f[0].reset();
            $('#usuario_id').val('');
            $('#chkAtivo, [name="ativo"]').prop('checked', true);
            $('#modalUsuario').modal('show');
          }
        }
      },
      {
        extend: 'colvis',
        className: 'btn btn-primary dt-button-styled'
      },
      {
        extend: 'excel',
        className: 'btn btn-primary dt-button-styled'
      },
      {
        extend: 'print',
        className: 'btn btn-primary dt-button-styled'
      }
    ]
    // sem language.url (definido globalmente em app.js)
  });

  // Alternar "ativo" direto na grid
  $tbl.on('click', '.toggle-usuario', function () {
    const rowData = tblUsuarios.row($(this).closest('tr')).data();
    $.get(`/toggle-usuario/${rowData.id}`, () => tblUsuarios.ajax.reload(null, false));
  });

  // Clicar em "Visualizar" -> abre modal em modo somente leitura
  $tbl.on('click', '.btn-visualizar', function () {
    const r = tblUsuarios.row($(this).closest('tr')).data();
    const $f = $('#formUsuario');
    if ($f.length && r) {
      if (!$f.find('input[name="id"]').length) {
        $f.append('<input type="hidden" name="id" id="usuario_id">');
      }
      $('#usuario_id').val(r.id || '');

      // ===== ABA 1: DADOS PESSOAIS =====
      $('#txtUsuarioNome').val(r.name || '');

      // Carregar data de nascimento com tratamento especial
      if (r.data_nasc) {
        console.log('Visualizar - Data de nascimento do banco:', r.data_nasc);
        // Remover qualquer caractere inválido e garantir formato YYYY-MM-DD
        const dataParts = r.data_nasc.trim().substring(0, 10); // Pega só os 10 primeiros caracteres
        console.log('Visualizar - Data formatada para o campo:', dataParts);
        $('#txtUsuarioDataNasc').val(dataParts);
        // Forçar atualização visual do campo
        $('#txtUsuarioDataNasc')[0].dispatchEvent(new Event('input', { bubbles: true }));
      }
      $('#txtUsuarioEmail').val(r.email || '');
      $('#txtUsuarioCelular').val(r.celular || '').trigger('input');
      $('#slcUsuarioPapel').val(r.papel || '');
      $('#txtUsuarioCPF').val(r.cgc || '').trigger('input');

      // Formata valores monetários: 150.00 → R$ 150,00
      const formatMoneyValue = (value) => {
        if (!value) return '';
        const num = parseFloat(value);
        return !isNaN(num) ? num.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'}).replace('R$', '').trim() : '';
      };

      $('#txtUsuarioValorHora').val(formatMoneyValue(r.valor_hora)).trigger('input');
      $('#txtUsuarioValorDesloc').val(formatMoneyValue(r.valor_desloc)).trigger('input');
      $('#txtUsuarioValorKM').val(formatMoneyValue(r.valor_km)).trigger('input');
      $('#txtUsuarioSalarioBase').val(formatMoneyValue(r.salario_base)).trigger('input');

      // ===== ABA 2: PESSOA JURÍDICA =====
      $('#txtPJCNPJ').val(r.cnpj || '').trigger('input');
      $('#txtPJRazaoSocial').val(r.razao_social || '');
      $('#txtPJNomeFantasia').val(r.nome_fantasia || '');
      $('#txtPJInscricaoEstadual').val(r.inscricao_estadual || '');
      $('#txtPJInscricaoMunicipal').val(r.inscricao_municipal || '');
      $('#txtPJEndereco').val(r.endereco || '');
      $('#txtPJNumero').val(r.numero || '');
      $('#txtPJComplemento').val(r.complemento || '');
      $('#txtPJBairro').val(r.bairro || '');
      $('#txtPJCidade').val(r.cidade || '');
      $('#txtPJEstado').val(r.estado || '');
      $('#txtPJCEP').val(r.cep || '').trigger('input');
      $('#txtPJTelefone').val(r.telefone || '').trigger('input');
      $('#txtPJEmail').val(r.email_pj || '');
      $('#txtPJSite').val(r.site || '');
      $('#txtPJRamoAtividade').val(r.ramo_atividade || '');
      $('#txtPJDataConstituicao').val(r.data_constituicao || '');

      // ===== ABA 3: DADOS DE PAGAMENTO =====
      $('#txtPagTitularConta').val(r.titular_conta || '');
      $('#txtPagCpfCnpjTitular').val(r.cpf_cnpj_titular || '').trigger('input');
      $('#txtPagBanco').val(r.banco || '');
      $('#txtPagAgencia').val(r.agencia || '');
      $('#txtPagConta').val(r.conta || '');
      $('#slcPagTipoConta').val(r.tipo_conta || '');
      $('#txtPagPixKey').val(r.pix_key || '');

      // desabilita todos os campos e o botão salvar
      $('#formUsuario input, #formUsuario select').prop('disabled', true);
      $('.btn-salvar-usuario').hide();
      $('#modalUsuarioLabel').text('Visualizar Usuário');
      $('#modalUsuario').modal('show');
    }
  });

  // Clicar em "Editar" -> abre modal preenchido para edição
  $tbl.on('click', '.btn-editar', function () {
    const r = tblUsuarios.row($(this).closest('tr')).data();
    const $f = $('#formUsuario');
    if ($f.length && r) {
      if (!$f.find('input[name="id"]').length) {
        $f.append('<input type="hidden" name="id" id="usuario_id">');
      }
      $('#usuario_id').val(r.id || '');

      // ===== ABA 1: DADOS PESSOAIS =====
      $('#txtUsuarioNome').val(r.name || '');

      // Carregar data de nascimento com tratamento especial
      if (r.data_nasc) {
        console.log('Editar - Data de nascimento do banco:', r.data_nasc);
        // Remover qualquer caractere inválido e garantir formato YYYY-MM-DD
        const dataParts = r.data_nasc.trim().substring(0, 10); // Pega só os 10 primeiros caracteres
        console.log('Editar - Data formatada para o campo:', dataParts);
        $('#txtUsuarioDataNasc').val(dataParts);
        // Forçar atualização visual do campo
        $('#txtUsuarioDataNasc')[0].dispatchEvent(new Event('input', { bubbles: true }));
      }
      $('#txtUsuarioEmail').val(r.email || '');
      $('#txtUsuarioCelular').val(r.celular || '').trigger('input');
      $('#slcUsuarioPapel').val(r.papel || '');
      $('#txtUsuarioCPF').val(r.cgc || '').trigger('input');

      // Formata valores monetários: 150.00 → R$ 150,00
      const formatMoneyValue = (value) => {
        if (!value) return '';
        const num = parseFloat(value);
        return !isNaN(num) ? num.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'}).replace('R$', '').trim() : '';
      };

      $('#txtUsuarioValorHora').val(formatMoneyValue(r.valor_hora)).trigger('input');
      $('#txtUsuarioValorDesloc').val(formatMoneyValue(r.valor_desloc)).trigger('input');
      $('#txtUsuarioValorKM').val(formatMoneyValue(r.valor_km)).trigger('input');
      $('#txtUsuarioSalarioBase').val(formatMoneyValue(r.salario_base)).trigger('input');

      // ===== ABA 2: PESSOA JURÍDICA =====
      $('#txtPJCNPJ').val(r.cnpj || '').trigger('input');
      $('#txtPJRazaoSocial').val(r.razao_social || '');
      $('#txtPJNomeFantasia').val(r.nome_fantasia || '');
      $('#txtPJInscricaoEstadual').val(r.inscricao_estadual || '');
      $('#txtPJInscricaoMunicipal').val(r.inscricao_municipal || '');
      $('#txtPJEndereco').val(r.endereco || '');
      $('#txtPJNumero').val(r.numero || '');
      $('#txtPJComplemento').val(r.complemento || '');
      $('#txtPJBairro').val(r.bairro || '');
      $('#txtPJCidade').val(r.cidade || '');
      $('#txtPJEstado').val(r.estado || '');
      $('#txtPJCEP').val(r.cep || '').trigger('input');
      $('#txtPJTelefone').val(r.telefone || '').trigger('input');
      $('#txtPJEmail').val(r.email_pj || '');
      $('#txtPJSite').val(r.site || '');
      $('#txtPJRamoAtividade').val(r.ramo_atividade || '');
      $('#txtPJDataConstituicao').val(r.data_constituicao || '');

      // ===== ABA 3: DADOS DE PAGAMENTO =====
      $('#txtPagTitularConta').val(r.titular_conta || '');
      $('#txtPagCpfCnpjTitular').val(r.cpf_cnpj_titular || '').trigger('input');
      $('#txtPagBanco').val(r.banco || '');
      $('#txtPagAgencia').val(r.agencia || '');
      $('#txtPagConta').val(r.conta || '');
      $('#slcPagTipoConta').val(r.tipo_conta || '');
      $('#txtPagPixKey').val(r.pix_key || '');

      // habilita campos para edição
      $('#formUsuario input, #formUsuario select').prop('disabled', false);
      $('.btn-salvar-usuario').show();
      $('#modalUsuarioLabel').text('Editar Usuário');
      $('#modalUsuario').modal('show');
    }
  });

  // Clicar em "Enviar senha por e-mail"
  $tbl.on('click', '.btn-enviar-senha', function () {
    const r = tblUsuarios.row($(this).closest('tr')).data();
    if (r) {
      Swal.fire({
        title: 'Enviar senha por e-mail?',
        html: `Deseja gerar uma nova senha e enviar para <strong>${r.email}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, enviar',
        cancelButtonText: 'Cancelar',
        backdrop: false,
        customClass: {
          confirmButton: 'btn btn-primary',
          cancelButton: 'btn btn-secondary'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '/enviar-senha-usuario/' + r.id,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({}),
            success: function(response) {
              Toast.fire({
                icon: 'success',
                title: response.message || 'Senha enviada com sucesso!'
              });
            },
            error: function(jqXHR) {
              Toast.fire({
                icon: 'error',
                title: jqXHR.responseJSON?.message || 'Erro ao enviar senha.'
              });
            }
          });
        }
      });
    }
  });

  // Salvar do modal (criar/atualizar)
  $('.btn-salvar-usuario').on('click', function () {
    const $f = $('#formUsuario');

    // Validação básica: campos obrigatórios
    if (!validateFormRequired($f)) {
      return;
    }

    // Coletar dados do formulário
    const formData = new FormData($f[0]);
    const jsonData = {};

    formData.forEach((value, key) => {
      // ✅ SANITIZAR CPF: remover máscara (deixar só números)
      if (key === 'txtUsuarioCPF' && value) {
        jsonData[key] = value.replace(/\D/g, '');
      }
      // ✅ SANITIZAR CELULAR: remover máscara (deixar só números)
      else if (key === 'txtUsuarioCelular' && value) {
        jsonData[key] = value.replace(/\D/g, '');
      }
      // ✅ SANITIZAR CNPJ: remover máscara (deixar só números)
      else if (key === 'txtPJCNPJ' && value) {
        jsonData[key] = value.replace(/\D/g, '');
      }
      // ✅ SANITIZAR CEP: remover máscara
      else if (key === 'txtPJCEP' && value) {
        jsonData[key] = value.replace(/\D/g, '');
      }
      // ✅ SANITIZAR TELEFONE PJ: remover máscara (deixar só números)
      else if (key === 'txtPJTelefone' && value) {
        jsonData[key] = value.replace(/\D/g, '');
      }
      // ✅ SANITIZAR VALORES MONETÁRIOS: remover máscara e converter para número válido
      else if ((key === 'txtUsuarioValorHora' || key === 'txtUsuarioValorDesloc' ||
                key === 'txtUsuarioValorKM' || key === 'txtUsuarioSalarioBase') && value) {
        // Remove máscara de moeda: "R$ 1.250,56" → "1250.56"
        // E converte para número decimal válido
        const cleanValue = value.replace(/[^\d,]/g, '').replace(',', '.');
        const numericValue = parseFloat(cleanValue);
        // Se for um número válido, formata com 2 casas decimais, senão deixa vazio
        jsonData[key] = !isNaN(numericValue) && cleanValue ? numericValue.toFixed(2) : '';
      }
      // ✅ SANITIZAR CPF/CNPJ DO TITULAR: remover máscara
      else if (key === 'txtPagCpfCnpjTitular' && value) {
        jsonData[key] = value.replace(/\D/g, '');
      }
      // ✅ VALIDAR user_id: converter para inteiro ou null
      else if (key === 'id') {
        const id = parseInt(value);
        jsonData[key] = !isNaN(id) && id > 0 ? id : null;
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
      contentType: 'application/json',  // ← IMPORTANTE: indicar que é JSON
      data: JSON.stringify(jsonData),   // ← Enviar como JSON string
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // ← Token CSRF
      },
      dataType: 'json',  // ← Esperar JSON na resposta
      timeout: 30000,    // ← Timeout de 30 segundos

      success: function (response) {
        console.log('Sucesso:', response);
        Toast.fire({
          icon: 'success',
          title: response.message || 'Usuário salvo com sucesso!'
        });
        $('#modalUsuario').modal('hide');
        tblUsuarios.ajax.reload(null, false);
      },

      error: function (jqXHR, textStatus, errorThrown) {
        console.error('Erro completo:', {
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
          // Erro de conexão
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
          // Não autenticado
          errorMsg = 'Sessão expirada';
          errorDetails = 'Faça login novamente';
        } else if (jqXHR.status === 403) {
          // Não autorizado
          errorMsg = 'Acesso negado';
          errorDetails = 'Você não tem permissão para esta ação';
        } else if (jqXHR.status === 500) {
          // Erro do servidor
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

  // Ao fechar o modal, restaurar estado normal
  $('#modalUsuario').on('hidden.bs.modal', function () {
    $('#formUsuario input, #formUsuario select').prop('disabled', false);
    $('.btn-salvar-usuario').show();
    $('#modalUsuarioLabel').text('Adicionar Usuário');
    $('#formUsuario')[0].reset();
  });

  // Copiar com seleção de colunas
  $('.btn-copiar-selecionado').on('click', function () {
    const selectedCols = [];
    $('input[name="chkColunaCopiar"]:checked').each(function () {
      selectedCols.push($(this).val());
    });

    if (selectedCols.length === 0) {
      Toast.fire({
        icon: 'warning',
        title: 'Selecione pelo menos uma coluna'
      });
      return;
    }

    // Mapear nomes das colunas para índices
    const colMap = {
      'nome': 0,
      'email': 1,
      'celular': 2,
      'cpf_cnpj': 3,
      'ativo': 4
    };

    // Obter dados da tabela
    const data = tblUsuarios.rows({ search: 'applied' }).data();
    let textToCopy = '';

    // Adicionar cabeçalho
    const headers = selectedCols.map(col => {
      if (col === 'nome') return 'Nome';
      if (col === 'email') return 'Email';
      if (col === 'celular') return 'Celular';
      if (col === 'cpf_cnpj') return 'CPF/CNPJ';
      if (col === 'ativo') return 'Ativo';
      return col;
    });
    textToCopy = headers.join('\t') + '\n';

    // Adicionar dados
    data.each(function (row) {
      const rowData = selectedCols.map(col => {
        if (col === 'ativo') return row.ativo ? 'Sim' : 'Não';
        return row[col] || '';
      });
      textToCopy += rowData.join('\t') + '\n';
    });

    // Copiar para clipboard
    navigator.clipboard.writeText(textToCopy).then(() => {
      Toast.fire({
        icon: 'success',
        title: 'Dados copiados para a área de transferência!'
      });
      $('#modalSelecionarColunas').modal('hide');
    }).catch(() => {
      Toast.fire({
        icon: 'error',
        title: 'Erro ao copiar dados'
      });
    });
  });

});
