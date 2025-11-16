<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
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
        $clienteId = $this->input('id');

        return [
            'txtClienteCodigo' => [
                'required',
                'string',
                'max:255',
                $clienteId
                    ? "unique:cliente,codigo,{$clienteId}"
                    : 'unique:cliente,codigo'
            ],
            'txtClienteLoja' => 'required|string|max:255',
            'txtClienteNome' => 'required|string|max:255',
            'txtClienteNomeFantasia' => 'nullable|string|max:255',
            'txtClienteTipo' => 'nullable|string|max:255',
            'txtClienteCGC' => 'nullable|string|max:14',
            'txtClienteContato' => 'nullable|string|max:255',
            'txtClienteEndereco' => 'nullable|string|max:255',
            'txtClienteCidade' => 'nullable|string|max:255',
            'txtClienteEstado' => 'nullable|string|max:2',
            'txtClienteKm' => 'nullable|string|max:255',
            'txtClienteDeslocamento' => 'nullable|string|max:255',
            'slcClienteTabelaPrecos' => 'required|integer|exists:tabela_preco,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'txtClienteCodigo.required' => 'Código do cliente é obrigatório',
            'txtClienteCodigo.unique' => 'Este código de cliente já existe',
            'txtClienteLoja.required' => 'Loja é obrigatória',
            'txtClienteNome.required' => 'Nome do cliente é obrigatório',
            'slcClienteTabelaPrecos.required' => 'Tabela de preços é obrigatória',
            'slcClienteTabelaPrecos.exists' => 'Tabela de preços selecionada não existe',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'txtClienteCodigo' => 'Código do Cliente',
            'txtClienteLoja' => 'Loja',
            'txtClienteNome' => 'Nome do Cliente',
            'txtClienteNomeFantasia' => 'Nome Fantasia',
            'txtClienteTipo' => 'Tipo',
            'txtClienteCGC' => 'CNPJ',
            'txtClienteContato' => 'Contato',
            'txtClienteEndereco' => 'Endereço',
            'txtClienteCidade' => 'Cidade',
            'txtClienteEstado' => 'Estado',
            'txtClienteKm' => 'KM',
            'txtClienteDeslocamento' => 'Deslocamento',
            'slcClienteTabelaPrecos' => 'Tabela de Preços',
        ];
    }

    /**
     * Get the data that was validated and is being passed to the controller.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Mapear campos de entrada para campos do banco
        if (is_array($validated)) {
            return [
                'codigo' => $validated['txtClienteCodigo'] ?? null,
                'loja' => $validated['txtClienteLoja'] ?? null,
                'nome' => $validated['txtClienteNome'] ?? null,
                'nome_fantasia' => $validated['txtClienteNomeFantasia'] ?? null,
                'tipo' => $validated['txtClienteTipo'] ?? null,
                'cgc' => $validated['txtClienteCGC'] ?? null,
                'contato' => $validated['txtClienteContato'] ?? null,
                'endereco' => $validated['txtClienteEndereco'] ?? null,
                'municipio' => $validated['txtClienteCidade'] ?? null,
                'estado' => $validated['txtClienteEstado'] ?? null,
                'km' => $validated['txtClienteKm'] ?? null,
                'deslocamento' => $validated['txtClienteDeslocamento'] ?? null,
                'tabela_preco_id' => $validated['slcClienteTabelaPrecos'] ?? null,
            ];
        }

        return $validated;
    }
}
