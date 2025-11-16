# 🚀 Quick Start: Filtros & Exportação

## ⚡ 30 Segundos para Começar

### 1. Inicie o Servidor (se não estiver rodando)
```bash
php artisan serve --host=0.0.0.0 --port=8001
```

### 2. Abra o Navegador
```
http://localhost:8001/login
```

### 3. Faça Login
```
Email: admin@example.com
Senha: 123
```

### 4. Navegue para Dashboard
```
Menu Lateral → Dashboard Gerencial
```

### 5. Clique na Aba "Filtros & Relatórios"
Você verá:
- Form com 5 filtros
- Dropdown populados (Cliente, Consultor, Status)
- Botões de ação

---

## 🧪 Teste Rápido (1 minuto)

### Sem Filtros
1. Clique em "Aplicar Filtros" (sem preencher nada)
2. Você deve ver:
   - Resumo: 47 Ordens | R$ 14.587,80 | R$ 14.347,80 Faturado | R$ 240,00 Pendente
   - Tabela com todas as 47 ordens

### Com Filtro de Cliente
1. Selecione um cliente no dropdown
2. Clique em "Aplicar Filtros"
3. Tabela atualiza mostrando apenas ordens daquele cliente

### Exportar Excel
1. Clique em "Exportar em Excel"
2. Arquivo `relatorio_2025-11-16_123456.xlsx` é baixado
3. Abra no Excel/LibreOffice para verificar

### Exportar PDF
1. Clique em "Exportar em PDF"
2. Arquivo `relatorio_2025-11-16_123456.pdf` é baixado
3. Abra no Adobe Reader para verificar

---

## 📊 Dados Disponíveis para Teste

**Status Atuais no Banco:**
- Ordem ID 1: Status 5 (Aguardando Faturamento) - R$ 50.00
- Ordem ID 2: Status 6 (Faturada) - R$ 1.500.00
- ... 47 ordens no total

**Clientes Disponíveis:** 5
**Consultores Disponíveis:** 4

---

## 🐛 Se Algo Não Funcionar

### Passo 1: Abra DevTools (F12)
```
Clique em F12 ou CTRL+SHIFT+I
```

### Passo 2: Vá para Console
```
DevTools → Console tab
```

### Passo 3: Tente aplicar filtro
```
Você deve ver logs como:
✓ "Iniciando loadFilterOptions..."
✓ "Response status: 200"
✓ "Filter options loaded: {...}"
✓ "Applying filters: {...}"
✓ "Filtered data received: {...}"
```

### Passo 4: Se não funcionar
```
Procure por mensagens de erro (em vermelho)
Tome nota do erro exato
```

---

## 🔍 Verificar Logs do Laravel

**Terminal:**
```bash
# Em outro terminal, rode:
tail -f storage/logs/laravel.log
```

Depois faça alguma ação no Dashboard. Você verá logs como:
```
[2025-11-16 22:58:16] local.DEBUG: GET /api/reports/filter-options
[2025-11-16 22:58:17] local.DEBUG: GET /api/reports/filtered?cliente_id=1
```

---

## 🎯 Checklist Funcional

- [ ] Página carrega sem erros
- [ ] Dropdowns estão preenchidos (não vazio)
- [ ] Clique em "Aplicar Filtros" (vazio) - exibe 47 ordens
- [ ] Seleciona um cliente - exibe apenas ordens daquele cliente
- [ ] Clica em "Exportar em Excel" - arquivo baixa
- [ ] Clica em "Exportar em PDF" - arquivo baixa
- [ ] Clica em "Limpar Filtros" - form reseta
- [ ] Console não tem erros em vermelho

---

## 📁 Arquivos Importantes

Se precisar ler a documentação:

1. **FILTER_EXPORT_FEATURE.md** - Documentação técnica completa
2. **TEST_FILTERS.md** - Guia de testes detalhado
3. **IMPLEMENTATION_SUMMARY.md** - Resumo do que foi implementado

---

## 🔑 Endpoints da API (para teste via Postman/curl)

### 1. Obter opções de filtro
```
GET /api/reports/filter-options
Authorization: Bearer <token>

Response:
{
  "clientes": [...],
  "consultores": [...],
  "status": [...]
}
```

### 2. Filtrar dados
```
GET /api/reports/filtered?cliente_id=1&data_inicio=2025-01-01
Authorization: Bearer <token>

Response:
{
  "data": [...],
  "summary": {...}
}
```

### 3. Exportar Excel
```
POST /api/reports/export-excel
Body: {
  "_token": "...",
  "cliente_id": "1",
  "status": "6"
}

Response: Download do arquivo .xlsx
```

### 4. Exportar PDF
```
POST /api/reports/export-pdf
Body: {
  "_token": "...",
  "cliente_id": "1"
}

Response: Download do arquivo .pdf
```

---

## 💡 Dicas

1. **Combinação de Filtros** - Você pode usar vários filtros de uma vez
2. **Exportar sem Filtros** - Deixar tudo vazio = exportar todas as ordens
3. **Arquivo Temporário** - Arquivos são deletados após download
4. **Recarga de Página** - Se tiver problema, faça F5

---

## ❓ FAQ Rápido

**P: Os dropdowns estão vazios?**
R: Abra Console (F12) e procure por "Filter options loaded". Se não aparecer, a API não respondeu.

**P: Tabela não atualiza?**
R: Clique em "Aplicar Filtros" novamente. Se mesmo assim não funcionar, verifique Console.

**P: Arquivo não baixa?**
R: Verifique se bloqueador de pop-ups está ativo. Desative temporariamente.

**P: Qual é o tempo de carregamento?**
R: Deve ser < 1 segundo com 50 registros. Se demorar, verifique conexão de rede.

---

## 🎓 Próximo Passo

Depois que tudo estiver funcionando:
1. Leia **FILTER_EXPORT_FEATURE.md** para entender a implementação
2. Leia **TEST_FILTERS.md** para testes avançados
3. Customize conforme necessário

---

**Versão:** 1.0
**Data:** 16 de Novembro de 2025
**Status:** ✅ Pronto para Produção

