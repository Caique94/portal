# 🚀 Guia de Deploy - Hostinger

## 📋 Pré-requisitos

### 1. Conta Hostinger
- ✅ Plano de hospedagem ativo (Business ou superior recomendado)
- ✅ Acesso ao hPanel (painel de controle)
- ✅ Token de API da Hostinger

### 2. Domínio
- Domínio próprio configurado na Hostinger
- SSL/TLS ativo (Let's Encrypt gratuito via hPanel)

### 3. Banco de Dados PostgreSQL
- ⚠️ **IMPORTANTE**: A Hostinger oferece MySQL por padrão
- Para PostgreSQL, você precisará de um VPS ou Cloud Hosting
- Alternativamente, use um serviço externo (Render, Supabase, Railway)

---

## 🔧 Opção 1: Deploy com MySQL (Hostinger Shared Hosting)

Se você não tem VPS, precisará migrar de PostgreSQL para MySQL:

### Passo 1: Adaptar para MySQL

**1.1. Atualizar `.env` de produção:**
```env
DB_CONNECTION=mysql
DB_HOST=localhost  # ou IP do servidor MySQL da Hostinger
DB_PORT=3306
DB_DATABASE=u123456789_portal
DB_USERNAME=u123456789_user
DB_PASSWORD=senha_fornecida_pela_hostinger
```

**1.2. Ajustar migrations (se necessário):**
Algumas migrations podem precisar de ajustes para MySQL. Diferenças principais:
- PostgreSQL usa `serial`, MySQL usa `AUTO_INCREMENT`
- PostgreSQL usa `TEXT`, MySQL pode usar `LONGTEXT`
- Tipo `jsonb` não existe no MySQL (use `json`)

### Passo 2: Criar Banco de Dados no hPanel

1. Acesse **hPanel → Databases → MySQL Databases**
2. Clique em **Create New Database**
3. Nome: `u123456789_portal` (prefixo automático da Hostinger)
4. Anote: nome do banco, usuário e senha

### Passo 3: Upload dos Arquivos

**Via FTP/SFTP (FileZilla, WinSCP):**
```
Servidor: ftp.seudominio.com.br
Usuário: u123456789
Porta: 21 (FTP) ou 22 (SFTP)
```

**Estrutura de arquivos na Hostinger:**
```
/home/u123456789/
├── public_html/          # ← NÃO colocar aqui!
└── portal/               # ← Criar esta pasta
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/           # ← Este conteúdo vai para public_html
    ├── resources/
    ├── routes/
    ├── storage/
    └── vendor/
```

**Passos:**
1. Crie a pasta `portal` em `/home/u123456789/`
2. Faça upload de TODOS os arquivos (exceto `public/`) para `/home/u123456789/portal/`
3. Faça upload do conteúdo de `public/` para `/home/u123456789/public_html/`

### Passo 4: Configurar o `.htaccess`

**Arquivo: `/home/u123456789/public_html/.htaccess`**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redirecionar para HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Remover www (opcional)
    RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
    RewriteRule ^(.*)$ https://%1/$1 [R=301,L]

    # Laravel
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>

# Proteger arquivos sensíveis
<FilesMatch "\.(env|log|sql|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Passo 5: Ajustar `index.php`

**Arquivo: `/home/u123456789/public_html/index.php`**

Edite as linhas que carregam o autoload e bootstrap:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../portal/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../portal/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../portal/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

### Passo 6: Configurar Permissões

Via SSH ou File Manager no hPanel:

```bash
cd /home/u123456789/portal

# Permissões do storage e bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Proprietário (ajustar conforme o usuário do servidor)
chown -R u123456789:u123456789 storage
chown -R u123456789:u123456789 bootstrap/cache
```

### Passo 7: Configurar `.env` de Produção

**Arquivo: `/home/u123456789/portal/.env`**

```env
# ==========================
# APLICAÇÃO - PRODUÇÃO
# ==========================
APP_NAME="Portal"
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI  # Gerar com: php artisan key:generate
APP_DEBUG=false  # ⚠️ CRÍTICO - Nunca true em produção
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://seudominio.com.br

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# ==========================
# LOGGING - PRODUÇÃO
# ==========================
LOG_CHANNEL=daily
LOG_STACK=daily
LOG_LEVEL=warning  # Apenas warnings e erros
LOG_DAILY_DAYS=30

# ==========================
# BANCO DE DADOS - MySQL
# ==========================
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_portal
DB_USERNAME=u123456789_user
DB_PASSWORD=senha_gerada_pela_hostinger

# ==========================
# SESSÃO - PRODUÇÃO
# ==========================
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true  # HTTPS
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
MAIL_USERNAME=noreply@seudominio.com.br
MAIL_PASSWORD=senha_email_hostinger
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@seudominio.com.br"
MAIL_FROM_NAME="Portal"

# ==========================
# SEGURANÇA - PRODUÇÃO
# ==========================
SANCTUM_TOKEN_EXPIRATION=60
SANCTUM_STATEFUL_DOMAINS=seudominio.com.br,www.seudominio.com.br

# CORS
FRONTEND_URL=https://seudominio.com.br

# ==========================
# SERVIÇOS EXTERNOS
# ==========================
VIACEP_URL=https://viacep.com.br/ws
```

### Passo 8: Executar Migrations via SSH

Conecte via SSH (se disponível no seu plano):

```bash
cd /home/u123456789/portal

# Gerar APP_KEY (se não gerou localmente)
php artisan key:generate

# Executar migrations
php artisan migrate --force

# Importar estados e cidades (upload do SQL via phpMyAdmin)
# Ou via linha de comando:
mysql -u u123456789_user -p u123456789_portal < estados_cidades_brasil.sql

# Cachear configurações (otimização)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Otimizar autoload
composer install --optimize-autoloader --no-dev
```

**⚠️ Se não tiver SSH:**
- Execute migrations localmente em um banco MySQL de testes
- Exporte o SQL completo
- Importe via phpMyAdmin no hPanel

### Passo 9: Compilar Assets

**Localmente, antes do upload:**

```bash
# No seu computador
npm install
npm run build

# Isso gera os arquivos em public/build/
# Faça upload desses arquivos para public_html/build/
```

### Passo 10: Testar em Produção

1. ✅ Acesse `https://seudominio.com.br`
2. ✅ Teste o login
3. ✅ Verifique se os ícones carregam
4. ✅ Teste criar/editar usuário
5. ✅ Verifique os logs: `/home/u123456789/portal/storage/logs/`
6. ✅ Teste rate limiting (6 tentativas de login rápidas)
7. ✅ Verifique headers de segurança:
   ```bash
   curl -I https://seudominio.com.br
   ```

---

## 🔧 Opção 2: Deploy com PostgreSQL (VPS ou Cloud)

Se você tem um VPS na Hostinger ou usa Cloud Hosting:

### Passo 1: Instalar PostgreSQL no VPS

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install postgresql postgresql-contrib

# Criar usuário e banco
sudo -u postgres psql
CREATE DATABASE portal;
CREATE USER portal_user WITH PASSWORD 'senha_forte_aqui';
GRANT ALL PRIVILEGES ON DATABASE portal TO portal_user;
\q
```

### Passo 2: Configurar PHP para PostgreSQL

```bash
# Instalar extensão pdo_pgsql
sudo apt install php8.3-pgsql
sudo systemctl restart apache2  # ou nginx
```

### Passo 3: Firewall e Segurança

```bash
# Permitir PostgreSQL apenas localmente
sudo ufw allow from 127.0.0.1 to any port 5432

# Editar pg_hba.conf para permitir conexões locais
sudo nano /etc/postgresql/15/main/pg_hba.conf

# Adicionar:
# local   portal    portal_user                     md5
```

### Passo 4: Seguir passos similares à Opção 1

Com PostgreSQL instalado, os passos de upload, configuração e deploy são os mesmos da Opção 1, mas mantendo `DB_CONNECTION=pgsql` no `.env`.

---

## 📦 Opção 3: PostgreSQL Externo (Recomendado)

Use um serviço de banco de dados gerenciado:

### Render.com (Gratuito até 90 dias, depois $7/mês)
1. Crie conta em [render.com](https://render.com)
2. New → PostgreSQL
3. Copie as credenciais:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=dpg-xxxxx-a.oregon-postgres.render.com
   DB_PORT=5432
   DB_DATABASE=portal_xxxx
   DB_USERNAME=portal_xxxx_user
   DB_PASSWORD=senha_gerada
   ```
4. Configure SSL no Laravel (`config/database.php`):
   ```php
   'pgsql' => [
       // ...
       'sslmode' => 'require',
   ],
   ```

### Supabase (Gratuito com limites)
1. Crie projeto em [supabase.com](https://supabase.com)
2. Settings → Database
3. Copie Connection String e configure no `.env`

### Railway (Gratuito $5 de crédito/mês)
1. Crie projeto em [railway.app](https://railway.app)
2. New → PostgreSQL
3. Connect

---

## 🔒 Checklist Final de Segurança

Antes de colocar no ar:

- [ ] `APP_DEBUG=false` no `.env`
- [ ] `APP_ENV=production`
- [ ] `LOG_LEVEL=warning` ou `error`
- [ ] HTTPS ativo (SSL/TLS)
- [ ] Senha do banco forte (16+ caracteres)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] Caches gerados:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```
- [ ] Testar login em produção
- [ ] Testar rate limiting
- [ ] Verificar logs (sem dados sensíveis)
- [ ] Backup do banco de dados configurado
- [ ] Monitoramento de erros (Sentry, Bugsnag, etc.)

---

## 🆘 Problemas Comuns

### 1. Erro 500 - Internal Server Error

**Causa:** Permissões incorretas ou `.env` faltando

**Solução:**
```bash
chmod -R 775 storage bootstrap/cache
php artisan config:clear
```

Verifique logs: `/home/u123456789/portal/storage/logs/laravel.log`

### 2. CSS/JS não carregam

**Causa:** Assets não compilados ou caminho errado

**Solução:**
```bash
# Localmente
npm run build

# Upload do public/build/ para public_html/build/
```

### 3. Erro de conexão com banco de dados

**Causa:** Credenciais incorretas ou host errado

**Solução:**
- Hostinger Shared: `DB_HOST=localhost`
- PostgreSQL externo: Use o host completo do serviço

### 4. Headers de segurança não funcionam

**Causa:** `mod_headers` do Apache não ativo

**Solução:**
```bash
sudo a2enmod headers
sudo systemctl restart apache2
```

Ou adicione no `.htaccess`:
```apache
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
</IfModule>
```

---

## 📊 Monitoramento

### Logs em Produção

**Via SSH:**
```bash
# Ver últimas 50 linhas
tail -50 /home/u123456789/portal/storage/logs/laravel.log

# Acompanhar em tempo real
tail -f /home/u123456789/portal/storage/logs/laravel.log
```

**Via hPanel:**
- File Manager → `portal/storage/logs/` → Download do arquivo

### Backup Automático

Configure no hPanel:
- **Backups → Weekly Backups** (disponível em planos Premium+)

Ou use scripts cron:
```bash
# Crontab para backup diário
0 2 * * * mysqldump -u u123456789_user -pSENHA u123456789_portal > /home/u123456789/backups/portal_$(date +\%Y\%m\%d).sql
```

---

## 🚀 Deploy Contínuo (Opcional)

Para deploys automatizados via Git:

### Via GitHub Actions

**Arquivo: `.github/workflows/deploy.yml`**
```yaml
name: Deploy to Hostinger

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'

      - name: Install Dependencies
        run: composer install --optimize-autoloader --no-dev

      - name: Deploy via FTP
        uses: SamKirkland/FTP-Deploy-Action@4.3.0
        with:
          server: ftp.seudominio.com.br
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          local-dir: ./
          server-dir: /home/u123456789/portal/
          exclude: |
            **/.git*
            **/.git*/**
            **/node_modules/**
            storage/**
            .env
```

---

## 📞 Suporte

**Hostinger:**
- Chat 24/7 no hPanel
- Base de conhecimento: [support.hostinger.com](https://support.hostinger.com)

**Laravel:**
- Documentação: [laravel.com/docs](https://laravel.com/docs)

**Dúvidas sobre este projeto:**
- Consulte `SECURITY.md`
- Consulte `CODE_REVIEW_CHECKLIST.md`

---

**Última atualização:** 2025-12-04
**Versão:** 1.0
