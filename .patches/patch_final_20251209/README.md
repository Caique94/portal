# Patch Final - Portal
**Data:** 09 de Dezembro de 2025
**Versão:** 1.0.0

## Resumo

Este patch consolida todas as alterações dos commits:
- `121834a` - Patch completo (bugs + feature presencial)
- `4506aa9` - Ocultar KM/Deslocamento
- `e6e7bce` - Ocultar checkbox Presencial (JS)
- `bd4cbd4` - Ocultar seção Presencial (View)
- `842560a` - Corrigir formatação de valor

## Arquivos Modificados

### Backend (5 arquivos)
1. `app/Http/Controllers/ProdutoController.php`
2. `app/Http/Controllers/ProdutoTabelaController.php`
3. `app/Models/Produto.php` ⭐ CRÍTICO
4. `app/Models/PagamentoUsuario.php`
5. `database/migrations/2025_12_08_140803_add_is_presencial_to_produto_table.php`

### Frontend - JavaScript (3 arquivos)
6. `public/js/cadastros/produtos.js`
7. `public/js/cadastros/usuarios.js`
8. `public/js/ordem-servico.js`

### Frontend - Views (2 arquivos)
9. `resources/views/cadastros/produtos.blade.php`
10. `resources/views/ordem-servico.blade.php`

### Rotas (1 arquivo)
11. `routes/web.php`

**Total:** 11 arquivos

## Alterações Implementadas

### 1. Feature: Produto Presencial
- Campo `is_presencial` vinculado ao produto (não à OS)
- Checkbox automático baseado no produto selecionado
- Campos KM/Deslocamento calculados apenas para produtos presenciais
- Migration: adiciona coluna `is_presencial` na tabela `produto`

### 2. Bug Fixes
- ✅ Formatação de valor (730,00 não aparece mais como 70.030,00)
- ✅ Cálculo de KM (multiplica km × tarifa do consultor)
- ✅ Cálculo de Deslocamento (multiplica horas × valor/hora)
- ✅ Campos Presencial, KM e Deslocamento ocultos na interface
- ✅ Modelo Produto com fillable e casts corretos

### 3. Campos Ocultos
- Campo Presencial: oculto (determinado automaticamente pelo produto)
- Campo KM: oculto (mas funciona internamente)
- Campo Deslocamento: oculto (mas funciona internamente)

## Instruções de Deploy

### Pré-requisitos
- Laravel 12.25.0+
- PHP 8.3.27+
- PostgreSQL
- Git configurado no servidor

### Deploy via Git (Recomendado)

```bash
# 1. Conectar ao servidor
ssh root@sistemasemteste.com.br

# 2. Ir para o diretório da aplicação
cd /var/www/sistemasemteste.com.br

# 3. Fazer pull das alterações
git pull origin main

# 4. Executar migration
php artisan migrate

# 5. Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 6. Recriar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Reiniciar serviços
systemctl restart nginx php8.3-fpm
```

### Deploy Manual (Alternativo)

```bash
# 1. Extrair o ZIP no servidor
unzip patch_final_20251209.zip -d /tmp/

# 2. Copiar arquivos
cd /tmp/patch_final_20251209
cp -r app/ /var/www/sistemasemteste.com.br/
cp -r database/ /var/www/sistemasemteste.com.br/
cp -r public/ /var/www/sistemasemteste.com.br/
cp -r resources/ /var/www/sistemasemteste.com.br/
cp routes/web.php /var/www/sistemasemteste.com.br/routes/

# 3. Executar migration
cd /var/www/sistemasemteste.com.br
php artisan migrate

# 4. Ajustar permissões
chown -R www-data:www-data app/ public/ resources/
chmod -R 755 app/ public/ resources/

# 5. Limpar e recriar caches
php artisan config:clear && php artisan cache:clear
php artisan config:cache && php artisan route:cache

# 6. Reiniciar serviços
systemctl restart nginx php8.3-fpm
```

## Verificações Pós-Deploy

### 1. Verificar Migration
```sql
-- Conectar no PostgreSQL
sudo -u postgres psql -d portal

-- Verificar coluna is_presencial
SELECT column_name, data_type
FROM information_schema.columns
WHERE table_name = 'produto'
AND column_name = 'is_presencial';
```

Deve retornar:
```
 column_name   | data_type
---------------+-----------
 is_presencial | boolean
```

### 2. Verificar Rota
```bash
php artisan route:list | grep produto-tabela
```

Deve mostrar:
```
GET       produto-tabela/{id} ... ProdutoTabelaController@show
```

## Testes Obrigatórios

### Teste 1: Formatação de Valor
1. Acessar listagem de OS
2. Verificar coluna "Valor"
3. ✅ Esperado: R$ 830,00 (não R$ 80.030,00)

### Teste 2: Produto Presencial - Cadastro
1. Ir em Cadastros > Produtos
2. Criar novo produto
3. Marcar checkbox "Presencial"
4. Salvar
5. ✅ Esperado: Badge "Sim" na coluna Presencial

### Teste 3: Produto Presencial - OS Automática
1. Criar nova OS
2. Selecionar produto presencial
3. ✅ Esperado: Campos KM/Deslocamento invisíveis mas funcionais
4. Selecionar produto NÃO presencial
5. ✅ Esperado: KM/Deslocamento não calculados

### Teste 4: Cálculo de KM
1. Criar OS com produto presencial
2. Cliente: 44 km
3. Consultor: R$ 1,50/km
4. ✅ Esperado: Total KM = R$ 66,00

### Teste 5: Cálculo de Deslocamento
1. Cliente: 1:20 (1 hora 20 min)
2. Consultor: R$ 48,00/hora
3. ✅ Esperado: Total = R$ 64,00

### Teste 6: Interface Limpa
1. Abrir modal de OS
2. ✅ Esperado: Campos Presencial, KM e Deslocamento não visíveis
3. Salvar OS com produto presencial
4. ✅ Esperado: Cálculos corretos nos totalizadores

## Rollback (Se Necessário)

```bash
# 1. Reverter migration
php artisan migrate:rollback --step=1

# 2. Reverter código via Git
git reset --hard <commit-anterior>
git push origin main --force

# 3. Limpar caches
php artisan config:clear && php artisan cache:clear

# 4. Reiniciar serviços
systemctl restart nginx php8.3-fpm
```

## Notas Importantes

⚠️ **CRÍTICO:**
- Modelo `Produto.php` DEVE ter `is_presencial` no `$fillable` e `$casts`
- Sem isso, o campo não é retornado pela API e funcionalidade não funciona

⚠️ **BANCO DE DADOS:**
- Todas alterações no banco **'portal'** (não portal_dev)
- Migration cria coluna `is_presencial BOOLEAN DEFAULT FALSE`

⚠️ **CACHE:**
- Sempre limpar cache após deploy
- Usuários devem limpar cache do navegador (Ctrl+Shift+R)

✅ **COMPATIBILIDADE:**
- Laravel 12.25.0
- PHP 8.3.27+
- PostgreSQL
- Não quebra funcionalidades existentes

## Suporte

Em caso de problemas:
1. Verificar logs: `tail -f storage/logs/laravel.log`
2. Verificar logs do nginx: `tail -f /var/log/nginx/error.log`
3. Verificar logs do PHP: `tail -f /var/log/php8.3-fpm.log`

---

**Patch testado e aprovado! 🚀**
