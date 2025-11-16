@echo off
echo.
echo ========================================
echo   PORTAL - Ambiente de DESENVOLVIMENTO
echo ========================================
echo.

REM Verificar se .env.development existe
if not exist ".env.development" (
    echo ERRO: Arquivo .env.development nao encontrado!
    echo Certifique-se de que existe .env.development na pasta do projeto.
    pause
    exit /b 1
)

REM Copiar .env.development para .env
echo Configurando ambiente de desenvolvimento...
copy /Y .env.development .env > nul

REM Limpar cache
echo Limpando cache...
call php artisan cache:clear
call php artisan config:clear

REM Executar migrações (se necessário)
REM php artisan migrate --force

echo.
echo Iniciando servidores...
echo.

echo 1. Servidor Laravel (http://localhost:8000)
echo 2. Worker de Filas (PDFs e Emails)
echo 3. Watch Assets (CSS/JS)
echo.
echo Abrindo terminais...
echo.

REM Terminal 1: Servidor Laravel na porta 8000
start "Portal DEV - Servidor Laravel" cmd /k "cd /d %~dp0 && php artisan serve --host=0.0.0.0 --port=8000"

timeout /t 2 /nobreak

REM Terminal 2: Worker de Filas
start "Portal DEV - Queue Worker" cmd /k "cd /d %~dp0 && php artisan queue:work"

timeout /t 2 /nobreak

REM Terminal 3: Watch Assets (opcional)
start "Portal DEV - Watch Assets" cmd /k "cd /d %~dp0 && npm run dev"

echo.
echo ========================================
echo Ambiente de DESENVOLVIMENTO iniciado!
echo ========================================
echo.
echo Acesse: http://localhost:8000
echo Rede Local: http://192.168.0.166:8000
echo.
echo Banco de dados: portal_dev
echo.
pause
