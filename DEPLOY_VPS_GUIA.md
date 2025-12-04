# 🚀 Guia de Deploy Automático - VPS Hostinger

## 📋 Pré-requisitos

### 1. Acesso à sua VPS
Você precisa ter:
- ✅ IP da VPS
- ✅ Usuário root ou sudo
- ✅ Senha ou chave SSH

### 2. Sistema Operacional
O script funciona em:
- ✅ Ubuntu 20.04/22.04/24.04
- ✅ Debian 11/12
- ✅ Outras distros baseadas em Debian/Ubuntu

---

## 🎯 Método 1: Deploy Automático (Recomendado)

### Passo 1: Enviar Script para o VPS

**No seu computador (PowerShell/CMD):**

```powershell
# Copiar script para o VPS via SCP
scp deploy-vps.sh root@SEU_IP_VPS:/root/

# OU se tiver usuário não-root:
scp deploy-vps.sh usuario@SEU_IP_VPS:/home/usuario/
```

**Exemplo:**
```powershell
scp deploy-vps.sh root@195.200.123.45:/root/
```

### Passo 2: Conectar ao VPS via SSH

```bash
ssh root@SEU_IP_VPS
# OU
ssh usuario@SEU_IP_VPS
```

**Exemplo:**
```bash
ssh root@195.200.123.45
```

### Passo 3: Dar Permissão de Execução

```bash
chmod +x deploy-vps.sh
```

### Passo 4: Executar o Script

```bash
sudo ./deploy-vps.sh
```

**O script vai perguntar:**

1. ✅ **Criar banco de dados?** (s/n)
   - Responda: `s`
   - Ele vai criar automaticamente e gerar senha

2. ✅ **Método de deploy:** (1/2)
   - Opção 1: Git Clone (se código estiver no GitHub)
   - Opção 2: Upload manual (se você já fez upload via SCP)
   - Escolha: `2` (já vamos fazer upload antes)

3. ✅ **Executar migrations?** (s/n)
   - Responda: `s`

4. ✅ **Importar estados e cidades?** (s/n)
   - Responda: `s` (se tiver o SQL)

5. ✅ **Instalar SSL?** (s/n)
   - Responda: `s` (Let's Encrypt gratuito)

**O script faz automaticamente:**
- ✅ Instala dependências faltantes (PHP, PostgreSQL, etc.)
- ✅ Cria banco de dados
- ✅ Configura .env
- ✅ Instala Composer dependencies
- ✅ Compila assets (npm run build)
- ✅ Roda migrations
- ✅ Ajusta permissões
- ✅ Configura Apache/Nginx
- ✅ Instala SSL (Let's Encrypt)
- ✅ Configura firewall (UFW)

---

## 📦 Passo 0: Enviar Arquivos do Laravel

**ANTES de rodar o script**, envie os arquivos do Laravel para o VPS:

### Opção A: Via SCP (Simples)

**No seu computador:**

```powershell
# Comprimir o projeto (sem node_modules e vendor)
cd c:\Users\caique\Documents\portal\portal
tar -czf portal.tar.gz --exclude=node_modules --exclude=vendor --exclude=.git *

# Enviar para o VPS
scp portal.tar.gz root@SEU_IP_VPS:/tmp/

# Conectar ao VPS
ssh root@SEU_IP_VPS

# No VPS, extrair os arquivos
mkdir -p /var/www/sistemasemteste.com.br
tar -xzf /tmp/portal.tar.gz -C /var/www/sistemasemteste.com.br
```

### Opção B: Via Git (Melhor prática)

**1. No seu computador, fazer push para GitHub:**

```bash
cd c:\Users\caique\Documents\portal\portal
git add .
git commit -m "Preparar para deploy"
git push origin main
```

**2. No script, quando perguntar "Método de deploy", escolher opção 1 (Git)**

---

## 🔧 Método 2: Deploy Manual Passo a Passo

Se preferir fazer manualmente sem o script:

### 1. Atualizar Sistema

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Instalar PHP 8.3

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-intl
```

### 3. Instalar PostgreSQL

```bash
sudo apt install -y postgresql postgresql-contrib
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### 4. Criar Banco de Dados

```bash
sudo -u postgres psql <<EOF
CREATE DATABASE portal_prod;
CREATE USER portal_user WITH PASSWORD 'SUA_SENHA_FORTE_AQUI';
GRANT ALL PRIVILEGES ON DATABASE portal_prod TO portal_user;
\c portal_prod
GRANT ALL ON SCHEMA public TO portal_user;
EOF
```

### 5. Instalar Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 6. Instalar Node.js e npm

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 7. Instalar Apache

```bash
sudo apt install -y apache2
sudo a2enmod rewrite ssl headers
sudo systemctl start apache2
sudo systemctl enable apache2
```

### 8. Criar Diretório e Copiar Arquivos

```bash
sudo mkdir -p /var/www/sistemasemteste.com.br
# (Copiar arquivos via SCP, conforme Passo 0 acima)
```

### 9. Instalar Dependências

```bash
cd /var/www/sistemasemteste.com.br
sudo composer install --optimize-autoloader --no-dev
sudo npm install
sudo npm run build
```

### 10. Configurar .env

```bash
sudo cp .env.example .env
sudo nano .env
```

**Editar:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sistemasemteste.com.br

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=portal_prod
DB_USERNAME=portal_user
DB_PASSWORD=SUA_SENHA_AQUI
```

**Gerar chave:**
```bash
sudo php artisan key:generate
```

### 11. Permissões

```bash
sudo chown -R www-data:www-data /var/www/sistemasemteste.com.br
sudo chmod -R 755 /var/www/sistemasemteste.com.br
sudo chmod -R 775 /var/www/sistemasemteste.com.br/storage
sudo chmod -R 775 /var/www/sistemasemteste.com.br/bootstrap/cache
sudo chmod 600 /var/www/sistemasemteste.com.br/.env
```

### 12. Migrations

```bash
sudo php artisan migrate --force
```

### 13. Cachear Configurações

```bash
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

### 14. Configurar Apache

```bash
sudo nano /etc/apache2/sites-available/sistemasemteste.com.br.conf
```

**Conteúdo:**
```apache
<VirtualHost *:80>
    ServerName sistemasemteste.com.br
    ServerAlias www.sistemasemteste.com.br

    DocumentRoot /var/www/sistemasemteste.com.br/public

    <Directory /var/www/sistemasemteste.com.br/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sistemasemteste-error.log
    CustomLog ${APACHE_LOG_DIR}/sistemasemteste-access.log combined
</VirtualHost>
```

**Ativar site:**
```bash
sudo a2ensite sistemasemteste.com.br.conf
sudo a2dissite 000-default.conf
sudo apache2ctl configtest
sudo systemctl restart apache2
```

### 15. Instalar SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d sistemasemteste.com.br -d www.sistemasemteste.com.br
```

### 16. Configurar Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable
```

---

## ✅ Verificação Pós-Deploy

### 1. Testar Acesso

```bash
curl -I https://sistemasemteste.com.br
```

**Deve retornar:**
```
HTTP/2 200
```

### 2. Ver Logs

```bash
tail -f /var/www/sistemasemteste.com.br/storage/logs/laravel.log
```

### 3. Testar Login

Acesse: https://sistemasemteste.com.br/login

### 4. Verificar Headers de Segurança

```bash
curl -I https://sistemasemteste.com.br | grep -E "X-XSS|X-Frame|X-Content"
```

---

## 🐛 Problemas Comuns

### Erro: "Connection refused" ao acessar o site

**Causa:** Apache não está rodando ou porta bloqueada

**Solução:**
```bash
sudo systemctl status apache2
sudo systemctl start apache2
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

### Erro 500 - Internal Server Error

**Verificar logs:**
```bash
tail -50 /var/www/sistemasemteste.com.br/storage/logs/laravel.log
```

**Causas comuns:**
- Permissões incorretas
- .env mal configurado
- Erro de conexão com banco

**Soluções:**
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data /var/www/sistemasemteste.com.br
sudo php artisan config:clear
```

### Erro: "SQLSTATE[08006] Connection refused"

**Causa:** PostgreSQL não está aceitando conexões

**Solução:**
```bash
sudo systemctl status postgresql
sudo systemctl start postgresql

# Verificar se o banco existe
sudo -u postgres psql -c "\l" | grep portal
```

### CSS/JS não carregam

**Causa:** Assets não compilados

**Solução:**
```bash
cd /var/www/sistemasemteste.com.br
npm run build
sudo systemctl restart apache2
```

---

## 📊 Monitoramento

### Ver Logs em Tempo Real

```bash
# Laravel
tail -f /var/www/sistemasemteste.com.br/storage/logs/laravel.log

# Apache
tail -f /var/log/apache2/sistemasemteste-error.log
tail -f /var/log/apache2/sistemasemteste-access.log

# PostgreSQL
sudo tail -f /var/log/postgresql/postgresql-15-main.log
```

### Comandos Úteis

```bash
# Status dos serviços
sudo systemctl status apache2
sudo systemctl status postgresql

# Reiniciar serviços
sudo systemctl restart apache2
sudo systemctl restart postgresql

# Ver uso de recursos
htop

# Ver espaço em disco
df -h

# Ver processos PHP
ps aux | grep php
```

---

## 🔄 Deploy de Atualizações

Para atualizar o site após mudanças:

```bash
cd /var/www/sistemasemteste.com.br

# Se usar Git
git pull origin main

# OU se fizer upload manual via SCP
# (fazer upload dos arquivos alterados)

# Atualizar dependências (se necessário)
composer install --no-dev
npm run build

# Rodar migrations (se houver novas)
php artisan migrate --force

# Limpar e cachear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar servidor
sudo systemctl restart apache2
```

---

## 🔒 Checklist de Segurança Final

- [ ] `APP_DEBUG=false` no `.env`
- [ ] `APP_ENV=production`
- [ ] SSL ativo (HTTPS)
- [ ] Firewall configurado (UFW)
- [ ] Senha do banco forte (16+ caracteres)
- [ ] Permissões corretas (775 storage, 600 .env)
- [ ] Logs não expõem dados sensíveis
- [ ] Rate limiting ativo
- [ ] Headers de segurança funcionando
- [ ] Backup configurado
- [ ] Renovação automática de SSL ativa

---

## 📞 Suporte

**Hostinger VPS:**
- Chat 24/7: [hpanel.hostinger.com](https://hpanel.hostinger.com)
- Base de conhecimento: [support.hostinger.com](https://support.hostinger.com)

**Documentação:**
- DEPLOY_SISTEMASEMTESTE.md - Guia completo
- SECURITY.md - Política de segurança
- RESUMO_DEPLOY.md - Resumo executivo

---

**Domínio:** sistemasemteste.com.br
**Criado em:** 2025-12-04
**Versão:** 1.0

🚀 **Sucesso com seu deploy!**
