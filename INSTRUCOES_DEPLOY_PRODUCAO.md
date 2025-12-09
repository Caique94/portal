# Instruções para Deploy em Produção
## Patch: Produto Presencial Feature

Data: 08/12/2025
Servidor: sistemasemteste.com.br

---

## Passo 1: Preparar Arquivos no Windows

### 1.1 Verificar se o patch foi gerado
```bash
# No terminal do Windows (no diretório do projeto)
dir .patches\patch_20251208_produto_presencial.tar.gz
```

Deve mostrar um arquivo de aproximadamente 27KB.

### 1.2 Verificar se o script de deploy existe
```bash
dir deploy-patch-produto-presencial.sh
```

---

## Passo 2: Conectar no Servidor VPS

### 2.1 Abrir conexão SSH
```bash
# Substitua pelo IP ou domínio do seu servidor
ssh root@sistemasemteste.com.br

# OU
ssh root@SEU_IP_VPS
```

### 2.2 Verificar se a aplicação está rodando
```bash
cd /var/www/sistemasemteste.com.br
ls -la
php artisan --version
```

Deve mostrar: `Laravel Framework 12.25.0`

---

## Passo 3: Enviar Patch para o Servidor

### 3.1 Do Windows, enviar o patch (abra OUTRO terminal Windows, não feche o SSH)
```bash
# Navegar até o diretório do projeto
cd c:\Users\caique\Documents\portal\portal

# Enviar o patch compactado
scp .patches\patch_20251208_produto_presencial.tar.gz root@sistemasemteste.com.br:/tmp/

# Enviar também o script de deploy
scp deploy-patch-produto-presencial.sh root@sistemasemteste.com.br:/tmp/
```

**Nota:** Se `scp` não funcionar no Windows, use o WinSCP ou FileZilla para fazer upload dos arquivos.

### 3.2 No servidor (volte para o terminal SSH), extrair o patch
```bash
cd /tmp
tar -xzf patch_20251208_produto_presencial.tar.gz
ls -la patch_20251208_produto_presencial/
```

Deve listar 14 arquivos do patch.

---

## Passo 4: Executar o Deploy

### 4.1 Dar permissão de execução ao script
```bash
chmod +x /tmp/deploy-patch-produto-presencial.sh
```

### 4.2 Executar o script de deploy
```bash
sudo /tmp/deploy-patch-produto-presencial.sh
```

### 4.3 Acompanhar a execução

O script vai executar automaticamente:
1. ✅ Verificar pré-requisitos
2. ✅ Fazer backup do banco de dados
3. ✅ Fazer backup dos arquivos que serão modificados
4. ✅ Copiar arquivos do patch
5. ✅ Ajustar permissões
6. ✅ Verificar conexão com banco 'portal'
7. ✅ Aplicar migration (adicionar coluna is_presencial)
8. ✅ Verificar se coluna foi criada
9. ✅ Limpar caches do Laravel
10. ✅ Reiniciar nginx e php-fpm

**IMPORTANTE:** O script vai pausar em alguns pontos para você confirmar ações.

---

## Passo 5: Adicionar Rota Manualmente

### 5.1 Abrir o arquivo de rotas
```bash
nano /var/www/sistemasemteste.com.br/routes/web.php
```

### 5.2 Procurar a seção de rotas de produtos

Use `Ctrl+W` para buscar por "produto" no nano.

### 5.3 Adicionar a nova rota

Adicione esta linha junto com as outras rotas de produto:
```php
Route::get('/produto-tabela/{id}', [ProdutoTabelaController::class, 'show']);
```

### 5.4 Salvar e fechar
- `Ctrl+O` para salvar
- `Enter` para confirmar
- `Ctrl+X` para sair

### 5.5 Limpar cache de rotas
```bash
cd /var/www/sistemasemteste.com.br
php artisan route:clear
php artisan route:cache
```

---

## Passo 6: Verificar Deploy

### 6.1 Verificar coluna no banco de dados
```bash
sudo -u postgres psql -d portal -c "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'produto' AND column_name = 'is_presencial';"
```

Deve retornar:
```
 column_name   | data_type
---------------+-----------
 is_presencial | boolean
```

### 6.2 Listar produtos
```bash
sudo -u postgres psql -d portal -c "SELECT id, codigo, is_presencial FROM produto LIMIT 5;"
```

### 6.3 Verificar logs
```bash
tail -f /var/www/sistemasemteste.com.br/storage/logs/laravel.log
```

Deixe este comando rodando e abra outro terminal para os próximos testes.

---

## Passo 7: Testes Funcionais

### 7.1 Acessar a aplicação
Abra no navegador: `https://sistemasemteste.com.br`

### 7.2 Fazer login
Use suas credenciais de administrador.

### 7.3 Testar Cadastro de Produto

1. **Ir para Cadastros > Produtos**
2. **Clicar em "Adicionar"**
3. **Verificar:**
   - Checkbox "Presencial" aparece no formulário?
   - Checkbox está ao lado do switch "Ativo"?
4. **Preencher:**
   - Código: 9999 (código de teste)
   - Descrição: "Teste Produto Presencial"
   - Marcar checkbox "Presencial"
   - Deixar "Ativo" marcado
5. **Salvar**
6. **Verificar na listagem:**
   - Badge "Sim" aparece na coluna "Presencial"?
   - Produto aparece na lista?

### 7.4 Testar Edição de Produto

1. **Clicar no botão de editar (ícone lápis) do produto criado**
2. **Verificar:**
   - Modal abre com título "Editar Produto"?
   - Dados do produto estão preenchidos?
   - Checkbox "Presencial" está marcado?
3. **Desmarcar "Presencial"**
4. **Salvar**
5. **Verificar:**
   - Badge mudou para "Não"?

### 7.5 Testar na Ordem de Serviço

1. **Ir para Ordem de Serviço**
2. **Começar a criar uma nova OS**
3. **No campo "Produto", selecionar o produto de teste criado**
4. **Verificar:**
   - Checkbox "Presencial" foi automaticamente marcado?
   - Checkbox está desabilitado (não pode ser alterado)?
   - Campos de deslocamento/km aparecem?
5. **Selecionar outro produto (não presencial)**
6. **Verificar:**
   - Checkbox "Presencial" foi automaticamente desmarcado?

### 7.6 Testar Exclusão de Produto

1. **Voltar para Cadastros > Produtos**
2. **Clicar no botão de excluir (ícone lixeira) do produto de teste**
3. **Verificar:**
   - Modal de confirmação aparece?
   - Nome do produto está correto na mensagem?
4. **Confirmar exclusão**
5. **Verificar:**
   - Produto foi removido da lista?
   - Toast de sucesso apareceu?

---

## Passo 8: Verificar Logs

### 8.1 No terminal com logs abertos (tail -f), verificar

Se tudo estiver OK, NÃO deve aparecer:
- ❌ Erros 500
- ❌ SQLSTATE errors
- ❌ "column does not exist"
- ❌ Stack traces

Pode aparecer (normal):
- ✅ Logs de info sobre ProdutoController::store
- ✅ Queries SQL

### 8.2 Se houver erros

Se aparecer erros, anote a mensagem completa e:
```bash
# Ver últimas 100 linhas do log
tail -n 100 /var/www/sistemasemteste.com.br/storage/logs/laravel.log

# Buscar por erros específicos
grep -i "error\|exception" /var/www/sistemasemteste.com.br/storage/logs/laravel.log | tail -n 20
```

---

## Passo 9: Limpeza

### 9.1 Remover arquivos temporários
```bash
rm -rf /tmp/patch_20251208_produto_presencial
rm /tmp/patch_20251208_produto_presencial.tar.gz
rm /tmp/deploy-patch-produto-presencial.sh
```

### 9.2 Verificar espaço em disco
```bash
df -h
```

### 9.3 Limpar logs antigos (opcional)
```bash
# Limpar logs com mais de 7 dias
find /var/www/sistemasemteste.com.br/storage/logs/ -name "*.log" -mtime +7 -delete
```

---

## Rollback (Se Necessário)

### Se algo der errado e precisar reverter:

#### 1. Restaurar banco de dados
```bash
# Encontrar backup mais recente
ls -lht /var/www/sistemasemteste.com.br/.backups/db_backup_*.sql | head -n 1

# Restaurar (substitua YYYYMMDD_HHMMSS pela data do backup)
sudo -u postgres psql portal < /var/www/sistemasemteste.com.br/.backups/db_backup_YYYYMMDD_HHMMSS.sql
```

#### 2. Restaurar arquivos
```bash
cd /var/www/sistemasemteste.com.br

# Encontrar backup mais recente
ls -lht .backups/files_backup_*.tar.gz | head -n 1

# Restaurar (substitua YYYYMMDD_HHMMSS pela data do backup)
tar -xzf .backups/files_backup_YYYYMMDD_HHMMSS.tar.gz
```

#### 3. Reverter migration
```bash
cd /var/www/sistemasemteste.com.br
php artisan migrate:rollback --step=1
```

#### 4. Limpar caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

#### 5. Reiniciar serviços
```bash
systemctl restart nginx
systemctl restart php8.3-fpm
```

---

## Checklist Final

Marque cada item após testar:

- [ ] Patch enviado para servidor
- [ ] Script de deploy executado sem erros
- [ ] Backup do banco criado
- [ ] Backup dos arquivos criado
- [ ] Migration aplicada com sucesso
- [ ] Coluna is_presencial existe no banco 'portal'
- [ ] Rota /produto-tabela/{id} adicionada
- [ ] Caches limpos
- [ ] Nginx e PHP-FPM reiniciados
- [ ] Login funciona
- [ ] Cadastro de produto mostra checkbox "Presencial"
- [ ] Edição de produto funciona
- [ ] Exclusão de produto funciona (com confirmação)
- [ ] Listagem mostra badge "Sim"/"Não"
- [ ] OS preenche presencial automaticamente
- [ ] Logs sem erros
- [ ] Arquivos temporários removidos

---

## Contatos de Emergência

Se encontrar problemas:

1. **Verificar logs:**
   ```bash
   tail -f /var/www/sistemasemteste.com.br/storage/logs/laravel.log
   ```

2. **Verificar status dos serviços:**
   ```bash
   systemctl status nginx
   systemctl status php8.3-fpm
   systemctl status postgresql
   ```

3. **Testar conectividade com banco:**
   ```bash
   sudo -u postgres psql -d portal -c "SELECT version();"
   ```

---

## Notas Importantes

⚠️ **CRÍTICO:**
- Todo o deploy deve ser feito no banco **'portal'**, não 'portal_dev'
- Sempre fazer backup antes de qualquer alteração
- Testar cada funcionalidade após o deploy

✅ **SEGURANÇA:**
- Backups são criados automaticamente pelo script
- Arquivos temporários devem ser removidos após deploy
- Verificar permissões dos arquivos

🎯 **PERFORMANCE:**
- Caches são automaticamente recriados
- Não é necessário reiniciar o servidor inteiro
- Deploy pode ser feito durante horário de produção (rápido)

---

**Boa sorte com o deploy! 🚀**
