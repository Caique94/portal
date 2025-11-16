@extends('layout.master')

@section('title', '- Fornecedores')

@push('styles')
<style>
  /* tabela alinhada, sem “torto” no cabeçalho */
  #tblFornecedores{width:100%!important}
  #tblFornecedores thead th{vertical-align:middle; white-space:nowrap}
  /* posição dos ícones de ordenação (DataTables + Bootstrap 5) */
  table.dataTable>thead .sorting:before,
  table.dataTable>thead .sorting_asc:before,
  table.dataTable>thead .sorting_desc:before { right: .95rem; }
  table.dataTable>thead .sorting:after,
  table.dataTable>thead .sorting_asc:after,
  table.dataTable>thead .sorting_desc:after { right: .55rem; }
  /* centralizações e alturas iguais */
  #tblFornecedores td, #tblFornecedores th{vertical-align:middle}
  /* dropdown ações gruda à direita da célula */
  #tblFornecedores td .dropdown-menu{min-width: 8rem}
</style>
@endpush


@section('content')
<div class="d-flex align-items-center gap-2 mb-2">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFornecedor">Adicionar</button>

    <div class="btn-group">
        <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">Colunas</button>
        <ul class="dropdown-menu">
            <li class="dropdown-item"><label class="form-check"><input class="form-check-input toggle-col" type="checkbox" data-col="0" checked> Código</label></li>
            <li class="dropdown-item"><label class="form-check"><input class="form-check-input toggle-col" type="checkbox" data-col="1" checked> Nome</label></li>
            <li class="dropdown-item"><label class="form-check"><input class="form-check-input toggle-col" type="checkbox" data-col="2" checked> CNPJ</label></li>
            <li class="dropdown-item"><label class="form-check"><input class="form-check-input toggle-col" type="checkbox" data-col="3" checked> Telefone</label></li>
            <li class="dropdown-item"><label class="form-check"><input class="form-check-input toggle-col" type="checkbox" data-col="4" checked> Email</label></li>
            <li class="dropdown-item"><label class="form-check"><input class="form-check-input toggle-col" type="checkbox" data-col="5" checked> Ações</label></li>
        </ul>
    </div>

    <span class="ms-2">Exibir</span>
    <select id="pageLen" class="form-select form-select-sm" style="width:80px">
        <option>10</option><option>25</option><option>50</option><option>100</option>
    </select>
    <span class="ms-2">resultados por página</span>

    <div class="ms-auto d-flex align-items-center" style="width:280px">
        <span class="me-2">Buscar</span>
        <input id="buscaFor" class="form-control form-control-sm" />
    </div>
</div>

<div class="table-responsive">
    <table id="tblFornecedores" class="table table-striped mb-0 w-100">
        <thead class="table-primary">
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>CNPJ</th>
                <th>Telefone</th>
                <th>Email</th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- MODAL ADD/EDIT -->
<div class="modal fade" id="modalFornecedor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalFornecedorLabel">Adicionar Fornecedor</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formFornecedor">
            <input type="hidden" name="id" id="fornecedor_id">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Código</label>
                    <input name="codigo" class="form-control" required>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Nome</label>
                    <input name="nome" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">CPF/CNPJ</label>
                    <input name="cnpj" class="form-control cpf-cnpj">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Inscrição Estadual</label>
                    <input name="ie" class="form-control">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Telefone</label>
                    <input name="telefone" class="form-control phone">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-control">
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Endereço</label>
                <input name="endereco" class="form-control">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button id="btnSalvarFornecedor" class="btn btn-success" type="button">Salvar</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Fechar</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
  // Remove “Pesquisar/Buscar” externo se ainda vier do layout
  (function killExtraSearch(){
    $('label,span').filter(function(){
      const t=$(this).text().trim().toLowerCase();
      return (t==='pesquisar'||t==='buscar') && $(this).next('input').length;
    }).each(function(){ $(this).closest('.row, .col, .mb-3, .form-group, .input-group, div').remove(); });
  })();

  const $tbl = $('#tblFornecedores').length ? $('#tblFornecedores') : $('#tbl-fornecedores');
  if(!$tbl.length) return;

  const dt = $tbl.DataTable({
    ajax:{ url:'/listar-fornecedores', dataSrc:'' },
    autoWidth:false,
    lengthChange:false, pageLength:10,
    searching:true, ordering:true, responsive:false,
    columns:[
      { data:'codigo',   className:'dt-center', width: 90 },
      { data:'nome',     className:'' },
      { data:'cnpj',     className:'dt-center', width:160, defaultContent:'' },
      { data:'telefone', className:'dt-center', width:160, defaultContent:'' },
      { data:'email',    className:'', defaultContent:'' },
      {
        data:null, orderable:false, className:'dt-center', width:80,
        render:()=>(
          '<div class="dropdown">'+
            '<button class="btn btn-sm btn-outline-primary border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">'+
              '<i class="bi bi-list"></i>'+
            '</button>'+
            '<ul class="dropdown-menu dropdown-menu-end">'+
              '<li><a class="dropdown-item text-warning act-edit" href="javascript:void(0)">Editar</a></li>'+
            '</ul>'+
          '</div>'
        )
      }
    ],
    initComplete:function(){ killExtraSearch && killExtraSearch(); }
  });

  // “Colunas”, “Buscar”, “Exibir por página” (se existirem na sua view)
  $('#pageLen').on('change', function(){ dt.page.len(+this.value).draw(); });
  $('#buscaFor').on('input', function(){ dt.search(this.value).draw(); });
  $('.toggle-col').on('change', function(){ dt.column($(this).data('col')).visible(this.checked); });

  // Abrir modal de Edição preenchendo dados
  $tbl.on('click', '.act-edit', function(){
    const r = dt.row($(this).closest('tr')).data(); if(!r) return;
    if (!$('#fornecedor_id').length) $('#formFornecedor').append('<input type="hidden" id="fornecedor_id" name="id">');
    $('#fornecedor_id').val(r.id||'');
    $('[name="codigo"]').val(r.codigo||'');
    $('[name="nome"]').val(r.nome||'');
    $('[name="cnpj"]').val(r.cnpj||r.cgc||'');
    $('[name="ie"]').val(r.ie||'');
    $('[name="telefone"]').val(r.telefone||'');
    $('[name="email"]').val(r.email||'');
    $('[name="endereco"]').val(r.endereco||'');
    $('#modalFornecedorLabel').text('Editar Fornecedor');
    $('#modalFornecedor').modal('show');
  });

  // Salvar (add/editar)
  $('#btnSalvarFornecedor').off('click').on('click', function(){
    const $btn=$(this).prop('disabled',true).text('Salvando...');
    $.post('/salvar-fornecedor', $('#formFornecedor').serialize())
      .done(r=>{ $('#modalFornecedor').modal('hide'); dt.ajax.reload(null,false);
                 Toast.fire({icon:'success', title:r?.message||'Salvo com sucesso'}); })
      .fail(jq=>{
        if (jq.status===422){ let m=''; $.each(jq.responseJSON?.errors||{},(_,a)=>a.forEach(e=>m+=m?'<br>'+e:e));
          Toast.fire({icon:'error', title:m||'Erro de validação'}); }
        else { Toast.fire({icon:'error', title:jq.statusText||'Erro ao salvar'}); }
      })
      .always(()=> $btn.prop('disabled',false).text('Salvar'));
  });

  // “Adicionar” (zera modal)
  $('[data-bs-target="#modalFornecedor"]').on('click', function(){
    $('#formFornecedor')[0].reset();
    $('#fornecedor_id').val('');
    $('#modalFornecedorLabel').text('Adicionar Fornecedor');
  });
});
</script>
@endpush
