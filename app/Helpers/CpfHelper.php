<?php

namespace App\Helpers;

class CpfHelper
{
    /**
     * Validar CPF
     *
     * Valida um CPF verificando:
     * - Formato (11 dígitos)
     * - Dígitos verificadores
     *
     * @param string $cpf CPF com ou sem máscara
     * @return bool true se CPF é válido
     */
    public static function isValid(?string $cpf): bool
    {
        if (empty($cpf)) {
            return false;
        }

        // Remove máscara e caracteres especiais
        $cpf = preg_replace('/\D/', '', (string)$cpf);

        // Verifica se tem exatamente 11 dígitos
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Verifica se não é sequência de números iguais (CPFs inválidos conhecidos)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $cpf[$i] * (10 - $i);
        }
        $digit1 = (int)(11 - ($sum % 11));
        if ($digit1 > 9) {
            $digit1 = 0;
        }

        // Calcula segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $cpf[$i] * (11 - $i);
        }
        $digit2 = (int)(11 - ($sum % 11));
        if ($digit2 > 9) {
            $digit2 = 0;
        }

        // Verifica se os dígitos conferem
        return ($cpf[9] == $digit1 && $cpf[10] == $digit2);
    }

    /**
     * Formatar CPF
     *
     * Formata CPF para o padrão: XXX.XXX.XXX-XX
     *
     * @param string $cpf CPF com ou sem máscara
     * @return string|null CPF formatado ou null se inválido
     */
    public static function format(?string $cpf): ?string
    {
        if (empty($cpf)) {
            return null;
        }

        // Remove máscara e caracteres especiais
        $cpf = preg_replace('/\D/', '', (string)$cpf);

        // Verifica se tem exatamente 11 dígitos
        if (strlen($cpf) !== 11) {
            return null;
        }

        // Formata: XXX.XXX.XXX-XX
        return substr($cpf, 0, 3) . '.' .
               substr($cpf, 3, 3) . '.' .
               substr($cpf, 6, 3) . '-' .
               substr($cpf, 9, 2);
    }

    /**
     * Limpar CPF
     *
     * Remove máscara e caracteres especiais, mantendo apenas dígitos
     *
     * @param string $cpf CPF com ou sem máscara
     * @return string|null CPF limpo (apenas dígitos) ou null se vazio
     */
    public static function clean(?string $cpf): ?string
    {
        if (empty($cpf)) {
            return null;
        }

        $cleaned = preg_replace('/\D/', '', (string)$cpf);
        return !empty($cleaned) ? $cleaned : null;
    }
}
