$(function () {
  const $tbl = $('#tblFornecedores');

  // remove "Pesquisar" externo (robusto + watchdog)
  (function removePesquisar(){
    const kill = () => {
      $('label, span, div').filter(function(){
        const t = $(this).text().replace(/[:\s]/g,'').toLowerCase();
        return t === 'pesquisar';
      }).each(function(){
        const box = $(this).closest('.row, .col, .mb-3, .form-group, .input-group, div');
        if (box.length) box.remove();
      });
      $("[placeholder='Pesquisar']").closest('.row, .col, .mb-3, .form-group, .input-group, div').remove();
    };
    kill();
    const mo = new MutationObserver(kill);
    mo.observe(document.body, {childList:true, subtree:true});
  })();

  const tbl = $tbl.DataTable({
    ajax: { url: '/listar-fornecedores', dataSrc: '' },
    dom:
      "<'row'<'col-sm-6'B><'col-sm-6 text-end'f>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [
      {
        text: 'Adicionar',
        className: 'btn-primary',
        action: function () {
          ensureHiddenId();
          $('#formFornecedor')[0].reset();
          $('#fornecedor_id').val('');
          $('#modalFornecedorLabel').text('Adicionar Fornecedor');
          $('#modalFornecedor').modal('show');
        }
      },
      { extend: 'colvis', columns: ':not(.noVis)', text: 'Colunas' }
    ],
    columns: [
      { title: 'Código',   data: 'codigo',   className: 'dt-center', width: 100 },
      { title: 'Nome',     data: 'nome' },
      { title: 'CNPJ',     data: 'cnpj',     className: 'dt-center', width: 160, defaultContent: '' },
      { title: 'Telefone', data: 'telefone', className: 'dt-center', width: 160, defaultContent: '' },
      { title: 'Email',    data: 'email',    defaultContent: '' },
      {
        title: 'Ações',
        data: null,
        className: 'dt-center',
        orderable: false,
        width: 80,
        render: () => (
          '<div class="dropdown">'+
            '<button class="btn btn-sm btn-outline-primary border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">'+
              '<i class="bi bi-list"></i>'+
            '</button>'+
            '<ul class="dropdown-menu">'+
              '<li><a class="dropdown-item text-warning exibir-modal-edicao" href="javascript:void(0);">Editar</a></li>'+
            '</ul>'+
          '</div>'
        )
      }
    ],
    initComplete: function () {
      // caso algum bundle ainda injete CSV/PDF, remove
      try { tbl.buttons(['.buttons-csv', '.buttons-pdf']).remove(); } catch(e){}
    }
  });

  // clique em Editar
  $tbl.on('click', '.exibir-modal-edicao', function () {
    const r = tbl.row($(this).closest('tr')).data();
    if (!r) return;

    ensureHiddenId();
    $('[name="id"]').val(r.id || '');
    $('[name="codigo"]').val(r.codigo || '');
    $('[name="loja"]').val(r.loja || '');
    $('[name="nome"]').val(r.nome || '');
    $('[name="nome_fantasia"]').val(r.nome_fantasia || '');
    $('[name="cnpj"]').val(r.cnpj || r.cgc || '');
    $('[name="ie"]').val(r.ie || '');
    $('[name="telefone"]').val(r.telefone || '');
    $('[name="email"]').val(r.email || '');
    $('[name="endereco"]').val(r.endereco || '');

    $('#modalFornecedorLabel').text('Editar Fornecedor');
    $('#modalFornecedor').modal('show');
  });

  // salvar add/editar
  $('.btn-salvar-fornecedor').on('click', function () {
    $.post('/salvar-fornecedor', $('#formFornecedor').serialize())
      .done(resp => {
        tbl.ajax.reload(null, false);
        $('#modalFornecedor').modal('hide');
        Toast.fire({ icon: 'success', title: resp?.message || 'Salvo com sucesso' });
      })
      .fail(jq => {
        if (jq.status === 422) {
          let msg = '';
          $.each(jq.responseJSON?.errors || {}, (_, arr) => arr.forEach(e => msg += (msg?'<br>':'')+e));
          Toast.fire({ icon:'error', title: msg || 'Erro de validação' });
        } else {
          Toast.fire({ icon:'error', title: jq.statusText || 'Erro ao salvar' });
        }
      });
  });

  $('#modalFornecedor').on('hidden.bs.modal', function () {
    $('#modalFornecedor input[type="text"], #modalFornecedor input[type="date"]').val('');
  });

  function ensureHiddenId(){
    if (!$('#formFornecedor input[name="id"]').length) {
      $('#formFornecedor').append('<input type="hidden" name="id" id="fornecedor_id">');
    }
  }
});
