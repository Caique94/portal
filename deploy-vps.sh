#!/bin/bash

# ============================================
# Script de Deploy Automatizado - VPS
# sistemasemteste.com.br
# ============================================

set -e  # Parar em caso de erro

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Funções de log
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[OK]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[AVISO]${NC} $1"; }
log_error() { echo -e "${RED}[ERRO]${NC} $1"; }
log_step() { echo -e "\n${CYAN}==== $1 ====${NC}\n"; }

# Banner
clear
echo -e "${CYAN}"
echo "============================================"
echo "    Deploy Automatizado - VPS Hostinger    "
echo "    sistemasemteste.com.br                 "
echo "============================================"
echo -e "${NC}"

# ============================================
# CONFIGURAÇÕES (Ajustar conforme sua VPS)
# ============================================

# Diretório da aplicação
APP_DIR="/var/www/sistemasemteste.com.br"
PUBLIC_DIR="/var/www/sistemasemteste.com.br/public"

# Repositório Git (se usar)
# GIT_REPO="https://github.com/seu-usuario/portal.git"
# GIT_BRANCH="main"

# Banco de Dados
DB_NAME="portal_prod"
DB_USER="portal_user"
# DB_PASS será solicitado

# Usuário do servidor web (Apache ou Nginx)
WEB_USER="www-data"  # Apache: www-data, Nginx: nginx

# Domínio
DOMAIN="sistemasemteste.com.br"

# ============================================
# 1. VERIFICAÇÕES PRÉ-REQUISITOS
# ============================================

log_step "1. Verificando Pré-requisitos"

# Verificar se é root ou sudo
if [[ $EUID -ne 0 ]]; then
   log_error "Este script precisa ser executado como root (sudo)"
   exit 1
fi

log_success "Executando como root"

# Verificar se PHP está instalado
if ! command -v php &> /dev/null; then
    log_error "PHP não está instalado!"
    log_info "Instale com: apt install php8.3 php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip"
    exit 1
fi

PHP_VERSION=$(php -v | head -n 1 | awk '{print $2}')
log_success "PHP $PHP_VERSION instalado"

# Verificar Composer
if ! command -v composer &> /dev/null; then
    log_error "Composer não está instalado!"
    log_info "Instale com: curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer"
    exit 1
fi

log_success "Composer instalado"

# Verificar PostgreSQL
if ! command -v psql &> /dev/null; then
    log_warning "PostgreSQL não está instalado!"
    read -p "Deseja instalar PostgreSQL agora? (s/n): " INSTALL_PG
    if [ "$INSTALL_PG" == "s" ]; then
        log_info "Instalando PostgreSQL..."
        apt update
        apt install -y postgresql postgresql-contrib
        systemctl start postgresql
        systemctl enable postgresql
        log_success "PostgreSQL instalado"
    else
        exit 1
    fi
else
    log_success "PostgreSQL instalado"
fi

# Verificar servidor web
if systemctl is-active --quiet apache2; then
    WEB_SERVER="apache2"
    WEB_USER="www-data"
    log_success "Apache detectado"
elif systemctl is-active --quiet nginx; then
    WEB_SERVER="nginx"
    WEB_USER="www-data"  # ou nginx, dependendo da distro
    log_success "Nginx detectado"
else
    log_warning "Nenhum servidor web (Apache/Nginx) detectado"
    read -p "Deseja instalar Apache? (s/n): " INSTALL_APACHE
    if [ "$INSTALL_APACHE" == "s" ]; then
        apt install -y apache2
        systemctl start apache2
        systemctl enable apache2
        WEB_SERVER="apache2"
        log_success "Apache instalado"
    else
        exit 1
    fi
fi

# ============================================
# 2. CRIAR BANCO DE DADOS
# ============================================

log_step "2. Configurando Banco de Dados PostgreSQL"

read -p "Criar novo banco de dados '$DB_NAME'? (s/n): " CREATE_DB

if [ "$CREATE_DB" == "s" ]; then
    log_info "Criando banco de dados..."

    # Gerar senha aleatória forte
    DB_PASS=$(openssl rand -base64 16 | tr -d "=+/" | cut -c1-20)

    # Criar usuário e banco
    sudo -u postgres psql <<EOF
CREATE DATABASE $DB_NAME;
CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';
GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;
\c $DB_NAME
GRANT ALL ON SCHEMA public TO $DB_USER;
EOF

    log_success "Banco criado: $DB_NAME"
    log_success "Usuário criado: $DB_USER"
    log_info "Senha gerada: $DB_PASS"

    # Salvar credenciais
    echo "DB_DATABASE=$DB_NAME" > /tmp/db_credentials.txt
    echo "DB_USERNAME=$DB_USER" >> /tmp/db_credentials.txt
    echo "DB_PASSWORD=$DB_PASS" >> /tmp/db_credentials.txt

    log_warning "Credenciais salvas em: /tmp/db_credentials.txt"
    log_warning "ANOTE ESSAS CREDENCIAIS!"

    read -p "Pressione Enter para continuar..."
else
    log_info "Pulando criação do banco"
    read -p "Digite a senha do banco existente: " -s DB_PASS
    echo ""
fi

# ============================================
# 3. CRIAR DIRETÓRIO DA APLICAÇÃO
# ============================================

log_step "3. Criando Diretório da Aplicação"

if [ -d "$APP_DIR" ]; then
    log_warning "Diretório já existe: $APP_DIR"
    read -p "Deseja fazer backup e recriar? (s/n): " RECREATE
    if [ "$RECREATE" == "s" ]; then
        BACKUP_DIR="$APP_DIR.backup.$(date +%Y%m%d_%H%M%S)"
        mv "$APP_DIR" "$BACKUP_DIR"
        log_success "Backup criado: $BACKUP_DIR"
        mkdir -p "$APP_DIR"
    fi
else
    mkdir -p "$APP_DIR"
    log_success "Diretório criado: $APP_DIR"
fi

# ============================================
# 4. UPLOAD/CLONE DO CÓDIGO
# ============================================

log_step "4. Obtendo Código da Aplicação"

echo "Escolha o método de deploy:"
echo "1) Git Clone (recomendado)"
echo "2) Upload manual via SCP (você já fez upload)"
read -p "Opção (1/2): " DEPLOY_METHOD

if [ "$DEPLOY_METHOD" == "1" ]; then
    if [ -z "$GIT_REPO" ]; then
        log_error "GIT_REPO não está configurado!"
        read -p "Digite a URL do repositório Git: " GIT_REPO
    fi

    log_info "Clonando repositório..."

    if [ -d "$APP_DIR/.git" ]; then
        cd "$APP_DIR"
        git pull origin main
        log_success "Repositório atualizado"
    else
        git clone "$GIT_REPO" "$APP_DIR"
        cd "$APP_DIR"
        log_success "Repositório clonado"
    fi
elif [ "$DEPLOY_METHOD" == "2" ]; then
    log_info "Assumindo que os arquivos já foram enviados para: $APP_DIR"
    cd "$APP_DIR"

    if [ ! -f "artisan" ]; then
        log_error "Arquivo 'artisan' não encontrado em $APP_DIR"
        log_error "Certifique-se de ter feito upload dos arquivos do Laravel"
        exit 1
    fi

    log_success "Arquivos encontrados"
else
    log_error "Opção inválida"
    exit 1
fi

# ============================================
# 5. INSTALAR DEPENDÊNCIAS
# ============================================

log_step "5. Instalando Dependências"

log_info "Instalando dependências do Composer..."
composer install --optimize-autoloader --no-dev --no-interaction

log_success "Dependências instaladas"

# NPM (se tiver)
if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        log_info "Instalando dependências do NPM..."
        npm install
        npm run build
        log_success "Assets compilados"
    else
        log_warning "npm não instalado, pulando compilação de assets"
    fi
fi

# ============================================
# 6. CONFIGURAR .env
# ============================================

log_step "6. Configurando .env"

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        log_success ".env criado a partir de .env.example"
    else
        log_error ".env.example não encontrado!"
        exit 1
    fi
else
    log_info ".env já existe, pulando"
fi

# Atualizar configurações no .env
log_info "Atualizando configurações do .env..."

sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" .env
sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|" .env
sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" .env
sed -i "s|^DB_PORT=.*|DB_PORT=5432|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env
sed -i "s|^LOG_LEVEL=.*|LOG_LEVEL=warning|" .env
sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=database|" .env
sed -i "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" .env
sed -i "s|^CACHE_STORE=.*|CACHE_STORE=database|" .env

log_success ".env configurado"

# Gerar APP_KEY
log_info "Gerando APP_KEY..."
php artisan key:generate --force
log_success "APP_KEY gerada"

# ============================================
# 7. PERMISSÕES
# ============================================

log_step "7. Ajustando Permissões"

chown -R $WEB_USER:$WEB_USER "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"
chmod 600 "$APP_DIR/.env"

log_success "Permissões ajustadas"

# ============================================
# 8. MIGRATIONS
# ============================================

log_step "8. Executando Migrations"

read -p "Executar migrations? (s/n): " RUN_MIGRATIONS

if [ "$RUN_MIGRATIONS" == "s" ]; then
    log_info "Rodando migrations..."
    php artisan migrate --force
    log_success "Migrations executadas"

    # Importar estados e cidades (se tiver)
    if [ -f "database/seeds/estados_cidades_brasil.sql" ]; then
        read -p "Importar estados e cidades? (s/n): " IMPORT_ESTADOS
        if [ "$IMPORT_ESTADOS" == "s" ]; then
            PGPASSWORD=$DB_PASS psql -U $DB_USER -d $DB_NAME -f database/seeds/estados_cidades_brasil.sql
            log_success "Estados e cidades importados"
        fi
    fi
else
    log_info "Pulando migrations"
fi

# ============================================
# 9. OTIMIZAÇÕES
# ============================================

log_step "9. Otimizando Aplicação"

log_info "Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

log_info "Cacheando configurações..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

log_success "Otimizações aplicadas"

# ============================================
# 10. CONFIGURAR SERVIDOR WEB
# ============================================

log_step "10. Configurando Servidor Web"

if [ "$WEB_SERVER" == "apache2" ]; then
    log_info "Configurando Apache..."

    APACHE_CONF="/etc/apache2/sites-available/$DOMAIN.conf"

    cat > "$APACHE_CONF" <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN

    # Redirecionar para HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}\$1 [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN

    DocumentRoot $PUBLIC_DIR

    <Directory $PUBLIC_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/$DOMAIN-error.log
    CustomLog \${APACHE_LOG_DIR}/$DOMAIN-access.log combined

    # SSL (será configurado pelo Certbot)
    # SSLEngine on
    # SSLCertificateFile /etc/letsencrypt/live/$DOMAIN/fullchain.pem
    # SSLCertificateKeyFile /etc/letsencrypt/live/$DOMAIN/privkey.pem
</VirtualHost>
EOF

    # Ativar módulos necessários
    a2enmod rewrite
    a2enmod ssl
    a2enmod headers

    # Ativar site
    a2ensite "$DOMAIN.conf"
    a2dissite 000-default.conf

    # Testar configuração
    apache2ctl configtest

    # Reiniciar Apache
    systemctl restart apache2

    log_success "Apache configurado"

elif [ "$WEB_SERVER" == "nginx" ]; then
    log_info "Configurando Nginx..."

    NGINX_CONF="/etc/nginx/sites-available/$DOMAIN"

    cat > "$NGINX_CONF" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;

    # Redirecionar para HTTPS
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name $DOMAIN www.$DOMAIN;

    root $PUBLIC_DIR;
    index index.php index.html;

    # SSL (será configurado pelo Certbot)
    # ssl_certificate /etc/letsencrypt/live/$DOMAIN/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/$DOMAIN/privkey.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \\.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\\.(?!well-known).* {
        deny all;
    }
}
EOF

    # Ativar site
    ln -sf "$NGINX_CONF" "/etc/nginx/sites-enabled/"
    rm -f /etc/nginx/sites-enabled/default

    # Testar configuração
    nginx -t

    # Reiniciar Nginx
    systemctl restart nginx

    log_success "Nginx configurado"
fi

# ============================================
# 11. CONFIGURAR SSL (Certbot)
# ============================================

log_step "11. Configurando SSL (Let's Encrypt)"

read -p "Instalar SSL com Certbot? (s/n): " INSTALL_SSL

if [ "$INSTALL_SSL" == "s" ]; then
    if ! command -v certbot &> /dev/null; then
        log_info "Instalando Certbot..."
        apt install -y certbot python3-certbot-$WEB_SERVER
    fi

    log_info "Obtendo certificado SSL..."
    certbot --$WEB_SERVER -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

    log_success "SSL configurado"

    # Configurar renovação automática
    systemctl enable certbot.timer
    systemctl start certbot.timer

    log_success "Renovação automática de SSL configurada"
else
    log_info "Pulando configuração de SSL"
fi

# ============================================
# 12. FIREWALL
# ============================================

log_step "12. Configurando Firewall (UFW)"

if command -v ufw &> /dev/null; then
    log_info "Configurando regras do firewall..."

    ufw allow 22/tcp   # SSH
    ufw allow 80/tcp   # HTTP
    ufw allow 443/tcp  # HTTPS
    ufw --force enable

    log_success "Firewall configurado"
else
    log_warning "UFW não instalado, pulando configuração de firewall"
fi

# ============================================
# RESUMO FINAL
# ============================================

log_step "DEPLOY CONCLUÍDO!"

echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║    DEPLOY FINALIZADO COM SUCESSO!      ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""

echo -e "${CYAN}📋 Informações Importantes:${NC}"
echo ""
echo "🌐 URL do site: https://$DOMAIN"
echo "📂 Diretório: $APP_DIR"
echo "🗄️  Banco de dados: $DB_NAME"
echo "👤 Usuário do banco: $DB_USER"

if [ -f "/tmp/db_credentials.txt" ]; then
    echo ""
    echo -e "${YELLOW}⚠️  Credenciais do banco salvas em: /tmp/db_credentials.txt${NC}"
    echo -e "${YELLOW}   ANOTE ESSAS CREDENCIAIS E DELETE O ARQUIVO!${NC}"
fi

echo ""
echo -e "${CYAN}🧪 Próximos Passos:${NC}"
echo ""
echo "1. Acesse: https://$DOMAIN"
echo "2. Teste o login"
echo "3. Verifique se os ícones aparecem corretamente"
echo "4. Teste criar/editar usuário"
echo "5. Verificar logs:"
echo "   tail -f $APP_DIR/storage/logs/laravel.log"
echo ""

echo -e "${CYAN}🔒 Verificações de Segurança:${NC}"
echo ""
echo "✅ APP_DEBUG=false"
echo "✅ APP_ENV=production"
echo "✅ SSL configurado"
echo "✅ Permissões ajustadas"
echo "✅ Firewall ativo"
echo ""

echo -e "${CYAN}📊 Comandos Úteis:${NC}"
echo ""
echo "# Ver logs em tempo real:"
echo "tail -f $APP_DIR/storage/logs/laravel.log"
echo ""
echo "# Limpar caches:"
echo "cd $APP_DIR && php artisan cache:clear"
echo ""
echo "# Reiniciar servidor web:"
echo "systemctl restart $WEB_SERVER"
echo ""
echo "# Status do banco:"
echo "sudo -u postgres psql -c '\l'"
echo ""

echo -e "${GREEN}✨ Deploy concluído! Boa sorte! 🚀${NC}"
echo ""
