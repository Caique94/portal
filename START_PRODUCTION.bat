@echo off
echo.
echo ========================================
echo   PORTAL - Ambiente de PRODUCAO
echo ========================================
echo.

REM Verificar se .env.production existe
if not exist ".env.production" (
    echo ERRO: Arquivo .env.production nao encontrado!
    echo Certifique-se de que existe .env.production na pasta do projeto.
    pause
    exit /b 1
)

REM Copiar .env.production para .env
echo Configurando ambiente de producao...
copy /Y .env.production .env > nul

REM Limpar cache
echo Limpando cache...
call php artisan cache:clear
call php artisan config:clear

REM Executar migrações (se necessário)
REM php artisan migrate --force

echo.
echo Iniciando servidores...
echo.

echo 1. Servidor Laravel (http://localhost:8001)
echo 2. Worker de Filas (PDFs e Emails)
echo 3. Watch Assets (CSS/JS)
echo.
echo Abrindo terminais...
echo.

REM Terminal 1: Servidor Laravel na porta 8001
start "Portal PRODUCAO - Servidor Laravel" cmd /k "cd /d %~dp0 && php artisan serve --host=0.0.0.0 --port=8001"

timeout /t 2 /nobreak

REM Terminal 2: Worker de Filas
start "Portal PRODUCAO - Queue Worker" cmd /k "cd /d %~dp0 && php artisan queue:work"

timeout /t 2 /nobreak

REM Terminal 3: Watch Assets (opcional)
start "Portal PRODUCAO - Watch Assets" cmd /k "cd /d %~dp0 && npm run dev"

echo.
echo ========================================
echo Ambiente de PRODUCAO iniciado!
echo ========================================
echo.
echo Acesse: http://localhost:8001
echo Rede Local: http://192.168.0.166:8001
echo.
echo Banco de dados: portal_prod
echo.
pause
