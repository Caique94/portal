#!/bin/bash

# 🧪 Script de Teste do Sistema de Filtros
# Use este script para verificar se tudo está funcionando

echo "🚀 Iniciando teste do sistema de filtros..."
echo ""

# Cores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Teste 1: Servidor está rodando?
echo "📊 Teste 1: Verificar se servidor está rodando na porta 8001"
if timeout 2 bash -c '</dev/tcp/localhost/8001' 2>/dev/null; then
  echo -e "${GREEN}✅ Servidor está rodando na porta 8001${NC}"
else
  echo -e "${RED}❌ Servidor NÃO está rodando na porta 8001${NC}"
  echo "   Para iniciar:"
  echo "   php artisan serve --host=0.0.0.0 --port=8001"
  exit 1
fi

echo ""

# Teste 2: Verificar logs
echo "📋 Teste 2: Verificar últimos erros dos logs"
if [ -f "storage/logs/laravel.log" ]; then
  ERROR_COUNT=$(grep -i "ERROR\|ERRO" storage/logs/laravel.log | wc -l)
  if [ $ERROR_COUNT -gt 0 ]; then
    echo -e "${YELLOW}⚠️  Encontrados $ERROR_COUNT erros nos logs${NC}"
    echo "   Últimos 3 erros:"
    grep -i "ERROR\|ERRO" storage/logs/laravel.log | tail -3
  else
    echo -e "${GREEN}✅ Nenhum erro nos logs${NC}"
  fi
else
  echo -e "${YELLOW}⚠️  Log file não existe ainda (será criado na primeira requisição)${NC}"
fi

echo ""

# Teste 3: Verificar se banco de dados tem dados
echo "🗄️  Teste 3: Verificar dados no banco"
php artisan tinker << 'EOF' 2>/dev/null | grep -E "Total|Clientes|Consultores"
try {
    $orders = \App\Models\OrdemServico::count();
    $clients = \App\Models\Cliente::count();
    $consultants = \App\Models\User::where('papel', 'consultor')->count();
    echo "✅ Total de Ordens: $orders\n";
    echo "✅ Total de Clientes: $clients\n";
    echo "✅ Total de Consultores: $consultants\n";
} catch (Exception $e) {
    echo "❌ Erro ao conectar ao banco\n";
}
EOF

echo ""

# Teste 4: Verificar arquivos importantes
echo "📁 Teste 4: Verificar se arquivos existem"
FILES=(
  "app/Services/ReportExportService.php"
  "app/Http/Controllers/ReportFilterController.php"
  "resources/views/managerial-dashboard.blade.php"
)

for file in "${FILES[@]}"; do
  if [ -f "$file" ]; then
    echo -e "${GREEN}✅ $file${NC}"
  else
    echo -e "${RED}❌ $file NÃO ENCONTRADO${NC}"
  fi
done

echo ""
echo "🎉 Teste concluído!"
echo ""
echo "Próximos passos:"
echo "1. Abra http://localhost:8001/login no navegador"
echo "2. Faça login com:"
echo "   Email: admin@example.com"
echo "   Senha: 123"
echo "3. Vá para: Menu → Dashboard Gerencial → Filtros & Relatórios"
echo "4. Abra o Console do navegador (F12 → Console)"
echo "5. Clique em 'Aplicar Filtros'"
echo "6. Verifique se há erros no console"
echo ""
echo "Se houver erros, envie a mensagem do console para debug"
