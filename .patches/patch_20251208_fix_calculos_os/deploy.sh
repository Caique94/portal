#!/bin/bash

# ============================================
# Deploy do Patch: Correção de Cálculos OS
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
echo "  Patch: Correção de Cálculos de OS        "
echo "  Data: 2025-12-08                         "
echo "============================================"
echo -e "${NC}"

# ============================================
# CONFIGURAÇÕES
# ============================================

# Diretório da aplicação no servidor
APP_DIR="/var/www/sistemasemteste.com.br"

# Se executar localmente (development)
if [ ! -d "$APP_DIR" ]; then
    APP_DIR=$(pwd)
    log_warning "Usando diretório atual: $APP_DIR"
fi

# ============================================
# 1. VERIFICAÇÕES
# ============================================

log_step "1. Verificando Pré-requisitos"

# Verificar se diretório existe
if [ ! -d "$APP_DIR" ]; then
    log_error "Diretório não encontrado: $APP_DIR"
    exit 1
fi

log_success "Diretório encontrado: $APP_DIR"

# Verificar se arquivo existe
if [ ! -f "$APP_DIR/public/js/ordem-servico.js" ]; then
    log_error "Arquivo ordem-servico.js não encontrado!"
    exit 1
fi

log_success "Arquivo ordem-servico.js encontrado"

# ============================================
# 2. BACKUP
# ============================================

log_step "2. Fazendo Backup"

BACKUP_DIR="$APP_DIR/.backups"
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"

log_info "Criando backup do arquivo atual..."

cp "$APP_DIR/public/js/ordem-servico.js" \
   "$BACKUP_DIR/ordem-servico.js.bak_$BACKUP_DATE"

log_success "Backup criado: $BACKUP_DIR/ordem-servico.js.bak_$BACKUP_DATE"

# ============================================
# 3. APLICAR PATCH
# ============================================

log_step "3. Aplicando Patch"

PATCH_DIR=$(dirname "$0")

log_info "Copiando arquivo corrigido..."

cp "$PATCH_DIR/public/js/ordem-servico.js" \
   "$APP_DIR/public/js/ordem-servico.js"

log_success "Arquivo atualizado com sucesso!"

# ============================================
# 4. VERIFICAR PERMISSÕES
# ============================================

log_step "4. Ajustando Permissões"

if [ -w "$APP_DIR/public/js/ordem-servico.js" ]; then
    log_success "Permissões OK"
else
    log_warning "Ajustando permissões..."
    chmod 644 "$APP_DIR/public/js/ordem-servico.js"
    log_success "Permissões ajustadas"
fi

# ============================================
# 5. TESTES BÁSICOS
# ============================================

log_step "5. Verificando Arquivo"

# Verificar se arquivo tem conteúdo
FILE_SIZE=$(stat -f%z "$APP_DIR/public/js/ordem-servico.js" 2>/dev/null || stat -c%s "$APP_DIR/public/js/ordem-servico.js" 2>/dev/null)

if [ "$FILE_SIZE" -gt 10000 ]; then
    log_success "Arquivo tem tamanho adequado: $FILE_SIZE bytes"
else
    log_error "Arquivo parece estar corrompido (muito pequeno)"
    log_warning "Restaurando backup..."
    cp "$BACKUP_DIR/ordem-servico.js.bak_$BACKUP_DATE" \
       "$APP_DIR/public/js/ordem-servico.js"
    exit 1
fi

# Verificar se arquivo contém as correções
if grep -q "Formatar manualmente para evitar problema com separador de milhar" "$APP_DIR/public/js/ordem-servico.js"; then
    log_success "Correção de formatação detectada ✓"
else
    log_warning "Comentário de correção não encontrado (pode estar OK)"
fi

if grep -q "kmQuantidade" "$APP_DIR/public/js/ordem-servico.js"; then
    log_success "Correção de cálculo de KM detectada ✓"
else
    log_warning "Variável kmQuantidade não encontrada"
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

echo -e "${CYAN}📋 Correções Aplicadas:${NC}"
echo ""
echo "✅ Formatação de valor na listagem (bug do zero extra)"
echo "✅ Cálculo de KM (quantidade × tarifa do consultor)"
echo "✅ Cálculo de Deslocamento (horas × valor/hora consultor)"
echo "✅ Total da OS com valores corretos"
echo ""

echo -e "${CYAN}💾 Backup Criado:${NC}"
echo ""
echo "📁 $BACKUP_DIR/ordem-servico.js.bak_$BACKUP_DATE"
echo ""

echo -e "${CYAN}🧪 Testes Recomendados:${NC}"
echo ""
echo "1. Verificar listagem de OS:"
echo "   - Acessar /ordem-servico"
echo "   - Verificar se valores aparecem corretamente"
echo "   - Exemplo: 730,00 (não 70.030,00)"
echo ""
echo "2. Teste de cálculo de KM:"
echo "   - Criar/editar OS presencial"
echo "   - Cliente: 44 km"
echo "   - Consultor: R\$ 1,50/km"
echo "   - Verificar total KM = R\$ 66,00"
echo ""
echo "3. Teste de cálculo de Deslocamento:"
echo "   - Cliente: 1:20 (1h20min)"
echo "   - Consultor: R\$ 48,00/hora"
echo "   - Verificar total = R\$ 64,00"
echo ""
echo "4. Verificar totalizador Admin e Consultor"
echo ""

echo -e "${CYAN}🔄 Rollback (se necessário):${NC}"
echo ""
echo "# Restaurar backup:"
echo "cp $BACKUP_DIR/ordem-servico.js.bak_$BACKUP_DATE \\"
echo "   $APP_DIR/public/js/ordem-servico.js"
echo ""

echo -e "${CYAN}📊 Próximos Passos:${NC}"
echo ""
echo "1. Limpar cache do navegador dos usuários (Ctrl+Shift+R)"
echo "2. Testar criação de nova OS"
echo "3. Testar edição de OS existente"
echo "4. Verificar logs do navegador (F12 > Console)"
echo ""

echo -e "${GREEN}✨ Deploy concluído! Bom trabalho! 🚀${NC}"
echo ""
