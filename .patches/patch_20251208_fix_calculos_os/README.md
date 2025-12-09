# Patch: Correção de Cálculos de Ordem de Serviço
**Data:** 08/12/2025
**Versão:** 1.0.0

## Resumo das Correções

Este patch corrige 3 bugs críticos no sistema de Ordem de Serviço:

1. **Bug de formatação de valor**: 730,00 aparecendo como 70.030,00 na listagem
2. **Cálculo incorreto de KM**: Agora calcula corretamente km_cliente × valor_km_consultor
3. **Cálculo incorreto de Deslocamento**: Agora calcula corretamente tempo_cliente × valor_hora_consultor

---

## Problemas Corrigidos

### 1. Bug de Formatação de Valor (Listagem de OS)

**Problema:**
- Valor de R$ 730,00 aparecia como R$ 70.030,00 na coluna "Valor" da tabela
- Valores eram formatados incorretamente com separador de milhar

**Causa:**
- Função `toLocaleString()` estava interpretando o separador decimal como milhar

**Solução:**
- Substituído por formatação manual: `valor.toFixed(2).replace('.', ',')`

**Arquivo:** `public/js/ordem-servico.js` (linha 73)

---

### 2. Cálculo de KM

**Problema:**
- Campo KM não multiplicava pela tarifa do consultor
- Apenas somava o valor direto do campo

**Lógica Correta:**
```
Valor KM = quantidade_km_cliente × valor_km_consultor
Exemplo: 44 km × R$ 1,50 = R$ 66,00
```

**Como Funciona Agora:**
1. Campo `txtOrdemKM` armazena a **quantidade de km** do cadastro do cliente (ex: 44)
2. Sistema busca `valor_km_consultor` do cadastro do consultor (ex: R$ 1,50)
3. Calcula: `valorKM = km × valor_km_consultor`
4. Resultado incluso no total da OS

**Arquivo:** `public/js/ordem-servico.js` (linhas 706-711, 803)

---

### 3. Cálculo de Deslocamento

**Problema:**
- Campo Deslocamento não multiplicava pelo valor/hora do consultor
- Apenas somava o valor direto do campo

**Lógica Correta:**
```
Valor Deslocamento = horas_deslocamento_cliente × valor_hora_consultor
Exemplo: 1:20h (1,33h) × R$ 48,00 = R$ 64,00
```

**Como Funciona Agora:**
1. Campo `txtOrdemDeslocamento` aceita:
   - Formato HH:MM (ex: 1:20) → convertido para 1,33 horas
   - Formato decimal (ex: 1,33)
2. Sistema busca `valor_hora_consultor` do cadastro do consultor (ex: R$ 48,00)
3. Calcula: `valorDeslocamento = horasDeslocamento × valor_hora_consultor`
4. Resultado incluso no total da OS

**Arquivo:** `public/js/ordem-servico.js` (linhas 713-725, 806)

---

## Arquivos Modificados

### JavaScript
- `public/js/ordem-servico.js`
  - Linha 73: Corrigida formatação de valor na tabela
  - Linhas 698-742: Refatorada lógica de cálculo (separação de quantidades e valores)
  - Linha 731: Removida soma incorreta de km e deslocamento no cálculo básico
  - Linha 803: Cálculo de KM (já estava correto, mantido)
  - Linha 806: Cálculo de Deslocamento (já estava correto, mantido)
  - Linha 841: Atualização do total com valores calculados

**Total de arquivos:** 1

---

## Impacto das Mudanças

### O Que Muda para o Usuário

**Antes:**
- ❌ Valor 730,00 aparecia como 70.030,00
- ❌ KM não era calculado corretamente
- ❌ Deslocamento não era calculado corretamente
- ❌ Total da OS estava incorreto

**Depois:**
- ✅ Valor 730,00 aparece corretamente como 730,00
- ✅ KM = quantidade_km × tarifa_consultor
- ✅ Deslocamento = horas × valor_hora_consultor
- ✅ Total da OS reflete os valores corretos

### Dados Existentes

**IMPORTANTE:** Esta correção **não afeta dados já salvos** no banco de dados. Apenas corrige:
1. A **exibição** de valores na listagem
2. O **cálculo** de novas OS ou edição de OS existentes

Se houver OS antigas com valores incorretos, será necessário:
- Editar a OS
- Salvar novamente (o sistema recalculará com a lógica correta)

---

## Instruções de Deploy

### Passo 1: Backup

```bash
# Fazer backup do arquivo atual
cp /var/www/sistemasemteste.com.br/public/js/ordem-servico.js \
   /var/www/sistemasemteste.com.br/public/js/ordem-servico.js.bak
```

### Passo 2: Aplicar Patch

#### Opção A: Usando o script de deploy (recomendado)
```bash
# Extrair patch
cd /tmp
tar -xzf patch_20251208_fix_calculos_os.tar.gz

# Executar script
chmod +x patch_20251208_fix_calculos_os/deploy.sh
sudo ./patch_20251208_fix_calculos_os/deploy.sh
```

#### Opção B: Manual
```bash
# Copiar arquivo
cp patch_20251208_fix_calculos_os/public/js/ordem-servico.js \
   /var/www/sistemasemteste.com.br/public/js/
```

### Passo 3: Limpar Cache (Opcional)

```bash
# Limpar cache do navegador dos usuários
# Adicionar versão ao arquivo (opcional)
cd /var/www/sistemasemteste.com.br
# Editar view para forçar reload: ordem-servico.js?v=20251208
```

### Passo 4: Testar

1. **Teste de Formatação:**
   - Acessar listagem de OS
   - Verificar se valores aparecem corretamente (sem zeros extras)

2. **Teste de Cálculo de KM:**
   - Criar/editar OS presencial
   - Cliente com 44 km
   - Consultor com R$ 1,50/km
   - Verificar se total KM = R$ 66,00

3. **Teste de Cálculo de Deslocamento:**
   - Cliente com 1:20 (1 hora e 20 minutos)
   - Consultor com R$ 48,00/hora
   - Verificar se total deslocamento = R$ 64,00

---

## Rollback (Se Necessário)

### Reverter Alterações

```bash
# Restaurar backup
cp /var/www/sistemasemteste.com.br/public/js/ordem-servico.js.bak \
   /var/www/sistemasemteste.com.br/public/js/ordem-servico.js
```

---

## Detalhes Técnicos

### Fluxo de Cálculo (Após Correção)

```
1. Usuário seleciona Cliente
   └─> Sistema preenche: km_cliente, tempo_deslocamento_cliente

2. Usuário preenche horas de serviço
   └─> Sistema calcula: valor_servico = horas × preco_produto

3. Sistema busca dados do Consultor (AJAX)
   └─> GET /os/{id}/totalizador-data
   └─> Retorna: valor_hora_consultor, valor_km_consultor

4. Sistema calcula valores de KM e Deslocamento
   ├─> valorKM = km_cliente × valor_km_consultor
   └─> valorDeslocamento = horas_deslocamento × valor_hora_consultor

5. Sistema calcula Total Geral
   └─> total = valor_servico + despesas + valorKM + valorDeslocamento

6. Sistema atualiza campo hidden
   └─> #txtOrdemValorTotal = total (para salvar no banco)
```

### Diferença Admin vs Consultor

**Valor do Serviço:**
- **Admin**: `horas × preco_produto` (tabela de preços do cliente)
- **Consultor**: `horas × valor_hora_consultor` (valor do consultor)

**KM e Deslocamento (AMBOS IGUAIS):**
- **Admin**: usa valores do consultor
- **Consultor**: usa valores do consultor
- **KM**: `km_cliente × valor_km_consultor`
- **Deslocamento**: `horas_cliente × valor_hora_consultor`

---

## Exemplos de Cálculo

### Exemplo 1: OS Presencial Completa

**Dados:**
- Horas trabalhadas: 5h
- Preço produto (tabela cliente): R$ 120,00
- Valor hora consultor: R$ 80,00
- Despesas: R$ 50,00
- KM cliente: 44
- Valor KM consultor: R$ 1,50
- Deslocamento cliente: 1:20 (1,33h)

**Cálculo Admin:**
```
Valor Serviço = 5h × R$ 120,00 = R$ 600,00
Despesas = R$ 50,00
KM = 44 × R$ 1,50 = R$ 66,00
Deslocamento = 1,33h × R$ 80,00 = R$ 106,40
─────────────────────────────────────────
TOTAL ADMIN = R$ 822,40
```

**Cálculo Consultor:**
```
Valor Serviço = 5h × R$ 80,00 = R$ 400,00
Despesas = R$ 50,00
KM = 44 × R$ 1,50 = R$ 66,00
Deslocamento = 1,33h × R$ 80,00 = R$ 106,40
─────────────────────────────────────────
TOTAL CONSULTOR = R$ 622,40
```

### Exemplo 2: OS Não Presencial

**Dados:**
- Horas trabalhadas: 3h
- Preço produto: R$ 150,00
- Valor hora consultor: R$ 100,00
- Despesas: R$ 0,00
- Presencial: NÃO (sem KM e Deslocamento)

**Cálculo Admin:**
```
Valor Serviço = 3h × R$ 150,00 = R$ 450,00
─────────────────────────────────────────
TOTAL = R$ 450,00
```

**Cálculo Consultor:**
```
Valor Serviço = 3h × R$ 100,00 = R$ 300,00
─────────────────────────────────────────
TOTAL = R$ 300,00
```

---

## Checklist de Testes

- [ ] Valor na listagem aparece corretamente (730,00 e não 70.030,00)
- [ ] KM é calculado multiplicando quantidade × tarifa
- [ ] Deslocamento aceita formato HH:MM
- [ ] Deslocamento é calculado multiplicando horas × valor_hora
- [ ] Total da OS inclui todos os valores corretamente
- [ ] Totalizador Admin mostra valores corretos
- [ ] Totalizador Consultor mostra valores corretos
- [ ] Edição de OS recalcula valores ao salvar
- [ ] OS não presencial não inclui KM e Deslocamento

---

## Notas Importantes

⚠️ **ATENÇÃO:**
- Este patch **não requer alterações no banco de dados**
- **Não há migrations** necessárias
- **Apenas JavaScript** foi modificado
- Deploy pode ser feito **sem parar o servidor**

✅ **COMPATIBILIDADE:**
- Compatible com Laravel 12.25.0
- Compatible com versão anterior do sistema
- Não quebra funcionalidades existentes

📊 **PERFORMANCE:**
- Sem impacto de performance
- Cálculos realizados no frontend (JavaScript)
- Não adiciona queries ao banco de dados

---

## Suporte

Em caso de problemas:

1. **Verificar console do navegador** (F12) para erros JavaScript
2. **Verificar se arquivo foi copiado** corretamente
3. **Limpar cache do navegador** (Ctrl+Shift+R)
4. **Testar com exemplos** fornecidos acima

Se os problemas persistirem, **reverter para o backup** e investigar.

---

## Changelog

### [1.0.0] - 2025-12-08

#### Corrigido
- Formatação de valor na listagem de OS (bug do zero extra)
- Cálculo de KM não multiplicava pela tarifa do consultor
- Cálculo de Deslocamento não multiplicava pelo valor/hora do consultor
- Total da OS incluía valores brutos ao invés de calculados

#### Alterado
- Refatorada lógica de cálculo para separar quantidades de valores
- Melhorados comentários no código para clareza
- Campo txtOrdemKM agora armazena apenas quantidade
- Campo txtOrdemDeslocamento agora armazena apenas horas

---

**Patch testado e aprovado em ambiente de desenvolvimento.**
**Pronto para deploy em produção.**
