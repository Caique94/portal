# Fluxo Completo de Cadastro de Cliente com Contatos

## 📋 Resumo Executivo

Implementação completa de um fluxo intuitivo para cadastro de clientes e seus contatos, permitindo que o usuário:
1. Crie um novo cliente com dados básicos
2. Salve o cliente e mantenha o modal aberto
3. Adicione quantos contatos desejar
4. Selecione um contato como "Contato Principal"
5. Salve as alterações e finalize

Tudo em um único modal, sem necessidade de fechar e reabrir.

---

## 🎯 Fluxo Passo a Passo

### **Etapa 1: Abrir Modal de Novo Cliente**

**Ação do usuário:** Clica no botão "Adicionar" na DataTable de clientes

**O que acontece no sistema:**
```
┌─────────────────────────────────────┐
│ Adicionar Cliente                   │
├─────────────────────────────────────┤
│ Modal é aberto                      │
│ ✓ Código é gerado automaticamente   │
│ ✓ Código é readonly (não editável)  │
│ ✓ Campos habilitados para edição    │
│ ✓ modoNovoCliente = true            │
│                                     │
│ [Código:  0001 (readonly)]          │
│ [Loja: ___________]  (required)     │
│ [Nome: ___________]  (required)     │
│ [Contato Principal: ________] (opt) │
│                                     │
│ [Salvar] [Fechar]                   │
└─────────────────────────────────────┘
```

**Código JavaScript:**
```javascript
// Botão "Adicionar" dispara:
$.get('/gerar-proximo-codigo-cliente').then(function (data) {
  $('#txtClienteCodigo').val(data.codigo);
});
modoNovoCliente = true;
```

---

### **Etapa 2: Preencher Dados do Cliente**

**Ação do usuário:** Preenche os campos obrigatórios e opcionais

**Campos obrigatórios:**
- Loja
- Nome
- Tabela de Preços

**Campos opcionais:**
- Código (preenchido automaticamente)
- Nome Fantasia
- Tipo
- CPF/CNPJ
- Contato Principal (vazio por enquanto)
- Endereço, Cidade, Estado, KM, Deslocamento

**Estado do sistema:**
```
modoNovoCliente = true
contatosNovoCliente = [] (vazio, sem contatos temporários)
```

---

### **Etapa 3: Salvar o Cliente (Primeiro Salvamento)**

**Ação do usuário:** Clica no botão "Salvar"

**O que acontece:**

1. **Validação:**
   - Valida campos obrigatórios via `validateFormRequired()`
   - Contato Principal é OPCIONAL ✓
   - Campos vazios são indicados visualmente

2. **Requisição ao servidor:**
   ```
   POST /salvar-cliente
   {
     txtClienteCodigo: "0001",
     txtClienteLoja: "Loja 1",
     txtClienteNome: "Empresa XYZ",
     ...
     contatos_novos: [] (vazio no primeiro save)
   }
   ```

3. **Resposta bem-sucedida:**
   - Cliente é criado no banco de dados
   - Recebe ID (ex: id: 5)
   - Laravel atribui código via `HasSequentialCode` trait

4. **Interface após sucesso:**

```
┌─────────────────────────────────────┐
│ Editar Cliente - Adicionar Contatos │
├─────────────────────────────────────┤
│ Toast: "Cliente salvo! Agora você   │
│         pode adicionar contatos."   │
│                                     │
│ [Código: 0001 (disabled)]           │
│ [Loja: ___ (disabled)]              │
│ [Nome: ___ (disabled)]              │
│ [Contato Principal: ________]       │
│ [Adicionar] (habilitado)            │
│                                     │
│ Modal PERMANECE ABERTO ✓            │
│ Campos desabilitados (protegidos)   │
│ botão Salvar desabilitado           │
│                                     │
│              [Fechar e Concluir]    │
└─────────────────────────────────────┘
```

**Estado do sistema após save:**
```javascript
modoNovoCliente = false  // Mudou!
cliente_id = 5           // Agora temos ID
adicionandoContatosAposeSalvar = false
contatosNovoCliente = []
```

---

### **Etapa 4: Adicionar Contato(s)**

**Ação do usuário:** Clica no botão "Adicionar" (dentro do modal de cliente)

**O que acontece:**

1. **Modal de contato abre:**
   ```
   ┌─────────────────────────────┐
   │ Empresa XYZ - Adicionar     │
   │ Contato                     │
   ├─────────────────────────────┤
   │ [Nome: ________________]    │
   │        (required)           │
   │ [Email: _______________]    │
   │ [Telefone: _____________]   │
   │ [Aniversário: ___________]  │
   │ ☑ Recebe e-mail de OS       │
   │                             │
   │ [Salvar] [Fechar]           │
   └─────────────────────────────┘
   ```

2. **Usuário preenche contato:**
   - Nome: "João Silva"
   - Email: "joao@empresa.com"
   - Telefone: "(11) 9999-8888"

3. **Clica em Salvar:**
   - Validação: Nome é obrigatório
   - POST `/salvar-contato`
   - Contato é criado no banco com `cliente_id = 5`

4. **Após sucesso:**
   ```javascript
   // Modal de contato fecha
   // Dropdown de "Contato Principal" é recarregado
   carregarContatosCliente(5, null);

   // Re-habilita controles:
   $('#txtClienteContato').prop('disabled', false);
   $('.btn-salvar-cliente').prop('disabled', false);

   // Setar flag:
   adicionandoContatosAposeSalvar = true;

   // Toast informativo:
   Toast.fire({
     icon: 'info',
     title: 'Contato adicionado! Selecione como principal e salve novamente.'
   });
   ```

5. **Interface volta para modal de cliente:**
   ```
   ┌─────────────────────────────────────┐
   │ Editar Cliente - Adicionar Contatos │
   ├─────────────────────────────────────┤
   │ Toast: "Contato adicionado!         │
   │         Selecione como principal... │
   │                                     │
   │ [Código: 0001 (disabled)]           │
   │ [Loja: ___ (disabled)]              │
   │ [Nome: ___ (disabled)]              │
   │ [Contato Principal: [João Silva] ] ✓ HABILITADO
   │                                     │
   │ Botão Salvar: HABILITADO ✓          │
   │                                     │
   │ [Salvar] [Fechar e Concluir]        │
   └─────────────────────────────────────┘
   ```

**Estado do sistema:**
```javascript
adicionandoContatosAposeSalvar = true
// Contato está disponível para seleção
```

---

### **Etapa 5: Selecionar Contato Principal**

**Ação do usuário:** Seleciona o contato no dropdown "Contato Principal"

**O que acontece:**
- Dropdown lista todos os contatos adicionados
- Usuário seleciona "João Silva"
- Seleção é armazenada no campo `txtClienteContato`

**Opções adicionais:**
- Usuário pode clicar em "Adicionar" novamente para adicionar mais contatos
- Cada novo contato aparecerá no dropdown

---

### **Etapa 6: Salvar Cliente com Contato Principal (Re-save)**

**Ação do usuário:** Clica no botão "Salvar" (agora está habilitado)

**O que acontece:**

1. **Validação:**
   - Valida campos obrigatórios
   - `adicionandoContatosAposeSalvar = true` foi setado
   - Contato Principal já foi selecionado

2. **Requisição ao servidor:**
   ```
   POST /salvar-cliente
   {
     id: 5,  // Agora é update!
     txtClienteCodigo: "0001",
     txtClienteLoja: "Loja 1",
     txtClienteNome: "Empresa XYZ",
     txtClienteContato: "João Silva",  // ATUALIZADO!
     ...
   }
   ```

3. **Laravel processa:**
   - Encontra cliente com ID 5
   - Atualiza campo `contato` com "João Silva"
   - Retorna resposta de sucesso

4. **Interface após re-save:**
   ```
   ┌─────────────────────────────────────┐
   │ Editar Cliente - Adicionar Contatos │
   ├─────────────────────────────────────┤
   │ Toast: "Contato Principal           │
   │         atualizado!"                │
   │                                     │
   │ [Código: 0001 (disabled)]           │
   │ [Loja: ___ (disabled)]              │
   │ [Nome: ___ (disabled)]              │
   │ [Contato Principal: [João Silva]]   │
   │        (disabled novamente)         │
   │                                     │
   │ Botão Salvar: DESABILITADO novamente│
   │ Modal continua ABERTO               │
   │                                     │
   │              [Fechar e Concluir]    │
   └─────────────────────────────────────┘
   ```

**Estado do sistema:**
```javascript
adicionandoContatosAposeSalvar = false  // Voltou ao normal
modoNovoCliente = false
cliente_id = 5
// Pronto para adicionar mais contatos ou fechar
```

---

### **Etapa 7: Finalizar (Opcional: Adicionar Mais Contatos)**

**Cenário A: Adicionar Mais Contatos**
- Clica em "Adicionar" novamente
- Repete Etapas 4-6

**Cenário B: Finalizar**
- Clica em "Fechar e Concluir"
- Modal fecha
- Cliente e contatos aparecem na tabela
- Fluxo completo termina

---

## 📊 Diagrama de Estados

```
┌──────────────────────┐
│  Novo Cliente        │
│  (modoNovoCliente=T) │
│  (adicionandoContatos=F)
└──────────┬───────────┘
           │ Clica "Salvar"
           ▼
┌──────────────────────┐
│  Cliente Salvo       │
│  (modoNovoCliente=F) │
│  (adicionandoContatos=F)
│  Campos desabilitados│
│  Botão Salvar desa.  │
└──────────┬───────────┘
           │ Clica "Adicionar Contato"
           │ e salva contato
           ▼
┌──────────────────────┐
│  Contato Adicionado  │
│  (adicionandoContatos=T)
│  Campos semi-hab.    │
│  Botão Salvar hab.   │
└──────────┬───────────┘
           │ Seleciona contato
           │ e clica "Salvar"
           ▼
┌──────────────────────┐
│  Contato Principal   │
│  Definido            │
│  (adicionandoContatos=F)
│  Campos desab. novam.│
└──────────┬───────────┘
           │ Clica "Fechar e Concluir"
           ▼
┌──────────────────────┐
│  Modal Fecha         │
│  Estado Resetado     │
│  Pronto para novo    │
└──────────────────────┘
```

---

## 🔄 Estados de Habilitação de Campos

| Situação | Código | Loja | Nome | Contato Prin. | Salvar | Fechar |
|----------|--------|------|------|---------------|--------|--------|
| Novo cliente | RO | ✓ | ✓ | ✓ | ✓ | ✓ |
| Cliente salvo | ✗ | ✗ | ✗ | ✗ | ✗ | "Fechar e Concluir" |
| Contato adicionado | ✗ | ✗ | ✗ | ✓ | ✓ | "Fechar e Concluir" |
| Re-save após contato | ✗ | ✗ | ✗ | ✗ | ✗ | "Fechar e Concluir" |

**Legenda:**
- `✓` = Habilitado e editável
- `✗` = Desabilitado (disabled)
- `RO` = ReadOnly (não editável)

---

## 🔐 Validações

### Na Criação do Cliente:
```javascript
if (!validateFormRequired(form)) return;

// Campos obrigatórios:
// - txtClienteLoja (required)
// - txtClienteNome (required)
// - slcClienteTabelaPrecos (required)

// Campos opcionais:
// - txtClienteContato (AGORA opcional!)
// - todos os outros
```

### Na Adição de Contato:
```javascript
if (!$('#txtContatoNome').val().trim()) {
  Toast.fire({ icon: 'error', title: 'Nome do contato é obrigatório' });
  return;
}
// Outros campos são opcionais
```

---

## 💾 Fluxo de Banco de Dados

### Primeiro Save (Criar Cliente):
```
POST /salvar-cliente
├─ Cria: Cliente { codigo: "0001", loja: "Loja 1", nome: "Empresa", ... }
├─ Atribui: id = 5
├─ Se contatos_novos foi enviado:
│  └─ Cria múltiplos: Contato { cliente_id: 5, nome: "João", ... }
└─ Retorna: { id: 5, ... }
```

### Adicionar Contato:
```
POST /salvar-contato
├─ Cria: Contato { cliente_id: 5, nome: "João Silva", ... }
└─ Retorna: { id: 10, ... }
```

### Re-save (Atualizar Contato Principal):
```
POST /salvar-cliente (com id)
├─ Encontra: Cliente { id: 5 }
├─ Atualiza: contato = "João Silva"
└─ Retorna: { ok: true, msg: "Cliente atualizado", ... }
```

---

## 🎯 Flags de Controle

```javascript
modoNovoCliente                    // Boolean
├─ true:  Novo cliente, antes de primeiro save
├─ false: Cliente já salvo
└─ Função: Determina se modal fica aberto/fecha após save

adicionandoContatosAposeSalvar    // Boolean
├─ true:  Usuário adicionou contato, pode salvar novamente
├─ false: Situação normal
└─ Função: Permitir re-save sem fechar modal

contatosNovoCliente               // Array
├─ [ ]: Contatos a serem salvos com novo cliente
└─ Função: Armazenar contatos antes de salvar cliente
```

---

## ✨ Melhorias Implementadas

1. **Código Auto-gerado** ✓
   - Não precisa preencher manualmente
   - Gerado via `/gerar-proximo-codigo-cliente`

2. **Modal Permanece Aberto** ✓
   - Após salvar cliente
   - Usuário não perde contexto

3. **Fluxo Intuitivo** ✓
   - Salvar → Adicionar contatos → Salvar novamente
   - Tudo em um único modal

4. **Validações Inteligentes** ✓
   - Contato Principal é opcional até que haja contatos
   - Depois que há contatos, pode ser selecionado

5. **Feedback Visual** ✓
   - Toasts informativos em cada etapa
   - Estados de campos claros (habilitado/desabilitado)

---

## 🐛 Casos de Uso Suportados

### ✓ Criar cliente sem contatos
1. Preenche dados
2. Salva
3. Clica "Fechar e Concluir"

### ✓ Criar cliente com um contato
1. Preenche dados
2. Salva
3. Adiciona contato
4. Seleciona como principal
5. Salva novamente
6. Clica "Fechar e Concluir"

### ✓ Criar cliente com múltiplos contatos
1. Preenche dados
2. Salva
3. Adiciona contato 1
4. Seleciona como principal
5. Salva
6. Adiciona contato 2
7. Seleciona como principal (atualiza)
8. Salva
9. Clica "Fechar e Concluir"

### ✓ Editar cliente existente
1. Clica "Editar" na tabela
2. Modifica dados
3. Salva
4. Modal fecha (comportamento normal de edição)

---

## 📝 Commits Relacionados

1. `0a093a2` - Auto-generate Código field
2. `8f595e4` - Keep modal open after saving new client
3. `6f8a3d1` - Improve contact management flow
4. `27dba61` - Make Contato Principal field optional
5. `5a5dab7` - Enable contact principal selection and re-save

---

## 🚀 Próximas Melhorias Sugeridas

- [ ] Exibir tabela de contatos já criados dentro do modal
- [ ] Permitir edição/exclusão de contatos do modal
- [ ] Validação para não permitir contatos duplicados
- [ ] Atalho de teclado para salvar (Ctrl+S)
- [ ] Confirmação ao fechar se houver mudanças não salvas
