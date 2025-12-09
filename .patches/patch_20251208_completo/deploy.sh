#!/bin/bash

# ============================================
# Deploy Completo - Patch 08/12/2025
# Todas as alterações do dia
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
echo "  Deploy Completo - Patch 08/12/2025       "
echo "  Todas as Alterações do Dia               "
echo "============================================"
echo -e "${NC}"

# ============================================
# CONFIGURAÇÕES
# ============================================

APP_DIR="/var/www/sistemasemteste.com.br"
DB_NAME="portal"

# ============================================
# 1. VERIFICAÇÕES
# ============================================

log_step "1. Verificando Pré-requisitos"

if [[ $EUID -ne 0 ]]; then
   log_error "Este script precisa ser executado como root (sudo)"
   exit 1
fi

log_success "Executando como root"

if [ ! -d "$APP_DIR" ]; then
    log_error "Diretório não encontrado: $APP_DIR"
    exit 1
fi

log_success "Diretório encontrado: $APP_DIR"

# ============================================
# 2. BACKUP
# ============================================

log_step "2. Fazendo Backups"

BACKUP_DIR="$APP_DIR/.backups"
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"

# Backup do banco
log_info "Backup do banco de dados '$DB_NAME'..."
sudo -u postgres pg_dump $DB_NAME > "$BACKUP_DIR/db_backup_$BACKUP_DATE.sql"
log_success "Backup do banco criado"

# Backup dos arquivos
log_info "Backup dos arquivos..."
tar -czf "$BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz" \
    -C "$APP_DIR" \
    app/Http/Controllers/ProdutoController.php \
    app/Http/Controllers/ProdutoTabelaController.php \
    app/Models/Produto.php \
    app/Models/PagamentoUsuario.php \
    public/js/cadastros/produtos.js \
    public/js/cadastros/usuarios.js \
    public/js/ordem-servico.js \
    resources/views/cadastros/produtos.blade.php \
    2>/dev/null || log_warning "Alguns arquivos não existiam"

log_success "Backup de arquivos criado"

# ============================================
# 3. APLICAR ARQUIVOS
# ============================================

log_step "3. Aplicando Arquivos do Patch"

PATCH_DIR=$(dirname "$0")

log_info "Copiando controllers..."
cp -v "$PATCH_DIR/app/Http/Controllers/ProdutoController.php" "$APP_DIR/app/Http/Controllers/"
cp -v "$PATCH_DIR/app/Http/Controllers/ProdutoTabelaController.php" "$APP_DIR/app/Http/Controllers/"

log_info "Copiando models..."
cp -v "$PATCH_DIR/app/Models/Produto.php" "$APP_DIR/app/Models/"
cp -v "$PATCH_DIR/app/Models/PagamentoUsuario.php" "$APP_DIR/app/Models/"

log_info "Copiando JavaScript..."
cp -v "$PATCH_DIR/public/js/cadastros/produtos.js" "$APP_DIR/public/js/cadastros/"
cp -v "$PATCH_DIR/public/js/cadastros/usuarios.js" "$APP_DIR/public/js/cadastros/"
cp -v "$PATCH_DIR/public/js/ordem-servico.js" "$APP_DIR/public/js/"

log_info "Copiando views..."
cp -v "$PATCH_DIR/resources/views/cadastros/produtos.blade.php" "$APP_DIR/resources/views/cadastros/"

log_info "Copiando migration..."
cp -v "$PATCH_DIR/database/migrations/2025_12_08_140803_add_is_presencial_to_produto_table.php" "$APP_DIR/database/migrations/"

log_success "Arquivos copiados com sucesso"

# ============================================
# 4. APLICAR MIGRATION
# ============================================

log_step "4. Aplicando Migration"

cd "$APP_DIR"

log_info "Executando migration..."
php artisan migrate --force

log_success "Migration aplicada"

# ============================================
# 5. VERIFICAR BANCO
# ============================================

log_step "5. Verificando Banco de Dados"

HAS_COLUMN=$(sudo -u postgres psql -d $DB_NAME -tAc "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'produto' AND column_name = 'is_presencial';")

if [ "$HAS_COLUMN" -eq "1" ]; then
    log_success "✅ Coluna is_presencial existe no banco '$DB_NAME'"
else
    log_error "❌ Coluna is_presencial NÃO existe!"
    log_warning "Tentando criar manualmente..."
    sudo -u postgres psql -d $DB_NAME -c "ALTER TABLE produto ADD COLUMN IF NOT EXISTS is_presencial BOOLEAN DEFAULT FALSE;"
    log_success "Coluna criada"
fi

# ============================================
# 6. VERIFICAR ROTA
# ============================================

log_step "6. Verificando Rotas"

if grep -q "produto-tabela/{id}" "$APP_DIR/routes/web.php"; then
    log_success "Rota /produto-tabela/{id} existe"
else
    log_warning "Rota /produto-tabela/{id} NÃO encontrada!"
    log_info "Adicione manualmente em routes/web.php:"
    echo "Route::get('/produto-tabela/{id}', [ProdutoTabelaController::class, 'show']);"
    read -p "Pressione Enter para continuar..."
fi

# ============================================
# 7. LIMPAR CACHES
# ============================================

log_step "7. Limpando Caches"

cd "$APP_DIR"

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

log_info "Recriando caches otimizados..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

log_success "Caches atualizados"

# ============================================
# 8. AJUSTAR PERMISSÕES
# ============================================

log_step "8. Ajustando Permissões"

chown -R www-data:www-data "$APP_DIR/app/"
chown -R www-data:www-data "$APP_DIR/public/"
chown -R www-data:www-data "$APP_DIR/resources/"
chmod -R 755 "$APP_DIR/app/"
chmod -R 755 "$APP_DIR/public/"
chmod -R 755 "$APP_DIR/resources/"

log_success "Permissões ajustadas"

# ============================================
# 9. REINICIAR SERVIÇOS
# ============================================

log_step "9. Reiniciando Serviços"

if systemctl is-active --quiet nginx; then
    systemctl restart nginx
    systemctl restart php8.3-fpm
    log_success "Nginx e PHP-FPM reiniciados"
elif systemctl is-active --quiet apache2; then
    systemctl restart apache2
    log_success "Apache reiniciado"
fi

# ============================================
# RESUMO FINAL
# ============================================

log_step "DEPLOY CONCLUÍDO!"

echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  PATCH COMPLETO APLICADO!              ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""

echo -e "${CYAN}📋 Alterações Aplicadas:${NC}"
echo ""
echo "✅ Feature: Campo is_presencial vinculado ao produto"
echo "✅ Bug Fix: Formatação de valor (zero extra)"
echo "✅ Bug Fix: Cálculo de KM (multiplica por tarifa)"
echo "✅ Bug Fix: Cálculo de Deslocamento (multiplica por hora)"
echo "✅ Bug Fix: Checkbox presencial automático"
echo "✅ Bug Fix: Modelo Produto com fillable e casts"
echo "✅ Bug Fix: Campos KM/Deslocamento aparecem/desaparecem"
echo "✅ Bug Fix: Totalizador condicional"
echo ""

echo -e "${CYAN}💾 Backups Criados:${NC}"
echo ""
echo "📁 Banco: $BACKUP_DIR/db_backup_$BACKUP_DATE.sql"
echo "📁 Arquivos: $BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz"
echo ""

echo -e "${CYAN}🧪 Testes Obrigatórios:${NC}"
echo ""
echo "1. Formatação de Valor:"
echo "   - Listar OS → verificar R\$ 730,00 (não 70.030,00)"
echo ""
echo "2. Produto Presencial - Cadastro:"
echo "   - Criar produto → marcar presencial → badge 'Sim'"
echo ""
echo "3. Produto Presencial - OS Automática:"
echo "   - Criar OS → selecionar produto presencial"
echo "   - Checkbox marcado e desabilitado automaticamente"
echo "   - Campos KM/Deslocamento aparecem"
echo ""
echo "4. Cálculo de KM:"
echo "   - Cliente: 44 km, Consultor: R\$ 1,50/km"
echo "   - Verificar total KM = R\$ 66,00"
echo ""
echo "5. Cálculo de Deslocamento:"
echo "   - Cliente: 1:20, Consultor: R\$ 48,00/hora"
echo "   - Verificar total = R\$ 64,00"
echo ""
echo "6. Console do Navegador (F12):"
echo "   - Verificar mensagens de debug"
echo "   - Verificar is_presencial está sendo retornado"
echo ""

echo -e "${CYAN}🔄 Rollback (se necessário):${NC}"
echo ""
echo "# Reverter migration:"
echo "php artisan migrate:rollback --step=1"
echo ""
echo "# Reverter arquivos:"
echo "tar -xzf $BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz -C $APP_DIR/"
echo ""
echo "# Reverter banco:"
echo "sudo -u postgres psql $DB_NAME < $BACKUP_DIR/db_backup_$BACKUP_DATE.sql"
echo ""

echo -e "${GREEN}✨ Deploy concluído! Bom trabalho! 🚀${NC}"
echo ""
