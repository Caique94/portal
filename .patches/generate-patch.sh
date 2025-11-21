#!/bin/bash

# PATCH GENERATOR - Script Shell
# Facilita geração de patches antes do deploy

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funções utilitárias
print_header() {
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
    echo ""
}

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
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Verificar argumentos
if [ -z "$1" ]; then
    echo "Uso: bash generate-patch.sh [branch-name] [--auto-commit]"
    echo ""
    echo "Exemplos:"
    echo "  bash generate-patch.sh main"
    echo "  bash generate-patch.sh feature/rps-filtro --auto-commit"
    echo ""
    exit 1
fi

BRANCH_NAME="$1"
AUTO_COMMIT="${2:-}"

# Verificar se estamos em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    print_error "Não estou em um repositório Git!"
    exit 1
fi

print_header "GERADOR DE PATCH"

# Mostrar informações da branch atual
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
CURRENT_COMMIT=$(git rev-parse --short HEAD)

print_info "Branch atual: $CURRENT_BRANCH"
print_info "Commit: $CURRENT_COMMIT"
echo ""

# Verificar se há mudanças não comitadas
if ! git diff-index --quiet HEAD --; then
    print_warning "Existem mudanças não comitadas!"
    echo ""
    read -p "Deseja continuar mesmo assim? (s/n) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        print_error "Operação cancelada"
        exit 1
    fi
fi

# Se --auto-commit foi passado, fazer commit automático
if [ "$AUTO_COMMIT" = "--auto-commit" ]; then
    print_info "Fazendo auto-commit..."
    git add .
    git commit -m "patch: Auto-commit before patch generation - $(date +'%Y-%m-%d %H:%M:%S')" || true
    echo ""
fi

# Gerar patch usando o script PHP
print_info "Gerando patch..."
echo ""

cd "$PROJECT_ROOT"

if command -v php &> /dev/null; then
    php "$SCRIPT_DIR/generate-patch.php" "$BRANCH_NAME"
else
    print_error "PHP não encontrado! Certifique-se de que PHP está instalado e no PATH"
    exit 1
fi

print_header "PRÓXIMAS ETAPAS"
echo ""
print_info "1. Revisar o arquivo .patches/generated/patch_*.zip"
print_info "2. Fazer review do código (PATCH_MANIFEST.md)"
print_info "3. Para deploy, use: git push origin $CURRENT_BRANCH"
print_info "4. Criar Pull Request se necessário"
echo ""

print_success "Patch gerado com sucesso!"
