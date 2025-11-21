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

                // ===== 4.5 Salvar Pessoa Jurídica (sempre, mesmo que parcial) =====
                try {
                    $pessoaJuridica = $this->validatePessoaJuridica($validated);
                    // Salvar se houver algum dado preenchido
                    if (!empty($pessoaJuridica['cnpj']) || !empty($pessoaJuridica['razao_social'])) {
                        $pessoaJuridica['user_id'] = $user->id;
                        $user->pessoaJuridica()->updateOrCreate(
                            ['user_id' => $user->id],
                            $pessoaJuridica
                        );
                        \Log::info('Pessoa Jurídica salva com sucesso', [
                            'user_id' => $user->id,
                            'cnpj' => $pessoaJuridica['cnpj'] ?? 'vazio'
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro ao salvar Pessoa Jurídica', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id
                    ]);
                    throw $e;
                }

                // ===== 4.6 Salvar Dados de Pagamento (sempre, mesmo que parcial) =====
                try {
                    $pagamento = $this->validatePagamento($validated);
                    // Salvar se houver algum dado preenchido
                    if (!empty($pagamento['titular_conta']) || !empty($pagamento['banco'])) {
                        $pagamento['user_id'] = $user->id;
                        $user->pagamento()->updateOrCreate(
                            ['user_id' => $user->id],
                            $pagamento
                        );
                        \Log::info('Dados de Pagamento salvos com sucesso', [
                            'user_id' => $user->id,
                            'banco' => $pagamento['banco'] ?? 'vazio'
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
            'txtUsuarioCGC'         => 'nullable|string|max:20',
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

        $user = User::create([
            'name'     => $data['txtUsuarioNome'],
            'email'    => $data['txtUsuarioEmail'],
            'password' => Hash::make($senha),
            'papel'    => $data['slcUsuarioPapel'],
            'ativo'    => true,
            'data_nasc' => $data['txtUsuarioDataNasc'] ?? null,
            'cgc'      => $data['txtUsuarioCGC'] ?? null,
            'celular'  => $data['txtUsuarioCelular'] ?? null,
            'valor_hora' => $data['txtUsuarioValorHora'] ?? '0.00',
            'valor_desloc' => $data['txtUsuarioValorDesloc'] ?? '0.00',
            'valor_km' => $data['txtUsuarioValorKM'] ?? '0.00',
            'salario_base' => $data['txtUsuarioSalarioBase'] ?? '0.00',
        ]);

        \Log::info('Novo usuário criado', [
            'userId' => $user->id,
            'email' => $user->email,
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

        $user->update([
            'name'     => $data['txtUsuarioNome'],
            'email'    => $data['txtUsuarioEmail'],
            'papel'    => $data['slcUsuarioPapel'],
            'data_nasc' => $data['txtUsuarioDataNasc'] ?? $user->data_nasc,
            'cgc'      => $data['txtUsuarioCGC'] ?? $user->cgc,
            'celular'  => $data['txtUsuarioCelular'] ?? $user->celular,
            'valor_hora' => $data['txtUsuarioValorHora'] ?? $user->valor_hora,
            'valor_desloc' => $data['txtUsuarioValorDesloc'] ?? $user->valor_desloc,
            'valor_km' => $data['txtUsuarioValorKM'] ?? $user->valor_km,
            'salario_base' => $data['txtUsuarioSalarioBase'] ?? $user->salario_base,
        ]);

        \Log::info('Usuário atualizado', [
            'userId' => $user->id,
            'email' => $user->email
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
