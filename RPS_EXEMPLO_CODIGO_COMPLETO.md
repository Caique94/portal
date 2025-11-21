# 🔍 RPS Filtro de Clientes - EXEMPLOS DE CÓDIGO COMPLETO

## Documento Complementar: Código Pronto para Copiar e Colar

Este documento contém o **código exato** a adicionar/modificar em cada arquivo.

---

## 📄 ARQUIVO 1: resources/views/faturamento.blade.php

### Onde adicionar?
**APÓS linha 122** (antes do `@endsection` da seção de modals)

### Código a adicionar (copiar e colar):

```blade
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

@endsection
```

**Importante:** Garanta que esta seção seja **ANTES de `@endsection`**

---

## 📝 ARQUIVO 2: public/js/faturamento.js

### Local 1: Adicionar após carregarCondicoesPagamento() (após linha 35)

```javascript
    }

    // ===== NOVO: Carregar clientes disponíveis para RPS =====
    function carregarClientesParaRPS() {
        $.ajax({
            url: '/clientes-com-ordens-rps',
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

                document.querySelectorAll('.rps-checkbox-novo:checked').forEach(checkbox => {
                    var id = parseInt(checkbox.value);
                    var ordem = ordem_arr.find(o => o.id == id);
                    if (ordem) {
                        ordem_arr_final.push(ordem.id);
                        valor_total_final += ordem.valor;
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
            var id = parseInt(checkbox.value);
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

    let tblFaturamento = $('#tblFaturamento').DataTable({
```

---

### Local 2: SUBSTITUIR a ação do botão "Emitir RPS" (linhas 261-403)

**ENCONTRE:**
```javascript
                text: 'Emitir RPS',
                className: 'btn-primary',
                visible: papel == 'financeiro' || papel == 'admin',
                action: function(e, dt, node, config) {
                    var ordem_arr = [];
                    // ... TODO O CÓDIGO ANTIGO ATÉ linha 403
                }
```

**SUBSTITUA POR:**
```javascript
                text: 'Emitir RPS',
                className: 'btn-primary',
                visible: papel == 'financeiro' || papel == 'admin',
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

---

## 💾 ARQUIVO 3: app/Http/Controllers/OrdemServicoController.php

### Onde adicionar?
**Após o método `list_invoice()`** ou em qualquer local público da classe (antes do fechamento da classe)

### Código a adicionar:

```php
    /**
     * Endpoint: GET /clientes-com-ordens-rps
     * Retorna lista de clientes que têm ordens aguardando RPS
     *
     * @return \Illuminate\Http\JsonResponse
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar clientes'
            ], 500);
        }
    }
```

---

## 🛣️ ARQUIVO 4: routes/web.php

### Onde adicionar?
**Na seção de rotas de Faturamento/RPS**, próximo às outras rotas de `OrdemServicoController`

Procure por uma linha como:
```php
Route::get('/listar-ordens-faturamento', [OrdemServicoController::class, 'list_invoice']);
```

E **ADICIONE após esta linha**:
```php
Route::get('/clientes-com-ordens-rps', [OrdemServicoController::class, 'clientesComOrdensRPS']);
```

**Resultado esperado:**
```php
Route::get('/listar-ordens-faturamento', [OrdemServicoController::class, 'list_invoice']);
Route::get('/clientes-com-ordens-rps', [OrdemServicoController::class, 'clientesComOrdensRPS']);  // ← NOVA ROTA
```

---

## 🧪 TESTE RÁPIDO

Após adicionar o código, teste assim:

### Teste 1: Verificar se modal aparece
```
1. Abra navegador: http://localhost:8001
2. Vá para FATURAMENTO
3. Selecione uma ordem com status "Aguardando RPS"
4. Clique "Emitir RPS"
5. Esperado: ✅ Modal de seleção de clientes deve aparecer
```

### Teste 2: Verificar se endpoint funciona
```
1. Abra navegador console (F12)
2. Na aba Network, filtre por XHR
3. Clique "Emitir RPS"
4. Procure por request para "/clientes-com-ordens-rps"
5. Esperado: ✅ Status 200 com JSON contendo clientes
```

### Teste 3: Verificar busca
```
1. Modal de clientes aberto
2. Digite nome de cliente na busca
3. Esperado: ✅ Lista deve filtrar em tempo real
```

### Teste 4: Emitir RPS completo
```
1. Modal de clientes aberto
2. Clique em um cliente
3. Selecione uma ou mais ordens
4. Clique "Confirmar Seleção"
5. Preencha formulário de RPS
6. Clique "Salvar"
7. Esperado: ✅ RPS criada com sucesso
```

---

## 🐛 TROUBLESHOOTING

### Erro: "Arquivo não encontrado" ao abrir /clientes-com-ordens-rps
**Solução:** Verifique se a rota foi adicionada em `routes/web.php`

### Erro: "Método não encontrado"
**Solução:** Verifique se o método `clientesComOrdensRPS()` foi adicionado em `OrdemServicoController`

### Modal não abre
**Solução:**
1. Abra F12 > Console
2. Verifique se há erros JavaScript
3. Verifique se `#modalSelecionarCliente` existe no HTML

### Lista de clientes vazia
**Solução:**
1. Verifique no banco se existem ordens com `status = 6`
2. Execute: `SELECT * FROM ordem_servico WHERE status = 6;`
3. Verifique se o endpoint `/clientes-com-ordens-rps` retorna dados (teste em browser)

### Checkboxes não funcionam
**Solução:**
1. Verifique se classe `.rps-checkbox-novo` está sendo usada
2. Não confunda com `.rps-checkbox` (era a classe antiga)
3. Teste F12 > Console > `document.querySelectorAll('.rps-checkbox-novo')`

---

## 📊 ESTRUTURA FINAL DOS ARQUIVOS

```
Faturamento/
├── resources/views/faturamento.blade.php
│   ├── Modal original (emissão RPS)
│   └── ✨ NOVO: Modal seleção clientes
│
├── public/js/faturamento.js
│   ├── Funções originais (datatables, etc)
│   ├── ✨ NOVO: carregarClientesParaRPS()
│   ├── ✨ NOVO: filtrarTabelaPorClienteRPS()
│   ├── ✨ NOVO: abrirModalSelecaoRPS()
│   ├── ✨ NOVO: atualizarValorTotalModal()
│   └── ✏️ MODIFICADO: Botão "Emitir RPS"
│
├── app/Http/Controllers/OrdemServicoController.php
│   ├── Métodos originais
│   └── ✨ NOVO: clientesComOrdensRPS()
│
└── routes/web.php
    └── ✨ NOVA ROTA: /clientes-com-ordens-rps
```

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

```
ARQUIVO: faturamento.blade.php
- [ ] Adicionado modal #modalSelecionarCliente
- [ ] Modal tem input #inputBuscaCliente
- [ ] Modal tem div #listaClientesRPS
- [ ] Modal está ANTES de @endsection

ARQUIVO: faturamento.js
- [ ] Função carregarClientesParaRPS() adicionada
- [ ] Event handler para #inputBuscaCliente adicionado
- [ ] Event handler para .btn-selecionar-cliente-rps adicionado
- [ ] Função filtrarTabelaPorClienteRPS() adicionada
- [ ] Função abrirModalSelecaoRPS() adicionada
- [ ] Função atualizarValorTotalModal() adicionada
- [ ] Botão "Emitir RPS" tem nova ação
- [ ] Não há duplicatas de funções

ARQUIVO: OrdemServicoController.php
- [ ] Método clientesComOrdensRPS() adicionado
- [ ] Método tem try/catch para erros
- [ ] Método retorna JSON com 'success' e 'data'
- [ ] Filtro por status = 6 está correto

ARQUIVO: routes/web.php
- [ ] Rota GET /clientes-com-ordens-rps adicionada
- [ ] Rota aponta para OrdemServicoController::clientesComOrdensRPS
- [ ] Não há duplicatas de rotas

TESTES
- [ ] Modal de clientes abre ao clicar "Emitir RPS"
- [ ] Busca de cliente funciona
- [ ] Seleção de cliente carrega ordens corretamente
- [ ] Modal de seleção de ordens mostra checkboxes
- [ ] Total é recalculado ao desmarcar ordens
- [ ] Confirmação abre modal de emissão com dados corretos
- [ ] RPS é criada com sucesso
```

---

**Versão:** 1.0
**Data:** 2025-11-21
**Tipo:** Código Pronto para Colar
