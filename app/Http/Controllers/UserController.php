<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PessoaJuridicaUsuario;
use App\Models\PagamentoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMail;
use App\Helpers\CpfHelper;

class UserController extends Controller
{
    public function store(Request $request)
    {
        try {
            // ===== 1. Preparar dados =====
            $userId = $request->input('id');
            $isUpdate = !empty($userId);

            // Log da requisição
            \Log::info('UserController::store iniciado', [
                'isUpdate' => $isUpdate,
                'userId' => $userId,
                'method' => $request->method(),
                'contentType' => $request->header('Content-Type'),
            ]);

            // ===== 2. Validar entrada =====
            $validated = $this->validateUserInput($request, $isUpdate);

            // ===== 3. Verificar email único (para novo) =====
            if (!empty($validated['txtUsuarioEmail'])) {
                $emailExists = User::where('email', $validated['txtUsuarioEmail'])
                    ->when(!$isUpdate, fn($q) => $q)  // Se for criar novo
                    ->when($isUpdate, fn($q) => $q->where('id', '!=', $userId))  // Se for update, ignore o próprio
                    ->exists();

                if ($emailExists) {
                    \Log::warning('Email duplicado tentou ser criado', ['email' => $validated['txtUsuarioEmail']]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Email já está cadastrado no sistema',
                        'errors' => ['txtUsuarioEmail' => ['Este email já está em uso']]
                    ], 422);
                }
            }

            // ===== 4. Iniciar transação =====
            DB::beginTransaction();

            try {
                if ($isUpdate) {
                    $user = $this->updateUser($userId, $validated);
                    $message = 'Usuário atualizado com sucesso';
                    $statusCode = 200;
                } else {
                    $user = $this->createUser($validated);
                    $message = 'Usuário criado com sucesso';
                    $statusCode = 201;
                }

                // ===== 4.5 Salvar Pessoa Jurídica (apenas se todos os obrigatórios preenchidos) =====
                try {
                    $pessoaJuridica = $this->validatePessoaJuridica($validated);

                    // Campos OBRIGATÓRIOS: cnpj, razao_social, endereco, numero, bairro, cidade, estado, cep, telefone, email
                    $temTodosCamposObrigatorios =
                        !empty($pessoaJuridica['cnpj']) &&
                        !empty($pessoaJuridica['razao_social']) &&
                        !empty($pessoaJuridica['endereco']) &&
                        !empty($pessoaJuridica['numero']) &&
                        !empty($pessoaJuridica['bairro']) &&
                        !empty($pessoaJuridica['cidade']) &&
                        !empty($pessoaJuridica['estado']) &&
                        !empty($pessoaJuridica['cep']) &&
                        !empty($pessoaJuridica['telefone']) &&
                        !empty($pessoaJuridica['email']);

                    if ($temTodosCamposObrigatorios) {
                        $pessoaJuridica['user_id'] = $user->id;
                        $user->pessoaJuridica()->updateOrCreate(
                            ['user_id' => $user->id],
                            $pessoaJuridica
                        );
                        \Log::info('Pessoa Jurídica salva com sucesso', [
                            'user_id' => $user->id,
                            'cnpj' => $pessoaJuridica['cnpj'] ?? 'vazio'
                        ]);
                    } else {
                        \Log::info('Pessoa Jurídica não salva (faltam campos obrigatórios)', [
                            'user_id' => $user->id,
                            'cnpj' => $pessoaJuridica['cnpj'] ?? 'vazio',
                            'razao_social' => $pessoaJuridica['razao_social'] ?? 'vazio',
                            'estado' => $pessoaJuridica['estado'] ?? 'vazio'
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro ao salvar Pessoa Jurídica', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id
                    ]);
                    throw $e;
                }

                // ===== 4.6 Salvar Dados de Pagamento (apenas se todos os obrigatórios preenchidos) =====
                try {
                    $pagamento = $this->validatePagamento($validated);

                    // Campos OBRIGATÓRIOS: titular_conta, banco, agencia, conta
                    $temTodosCamposPagamento =
                        !empty($pagamento['titular_conta']) &&
                        !empty($pagamento['banco']) &&
                        !empty($pagamento['agencia']) &&
                        !empty($pagamento['conta']);

                    if ($temTodosCamposPagamento) {
                        $pagamento['user_id'] = $user->id;
                        $user->pagamento()->updateOrCreate(
                            ['user_id' => $user->id],
                            $pagamento
                        );
                        \Log::info('Dados de Pagamento salvos com sucesso', [
                            'user_id' => $user->id,
                            'banco' => $pagamento['banco'] ?? 'vazio'
                        ]);
                    } else {
                        \Log::info('Dados de Pagamento não salvos (faltam campos obrigatórios)', [
                            'user_id' => $user->id,
                            'titular_conta' => $pagamento['titular_conta'] ?? 'vazio',
                            'banco' => $pagamento['banco'] ?? 'vazio',
                            'agencia' => $pagamento['agencia'] ?? 'vazio',
                            'conta' => $pagamento['conta'] ?? 'vazio'
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro ao salvar Pagamento', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id
                    ]);
                    throw $e;
                }

                // ===== 5. Commit da transação =====
                DB::commit();

                \Log::info('Usuário salvo com sucesso', ['userId' => $user->id, 'email' => $user->email]);

                // ===== 6. Retornar resposta =====
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'papel' => $user->papel,
                        'ativo' => $user->ativo ?? true,
                    ]
                ], $statusCode);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erro de validação (422)
            \Log::warning('Validação falhou', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação dos dados',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            // Erro de banco de dados (500)
            \Log::error('Erro de banco de dados', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar no banco de dados: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);

        } catch (\Exception $e) {
            // Erro genérico (500)
            \Log::error('Erro ao salvar usuário', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar usuário: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Validar entrada do usuário
     */
    private function validateUserInput($request, $isUpdate = false): array
    {
        $rules = [
            'id'                    => 'nullable|integer',
            'txtUsuarioNome'        => 'required|string|min:3|max:255',
            'txtUsuarioEmail'       => [
                'required',
                'email',
                'max:255',
                $isUpdate ? \Illuminate\Validation\Rule::unique('users', 'email')->ignore($request->input('id'))
                          : \Illuminate\Validation\Rule::unique('users', 'email')
            ],
            'slcUsuarioPapel'       => 'required|in:admin,consultor,financeiro',
            'txtUsuarioDataNasc'    => 'nullable|date_format:Y-m-d',
            'txtUsuarioCelular'     => 'nullable|string|max:20',
            'txtUsuarioCPF'         => 'nullable|string|max:20',
            'txtUsuarioValorHora'   => 'nullable|numeric|min:0',
            'txtUsuarioValorDesloc' => 'nullable|numeric|min:0',
            'txtUsuarioValorKM'     => 'nullable|numeric|min:0',
            'txtUsuarioSalarioBase' => 'nullable|numeric|min:0',

            // Pessoa Jurídica
            'txtPJCNPJ'              => 'nullable|string|max:20',
            'txtPJRazaoSocial'       => 'nullable|string|max:255',
            'txtPJNomeFantasia'      => 'nullable|string|max:255',
            'txtPJInscricaoEstadual' => 'nullable|string|max:20',
            'txtPJInscricaoMunicipal'=> 'nullable|string|max:20',
            'txtPJEndereco'          => 'nullable|string|max:255',
            'txtPJNumero'            => 'nullable|string|max:20',
            'txtPJComplemento'       => 'nullable|string|max:100',
            'txtPJBairro'            => 'nullable|string|max:100',
            'txtPJCidade'            => 'nullable|string|max:100',
            'txtPJEstado'            => 'nullable|string|max:2',
            'txtPJCEP'               => 'nullable|string|max:10',
            'txtPJTelefone'          => 'nullable|string|max:20',
            'txtPJEmail'             => 'nullable|email|max:255',
            'txtPJSite'              => 'nullable|string|max:255',
            'txtPJRamoAtividade'     => 'nullable|string|max:255',
            'txtPJDataConstituicao'  => 'nullable|date_format:Y-m-d',

            // Pagamento
            'txtPagTitularConta'     => 'nullable|string|max:255',
            'txtPagCpfCnpjTitular'   => 'nullable|string|max:20',
            'txtPagBanco'            => 'nullable|string|max:100',
            'txtPagAgencia'          => 'nullable|string|max:20',
            'txtPagConta'            => 'nullable|string|max:20',
            'slcPagTipoConta'        => 'nullable|in:corrente,poupanca',
            'txtPagPixKey'           => 'nullable|string|max:255',
        ];

        return $request->validate($rules, $this->validationMessages());
    }

    /**
     * Mensagens de validação customizadas
     */
    private function validationMessages(): array
    {
        return [
            'txtUsuarioNome.required'  => 'O nome é obrigatório',
            'txtUsuarioNome.min'       => 'O nome deve ter no mínimo 3 caracteres',
            'txtUsuarioEmail.required' => 'O email é obrigatório',
            'txtUsuarioEmail.email'    => 'O email deve ser válido',
            'txtUsuarioEmail.unique'   => 'Este email já está cadastrado',
            'slcUsuarioPapel.required' => 'O papel é obrigatório',
            'slcUsuarioPapel.in'       => 'O papel deve ser admin, consultor ou financeiro',
            'txtUsuarioDataNasc.date_format' => 'A data deve estar no formato YYYY-MM-DD',
        ];
    }

    /**
     * Criar novo usuário
     */
    private function createUser(array $data): User
    {
        // Gerar senha baseada na data de nascimento
        if (!empty($data['txtUsuarioDataNasc'])) {
            $senha = str_replace('-', '', $data['txtUsuarioDataNasc']);
        } else {
            $senha = substr(uniqid(), 0, 8);
        }

        // Limpar CPF (remover máscara)
        $cpf = CpfHelper::clean($data['txtUsuarioCPF'] ?? null);

        // Validar CPF se preenchido
        if (!empty($cpf) && !CpfHelper::isValid($cpf)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'txtUsuarioCPF' => ['O CPF é inválido']
            ]);
        }

        $user = User::create([
            'name'     => $data['txtUsuarioNome'],
            'email'    => $data['txtUsuarioEmail'],
            'password' => Hash::make($senha),
            'papel'    => $data['slcUsuarioPapel'],
            'ativo'    => true,
            'data_nasc' => $data['txtUsuarioDataNasc'] ?? null,
            'cgc'      => $cpf,
            'celular'  => $data['txtUsuarioCelular'] ?? null,
            'valor_hora' => $data['txtUsuarioValorHora'] ?? '0.00',
            'valor_desloc' => $data['txtUsuarioValorDesloc'] ?? '0.00',
            'valor_km' => $data['txtUsuarioValorKM'] ?? '0.00',
            'salario_base' => $data['txtUsuarioSalarioBase'] ?? '0.00',
        ]);

        \Log::info('Novo usuário criado', [
            'userId' => $user->id,
            'email' => $user->email,
            'cpf' => $cpf,
            'senha_provisoria' => $senha
        ]);

        return $user;
    }

    /**
     * Atualizar usuário existente
     */
    private function updateUser($userId, array $data): User
    {
        $user = User::findOrFail($userId);

        // Limpar CPF (remover máscara)
        $cpf = !empty($data['txtUsuarioCPF']) ? CpfHelper::clean($data['txtUsuarioCPF']) : $user->cgc;

        // Validar CPF se preenchido e diferente do anterior
        if (!empty($data['txtUsuarioCPF']) && !empty($cpf) && !CpfHelper::isValid($cpf)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'txtUsuarioCPF' => ['O CPF é inválido']
            ]);
        }

        $user->update([
            'name'     => $data['txtUsuarioNome'],
            'email'    => $data['txtUsuarioEmail'],
            'papel'    => $data['slcUsuarioPapel'],
            'data_nasc' => $data['txtUsuarioDataNasc'] ?? $user->data_nasc,
            'cgc'      => $cpf,
            'celular'  => $data['txtUsuarioCelular'] ?? $user->celular,
            'valor_hora' => $data['txtUsuarioValorHora'] ?? $user->valor_hora,
            'valor_desloc' => $data['txtUsuarioValorDesloc'] ?? $user->valor_desloc,
            'valor_km' => $data['txtUsuarioValorKM'] ?? $user->valor_km,
            'salario_base' => $data['txtUsuarioSalarioBase'] ?? $user->salario_base,
        ]);

        \Log::info('Usuário atualizado', [
            'userId' => $user->id,
            'email' => $user->email,
            'cpf' => $cpf
        ]);

        return $user;
    }

    /**
     * ========================================
     * PESSOA JURÍDICA - MÉTODOS DE VALIDAÇÃO
     * ========================================
     */

    /**
     * Validar e sanitizar dados de Pessoa Jurídica
     */
    private function validatePessoaJuridica(array $data): array
    {
        $cnpj = isset($data['txtPJCNPJ'])
            ? preg_replace('/\D/', '', (string)$data['txtPJCNPJ'])
            : null;

        if (!empty($cnpj) && strlen($cnpj) !== 14) {
            Log::warning('CNPJ inválido', ['cnpj' => $cnpj, 'comprimento' => strlen($cnpj)]);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'txtPJCNPJ' => ['CNPJ deve conter exatamente 14 dígitos']
            ]);
        }

        Log::info('Pessoa Jurídica validada', ['cnpj' => $cnpj]);

        return [
            'user_id'              => $data['user_id'] ?? null,
            'cnpj'                 => $cnpj,
            'razao_social'         => $data['txtPJRazaoSocial'] ?? null,
            'nome_fantasia'        => $data['txtPJNomeFantasia'] ?? null,
            'inscricao_estadual'   => $data['txtPJInscricaoEstadual'] ?? null,
            'inscricao_municipal'  => $data['txtPJInscricaoMunicipal'] ?? null,
            'endereco'             => $data['txtPJEndereco'] ?? null,
            'numero'               => $data['txtPJNumero'] ?? null,
            'complemento'          => $data['txtPJComplemento'] ?? null,
            'bairro'               => $data['txtPJBairro'] ?? null,
            'cidade'               => $data['txtPJCidade'] ?? null,
            'estado'               => $data['txtPJEstado'] ?? null,
            'cep'                  => $data['txtPJCEP'] ?? null,
            'telefone'             => $data['txtPJTelefone'] ?? null,
            'email'                => $data['txtPJEmail'] ?? null,
            'site'                 => $data['txtPJSite'] ?? null,
            'ramo_atividade'       => $data['txtPJRamoAtividade'] ?? null,
            'data_constituicao'    => $data['txtPJDataConstituicao'] ?? null,
        ];
    }

    /**
     * Verificar se CNPJ já existe
     */
    private function checkCNPJDuplicate(?string $cnpj, ?int $userId = null, ?int $pessoaJuridicaId = null): bool
    {
        if (empty($cnpj)) {
            return false;
        }

        $query = PessoaJuridicaUsuario::where('cnpj', $cnpj);

        if (!empty($pessoaJuridicaId)) {
            $query->where('id', '!=', $pessoaJuridicaId);
        } elseif (!empty($userId)) {
            $query->where('user_id', '!=', intval($userId));
        }

        $exists = $query->exists();

        if ($exists) {
            Log::warning('CNPJ duplicado detectado', ['cnpj' => $cnpj]);
        }

        return $exists;
    }

    /**
     * Validar dados de Pagamento
     */
    private function validatePagamento(array $data): array
    {
        $cpfCnpj = isset($data['txtPagCpfCnpjTitular'])
            ? preg_replace('/\D/', '', (string)$data['txtPagCpfCnpjTitular'])
            : null;

        return [
            'user_id'           => $data['user_id'] ?? null,
            'titular_conta'     => $data['txtPagTitularConta'] ?? null,
            'cpf_cnpj_titular'  => $cpfCnpj,
            'banco'             => $data['txtPagBanco'] ?? null,
            'agencia'           => $data['txtPagAgencia'] ?? null,
            'conta'             => $data['txtPagConta'] ?? null,
            'tipo_conta'        => $data['slcPagTipoConta'] ?? 'corrente',
            'pix_key'           => $data['txtPagPixKey'] ?? null,
            'ativo'             => true,
        ];
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
        // ===== CARREGA TODOS OS 34 CAMPOS =====
        // 10 da ABA 1 (users) + 17 da ABA 2 (pessoa_juridica_usuarios) + 7 da ABA 3 (pagamento_usuarios)

        $rows = User::query()
            // LEFT JOIN com Pessoa Jurídica
            ->leftJoin('pessoa_juridica_usuarios', 'users.id', '=', 'pessoa_juridica_usuarios.user_id')
            // LEFT JOIN com Pagamento
            ->leftJoin('pagamento_usuarios', 'users.id', '=', 'pagamento_usuarios.user_id')
            // SELECT de todos os 34 campos
            ->select(
                // ===== ABA 1: Dados Pessoais (10 campos) =====
                'users.id',
                'users.name',
                'users.email',
                'users.celular',
                'users.papel',
                'users.cgc',
                'users.valor_hora',
                'users.valor_desloc',
                'users.valor_km',
                'users.salario_base',
                'users.data_nasc',

                // ===== ABA 2: Pessoa Jurídica (17 campos) =====
                'pessoa_juridica_usuarios.cnpj',
                'pessoa_juridica_usuarios.razao_social',
                'pessoa_juridica_usuarios.nome_fantasia',
                'pessoa_juridica_usuarios.inscricao_estadual',
                'pessoa_juridica_usuarios.inscricao_municipal',
                'pessoa_juridica_usuarios.endereco',
                'pessoa_juridica_usuarios.numero',
                'pessoa_juridica_usuarios.complemento',
                'pessoa_juridica_usuarios.bairro',
                'pessoa_juridica_usuarios.cidade',
                'pessoa_juridica_usuarios.estado',
                'pessoa_juridica_usuarios.cep',
                'pessoa_juridica_usuarios.telefone',
                DB::raw('pessoa_juridica_usuarios.email as email_pj'),
                'pessoa_juridica_usuarios.site',
                'pessoa_juridica_usuarios.ramo_atividade',
                'pessoa_juridica_usuarios.data_constituicao',

                // ===== ABA 3: Dados de Pagamento (7 campos) =====
                'pagamento_usuarios.titular_conta',
                'pagamento_usuarios.cpf_cnpj_titular',
                'pagamento_usuarios.banco',
                'pagamento_usuarios.agencia',
                'pagamento_usuarios.conta',
                'pagamento_usuarios.tipo_conta',
                'pagamento_usuarios.pix_key',

                // Campos adicionais
                'users.ativo',
                'users.created_at'
            )
            ->get()
            ->map(function ($u) {
                return [
                    // ===== ABA 1: Dados Pessoais =====
                    'id'                      => (int)$u->id,
                    'name'                    => (string)($u->name ?? ''),
                    'email'                   => (string)($u->email ?? ''),
                    'data_nasc'               => (string)($u->data_nasc ?? ''),
                    'celular'                 => (string)($u->celular ?? ''),
                    'papel'                   => (string)($u->papel ?? ''),
                    'cgc'                     => (string)($u->cgc ?? ''),
                    'valor_hora'              => isset($u->valor_hora)    ? (string)$u->valor_hora    : '',
                    'valor_desloc'            => isset($u->valor_desloc)  ? (string)$u->valor_desloc  : '',
                    'valor_km'                => isset($u->valor_km)      ? (string)$u->valor_km      : '',
                    'salario_base'            => isset($u->salario_base)  ? (string)$u->salario_base  : '',

                    // ===== ABA 2: Pessoa Jurídica =====
                    'cnpj'                    => (string)($u->cnpj ?? ''),
                    'razao_social'            => (string)($u->razao_social ?? ''),
                    'nome_fantasia'           => (string)($u->nome_fantasia ?? ''),
                    'inscricao_estadual'      => (string)($u->inscricao_estadual ?? ''),
                    'inscricao_municipal'     => (string)($u->inscricao_municipal ?? ''),
                    'endereco'                => (string)($u->endereco ?? ''),
                    'numero'                  => (string)($u->numero ?? ''),
                    'complemento'             => (string)($u->complemento ?? ''),
                    'bairro'                  => (string)($u->bairro ?? ''),
                    'cidade'                  => (string)($u->cidade ?? ''),
                    'estado'                  => (string)($u->estado ?? ''),
                    'cep'                     => (string)($u->cep ?? ''),
                    'telefone'                => (string)($u->telefone ?? ''),
                    'email_pj'                => (string)($u->email_pj ?? ''),
                    'site'                    => (string)($u->site ?? ''),
                    'ramo_atividade'          => (string)($u->ramo_atividade ?? ''),
                    'data_constituicao'       => (string)($u->data_constituicao ?? ''),

                    // ===== ABA 3: Dados de Pagamento =====
                    'titular_conta'           => (string)($u->titular_conta ?? ''),
                    'cpf_cnpj_titular'        => (string)($u->cpf_cnpj_titular ?? ''),
                    'banco'                   => (string)($u->banco ?? ''),
                    'agencia'                 => (string)($u->agencia ?? ''),
                    'conta'                   => (string)($u->conta ?? ''),
                    'tipo_conta'              => (string)($u->tipo_conta ?? ''),
                    'pix_key'                 => (string)($u->pix_key ?? ''),

                    // Campos adicionais
                    'ativo'                   => (bool)($u->ativo ?? false),
                    'created_at'              => $u->created_at ? $u->created_at->toDateTimeString() : '',
                ];
            })
            ->values()
            ->all();

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
