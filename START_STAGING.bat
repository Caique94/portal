@echo off
echo.
echo ========================================
echo   PORTAL - Ambiente de STAGING
echo   (Validacao/Testes dos Usuarios)
echo ========================================
echo.

REM Verificar se .env.staging existe
if not exist ".env.staging" (
    echo ERRO: Arquivo .env.staging nao encontrado!
    echo Certifique-se de que existe .env.staging na pasta do projeto.
    pause
    exit /b 1
)

REM Copiar .env.staging para .env
echo Configurando ambiente de staging...
copy /Y .env.staging .env > nul

REM Limpar cache
echo Limpando cache...
call php artisan cache:clear
call php artisan config:clear

REM Executar migrações (se necessário)
REM php artisan migrate --force

echo.
echo Iniciando servidores...
echo.

echo 1. Servidor Laravel (http://localhost:8080)
echo 2. Worker de Filas (PDFs e Emails)
echo 3. Watch Assets (CSS/JS)
echo.
echo Abrindo terminais...
echo.

REM Terminal 1: Servidor Laravel na porta 8080
start "Portal STAGING - Servidor Laravel" cmd /k "cd /d %~dp0 && php artisan serve --host=0.0.0.0 --port=8080"

timeout /t 2 /nobreak

REM Terminal 2: Worker de Filas
start "Portal STAGING - Queue Worker" cmd /k "cd /d %~dp0 && php artisan queue:work"

timeout /t 2 /nobreak

REM Terminal 3: Watch Assets (opcional)
start "Portal STAGING - Watch Assets" cmd /k "cd /d %~dp0 && npm run dev"

echo.
echo ========================================
echo Ambiente de STAGING iniciado!
echo ========================================
echo.
echo Acesse: http://localhost:8080
echo Rede Local: http://192.168.0.166:8080
echo.
echo Banco de dados: portal_staging
echo.
pause
