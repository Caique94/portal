<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContatoRequest extends FormRequest
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
        return [
            'txtContatoClienteId' => 'required|integer|exists:cliente,id',
            'txtContatoNome' => 'required|string|max:255',
            'txtContatoEmail' => 'nullable|email|max:255',
            'txtContatoTelefone' => 'nullable|string|max:255',
            'txtContatoAniversario' => 'nullable|date_format:Y-m-d',
            'chkContatoRecebeEmailOS' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'txtContatoClienteId.required' => 'Cliente é obrigatório',
            'txtContatoClienteId.exists' => 'Cliente selecionado não existe',
            'txtContatoNome.required' => 'Nome do contato é obrigatório',
            'txtContatoEmail.email' => 'Email inválido',
            'txtContatoAniversario.date_format' => 'Data de aniversário em formato inválido',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'txtContatoClienteId' => 'Cliente',
            'txtContatoNome' => 'Nome do Contato',
            'txtContatoEmail' => 'Email',
            'txtContatoTelefone' => 'Telefone',
            'txtContatoAniversario' => 'Aniversário',
            'chkContatoRecebeEmailOS' => 'Recebe Email OS',
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
                'cliente_id' => $validated['txtContatoClienteId'] ?? null,
                'nome' => $validated['txtContatoNome'] ?? null,
                'email' => $validated['txtContatoEmail'] ?? null,
                'telefone' => $validated['txtContatoTelefone'] ?? null,
                'aniversario' => $validated['txtContatoAniversario'] ?? null,
                'recebe_email_os' => $validated['chkContatoRecebeEmailOS'] ?? false,
            ];
        }

        return $validated;
    }
}
