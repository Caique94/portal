<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Contato;
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

    // GERAR PRÓXIMO CÓDIGO
    public function gerarProximoCodigo()
    {
        $sequence = \App\Models\Sequence::getSequence('cliente');
        $nextNumber = $sequence->current_number + 1;

        $paddedNumber = str_pad(
            (string) $nextNumber,
            $sequence->min_digits,
            '0',
            STR_PAD_LEFT
        );

        $proximoCodigo = $sequence->prefix . $paddedNumber;

        return response()->json(['codigo' => $proximoCodigo]);
    }

    // SALVAR (mantido com os MESMOS nomes de campos do seu front: txtCliente...)
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'txtClienteCodigo'          => 'nullable|string|max:255',
            'txtClienteLoja'            => 'required|string|max:255',
            'txtClienteNome'            => 'required|string|max:255',
            'txtClienteNomeFantasia'    => 'nullable|string|max:255',
            'txtClienteTipo'            => 'nullable|string|max:255',
            'txtClienteCGC'             => 'nullable|string|max:255',
            'txtClienteContato'         => 'nullable|string|max:255',  // Opcional - pode ser preenchido após criar contatos
            'txtClienteEndereco'        => 'nullable|string|max:255',
            'txtClienteCidade'          => 'nullable|string|max:255',
            'txtClienteEstado'          => 'nullable|string|max:255',
            'txtClienteKm'              => 'nullable|string|max:255',
            'txtClienteValorHora'       => 'nullable|numeric|min:0',
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
            'valor_hora'        => $validatedData['txtClienteValorHora'] ?? null,
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

        // Processar contatos novos se houver
        if ($request->filled('contatos_novos')) {
            $contatosNovos = json_decode($request->input('contatos_novos'), true);

            if (is_array($contatosNovos)) {
                foreach ($contatosNovos as $contatoData) {
                    Contato::create([
                        'cliente_id' => $cliente->id,
                        'nome' => $contatoData['nome'] ?? null,
                        'email' => $contatoData['email'] ?? null,
                        'telefone' => $contatoData['telefone'] ?? null,
                        'aniversario' => $contatoData['aniversario'] ?? null,
                        'recebe_email_os' => $contatoData['recebe_email_os'] ?? false
                    ]);
                }
            }
        }

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
