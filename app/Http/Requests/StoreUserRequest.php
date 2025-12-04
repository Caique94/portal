<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request para criação/atualização de usuários
 *
 * Valida e sanitiza todos os dados de entrada
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ou adicionar lógica de autorização
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->input('id');
        $isUpdate = !empty($userId);

        return [
            // ABA 1: Dados Pessoais
            'txtUsuarioNome' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[\p{L}\s\-\']+$/u', // Apenas letras, espaços, hífen e apóstrofo
            ],

            'txtUsuarioEmail' => [
                'required',
                'email:rfc,dns',
                'max:255',
                $isUpdate
                    ? Rule::unique('users', 'email')->ignore($userId)
                    : Rule::unique('users', 'email'),
            ],

            'slcUsuarioPapel' => [
                'required',
                Rule::in(['admin', 'consultor', 'financeiro']),
            ],

            'txtUsuarioDataNasc' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'before:today',
                'after:1900-01-01',
            ],

            'txtUsuarioCelular' => [
                'nullable',
                'string',
                'regex:/^\(?\d{2}\)?\s?9?\d{4}-?\d{4}$/', // (00) 90000-0000
            ],

            'txtUsuarioCPF' => [
                'nullable',
                'string',
                'regex:/^\d{3}\.?\d{3}\.?\d{4}-?\d{2}$/', // 000.000.000-00
                function ($attribute, $value, $fail) {
                    if ($value && !$this->validateCPF($value)) {
                        $fail('O CPF informado é inválido.');
                    }
                },
            ],

            'txtUsuarioValorHora' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999.99',
                'regex:/^\d+(\.\d{1,2})?$/', // Máximo 2 casas decimais
            ],

            'txtUsuarioValorDesloc' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            'txtUsuarioValorKM' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            'txtUsuarioSalarioBase' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            // ABA 2: Pessoa Jurídica
            'txtPJCNPJ' => [
                'nullable',
                'string',
                'regex:/^\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2}$/', // 00.000.000/0000-00
                function ($attribute, $value, $fail) {
                    if ($value && !$this->validateCNPJ($value)) {
                        $fail('O CNPJ informado é inválido.');
                    }
                },
            ],

            'txtPJRazaoSocial' => 'nullable|string|max:255',
            'txtPJNomeFantasia' => 'nullable|string|max:255',
            'txtPJInscricaoEstadual' => 'nullable|string|max:20',
            'txtPJInscricaoMunicipal' => 'nullable|string|max:20',
            'txtPJEndereco' => 'nullable|string|max:255',
            'txtPJNumero' => 'nullable|string|max:20',
            'txtPJComplemento' => 'nullable|string|max:100',
            'txtPJBairro' => 'nullable|string|max:100',
            'txtPJCidade' => 'nullable|string|max:100',
            'txtPJEstado' => 'nullable|string|max:255',
            'txtPJCEP' => [
                'nullable',
                'string',
                'regex:/^\d{5}-?\d{3}$/', // 00000-000
            ],
            'txtPJTelefone' => [
                'nullable',
                'string',
                'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/',
            ],
            'txtPJEmail' => 'nullable|email:rfc|max:255',
            'txtPJSite' => 'nullable|url|max:255',
            'txtPJRamoAtividade' => 'nullable|string|max:255',
            'txtPJDataConstituicao' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'before:today',
            ],

            // ABA 3: Dados de Pagamento
            'txtPagTitularConta' => 'nullable|string|max:255',
            'txtPagCpfCnpjTitular' => [
                'nullable',
                'string',
                'regex:/^(\d{3}\.?\d{3}\.?\d{3}-?\d{2}|\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2})$/',
            ],
            'txtPagBanco' => 'nullable|string|max:100',
            'txtPagAgencia' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\d{1,5}-?\d?$/', // 0000-0
            ],
            'txtPagConta' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\d{1,12}-?\d{1}$/', // 000000000-0
            ],
            'slcPagTipoConta' => [
                'nullable',
                Rule::in(['corrente', 'poupanca']),
            ],
            'txtPagPixKey' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'txtUsuarioNome.required' => 'O nome é obrigatório',
            'txtUsuarioNome.min' => 'O nome deve ter no mínimo 3 caracteres',
            'txtUsuarioNome.regex' => 'O nome contém caracteres inválidos',

            'txtUsuarioEmail.required' => 'O email é obrigatório',
            'txtUsuarioEmail.email' => 'O email deve ser válido',
            'txtUsuarioEmail.unique' => 'Este email já está cadastrado',

            'slcUsuarioPapel.required' => 'O papel é obrigatório',
            'slcUsuarioPapel.in' => 'O papel deve ser admin, consultor ou financeiro',

            'txtUsuarioDataNasc.date' => 'Data de nascimento inválida',
            'txtUsuarioDataNasc.before' => 'Data de nascimento deve ser anterior a hoje',

            'txtUsuarioCelular.regex' => 'Formato de celular inválido. Use: (00) 90000-0000',
            'txtUsuarioCPF.regex' => 'Formato de CPF inválido. Use: 000.000.000-00',

            'txtUsuarioValorHora.numeric' => 'Valor hora deve ser numérico',
            'txtUsuarioValorHora.min' => 'Valor hora deve ser maior ou igual a zero',

            'txtPJCNPJ.regex' => 'Formato de CNPJ inválido. Use: 00.000.000/0000-00',
            'txtPJCEP.regex' => 'Formato de CEP inválido. Use: 00000-000',
            'txtPJEmail.email' => 'Email da empresa inválido',
            'txtPJSite.url' => 'URL do site inválida',

            'txtPagAgencia.regex' => 'Formato de agência inválido. Use: 0000-0',
            'txtPagConta.regex' => 'Formato de conta inválido. Use: 000000000-0',
            'slcPagTipoConta.in' => 'Tipo de conta deve ser corrente ou poupanca',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitizar valores monetários (remover R$, ., manter ,)
        $monetaryFields = [
            'txtUsuarioValorHora',
            'txtUsuarioValorDesloc',
            'txtUsuarioValorKM',
            'txtUsuarioSalarioBase',
        ];

        foreach ($monetaryFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                // Remover R$, espaços, pontos (milhares)
                $value = str_replace(['R$', ' ', '.'], '', $value);
                // Trocar vírgula por ponto
                $value = str_replace(',', '.', $value);
                $this->merge([$field => $value]);
            }
        }

        // Remover máscara de CEP
        if ($this->has('txtPJCEP')) {
            $cep = preg_replace('/\D/', '', $this->input('txtPJCEP'));
            $this->merge(['txtPJCEP' => $cep]);
        }
    }

    /**
     * Validar CPF
     */
    private function validateCPF(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // CPFs inválidos conhecidos
        $invalidCPFs = [
            '00000000000',
            '11111111111',
            '22222222222',
            '33333333333',
            '44444444444',
            '55555555555',
            '66666666666',
            '77777777777',
            '88888888888',
            '99999999999',
        ];

        if (in_array($cpf, $invalidCPFs)) {
            return false;
        }

        // Validar dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validar CNPJ
     */
    private function validateCNPJ(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        // CNPJs inválidos conhecidos
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Validar dígitos verificadores
        $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        for ($i = 0, $n = 0; $i < 12; $n += $cnpj[$i] * $b[++$i]);

        if ($cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            return false;
        }

        for ($i = 0, $n = 0; $i <= 12; $n += $cnpj[$i] * $b[$i++]);

        if ($cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            return false;
        }

        return true;
    }
}
