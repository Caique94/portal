<?php

namespace App\Http\Controllers;

use App\Models\Contato;
use Illuminate\Http\Request;

class ContatoController extends Controller
{

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id'                    => 'nullable|numeric',
            'txtContatoClienteId'   => 'required|numeric|min:0',
            'txtContatoNome'        => 'required|string|max:255',
            'txtContatoEmail'       => 'nullable|email|max:255',
            'txtContatoTelefone'    => 'nullable|string|max:255',
            'txtContatoAniversario' => 'nullable|string|max:255',
            'chkContatoRecebeEmailOS' => 'nullable'
        ]);

        $mappedData = [
            'cliente_id'        => $validatedData['txtContatoClienteId'],
            'nome'              => $validatedData['txtContatoNome'],
            'email'             => $validatedData['txtContatoEmail'] ?? null,
            'telefone'          => $validatedData['txtContatoTelefone'] ?? null,
            'recebe_email_os'   => $request->has('chkContatoRecebeEmailOS') && $request->input('chkContatoRecebeEmailOS') !== 'false' ? true : false,
            'aniversario'       => $validatedData['txtContatoAniversario'] ?? null
        ];

        // Se tiver ID, atualiza. Senão, cria novo
        if (isset($validatedData['id']) && $validatedData['id']) {
            $contato = Contato::findOrFail($validatedData['id']);
            $contato->update($mappedData);
            $message = 'Contato atualizado com sucesso';
        } else {
            $contato = Contato::create($mappedData);
            $message = 'Contato criado com sucesso';
        }

        return response()->json([
            'message'   => $message,
            'data'      => $contato
        ], 201);
    }

    public function list(Request $request)
    {
        $id = $request->input('id');

        $data = Contato::where('cliente_id',$id)->get();
        return response()->json($data);
    }

    public function delete(Request $request, string $id)
    {
        $contato = Contato::destroy($id);

        return response()->json([
            'message' => 'Contato removido com sucesso',
            'data' => $contato
        ], 201);
    }

}