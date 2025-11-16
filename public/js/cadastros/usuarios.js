$(function () {

  const $tbl = $('#tblUsuarios');

  const tblUsuarios = $tbl.DataTable({
    ajax: {
      url: '/listar-usuarios',
      type: 'GET',
      dataSrc: 'data' // { data: [...] }
    },
    columns: [
      { title: 'Nome',      data: 'name',    defaultContent: '' },
      { title: 'Email',     data: 'email',   defaultContent: '' },
      { title: 'Celular',   data: 'celular', className: 'dt-center', orderable: false, width: '150px', defaultContent: '' },
      { title: 'CPF/CNPJ',  data: 'cgc',     className: 'dt-center', orderable: false, width: '150px', defaultContent: '' },
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

      // preenche campos
      $('#txtUsuarioNome').val(r.name || '');
      $('#txtUsuarioDataNasc').val(r.data_nasc || '');
      $('#txtUsuarioEmail').val(r.email || '');
      $('#txtUsuarioCelular').val(r.celular || '');
      $('#slcUsuarioPapel').val(r.papel || '');
      $('#txtUsuarioCGC').val(r.cgc || '');
      $('#txtUsuarioValorHora').val(r.valor_hora || '');
      $('#txtUsuarioValorDesloc').val(r.valor_desloc || '');
      $('#txtUsuarioValorKM').val(r.valor_km || '');
      $('#txtUsuarioSalarioBase').val(r.salario_base || '');

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

      // preenche campos
      $('#txtUsuarioNome').val(r.name || '');
      $('#txtUsuarioDataNasc').val(r.data_nasc || '');
      $('#txtUsuarioEmail').val(r.email || '');
      $('#txtUsuarioCelular').val(r.celular || '');
      $('#slcUsuarioPapel').val(r.papel || '');
      $('#txtUsuarioCGC').val(r.cgc || '');
      $('#txtUsuarioValorHora').val(r.valor_hora || '');
      $('#txtUsuarioValorDesloc').val(r.valor_desloc || '');
      $('#txtUsuarioValorKM').val(r.valor_km || '');
      $('#txtUsuarioSalarioBase').val(r.salario_base || '');

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
    $.ajax({
      url: '/salvar-usuario',
      type: 'POST',
      data: $f.serialize(),
      success: function (response) {
        Toast.fire({
          icon: 'success',
          title: response.message || 'Usuário salvo com sucesso!'
        });
        $('#modalUsuario').modal('hide');
        tblUsuarios.ajax.reload(null, false);
      },
      error: function (jqXHR) {
        let errorMsg = 'Erro ao salvar usuário';
        if (jqXHR.status === 422) {
          const errors = jqXHR.responseJSON.errors || {};
          let errorText = '';
          for (const field in errors) {
            errorText += errors[field].join('<br>') + '<br>';
          }
          errorMsg = errorText || 'Dados inválidos';
        } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
          errorMsg = jqXHR.responseJSON.message;
        }
        Toast.fire({
          icon: 'error',
          title: errorMsg
        });
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
