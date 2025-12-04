# Fluxo de Cadastro de Cliente - Versão 3 (Melhorado)

## Resumo Geral

Implementação de um fluxo otimizado para cadastro de clientes com contatos, onde o usuário pode:
1. Abrir o formulário de novo cliente
2. O **Código** é gerado automaticamente
3. Preencher os dados do cliente
4. **Salvar o cliente** e manter o modal aberto
5. **Adicionar contatos** sem fechar o modal
6. **Fechar e concluir** após terminar

## Fluxo Passo a Passo

### 1. Abrir Modal de Novo Cliente

**Ação:** Usuário clica no botão "Adicionar"

**O que acontece:**
- Modal é aberto com formulário em branco
- **Código é gerado automaticamente** via API `/gerar-proximo-codigo-cliente`
- Código é exibido como **readonly** (não pode ser editado)
- Campos de cliente ficam habilitados para edição
- Badge de contatos não é visível
- Botões disponíveis: **"Salvar"** e **"Fechar"**

```javascript
// Lógica no "Adicionar" button
$.get('/gerar-proximo-codigo-cliente').then(function (data) {
  $('#txtClienteCodigo').val(data.codigo || '');
});
```

### 2. Preencher Dados do Cliente

**Campos obrigatórios:**
- `txtClienteCodigo` - Preenchido automaticamente, readonly
- `txtClienteLoja` - Obrigatório
- `txtClienteNome` - Obrigatório
- `slcClienteTabelaPrecos` - Obrigatório

**Campos opcionais:**
- Nome Fantasia, Tipo, CPF/CNPJ, Contato Principal, Endereço, Cidade, Estado, KM, Deslocamento

### 3. Salvar o Cliente (Primeiro Salvamento)

**Ação:** Usuário clica em **"Salvar"**

**Validação:**
- Valida campos obrigatórios (sem "Contato Principal" obrigatório no primeiro salvamento)
- Se houver contatos temporários, inclui no payload

**Após Sucesso:**
- ✅ Cliente é criado no banco de dados
- ✅ Modal **PERMANECE ABERTO**
- ✅ Toast de sucesso: `"Cliente salvo! Agora você pode adicionar contatos."`
- ✅ Campos do cliente ficam **DESABILITADOS** (readonly)
- ✅ Botão "Salvar" fica **DESABILITADO**
- ✅ Botão "Fechar" muda para **"Fechar e Concluir"**
- ✅ Tabela de clientes é atualizada em background
- ✅ Label do modal muda para `"Editar Cliente - Adicionar Contatos"`

```javascript
// Modal permanece aberto
// Modal não é fechado com $('#modalCliente').modal('hide')

// Campos desabilitados
$('#modalCliente input[type="text"], #modalCliente input[type="date"]')
  .prop('disabled', true);
$('#slcClienteTipo').prop('disabled', true);
$('#slcClienteTabelaPrecos').prop('disabled', true);
```

### 4. Adicionar Contatos

**Ação:** Usuário clica em **"Adicionar"** (botão com ícone de pessoa+)

**O que acontece:**
- Modal de contatos é aberto
- Formulário de contatos é limpo
- Cliente ID é preenchido automaticamente
- Modal label mostra: `"Cliente Nome - Adicionar Contato"`

**Formulário de Contato:**
- **Nome** - Obrigatório
- Email - Opcional
- Telefone - Opcional
- Aniversário - Opcional
- Checkbox: "Recebe e-mail de Ordem de Serviço" - Marcado por padrão

**Após Salvar Contato:**
- ✅ Contato é criado no banco de dados imediatamente
- ✅ Modal de contatos fecha
- ✅ Toast de sucesso: `"Salvo"`
- ✅ **Tabela de contatos (à direita) é atualizada**
- ✅ **Dropdown "Contato Principal" é recarregado com novo contato**
- ✅ Usuário pode adicionar mais contatos clicando novamente em "Adicionar"

### 5. Selecionar Contato Principal

**Ação:** Após adicionar contatos, usuário pode selecionar um no dropdown **"Contato Principal"**

**O que acontece:**
- Dropdown lista todos os contatos criados
- Usuário seleciona um como principal
- Seleção é feita apenas para exibição/referência no formulário

### 6. Fechar e Concluir

**Ação:** Usuário clica em **"Fechar e Concluir"**

**O que acontece:**
- Modal fecha
- Formulário é resetado
- Campos são reabilitados para próximo uso
- Botão "Salvar" volta a ficar habilitado
- Botão "Fechar" volta a aparecer no lugar de "Fechar e Concluir"
- Cliente aparece na tabela com todos os seus contatos

## Mudanças Técnicas Implementadas

### 1. Auto-geração de Código (feat: Auto-generate Código field)
- Campo `txtClienteCodigo` agora é `readonly`
- Novo endpoint: `GET /gerar-proximo-codigo-cliente`
- Lógica: Busca próximo número da sequence sem incrementar
- Validação: Campo agora é `nullable` em vez de `required`

### 2. Modal Aberto Após Salvar (feat: Keep modal open after saving)
- Salvamento não fecha modal automaticamente
- Campos desabilitados após save
- Botões dinâmicos (Salvar ↔ Fechar e Concluir)
- Label dinâmico

### 3. Fluxo de Contatos Melhorado (fix: Improve contact management)
- Botão "Adicionar Contato" funciona pós-save
- Detecção automática de cliente_id
- Recarregamento de dropdown de contatos
- Suporte a ambos cenários: novo cliente vs. cliente já salvo

## Estados do Modal

### Estado 1: Novo Cliente (Antes de Salvar)
```
┌─────────────────────────────────┐
│ Adicionar Cliente               │
├─────────────────────────────────┤
│ [Código: 0001 (readonly)]       │
│ [Loja: ________]                │
│ [Nome: ________]                │
│ [Contato Principal: ________]   │
│ [Adicionar Contato button]      │
│                                 │
│ [Salvar] [Fechar]              │
└─────────────────────────────────┘
```

### Estado 2: Cliente Salvo (Pós-Save)
```
┌─────────────────────────────────┐
│ Editar Cliente - Adicionar      │
│ Contatos                        │
├─────────────────────────────────┤
│ [Código: 0001 (disabled)]       │
│ [Loja: ________ (disabled)]     │
│ [Nome: ________ (disabled)]     │
│ [Contato Principal: ________]   │
│ [Adicionar Contato button]      │
│ 2 contatos                      │
│                                 │
│                 [Fechar e       │
│                 Concluir]       │
└─────────────────────────────────┘

Toast: "Cliente salvo! Agora você pode adicionar contatos."
```

## Validações

### Na criação do cliente:
- Código: gerado automaticamente
- Loja: obrigatório
- Nome: obrigatório
- Tabela de Preços: obrigatório

### Na adição de contato:
- Nome do contato: obrigatório
- Recebe email OS: marcado por padrão
- Email, Telefone, Aniversário: opcionais

## Fluxo do Banco de Dados

```
POST /salvar-cliente
├─ Cria Cliente
├─ Atribui código via HasSequentialCode trait
└─ Se contatos_novos foi enviado:
   └─ Cria múltiplos registros de Contato

POST /salvar-contato
└─ Cria/Atualiza Contato e vincula ao cliente_id
```

## Commits Relacionados

1. `0a093a2` - Auto-generate Código field
2. `8f595e4` - Keep modal open after save
3. `6f8a3d1` - Improve contact management flow

## Próximas Melhorias (Sugestões)

- [ ] Tornar "Contato Principal" opcional até que haja pelo menos um contato
- [ ] Adicionar validação para evitar duplicação de contatos
- [ ] Exibir contatos já salvos em uma tabela dentro do modal
- [ ] Permitir edição de contatos direto do modal pós-save
