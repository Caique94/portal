<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTabelaPrecoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->papel === 'admin';
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure cboTabelaPrecoAtivo is always 0 or 1 for validation
        if (!$this->has('cboTabelaPrecoAtivo')) {
            $this->merge([
                'cboTabelaPrecoAtivo' => '0',
            ]);
        } else {
            $this->merge([
                'cboTabelaPrecoAtivo' => $this->input('cboTabelaPrecoAtivo') ? '1' : '0',
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $tabelaId = $this->input('id');

        return [
            'txtTabelaPrecoCodigo' => [
                'required',
                'string',
                'max:255',
                $tabelaId
                    ? "unique:tabela_preco,codigo,{$tabelaId}"
                    : 'unique:tabela_preco,codigo'
            ],
            'txtTabelaPrecoNome' => 'required|string|max:255',
            'txtTabelaPrecoDescricao' => 'nullable|string|max:1000',
            'cboTabelaPrecoAtivo' => 'required|in:0,1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'txtTabelaPrecoCodigo.required' => 'Código da tabela é obrigatório',
            'txtTabelaPrecoCodigo.unique' => 'Este código de tabela já existe',
            'txtTabelaPrecoNome.required' => 'Nome da tabela é obrigatório',
            'cboTabelaPrecoAtivo.required' => 'Status é obrigatório',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'txtTabelaPrecoCodigo' => 'Código da Tabela',
            'txtTabelaPrecoNome' => 'Nome da Tabela',
            'txtTabelaPrecoDescricao' => 'Descrição',
            'cboTabelaPrecoAtivo' => 'Status',
        ];
    }

    /**
     * Get the data that was validated and is being passed to the controller.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (is_array($validated)) {
            return [
                'codigo' => $validated['txtTabelaPrecoCodigo'] ?? null,
                'nome' => $validated['txtTabelaPrecoNome'] ?? null,
                'descricao' => $validated['txtTabelaPrecoDescricao'] ?? null,
                'ativo' => (bool) ($validated['cboTabelaPrecoAtivo'] === '1' || $validated['cboTabelaPrecoAtivo'] === 1 || $validated['cboTabelaPrecoAtivo'] === true),
            ];
        }

        return $validated;
    }
}
