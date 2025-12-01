<?php

namespace App\Helpers;

/**
 * Helper para cálculos e validações de Ordem de Serviço
 * Implements time parsing, formatting, and calculation logic
 */
class OrdemServicoCalculator
{
    /**
     * Parse HH:MM string to minutes
     * @param string $timeStr Time in HH:MM format
     * @return int Minutes
     */
    public static function timeToMinutes($timeStr)
    {
        if (empty($timeStr) || $timeStr === '--:--') {
            return 0;
        }

        if (strpos($timeStr, ':') === false) {
            return intval($timeStr);
        }

        list($horas, $minutos) = explode(':', trim($timeStr));
        return intval($horas) * 60 + intval($minutos);
    }

    /**
     * Format minutes to HH:MM string
     * @param int $minutos Minutes
     * @return string HH:MM format
     */
    public static function minutesToTime($minutos)
    {
        $horas = intval($minutos / 60);
        $mins = $minutos % 60;
        return sprintf('%02d:%02d', $horas, $mins);
    }

    /**
     * Convert minutes to decimal hours with 2 decimal places
     * @param int $minutos Minutes
     * @return float Decimal hours (e.g., 9.50)
     */
    public static function minutesToDecimalHours($minutos)
    {
        return round($minutos / 60, 2);
    }

    /**
     * Calculate total hours: (hora_fim - hora_inicio - hora_desconto)
     * @param string $horaInicio Start time (HH:MM)
     * @param string $horaFim End time (HH:MM)
     * @param string $horaDesconto Discount time (HH:MM), optional
     * @return float Total hours in decimal format (e.g., 9.00, 7.50)
     */
    public static function calculateTotalHoras($horaInicio, $horaFim, $horaDesconto = null)
    {
        $minutosInicio = self::timeToMinutes($horaInicio);
        $minutosFim = self::timeToMinutes($horaFim);
        $minutosDesconto = self::timeToMinutes($horaDesconto);

        // Calcular duração: fim - inicio
        $duracaoMinutos = $minutosFim - $minutosInicio;

        // Subtrair desconto se existir
        if ($minutosDesconto > 0) {
            $duracaoMinutos -= $minutosDesconto;
        }

        // Se resultado negativo, retornar 0.00
        if ($duracaoMinutos < 0) {
            return 0.00;
        }

        return self::minutesToDecimalHours($duracaoMinutos);
    }

    /**
     * Format currency to PT-BR (R$ 435,00)
     * @param float $value Value to format
     * @return string Formatted currency string
     */
    public static function formatCurrency($value)
    {
        if (is_null($value) || $value === '') {
            return 'R$ 0,00';
        }

        return 'R$ ' . number_format(floatval($value), 2, ',', '.');
    }

    /**
     * Format decimal hours (9.00)
     * @param float $hours Decimal hours
     * @return string Formatted hours
     */
    public static function formatHours($hours)
    {
        return number_format(floatval($hours), 2, '.', '');
    }

    /**
     * Validate visual output against expected values
     * Test case 1: 08:00→17:00, no discount = 9.00 hours
     * Test case 2: 08:00→17:00, 01:30 discount = 7.50 hours
     * Test case 3: Empty times = 0.00 hours
     * @return array Validation results
     */
    public static function runValidationTests()
    {
        $results = [];

        // Test 1: No discount
        $test1 = self::calculateTotalHoras('08:00', '17:00', '');
        $results['test1_no_discount'] = [
            'input' => ['08:00', '17:00', ''],
            'expected' => 9.00,
            'actual' => $test1,
            'pass' => abs($test1 - 9.00) < 0.01,
            'message' => 'Test 1 (08:00→17:00, no discount): ' . ($test1 === 9.00 ? 'PASS' : 'FAIL (got ' . $test1 . ')')
        ];

        // Test 2: With 01:30 discount
        $test2 = self::calculateTotalHoras('08:00', '17:00', '01:30');
        $results['test2_with_discount'] = [
            'input' => ['08:00', '17:00', '01:30'],
            'expected' => 7.50,
            'actual' => $test2,
            'pass' => abs($test2 - 7.50) < 0.01,
            'message' => 'Test 2 (08:00→17:00, 01:30 discount): ' . ($test2 === 7.50 ? 'PASS' : 'FAIL (got ' . $test2 . ')')
        ];

        // Test 3: Empty times
        $test3 = self::calculateTotalHoras('', '', '');
        $results['test3_empty_times'] = [
            'input' => ['', '', ''],
            'expected' => 0.00,
            'actual' => $test3,
            'pass' => $test3 === 0.00,
            'message' => 'Test 3 (empty times): ' . ($test3 === 0.00 ? 'PASS' : 'FAIL (got ' . $test3 . ')')
        ];

        // Summarize
        $passCount = array_sum(array_map(fn($t) => $t['pass'] ? 1 : 0, $results));
        $results['summary'] = [
            'total_tests' => count($results) - 1,
            'passed' => $passCount,
            'failed' => (count($results) - 1) - $passCount,
            'all_passed' => $passCount === (count($results) - 1)
        ];

        return $results;
    }
}
