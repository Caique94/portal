# PATCH GENERATOR - PowerShell Script
# Para Windows PowerShell
# Uso: .\generate-patch.ps1 -BranchName "main" -AutoCommit

param(
    [Parameter(Mandatory=$true)]
    [string]$BranchName,

    [Parameter(Mandatory=$false)]
    [switch]$AutoCommit
)

# Cores para output
$green = "`e[32m"
$red = "`e[31m"
$yellow = "`e[33m"
$blue = "`e[34m"
$reset = "`e[0m"

function Write-Success { Write-Host "$green✅ $args$reset" }
function Write-Error-Custom { Write-Host "$red❌ $args$reset" }
function Write-Warning-Custom { Write-Host "$yellow⚠️  $args$reset" }
function Write-Info { Write-Host "$blue ℹ️  $args$reset" }

function Write-Header {
    Write-Host "$blue═══════════════════════════════════════════════════════════════$reset"
    Write-Host "$blue$args$reset"
    Write-Host "$blue═══════════════════════════════════════════════════════════════$reset"
    Write-Host ""
}

# Verificar se estamos em um repositório Git
try {
    $null = git rev-parse --git-dir 2>&1
} catch {
    Write-Error-Custom "Não estou em um repositório Git!"
    exit 1
}

Write-Header "GERADOR DE PATCH"

# Mostrar informações da branch atual
$currentBranch = git rev-parse --abbrev-ref HEAD
$currentCommit = git rev-parse --short HEAD

Write-Info "Branch atual: $currentBranch"
Write-Info "Commit: $currentCommit"
Write-Host ""

# Verificar se há mudanças não comitadas
$hasChanges = git diff-index --quiet HEAD -- 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Warning-Custom "Existem mudanças não comitadas!"
    Write-Host ""
    $response = Read-Host "Deseja continuar mesmo assim? (s/n)"
    if ($response -ne "s") {
        Write-Error-Custom "Operação cancelada"
        exit 1
    }
}

# Se -AutoCommit foi passado, fazer commit automático
if ($AutoCommit) {
    Write-Info "Fazendo auto-commit..."
    git add .
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    git commit -m "patch: Auto-commit before patch generation - $timestamp" -ErrorAction SilentlyContinue
    Write-Host ""
}

# Obter diretórios
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir

# Gerar patch usando o script PHP
Write-Info "Gerando patch..."
Write-Host ""

Set-Location $projectRoot

# Verificar se PHP está disponível
if (!(Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Error-Custom "PHP não encontrado! Certifique-se de que PHP está instalado e no PATH"
    exit 1
}

& php "$scriptDir\generate-patch.php" $BranchName

if ($LASTEXITCODE -eq 0) {
    Write-Header "PRÓXIMAS ETAPAS"
    Write-Host ""
    Write-Info "1. Revisar o arquivo .patches/generated/patch_*.zip"
    Write-Info "2. Fazer review do código (PATCH_MANIFEST.md)"
    Write-Info "3. Para deploy, use: git push origin $currentBranch"
    Write-Info "4. Criar Pull Request se necessário"
    Write-Host ""

    Write-Success "Patch gerado com sucesso!"
} else {
    Write-Error-Custom "Erro ao gerar patch"
    exit 1
}
