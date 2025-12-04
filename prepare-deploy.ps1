# ==================================================
# Script de Preparação para Deploy - Hostinger
# sistemasemteste.com.br
# ==================================================

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Preparação para Deploy - Hostinger   " -ForegroundColor Cyan
Write-Host "  sistemasemteste.com.br                " -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se está no diretório correto
if (-not (Test-Path "artisan")) {
    Write-Host "ERRO: Este script deve ser executado na raiz do projeto Laravel!" -ForegroundColor Red
    exit 1
}

Write-Host "[1/8] Verificando ambiente..." -ForegroundColor Yellow

# Verificar se composer está instalado
try {
    $composerVersion = composer --version 2>&1
    Write-Host "  Composer encontrado" -ForegroundColor Green
} catch {
    Write-Host "  ERRO: Composer não está instalado!" -ForegroundColor Red
    exit 1
}

# Verificar se npm está instalado
try {
    $npmVersion = npm --version 2>&1
    Write-Host "  npm encontrado" -ForegroundColor Green
    $hasNpm = $true
} catch {
    Write-Host "  AVISO: npm não encontrado. Assets não serão compilados." -ForegroundColor Yellow
    $hasNpm = $false
}

Write-Host ""
Write-Host "[2/8] Limpando caches..." -ForegroundColor Yellow

# Limpar caches do Laravel
php artisan config:clear 2>&1 | Out-Null
php artisan cache:clear 2>&1 | Out-Null
php artisan view:clear 2>&1 | Out-Null
php artisan route:clear 2>&1 | Out-Null

Write-Host "  Caches limpos" -ForegroundColor Green

Write-Host ""
Write-Host "[3/8] Instalando dependências do Composer..." -ForegroundColor Yellow

# Instalar dependências de produção
composer install --optimize-autoloader --no-dev

Write-Host "  Dependências instaladas" -ForegroundColor Green

Write-Host ""
Write-Host "[4/8] Compilando assets..." -ForegroundColor Yellow

if ($hasNpm) {
    # Instalar dependências do npm
    npm install

    # Compilar para produção
    npm run build

    Write-Host "  Assets compilados" -ForegroundColor Green
} else {
    Write-Host "  Pulado (npm não disponível)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[5/8] Criando pasta de deploy..." -ForegroundColor Yellow

# Criar pasta deploy-hostinger
$deployFolder = "deploy-hostinger"
if (Test-Path $deployFolder) {
    Remove-Item -Path $deployFolder -Recurse -Force
}
New-Item -ItemType Directory -Path $deployFolder | Out-Null
New-Item -ItemType Directory -Path "$deployFolder\portal" | Out-Null
New-Item -ItemType Directory -Path "$deployFolder\public_html" | Out-Null

Write-Host "  Pasta criada: $deployFolder\" -ForegroundColor Green

Write-Host ""
Write-Host "[6/8] Copiando arquivos do Laravel..." -ForegroundColor Yellow

# Copiar pastas do Laravel (EXCETO public)
$laravelFolders = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "resources",
    "routes",
    "storage",
    "vendor"
)

foreach ($folder in $laravelFolders) {
    Write-Host "  Copiando $folder\" -ForegroundColor Gray
    Copy-Item -Path $folder -Destination "$deployFolder\portal\$folder" -Recurse
}

# Copiar arquivos raiz do Laravel
$laravelFiles = @(
    "artisan",
    "composer.json",
    "composer.lock",
    "package.json",
    "package-lock.json"
)

foreach ($file in $laravelFiles) {
    if (Test-Path $file) {
        Copy-Item -Path $file -Destination "$deployFolder\portal\$file"
    }
}

Write-Host "  Arquivos do Laravel copiados" -ForegroundColor Green

Write-Host ""
Write-Host "[7/8] Copiando arquivos public..." -ForegroundColor Yellow

# Copiar conteúdo de public/ para public_html/
$publicItems = Get-ChildItem -Path "public" -Recurse
foreach ($item in $publicItems) {
    $destination = $item.FullName -replace [regex]::Escape("public"), "$deployFolder\public_html"

    if ($item.PSIsContainer) {
        if (-not (Test-Path $destination)) {
            New-Item -ItemType Directory -Path $destination -Force | Out-Null
        }
    } else {
        Copy-Item -Path $item.FullName -Destination $destination -Force
    }
}

Write-Host "  Arquivos public copiados" -ForegroundColor Green

Write-Host ""
Write-Host "[8/8] Criando .env de exemplo..." -ForegroundColor Yellow

# Copiar .env.hostinger para a pasta deploy
if (Test-Path ".env.hostinger") {
    Copy-Item -Path ".env.hostinger" -Destination "$deployFolder\portal\.env.example"
    Write-Host "  .env.example criado (lembre-se de preencher as credenciais!)" -ForegroundColor Green
} else {
    Write-Host "  AVISO: .env.hostinger não encontrado" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  PREPARAÇÃO CONCLUÍDA COM SUCESSO!    " -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Estrutura criada em: $deployFolder\" -ForegroundColor Green
Write-Host ""
Write-Host "Próximos passos:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Fazer upload via FTP/SFTP:" -ForegroundColor White
Write-Host "   - Upload de '$deployFolder\portal\' para:" -ForegroundColor Gray
Write-Host "     /home/u123456789/domains/sistemasemteste.com.br/portal/" -ForegroundColor Gray
Write-Host ""
Write-Host "   - Upload de '$deployFolder\public_html\' para:" -ForegroundColor Gray
Write-Host "     /home/u123456789/public_html/" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Criar .env no servidor (copiar de .env.example)" -ForegroundColor White
Write-Host ""
Write-Host "3. Ajustar index.php conforme DEPLOY_SISTEMASEMTESTE.md" -ForegroundColor White
Write-Host ""
Write-Host "4. Rodar migrations via SSH:" -ForegroundColor White
Write-Host "   php artisan migrate --force" -ForegroundColor Gray
Write-Host ""
Write-Host "5. Ajustar permissões:" -ForegroundColor White
Write-Host "   chmod -R 775 storage bootstrap/cache" -ForegroundColor Gray
Write-Host "   chmod 600 .env" -ForegroundColor Gray
Write-Host ""
Write-Host "Consulte DEPLOY_SISTEMASEMTESTE.md para instruções detalhadas." -ForegroundColor Cyan
Write-Host ""

# Criar arquivo de instruções
$instructionsFile = "$deployFolder\LEIA-ME.txt"
$instructions = @"
==================================================
INSTRUÇÕES DE DEPLOY - sistemasemteste.com.br
==================================================

Esta pasta contém os arquivos preparados para upload na Hostinger.

ESTRUTURA:
----------
- portal/        → Upload para: /home/u123456789/domains/sistemasemteste.com.br/portal/
- public_html/   → Upload para: /home/u123456789/public_html/

CREDENCIAIS FTP:
----------------
Host: ftp.sistemasemteste.com.br
Port: 22 (SFTP) ou 21 (FTP)
User: u123456789 (verificar no hPanel)
Pass: [sua senha]

PASSOS APÓS UPLOAD:
-------------------
1. Criar .env no servidor
   - Copiar de portal/.env.example
   - Preencher credenciais do banco (MySQL)
   - Preencher credenciais de email

2. Ajustar public_html/index.php
   - Apontar caminhos para ../domains/sistemasemteste.com.br/portal/

3. Via SSH, rodar:
   cd ~/domains/sistemasemteste.com.br/portal
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   chmod -R 775 storage bootstrap/cache
   chmod 600 .env

4. Testar:
   https://sistemasemteste.com.br

DOCUMENTAÇÃO COMPLETA:
----------------------
Consulte: DEPLOY_SISTEMASEMTESTE.md

Data de preparação: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
"@

Set-Content -Path $instructionsFile -Value $instructions -Encoding UTF8

Write-Host "Arquivo de instruções criado: $instructionsFile" -ForegroundColor Green
Write-Host ""
Write-Host "Pressione qualquer tecla para sair..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
