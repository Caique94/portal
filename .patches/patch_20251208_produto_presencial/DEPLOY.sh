#!/bin/bash
# Script de Deploy - Patch Produto Presencial
# Data: 2025-12-08
# Uso: ./DEPLOY.sh [caminho_do_projeto]

set -e  # Parar em caso de erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para log
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar argumento
if [ -z "$1" ]; then
    PROJECT_PATH=$(pwd)
    log_warn "Caminho do projeto não especificado. Usando diretório atual: $PROJECT_PATH"
else
    PROJECT_PATH=$1
fi

# Verificar se diretório existe
if [ ! -d "$PROJECT_PATH" ]; then
    log_error "Diretório não encontrado: $PROJECT_PATH"
    exit 1
fi

log_info "=========================================="
log_info "Deploy do Patch: Produto Presencial"
log_info "Diretório: $PROJECT_PATH"
log_info "=========================================="
echo

# 1. Backup
log_info "Passo 1: Criando backup..."
BACKUP_DIR="$PROJECT_PATH/.backups"
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"

# Backup de arquivos
tar -czf "$BACKUP_DIR/files_before_patch_$BACKUP_DATE.tar.gz" \
    -C "$PROJECT_PATH" \
    app/Http/Controllers/ProdutoController.php \
    app/Http/Controllers/ProdutoTabelaController.php \
    app/Models/PagamentoUsuario.php \
    resources/views/cadastros/produtos.blade.php \
    public/js/cadastros/produtos.js \
    public/js/cadastros/usuarios.js \
    public/js/ordem-servico.js \
    2>/dev/null || log_warn "Alguns arquivos não existiam no backup"

log_info "Backup criado: $BACKUP_DIR/files_before_patch_$BACKUP_DATE.tar.gz"

# 2. Copiar arquivos
log_info "Passo 2: Copiando arquivos..."
cp -v app/Http/Controllers/ProdutoController.php "$PROJECT_PATH/app/Http/Controllers/"
cp -v app/Http/Controllers/ProdutoTabelaController.php "$PROJECT_PATH/app/Http/Controllers/"
cp -v app/Models/PagamentoUsuario.php "$PROJECT_PATH/app/Models/"
cp -v resources/views/cadastros/produtos.blade.php "$PROJECT_PATH/resources/views/cadastros/"
mkdir -p "$PROJECT_PATH/public/js/cadastros"
cp -v public/js/cadastros/produtos.js "$PROJECT_PATH/public/js/cadastros/"
cp -v public/js/cadastros/usuarios.js "$PROJECT_PATH/public/js/cadastros/"
cp -v public/js/ordem-servico.js "$PROJECT_PATH/public/js/"
cp -v database/migrations/2025_12_08_140803_add_is_presencial_to_produto_table.php "$PROJECT_PATH/database/migrations/"

log_info "Arquivos copiados com sucesso"

# 3. Migration
log_info "Passo 3: Aplicando migration..."
cd "$PROJECT_PATH"

if command -v php &> /dev/null; then
    php artisan migrate --force
    log_info "Migration aplicada com sucesso"
else
    log_error "PHP não encontrado. Aplique a migration manualmente:"
    log_error "  php artisan migrate"
    exit 1
fi

# 4. Limpar caches
log_info "Passo 4: Limpando caches do Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
log_info "Caches limpos"

# 5. Verificar banco
log_info "Passo 5: Verificando banco de dados..."
if [ -f "scripts/check_produto_columns.php" ]; then
    php scripts/check_produto_columns.php
else
    log_warn "Script de verificação não encontrado. Copie scripts/ para o projeto."
fi

# 6. Instruções finais
echo
log_info "=========================================="
log_info "Deploy concluído com sucesso!"
log_info "=========================================="
echo
log_warn "PRÓXIMOS PASSOS:"
echo "  1. Reinicie o servidor web (nginx/apache)"
echo "  2. Se usar php-fpm: sudo systemctl restart php8.3-fpm"
echo "  3. Teste a funcionalidade em: /produtos"
echo "  4. Verifique os logs: tail -f storage/logs/laravel.log"
echo
log_info "Backup salvo em: $BACKUP_DIR/files_before_patch_$BACKUP_DATE.tar.gz"
echo

# Checklist
echo "CHECKLIST:"
echo "  [ ] Servidor reiniciado"
echo "  [ ] Teste de cadastro de produto"
echo "  [ ] Teste de edição de produto"
echo "  [ ] Teste de OS com produto presencial"
echo "  [ ] Logs verificados"
