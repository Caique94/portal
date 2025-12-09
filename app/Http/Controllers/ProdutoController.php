<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    private function has(string $col): bool
    {
        return DB::getSchemaBuilder()->hasColumn('produto', $col);
    }

    // GET /listar-produtos
    public function list(Request $request)
    {
        $q = trim((string)$request->get('q',''));

        $hasNome      = $this->has('nome');
        $hasDescricao = $this->has('descricao');
        $hasNarrativa = $this->has('narrativa');

        $select = ['id','codigo','ativo'];
        if ($this->has('is_presencial')) $select[] = 'is_presencial';
        if ($hasNome)      $select[] = 'nome';
        elseif ($hasDescricao) $select[] = DB::raw('descricao AS nome');
        if ($hasNarrativa) $select[] = 'narrativa';

        $sql = DB::table('produto')->select($select);

        if ($q !== '') {
            $like = "%{$q}%";
            $sql->where(function($x) use($like,$hasNome,$hasDescricao){
                $x->where('codigo','ILIKE',$like);
                if ($hasNome)      $x->orWhere('nome','ILIKE',$like);
                if ($hasDescricao) $x->orWhere('descricao','ILIKE',$like);
            });
        }

        if     ($this->has('nome'))      $sql->orderBy('nome','asc');
        elseif ($this->has('descricao')) $sql->orderBy('descricao','asc');
        else                              $sql->orderBy('codigo','asc');

        return response()->json($sql->get(), 200, [], JSON_UNESCAPED_UNICODE);
    }

    // GET /listar-produtos-ativos
    public function active_list()
    {
        $hasNome      = $this->has('nome');
        $hasDescricao = $this->has('descricao');
        $hasNarrativa = $this->has('narrativa');

        $select = ['id','codigo','ativo'];
        if ($this->has('is_presencial')) $select[] = 'is_presencial';
        if ($hasNome)      $select[] = 'nome';
        elseif ($hasDescricao) $select[] = DB::raw('descricao AS nome');
        if ($hasNarrativa) $select[] = 'narrativa';

        $q = DB::table('produto')->select($select)->where('ativo',true);

        if     ($hasNome)      $q->orderBy('nome','asc');
        elseif ($hasDescricao) $q->orderBy('descricao','asc');
        else                    $q->orderBy('codigo','asc');

        return response()->json($q->get(), 200, [], JSON_UNESCAPED_UNICODE);
    }

    // POST /salvar-produto
    public function store(Request $request)
    {
        $in = array_change_key_case(($request->json()->all() ?: []) + $request->all(), CASE_LOWER);

        // Log temporário para debug
        \Log::info('ProdutoController::store - Dados recebidos', [
            'is_presencial' => $in['is_presencial'] ?? 'não enviado',
            'all_data' => $in
        ]);

        // aliases do form
        $codigo = null;
        foreach (['codigo','cod','txtcodigo','txt_produto_codigo','txtprodutocodigo'] as $k) {
            if (isset($in[$k]) && $in[$k] !== '') { $codigo = trim((string)$in[$k]); break; }
        }
        $nomeIn = null;
        foreach (['nome','descricao','descrição','txtdescricao','txt_produto_descricao','txtprodutodescricao'] as $k) {
            if (isset($in[$k]) && $in[$k] !== '') { $nomeIn = trim((string)$in[$k]); break; }
        }
        $narrativa = null;
        foreach (['narrativa','obs','observacao','observação','txtnarrativa'] as $k) {
            if (isset($in[$k]) && $in[$k] !== '') { $narrativa = trim((string)$in[$k]); break; }
        }

        if ($codigo === null || $codigo === '' || $nomeIn === null || $nomeIn === '') {
            return response()->json(['ok'=>false,'msg'=>'Campos obrigatórios: codigo e nome/descricao.'], 422);
        }

        // normalizações
        $preco_base = $in['preco_base'] ?? null;
        if (is_string($preco_base)) {
            $preco_base = str_replace('.','',$preco_base);
            $preco_base = str_replace(',','.',$preco_base);
        }
        $unidade = isset($in['unidade']) ? trim((string)$in['unidade']) : null;
        $ativo   = array_key_exists('ativo',$in)
            ? in_array($in['ativo'], [true,1,'1','true','on','yes','sim'], true)
            : true;
        $is_presencial = array_key_exists('is_presencial',$in)
            ? in_array($in['is_presencial'], [true,1,'1','true','on','yes','sim'], true)
            : false;

        // monta payload SOMENTE com colunas existentes (evita "column does not exist" -> 500)
        $data = ['codigo'=>$codigo];
        if ($this->has('nome'))      $data['nome']      = $nomeIn;
        if ($this->has('descricao')) $data['descricao'] = $nomeIn;
        if ($this->has('narrativa') && $narrativa !== null) $data['narrativa'] = $narrativa;
        if ($this->has('unidade') && $unidade !== null)     $data['unidade']   = $unidade;
        if ($this->has('preco_base') && $preco_base !== null) $data['preco_base'] = (float)$preco_base;
        if ($this->has('is_presencial')) $data['is_presencial'] = $is_presencial;
        if ($this->has('ativo'))     $data['ativo']     = $ativo;
        if ($this->has('updated_at'))$data['updated_at']= now();

        \Log::info('ProdutoController::store - Dados que serão salvos', [
            'data' => $data,
            'is_presencial_value' => $is_presencial,
            'has_is_presencial' => $this->has('is_presencial')
        ]);

        $id = $in['id'] ?? null;

        // update por id
        if (!empty($id)) {
            $row = DB::table('produto')->where('id',$id)->first();
            if (!$row) return response()->json(['ok'=>false,'msg'=>'Produto não encontrado.'],404);

            $dup = DB::table('produto')->where('codigo',$codigo)->where('id','<>',$row->id)->exists();
            if ($dup) return response()->json(['ok'=>false,'msg'=>'Código já utilizado por outro produto.'],422);

            DB::table('produto')->where('id',$row->id)->update($data);

            $out = DB::table('produto')->where('id',$row->id)->first();
            return response()->json(['ok'=>true,'msg'=>'Produto atualizado','data'=>$out]);
        }

        // upsert por codigo
        $exists = DB::table('produto')->where('codigo',$codigo)->first();
        if ($exists) {
            DB::table('produto')->where('id',$exists->id)->update($data);
            $out = DB::table('produto')->where('id',$exists->id)->first();
            return response()->json(['ok'=>true,'msg'=>'Produto atualizado','data'=>$out]);
        }

        if ($this->has('created_at')) $data['created_at'] = now();
        $newId = DB::table('produto')->insertGetId($data);

        $out = DB::table('produto')->where('id',$newId)->first();
        return response()->json(['ok'=>true,'msg'=>'Produto cadastrado','data'=>$out],201);
    }

    // GET /toggle-produto/{id}
    public function toggle($id)
    {
        $row = DB::table('produto')->where('id',$id)->first();
        if (!$row) return response()->json(['ok'=>false,'msg'=>'Produto não encontrado'],404);

        $novo = !$row->ativo;
        $upd  = ['ativo'=>$novo];
        if ($this->has('updated_at')) $upd['updated_at'] = now();

        DB::table('produto')->where('id',$id)->update($upd);
        return response()->json(['ok'=>true,'msg'=>'Status atualizado','data'=>['id'=>(int)$id,'ativo'=>$novo]]);
    }

    // DELETE /excluir-produto/{id}
    public function delete($id)
    {
        $row = DB::table('produto')->where('id',$id)->first();
        if (!$row) return response()->json(['ok'=>false,'msg'=>'Produto não encontrado'],404);

        DB::table('produto')->where('id',$id)->delete();
        return response()->json(['ok'=>true,'msg'=>'Produto excluído']);
    }

    // GET /gerar-proximo-codigo-produto
    public function gerarProximoCodigo()
    {
        // Busca o maior código numérico existente
        $maxCodigo = DB::table('produto')
            ->selectRaw('MAX(CAST(codigo AS INTEGER)) as max_codigo')
            ->whereRaw('codigo ~ \'^[0-9]+$\'') // Apenas códigos numéricos
            ->value('max_codigo');

        // Se não houver nenhum código, começa do 1, senão incrementa
        $proximoCodigo = $maxCodigo ? (int)$maxCodigo + 1 : 1;

        // Formata com zeros à esquerda (exemplo: 0001, 0002, etc.)
        $codigoFormatado = str_pad($proximoCodigo, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'ok' => true,
            'codigo' => $codigoFormatado
        ]);
    }
}
