// public/js/produtos.js
$(document).ready(function () {
  $.fn.dataTable.ext.errMode = 'none';

  function pickVal($root, names) {
    for (const n of names) {
      const $el = $root.find(`[name="${n}"]`);
      if ($el.length && String($el.val()).trim() !== '') return String($el.val()).trim();
    }
    return '';
  }

  const tblProdutos = $('#tblProdutos').DataTable({
    ajax: {
      url: '/listar-produtos',
      type: 'GET',
      dataType: 'text',
      cache: false,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      dataSrc: function (raw) {
        try {
          const json = JSON.parse(raw);
          if (Array.isArray(json)) return json;
          if (json && Array.isArray(json.data)) return json.data;
          return [];
        } catch {
          Toast?.fire ? Toast.fire({ icon: 'error', title: 'Falha ao listar produtos.' }) : alert('Falha ao listar produtos.');
          return [];
        }
      }
    },
    columns: [
      { title: 'C&oacute;digo', data: 'codigo', className: 'dt-center', width: '120px' },
      { title: 'Descri&ccedil;&atilde;o', data: 'nome', width: '40%' },
      { title: 'Narrativa', data: 'narrativa', defaultContent: '' },
      {
        title: 'Ativo', className: 'dt-center', orderable: false, width: '80px',
        render: (_, __, row) => {
          const checked = (row.ativo === true || row.ativo === 1 || row.ativo === '1') ? 'checked' : '';
          return `<div class="d-flex align-items-center justify-content-center">
                    <div class="form-check form-switch">
                      <input type="checkbox" role="switch"
                             id="chkProdutosAtivo${row.id}"
                             class="form-check-input toggle-produto" ${checked}>
                    </div>
                  </div>`;
        }
      }
    ],
    order: [[1, 'asc']],
    buttons: {
      name: 'primary',
      buttons: [{
        text: 'Adicionar',
        className: 'btn-primary',
        action: function () {
          $('#formProduto')[0].reset();
          const $chk = $('#formProduto input[name="ativo"]');
          if ($chk.length) $chk.prop('checked', true);
          if (!$('#formProduto input[name="id"]').length) {
            $('#formProduto').append('<input type="hidden" name="id" value="">');
          } else {
            $('#formProduto input[name="id"]').val('');
          }
          $('#modalProduto').modal('show');
        }
      }]
    },
    initComplete: function () {
      $('#tblProdutos tbody').on('click', '.toggle-produto', function () {
        const rowData = tblProdutos.row($(this).closest('tr')).data();
        $.ajax({
          url: `/toggle-produto/${rowData.id}`,
          type: 'GET',
          dataType: 'json',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .done(resp => {
          tblProdutos.ajax.reload(null, false);
          Toast.fire({ icon: 'success', title: resp?.msg || 'Status atualizado.' });
        })
        .fail(jq => {
          const msg = jq.responseJSON?.msg
            || (jq.responseJSON?.errors ? Object.values(jq.responseJSON.errors).flat().join('<br>') : '')
            || 'Falha ao atualizar.';
          Toast.fire({ icon: 'error', title: msg });
        });
      });
    }
  });

  $('#modalProduto').on('hidden.bs.modal', function () {
    $('#modalProduto input[type="text"], #modalProduto textarea').val('');
  });

  $('.btn-salvar-produto').on('click', function () {
    const $form = $('#formProduto');
    if (!validateFormRequired($form)) return;

    // aceita vários names possíveis do seu form
    const codigo = pickVal($form, ['codigo','cod','txtcodigo','txt_produto_codigo','txtprodutocodigo']);
    const descricao = pickVal($form, ['nome','descricao','descrição','txtdescricao','txt_produto_descricao','txtprodutodescricao']);
    const narrativa = pickVal($form, ['narrativa','obs','observacao','observação','txtnarrativa']);

    const payload = {
      id: $form.find('input[name="id"]').val() || '',
      codigo: codigo,
      nome: descricao,                 // backend espera "nome"
      narrativa: narrativa || '',
      ativo: $form.find('input[name="ativo"]').is(':checked') ? 1 : 0
    };

    $.ajax({
      url: '/salvar-produto',
      type: 'POST',
      data: payload,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .done(resp => {
      tblProdutos.ajax.reload(null, false);
      $('#modalProduto').modal('hide');
      Toast.fire({ icon: 'success', title: resp?.msg || 'Salvo com sucesso.' });
    })
    .fail(jq => {
      const msg = jq.responseJSON?.msg
        || (jq.responseJSON?.errors ? Object.values(jq.responseJSON.errors).flat().join('<br>') : '')
        || jq.responseText
        || 'Erro ao salvar.';
      Toast.fire({ icon: 'error', title: msg });
    });
  });

});
