<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdutoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->papel === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $produtoId = $this->input('id');

        return [
            'txtProdutoCodigo' => [
                'required',
                'string',
                'max:255',
                $produtoId
                    ? "unique:produto,codigo,{$produtoId}"
                    : 'unique:produto,codigo'
            ],
            'txtProdutoNome' => 'required|string|max:255',
            'txtProdutoDescricao' => 'nullable|string|max:1000',
            'cboProdutoAtivo' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'txtProdutoCodigo.required' => 'Código do produto é obrigatório',
            'txtProdutoCodigo.unique' => 'Este código de produto já existe',
            'txtProdutoNome.required' => 'Nome do produto é obrigatório',
            'cboProdutoAtivo.required' => 'Status é obrigatório',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'txtProdutoCodigo' => 'Código do Produto',
            'txtProdutoNome' => 'Nome do Produto',
            'txtProdutoDescricao' => 'Descrição',
            'cboProdutoAtivo' => 'Status',
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
                'codigo' => $validated['txtProdutoCodigo'] ?? null,
                'nome' => $validated['txtProdutoNome'] ?? null,
                'descricao' => $validated['txtProdutoDescricao'] ?? null,
                'ativo' => $validated['cboProdutoAtivo'] ?? true,
            ];
        }

        return $validated;
    }
}
