<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FornecedorController extends Controller
{
    // LISTA
    public function list(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $sql = DB::table('fornecedor')
            ->when($q !== '', function ($w) use ($q) {
                $like = "%{$q}%";
                $w->where(function ($x) use ($like) {
                    $x->where('codigo', 'ILIKE', $like)
                      ->orWhere('nome', 'ILIKE', $like)
                      ->orWhere('cgc', 'ILIKE', $like)
                      ->orWhere('contato', 'ILIKE', $like);
                });
            })
            ->orderByDesc('id');

        return response()->json($sql->get());
    }

    // SALVA (robusto aos nomes de campos do form)
    public function store(Request $request)
    {
        // aceita JSON e form-data
        $in = array_change_key_case($request->json()->all() + $request->all(), CASE_LOWER);

        // normalização de aliases do front
        $codigo = $in['codigo'] ?? $in['cod'] ?? $in['codigo_fornecedor'] ?? null;
        $nome   = $in['nome']   ?? $in['razao'] ?? $in['razao_social'] ?? null;

        if (blank($codigo) || blank($nome)) {
            return response()->json([
                'ok' => false,
                'msg' => 'Campos obrigatórios ausentes: codigo e nome'
            ], 422);
        }

        $data = [
            'codigo'        => trim($codigo),
            'loja'          => $in['loja'] ?? '',
            'nome'          => trim($nome),
            'nome_fantasia' => $in['nome_fantasia'] ?? null,
            'tipo'          => $in['tipo'] ?? null,
            'cgc'           => isset($in['cnpj']) ? preg_replace('/\D+/', '', (string)$in['cnpj']) : null,
            'contato'       => $in['telefone'] ?? $in['contato'] ?? null,
            'endereco'      => $in['endereco'] ?? null,
            'municipio'     => $in['municipio'] ?? null,
            'estado'        => $in['estado'] ?? null,
        ];

        // upsert por codigo
        $exists = DB::table('fornecedor')->where('codigo', $data['codigo'])->first();

        if ($exists) {
            $data['updated_at'] = now();
            DB::table('fornecedor')->where('codigo', $data['codigo'])->update($data);
        } else {
            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::table('fornecedor')->insert($data);
        }

        return response()->json(['ok' => true, 'msg' => 'Fornecedor salvo com sucesso']);
    }

    // EXCLUIR (DELETE /excluir-fornecedor/{id})
    public function delete($id)
    {
        $row = DB::table('fornecedor')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['ok'=>false, 'msg'=>'Fornecedor não encontrado'], 404);
        }

        DB::table('fornecedor')->where('id', $id)->delete();
        return response()->json(['ok'=>true, 'msg'=>'Fornecedor excluído']);
    }
}
