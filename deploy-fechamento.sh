#!/bin/bash

# Deploy Script - Sistema Avançado de Fechamento
# Servidor: sistemasemteste.com.br
# Data: 10/12/2025

set -e  # Parar em caso de erro

echo "=========================================="
echo "DEPLOY - SISTEMA AVANÇADO DE FECHAMENTO"
echo "=========================================="
echo ""

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Diretório do projeto
cd /var/www/sistemasemteste

echo -e "${YELLOW}[1/10] Git Pull...${NC}"
git pull origin main
echo -e "${GREEN}✓ Git pull concluído${NC}"
echo ""

echo -e "${YELLOW}[2/10] Verificando dependências...${NC}"
# Verificar se dompdf está instalado
if composer show barryvdh/laravel-dompdf >/dev/null 2>&1; then
    echo -e "${GREEN}✓ barryvdh/laravel-dompdf já instalado${NC}"
else
    echo "Instalando barryvdh/laravel-dompdf..."
    composer require barryvdh/laravel-dompdf
fi

# Verificar cron-expression
if composer show dragonmantank/cron-expression >/dev/null 2>&1; then
    echo -e "${GREEN}✓ dragonmantank/cron-expression já instalado${NC}"
else
    echo "Instalando dragonmantank/cron-expression..."
    composer require dragonmantank/cron-expression
fi
echo ""

echo -e "${YELLOW}[3/10] Executando migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✓ Migrations executadas${NC}"
echo ""

echo -e "${YELLOW}[4/10] Criando diretórios...${NC}"
mkdir -p storage/app/public/fechamentos
echo -e "${GREEN}✓ Diretório fechamentos criado${NC}"

# Verificar se symlink existe
if [ ! -L public/storage ]; then
    php artisan storage:link
    echo -e "${GREEN}✓ Storage link criado${NC}"
else
    echo -e "${GREEN}✓ Storage link já existe${NC}"
fi
echo ""

echo -e "${YELLOW}[5/10] Ajustando permissões...${NC}"
chown -R www-data:www-data storage/ bootstrap/cache/
chmod -R 775 storage/ bootstrap/cache/
echo -e "${GREEN}✓ Permissões ajustadas${NC}"
echo ""

echo -e "${YELLOW}[6/10] Verificando Queue Worker (Supervisor)...${NC}"
if [ -f /etc/supervisor/conf.d/sistemasemteste-queue.conf ]; then
    echo -e "${GREEN}✓ Configuração do supervisor já existe${NC}"
    supervisorctl reread
    supervisorctl update
    supervisorctl restart sistemasemteste-queue:*
    echo -e "${GREEN}✓ Queue worker reiniciado${NC}"
else
    echo -e "${RED}⚠ Configuração do supervisor não encontrada!${NC}"
    echo "Criando arquivo de configuração..."

    cat > /etc/supervisor/conf.d/sistemasemteste-queue.conf <<EOF
[program:sistemasemteste-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sistemasemteste/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sistemasemteste/storage/logs/queue-worker.log
stopwaitsecs=3600
EOF

    supervisorctl reread
    supervisorctl update
    supervisorctl start sistemasemteste-queue:*
    echo -e "${GREEN}✓ Queue worker configurado e iniciado${NC}"
fi
echo ""

echo -e "${YELLOW}[7/10] Verificando Cron/Scheduler...${NC}"
CRON_LINE="* * * * * cd /var/www/sistemasemteste && php artisan schedule:run >> /dev/null 2>&1"
if sudo -u www-data crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo -e "${GREEN}✓ Cron já configurado${NC}"
else
    echo "Configurando cron..."
    (sudo -u www-data crontab -l 2>/dev/null; echo "$CRON_LINE") | sudo -u www-data crontab -
    echo -e "${GREEN}✓ Cron configurado${NC}"
fi
echo ""

echo -e "${YELLOW}[8/10] Limpando caches...${NC}"
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
echo -e "${GREEN}✓ Caches limpos${NC}"
echo ""

echo -e "${YELLOW}[9/10] Otimizando aplicação...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
echo -e "${GREEN}✓ Aplicação otimizada${NC}"
echo ""

echo -e "${YELLOW}[10/10] Verificando instalação...${NC}"

# Verificar tabelas
echo "Verificando tabelas do banco..."
php artisan tinker --execute="
    \$tables = ['fechamento_jobs', 'fechamento_history', 'fechamento_audit', 'scheduled_tasks'];
    foreach (\$tables as \$table) {
        if (Schema::hasTable(\$table)) {
            echo \"✓ Tabela \$table criada\n\";
        } else {
            echo \"✗ Tabela \$table NÃO encontrada\n\";
        }
    }
"

# Verificar supervisor
echo ""
echo "Status do Queue Worker:"
supervisorctl status sistemasemteste-queue:*

# Verificar rotas
echo ""
echo "Rotas da API registradas:"
php artisan route:list --path=api/fechamentos | grep api/fechamentos | head -5

echo ""
echo -e "${GREEN}=========================================="
echo "✓ DEPLOY CONCLUÍDO COM SUCESSO!"
echo "==========================================${NC}"
echo ""
echo "Próximos passos:"
echo "1. Testar criação de job via tinker"
echo "2. Verificar processamento do queue worker"
echo "3. Conferir logs: tail -f storage/logs/laravel.log"
echo ""
echo "Para criar um job de teste:"
echo "php artisan tinker"
echo ""
echo ">>> \$job = \App\Models\FechamentoJob::create(['id' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'client', 'period_start' => '2025-11-01', 'period_end' => '2025-11-30', 'requested_by' => 1, 'status' => 'queued']);"
echo ">>> \App\Jobs\ProcessFechamentoJob::dispatch(\$job->id);"
echo ">>> exit"
echo ""
