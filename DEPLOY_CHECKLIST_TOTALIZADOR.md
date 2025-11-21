# ✅ DEPLOY CHECKLIST - Totalizador Personalizado

**Commit**: 8e11b2e
**Status**: ✅ Pronto para Deploy
**Data**: 2025-11-21

---

## 📋 Pré-Deploy

### Requisitos de Sistema
- [ ] Laravel 11+ instalado
- [ ] PHP 8.1+ ativo
- [ ] jQuery 3.x+ carregado
- [ ] Bootstrap 5+ disponível
- [ ] Banco de dados acessível

### Dados Necessários
- [ ] Usuários (admin, consultor) têm `valor_hora` preenchido
- [ ] Usuários (admin, consultor) têm `valor_km` preenchido
- [ ] Clientes têm campo `km` preenchido (pode ser NULL)
- [ ] Produtos têm `preco_produto` preenchido

### Verificações
- [ ] Git repository em estado limpo (`git status` sem alterações)
- [ ] Backup dos 4 arquivos realizado
- [ ] Commit 8e11b2e está em main
- [ ] Nenhuma migration pendente

---

## 🚀 Deploy Steps

### 1. Atualizar Arquivos (5 min)

```bash
# Copiar 4 arquivos modificados
cp routes/web.php routes/web.php.backup
cp app/Http/Controllers/OrdemServicoController.php app/Http/Controllers/OrdemServicoController.php.backup
cp resources/views/ordem-servico.blade.php resources/views/ordem-servico.blade.php.backup
cp public/js/ordem-servico.js public/js/ordem-servico.js.backup

# Arquivos já estão no commit 8e11b2e
# Se necessário fazer merge:
git merge origin/main  # ou seu branch
```

### 2. Limpar Cache (3 min)

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:cache
```

### 3. Validar Alterações (2 min)

```bash
# Verificar que os 4 arquivos foram atualizados
git diff HEAD~1 -- \
  routes/web.php \
  app/Http/Controllers/OrdemServicoController.php \
  resources/views/ordem-servico.blade.php \
  public/js/ordem-servico.js

# Deve mostrar 4 arquivos modificados, 164 linhas adicionadas
```

### 4. Testes (opcional, 5 min)

```bash
# Se tiver suite de testes
php artisan test

# Ou validar sintaxe PHP
php -l app/Http/Controllers/OrdemServicoController.php
php -l routes/web.php
```

### 5. Monitorar (contínuo)

```bash
# Em terminal separado, monitorar logs
tail -f storage/logs/laravel.log | grep -i "totalizador\|ordem.*servico\|erro"
```

---

## 🧪 Testes Pós-Deploy

### Teste 1: Admin Criando OS (5 min)

```
1. Fazer login como Admin
2. Ir para "Ordem de Serviço"
3. Clicar "Nova OS"
4. Preencher:
   - Cliente: [selecionar]
   - Produto: [selecionar]
   - Preço Produto: R$ 500,00
   - Horas: 2
   - Despesas: R$ 50,00
   - Check "Presencial"
   - KM: 30
   - Deslocamento: 01:00
5. Verificar Totalizador mostra:
   ✓ Valor Serviço = 500 × 2 = R$ 1.000,00
   ✓ Despesas = R$ 50,00
   ✓ KM = 30 × valor_km
   ✓ Deslocamento = 1.0 × valor_hora
   ✓ Total correto
6. Status: ✅ PASSA
```

### Teste 2: Consultor Vendo seu OS (5 min)

```
1. Fazer login como Consultor
2. Ir para "Ordem de Serviço"
3. Clicar para editar seu próprio OS
4. Verificar Totalizador mostra:
   ✓ Valor Serviço = horas × valor_hora (NÃO preco)
   ✓ Despesas = mesmo valor
   ✓ KM = 30 × valor_km (mesmo)
   ✓ Deslocamento = horas × valor_hora (mesmo)
   ✓ Total DIFERENTE do Admin
5. Status: ✅ PASSA
```

### Teste 3: Formato HH:MM (3 min)

```
1. No campo "Deslocamento", preencher: 02:30
2. Verificar que calcula como 2.5 horas (não 2 ou 3)
3. Total deve refletir 2.5h × valor_hora
4. Testar variações: 00:15, 01:45, 03:00
5. Status: ✅ PASSA
```

### Teste 4: Permissões (3 min)

```
1. Fazer login como Consultor A
2. Tentar editar OS de Consultor B via URL
3. Verificar se retorna erro ou bloqueia acesso
4. Verificar console do navegador (F12) por erros
5. Status: ✅ PASSA
```

### Teste 5: Validação de Moeda (2 min)

```
1. Qualquer valor no totalizador deve ser R$ X,XX
2. Não deve aparecer R$ X.XX (ponto em vez de vírgula)
3. Testar com valores: 1.23, 123.45, 1234.56
4. Status: ✅ PASSA
```

### Teste 6: Campos Vazios (2 min)

```
1. Deixar KM vazio (ou zero)
2. Deixar Deslocamento vazio
3. Verificar que linhas de KM/Deslocamento se ocultam
4. Preencher KM = 10
5. Verificar que linha de KM aparece
6. Status: ✅ PASSA
```

---

## 📊 Matriz de Aceitação

| Critério | Admin | Consultor | Status |
|----------|-------|-----------|--------|
| Endpoint retorna dados | ✅ | ✅ | Testar |
| Valor Serviço = preco × horas | ✅ | ❌ | Testar |
| Valor Serviço = horas × hora | ❌ | ✅ | Testar |
| KM cálculo correto | ✅ | ✅ | Testar |
| Deslocamento em HH:MM | ✅ | ✅ | Testar |
| Moeda em R$ X,XX | ✅ | ✅ | Testar |
| Permissões bloqueiam | ✅ | ✅ | Testar |
| Console sem erros | ✅ | ✅ | Testar |
| Logs sem erros | ✅ | ✅ | Testar |

---

## 🔄 Rollback (Se Necessário)

### Opção 1: Restaurar de Backup

```bash
# Se fez backup antes
cp routes/web.php.backup routes/web.php
cp app/Http/Controllers/OrdemServicoController.php.backup app/Http/Controllers/OrdemServicoController.php
cp resources/views/ordem-servico.blade.php.backup resources/views/ordem-servico.blade.php
cp public/js/ordem-servico.js.backup public/js/ordem-servico.js

# Limpar cache
php artisan cache:clear
php artisan view:clear
```

### Opção 2: Git Rollback

```bash
# Se quiser reverter commit
git revert 8e11b2e

# OU restaurar arquivo específico
git checkout HEAD~1 -- app/Http/Controllers/OrdemServicoController.php

# Limpar cache
php artisan cache:clear
```

---

## 📝 Logs Para Monitorar

### Esperado em storage/logs/laravel.log

✅ Ao criar/editar OS:
```
[2025-11-21 20:00:00] local.INFO: GET /os/123/totalizador-data - Success
```

❌ Erros esperados (se algo der errado):
```
[2025-11-21 20:00:01] local.ERROR: Erro ao buscar dados do totalizador
```

### Console do Navegador (F12)

✅ Esperado:
```
Network tab:
- GET /os/123/totalizador-data → 200 OK

Console:
- Sem erros vermelhos
```

❌ Não esperado:
```
- GET /os/123/totalizador-data → 500 ERROR
- Uncaught SyntaxError em ordem-servico.js
- CORS error
```

---

## 🆘 Troubleshooting

### Problema: "Erro ao carregar dados do totalizador"

**Causa provável**: Endpoint retornando erro

**Solução**:
```bash
# 1. Verificar se rota foi adicionada
grep "totalizador-data" routes/web.php

# 2. Verificar método no controller
grep "getTotalizadorData" app/Http/Controllers/OrdemServicoController.php

# 3. Limpar cache
php artisan route:cache
php artisan cache:clear

# 4. Validar relacionamentos
# Verificar se OS tem consultor_id e consultor_id tem valor_hora
```

### Problema: "Permissão negada" para consultor acessar próprio OS

**Causa provável**: `papel` do usuário não é 'consultor'

**Solução**:
```bash
# Verificar papel do usuário
select id, name, papel, valor_hora from users where id = ?;

# Debe ser 'consultor' não 'user'
# Verificar User model tem papel field
```

### Problema: Deslocamento calculando errado

**Causa provável**: Campo não em formato HH:MM

**Solução**:
```javascript
// No console (F12):
console.log($('#txtOrdemDeslocamento').val());
// Deve ser "01:30" ou "00:45"
// Não "1.5" ou "90" (minutos)
```

### Problema: Valores mostrando com ponto em vez de vírgula

**Causa provável**: JavaScript não formatando corretamente

**Solução**:
```javascript
// Verificar função formatarMoeda() em ordem-servico.js
// Deve ter: .replace('.', ',')

// Testar no console:
formatarMoeda(123.45)
// Deve retornar: "R$ 123,45"
```

---

## ✅ Checklist Final

Antes de considerar deploy concluído:

- [ ] Todos 4 arquivos foram atualizados
- [ ] Cache foi limpo (view, config, route)
- [ ] Teste 1 (Admin) passou ✅
- [ ] Teste 2 (Consultor) passou ✅
- [ ] Teste 3 (HH:MM) passou ✅
- [ ] Teste 4 (Permissões) passou ✅
- [ ] Teste 5 (Moeda) passou ✅
- [ ] Teste 6 (Campos) passou ✅
- [ ] Nenhum erro no console do navegador
- [ ] Nenhum erro em storage/logs/laravel.log
- [ ] Usuários relatam satisfação

---

## 📞 Suporte Pós-Deploy

### Em caso de dúvidas:

1. **Verificar Console** (F12 → Console)
   - Tem erros JS?
   - Qual é o erro?

2. **Verificar Network** (F12 → Network)
   - Chamada AJAX retorna 200 ou erro?
   - Qual é a resposta?

3. **Verificar Logs**
   - `tail -f storage/logs/laravel.log`
   - Tem mensagens de erro?

4. **Dados do Usuário**
   - Usuario tem `valor_hora` e `valor_km` preenchidos?
   - Consultor tem `papel = 'consultor'`?

---

## 📊 Estatísticas

```
Tempo de Deploy: ~15-20 minutos
  - Atualizar arquivos: 5 min
  - Limpar cache: 3 min
  - Testes: 15 min
  - Monitoramento: contínuo

Arquivos Atualizados: 4
Novo Endpoint: 1 (/os/{id}/totalizador-data)
Linhas de Código: +164 -38 = +126 líquidas
```

---

## 🎉 Status

```
✅ IMPLEMENTAÇÃO: Completa
✅ TESTES: Prontos para executar
✅ DOCUMENTAÇÃO: Completa
✅ ROLLBACK: Planejado
✅ PRONTO: Para Deploy Imediato
```

---

## 📚 Referências

- Implementação: `TOTALIZADOR_PERSONALIZADO_PATCH.md`
- Resumo: `RESUMO_IMPLEMENTACAO_TOTALIZADOR.md`
- Commit: `8e11b2e`

---

**Versão**: 1.0
**Data**: 2025-11-21
**Status**: ✅ Pronto para Deploy

*Siga este checklist para garantir deploy suave e sem problemas.*
