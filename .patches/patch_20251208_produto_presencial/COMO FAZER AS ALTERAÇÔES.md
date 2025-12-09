# Patch: Produto Presencial Feature
**Data:** 08/12/2025
**Banco de Dados:** portal
**Laravel Version:** 12.25.0
**PHP Version:** 8.3.27

## Resumo das Alterações

Este patch implementa a funcionalidade de vincular o campo "presencial" ao cadastro de produtos, removendo a opção manual na geração de Ordem de Serviço (OS). Quando um produto é selecionado na OS, o sistema automaticamente determina se é presencial baseado no cadastro do produto.

### Principais Mudanças

1. **Nova coluna no banco de dados:** `is_presencial` (BOOLEAN) na tabela `produto`
2. **Cadastro de Produtos:** Adicionado checkbox "Presencial" no formulário
3. **Listagem de Produtos:** Nova coluna "Presencial" com badges visuais
4. **CRUD Completo:** Botões de editar/excluir adicionados à tabela de produtos
5. **Ordem de Serviço:** Campo presencial automaticamente preenchido ao selecionar produto
6. **Correções:** Modelo PagamentoUsuario e tratamento de CSRF token expirado

## Arquivos Modificados

### Backend (PHP)

#### Controllers
- `app/Http/Controllers/ProdutoController.php`
  - Adicionado suporte à coluna `is_presencial` nos métodos list(), active_list() e store()
  - Implementado logging para debug
  - Validação e normalização do campo is_presencial

- `app/Http/Controllers/ProdutoTabelaController.php`
  - Adicionado método show() para retornar dados do produto com relacionamentos
  - Necessário para buscar is_presencial ao selecionar produto na OS

#### Models
- `app/Models/PagamentoUsuario.php`
  - Removido campo 'ativo' do $fillable (coluna não existe na tabela)
  - Removido $casts para 'ativo'

### Frontend

#### Views
- `resources/views/cadastros/produtos.blade.php`
  - Adicionado checkbox "Presencial" no formulário modal
  - Campo posicionado ao lado do checkbox "Ativo"

#### JavaScript
- `public/js/cadastros/produtos.js`
  - Nova coluna "Presencial" no DataTable com badges (Sim/Não)
  - Nova coluna "Ações" com botões editar/excluir
  - Handler para editar produto (preenche modal com dados)
  - Handler para excluir produto (confirmação com SweetAlert)
  - Payload de salvamento inclui is_presencial
  - Atualização do título do modal (Adicionar/Editar)

- `public/js/ordem-servico.js`
  - AJAX call ao selecionar produto para buscar is_presencial
  - Checkbox presencial desabilitado e preenchido automaticamente
  - Fallback caso produto não tenha o campo

- `public/js/cadastros/usuarios.js`
  - Tratamento de erro 419 (CSRF token expirado)
  - Auto-reload da página após 2 segundos com notificação

### Database

#### Migration
- `database/migrations/2025_12_08_140803_add_is_presencial_to_produto_table.php`
  - Adiciona coluna `is_presencial BOOLEAN DEFAULT FALSE`
  - Método up() e down() para reversão

#### SQL Scripts
- `sql/01_add_is_presencial_column.sql`
  - Script manual para adicionar coluna com verificação
  - Útil para ambientes onde migration não pode rodar

- `sql/02_verify_produto_columns.sql`
  - Script de verificação das colunas da tabela produto
  - Lista produtos com status presencial

### Routes
- `routes/web.php`
  - Adicionada rota: `GET /produto-tabela/{id}`

### Helper Scripts
- `scripts/check_produto_columns.php`
  - Verifica conexão com banco de dados
  - Lista todas as colunas da tabela produto
  - Verifica existência da coluna is_presencial

- `scripts/fix_portal_db.php`
  - Força conexão com banco 'portal'
  - Verifica e cria coluna is_presencial se não existir
  - Lista todos os produtos com status presencial

## Instruções de Deploy

### 1. Backup do Banco de Dados
```bash
pg_dump -U seu_usuario -d portal > backup_portal_antes_patch_$(date +%Y%m%d).sql
```

### 2. Aplicar Arquivos do Patch

#### Opção A: Copiar arquivos manualmente
```bash
# A partir do diretório do patch
cp -r app/ /caminho/do/projeto/
cp -r resources/ /caminho/do/projeto/
cp -r public/ /caminho/do/projeto/
cp -r database/ /caminho/do/projeto/
```

#### Opção B: Usar rsync
```bash
rsync -av --exclude='.git' ./ /caminho/do/projeto/
```

### 3. Aplicar Migration

#### Opção A: Laravel Migration (recomendado)
```bash
cd /caminho/do/projeto
php artisan migrate
```

#### Opção B: SQL Manual (caso migration não rode)
```bash
# Conectar ao PostgreSQL
psql -U seu_usuario -d portal -f sql/01_add_is_presencial_column.sql
```

### 4. Verificar Banco de Dados

Usando script PHP:
```bash
php scripts/check_produto_columns.php
```

Ou usando SQL:
```bash
psql -U seu_usuario -d portal -f sql/02_verify_produto_columns.sql
```

### 5. Limpar Caches do Laravel
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 6. Reiniciar Servidor

#### Servidor de Desenvolvimento
```bash
# Se estiver usando php artisan serve
# Ctrl+C e reiniciar
php artisan serve
```

#### Nginx/Apache (Produção)
```bash
# Nginx
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm

# Apache
sudo systemctl restart apache2
```

### 7. Testar Funcionalidade

1. **Cadastro de Produto:**
   - Acessar menu Cadastros > Produtos
   - Clicar em "Adicionar"
   - Verificar checkbox "Presencial" no formulário
   - Criar produto de teste marcando como presencial
   - Verificar badge "Sim" na listagem

2. **Edição de Produto:**
   - Clicar no botão de editar (lápis)
   - Verificar dados preenchidos no modal
   - Alterar status presencial
   - Salvar e verificar atualização

3. **Ordem de Serviço:**
   - Acessar geração de OS
   - Selecionar produto marcado como presencial
   - Verificar checkbox "Presencial" automaticamente marcado e desabilitado
   - Confirmar cálculo de deslocamento/km funciona

4. **Exclusão de Produto:**
   - Clicar no botão de excluir (lixeira)
   - Confirmar modal de confirmação aparece
   - Cancelar ou confirmar exclusão

## Problemas Conhecidos e Soluções

### Problema 1: Coluna is_presencial não existe
**Sintoma:** Erro ao salvar produto ou erro 500
**Causa:** Migration não rodou no banco correto
**Solução:**
```bash
# Verificar banco conectado
php scripts/check_produto_columns.php

# Se não existir, rodar fix
php scripts/fix_portal_db.php

# Reiniciar servidor
```

### Problema 2: CSRF Token Mismatch (419)
**Sintoma:** Erro 419 ao salvar após página aberta por muito tempo
**Causa:** Token de sessão expirado
**Solução:** Agora tratado automaticamente - página recarrega com notificação

### Problema 3: Dados não salvando
**Sintoma:** Checkbox marcado mas não salva no banco
**Causa:** Laravel conectando no banco errado (portal_dev vs portal)
**Solução:**
```bash
# Limpar config cache
php artisan config:clear
DB_CONNECTION=pgsql DB_DATABASE=portal

# Verificar .env
grep DB_DATABASE .env

# Deve mostrar: DB_DATABASE=portal
```

### Problema 4: Erro ao salvar usuário (500)
**Sintoma:** `SQLSTATE[42703]: Undefined column: 7 ERRO: coluna "ativo" da relação "pagamento_usuario" não existe`
**Causa:** Modelo PagamentoUsuario tinha campo 'ativo' que não existe na tabela
**Solução:** Já corrigido neste patch - campo removido do modelo

## Rollback (Reverter Alterações)

### 1. Reverter Migration
```bash
php artisan migrate:rollback --step=1
```

### 2. Reverter SQL Manual
```sql
-- Conectar ao banco portal
psql -U seu_usuario -d portal

-- Remover coluna
ALTER TABLE produto DROP COLUMN IF EXISTS is_presencial;
```

### 3. Restaurar Backup
```bash
# Restaurar backup completo
psql -U seu_usuario -d portal < backup_portal_antes_patch_YYYYMMDD.sql
```

### 4. Reverter Arquivos
```bash
# Usar git para reverter (se estiver versionado)
git checkout HEAD -- app/Http/Controllers/ProdutoController.php
git checkout HEAD -- public/js/cadastros/produtos.js
# ... outros arquivos
```

## Checklist de Deploy

- [ ] Backup do banco de dados criado
- [ ] Arquivos copiados para servidor
- [ ] Migration aplicada ou SQL executado
- [ ] Coluna is_presencial existe no banco 'portal' (verificado)
- [ ] Caches do Laravel limpos
- [ ] Servidor reiniciado
- [ ] Teste de cadastro de produto OK
- [ ] Teste de edição de produto OK
- [ ] Teste de exclusão de produto OK
- [ ] Teste de OS com produto presencial OK
- [ ] Badge "Presencial" aparecendo na listagem OK
- [ ] Logs verificados (sem erros)

## Logs e Debugging

### Verificar Logs do Laravel
```bash
tail -f storage/logs/laravel.log | grep -i "produto\|presencial"
```

### Logs Relevantes
Os seguintes logs foram adicionados para debug:
- `ProdutoController::store - Dados recebidos`
- `ProdutoController::store - Dados que serão salvos`

### Console do Browser
O JavaScript inclui console.logs:
- `is_presencial checkbox encontrado`
- `is_presencial está marcado`
- `Payload sendo enviado`

## Informações de Contato e Suporte

Em caso de problemas durante o deploy:
1. Verificar logs do Laravel: `storage/logs/laravel.log`
2. Verificar logs do PostgreSQL: `/var/log/postgresql/`
3. Testar scripts de verificação: `php scripts/check_produto_columns.php`
4. Verificar conexão com banco correto (portal, não portal_dev)

## Notas Importantes

⚠️ **CRÍTICO:** Todos os comandos devem ser executados no banco de dados **'portal'**, não 'portal_dev'

⚠️ **IMPORTANTE:** Após aplicar patch, sempre limpar caches do Laravel e reiniciar servidor

✅ **TESTADO:** Patch testado e confirmado funcionando em ambiente de desenvolvimento

## Changelog

### [1.0.0] - 2025-12-08

#### Adicionado
- Coluna `is_presencial` na tabela `produto`
- Checkbox presencial no formulário de produto
- Coluna presencial na listagem de produtos com badges
- Botões de editar/excluir na tabela de produtos
- Auto-preenchimento de presencial na OS baseado no produto
- Tratamento automático de CSRF token expirado
- Scripts de verificação e correção do banco de dados
- Logs para debugging

#### Corrigido
- Modelo PagamentoUsuario tentando salvar campo 'ativo' inexistente
- Problema de conexão com banco errado (portal_dev vs portal)
- CSRF token mismatch sem feedback ao usuário
- Botões de edição não aparecendo na listagem

#### Alterado
- Lógica de presencial movida de OS para cadastro de produto
- Checkbox presencial na OS agora é read-only quando produto selecionado
