#!/bin/bash

# ============================================
# Deploy do Patch: Produto Presencial
# sistemasemteste.com.br
# Data: 2025-12-08
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
echo "  Deploy Patch: Produto Presencial Feature "
echo "  sistemasemteste.com.br                   "
echo "  Data: 2025-12-08                         "
echo "============================================"
echo -e "${NC}"

# ============================================
# CONFIGURAÇÕES
# ============================================

# Diretório da aplicação no servidor
APP_DIR="/var/www/sistemasemteste.com.br"

# Banco de dados (deve ser 'portal' em produção)
DB_NAME="portal"

# Servidor web
WEB_SERVER="nginx"  # ou apache2

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

# Verificar se diretório existe
if [ ! -d "$APP_DIR" ]; then
    log_error "Diretório da aplicação não encontrado: $APP_DIR"
    exit 1
fi

log_success "Diretório encontrado: $APP_DIR"

# Verificar se arquivo artisan existe
if [ ! -f "$APP_DIR/artisan" ]; then
    log_error "Arquivo artisan não encontrado. Diretório incorreto?"
    exit 1
fi

log_success "Aplicação Laravel encontrada"

# ============================================
# 2. BACKUP DO BANCO DE DADOS
# ============================================

log_step "2. Fazendo Backup do Banco de Dados"

BACKUP_DIR="$APP_DIR/.backups"
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"

log_info "Criando backup do banco '$DB_NAME'..."

# Backup do banco de dados
sudo -u postgres pg_dump $DB_NAME > "$BACKUP_DIR/db_backup_$BACKUP_DATE.sql"

if [ -f "$BACKUP_DIR/db_backup_$BACKUP_DATE.sql" ]; then
    log_success "Backup do banco criado: $BACKUP_DIR/db_backup_$BACKUP_DATE.sql"
else
    log_error "Falha ao criar backup do banco!"
    read -p "Continuar mesmo assim? (s/n): " CONTINUE
    if [ "$CONTINUE" != "s" ]; then
        exit 1
    fi
fi

# ============================================
# 3. BACKUP DOS ARQUIVOS
# ============================================

log_step "3. Fazendo Backup dos Arquivos"

log_info "Criando backup dos arquivos que serão modificados..."

# Lista de arquivos que serão modificados
FILES_TO_BACKUP=(
    "app/Http/Controllers/ProdutoController.php"
    "app/Http/Controllers/ProdutoTabelaController.php"
    "app/Models/PagamentoUsuario.php"
    "resources/views/cadastros/produtos.blade.php"
    "public/js/cadastros/produtos.js"
    "public/js/cadastros/usuarios.js"
    "public/js/ordem-servico.js"
)

cd "$APP_DIR"

# Criar arquivo tar com backup
tar -czf "$BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz" \
    "${FILES_TO_BACKUP[@]}" \
    2>/dev/null || log_warning "Alguns arquivos não existiam no backup"

log_success "Backup de arquivos criado: $BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz"

# ============================================
# 4. VERIFICAR SE PATCH FOI ENVIADO
# ============================================

log_step "4. Verificando Patch"

PATCH_DIR="/tmp/patch_20251208_produto_presencial"

if [ ! -d "$PATCH_DIR" ]; then
    log_error "Patch não encontrado em: $PATCH_DIR"
    echo ""
    log_info "Para enviar o patch do Windows para o servidor, execute:"
    echo "  scp -r .patches/patch_20251208_produto_presencial root@IP_DO_SERVIDOR:/tmp/"
    echo ""
    log_info "Ou extraia o arquivo .tar.gz:"
    echo "  scp .patches/patch_20251208_produto_presencial.tar.gz root@IP_DO_SERVIDOR:/tmp/"
    echo "  ssh root@IP_DO_SERVIDOR"
    echo "  cd /tmp && tar -xzf patch_20251208_produto_presencial.tar.gz"
    echo ""
    exit 1
fi

log_success "Patch encontrado: $PATCH_DIR"

# ============================================
# 5. APLICAR ARQUIVOS DO PATCH
# ============================================

log_step "5. Aplicando Arquivos do Patch"

log_info "Copiando arquivos modificados..."

# Copiar controllers
cp -v "$PATCH_DIR/app/Http/Controllers/ProdutoController.php" "$APP_DIR/app/Http/Controllers/"
cp -v "$PATCH_DIR/app/Http/Controllers/ProdutoTabelaController.php" "$APP_DIR/app/Http/Controllers/"

# Copiar model
cp -v "$PATCH_DIR/app/Models/PagamentoUsuario.php" "$APP_DIR/app/Models/"

# Copiar view
cp -v "$PATCH_DIR/resources/views/cadastros/produtos.blade.php" "$APP_DIR/resources/views/cadastros/"

# Copiar JavaScript
mkdir -p "$APP_DIR/public/js/cadastros"
cp -v "$PATCH_DIR/public/js/cadastros/produtos.js" "$APP_DIR/public/js/cadastros/"
cp -v "$PATCH_DIR/public/js/cadastros/usuarios.js" "$APP_DIR/public/js/cadastros/"
cp -v "$PATCH_DIR/public/js/ordem-servico.js" "$APP_DIR/public/js/"

# Copiar migration
cp -v "$PATCH_DIR/database/migrations/2025_12_08_140803_add_is_presencial_to_produto_table.php" "$APP_DIR/database/migrations/"

# Copiar scripts auxiliares
mkdir -p "$APP_DIR/scripts"
cp -v "$PATCH_DIR/scripts/check_produto_columns.php" "$APP_DIR/scripts/" || true
cp -v "$PATCH_DIR/scripts/fix_portal_db.php" "$APP_DIR/scripts/" || true

log_success "Arquivos copiados com sucesso"

# ============================================
# 6. AJUSTAR PERMISSÕES
# ============================================

log_step "6. Ajustando Permissões"

cd "$APP_DIR"

chown -R www-data:www-data app/
chown -R www-data:www-data resources/
chown -R www-data:www-data public/
chown -R www-data:www-data database/

chmod -R 755 app/
chmod -R 755 resources/
chmod -R 755 public/
chmod -R 755 database/

log_success "Permissões ajustadas"

# ============================================
# 7. VERIFICAR BANCO DE DADOS CONECTADO
# ============================================

log_step "7. Verificando Conexão com Banco de Dados"

log_info "Verificando banco de dados configurado no .env..."

# Verificar qual banco está no .env
DB_CONFIGURED=$(grep "^DB_DATABASE=" "$APP_DIR/.env" | cut -d'=' -f2)

if [ "$DB_CONFIGURED" != "$DB_NAME" ]; then
    log_error "ATENÇÃO! .env está configurado para banco: $DB_CONFIGURED"
    log_error "Mas deveria ser: $DB_NAME"

    read -p "Deseja corrigir o .env agora? (s/n): " FIX_ENV
    if [ "$FIX_ENV" == "s" ]; then
        sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" "$APP_DIR/.env"
        log_success ".env corrigido para usar banco: $DB_NAME"
    else
        log_warning "Continuando sem corrigir..."
    fi
else
    log_success "Banco de dados correto no .env: $DB_NAME"
fi

# ============================================
# 8. APLICAR MIGRATION
# ============================================

log_step "8. Aplicando Migration"

cd "$APP_DIR"

log_info "Executando migration para adicionar coluna is_presencial..."

php artisan migrate --force

log_success "Migration aplicada"

# ============================================
# 9. VERIFICAR COLUNA NO BANCO
# ============================================

log_step "9. Verificando Coluna no Banco"

log_info "Verificando se coluna is_presencial foi criada..."

# Usar script PHP se disponível
if [ -f "$APP_DIR/scripts/check_produto_columns.php" ]; then
    log_info "Executando script de verificação..."
    php "$APP_DIR/scripts/check_produto_columns.php"
else
    # Verificar diretamente via SQL
    HAS_COLUMN=$(sudo -u postgres psql -d $DB_NAME -tAc "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'produto' AND column_name = 'is_presencial';")

    if [ "$HAS_COLUMN" -eq "1" ]; then
        log_success "✅ Coluna is_presencial existe no banco '$DB_NAME'"
    else
        log_error "❌ Coluna is_presencial NÃO existe no banco '$DB_NAME'"
        log_warning "Tentando criar manualmente..."

        sudo -u postgres psql -d $DB_NAME -c "ALTER TABLE produto ADD COLUMN IF NOT EXISTS is_presencial BOOLEAN DEFAULT FALSE;"

        log_success "Coluna criada manualmente"
    fi
fi

# ============================================
# 10. ADICIONAR ROTA (se necessário)
# ============================================

log_step "10. Verificando Rotas"

log_info "Verificando se rota /produto-tabela/{id} existe..."

if grep -q "produto-tabela/{id}" "$APP_DIR/routes/web.php"; then
    log_success "Rota já existe em routes/web.php"
else
    log_warning "Rota não encontrada!"
    log_info "Você precisa adicionar manualmente em routes/web.php:"
    echo ""
    echo "Route::get('/produto-tabela/{id}', [ProdutoTabelaController::class, 'show']);"
    echo ""
    read -p "Pressione Enter para continuar..."
fi

# ============================================
# 11. LIMPAR CACHES
# ============================================

log_step "11. Limpando Caches"

cd "$APP_DIR"

log_info "Limpando todos os caches..."

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

log_success "Caches limpos"

log_info "Recriando caches otimizados..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

log_success "Caches otimizados"

# ============================================
# 12. REINICIAR SERVIÇOS
# ============================================

log_step "12. Reiniciando Serviços"

log_info "Reiniciando $WEB_SERVER..."

if systemctl is-active --quiet nginx; then
    systemctl restart nginx
    systemctl restart php8.3-fpm
    log_success "Nginx e PHP-FPM reiniciados"
elif systemctl is-active --quiet apache2; then
    systemctl restart apache2
    log_success "Apache reiniciado"
else
    log_warning "Servidor web não identificado"
fi

# ============================================
# 13. TESTES AUTOMÁTICOS
# ============================================

log_step "13. Executando Testes"

log_info "Verificando se aplicação está respondendo..."

# Testar se o site responde
if command -v curl &> /dev/null; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)

    if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "302" ]; then
        log_success "Aplicação está respondendo (HTTP $HTTP_CODE)"
    else
        log_warning "Aplicação retornou HTTP $HTTP_CODE"
    fi
else
    log_info "curl não disponível, pulando teste HTTP"
fi

# ============================================
# RESUMO FINAL
# ============================================

log_step "DEPLOY CONCLUÍDO!"

echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  PATCH APLICADO COM SUCESSO!           ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""

echo -e "${CYAN}📋 Resumo das Alterações:${NC}"
echo ""
echo "✅ Coluna is_presencial adicionada à tabela produto"
echo "✅ Controllers atualizados (Produto, ProdutoTabela)"
echo "✅ Model PagamentoUsuario corrigido"
echo "✅ View de produtos atualizada (checkbox presencial)"
echo "✅ JavaScript atualizado (produtos, usuarios, ordem-servico)"
echo "✅ Migration aplicada"
echo "✅ Caches limpos e otimizados"
echo "✅ Serviços reiniciados"
echo ""

echo -e "${CYAN}💾 Backups Criados:${NC}"
echo ""
echo "📁 Banco de dados: $BACKUP_DIR/db_backup_$BACKUP_DATE.sql"
echo "📁 Arquivos: $BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz"
echo ""

echo -e "${CYAN}🧪 Testes a Realizar:${NC}"
echo ""
echo "1. Acesse: https://sistemasemteste.com.br/produtos"
echo "2. Teste cadastrar novo produto:"
echo "   - Verificar checkbox 'Presencial'"
echo "   - Salvar produto marcado como presencial"
echo "   - Verificar badge 'Sim' na listagem"
echo "3. Teste editar produto existente:"
echo "   - Clicar no botão de editar (lápis)"
echo "   - Alterar status presencial"
echo "   - Salvar e verificar atualização"
echo "4. Teste na Ordem de Serviço:"
echo "   - Criar nova OS"
echo "   - Selecionar produto marcado como presencial"
echo "   - Verificar checkbox presencial automaticamente marcado"
echo "   - Verificar cálculo de deslocamento/km"
echo "5. Verificar logs:"
echo "   tail -f $APP_DIR/storage/logs/laravel.log"
echo ""

echo -e "${CYAN}🔄 Rollback (se necessário):${NC}"
echo ""
echo "# Restaurar banco de dados:"
echo "sudo -u postgres psql $DB_NAME < $BACKUP_DIR/db_backup_$BACKUP_DATE.sql"
echo ""
echo "# Restaurar arquivos:"
echo "cd $APP_DIR && tar -xzf $BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz"
echo ""
echo "# Remover migration:"
echo "php artisan migrate:rollback --step=1"
echo ""

echo -e "${CYAN}📊 Comandos Úteis:${NC}"
echo ""
echo "# Ver logs em tempo real:"
echo "tail -f $APP_DIR/storage/logs/laravel.log"
echo ""
echo "# Verificar coluna no banco:"
echo "sudo -u postgres psql -d $DB_NAME -c \"SELECT column_name FROM information_schema.columns WHERE table_name = 'produto' AND column_name = 'is_presencial';\""
echo ""
echo "# Listar produtos com is_presencial:"
echo "sudo -u postgres psql -d $DB_NAME -c \"SELECT id, codigo, is_presencial FROM produto LIMIT 10;\""
echo ""
echo "# Reiniciar serviços:"
echo "systemctl restart nginx php8.3-fpm"
echo ""

echo -e "${GREEN}✨ Deploy do patch concluído! Bom trabalho! 🚀${NC}"
echo ""
