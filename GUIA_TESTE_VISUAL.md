# 👁️ GUIA DE TESTE VISUAL - TOTALIZADOR

**Data**: 2025-11-22
**Propósito**: Teste prático do totalizador com exemplo do usuário
**Tempo estimado**: 5-10 minutos

---

## 📋 PRÉ-REQUISITOS

### 1. Cliente Preparado
```
Nome: [Qualquer cliente]
Valor Hora: 80,00 ✅
KM: 48 ✅
```

### 2. Consultor Preparado
```
Nome: [Qualquer consultor]
Valor Hora: 48,00 ✅
Valor KM: 2,00 ✅
```

---

## 🎯 TESTE 1: CRIAR ORDEM DE SERVIÇO (Como Admin)

### Passo 1: Login Admin
```
URL: http://seu-site.com
Usuário: admin@example.com
Senha: [sua senha]
```
**Resultado esperado**: Dashboard admin carrega ✅

### Passo 2: Navegar para Ordem de Serviço
```
Menu → Ordens de Serviço → Nova
```
**Resultado esperado**: Modal "Adicionar Ordem de Serviço" abre ✅

### Passo 3: Preencher Dados Básicos
```
┌─────────────────────────────────────────┐
│ ORDEM DE SERVIÇO - NOVA                 │
├─────────────────────────────────────────┤
│ Cliente: [cliente com valor_hora=80]    │
│ Consultor: [consultor com vh=48, vkm=2]│
│ Data: [hoje]                            │
│ Número: [auto]                          │
└─────────────────────────────────────────┘
```
**Clique**: Campo cliente → selecionar cliente

### Passo 4: Preencher Horários
```
┌─────────────────────────────────────────┐
│ HORÁRIOS                                 │
├─────────────────────────────────────────┤
│ Início: 08:00                           │
│ Final: 17:00                            │
│ Intervalo: 01:00                        │
├─────────────────────────────────────────┤
│ Total de horas: 8 (calculado) ✅        │
└─────────────────────────────────────────┘
```
**Resultado esperado**: Total de horas mostra 8 ✅

### Passo 5: Marcar Presencial
```
☑️ Ordem Presencial
```
**Resultado esperado**: Checkbox marcado ✅

### Passo 6: Preencher Distância
```
┌─────────────────────────────────────────┐
│ DESLOCAMENTO                            │
├─────────────────────────────────────────┤
│ Distância em KM: 48                     │
│ Horas Deslocamento: 01:00               │
└─────────────────────────────────────────┘
```
**Resultado esperado**: Campos visíveis (presencial ✓) ✅

### Passo 7: Preencher Despesas
```
┌─────────────────────────────────────────┐
│ DESPESAS                                │
├─────────────────────────────────────────┤
│ Tipo: combustível (ou outro)            │
│ Valor: 30,00                            │
└─────────────────────────────────────────┘
```
**Resultado esperado**: Despesa salva ✅

### Passo 8: Descer para Totalizador
```
Scroll down ↓ até ver os totalizadores
```

---

## 🧮 TESTE 2: VALIDAR TOTALIZADOR (Admin)

### ✅ Você deve ver DOIS totalizadores lado a lado

```
╔═══════════════════════════════════════════════════════════════════════════╗
║                                                                           ║
║  ┌─────────────────────────────┐  ┌──────────────────────────────┐     ║
║  │ 🧮 ADMINISTRAÇÃO            │  │ 🧮 VISÃO DO CONSULTOR        │     ║
║  ├─────────────────────────────┤  ├──────────────────────────────┤     ║
║  │                             │  │                              │     ║
║  │ Valor da Hora:              │  │ Valor da Hora:               │     ║
║  │ R$ 80,00 ✅                 │  │ R$ 48,00 ✅                  │     ║
║  │                             │  │                              │     ║
║  │ Valor do KM:                │  │ Valor do KM:                 │     ║
║  │ R$ 2,00 ✅                  │  │ R$ 2,00 ✅                   │     ║
║  │                             │  │                              │     ║
║  ├─────────────────────────────┤  ├──────────────────────────────┤     ║
║  │ Horas Consultor Total:      │  │ Horas Consultor Total:       │     ║
║  │ R$ 640,00 ✅               │  │ R$ 384,00 ✅                │     ║
║  │                             │  │                              │     ║
║  │ Valor KM Total:             │  │ Valor KM Total:              │     ║
║  │ R$ 96,00 ✅                 │  │ R$ 96,00 ✅                  │     ║
║  │                             │  │                              │     ║
║  │ Valor Deslocamento:         │  │ Valor Deslocamento:          │     ║
║  │ R$ 48,00 ✅                 │  │ R$ 48,00 ✅                  │     ║
║  │                             │  │                              │     ║
║  │ Despesas:                   │  │ Despesas:                    │     ║
║  │ R$ 30,00 ✅                 │  │ R$ 30,00 ✅                  │     ║
║  │                             │  │                              │     ║
║  ├─────────────────────────────┤  ├──────────────────────────────┤     ║
║  │ TOTAL: R$ 814,00 ✅         │  │ TOTAL: R$ 558,00 ✅          │     ║
║  └─────────────────────────────┘  └──────────────────────────────┘     ║
║                                                                           ║
╚═══════════════════════════════════════════════════════════════════════════╝
```

### ✅ Validação de Cada Campo

| Campo | Esperado | Status |
|-------|----------|--------|
| Admin - Valor Hora | R$ 80,00 | ☐ |
| Admin - Horas Total | R$ 640,00 (8×80) | ☐ |
| Admin - KM Total | R$ 96,00 (48×2) | ☐ |
| Admin - Deslocamento | R$ 48,00 (1×48) | ☐ |
| Admin - Despesas | R$ 30,00 | ☐ |
| Admin - TOTAL | **R$ 814,00** | ☐ |
| | | |
| Consultor - Valor Hora | R$ 48,00 | ☐ |
| Consultor - Horas Total | R$ 384,00 (8×48) | ☐ |
| Consultor - KM Total | R$ 96,00 (48×2) | ☐ |
| Consultor - Deslocamento | R$ 48,00 (1×48) | ☐ |
| Consultor - Despesas | R$ 30,00 | ☐ |
| Consultor - TOTAL | **R$ 558,00** | ☐ |

### Se todos os valores forem ✅
**Resultado**: TOTALIZADOR ADMIN CORRETO! ✅

---

## 🧮 TESTE 3: VALIDAR COMO CONSULTOR

### Passo 1: Fazer Logout
```
Menu → Logout
```

### Passo 2: Login Como Consultor
```
Usuário: consultor@example.com
Senha: [sua senha]
```

### Passo 3: Navegar para Ordem de Serviço
```
Menu → Ordens de Serviço → Minhas Ordens
```

### Passo 4: Abrir a OS Que Criou
```
Procure pelo ID da OS que criou como admin
Clique para abrir
```

### Passo 5: Ver Totalizador
```
Scroll down até totalizador
```

### ✅ Você deve ver UM totalizador (não dois!)

```
┌──────────────────────────────┐
│ 🧮 TOTALIZADOR - CONSULTOR   │
├──────────────────────────────┤
│                              │
│ Valor da Hora:               │
│ R$ 48,00 ✅                  │
│                              │
│ Valor do KM:                 │
│ R$ 2,00 ✅                   │
│                              │
├──────────────────────────────┤
│ Horas Consultor Total:       │
│ R$ 384,00 ✅                 │
│                              │
│ Valor KM Total:              │
│ R$ 96,00 ✅                  │
│                              │
│ Valor Deslocamento:          │
│ R$ 48,00 ✅                  │
│                              │
│ Despesas:                    │
│ R$ 30,00 ✅                  │
│                              │
├──────────────────────────────┤
│ TOTAL: R$ 558,00 ✅          │
└──────────────────────────────┘
```

### ✅ Validação para Consultor

| Campo | Esperado | Status |
|-------|----------|--------|
| Valor Hora | R$ 48,00 | ☐ |
| Horas Total | R$ 384,00 (8×48) | ☐ |
| KM Total | R$ 96,00 (48×2) | ☐ |
| Deslocamento | R$ 48,00 (1×48) | ☐ |
| Despesas | R$ 30,00 | ☐ |
| **TOTAL** | **R$ 558,00** | ☐ |

### Se todos os valores forem ✅
**Resultado**: TOTALIZADOR CONSULTOR CORRETO! ✅

---

## 🔍 TESTE 4: VALIDAR NÚMEROS (Opcional)

### Você pode fazer contas na calculadora para validar:

**Admin:**
```
R$ 80,00 × 8 horas = R$ 640,00 ✅
R$ 2,00 × 48 km = R$ 96,00 ✅
R$ 48,00 × 1 hora = R$ 48,00 ✅
R$ 640,00 + R$ 96,00 + R$ 48,00 + R$ 30,00 = R$ 814,00 ✅
```

**Consultor:**
```
R$ 48,00 × 8 horas = R$ 384,00 ✅
R$ 2,00 × 48 km = R$ 96,00 ✅
R$ 48,00 × 1 hora = R$ 48,00 ✅
R$ 384,00 + R$ 96,00 + R$ 48,00 + R$ 30,00 = R$ 558,00 ✅
```

---

## ✅ CHECKLIST FINAL

- [ ] Admin vê dois totalizadores lado a lado
- [ ] Admin - Valor Hora mostra R$ 80,00 (do cliente)
- [ ] Admin - Total mostra R$ 814,00
- [ ] Consultor vê um totalizador
- [ ] Consultor - Valor Hora mostra R$ 48,00
- [ ] Consultor - Total mostra R$ 558,00
- [ ] Diferença é exatamente R$ 256,00 (814 - 558)
- [ ] Formatação em Real brasileiro está correta
- [ ] KM aparece porque marcou presencial
- [ ] Deslocamento aparece porque marcou presencial

---

## 🎉 Se Todos os ✅ Estiverem Marcados

### 🎊 PARABÉNS! TOTALIZADOR FUNCIONANDO PERFEITAMENTE!

```
╔════════════════════════════════════════╗
║  ✅ TESTE VISUAL VALIDADO COM SUCESSO ║
║  ✅ PRONTO PARA PRODUÇÃO               ║
║  ✅ TODOS OS CÁLCULOS CORRETOS        ║
╚════════════════════════════════════════╝
```

---

## 🐛 Se Algo Estiver Errado

### Cenário 1: Valores não aparecem
```
→ Verificar se cliente tem valor_hora preenchido
→ Verificar se consultor tem valor_hora e valor_km
→ Abrir Console (F12) e procurar por erros
```

### Cenário 2: Totalizador não aparece
```
→ Verificar se marcou "Presencial"
→ Verificar se preencheu KM ou Deslocamento
→ Verificar console para erros AJAX
```

### Cenário 3: Valores estão trocados
```
→ Admin mostra valores de consultor?
→ Verificar se valor_hora_cliente está sendo retornado
→ Verificar se JavaScript está usando o campo correto
```

---

**Versão**: 1.0
**Data**: 2025-11-22
**Tempo estimado**: 5-10 minutos

*Siga este guia passo a passo para validar o totalizador em sua produção!* ✅
