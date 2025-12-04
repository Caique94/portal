#!/bin/bash

# =================================
# Script de Deploy - Hostinger
# =================================

set -e  # Parar em caso de erro

echo "🚀 Iniciando processo de deploy..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para mensagens
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "ℹ️  $1"
}

# =================================
# 1. Verificações Pré-Deploy
# =================================
echo ""
print_info "Verificando ambiente..."

# Verificar se está no diretório correto
if [ ! -f "artisan" ]; then
    print_error "Este script deve ser executado na raiz do projeto Laravel!"
    exit 1
fi

# Verificar se .env existe
if [ ! -f ".env" ]; then
    print_error "Arquivo .env não encontrado!"
    exit 1
fi

# Verificar se composer está instalado
if ! command -v composer &> /dev/null; then
    print_error "Composer não está instalado!"
    exit 1
fi

# Verificar se npm está instalado
if ! command -v npm &> /dev/null; then
    print_warning "npm não está instalado. Assets não serão compilados."
else
    HAS_NPM=true
fi

print_success "Ambiente verificado"

# =================================
# 2. Perguntar tipo de deploy
# =================================
echo ""
print_info "Selecione o tipo de deploy:"
echo "1) Deploy completo (primeira vez)"
echo "2) Deploy rápido (atualização de código)"
echo "3) Apenas compilar assets"
read -p "Escolha (1/2/3): " DEPLOY_TYPE

# =================================
# 3. Perguntar ambiente
# =================================
echo ""
read -p "Ambiente de destino (local/production): " ENVIRONMENT

if [ "$ENVIRONMENT" == "production" ]; then
    print_warning "ATENÇÃO: Deploy para PRODUÇÃO!"
    read -p "Confirma? (sim/nao): " CONFIRM
    if [ "$CONFIRM" != "sim" ]; then
        print_error "Deploy cancelado pelo usuário"
        exit 0
    fi
fi

# =================================
# 4. Testes (opcional em produção)
# =================================
if [ "$ENVIRONMENT" == "production" ]; then
    echo ""
    read -p "Executar testes antes do deploy? (s/n): " RUN_TESTS
    if [ "$RUN_TESTS" == "s" ]; then
        print_info "Executando testes..."
        if php artisan test; then
            print_success "Testes passaram"
        else
            print_error "Testes falharam! Deploy abortado."
            exit 1
        fi
    fi
fi

# =================================
# 5. Backup (produção)
# =================================
if [ "$ENVIRONMENT" == "production" ] && [ "$DEPLOY_TYPE" != "3" ]; then
    echo ""
    print_info "Criando backup do banco de dados..."

    # Ler variáveis do .env
    DB_CONNECTION=$(grep DB_CONNECTION .env | cut -d '=' -f2)
    DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
    DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
    DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

    BACKUP_DIR="backups"
    mkdir -p $BACKUP_DIR
    BACKUP_FILE="$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql"

    if [ "$DB_CONNECTION" == "mysql" ]; then
        if mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_FILE"; then
            print_success "Backup criado: $BACKUP_FILE"
        else
            print_warning "Falha ao criar backup. Continuar? (s/n)"
            read -p "> " CONTINUE
            if [ "$CONTINUE" != "s" ]; then
                exit 1
            fi
        fi
    elif [ "$DB_CONNECTION" == "pgsql" ]; then
        if PGPASSWORD="$DB_PASSWORD" pg_dump -U "$DB_USERNAME" "$DB_DATABASE" > "$BACKUP_FILE"; then
            print_success "Backup criado: $BACKUP_FILE"
        else
            print_warning "Falha ao criar backup. Continuar? (s/n)"
            read -p "> " CONTINUE
            if [ "$CONTINUE" != "s" ]; then
                exit 1
            fi
        fi
    fi
fi

# =================================
# 6. Instalar dependências
# =================================
if [ "$DEPLOY_TYPE" == "1" ] || [ "$DEPLOY_TYPE" == "2" ]; then
    echo ""
    print_info "Instalando dependências do Composer..."

    if [ "$ENVIRONMENT" == "production" ]; then
        composer install --optimize-autoloader --no-dev
    else
        composer install
    fi

    print_success "Dependências do Composer instaladas"
fi

# =================================
# 7. Compilar assets
# =================================
if [ "$HAS_NPM" == true ]; then
    if [ "$DEPLOY_TYPE" == "1" ] || [ "$DEPLOY_TYPE" == "3" ]; then
        echo ""
        print_info "Instalando dependências do NPM..."
        npm install
        print_success "Dependências do NPM instaladas"
    fi

    if [ "$DEPLOY_TYPE" != "2" ]; then
        echo ""
        print_info "Compilando assets..."
        npm run build
        print_success "Assets compilados"
    fi
fi

# =================================
# 8. Configurar Laravel
# =================================
if [ "$DEPLOY_TYPE" == "1" ]; then
    echo ""
    print_info "Gerando APP_KEY..."
    php artisan key:generate
    print_success "APP_KEY gerada"
fi

# =================================
# 9. Migrations
# =================================
if [ "$DEPLOY_TYPE" == "1" ] || [ "$DEPLOY_TYPE" == "2" ]; then
    echo ""
    read -p "Executar migrations? (s/n): " RUN_MIGRATIONS
    if [ "$RUN_MIGRATIONS" == "s" ]; then
        print_info "Executando migrations..."

        if [ "$ENVIRONMENT" == "production" ]; then
            php artisan migrate --force
        else
            php artisan migrate
        fi

        print_success "Migrations executadas"
    fi
fi

# =================================
# 10. Limpar e cachear
# =================================
if [ "$DEPLOY_TYPE" != "3" ]; then
    echo ""
    print_info "Limpando caches..."

    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear

    print_success "Caches limpos"

    if [ "$ENVIRONMENT" == "production" ]; then
        echo ""
        print_info "Cacheando configurações (otimização)..."

        php artisan config:cache
        php artisan route:cache
        php artisan view:cache

        print_success "Configurações cacheadas"
    fi
fi

# =================================
# 11. Permissões
# =================================
if [ "$DEPLOY_TYPE" == "1" ]; then
    echo ""
    print_info "Ajustando permissões..."

    chmod -R 775 storage
    chmod -R 775 bootstrap/cache

    print_success "Permissões ajustadas"
fi

# =================================
# 12. Verificação final
# =================================
echo ""
print_info "Verificando configuração..."

# Verificar APP_DEBUG em produção
if [ "$ENVIRONMENT" == "production" ]; then
    APP_DEBUG=$(grep APP_DEBUG .env | cut -d '=' -f2)
    if [ "$APP_DEBUG" == "true" ]; then
        print_error "ATENÇÃO: APP_DEBUG=true em produção!"
        print_error "Execute: Altere APP_DEBUG=false no .env"
        exit 1
    fi

    APP_ENV=$(grep APP_ENV .env | cut -d '=' -f2)
    if [ "$APP_ENV" != "production" ]; then
        print_warning "APP_ENV não está definido como 'production'"
    fi
fi

print_success "Verificação concluída"

# =================================
# 13. Resumo
# =================================
echo ""
echo "╔════════════════════════════════════════╗"
echo "║     DEPLOY CONCLUÍDO COM SUCESSO       ║"
echo "╚════════════════════════════════════════╝"
echo ""
print_info "Resumo:"
echo "  • Tipo: $DEPLOY_TYPE"
echo "  • Ambiente: $ENVIRONMENT"
if [ "$DEPLOY_TYPE" == "1" ] || [ "$DEPLOY_TYPE" == "2" ]; then
    echo "  • Dependências: ✅"
fi
if [ "$HAS_NPM" == true ] && [ "$DEPLOY_TYPE" != "2" ]; then
    echo "  • Assets: ✅"
fi
if [ "$RUN_MIGRATIONS" == "s" ]; then
    echo "  • Migrations: ✅"
fi
if [ "$ENVIRONMENT" == "production" ]; then
    echo "  • Backup: ✅"
    echo "  • Cache: ✅"
fi

# =================================
# 14. Próximos passos
# =================================
echo ""
print_info "Próximos passos:"

if [ "$ENVIRONMENT" == "production" ]; then
    echo "1. Testar a aplicação em https://seudominio.com.br"
    echo "2. Verificar logs: storage/logs/laravel.log"
    echo "3. Testar login e funcionalidades principais"
    echo "4. Verificar headers de segurança:"
    echo "   curl -I https://seudominio.com.br"
else
    echo "1. Testar a aplicação em http://localhost:8000"
    echo "2. Executar: php artisan serve"
fi

echo ""
print_success "Deploy finalizado! 🎉"
