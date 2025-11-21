# 📋 RPS - Implementação de Filtro de Clientes

## 📌 Visão Geral da Implementação

Este documento descreve as alterações necessárias para adicionar um **filtro de seleção de clientes** na emissão de RPS. O fluxo será:

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. Usuário clica em "Emitir RPS"                                │
│    └─ Abre MODAL DE SELEÇÃO DE CLIENTES (novo)                  │
│                                                                  │
│ 2. Usuário seleciona UM cliente                                 │
│    └─ Carrega APENAS ordens daquele cliente com status = 6      │
│                                                                  │
│ 3. Usuário seleciona UMA OU MAIS RPS do cliente selecionado     │
│    └─ Lógica de seleção múltipla já existe!                     │
│                                                                  │
│ 4. Abre MODAL DE EMISSÃO (já existe)                            │
│    └─ Preenchido com dados do cliente e RPS selecionadas        │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔧 ALTERAÇÕES TÉCNICAS

### **ARQUIVO 1: resources/views/faturamento.blade.php**

#### Mudança 1.1: Adicionar Modal de Seleção de Clientes (NOVO)

**O QUE:** Criar novo modal para seleção de clientes
**ONDE:** Antes do fechamento da seção `@section('modal')`
**LOCALIZAÇÃO:** Após a linha 122 (antes de `@endsection`)

```html
<!-- ===== NOVO MODAL: Seleção de Clientes ===== -->
<div class="modal fade" id="modalSelecionarCliente" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecionar Cliente para Emissão de RPS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Campo de busca -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="inputBuscaCliente" class="form-control" placeholder="Buscar cliente por nome ou código...">
                    </div>
                </div>

                <!-- Lista de clientes -->
                <div class="list-group" id="listaClientesRPS" style="max-height: 400px; overflow-y: auto;">
                    <div class="list-group-item text-muted">
                        <small>Carregando clientes...</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<!-- ===== FIM NOVO MODAL ===== -->
```

---

### **ARQUIVO 2: public/js/faturamento.js**

#### Mudança 2.1: Adicionar Função para Carregar Clientes com RPS

**O QUE:** Nova função para buscar clientes que têm ordens aguardando RPS
**ONDE:** Logo após `carregarCondicoesPagamento()` (linha 35)
**ANTES DE:** `let tblFaturamento = ...`

```javascript
// ===== NOVO: Carregar clientes disponíveis para RPS =====
function carregarClientesParaRPS() {
    $.ajax({
        url: '/clientes-com-ordens-rps',  // ← Novo endpoint (ver Mudança 3.1)
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var lista = $('#listaClientesRPS');
            lista.empty();

            if (response.data && response.data.length > 0) {
                $.each(response.data, function(i, cliente) {
                    var html = `
                        <button type="button" class="list-group-item list-group-item-action btn-selecionar-cliente-rps"
                                data-cliente-id="${cliente.id}"
                                data-cliente-nome="${cliente.nome}"
                                data-cliente-codigo="${cliente.codigo}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${cliente.nome}</h6>
                                <small class="text-muted">${cliente.codigo}</small>
                            </div>
                            <p class="mb-0 text-muted"><small>${cliente.numero_ordens} ordem(s) aguardando RPS</small></p>
                        </button>
                    `;
                    lista.append(html);
                });
            } else {
                lista.html('<div class="list-group-item text-muted text-center"><small>Nenhum cliente com ordens aguardando RPS</small></div>');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Erro ao carregar clientes:', errorThrown);
            $('#listaClientesRPS').html('<div class="list-group-item text-danger"><small>Erro ao carregar clientes</small></div>');
        }
    });
}

// ===== NOVO: Filtrar lista de clientes durante busca =====
$('#inputBuscaCliente').on('keyup', function() {
    var termo = $(this).val().toLowerCase();
    $('#listaClientesRPS .btn-selecionar-cliente-rps').each(function() {
        var nome = $(this).data('cliente-nome').toLowerCase();
        var codigo = $(this).data('cliente-codigo').toLowerCase();

        if (nome.includes(termo) || codigo.includes(termo)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// ===== NOVO: Ao selecionar cliente, filtrar tabela e abrir modal de RPS =====
$(document).on('click', '.btn-selecionar-cliente-rps', function() {
    var cliente_id = $(this).data('cliente-id');
    var cliente_nome = $(this).data('cliente-nome');

    console.log('Cliente selecionado:', cliente_id, cliente_nome);

    // Fechar modal de seleção
    var modalSelecionarCliente = bootstrap.Modal.getInstance(document.getElementById('modalSelecionarCliente'));
    if (modalSelecionarCliente) {
        modalSelecionarCliente.hide();
    }

    // Filtrar tabela para mostrar apenas ordens deste cliente com status = 6
    filtrarTabelaPorClienteRPS(cliente_id, cliente_nome);
});
```

#### Mudança 2.2: Nova Função para Filtrar Tabela

**O QUE:** Filtrar e exibir apenas ordens do cliente selecionado
**ONDE:** Logo após Mudança 2.1

```javascript
// ===== NOVO: Filtrar tabela por cliente e abrir seleção de RPS =====
function filtrarTabelaPorClienteRPS(cliente_id, cliente_nome) {
    var ordem_arr = [];
    var valor_total = 0;

    // Limpar filtros anteriores
    tblFaturamento.search('').draw();

    // Buscar todas as ordens do cliente com status = 6 (AGUARDANDO_RPS)
    $('#tblFaturamento tbody tr').each(function() {
        var rowData = tblFaturamento.row($(this)).data();

        if (rowData && rowData.status == 6 && rowData.cliente_id == cliente_id) {
            ordem_arr.push({
                id: rowData.id,
                numero: ('00000000' + rowData.id).slice(-8),
                valor: parseFloat(rowData.valor_total || 0)
            });
            valor_total += parseFloat(rowData.valor_total || 0);
        }
    });

    console.log('Ordens encontradas:', ordem_arr, 'Valor total:', valor_total);

    if (ordem_arr.length > 0) {
        // Mostrar checkbox para seleção múltipla
        abrirModalSelecaoRPS(cliente_id, cliente_nome, ordem_arr, valor_total);
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Sem ordens disponíveis',
            text: `Nenhuma ordem aguardando RPS para o cliente ${cliente_nome}`
        });
    }
}
```

#### Mudança 2.3: Nova Função para Modal de Seleção

**O QUE:** Abre modal para selecionar quais RPS agrupar
**ONDE:** Logo após Mudança 2.2

```javascript
// ===== NOVO: Modal para seleção de múltiplas RPS =====
function abrirModalSelecaoRPS(cliente_id, cliente_nome, ordem_arr, valor_total) {
    var checkboxesHTML = `
        <div class="mb-3">
            <p><strong>Selecione quais ordens deseja agrupar para este RPS:</strong></p>
            <p class="text-muted"><small>Cliente: <strong>${cliente_nome}</strong></small></p>
        </div>
        <div style="max-height: 300px; overflow-y: auto;">
    `;

    $.each(ordem_arr, function(i, ordem) {
        checkboxesHTML += `
            <div class="form-check" style="margin-bottom: 10px;">
                <input class="form-check-input rps-checkbox-novo" type="checkbox"
                       id="rps_novo_${ordem.id}" value="${ordem.id}" checked>
                <label class="form-check-label" for="rps_novo_${ordem.id}">
                    OS ${ordem.numero} - R$ ${parseFloat(ordem.valor).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'})}
                </label>
            </div>
        `;
    });

    checkboxesHTML += '</div>';

    Swal.fire({
        title: 'Selecionar Ordens para Agrupar',
        html: checkboxesHTML,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Confirmar Seleção',
        cancelButtonText: 'Voltar',
        backdrop: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary'
        },
        didOpen: (modal) => {
            // Evento para atualizar total quando checkbox muda
            modal.querySelectorAll('.rps-checkbox-novo').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    atualizarValorTotalModal(ordem_arr);
                });
            });
            // Mostrar valor inicial
            atualizarValorTotalModal(ordem_arr);
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Coletar ordens selecionadas
            var ordem_arr_final = [];
            var valor_total_final = 0;

            $('#rps-checkbox-novo').each(function() {
                if ($(this).is(':checked')) {
                    var id = $(this).val();
                    var ordem = ordem_arr.find(o => o.id == id);
                    if (ordem) {
                        ordem_arr_final.push(ordem.id);
                        valor_total_final += ordem.valor;
                    }
                }
            });

            // Se nada foi selecionado, usar todas
            if (ordem_arr_final.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecione pelo menos uma ordem'
                });
                return;
            }

            // Abrir modal de emissão (já existente)
            abrirModalEmissaoRPS(cliente_id, cliente_nome, ordem_arr_final, valor_total_final);
        }
    });
}

// ===== NOVO: Atualizar total no modal de seleção =====
function atualizarValorTotalModal(ordem_arr) {
    var valor_total = 0;
    var checked = [];

    document.querySelectorAll('.rps-checkbox-novo:checked').forEach(checkbox => {
        var id = checkbox.value;
        var ordem = ordem_arr.find(o => o.id == id);
        if (ordem) {
            valor_total += ordem.valor;
            checked.push(ordem.numero);
        }
    });

    var totalFormatado = valor_total.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });

    var swalContent = document.querySelector('.swal2-html-container');
    if (swalContent) {
        var msgAnterior = swalContent.querySelector('.total-selecionado-modal');
        if (msgAnterior) {
            msgAnterior.remove();
        }

        if (checked.length > 0) {
            var msgExtra = `
                <div class="total-selecionado-modal mt-3 p-3 bg-light border rounded">
                    <p class="mb-2"><strong>${checked.length} ordem(s) selecionada(s)</strong></p>
                    <p class="mb-0"><strong>Total:</strong> <span class="text-success">${totalFormatado}</span></p>
                </div>
            `;
            swalContent.insertAdjacentHTML('beforeend', msgExtra);
        }
    }
}
```

#### Mudança 2.4: Modificar Button "Emitir RPS" Original

**O QUE:** Mudar comportamento do botão "Emitir RPS" para abrir modal de clientes
**ONDE:** Linhas 261-403 (substitua a ação do botão)

**ANTES (linhas 264-403):**
```javascript
action: function(e, dt, node, config) {
    // ... todo o código antigo
}
```

**DEPOIS (novo código):**
```javascript
action: function(e, dt, node, config) {
    // Validação: deve ter pelo menos um item selecionado com status = 6
    var temOrdenValida = false;
    $('#tblFaturamento').find('.check-faturamento-row:checked').each(function() {
        var row = $(this).closest('tr');
        var rowData = tblFaturamento.row(row).data();
        if (rowData && rowData.status == 6) {
            temOrdenValida = true;
            return false;
        }
    });

    if (!temOrdenValida) {
        Swal.fire({
            icon: 'warning',
            title: 'Nenhuma OS válida',
            text: 'Selecione pelo menos uma ordem com status "Aguardando RPS"'
        });
        return;
    }

    // Abrir modal de seleção de clientes
    carregarClientesParaRPS();
    var modalSelecionarCliente = new bootstrap.Modal(
        document.getElementById('modalSelecionarCliente'),
        { backdrop: 'static', keyboard: false }
    );
    modalSelecionarCliente.show();
}
```

#### Mudança 2.5: Corrigir Seletor no Modal de Seleção

**O QUE:** Bug fix - seletor estava errado na função Mudança 2.3
**ONDE:** Na função `abrirModalSelecaoRPS` onde coleta valores

**ANTES:**
```javascript
$('#rps-checkbox-novo').each(function() {
```

**DEPOIS:**
```javascript
$('.rps-checkbox-novo').each(function() {
```

---

### **ARQUIVO 3: app/Http/Controllers/OrdemServicoController.php** (ou RPSController.php)

#### Mudança 3.1: Adicionar Novo Endpoint - Listar Clientes com RPS Pendentes

**O QUE:** Nova rota API que retorna clientes com ordens aguardando RPS
**ONDE:** Adicionar novo método público na classe controller
**LOCALIZAÇÃO:** Em OrdemServicoController.php, adicionar após o método `list_invoice()`

```php
/**
 * Endpoint: GET /clientes-com-ordens-rps
 * Retorna lista de clientes que têm ordens aguardando RPS
 */
public function clientesComOrdensRPS()
{
    try {
        // Buscar todos os clientes que têm ordens com status = 6 (AGUARDANDO_RPS)
        $clientes = Cliente::whereHas('ordensServico', function($query) {
                $query->where('status', 6);  // Status 6 = AGUARDANDO_RPS
            })
            ->with([
                'ordensServico' => function($query) {
                    $query->where('status', 6)
                          ->select('id', 'cliente_id', 'status');
                }
            ])
            ->select('id', 'codigo', 'nome')
            ->orderBy('nome')
            ->get()
            ->map(function($cliente) {
                return [
                    'id'               => $cliente->id,
                    'codigo'           => $cliente->codigo,
                    'nome'             => $cliente->nome,
                    'numero_ordens'    => $cliente->ordensServico->count()
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $clientes
        ]);
    } catch (\Exception $e) {
        \Log::error('Erro ao buscar clientes para RPS', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erro ao carregar clientes'
        ], 500);
    }
}
```

---

### **ARQUIVO 4: routes/web.php**

#### Mudança 4.1: Adicionar Nova Rota

**O QUE:** Registrar a nova rota `/clientes-com-ordens-rps`
**ONDE:** Na seção de rotas de Faturamento/RPS (próximo a `/listar-ordens-faturamento`)
**LOCALIZAÇÃO:** Após a linha que tem `Route::get('/listar-ordens-faturamento', ...)`

```php
// Nova rota para obter clientes com RPS pendentes
Route::get('/clientes-com-ordens-rps', [OrdemServicoController::class, 'clientesComOrdensRPS']);
```

---

## 📊 RESUMO DAS ALTERAÇÕES

| Arquivo | Tipo | Linha(s) | O QUE MUDA |
|---------|------|----------|-----------|
| `faturamento.blade.php` | View | +122 | Adiciona Modal de Seleção de Clientes |
| `faturamento.js` | JavaScript | +36-150 | 5 novas funções + modifica botão "Emitir RPS" |
| `OrdemServicoController.php` | Controller | +método | Novo método `clientesComOrdensRPS()` |
| `routes/web.php` | Rotas | +1 linha | Nova rota GET `/clientes-com-ordens-rps` |

---

## 🔄 FLUXO COMPLETO APÓS AS ALTERAÇÕES

```
┌─────────────────────────────────────────────────────────────────────┐
│ 1. USUÁRIO NA TABELA DE FATURAMENTO                                 │
│    - Vê lista de todas as ordens em status "Aguardando RPS"          │
│    - Pode ter ordens de múltiplos clientes                           │
└────────────────┬────────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 2. CLICA EM "EMITIR RPS" (nova lógica)                             │
│    - Abre Modal: "Selecionar Cliente para Emissão"                  │
│    - Modal carrega clientes via /clientes-com-ordens-rps            │
│    - Mostra: Nome, Código, Número de ordens aguardando              │
│    - Permite buscar cliente por nome/código                         │
└────────────────┬────────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 3. SELECIONA UM CLIENTE                                              │
│    - Fecha modal de seleção                                          │
│    - Carrega APENAS ordens daquele cliente (status = 6)              │
│    - Abre Modal: "Selecionar Ordens para Agrupar"                    │
│    - Mostra lista de checkboxes para múltiplas ordens                │
│    - Todas pré-selecionadas (checked)                                │
│    - Atualiza total dinamicamente                                    │
└────────────────┬────────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 4. SELECIONA QUAL(IS) ORDEM(S) AGRUPAR                              │
│    - Pode desselecionar uma ou mais ordens                           │
│    - Total é recalculado em tempo real                               │
│    - Clica "Confirmar Seleção"                                       │
└────────────────┬────────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 5. ABRE MODAL DE EMISSÃO (JÁ EXISTE)                                │
│    - Modal: "Emitir RPS"                                             │
│    - Preenchido com:                                                 │
│      * Cliente (já definido)                                         │
│      * Ordens selecionadas (já definidas)                            │
│      * Total calculado                                               │
│    - Usuário preenche: Número, Série, Data, Condição de Pagamento   │
│    - Clica "Salvar"                                                  │
└────────────────┬────────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 6. RPS CRIADA COM SUCESSO                                           │
│    - Tabela atualiza automaticamente                                 │
│    - Ordens mudam para status "RPS Emitida"                         │
│    - Toast mostra mensagem de sucesso                                │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🧪 TESTES A REALIZAR

### Teste 1: Carregar Modal de Clientes
```
1. Ir para FATURAMENTO
2. Selecionar uma ordem (status = 6)
3. Clicar "Emitir RPS"
4. ✅ Deve abrir modal com lista de clientes
5. ✅ Deve mostrar: Nome, Código, Número de ordens
```

### Teste 2: Buscar Cliente
```
1. Modal de clientes aberto
2. Digitar nome parcial na busca
3. ✅ Lista deve filtrar em tempo real
```

### Teste 3: Selecionar Cliente e Ordens
```
1. Clicar em um cliente
2. ✅ Deve fechar modal de seleção
3. ✅ Deve abrir modal de seleção de ordens
4. ✅ Deve mostrar APENAS ordens daquele cliente
5. ✅ Deve ter checkboxes para múltiplas seleções
```

### Teste 4: Cálculo de Total
```
1. Modal de seleção de ordens aberto
2. Desmarcar uma ordem
3. ✅ Total deve recalcular
4. ✅ Deve mostrar número de ordens selecionadas
```

### Teste 5: Emitir RPS
```
1. Confirmar seleção de ordens
2. ✅ Deve abrir modal de emissão de RPS
3. ✅ Modal deve ter cliente e ordens pré-preenchidas
4. Preencher formulário normalmente
5. Clicar "Salvar"
6. ✅ RPS deve ser criada com sucesso
```

---

## 📌 DEPENDÊNCIAS E CONSIDERAÇÕES

### Dependências Existentes (JÁ ESTÃO NO PROJETO)
- ✅ DataTables
- ✅ Bootstrap 5
- ✅ jQuery
- ✅ SweetAlert2
- ✅ Modelos: Cliente, OrdemServico

### Cuidados
- A função `abrirModalEmissaoRPS()` já existe e continua funcionando normalmente
- O filtro cliente_id === cliente_id já existe e continua valendo
- Status 6 = AGUARDANDO_RPS (não mudar!)

---

## 💡 EXPLICAÇÃO TÉCNICA PARA PASSAR CONHECIMENTO ADIANTE

### Conceito Chave 1: Modal em Cascata
O sistema agora usa **2 modais em sequência**:
1. **Modal 1:** Seleção de clientes (novo)
2. **Modal 2:** Seleção de ordens para agrupar (adaptado do existente)
3. **Modal 3:** Formulário de emissão (já existia)

### Conceito Chave 2: Filtragem em Frontend
A filtragem de clientes e ordens é feita **no browser**:
- Cliente clica "Emitir RPS"
- JavaScript busca `/clientes-com-ordens-rps` (backend)
- Exibe lista em modal (frontend)
- Ao selecionar cliente, **filtra tabela local** (no DataTable)

### Conceito Chave 3: AJAX Assíncrono
O endpoint `/clientes-com-ordens-rps` usa AJAX:
```javascript
$.ajax({
    url: '/clientes-com-ordens-rps',  // Request ao backend
    success: function(response) {      // Quando resposta chegar
        // Renderizar no frontend
    }
})
```

### Conceito Chave 4: Data Attributes para Armazenar Dados
Botões de cliente armazenam dados via `data-*`:
```html
<button ...
    data-cliente-id="${cliente.id}"
    data-cliente-nome="${cliente.nome}"
    data-cliente-codigo="${cliente.codigo}">
```

Depois acessados via jQuery:
```javascript
$(this).data('cliente-id')     // Obtém valor
```

### Conceito Chave 5: Event Delegation
Usar `.on()` ao invés de `.click()` permite que elementos **criados dinamicamente** tenham handlers:
```javascript
// ✅ Funciona para elementos criados depois
$(document).on('click', '.btn-selecionar-cliente-rps', function() {...})

// ❌ NÃO funciona para elementos dinâmicos
$('.btn-selecionar-cliente-rps').click(function() {...})
```

---

## 📝 RESUMO PARA COMPARTILHAR COM A EQUIPE

**Passo 1:** Adicionar Modal de Seleção de Clientes em `faturamento.blade.php`
- Modal com input de busca
- Lista dinâmica de clientes com ordens aguardando RPS

**Passo 2:** Adicionar Funções JavaScript em `faturamento.js`
- `carregarClientesParaRPS()` - busca clientes no backend
- `filtrarTabelaPorClienteRPS()` - filtra ordens por cliente
- `abrirModalSelecaoRPS()` - abre modal com checkboxes
- `atualizarValorTotalModal()` - recalcula total

**Passo 3:** Modificar Botão "Emitir RPS"
- Ao invés de fazer tudo de uma vez
- Agora abre modal de seleção de clientes

**Passo 4:** Adicionar Endpoint Backend
- Novo método em `OrdemServicoController`
- Retorna clientes que têm ordens com status = 6

**Passo 5:** Registrar Rota
- Nova rota GET `/clientes-com-ordens-rps`

---

## 🎯 BENEFÍCIOS DA IMPLEMENTAÇÃO

| Benefício | Descrição |
|-----------|-----------|
| **Clareza** | Usuário seleciona cliente explicitamente |
| **Redução de Erros** | Não consegue misturar ordens de clientes diferentes |
| **UX Melhorada** | Modal de busca é intuitivo |
| **Performance** | Carrega apenas dados do cliente selecionado |
| **Manutenibilidade** | Funções bem definidas e reutilizáveis |

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

- [ ] Adicionar modal em `faturamento.blade.php`
- [ ] Adicionar função `carregarClientesParaRPS()`
- [ ] Adicionar função `filtrarTabelaPorClienteRPS()`
- [ ] Adicionar função `abrirModalSelecaoRPS()`
- [ ] Adicionar função `atualizarValorTotalModal()`
- [ ] Adicionar event handler para seleção de cliente
- [ ] Adicionar event handler para busca de cliente
- [ ] Modificar ação do botão "Emitir RPS"
- [ ] Corrigir seletor `.rps-checkbox-novo`
- [ ] Adicionar método `clientesComOrdensRPS()` no controller
- [ ] Adicionar rota `/clientes-com-ordens-rps`
- [ ] Testar: Abrir modal de clientes
- [ ] Testar: Buscar cliente
- [ ] Testar: Selecionar cliente e carregar ordens
- [ ] Testar: Selecionar múltiplas ordens
- [ ] Testar: Emitir RPS com sucesso
- [ ] Testar: Verificar dados salvos no banco

---

**Versão:** 1.0
**Data:** 2025-11-21
**Status:** ✅ Pronto para Implementação
