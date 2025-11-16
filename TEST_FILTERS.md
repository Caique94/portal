# 🧪 Teste do Sistema de Filtros & Exportação

## Debug Checklist

### 1. Verificar Console do Navegador
```
1. Abra o Dashboard Gerencial (http://localhost:8001/dashboard-gerencial)
2. Clique na aba "Filtros & Relatórios"
3. Abra o Console do Navegador (F12 → Console)
4. Você deve ver os logs:
   - "Iniciando loadFilterOptions..."
   - "Response status: 200"
   - "Filter options loaded: {...}"
   - "Filter options populated successfully"
```

### 2. Verificar se os Dropdowns foram Populados
```
1. Na aba "Filtros & Relatórios"
2. Clique no dropdown "Cliente"
3. Verifique se aparecem clientes (não deve estar vazio)
4. Clique no dropdown "Consultor"
5. Verifique se aparecem consultores
6. Clique no dropdown "Status"
7. Verifique se aparecem os 8 status
```

### 3. Testar Filtro Básico
```
1. Preench NENHUM filtro (deixar todos vazios)
2. Clique em "Aplicar Filtros"
3. Você deve ver um resumo com 4 métricas
4. Abaixo uma tabela com todas as ordens de serviço
```

### 4. Testar Filtro por Cliente
```
1. Selecione um cliente no dropdown
2. Clique em "Aplicar Filtros"
3. Tabela deve mostrar apenas ordens daquele cliente
4. Resumo deve mudar refletindo o novo total
```

### 5. Testar Filtro por Data
```
1. Selecione "Data Início: 2025-01-01"
2. Selecione "Data Fim: 2025-12-31"
3. Clique em "Aplicar Filtros"
4. Tabela deve mostrar apenas ordens dentro do período
```

### 6. Testar Filtro por Status
```
1. Selecione um status (ex: "Faturada")
2. Clique em "Aplicar Filtros"
3. Tabela deve mostrar apenas ordens com aquele status
```

### 7. Testar Exportação Excel
```
1. Aplique qualquer filtro
2. Clique em "Exportar em Excel"
3. Um arquivo relatorio_YYYY-MM-DD_HHmmss.xlsx deve ser baixado
4. Abra o arquivo no Excel/LibreOffice
5. Verifique se contém:
   - Título: "PORTAL - RELATÓRIO DE ORDENS DE SERVIÇO"
   - Seção de Filtros Aplicados
   - Seção de RESUMO com 6 métricas
   - Tabela detalhada com as ordens
```

### 8. Testar Exportação PDF
```
1. Aplique qualquer filtro
2. Clique em "Exportar em PDF"
3. Um arquivo relatorio_YYYY-MM-DD_HHmmss.pdf deve ser baixado
4. Abra o arquivo no Adobe Reader
5. Verifique se contém:
   - Título: "PORTAL - RELATÓRIO DE ORDENS DE SERVIÇO"
   - Seção de Filtros Aplicados
   - 6 Boxes com resumo (coloridos)
   - Tabela detalhada com as ordens
```

### 9. Testar Limpar Filtros
```
1. Preencha alguns filtros
2. Clique em "Aplicar Filtros"
3. Clique em "Limpar Filtros"
4. Formulário deve resetar
5. Resumo, tabela e botões de exportação devem sumir (display: none)
```

### 10. Testar Combinação de Filtros
```
1. Selecione:
   - Cliente: Cliente A
   - Status: Faturada
   - Data Início: 2025-11-01
2. Clique em "Aplicar Filtros"
3. Tabela deve mostrar apenas ordens de "Cliente A" com status "Faturada" após 2025-11-01
```

---

## Possíveis Erros & Soluções

### Erro: "Carregando dados..." preso
**Causa:** API não está respondendo
**Solução:**
1. Abra DevTools (F12)
2. Vá para Network tab
3. Clique em "Aplicar Filtros"
4. Procure pela requisição para `/api/reports/filtered`
5. Clique nela e veja a resposta
6. Se erro 401: usuário não autenticado
7. Se erro 500: problema no backend (verificar logs do Laravel)

### Erro: Dropdowns vazios
**Causa:** `loadFilterOptions()` não conseguiu populá-los
**Solução:**
1. Abra Console (F12)
2. Procure pelo log "Filter options loaded:"
3. Se não aparecer: API não respondeu
4. Se aparecer mas dropdowns vazios: dados não foram parseados corretamente

### Erro: Tabela mostra "Nenhuma ordem encontrada"
**Causa:** Nenhum dado corresponde aos filtros
**Solução:**
1. Tente com filtros mais genéricos (sem filtros específicos)
2. Verifique se existem ordens no banco de dados
3. Execute no terminal: `php artisan tinker` → `\App\Models\OrdemServico::count()`

### Erro ao Exportar Excel: "Arquivo não baixado"
**Causa:** Problema na geração do arquivo
**Solução:**
1. Verifique se a pasta `storage/app/exports/` existe e tem permissões de escrita
2. Abra Console → Network → clique na requisição `export-excel`
3. Veja a resposta de erro

---

## Testes com cURL (via terminal)

### Teste 1: Obter opções de filtro
```bash
curl -b "XSRF-TOKEN=<token>; laravel_session=<session>" \
  http://localhost:8001/api/reports/filter-options
```

### Teste 2: Filtrar dados sem filtros
```bash
curl -b "XSRF-TOKEN=<token>; laravel_session=<session>" \
  "http://localhost:8001/api/reports/filtered"
```

### Teste 3: Filtrar por cliente
```bash
curl -b "XSRF-TOKEN=<token>; laravel_session=<session>" \
  "http://localhost:8001/api/reports/filtered?cliente_id=1"
```

---

## Variáveis de Ambiente

Verifique se `.env` está correto:
```
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=pgsql
DB_HOST=192.168.0.166
DB_PORT=5432
DB_DATABASE=portal
DB_USERNAME=postgres
DB_PASSWORD=root
```

---

## Logs do Laravel

Para ver erros em tempo real:
```bash
# Terminal 1: Inicie o servidor
php artisan serve --host=0.0.0.0 --port=8001

# Terminal 2: Monitore os logs
tail -f storage/logs/laravel.log
```

---

## Status Esperados

Os 8 status devem aparecer nos dropdowns:
1. Aberta
2. Aguardando Aprovação
3. Aprovado
4. Contestada
5. Aguardando Faturamento
6. Faturada
7. Aguardando RPS
8. RPS Emitida

---

## Dados de Teste

Se precisar de dados de teste, execute:
```bash
php artisan tinker
```

Depois rode:
```php
\App\Models\OrdemServico::count()  // Deve retornar > 0
\App\Models\Cliente::count()        // Deve retornar > 0
\App\Models\User::where('papel', 'consultor')->count()  // Deve retornar > 0
```

---

**Data de Criação:** 16 de Novembro de 2025
