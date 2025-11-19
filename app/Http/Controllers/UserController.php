<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PessoaJuridicaUsuario;
use App\Models\PagamentoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
