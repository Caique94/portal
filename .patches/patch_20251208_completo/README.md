# Patch Completo - 08/12/2025
**Versão:** 1.0.0
**Data:** 08 de Dezembro de 2025

## Resumo Geral

Este patch contém TODAS as alterações realizadas em 08/12/2025, incluindo:

1. ✅ **Feature: Produto Presencial** - Campo is_presencial vinculado ao produto
2. ✅ **Bug Fix: Formatação de Valor** - Corrigido zero extra na listagem (730,00 → 70.030,00)
3. ✅ **Bug Fix: Cálculo de KM** - Agora multiplica km × tarifa_consultor
4. ✅ **Bug Fix: Cálculo de Deslocamento** - Agora multiplica horas × valor_hora_consultor
5. ✅ **Bug Fix: Campo Presencial Automático** - Preenchido automaticamente baseado no produto
6. ✅ **Bug Fix: Modelo Produto** - Campo is_presencial adicionado ao fillable e casts

---

## Índice
- [Arquivos Modificados](#arquivos-modificados)
- [Feature 1: Produto Presencial](#feature-1-produto-presencial)
- [Bug Fixes](#bug-fixes)
- [Instruções de Deploy](#instruções-de-deploy)
- [Testes](#testes)
- [Rollback](#rollback)

---

## Arquivos Modificados

### Backend (PHP)
1. **app/Http/Controllers/ProdutoController.php**
   - Adicionado suporte a is_presencial

2. **app/Http/Controllers/ProdutoTabelaController.php**
   - Método show() para buscar produto com relacionamentos

3. **app/Models/Produto.php** ⭐ IMPORTANTE
   - Adicionado is_presencial ao $fillable
   - Adicionado $casts para is_presencial e ativo

4. **app/Models/PagamentoUsuario.php**
   - Removido campo 'ativo' inexistente

### Frontend (JavaScript)
5. **public/js/cadastros/produtos.js**
   - Checkbox presencial no formulário
   - Coluna presencial com badges
   - Botões editar/excluir

6. **public/js/cadastros/usuarios.js**
   - Tratamento de CSRF 419 com auto-reload

7. **public/js/ordem-servico.js** ⭐ MUITAS MUDANÇAS
   - Correção de formatação de valor
   - Cálculo correto de KM e Deslocamento
   - Checkbox presencial automático
   - Mostrar/ocultar campos baseado no produto
   - Console.log para debug

### Frontend (Views)
8. **resources/views/cadastros/produtos.blade.php**
   - Checkbox presencial no modal

### Database
9. **database/migrations/2025_12_08_140803_add_is_presencial_to_produto_table.php**
   - Adiciona coluna is_presencial

**Total:** 9 arquivos modificados

---

## Feature 1: Produto Presencial

### O Que Foi Implementado

Agora o campo "Presencial" está vinculado ao **produto** e não à Ordem de Serviço.

### Como Funciona

1. **No Cadastro de Produtos:**
   - Novo checkbox "Presencial"
   - Ao salvar, o campo `is_presencial` é armazenado no banco
   - Na listagem, aparece badge "Sim" ou "Não"

2. **Na Ordem de Serviço:**
   - Ao selecionar um produto, o sistema busca se ele é presencial
   - Checkbox "Presencial" é marcado/desmarcado **automaticamente**
   - Checkbox fica **desabilitado** (usuário não pode alterar)
   - Campos de KM e Deslocamento aparecem/desaparecem automaticamente

3. **Nos Cálculos:**
   - Se produto é presencial: calcula KM e Deslocamento
   - Se produto não é presencial: **não** calcula KM e Deslocamento

### Arquivos Envolvidos
- Migration: `database/migrations/2025_12_08_140803_add_is_presencial_to_produto_table.php`
- Model: `app/Models/Produto.php`
- Controller: `app/Http/Controllers/ProdutoController.php`
- Controller: `app/Http/Controllers/ProdutoTabelaController.php`
- View: `resources/views/cadastros/produtos.blade.php`
- JS: `public/js/cadastros/produtos.js`
- JS: `public/js/ordem-servico.js`

---

## Bug Fixes

### Bug 1: Formatação de Valor (Listagem OS)

**Problema:**
- Valor R$ 730,00 aparecia como R$ 70.030,00

**Causa:**
- `toLocaleString()` interpretava decimal como milhar

**Solução:**
- Substituído por `valor.toFixed(2).replace('.', ',')`

**Arquivo:** `public/js/ordem-servico.js` (linha 73)

---

### Bug 2: Cálculo de KM

**Problema:**
- KM não era multiplicado pela tarifa do consultor

**Lógica Correta:**
```
Valor KM = km_cliente × valor_km_consultor
Exemplo: 44 km × R$ 1,50 = R$ 66,00
```

**Solução:**
- Campo `txtOrdemKM` armazena apenas quantidade
- Cálculo: `valorKM = kmQuantidade × valor_km_consultor`

**Arquivo:** `public/js/ordem-servico.js` (linhas 706-711, 803)

---

### Bug 3: Cálculo de Deslocamento

**Problema:**
- Deslocamento não era multiplicado pelo valor/hora do consultor

**Lógica Correta:**
```
Valor Deslocamento = horas_deslocamento × valor_hora_consultor
Exemplo: 1:20h (1,33h) × R$ 48,00 = R$ 64,00
```

**Solução:**
- Campo aceita HH:MM ou decimal
- Conversão: 1:20 → 1,33 horas
- Cálculo: `valorDeslocamento = horasDeslocamento × valor_hora_consultor`

**Arquivo:** `public/js/ordem-servico.js` (linhas 713-725, 806)

---

### Bug 4: Campo Presencial Não Automático

**Problema:**
- Checkbox presencial não era preenchido automaticamente ao selecionar produto
- Usuário podia alterar manualmente

**Causa:**
- Modelo `Produto` não tinha `is_presencial` no `$fillable`
- API não retornava o campo

**Solução:**
- Adicionado `is_presencial` ao `$fillable` do modelo Produto
- Adicionado `$casts` para garantir tipo boolean
- JavaScript busca is_presencial ao selecionar produto
- Checkbox marcado/desmarcado automaticamente
- Checkbox desabilitado para evitar alteração manual
- Campos KM/Deslocamento aparecem/desaparecem automaticamente

**Arquivos:**
- `app/Models/Produto.php` (linhas 16-27) ⭐ CRÍTICO
- `public/js/ordem-servico.js` (linhas 647-695)

---

### Bug 5: KM e Deslocamento Calculados Sempre

**Problema:**
- KM e Deslocamento eram calculados mesmo quando produto não era presencial

**Solução:**
- KM e Deslocamento só são calculados se checkbox presencial estiver marcado
- Checkbox só é marcado se produto for presencial
- Campos são ocultados quando produto não é presencial
- Totalizador oculta linhas de KM/Deslocamento quando não aplicável

**Arquivo:** `public/js/ordem-servico.js` (linhas 725, 833, 885)

---

## Instruções de Deploy

### Pré-requisitos
- Laravel 12.25.0+
- PHP 8.3.27+
- PostgreSQL
- Acesso SSH ao servidor
- Permissões de root/sudo

### Passo 1: Backup

```bash
# Backup do banco de dados
sudo -u postgres pg_dump portal > /tmp/backup_portal_$(date +%Y%m%d_%H%M%S).sql

# Backup dos arquivos
cd /var/www/sistemasemteste.com.br
tar -czf /tmp/backup_files_$(date +%Y%m%d_%H%M%S).tar.gz \
    app/Http/Controllers/ProdutoController.php \
    app/Http/Controllers/ProdutoTabelaController.php \
    app/Models/Produto.php \
    app/Models/PagamentoUsuario.php \
    public/js/cadastros/produtos.js \
    public/js/cadastros/usuarios.js \
    public/js/ordem-servico.js \
    resources/views/cadastros/produtos.blade.php
```

### Passo 2: Enviar Patch para Servidor

```bash
# Do Windows
scp .patches\patch_20251208_completo.tar.gz root@sistemasemteste.com.br:/tmp/

# No servidor
ssh root@sistemasemteste.com.br
cd /tmp
tar -xzf patch_20251208_completo.tar.gz
```

### Passo 3: Aplicar Arquivos

```bash
cd /tmp/patch_20251208_completo

# Copiar arquivos
cp -v app/Http/Controllers/* /var/www/sistemasemteste.com.br/app/Http/Controllers/
cp -v app/Models/* /var/www/sistemasemteste.com.br/app/Models/
cp -v public/js/cadastros/* /var/www/sistemasemteste.com.br/public/js/cadastros/
cp -v public/js/ordem-servico.js /var/www/sistemasemteste.com.br/public/js/
cp -v resources/views/cadastros/* /var/www/sistemasemteste.com.br/resources/views/cadastros/
cp -v database/migrations/* /var/www/sistemasemteste.com.br/database/migrations/
```

### Passo 4: Aplicar Migration

```bash
cd /var/www/sistemasemteste.com.br
php artisan migrate --force
```

### Passo 5: Verificar Banco

```bash
# Verificar se coluna foi criada
sudo -u postgres psql -d portal -c "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'produto' AND column_name = 'is_presencial';"

# Deve retornar:
#  column_name   | data_type
# ---------------+-----------
#  is_presencial | boolean
```

### Passo 6: Adicionar Rota (SE NECESSÁRIO)

```bash
# Verificar se rota já existe
grep "produto-tabela/{id}" /var/www/sistemasemteste.com.br/routes/web.php

# Se não existir, adicionar:
nano /var/www/sistemasemteste.com.br/routes/web.php
# Adicionar linha:
# Route::get('/produto-tabela/{id}', [ProdutoTabelaController::class, 'show']);
```

### Passo 7: Limpar Caches

```bash
cd /var/www/sistemasemteste.com.br
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Passo 8: Ajustar Permissões

```bash
cd /var/www/sistemasemteste.com.br
chown -R www-data:www-data app/ public/ resources/
chmod -R 755 app/ public/ resources/
```

### Passo 9: Reiniciar Serviços

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

---

## Testes

### Teste 1: Formatação de Valor
1. Acessar listagem de OS
2. Verificar coluna "Valor"
3. **Esperado:** R$ 730,00 (não R$ 70.030,00)

### Teste 2: Produto Presencial - Cadastro
1. Ir em Cadastros > Produtos
2. Criar novo produto
3. Marcar checkbox "Presencial"
4. Salvar
5. **Esperado:** Badge "Sim" na coluna Presencial

### Teste 3: Produto Presencial - OS Automática
1. Criar nova OS
2. Selecionar produto marcado como presencial
3. **Esperado:**
   - Checkbox "Presencial" marcado automaticamente
   - Checkbox desabilitado (não pode alterar)
   - Campos KM e Deslocamento aparecem
4. Selecionar produto NÃO presencial
5. **Esperado:**
   - Checkbox desmarcado automaticamente
   - Campos KM e Deslocamento desaparecem

### Teste 4: Cálculo de KM
1. Criar OS com produto presencial
2. Cliente: 44 km
3. Consultor: R$ 1,50/km
4. **Esperado:** Total KM = R$ 66,00

### Teste 5: Cálculo de Deslocamento
1. Cliente: 1:20 (1 hora 20 min)
2. Consultor: R$ 48,00/hora
3. **Esperado:** Total Deslocamento = R$ 64,00

### Teste 6: Totalizador
1. Verificar totalizador Admin
2. Verificar totalizador Consultor
3. **Esperado:**
   - Linhas KM/Deslocamento só aparecem se produto presencial
   - Valores corretos (multiplicados)

### Teste 7: Produto Não Presencial
1. Criar OS com produto não presencial
2. **Esperado:**
   - Sem campos KM e Deslocamento
   - Total = apenas Serviço + Despesas
   - Totalizador sem linhas KM/Deslocamento

---

## Rollback

### Reverter Migration
```bash
cd /var/www/sistemasemteste.com.br
php artisan migrate:rollback --step=1
```

### Reverter Arquivos
```bash
# Restaurar do backup
cd /tmp
tar -xzf backup_files_YYYYMMDD_HHMMSS.tar.gz -C /var/www/sistemasemteste.com.br/
```

### Reverter Banco
```bash
sudo -u postgres psql portal < /tmp/backup_portal_YYYYMMDD_HHMMSS.sql
```

### Limpar Caches
```bash
cd /var/www/sistemasemteste.com.br
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Reiniciar Serviços
```bash
sudo systemctl restart nginx php8.3-fpm
```

---

## Notas Importantes

⚠️ **CRÍTICO:**
- Modelo `Produto.php` DEVE ter `is_presencial` no `$fillable` e `$casts`
- Sem isso, o campo não é retornado pela API
- Checkbox presencial não funcionará automaticamente

⚠️ **BANCO DE DADOS:**
- Todas alterações devem ser no banco **'portal'** (não portal_dev)
- Verificar conexão antes do deploy

⚠️ **CACHE:**
- Sempre limpar caches após deploy
- Usuários devem limpar cache do navegador (Ctrl+Shift+R)

✅ **COMPATIBILIDADE:**
- Laravel 12.25.0
- PHP 8.3.27+
- PostgreSQL
- Não quebra funcionalidades existentes

---

## Changelog

### [1.0.0] - 2025-12-08

#### Adicionado
- Campo is_presencial vinculado ao produto
- Checkbox presencial no cadastro de produtos
- Coluna presencial na listagem de produtos
- Botões editar/excluir em produtos
- Preenchimento automático do checkbox presencial na OS
- Console.log para debug do campo presencial
- Casts para is_presencial e ativo no modelo Produto

#### Corrigido
- Formatação de valor na listagem (bug do zero extra)
- Cálculo de KM (agora multiplica por tarifa)
- Cálculo de Deslocamento (agora multiplica por valor/hora)
- Checkbox presencial não automático (faltava no $fillable)
- KM e Deslocamento calculados mesmo sem produto presencial
- Modelo PagamentoUsuario com campo 'ativo' inexistente
- CSRF 419 sem feedback ao usuário

#### Alterado
- Lógica de presencial movida para produto
- Checkbox presencial desabilitado na OS (read-only)
- Campos KM/Deslocamento aparecem/desaparecem automaticamente
- Totalizador oculta linhas quando não aplicável

---

**Patch testado e aprovado! Pronto para produção! 🚀**
