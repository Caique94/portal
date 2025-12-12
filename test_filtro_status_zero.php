<?php

/**
 * Script de teste para verificar o comportamento do filtro de status com valor 0
 * Execute: php test_filtro_status_zero.php
 */

echo "=== TESTE DE FILTRO DE STATUS ===\n\n";

// Simular diferentes valores de status recebidos via GET
$testCases = [
    ['status' => '0', 'descricao' => 'String "0" (Em Aberto)'],
    ['status' => '1', 'descricao' => 'String "1" (Aguardando Aprovação)'],
    ['status' => '7', 'descricao' => 'String "7" (RPS Emitida)'],
    ['status' => '', 'descricao' => 'String vazia'],
    ['descricao' => 'Sem parâmetro status'],
];

foreach ($testCases as $index => $test) {
    echo "Teste " . ($index + 1) . ": {$test['descricao']}\n";
    echo str_repeat('-', 50) . "\n";

    // Simular $request->input('status')
    $filtroStatus = $test['status'] ?? null;

    // Simular $request->has('status')
    $hasStatus = isset($test['status']);

    echo "  \$request->has('status'): " . ($hasStatus ? 'true' : 'false') . "\n";
    echo "  \$filtroStatus: " . var_export($filtroStatus, true) . "\n";

    // Lógica ANTIGA (com bug)
    $filtroStatusAntigo = null;
    if ($filtroStatus !== null && $filtroStatus !== '') {
        $filtroStatusAntigo = (int) $filtroStatus;
    }
    echo "  ANTIGA - Seria aplicado?: " . ($filtroStatusAntigo !== null ? 'SIM' : 'NÃO') . "\n";
    if ($filtroStatusAntigo !== null) {
        echo "  ANTIGA - Valor convertido: {$filtroStatusAntigo}\n";
    }

    // Lógica NOVA (corrigida)
    $filtroStatusFornecido = $hasStatus && $filtroStatus !== null && $filtroStatus !== '';
    $filtroStatusNovo = null;
    if ($filtroStatusFornecido) {
        $filtroStatusNovo = (int) $filtroStatus;
    }
    echo "  NOVA - Seria aplicado?: " . ($filtroStatusFornecido ? 'SIM' : 'NÃO') . "\n";
    if ($filtroStatusFornecido) {
        echo "  NOVA - Valor convertido: {$filtroStatusNovo}\n";
    }

    // Verificar se o valor 0 passaria na condição if (!$filtroStatus)
    if ($filtroStatusAntigo !== null) {
        $passariaNoFinanceiro = !$filtroStatusAntigo;
        echo "  ANTIGA - Financeiro aplicaria filtro padrão?: " . ($passariaNoFinanceiro ? 'SIM (ERRO!)' : 'NÃO') . "\n";
    }

    echo "\n";
}

echo "=== RESUMO ===\n";
echo "PROBLEMA ANTIGO:\n";
echo "  - Status 0 era convertido para integer 0\n";
echo "  - if (!\$filtroStatus) avaliava como TRUE para 0\n";
echo "  - Financeiro aplicava filtro padrão mesmo com status 0 selecionado\n\n";

echo "SOLUÇÃO NOVA:\n";
echo "  - Usa \$filtroStatusFornecido para verificar se o filtro foi enviado\n";
echo "  - Converte para integer apenas se foi fornecido\n";
echo "  - Verifica \$filtroStatusFornecido ao invés de !\$filtroStatus\n";
