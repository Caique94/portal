<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PessoaJuridicaUsuario;
use App\Models\PagamentoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMail;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $userId = $request->input('id');
        $isUpdate = !empty($userId);

        // Regras de validação base
        $rules = [
            'txtUsuarioNome'        => 'required|string|max:255',
            'txtUsuarioDataNasc'    => 'nullable|date',
            'txtUsuarioEmail'       => 'required|email|max:255|' . ($isUpdate ? 'unique:users,email,' . $userId : 'unique:users,email'),
            'slcUsuarioPapel'       => 'required|string|max:100',
            'txtUsuarioCGC'         => 'nullable|string|max:255',
            'txtUsuarioCelular'     => 'nullable|string|max:255',
            'txtUsuarioValorHora'   => 'nullable|string',
            'txtUsuarioValorDesloc' => 'nullable|string',
            'txtUsuarioValorKM'     => 'nullable|string',
            'txtUsuarioSalarioBase' => 'nullable|string',
            // Pessoa Jurídica
            'txtPJCNPJ'             => 'nullable|string|max:18|unique:pessoa_juridica_usuario,cnpj' . ($isUpdate ? ',' . $userId . ',user_id' : ''),
            'txtPJRazaoSocial'      => 'nullable|string|max:255',
            'txtPJNomeFantasia'     => 'nullable|string|max:255',
            'txtPJEndereco'         => 'nullable|string|max:255',
            'txtPJNumero'           => 'nullable|string|max:10',
            'txtPJComplemento'      => 'nullable|string|max:255',
            'txtPJBairro'           => 'nullable|string|max:100',
            'txtPJCidade'           => 'nullable|string|max:100',
            'slcPJEstado'           => 'nullable|string|max:2',
            'txtPJCEP'              => 'nullable|string|max:10',
            'txtPJTelefone'         => 'nullable|string|max:20',
            'txtPJEmail'            => 'nullable|email|max:255',
            'txtPJSite'             => 'nullable|string|max:255',
            'txtPJRamoAtividade'    => 'nullable|string|max:255',
            'txtPJDataConstituicao' => 'nullable|date',
            // Pagamento
            'txtPagTitularConta'    => 'nullable|string|max:255',
            'txtPagBanco'           => 'nullable|string|max:100',
            'txtPagAgencia'         => 'nullable|string|max:20',
            'txtPagConta'           => 'nullable|string|max:20',
            'slcPagTipoConta'       => 'nullable|in:corrente,poupanca',
            'txtPagPixKey'          => 'nullable|string|max:255',
        ];

        $v = $request->validate($rules);

        if ($isUpdate) {
            // Atualizar usuário existente
            $user = User::findOrFail($userId);

            $p = [
                'name'   => $v['txtUsuarioNome'],
                'email'  => $v['txtUsuarioEmail'],
                'papel'  => $v['slcUsuarioPapel'],
            ];

            if (Schema::hasColumn('users','data_nasc'))     $p['data_nasc']    = $v['txtUsuarioDataNasc'] ?? null;
            if (Schema::hasColumn('users','cgc'))           $p['cgc']          = $v['txtUsuarioCGC'] ?? null;
            if (Schema::hasColumn('users','celular'))       $p['celular']      = $v['txtUsuarioCelular'] ?? null;
            if (Schema::hasColumn('users','valor_hora'))    $p['valor_hora']   = $v['txtUsuarioValorHora'] ?? null;
            if (Schema::hasColumn('users','valor_desloc'))  $p['valor_desloc'] = $v['txtUsuarioValorDesloc'] ?? null;
            if (Schema::hasColumn('users','valor_km'))      $p['valor_km']     = $v['txtUsuarioValorKM'] ?? null;
            if (Schema::hasColumn('users','salario_base'))  $p['salario_base'] = $v['txtUsuarioSalarioBase'] ?? null;

            $user->update($p);

            // Atualizar Pessoa Jurídica
            if (!empty($v['txtPJCNPJ'])) {
                $pessoaJuridica = [
                    'cnpj'                  => $v['txtPJCNPJ'],
                    'razao_social'          => $v['txtPJRazaoSocial'],
                    'nome_fantasia'         => $v['txtPJNomeFantasia'],
                    'inscricao_estadual'    => $v['txtPJInscricaoEstadual'] ?? null,
                    'inscricao_municipal'   => $v['txtPJInscricaoMunicipal'] ?? null,
                    'endereco'              => $v['txtPJEndereco'],
                    'numero'                => $v['txtPJNumero'],
                    'complemento'           => $v['txtPJComplemento'] ?? null,
                    'bairro'                => $v['txtPJBairro'],
                    'cidade'                => $v['txtPJCidade'],
                    'estado'                => $v['slcPJEstado'],
                    'cep'                   => $v['txtPJCEP'],
                    'telefone'              => $v['txtPJTelefone'],
                    'email'                 => $v['txtPJEmail'],
                    'site'                  => $v['txtPJSite'] ?? null,
                    'ramo_atividade'        => $v['txtPJRamoAtividade'] ?? null,
                    'data_constituicao'     => $v['txtPJDataConstituicao'] ?? null,
                ];
                $user->pessoaJuridica()->updateOrCreate(['user_id' => $user->id], $pessoaJuridica);
            }

            // Atualizar Pagamento
            if (!empty($v['txtPagBanco'])) {
                $pagamento = [
                    'titular_conta'         => $v['txtPagTitularConta'],
                    'cpf_cnpj_titular'      => $v['txtPagCpfCnpjTitular'] ?? null,
                    'banco'                 => $v['txtPagBanco'],
                    'agencia'               => $v['txtPagAgencia'],
                    'conta'                 => $v['txtPagConta'],
                    'tipo_conta'            => $v['slcPagTipoConta'] ?? 'corrente',
                    'pix_key'               => $v['txtPagPixKey'] ?? null,
                ];
                $user->pagamento()->updateOrCreate(['user_id' => $user->id], $pagamento);
            }

            return response()->json(['ok' => true, 'message' => 'Usuário atualizado com sucesso', 'data' => $user], 200);
        } else {
            // Criar novo usuário
            $senhaPlano = isset($v['txtUsuarioDataNasc'])
                ? preg_replace('/\D+/', '', $v['txtUsuarioDataNasc'])
                : substr(uniqid(), 0, 8);

            $p = [
                'name'     => $v['txtUsuarioNome'],
                'email'    => $v['txtUsuarioEmail'],
                'password' => Hash::make($senhaPlano),
                'papel'    => $v['slcUsuarioPapel'],
            ];
            if (Schema::hasColumn('users','data_nasc'))     $p['data_nasc']    = $v['txtUsuarioDataNasc'] ?? null;
            if (Schema::hasColumn('users','cgc'))           $p['cgc']          = $v['txtUsuarioCGC'] ?? null;
            if (Schema::hasColumn('users','celular'))       $p['celular']      = $v['txtUsuarioCelular'] ?? null;
            if (Schema::hasColumn('users','valor_hora'))    $p['valor_hora']   = $v['txtUsuarioValorHora'] ?? null;
            if (Schema::hasColumn('users','valor_desloc'))  $p['valor_desloc'] = $v['txtUsuarioValorDesloc'] ?? null;
            if (Schema::hasColumn('users','valor_km'))      $p['valor_km']     = $v['txtUsuarioValorKM'] ?? null;
            if (Schema::hasColumn('users','salario_base'))  $p['salario_base'] = $v['txtUsuarioSalarioBase'] ?? null;
            if (Schema::hasColumn('users','ativo'))         $p['ativo']        = true;

            $user = User::create($p);

            // Criar Pessoa Jurídica
            if (!empty($v['txtPJCNPJ'])) {
                $user->pessoaJuridica()->create([
                    'cnpj'                  => $v['txtPJCNPJ'],
                    'razao_social'          => $v['txtPJRazaoSocial'],
                    'nome_fantasia'         => $v['txtPJNomeFantasia'],
                    'inscricao_estadual'    => $v['txtPJInscricaoEstadual'] ?? null,
                    'inscricao_municipal'   => $v['txtPJInscricaoMunicipal'] ?? null,
                    'endereco'              => $v['txtPJEndereco'],
                    'numero'                => $v['txtPJNumero'],
                    'complemento'           => $v['txtPJComplemento'] ?? null,
                    'bairro'                => $v['txtPJBairro'],
                    'cidade'                => $v['txtPJCidade'],
                    'estado'                => $v['slcPJEstado'],
                    'cep'                   => $v['txtPJCEP'],
                    'telefone'              => $v['txtPJTelefone'],
                    'email'                 => $v['txtPJEmail'],
                    'site'                  => $v['txtPJSite'] ?? null,
                    'ramo_atividade'        => $v['txtPJRamoAtividade'] ?? null,
                    'data_constituicao'     => $v['txtPJDataConstituicao'] ?? null,
                ]);
            }

            // Criar Pagamento
            if (!empty($v['txtPagBanco'])) {
                $user->pagamento()->create([
                    'titular_conta'         => $v['txtPagTitularConta'],
                    'cpf_cnpj_titular'      => $v['txtPagCpfCnpjTitular'] ?? null,
                    'banco'                 => $v['txtPagBanco'],
                    'agencia'               => $v['txtPagAgencia'],
                    'conta'                 => $v['txtPagConta'],
                    'tipo_conta'            => $v['slcPagTipoConta'] ?? 'corrente',
                    'pix_key'               => $v['txtPagPixKey'] ?? null,
                ]);
            }

            return response()->json(['ok' => true, 'message' => 'Usuário criado com sucesso', 'data' => $user], 201);
        }
    }

    // === ALTERAR SENHA ===
public function changePasswordForm()
{
    return view('auth.change-password');
}

public function changePassword(\Illuminate\Http\Request $r)
{
    $r->validate([
        'current_password' => 'required|string',
        'new_password'     => 'required|string|min:6|confirmed',
    ]);

    $user = $r->user();
    if (!\Illuminate\Support\Facades\Hash::check($r->input('current_password'), $user->password)) {
        return back()->withErrors(['current_password' => 'Senha atual incorreta.'])->withInput();
    }

    $user->password = \Illuminate\Support\Facades\Hash::make($r->input('new_password'));
    $user->save();

    return redirect()->route('home')->with('status','Senha alterada com sucesso.');
}


    // DataTables: retorna JSON e ignora colunas inexistentes
    public function list()
    {
        // colunas base sempre seguras
        $cols = ['id','name','email'];
        // opcionais (só adiciona se existirem)
        foreach (['papel','cgc','celular','ativo','valor_hora','valor_desloc','valor_km','salario_base','data_nasc','created_at'] as $c) {
            if (Schema::hasColumn('users', $c)) $cols[] = $c;
        }

        $rows = User::query()->select($cols)->get()->map(function (User $u) {
            return [
                'id'            => (int)$u->id,
                'name'          => (string)($u->name ?? ''),
                'email'         => (string)($u->email ?? ''),
                'papel'         => (string)($u->papel ?? ''),
                'cgc'           => (string)($u->cgc ?? ''),
                'celular'       => (string)($u->celular ?? ''),
                'ativo'         => (bool)($u->ativo ?? false),
                'valor_hora'    => isset($u->valor_hora)    ? (string)$u->valor_hora    : '0.00',
                'valor_desloc'  => isset($u->valor_desloc)  ? (string)$u->valor_desloc  : '0.00',
                'valor_km'      => isset($u->valor_km)      ? (string)$u->valor_km      : '0.00',
                'salario_base'  => isset($u->salario_base)  ? (string)$u->salario_base  : '0.00',
                'data_nasc'     => method_exists($u, 'getAttribute') && $u->data_nasc ? $u->data_nasc->format('Y-m-d') : '',
                'created_at'    => $u->created_at ? $u->created_at->toDateTimeString() : '',
            ];
        })->values()->all();

        return response()->json(['data' => $rows], 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    public function toggle(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        if (Schema::hasColumn('users','ativo')) {
            $user->ativo = !$user->ativo;
            $user->save();
        }
        return response()->json(['ok' => true, 'data' => $user], 200);
    }

    public function sendPasswordEmail(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            // Generate new random password (8 characters)
            $newPassword = Str::random(8);

            // Update user password
            $user->password = Hash::make($newPassword);
            $user->save();

            // Send email with new password
            Mail::to($user->email)->send(new PasswordResetMail($user, $newPassword));

            return response()->json([
                'ok' => true,
                'message' => 'Senha gerada e enviada com sucesso para ' . $user->email
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Erro ao enviar senha: ' . $e->getMessage()
            ], 500);
        }
    }
}
