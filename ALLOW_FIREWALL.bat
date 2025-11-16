@echo off
echo.
echo ========================================
echo  Configurando Firewall Windows
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo ERRO: Este script precisa ser executado como Administrador!
    echo.
    echo Clique com botao direito neste arquivo e selecione:
    echo "Executar como administrador"
    echo.
    pause
    exit /b 1
)

echo Adicionando regra de firewall para porta 8001...
echo.

REM Add inbound rule for port 8001
netsh advfirewall firewall add rule name="Portal - Porta 8001" dir=in action=allow protocol=tcp localport=8001 profile=any enable=yes

if %errorLevel% equ 0 (
    echo.
    echo ========================================
    echo SUCESSO! Firewall configurado!
    echo ========================================
    echo.
    echo A porta 8001 agora esta aberta para:
    echo - Sua maquina: http://localhost:8001
    echo - Rede Local: http://192.168.0.72:8001
    echo.
    echo Outros dispositivos agora conseguem acessar!
    echo.
) else (
    echo.
    echo ERRO ao configurar firewall!
    echo.
)

pause
