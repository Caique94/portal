# ⚡ Guia Rápido de Deploy - sistemasemteste.com.br

## 🎯 Passos Essenciais (Ordem Correta)

### 1️⃣ PREPARAÇÃO LOCAL (Seu Computador)

```powershell
# No PowerShell
cd c:\Users\caique\Documents\portal\portal
.\prepare-deploy.ps1
```

**Resultado:** Cria pasta `deploy-hostinger/` com todos os arquivos prontos

---

### 2️⃣ CONFIGURAR HOSTINGER (hPanel)

#### 2.1. Criar Banco MySQL
1. hPanel → **Databases** → **MySQL Databases**
2. **Create New Database**
3. Anotar:
   ```
   DB_DATABASE: u123456789_portal
   DB_USERNAME: u123456789_portal_user
   DB_PASSWORD: [senha gerada]
   ```

#### 2.2. Ativar SSL
1. hPanel → **Security** → **SSL**
2. Encontrar `sistemasemteste.com.br`
3. **Install SSL** (Let's Encrypt - Free)
4. Ativar **Force HTTPS**

#### 2.3. Criar Email (Opcional)
1. hPanel → **Emails** → **Email Accounts**
2. Criar: `noreply@sistemasemteste.com.br`

---

### 3️⃣ UPLOAD VIA FTP

#### Conectar
```
Host: ftp.sistemasemteste.com.br
Port: 22 (SFTP)
User: u123456789
Pass: [sua senha]
```

#### Upload
```
deploy-hostinger/portal/     → /domains/sistemasemteste.com.br/portal/
deploy-hostinger/public_html/ → /public_html/
```

---

### 4️⃣ CONFIGURAR NO SERVIDOR (File Manager)

#### 4.1. Criar .env
1. File Manager → `/domains/sistemasemteste.com.br/portal/`
2. Copiar `.env.example` → `.env`
3. Editar `.env`:
   ```env
   APP_URL=https://sistemasemteste.com.br
   DB_DATABASE=u123456789_portal
   DB_USERNAME=u123456789_portal_user
   DB_PASSWORD=[senha do passo 2.1]
   ```

#### 4.2. Ajustar index.php
1. File Manager → `/public_html/index.php`
2. Substituir 3 linhas:

**ANTES:**
```php
require __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../bootstrap/app.php'
file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')
```

**DEPOIS:**
```php
require __DIR__.'/../domains/sistemasemteste.com.br/portal/vendor/autoload.php';
require_once __DIR__.'/../domains/sistemasemteste.com.br/portal/bootstrap/app.php'
file_exists($maintenance = __DIR__.'/../domains/sistemasemteste.com.br/portal/storage/framework/maintenance.php')
```

#### 4.3. Ativar HTTPS no .htaccess
1. File Manager → `/public_html/.htaccess`
2. Descomentar (remover `#`):
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

---

### 5️⃣ CONFIGURAR VIA SSH (Obrigatório)

#### Conectar
```bash
ssh u123456789@ssh.sistemasemteste.com.br
```

#### Comandos
```bash
cd ~/domains/sistemasemteste.com.br/portal

# Permissões
chmod -R 775 storage bootstrap/cache
chmod 600 .env

# Migrations
php artisan migrate --force

# Cache (otimização)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 6️⃣ TESTAR

1. Acesse: **https://sistemasemteste.com.br**
2. Fazer login
3. Criar/editar usuário
4. Verificar ícones (não podem ser quadrados)
5. Testar CEP

---

## 🆘 Problemas Comuns

### ❌ Erro 500
```bash
# Via SSH
tail -50 ~/domains/sistemasemteste.com.br/portal/storage/logs/laravel.log
chmod -R 775 storage bootstrap/cache
```

### ❌ Página em branco
Verificar `/public_html/index.php` (caminhos incorretos)

### ❌ CSS não carrega
```bash
# Localmente
npm run build
# Depois fazer upload de public/build/
```

### ❌ Erro de banco
Verificar credenciais no `.env`:
```env
DB_HOST=localhost  ← NÃO usar 127.0.0.1
```

---

## 📋 Checklist Rápido

**ANTES do deploy:**
- [ ] Rodar `prepare-deploy.ps1`
- [ ] Criar banco MySQL
- [ ] Ativar SSL

**UPLOAD:**
- [ ] Upload `portal/` → `/domains/sistemasemteste.com.br/portal/`
- [ ] Upload `public_html/` → `/public_html/`

**CONFIGURAR:**
- [ ] Criar `.env` e preencher credenciais
- [ ] Ajustar `index.php`
- [ ] Descomentar HTTPS em `.htaccess`

**SSH:**
- [ ] Permissões: `chmod -R 775 storage bootstrap/cache`
- [ ] Migrations: `php artisan migrate --force`
- [ ] Cache: `php artisan config:cache route:cache view:cache`

**TESTAR:**
- [ ] Acesso: https://sistemasemteste.com.br
- [ ] Login funciona
- [ ] Ícones aparecem
- [ ] CRUD funciona

---

## 📚 Documentação Completa

- **DEPLOY_SISTEMASEMTESTE.md** - Guia detalhado passo a passo
- **RESUMO_DEPLOY.md** - Resumo executivo com troubleshooting
- **DEPLOY_HOSTINGER.md** - Guia genérico da Hostinger
- **CREDENCIAIS_HOSTINGER.md** - Template de credenciais

---

**Domínio:** sistemasemteste.com.br
**Última atualização:** 2025-12-04
