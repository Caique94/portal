# 🚀 Guia de Deploy - sistemasemteste.com.br (Hostinger)

## 📋 Informações do Projeto

- **Domínio:** sistemasemteste.com.br
- **Hospedagem:** Hostinger
- **Framework:** Laravel 12.0
- **Banco de Dados:** PostgreSQL (local) → MySQL (Hostinger)
- **Servidor Web:** Apache

---

## ⚠️ IMPORTANTE: Migração PostgreSQL → MySQL

Sua aplicação atual usa PostgreSQL, mas a Hostinger oferece MySQL por padrão. Você tem 2 opções:

### Opção 1: Usar MySQL da Hostinger (RECOMENDADO - Mais Simples)
- ✅ Incluído no plano
- ✅ Gerenciado pelo hPanel
- ❌ Precisa migrar/adaptar algumas queries

### Opção 2: PostgreSQL Externo (Render, Supabase)
- ✅ Mantém PostgreSQL
- ✅ Sem mudanças no código
- ❌ Custo adicional ($7-10/mês)
- ❌ Mais complexo de configurar

**Recomendação:** Começar com MySQL (Opção 1) para simplificar.

---

## 🗂️ PASSO 1: Preparar Banco de Dados MySQL no hPanel

### 1.1. Acessar hPanel
1. Acesse: [hpanel.hostinger.com](https://hpanel.hostinger.com)
2. Login com suas credenciais
3. Selecione o plano onde está `sistemasemteste.com.br`

### 1.2. Criar Banco de Dados MySQL
1. No hPanel, vá em: **Databases → MySQL Databases**
2. Clique em **"Create New Database"**
3. Preencha:
   - **Database Name:** `portal` (será automaticamente prefixado, ex: `u123456789_portal`)
   - **Username:** `portal_user` (será prefixado, ex: `u123456789_portal_user`)
   - **Password:** Gere uma senha forte (mínimo 16 caracteres)
4. Clique em **"Create"**

### 1.3. Anotar Credenciais
```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_portal  ← Copie o nome completo
DB_USERNAME=u123456789_portal_user  ← Copie o usuário completo
DB_PASSWORD=sua_senha_forte_aqui
```

**⚠️ GUARDE ESTAS CREDENCIAIS - Você precisará delas no passo 5.**

---

## 📧 PASSO 2: Criar Conta de Email (Opcional, mas recomendado)

### 2.1. Criar Email no hPanel
1. No hPanel, vá em: **Emails → Email Accounts**
2. Clique em **"Create Email Account"**
3. Preencha:
   - **Email:** `noreply@sistemasemteste.com.br`
   - **Password:** Gere uma senha forte
4. Clique em **"Create"**

### 2.2. Configurações SMTP
```
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@sistemasemteste.com.br
MAIL_PASSWORD=senha_gerada_no_passo_anterior
MAIL_ENCRYPTION=tls
```

**Alternativa:** Você pode continuar usando o Gmail já configurado:
```
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=caique.soares.silva@gmail.com
MAIL_PASSWORD="akhz wtpe uyya rujc"
```

---

## 🔒 PASSO 3: Ativar SSL (HTTPS) - CRÍTICO

### 3.1. Ativar SSL Let's Encrypt (Gratuito)
1. No hPanel, vá em: **Security → SSL**
2. Encontre `sistemasemteste.com.br`
3. Clique em **"Install SSL"** (Let's Encrypt - Free)
4. Aguarde 5-10 minutos para ativação

### 3.2. Forçar HTTPS
1. Ainda em **Security → SSL**
2. Ative **"Force HTTPS"** para `sistemasemteste.com.br`

**✅ Após ativação, seu site estará em:** `https://sistemasemteste.com.br`

---

## 📁 PASSO 4: Upload dos Arquivos via FTP/SFTP

### 4.1. Credenciais FTP
No hPanel, vá em **Files → FTP Accounts** para ver suas credenciais:
```
Host: ftp.sistemasemteste.com.br
Username: u123456789
Password: [sua senha de acesso]
Port: 21 (FTP) ou 22 (SFTP recomendado)
```

### 4.2. Conectar via FileZilla/WinSCP
1. Baixe [FileZilla](https://filezilla-project.org/) ou [WinSCP](https://winscp.net/)
2. Configure a conexão:
   - **Protocolo:** SFTP (SSH File Transfer Protocol)
   - **Host:** ftp.sistemasemteste.com.br
   - **Porta:** 22
   - **Usuário:** u123456789
   - **Senha:** [sua senha]

### 4.3. Estrutura de Pastas na Hostinger

**⚠️ IMPORTANTE: Estrutura correta!**

```
/home/u123456789/
├── public_html/          ← Conteúdo da pasta "public" do Laravel
│   ├── index.php         ← Arquivo principal (modificado)
│   ├── .htaccess
│   ├── assets/
│   ├── build/
│   ├── css/
│   ├── js/
│   ├── plugins/
│   └── ...
│
└── domains/
    └── sistemasemteste.com.br/
        └── portal/       ← Todo o resto do Laravel
            ├── app/
            ├── bootstrap/
            ├── config/
            ├── database/
            ├── resources/
            ├── routes/
            ├── storage/
            ├── vendor/
            ├── .env      ← Criar aqui!
            ├── artisan
            └── composer.json
```

### 4.4. Passos de Upload

#### 4.4.1. Criar Pasta `portal`
1. Navegue até: `/home/u123456789/domains/sistemasemteste.com.br/`
2. Crie a pasta: `portal`

#### 4.4.2. Upload do Laravel (EXCETO `public/`)
Faça upload de TODAS as pastas **EXCETO** `public/` para:
```
/home/u123456789/domains/sistemasemteste.com.br/portal/
```

Pastas para fazer upload:
- ✅ `app/`
- ✅ `bootstrap/`
- ✅ `config/`
- ✅ `database/`
- ✅ `resources/`
- ✅ `routes/`
- ✅ `storage/`
- ✅ `vendor/` (se já rodou `composer install` localmente)
- ✅ `artisan`
- ✅ `composer.json`
- ✅ `composer.lock`
- ✅ `package.json`
- ❌ **NÃO** faça upload de `public/` (ainda)
- ❌ **NÃO** faça upload de `.env` (você vai criar um novo)

#### 4.4.3. Upload do `public/`
Faça upload de **TODO O CONTEÚDO** da pasta `public/` para:
```
/home/u123456789/public_html/
```

Arquivos para fazer upload:
- ✅ `index.php` (você vai modificar depois)
- ✅ `.htaccess`
- ✅ `assets/`
- ✅ `build/`
- ✅ `css/`
- ✅ `js/`
- ✅ `plugins/`
- ✅ Todos os outros arquivos/pastas dentro de `public/`

---

## 🔧 PASSO 5: Configurar `.env` de Produção

### 5.1. Criar `.env` via File Manager

1. No hPanel, vá em **Files → File Manager**
2. Navegue até: `/domains/sistemasemteste.com.br/portal/`
3. Clique em **"New File"**
4. Nome do arquivo: `.env`
5. Abra o arquivo para editar

### 5.2. Conteúdo do `.env`

**Copie o arquivo `.env.hostinger` que eu criei, e preencha os valores em branco:**

```env
# ==========================
# APLICAÇÃO - HOSTINGER
# ==========================
APP_NAME="Portal"
APP_ENV=production
APP_KEY=base64:aUaAumEbAaUBPDkXTpeDGA9pAK2lop/q679QbIsquSg=
APP_DEBUG=false  # ⚠️ NUNCA true em produção
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://sistemasemteste.com.br

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

# ==========================
# LOGGING - PRODUÇÃO
# ==========================
LOG_CHANNEL=daily
LOG_STACK=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning
LOG_DAILY_DAYS=30

# ==========================
# BANCO DE DADOS - MYSQL
# ==========================
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_portal  ← Preencher com o nome do PASSO 1
DB_USERNAME=u123456789_portal_user  ← Preencher com o usuário do PASSO 1
DB_PASSWORD=sua_senha_forte_aqui  ← Preencher com a senha do PASSO 1

# ==========================
# SESSÃO - PRODUÇÃO (HTTPS)
# ==========================
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

# ==========================
# EMAIL
# ==========================
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@sistemasemteste.com.br  ← Ou use Gmail
MAIL_PASSWORD=senha_do_email  ← Preencher
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@sistemasemteste.com.br"
MAIL_FROM_NAME="Portal"

# ==========================
# SEGURANÇA - PRODUÇÃO
# ==========================
SANCTUM_TOKEN_EXPIRATION=60
SANCTUM_STATEFUL_DOMAINS=sistemasemteste.com.br,www.sistemasemteste.com.br

FRONTEND_URL=https://sistemasemteste.com.br

# ==========================
# SERVIÇOS EXTERNOS
# ==========================
VIACEP_URL=https://viacep.com.br/ws
```

### 5.3. Salvar e Verificar Permissões
```bash
# Permissões do .env (via SSH ou File Manager)
chmod 600 .env
```

---

## 🔗 PASSO 6: Ajustar `index.php`

O `index.php` precisa apontar para a pasta `portal` onde está o Laravel.

### 6.1. Editar `public_html/index.php`

1. No File Manager, abra: `/public_html/index.php`
2. Localize estas linhas:

**ANTES (original):**
```php
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
```

**DEPOIS (modificado):**
```php
if (file_exists($maintenance = __DIR__.'/../domains/sistemasemteste.com.br/portal/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../domains/sistemasemteste.com.br/portal/vendor/autoload.php';

(require_once __DIR__.'/../domains/sistemasemteste.com.br/portal/bootstrap/app.php')
```

### 6.2. Conteúdo Completo do `index.php`

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../domains/sistemasemteste.com.br/portal/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../domains/sistemasemteste.com.br/portal/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../domains/sistemasemteste.com.br/portal/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

---

## 🛡️ PASSO 7: Configurar `.htaccess`

### 7.1. Copiar `.htaccess` Otimizado

Use o arquivo `.htaccess.hostinger` que eu criei:

1. Copie o conteúdo de `.htaccess.hostinger`
2. Cole em `/public_html/.htaccess`
3. **IMPORTANTE:** Descomente as linhas de HTTPS:

```apache
# Descomente estas linhas:
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 🔐 PASSO 8: Ajustar Permissões (Via SSH)

### 8.1. Conectar via SSH

**Credenciais SSH:**
```
Host: ssh.sistemasemteste.com.br (ou IP fornecido no hPanel)
Port: 22
User: u123456789
Password: [sua senha]
```

**Conectar:**
```bash
ssh u123456789@ssh.sistemasemteste.com.br
```

### 8.2. Executar Comandos de Permissões

```bash
cd ~/domains/sistemasemteste.com.br/portal

# Permissões do storage e bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Proprietário (ajuste conforme seu usuário)
chown -R u123456789:u123456789 storage
chown -R u123456789:u123456789 bootstrap/cache

# Permissões do .env (apenas leitura do proprietário)
chmod 600 .env
```

---

## 🗄️ PASSO 9: Rodar Migrations

### 9.1. Via SSH (Recomendado)

```bash
cd ~/domains/sistemasemteste.com.br/portal

# Verificar conexão com o banco
php artisan migrate:status

# Rodar migrations
php artisan migrate --force

# Importar estados e cidades (opcional, se tiver o SQL)
# Fazer upload do estados_cidades_brasil.sql via FTP
# Depois importar via phpMyAdmin ou:
mysql -u u123456789_portal_user -p u123456789_portal < ~/estados_cidades_brasil.sql
```

### 9.2. Via phpMyAdmin (Se não tiver SSH)

1. No hPanel, vá em **Databases → phpMyAdmin**
2. Selecione o banco `u123456789_portal`
3. Clique em **"Import"**
4. Faça upload do SQL completo (migrations + dados)

**⚠️ IMPORTANTE: Ajustar SQL de PostgreSQL para MySQL**

Algumas mudanças necessárias:
- `SERIAL` → `AUTO_INCREMENT`
- `TEXT` → `LONGTEXT` (se necessário)
- `jsonb` → `json`
- Remover `::` casts de PostgreSQL

---

## ⚡ PASSO 10: Otimizar Aplicação

### 10.1. Instalar Dependências de Produção

```bash
cd ~/domains/sistemasemteste.com.br/portal

# Composer (apenas produção, otimizado)
composer install --optimize-autoloader --no-dev
```

### 10.2. Cachear Configurações

```bash
# Limpar caches antigos
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Cachear para produção (otimização)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10.3. Compilar Assets (Localmente antes do upload)

**No seu computador:**
```bash
npm install
npm run build
```

**Depois fazer upload da pasta `public/build/` para `public_html/build/`**

---

## ✅ PASSO 11: Testar em Produção

### 11.1. Testes Básicos

1. ✅ Acesse: `https://sistemasemteste.com.br`
   - Deve mostrar a página de login
   - Sem erros 500
   - HTTPS ativo (cadeado verde)

2. ✅ Teste o login
   - Fazer login com usuário existente
   - Verificar se redireciona corretamente

3. ✅ Verificar ícones
   - Bootstrap Icons devem carregar (não quadrados)
   - Menu lateral deve estar correto

4. ✅ Teste CRUD
   - Criar usuário
   - Editar usuário
   - Listar usuários

5. ✅ Teste CEP (ViaCEP)
   - Preencher CEP e verificar auto-preenchimento

### 11.2. Testes de Segurança

**Verificar Headers:**
```bash
curl -I https://sistemasemteste.com.br
```

**Deve retornar:**
```
X-XSS-Protection: 1; mode=block
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Strict-Transport-Security: max-age=31536000
Content-Security-Policy: default-src 'self'; ...
```

**Testar Rate Limiting:**
```bash
# Fazer 6 tentativas de login rápidas
for i in {1..6}; do
  curl -X POST https://sistemasemteste.com.br/login \
    -d "email=test@test.com&password=123"
done
# 6ª requisição deve retornar HTTP 429
```

### 11.3. Verificar Logs

**Via SSH:**
```bash
tail -50 ~/domains/sistemasemteste.com.br/portal/storage/logs/laravel.log
```

**Via File Manager:**
- Navegar até: `/domains/sistemasemteste.com.br/portal/storage/logs/`
- Download do `laravel.log`
- Verificar se não há dados sensíveis (senhas, tokens)

---

## 🐛 Problemas Comuns e Soluções

### ❌ Erro 500 - Internal Server Error

**Causa:** Permissões incorretas ou `.env` faltando

**Solução:**
```bash
chmod -R 775 storage bootstrap/cache
php artisan config:clear
```

Verificar log: `/domains/sistemasemteste.com.br/portal/storage/logs/laravel.log`

### ❌ Página em branco

**Causa:** Erro no `index.php` ou caminho incorreto

**Solução:**
- Verificar caminho em `public_html/index.php`
- Verificar se `vendor/` existe em `/portal/`

### ❌ CSS/JS não carregam

**Causa:** Assets não compilados ou caminho errado

**Solução:**
```bash
# Localmente
npm run build

# Upload do public/build/ para public_html/build/
```

### ❌ Erro de conexão com banco de dados

**Causa:** Credenciais incorretas no `.env`

**Solução:**
- Verificar `DB_HOST=localhost` (não usar 127.0.0.1)
- Verificar credenciais no hPanel → Databases

### ❌ Ícones aparecem como quadrados

**Causa:** CSP bloqueando fonts do CDN

**Solução:** Já está corrigido no `SecurityHeaders.php`:
```php
"font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com data:",
```

---

## 📊 Monitoramento e Manutenção

### Backup Automático

**Via hPanel:**
- **Backups → Weekly Backups** (disponível em planos Premium+)

**Via Cron Job:**
```bash
# Adicionar em cPanel → Cron Jobs ou via SSH:
0 2 * * * cd ~/domains/sistemasemteste.com.br/portal && mysqldump -u u123456789_portal_user -pSENHA u123456789_portal > ~/backups/portal_$(date +\%Y\%m\%d).sql
```

### Monitorar Logs

**Logs do Laravel:**
```bash
tail -f ~/domains/sistemasemteste.com.br/portal/storage/logs/laravel.log
```

**Logs do Apache:**
- Via hPanel → **Logs**

---

## 🔒 Checklist Final de Segurança

- [ ] `APP_DEBUG=false` no `.env`
- [ ] `APP_ENV=production`
- [ ] `LOG_LEVEL=warning`
- [ ] HTTPS ativo (SSL)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] Senha do banco forte (16+ caracteres)
- [ ] Permissões corretas (775 storage, 600 .env)
- [ ] `.env` não acessível via browser
- [ ] Caches gerados (config, route, view)
- [ ] Testar login
- [ ] Testar rate limiting
- [ ] Verificar logs (sem dados sensíveis)
- [ ] Backup configurado

---

## 📞 Suporte

**Hostinger:**
- Chat 24/7: [hpanel.hostinger.com](https://hpanel.hostinger.com)
- Base de conhecimento: [support.hostinger.com](https://support.hostinger.com)

**Laravel:**
- Documentação: [laravel.com/docs](https://laravel.com/docs)

**Este Projeto:**
- Consulte: `SECURITY.md`
- Consulte: `CODE_REVIEW_CHECKLIST.md`
- Consulte: `DEPLOY_HOSTINGER.md` (guia genérico)

---

**Domínio:** sistemasemteste.com.br
**Data de criação:** 2025-12-04
**Versão:** 1.0

🚀 **Boa sorte com o deploy!**
