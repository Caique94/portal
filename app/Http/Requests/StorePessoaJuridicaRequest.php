<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePessoaJuridicaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'                      => 'nullable|integer|min:1',
            'txtPJCNPJ'              => [
                'nullable',
                'string',
                // Aceita com máscara (XX.XXX.XXX/XXXX-XX) ou sem (14 dígitos)
                'regex:/^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/',
            ],
            'txtPJRazaoSocial'       => 'nullable|string|max:255',
            'txtPJNomeFantasia'      => 'nullable|string|max:255',
            'txtPJInscricaoEstadual' => 'nullable|string|max:20',
            'txtPJInscricaoMunicipal'=> 'nullable|string|max:20',
            'txtPJEndereco'          => 'nullable|string|max:255',
            'txtPJNumero'            => 'nullable|string|max:20',
            'txtPJComplemento'       => 'nullable|string|max:100',
            'txtPJBairro'            => 'nullable|string|max:100',
            'txtPJCidade'            => 'nullable|string|max:100',
            'txtPJEstado'            => 'nullable|string|max:2',
            'txtPJCEP'               => 'nullable|string|max:10|regex:/^\d{5}-\d{3}|\d{8}$/',
            'txtPJTelefone'          => 'nullable|string|max:20',
            'txtPJEmail'             => 'nullable|email|max:255',
            'txtPJSite'              => 'nullable|url|max:255',
            'txtPJRamoAtividade'     => 'nullable|string|max:255',
            'txtPJDataConstituicao'  => 'nullable|date_format:Y-m-d',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'txtPJCNPJ.regex'                 => 'CNPJ deve estar no formato XX.XXX.XXX/XXXX-XX ou 14 dígitos',
            'txtPJEmail.email'                => 'Email da empresa é inválido',
            'txtPJSite.url'                   => 'URL do site é inválida',
            'txtPJDataConstituicao.date_format' => 'Data deve estar em formato YYYY-MM-DD',
            'txtPJCEP.regex'                  => 'CEP deve estar no formato XXXXX-XXX ou 8 dígitos',
        ];
    }

    /**
     * Prepare the data for validation.
     * Remove caracteres inválidos do CNPJ antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $cnpj = $this->input('txtPJCNPJ');

        if ($cnpj) {
            // Se começa com letra ou tem caracteres estranhos, remove máscara
            $cnpjLimpo = preg_replace('/\D/', '', (string)$cnpj);

            // Se ficou com 14 dígitos, usa a versão limpa
            if (strlen($cnpjLimpo) === 14) {
                $this->merge([
                    'txtPJCNPJ' => $cnpjLimpo
                ]);
            }
        }

        // Garantir que ID é sempre integer
        if ($this->has('id')) {
            $this->merge([
                'id' => $this->input('id') ? (int) $this->input('id') : null
            ]);
        }
    }
}
