# 💰 Teste Completo - Campos Monetários

**Data:** 30 de Novembro de 2025
**Status:** ✅ Pronto para Teste
**Objetivo:** Validar que campos monetários aceitam valores em formato brasileiro e exibem corretamente na edição

---

## 🧪 Teste Rápido (5 minutos)

### Passo 1: Criar novo usuário com valores monetários

1. Abrir: http://localhost:8000/cadastros/usuarios
2. Clicar em "Adicionar"
3. Preencher campos pessoais:
   - Nome: `João Silva`
   - Data Nasc: `1990-01-15`
   - Email: `joao@example.com`
   - Celular: `(11) 98765-4321`
   - Papel: `Consultor`
   - CPF: `12345678909`

4. **Preencher campos monetários:**
   ```
   ✅ Valor Hora:        150,00
   ✅ Valor Desloc.:     50,50
   ✅ Valor KM:          3,50
   ✅ Salário Base:      3.500,00
   ```

   **Observar enquanto digita:**
   - Campo formata automaticamente: `150` → `150,00` → `150,00`
   - Campo formata automaticamente: `3500` → `3.500,00`

5. Clicar em "Salvar"

**Resultado Esperado:**
```
✅ Mensagem: "Usuário criado com sucesso"
✅ Modal fecha
✅ Tabela atualiza com novo usuário
```

---

### Passo 2: Verificar dados na tabela

Na tabela de usuários, verificar se a linha do novo usuário aparece:
```
✅ Nome: João Silva
✅ Email: joao@example.com
✅ CPF: 123.456.789-09
```

---

### Passo 3: Editar usuário e verificar formatação

1. Na tabela, clicar no botão "Editar" da linha do João Silva
2. Modal abre com título "Editar Usuário"

**CRÍTICO - Verificar se os campos monetários estão formatados:**
```
✅ Valor Hora:        150,00        (NÃO: 150.00)
✅ Valor Desloc.:     50,50         (NÃO: 50.5)
✅ Valor KM:          3,50          (NÃO: 3.5)
✅ Salário Base:      3.500,00      (NÃO: 3500.00)
```

Se vir números sem formatação (150.00 em vez de 150,00), significa que `.trigger('input')` não funcionou.

---

### Passo 4: Modificar e salvar novamente

1. Alterar "Valor Hora" para: `200,00`
2. Alterar "Valor Desloc." para: `75,25`
3. Clicar em "Salvar"

**Resultado Esperado:**
```
✅ Mensagem: "Usuário atualizado com sucesso"
✅ Modal fecha
✅ Tabela atualiza com novos valores
```

---

### Passo 5: Abrir novamente para verificar persistência

1. Clicar em "Editar" novamente para o mesmo usuário
2. Verificar se os valores atualizados aparecem formatados:
```
✅ Valor Hora:        200,00
✅ Valor Desloc.:     75,25
```

---

## 🔍 Testes Detalhados

### Teste A: Valor com apenas inteiros

**Input:** Digitar `150` no campo "Valor Hora"

**Observar:**
- Enquanto digita: `1` → `1,` → `15,0` → `150,00`
- Campo mostra: `150,00` ✅

**Salvar e verificar:**
```
✅ Salva com sucesso
✅ Ao editar, mostra: 150,00
```

---

### Teste B: Valor com decimais

**Input:** Digitar `50,50` no campo "Valor Deslocamento"

**Observar:**
- Enquanto digita: `5` → `50,` → `50,5` → `50,50`
- Campo mostra: `50,50` ✅

**Salvar e verificar:**
```
✅ Salva com sucesso
✅ Ao editar, mostra: 50,50
```

---

### Teste C: Valor grande com separador de milhares

**Input:** Digitar `3500,00` no campo "Salário Base"

**Observar:**
- Enquanto digita: `3` → `35,00` → `350,0` → `3.500,00`
- Campo mostra: `3.500,00` com ponto separador ✅

**Salvar e verificar:**
```
✅ Salva com sucesso
✅ Ao editar, mostra: 3.500,00 (com ponto)
```

---

### Teste D: Valor com ponto e vírgula (entrada alternativa)

**Input:** Digitar `3.500,50` no campo "Valor KM"

**Observar:**
- Campo aceita: `3.500,50` ✅
- Exibe formatado: `3.500,50`

**Salvar e verificar:**
```
✅ Salva com sucesso como: 3500.50
✅ Ao editar, mostra: 3.500,50
```

---

### Teste E: Campo vazio (permitido)

**Input:** Deixar campo "Valor Hora" vazio

**Observar:**
- Campo fica vazio ✅

**Salvar e verificar:**
```
✅ Salva com sucesso
✅ Ao editar, campo fica vazio
```

---

### Teste F: Visualizar (sem edição)

1. Clicar em "Visualizar" na tabela
2. Modal abre com título "Visualizar Usuário"

**Verificar:**
```
✅ Campos estão desabilitados (não dá para editar)
✅ Valores aparecem formatados: 150,00
✅ Botão "Salvar" não aparece
```

---

## 🔄 Fluxo Completo de Conversão

### Ciclo Frontend → Backend → Banco → Frontend

```
┌─ VISUALIZAR/EDITAR (Frontend) ──────────────────┐
│                                                  │
│  Banco de Dados: 3500.00 (número decimal)      │
│        ↓                                         │
│  JavaScript: formatMoneyValue()                 │
│        ↓                                         │
│  toLocaleString('pt-BR')                        │
│        ↓                                         │
│  Resultado: " 3.500,00" (com espaço)           │
│        ↓                                         │
│  .replace('R$', '').trim()                      │
│        ↓                                         │
│  Campo: "3.500,00"                             │
│        ↓                                         │
│  .trigger('input') [CRITICAL]                   │
│        ↓                                         │
│  jQuery Mask: #.##0,00                          │
│        ↓                                         │
│  Exibição: 3.500,00 ✅                         │
│                                                  │
└──────────────────────────────────────────────────┘

┌─ SALVAR/EDITAR (Frontend → Backend) ────────────┐
│                                                  │
│  Input: "3.500,00" (com máscara)               │
│        ↓                                         │
│  Sanitize: /[^\d,]/g                            │
│        ↓                                         │
│  Result: "3500,00"                              │
│        ↓                                         │
│  Replace: ',' → '.'                             │
│        ↓                                         │
│  Result: "3500.00"                              │
│        ↓                                         │
│  Validation: numeric, min:0 ✅                  │
│        ↓                                         │
│  Banco: 3500.00 ✅                             │
│                                                  │
└──────────────────────────────────────────────────┘
```

---

## 🛠️ Verificação de Console (DevTools)

### Para ver a sanitização acontecendo:

1. Abrir: F12 (DevTools)
2. Ir para aba "Network"
3. Clicar em "Salvar" no formulário
4. Clicar no request "salvar-usuario" (POST)
5. Ir para aba "Request" ou "Payload"

**Esperado (antes da sanitização):**
```json
{
  "txtUsuarioValorHora": "150,00",
  "txtUsuarioValorDesloc": "50,50",
  "txtUsuarioValorKM": "3,50",
  "txtUsuarioSalarioBase": "3.500,00"
}
```

**Esperado (após sanitização):**
```json
{
  "txtUsuarioValorHora": "150.00",
  "txtUsuarioValorDesloc": "50.50",
  "txtUsuarioValorKM": "3.50",
  "txtUsuarioSalarioBase": "3500.00"
}
```

---

## ❌ Se Algo Der Errado

### Erro 1: Valores aparecem sem formatação na edição (150.00 em vez de 150,00)

**Diagnóstico:**
- `.trigger('input')` pode não estar funcionando
- jQuery Mask pode não estar reapplicando

**Solução:**
```bash
# 1. Verificar console:
F12 → Console → Ver se há erros

# 2. Verificar se jQuery Mask está carregado:
F12 → Console → digitar:
$.fn.mask

# 3. Se retornar "undefined", jQuery Mask não está carregado
```

### Erro 2: Valor monetário é rejeitado com erro 422

**Diagnóstico:**
- Sanitização pode estar falhando
- Validação backend pode estar rejeitando formato

**Solução:**
```bash
# Verificar sanitização no console:
F12 → Console → digitar:
const test = "3.500,00";
test.replace(/[^\d,]/g, '').replace(',', '.')
# Deve retornar: "3500.00"
```

### Erro 3: Valores não salvam

**Diagnóstico:**
- Problema na sanitização ou validação
- Erro 422 do backend

**Verificar logs:**
```bash
tail -50 storage/logs/laravel.log
```

Procurar por mensagens de erro como:
```
"O campo deve ser um número."
"txtUsuarioValorHora"
```

---

## 📊 Matriz de Testes

| # | Teste | Input | Esperado | Status |
|---|-------|-------|----------|--------|
| 1 | Inteiro simples | 150 | Salva: 150.00, Edita: 150,00 | ⬜ |
| 2 | Com decimais | 50,50 | Salva: 50.50, Edita: 50,50 | ⬜ |
| 3 | Com milhares | 3.500,00 | Salva: 3500.00, Edita: 3.500,00 | ⬜ |
| 4 | Ponto e vírgula | 3.500,50 | Salva: 3500.50, Edita: 3.500,50 | ⬜ |
| 5 | Campo vazio | (vazio) | Salva: nulo, Edita: vazio | ⬜ |
| 6 | Visualizar modo | - | Sem edição, valor formatado | ⬜ |
| 7 | Editar modo | - | Com edição, valor formatado | ⬜ |
| 8 | Atualizar valor | 200,00 → 250,00 | Novo valor persiste | ⬜ |

**Preenchimento dos testes:**
- ⬜ Não testado
- ✅ Passou
- ❌ Falhou

---

## 🚀 Próximo Passo

Você está pronto para testar! Siga os passos:

1. **Teste Rápido:** Complete os 5 passos iniciais (5 minutos)
2. **Testes Detalhados:** Execute A-F para cobrir casos específicos (10 minutos)
3. **Verificação Console:** Confirme sanitização no DevTools (2 minutos)
4. **Preencha Matriz:** Marque status dos testes na tabela

Se todos os testes passarem com ✅, os campos monetários estão funcionando corretamente! 🎉

---

**Última Atualização:** 30 de Novembro de 2025
**Versão:** 1.0
**Git Commit:** 2830125
