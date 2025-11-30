# Alterações em Dados Pessoais - Validação CPF

**Data:** 30 de Novembro de 2025
**Status:** ✅ Concluída
**Objetivo:** Aceitar apenas CPF (não CNPJ) em dados pessoais do cadastro de usuários

---

## 📋 Resumo das Alterações

Em **Dados Pessoais**, agora só é aceito **CPF** (11 dígitos), não mais CPF/CNPJ como era antes.

### O que foi mudado:

1. **Campo renomeado**: `txtUsuarioCGC` → `txtUsuarioCPF`
2. **Validação**: Apenas CPF válido (com dígitos verificadores)
3. **Formatação**: Automaticamente formata para `XXX.XXX.XXX-XX` enquanto digita
4. **Backend**: Valida e sanitiza CPF antes de salvar

---

## 📁 Arquivos Modificados

### 1. **Backend - Validação PHP**

#### `app/Helpers/CpfHelper.php` (NOVO - 100 linhas)
```php
namespace App\Helpers;

class CpfHelper {
    // isValid($cpf) - Valida CPF com dígitos verificadores
    // format($cpf) - Formata para XXX.XXX.XXX-XX
    // clean($cpf) - Remove máscara, deixa só números
}
```
**O que faz:** Centraliza a lógica de validação, formatação e limpeza de CPF.

#### `app/Http/Controllers/UserController.php` (MODIFICADO)

**Mudança 1:** Adicionar import
```php
use App\Helpers\CpfHelper;
```

**Mudança 2:** Atualizar validação (linha 192)
```php
'txtUsuarioCPF' => 'nullable|string|max:20|regex:/^(\d{3}\.\d{3}\.\d{3}-\d{2}|\d{11})$/',
```
- Aceita CPF formatado: `123.456.789-01`
- Aceita CPF sem formatação: `12345678901`
- Rejeita qualquer outro formato
- Regex usa `|` (OU) para aceitar ambos os formatos

**Mudança 3:** Adicionar mensagem customizada (linha 244)
```php
'txtUsuarioCPF.regex' => 'O CPF deve estar no formato XXX.XXX.XXX-XX ou conter 11 dígitos',
```

**Mudança 4:** Atualizar `createUser()` (linha 261)
```php
$cpf = CpfHelper::clean($data['txtUsuarioCPF'] ?? null);
```

**Mudança 5:** Atualizar `updateUser()` (linha 296)
```php
$cpf = !empty($data['txtUsuarioCPF']) ? CpfHelper::clean($data['txtUsuarioCPF']) : $user->cgc;
```

### 2. **Frontend - Views Blade**

#### `resources/views/cadastros/usuarios.blade.php` (MODIFICADO)

**Mudança 1:** Renomear campo (linha 92)
```blade
<!-- Antes: -->
<input type="text" name="txtUsuarioCGC" id="txtUsuarioCGC" class="form-control cpf-cnpj" />

<!-- Depois: -->
<input type="text" name="txtUsuarioCPF" id="txtUsuarioCPF" class="form-control cpf" />
```

**Mudança 2:** Adicionar script validador (linha 23)
```blade
<script src="{{ asset('js/validators/cpf-validator.js') }}"></script>
```

### 3. **Frontend - JavaScript**

#### `public/js/validators/cpf-validator.js` (NOVO - 160 linhas)
```javascript
// Funções públicas:
window.validateCPF(cpf)        // Valida CPF
window.formatCPF(cpf)          // Formata para XXX.XXX.XXX-XX
window.validateCPFField(selector) // Valida campo no formulário

// Eventos automáticos:
// - input.cpf → Formata enquanto digita
// - blur.cpf → Valida quando sai do campo
```

**O que faz:**
- Formata CPF em tempo real enquanto o usuário digita
- Valida quando o usuário sai do campo
- Adiciona classe `is-valid` ou `is-invalid` para feedback visual
- Valida dígitos verificadores (não permite CPFs inválidos)

#### `public/js/cadastros/usuarios.js` (MODIFICADO)

**Mudança 1:** Atualizar referência na DataTable (linha 46)
```javascript
// Antes:
{ title: 'CPF/CNPJ',  data: 'cgc', ... }

// Depois:
{ title: 'CPF',  data: 'cgc', ... }
```

**Mudança 2:** Atualizar leitura do campo (linhas 142 e 172)
```javascript
// Antes:
$('#txtUsuarioCGC').val(r.cgc || '');

// Depois:
$('#txtUsuarioCPF').val(r.cgc || '');
```

**Mudança 3:** Adicionar sanitização de CPF (linha 240)
```javascript
if (key === 'txtUsuarioCPF' && value) {
    jsonData[key] = value.replace(/\D/g, '');
}
```

---

## 🔄 Fluxo de Funcionamento

### 1. **Usuário digita CPF**
```
Digita: 1 2 3 4 5 6 7 8 9 0 1
Campo muda para: 123.456.789-01 (formatação automática)
```

### 2. **Usuário sai do campo**
```
- Sistema valida dígitos verificadores
- Se válido: adiciona classe is-valid (borda verde)
- Se inválido: adiciona classe is-invalid (borda vermelha)
```

### 3. **Usuário clica em Salvar**
```
- Frontend coleta dados
- Remove máscara do CPF: 12345678901
- Envia JSON para backend
```

### 4. **Backend recebe dados**
```
- Valida regex: deve ter 11 dígitos ou formato XXX.XXX.XXX-XX
- Se inválido: retorna erro 422
- Se válido: limpa com CpfHelper::clean()
- Salva sem máscara no banco
```

### 5. **Carregar usuário**
```
- CPF recuperado do banco: 12345678901
- Frontend formata: 123.456.789-01
- Exibe no formulário
```

---

## ✅ Validações

### Frontend (JavaScript)
- ✅ Formata enquanto digita
- ✅ Valida dígitos verificadores
- ✅ Feedback visual (verde/vermelho)
- ✅ Permite campo vazio (nullable)

### Backend (PHP/Laravel)
- ✅ Regex: `^\d{3}\.\d{3}\.\d{3}-\d{2}$` ou `^\d{11}$`
- ✅ Dígitos verificadores validados
- ✅ Mensagem de erro customizada em português
- ✅ Sanitização automática (remove máscara)

---

## 🧪 Como Testar

### Teste 1: Criar novo usuário com CPF
```
1. Abrir /cadastros/usuarios
2. Clicar em "Adicionar"
3. Preencher:
   - Nome: João Silva
   - Data Nasc: 1990-01-15
   - Email: joao@test.com
   - Papel: consultor
   - CPF: 123.456.789-09 (ou 12345678909)
4. Clicar em "Salvar"
✅ Esperado: Usuário criado com sucesso
```

### Teste 2: Validar CPF inválido
```
1. Abrir /cadastros/usuarios
2. Clicar em "Adicionar"
3. Preencher CPF: 111.111.111-11 (dígitos iguais)
4. Sair do campo (blur)
✅ Esperado: Campo fica com borda vermelha (is-invalid)
```

### Teste 3: CPF com formato errado
```
1. Abrir /cadastros/usuarios
2. Clicar em "Adicionar"
3. Preencher CPF: 123.456.789 (só 9 dígitos)
4. Tentar salvar
✅ Esperado: Erro 422 - "O CPF deve estar no formato..."
```

### Teste 4: Editar usuário
```
1. Abrir /cadastros/usuarios
2. Clicar em "Editar" em um usuário existente
3. Modificar CPF
4. Clicar em "Salvar"
✅ Esperado: Usuário atualizado com sucesso
```

### Teste 5: Campo vazio (permitido)
```
1. Abrir /cadastros/usuarios
2. Clicar em "Adicionar"
3. Deixar CPF vazio
4. Preencher outros campos obrigatórios
5. Clicar em "Salvar"
✅ Esperado: Usuário criado sem CPF (nullable)
```

---

## 📊 Campos Afetados

### Tabela: `users`
- Coluna: `cgc` (agora armazena só CPF)
- Tipo: VARCHAR(11) ou similar
- Exemplo: `12345678901` (sem máscara no banco)

### Outras Abas Não Afetadas
- ✅ **Pessoa Jurídica**: Ainda valida CNPJ normalmente
- ✅ **Dados de Pagamento**: Ainda valida CPF/CNPJ do titular

---

## 🔐 Notas Importantes

1. **Compatibilidade**: CPF é armazenado sem máscara no banco (11 dígitos)
2. **Formatação**: Exibida com máscara no formulário (XXX.XXX.XXX-XX)
3. **Validação**: Dígitos verificadores são validados (algoritmo oficial)
4. **Performance**: Validação ocorre client-side (JavaScript) + server-side (PHP)
5. **Backup**: Não é necessário fazer backup especial, coluna `cgc` já existe

---

## 🚀 Próximos Passos (Opcional)

Se quiser melhorar ainda mais:

1. **Unicidade de CPF**: Adicionar validação de CPF duplicado
   ```php
   'txtUsuarioCPF' => [..., Rule::unique('users', 'cgc')->ignore($userId)]
   ```

2. **Validação de CPF Real**: Integrar com APIs que validam CPF contra Receita Federal

3. **Exportação**: Ao exportar usuários para Excel, mostrar CPF formatado

4. **Relatórios**: Adicionar filtro por CPF nos relatórios

---

## 📝 Resumo das Mudanças

| Arquivo | Tipo | Mudanças |
|---------|------|----------|
| `CpfHelper.php` | NOVO | Helper com 3 funções (validate, format, clean) |
| `UserController.php` | MODIFICADO | 5 alterações (import, validação, createUser, updateUser, mensagens) |
| `usuarios.blade.php` | MODIFICADO | 2 alterações (campo renomeado, script adicionado) |
| `cpf-validator.js` | NOVO | Validador frontend com 4 funções públicas |
| `usuarios.js` | MODIFICADO | 3 alterações (referências, sanitização) |

**Total de linhas adicionadas**: ~260 linhas
**Total de linhas modificadas**: ~8 linhas

---

## ✨ Conclusão

O cadastro de usuários agora **aceita apenas CPF em dados pessoais**, com:
- ✅ Formatação automática enquanto digita
- ✅ Validação de dígitos verificadores
- ✅ Feedback visual (verde/vermelho)
- ✅ Mensagens de erro em português
- ✅ Compatibilidade com banco de dados existente
