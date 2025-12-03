// clientes.js (final)

$(function () {
  const $form = $('#formCliente');

  const tblClientes = $('#tblClientes').DataTable({
    ajax: { url: '/listar-clientes', dataSrc: json => json },
    order: [[1, 'asc']],
    responsive: true,
    lengthChange: false,
    pageLength: 10,
    columnDefs: [
      { targets: [3,4,5,6,7,8,9,11,12], visible: false } // mantém as colunas extras ocultas
    ],
    columns: [
      { title:'C&oacute;digo', data:'codigo', className:'dt-center', width:'120px' },
      { title:'Loja',         data:'loja',   className:'dt-center', width:'120px' },
      { title:'Nome',         data:'nome' },
      { title:'Nome Fantasia',data:'nome_fantasia' },
      { title:'Tipo',         data:'tipo' },
      { title:'CNPJ / CPF',   data:'cgc',   className:'dt-center', width:'150px' },
      { title:'Contato Principal', data:'contato' },
      { title:'Endere&ccedil;o', data:'endereco' },
      { title:'Municipio',    data:'municipio' },
      { title:'Estado',       data:'estado' },
      { title:'Tabela Pre&ccedil;os', data:'tabela_preco' },
      { title:'KM',           data:'km' },
      { title:'Desl.',        data:'deslocamento' },
      {
        title:'A&ccedil;&otilde;es', data:null, orderable:false, className:'dt-center', width:'80px',
        render: (data, type, row) => `
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-list"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item exibir-modal-visualizacao" href="javascript:void(0)"><i class="bi bi-eye"></i> Visualizar</a></li>
              <li><a class="dropdown-item exibir-modal-edicao" href="javascript:void(0)"><i class="bi bi-pencil"></i> Editar</a></li>
              <li><a class="dropdown-item ver-historico" href="/cliente/${row.id}/historico"><i class="bi bi-clock-history"></i> Histórico</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item adicionar-contato" href="javascript:void(0)"><i class="bi bi-person-plus"></i> Adicionar Contato</a></li>
              <li><a class="dropdown-item exibir-contatos" href="javascript:void(0)"><i class="bi bi-people"></i> Exibir Contatos</a></li>
            </ul>
          </div>`
      }
    ],
    buttons: {
      name: 'primary',
      buttons: [
        {
          text: 'Adicionar',
          className: 'btn-primary',
          action: () => {
            if (!$form.find('input[name="id"]').length) {
              $form.append('<input type="hidden" name="id" id="cliente_id">');
            }
            $form[0].reset();
            $('#cliente_id').val('');
            $('#slcClienteTabelaPrecos').val(null).trigger('change');
            $('#txtClienteContato').empty().append(new Option('', '', true, true)).trigger('change');
            $('#modalClienteLabel').text('Adicionar Cliente');
            $('#modalCliente').modal('show');
          }
        },
        { extend: 'colvis', text: 'Colunas', columns: ':not(.noVis)' }
      ]
    },
    initComplete: () => {
      initializeTooltips();
      $('.dataTables_wrapper .dt-buttons').addClass('mb-2'); // espaçamento
    }
  });

  // Função para carregar contatos de um cliente
  function carregarContatosCliente(clienteId, contatoAtual) {
    if (!clienteId) {
      $('#txtClienteContato').empty().append(new Option('', '', true, true)).trigger('change');
      return;
    }

    $.get('/listar-contatos?id=' + clienteId).then(function (data) {
      const $sel = $('#txtClienteContato');
      $sel.empty().append(new Option('', '', false, false));

      (data || []).forEach(contato => {
        const isSelected = contatoAtual && contatoAtual === contato.nome;
        $sel.append(new Option(contato.nome, contato.nome, false, isSelected));
      });

      $sel.trigger('change');
    }).fail(function() {
      $('#txtClienteContato').empty().append(new Option('', '', true, true)).trigger('change');
    });
  }

  // ==== EDITAR ====
  $('#tblClientes').on('click', '.exibir-modal-edicao', function () {
    const r = tblClientes.row($(this).closest('tr')).data();
    if (!r) return;

    if (!$form.find('input[name="id"]').length) {
      $form.append('<input type="hidden" name="id" id="cliente_id">');
    }

    $('#cliente_id').val(r.id || '');
    $('#txtClienteCodigo').val(r.codigo || '');
    $('#txtClienteLoja').val(r.loja || '');
    $('#txtClienteNome').val(r.nome || '');
    $('#txtClienteNomeFantasia').val(r.nome_fantasia || '');
    $('#slcClienteTipo').val(r.tipo || '');
    $('#txtClienteCGC').val(r.cgc || '');
    $('#txtClienteEndereco').val(r.endereco || '');
    $('#txtClienteMunicipio').val(r.municipio || '');
    $('#txtClienteEstado').val(r.estado || '');
    $('#txtClienteKm').val(r.km || '');
    $('#txtClienteDeslocamento').val(r.deslocamento || '');
    $('#txtClienteValorHora').val(r.valor_hora || '');
    if (r.tabela_preco_id) {
      $('#slcClienteTabelaPrecos').val(r.tabela_preco_id).trigger('change');
    } else {
      $('#slcClienteTabelaPrecos').val(null).trigger('change');
    }

    // Carregar contatos para o select
    carregarContatosCliente(r.id, r.contato);

    $('#modalClienteLabel').text('Editar Cliente');
    $('#modalCliente').modal('show');
  });

  // ==== VISUALIZAR ====
  $('#tblClientes').on('click', '.exibir-modal-visualizacao', function () {
    const r = tblClientes.row($(this).closest('tr')).data();
    if (!r) return;

    if (!$form.find('input[name="id"]').length) {
      $form.append('<input type="hidden" name="id" id="cliente_id">');
    }

    $('#cliente_id').val(r.id || '');
    $('#txtClienteCodigo').val(r.codigo || '').prop('disabled', true);
    $('#txtClienteLoja').val(r.loja || '').prop('disabled', true);
    $('#txtClienteNome').val(r.nome || '').prop('disabled', true);
    $('#txtClienteNomeFantasia').val(r.nome_fantasia || '').prop('disabled', true);
    $('#slcClienteTipo').val(r.tipo || '').prop('disabled', true);
    $('#txtClienteCGC').val(r.cgc || '').prop('disabled', true);
    $('#txtClienteEndereco').val(r.endereco || '').prop('disabled', true);
    $('#txtClienteMunicipio').val(r.municipio || '').prop('disabled', true);
    $('#txtClienteEstado').val(r.estado || '').prop('disabled', true);
    $('#txtClienteKm').val(r.km || '').prop('disabled', true);
    $('#txtClienteDeslocamento').val(r.deslocamento || '').prop('disabled', true);
    $('#txtClienteValorHora').val(r.valor_hora || '').prop('disabled', true);
    $('#slcClienteTabelaPrecos').val(r.tabela_preco_id || null).trigger('change').prop('disabled', true);

    // Carregar contatos para o select e desabilitar
    carregarContatosCliente(r.id, r.contato);
    setTimeout(() => $('#txtClienteContato').prop('disabled', true), 100);

    $('#modalClienteLabel').text('Visualizar Cliente');
    $('#modalCliente').modal('show');
  });

  // Reset modal ao fechar
  $('#modalCliente').on('hidden.bs.modal', function () {
    $('#modalCliente input[type="text"], #modalCliente input[type="date"]').prop('disabled', false).val('');
    $('#slcClienteTipo').prop('disabled', false);
    $('#slcClienteTabelaPrecos').prop('disabled', false).val(null).trigger('change');
    $('#txtClienteContato').prop('disabled', false).empty().append(new Option('', '', true, true)).trigger('change');
  });

  // ==== Salvar (add/editar) ====
  $('.btn-salvar-cliente').on('click', function () {
    const form = $('#formCliente');
    if (!validateFormRequired(form)) return;

    $.ajax({
      url: '/salvar-cliente',
      type: 'POST',
      data: form.serialize(),
      success: function (resp) {
        tblClientes.ajax.reload(null, false);
        $('#modalCliente').modal('hide');
        Toast.fire({ icon: 'success', title: resp.message || 'Salvo' });
      },
      error: function (jqXHR, textStatus, errorThrown) {
        if (jqXHR.status === 422 && jqXHR.responseJSON?.errors) {
          let html = '';
          $.each(jqXHR.responseJSON.errors, function (_, arr) {
            $.each(arr, function (_, err) { html += html ? '<br>'+err : err; });
          });
          Toast.fire({ icon: 'error', title: html });
        } else {
          console.error('Error:', textStatus, errorThrown);
          Toast.fire({ icon: 'error', title: errorThrown || 'Erro ao salvar' });
        }
      }
    });
  });

  // ===== Contatos =====
  const tblContatos = $('#tblContatos').DataTable({
    ajax: { url: '', dataSrc: json => json },
    searching: false, info: false, paging: false, ordering: false, processing: true,
    columns: [
      { title:'Nome',  data:'nome',  className:'col-md-3' },
      { title:'Email', data:'email', className:'col-md-3' },
      { title:'Telefone', data:'telefone', className:'col-md-2' },
      {
        title:'Recebe E-mail OS',
        data:'recebe_email_os',
        className:'dt-center',
        width:'120px',
        render: (data) => data ? '<i class="bi bi-check-circle-fill text-success"></i> Sim' : '<i class="bi bi-x-circle-fill text-danger"></i> Não'
      },
      { title:'Aniversário', data:'aniversario', className:'dt-center', width:'100px' },
      {
        title:'', orderable:false, className:'dt-center', width:'100px',
        render: () => `
          <button class="btn btn-sm btn-outline-warning border-0 editar-contato" data-bs-toggle="tooltip" data-bs-title="Editar Contato">
            <i class="bi bi-pencil-fill"></i>
          </button>
          <button class="btn btn-sm btn-outline-danger border-0 remover-contato" data-bs-toggle="tooltip" data-bs-title="Remover Contato">
            <i class="bi bi-person-dash-fill"></i>
          </button>`
      }
    ],
    initComplete: () => initializeTooltips()
  });

  $('#tblClientes').on('click', '.exibir-contatos', function () {
    const r = tblClientes.row($(this).closest('tr')).data();
    if (!r) return;
    $('#modalContatosLabel').text(r.nome + ' - Contatos');
    tblContatos.ajax.url('/listar-contatos?id=' + r.id).load();
    $('#modalContatoLabel').text(r.nome + ' - Adicionar Contato');
    $('#txtContatoClienteId').val(r.id);
    $('#modalContatos').modal('show');
  });

  // Editar contato
  $('#tblContatos').on('click', '.editar-contato', function () {
    const r = tblContatos.row($(this).closest('tr')).data();
    if (!r) return;

    // Adicionar campo hidden para ID do contato se não existir
    if (!$('#formContato').find('input[name="id"]').length) {
      $('#formContato').append('<input type="hidden" name="id" id="contato_id">');
    }

    // Preencher o formulário com os dados do contato
    $('#contato_id').val(r.id || '');
    $('#txtContatoClienteId').val(r.cliente_id || '');
    $('#txtContatoNome').val(r.nome || '');
    $('#txtContatoEmail').val(r.email || '');
    $('#txtContatoTelefone').val(r.telefone || '');
    $('#txtContatoAniversario').val(r.aniversario || '');
    $('#chkContatoRecebeEmailOS').prop('checked', r.recebe_email_os ? true : false);

    $('#modalContatoLabel').text('Editar Contato');
    $('#modalContato').modal('show');
  });

  $('#tblContatos').on('click', '.remover-contato', function () {
    const r = tblContatos.row($(this).closest('tr')).data();
    $.ajax({
      url: '/remover-contato/' + r.id,
      type: 'DELETE',
      success: function (resp) {
        tblContatos.ajax.reload(null, false);
        Toast.fire({ icon: 'success', title: resp.message || 'Removido' });
      },
      error: function (jqXHR, textStatus, errorThrown) {
        if (jqXHR.status === 422 && jqXHR.responseJSON?.errors) {
          let html = '';
          $.each(jqXHR.responseJSON.errors, function (_, arr) {
            $.each(arr, function (_, err) { html += html ? '<br>'+err : err; });
          });
          Toast.fire({ icon: 'error', title: html });
        } else {
          console.error('Error:', textStatus, errorThrown);
          Toast.fire({ icon: 'error', title: errorThrown || 'Erro' });
        }
      }
    });
  });

  // Salvar contato
  $('.btn-salvar-contato').on('click', function () {
    const form = $('#formContato');
    if (!validateFormRequired(form)) return;

    $.ajax({
      url: '/salvar-contato',
      type: 'POST',
      data: form.serialize(),
      success: function (resp) {
        tblContatos.ajax.reload(null, false);
        $('#modalContato').modal('hide');
        $('#formContato')[0].reset();
        Toast.fire({ icon: 'success', title: resp.message || 'Contato salvo com sucesso!' });
      },
      error: function (jqXHR, textStatus, errorThrown) {
        if (jqXHR.status === 422 && jqXHR.responseJSON?.errors) {
          let html = '';
          $.each(jqXHR.responseJSON.errors, function (_, arr) {
            $.each(arr, function (_, err) { html += html ? '<br>'+err : err; });
          });
          Toast.fire({ icon: 'error', title: html });
        } else {
          console.error('Error:', textStatus, errorThrown);
          Toast.fire({ icon: 'error', title: errorThrown || 'Erro ao salvar contato' });
        }
      }
    });
  });

  // Botão para adicionar contato direto no modal do cliente
  $('#btnAdicionarContatoRapido').on('click', function () {
    const clienteId = $('#cliente_id').val();

    if (!clienteId) {
      Toast.fire({ icon: 'warning', title: 'Salve o cliente primeiro antes de adicionar contatos' });
      return;
    }

    // Limpar o formulário e remover o ID se existir
    $('#formContato')[0].reset();
    $('#contato_id').remove();
    $('#chkContatoRecebeEmailOS').prop('checked', true);

    // Obter nome do cliente do modal
    const nomeCliente = $('#txtClienteNome').val() || 'Cliente';

    $('#modalContatoLabel').text(nomeCliente + ' - Adicionar Contato');
    $('#txtContatoClienteId').val(clienteId);

    // Fechar modal do cliente e abrir modal do contato
    $('#modalCliente').modal('hide');

    // Esperar um pouco para fechar antes de abrir o novo modal
    setTimeout(() => {
      $('#modalContato').modal('show');
    }, 300);
  });

  $('#tblClientes').on('click', '.adicionar-contato', function () {
    const r = tblClientes.row($(this).closest('tr')).data();
    if (!r) return;

    // Limpar o formulário e remover o ID se existir
    $('#formContato')[0].reset();
    $('#contato_id').remove();
    $('#chkContatoRecebeEmailOS').prop('checked', true);

    $('#modalContatoLabel').text(r.nome + ' - Adicionar Contato');
    $('#txtContatoClienteId').val(r.id);
    $('#modalContato').modal('show');
  });

  // Reset modal de contato ao fechar
  $('#modalContato').on('hidden.bs.modal', function () {
    $('#formContato')[0].reset();
    $('#contato_id').remove();
    $('#chkContatoRecebeEmailOS').prop('checked', true);
  });

  // ===== Select2 Tabela de Preços =====
  const lista_tabela_precos = $('#slcClienteTabelaPrecos').select2({
    dropdownParent: $('#modalCliente'),
    language: 'pt-BR',
    placeholder: 'Selecione ...',
    theme: 'bootstrap-5',
    width: '100%'
  });

  $.get('/listar-tabelas-precos-ativos').then(function (data) {
    const $sel = lista_tabela_precos;
    $sel.empty().append(new Option('', '', true, true)).trigger('change');
    (data || []).forEach(i => $sel.append(new Option(i.descricao, i.id, false, false)));
  });

  // Ajuste visual do select2/flutuante
  const $s2 = $('#slcClienteTabelaPrecos').parent().find('.select2-selection--single');
  $s2.css('height','calc(3.5rem + 2px)');
  $s2.find('.select2-selection__rendered').css('padding-top','18px');
  $('#slcClienteTabelaPrecos').parent().find('label').css('z-index','1');

  // ===== Select2 Contato Principal =====
  const lista_contato_principal = $('#txtClienteContato').select2({
    dropdownParent: $('#modalCliente'),
    language: 'pt-BR',
    placeholder: 'Selecione um contato ...',
    theme: 'bootstrap-5',
    width: '100%',
    allowClear: true
  });

  // Ajuste visual do select2 contato principal
  const $s2Contato = $('#txtClienteContato').parent().find('.select2-selection--single');
  $s2Contato.css('height','calc(3.5rem + 2px)');
  $s2Contato.find('.select2-selection__rendered').css('padding-top','18px');
  $('#txtClienteContato').parent().find('label').css('z-index','1');
});
