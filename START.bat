@echo off
echo.
echo ========================================
echo   PORTAL - Sistema de Gestao de OS e RPS
echo ========================================
echo.
echo Iniciando servidores...
echo.

echo 1. Servidor Laravel (http://localhost:8001)
echo 2. Worker de Filas (PDFs e Emails)
echo 3. Watch Assets (CSS/JS)
echo.
echo Abrindo terminais...
echo.

REM Terminal 1: Servidor Laravel
start "Portal - Servidor Laravel" cmd /k "cd /d %~dp0 && php artisan serve --host=127.0.0.1 --port=8001"

timeout /t 2 /nobreak

REM Terminal 2: Worker de Filas
start "Portal - Queue Worker" cmd /k "cd /d %~dp0 && php artisan queue:work"

timeout /t 2 /nobreak

REM Terminal 3: Watch Assets (opcional)
start "Portal - Watch Assets" cmd /k "cd /d %~dp0 && npm run dev"

echo.
echo ========================================
echo Acesse: http://localhost:8001
echo ========================================
echo.
pause
