ARQUIVOS ALTERADOS - RPS FILTRO DE CLIENTES

Data: 2025-11-21
Implementação: Filtro de Clientes na Emissão de RPS
Status: ✅ Completo e Deployado

ARQUIVOS INCLUSOS:
1. app/Http/Controllers/OrdemServicoController.php - Novo método clientesComOrdensRPS()
2. public/js/faturamento.js - 6 novas funções + UI melhorada

COMO USAR:
1. Extraia este ZIP
2. Copie os arquivos para as pastas correspondentes no seu projeto
3. Execute: php artisan cache:clear
4. Teste a funcionalidade

DOCUMENTAÇÃO:
Veja: ARQUIVOS_ALTERADOS_RPS_FILTRO.md para mais detalhes

Commits Relacionados:
- 2c800eb - feat: Implement client filter for RPS emission
- 73da932 - fix: Remove selection requirement for RPS emission button
- d777b61 - fix: Correct relationship name from ordensServico to ordemServicos
- 99e944c - refactor: Improve UI/UX of RPS order selection modal

