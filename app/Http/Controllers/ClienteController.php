<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    // LISTAR
    public function list(Request $request)
    {
        // Mantido no padrão que você já usava (join com tabela_preco)
        $data = Cliente::join('tabela_preco', 'cliente.tabela_preco_id', '=', 'tabela_preco.id')
            ->select('cliente.*', 'tabela_preco.descricao as tabela_preco')
            ->orderBy('cliente.nome', 'asc')
            ->get();

        return response()->json($data);
    }

    // SALVAR (mantido com os MESMOS nomes de campos do seu front: txtCliente...)
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'txtClienteCodigo'          => 'required|string|max:255',
            'txtClienteLoja'            => 'required|string|max:255',
            'txtClienteNome'            => 'required|string|max:255',
            'txtClienteNomeFantasia'    => 'nullable|string|max:255',
            'txtClienteTipo'            => 'nullable|string|max:255',
            'txtClienteCGC'             => 'nullable|string|max:255',
            'txtClienteContato'         => 'nullable|string|max:255',
            'txtClienteEndereco'        => 'nullable|string|max:255',
            'txtClienteCidade'          => 'nullable|string|max:255',
            'txtClienteEstado'          => 'nullable|string|max:255',
            'txtClienteKm'              => 'nullable|string|max:255',
            'txtClienteDeslocamento'    => 'nullable|string|max:255',
            'slcClienteTabelaPrecos'    => 'required|numeric|min:0'
        ]);

        $mappedData = [
            'codigo'            => $validatedData['txtClienteCodigo'],
            'loja'              => $validatedData['txtClienteLoja'],
            'nome'              => $validatedData['txtClienteNome'],
            'nome_fantasia'     => $validatedData['txtClienteNomeFantasia'] ?? null,
            'tipo'              => $validatedData['txtClienteTipo'] ?? null,
            'cgc'               => $validatedData['txtClienteCGC'] ?? null,
            'contato'           => $validatedData['txtClienteContato'] ?? null,
            'endereco'          => $validatedData['txtClienteEndereco'] ?? null,
            'municipio'         => $validatedData['txtClienteCidade'] ?? null,
            'estado'            => $validatedData['txtClienteEstado'] ?? null,
            'km'                => $validatedData['txtClienteKm'] ?? null,
            'deslocamento'      => $validatedData['txtClienteDeslocamento'] ?? null,
            'tabela_preco_id'   => $validatedData['slcClienteTabelaPrecos']
        ];

        // Se vier ID, atualiza; senão, cria (opcional, mantém compatibilidade)
        if ($request->filled('id')) {
            $cli = Cliente::find($request->input('id'));
            if (!$cli) {
                return response()->json(['ok'=>false, 'msg'=>'Cliente não encontrado'], 404);
            }
            $cli->update($mappedData);
            return response()->json(['ok'=>true, 'msg'=>'Cliente atualizado', 'data'=>$cli], 200);
        }

        $cliente = Cliente::create($mappedData);

        return response()->json([
            'ok'      => true,
            'msg'     => 'Cliente criado com sucesso',
            'data'    => $cliente,
        ], 201);
    }

    // EXCLUIR (rota: DELETE /excluir-cliente/{id})
    public function delete($id)
{
    $row = Cliente::find($id);
    if (!$row) {
        return response()->json(['ok'=>false,'msg'=>'Cliente não encontrado'], 404);
    }
    $row->delete();
    return response()->json(['ok'=>true,'msg'=>'Cliente excluído']);
}

}
