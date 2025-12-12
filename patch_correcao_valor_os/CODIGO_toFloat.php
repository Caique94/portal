<?php

/**
 * MÉTODO toFloat() - ADICIONE NO SEU OrdemServicoController
 *
 * Este método converte valores monetários no formato brasileiro (730,00)
 * para float de forma segura, evitando que valores sejam salvos incorretamente
 * como 70.030,00 ao invés de 730,00
 */

/**
 * Converter valor para float de forma segura, tratando vírgula e ponto
 */
private function toFloat($value)
{
    if (is_null($value) || $value === '') {
        return null;
    }

    // Se já é numérico, retorna como float
    if (is_numeric($value)) {
        return (float) $value;
    }

    // Se é string, remove espaços e trata vírgula/ponto
    $value = trim((string) $value);

    // Remove separadores de milhar (ponto no formato brasileiro, vírgula no americano)
    // e mantém apenas o separador decimal
    if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
        // Tem ambos: determinar qual é o separador decimal (último a aparecer)
        $posVirgula = strrpos($value, ',');
        $posPonto = strrpos($value, '.');

        if ($posVirgula > $posPonto) {
            // Vírgula é o decimal (formato BR: 1.234,56)
            $value = str_replace('.', '', $value); // Remove pontos de milhar
            $value = str_replace(',', '.', $value); // Troca vírgula por ponto
        } else {
            // Ponto é o decimal (formato US: 1,234.56)
            $value = str_replace(',', '', $value); // Remove vírgulas de milhar
        }
    } else if (strpos($value, ',') !== false) {
        // Só tem vírgula, assumir que é decimal (formato BR: 1234,56)
        $value = str_replace(',', '.', $value);
    }

    return (float) $value;
}


/**
 * EXEMPLO DE USO NO MÉTODO store():
 *
 * Substitua as linhas diretas por chamadas ao toFloat()
 */

// ANTES:
// 'valor_despesa'  => $validatedData['txtOrdemDespesas'],
// 'preco_produto'  => $validatedData['txtOrdemPrecoProduto'],
// 'valor_total'    => $validatedData['txtOrdemValorTotal'],
// 'km'             => isset($validatedData['txtOrdemKM']) ? $validatedData['txtOrdemKM'] : null,

// DEPOIS:
'valor_despesa'  => $this->toFloat($validatedData['txtOrdemDespesas']),
'preco_produto'  => $this->toFloat($validatedData['txtOrdemPrecoProduto']),
'valor_total'    => $this->toFloat($validatedData['txtOrdemValorTotal']),
'km'             => isset($validatedData['txtOrdemKM']) ? $this->toFloat($validatedData['txtOrdemKM']) : null,


/**
 * EXEMPLO DE USO NO MÉTODO update():
 *
 * Aplique o mesmo padrão
 */

'valor_despesa'  => $this->toFloat($validatedData['txtOrdemDespesas']),
'preco_produto'  => $this->toFloat($validatedData['txtOrdemPrecoProduto']),
'valor_total'    => $this->toFloat($validatedData['txtOrdemValorTotal']),
'km'             => isset($validatedData['txtOrdemKM']) ? $this->toFloat($validatedData['txtOrdemKM']) : null,


/**
 * TESTES DO MÉTODO:
 */

// toFloat("730,00")      → 730.00
// toFloat("730.00")      → 730.00
// toFloat("1.234,56")    → 1234.56
// toFloat("1,234.56")    → 1234.56
// toFloat("730")         → 730.00
// toFloat(730)           → 730.00
// toFloat(null)          → null
// toFloat("")            → null
