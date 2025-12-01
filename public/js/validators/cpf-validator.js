/**
 * CPF Validator & Formatter
 *
 * Fornece funções para validar e formatar CPF
 * Usa classe CSS "cpf" em inputs para aplicar automaticamente
 */

$(document).ready(function() {

    /**
     * Validar CPF
     * Verifica formato e dígitos verificadores
     */
    window.validateCPF = function(cpf) {
        if (!cpf) return false;

        // Remove máscara
        cpf = cpf.replace(/\D/g, '');

        // Verifica se tem exatamente 11 dígitos
        if (cpf.length !== 11) {
            return false;
        }

        // Verifica se não é sequência de números iguais
        if (/^(\d)\1{10}$/.test(cpf)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += cpf[i] * (10 - i);
        }
        let digit1 = 11 - (sum % 11);
        digit1 = digit1 > 9 ? 0 : digit1;

        // Calcula segundo dígito verificador
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += cpf[i] * (11 - i);
        }
        let digit2 = 11 - (sum % 11);
        digit2 = digit2 > 9 ? 0 : digit2;

        // Verifica se os dígitos conferem
        return (cpf[9] == digit1 && cpf[10] == digit2);
    };

    /**
     * Formatar CPF
     * Transforma 12345678901 em 123.456.789-01
     */
    window.formatCPF = function(cpf) {
        if (!cpf) return '';

        // Remove máscara
        cpf = cpf.replace(/\D/g, '');

        // Verifica se tem exatamente 11 dígitos
        if (cpf.length !== 11) {
            return cpf;
        }

        // Formata: XXX.XXX.XXX-XX
        return cpf.substring(0, 3) + '.' +
               cpf.substring(3, 6) + '.' +
               cpf.substring(6, 9) + '-' +
               cpf.substring(9, 11);
    };

    /**
     * Aplicar máscara de CPF enquanto digita
     */
    function applyCPFMask(element) {
        let value = element.value.replace(/\D/g, '');

        if (value.length > 11) {
            value = value.substring(0, 11);
        }

        // Formatar progressivamente
        if (value.length <= 3) {
            element.value = value;
        } else if (value.length <= 6) {
            element.value = value.substring(0, 3) + '.' + value.substring(3);
        } else if (value.length <= 9) {
            element.value = value.substring(0, 3) + '.' +
                           value.substring(3, 6) + '.' +
                           value.substring(6);
        } else {
            element.value = value.substring(0, 3) + '.' +
                           value.substring(3, 6) + '.' +
                           value.substring(6, 9) + '-' +
                           value.substring(9, 11);
        }
    }

    /**
     * Aplicar validação de CPF quando sair do campo
     */
    function validateCPFOnBlur(element) {
        const value = element.value.trim();

        if (!value) {
            // Campo vazio é válido (nullable)
            element.classList.remove('is-invalid');
            element.classList.remove('is-valid');
            return;
        }

        const isValid = window.validateCPF(value);

        if (isValid) {
            element.classList.remove('is-invalid');
            element.classList.add('is-valid');
            element.value = window.formatCPF(value);
        } else {
            element.classList.remove('is-valid');
            element.classList.add('is-invalid');
        }
    }

    /**
     * Bind eventos APENAS a inputs com classe "cpf" dentro do modal de formulário
     * Não aplica na tabela de listagem para não interferi com CPFs diferentes
     */
    $(document).on('input', '#formUsuario input.cpf', function() {
        applyCPFMask(this);
    });

    $(document).on('blur', '#formUsuario input.cpf', function() {
        validateCPFOnBlur(this);
    });

    /**
     * Validação de formulário (antes de enviar)
     */
    window.validateCPFField = function(fieldSelector) {
        const $field = $(fieldSelector);
        if ($field.length === 0) return true; // Campo não existe

        const value = $field.val().trim();

        if (!value) {
            // Campo vazio é válido (nullable)
            return true;
        }

        const isValid = window.validateCPF(value);

        if (isValid) {
            $field.removeClass('is-invalid').addClass('is-valid');
            return true;
        } else {
            $field.removeClass('is-valid').addClass('is-invalid');
            return false;
        }
    };

    console.log('✅ CPF Validator carregado');
});
