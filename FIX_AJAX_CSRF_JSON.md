# Patch CSRF + JSON Response - Correção Completa
## Solução Pronta para Ajax + DataTables + Laravel Session/Sanctum

**Versão:** 1.0
**Status:** Pronto para Deploy
**Tempo aplicação:** 15-20 minutos
**Risco:** Baixo (mudanças isoladas, sem quebra de compatibilidade)

---

## 📋 Problemas Resolvidos

✅ **POST 422 e 419** → Responses em JSON ao invés de HTML
✅ **SyntaxError: Unexpected token '<'** → Backend sempre retorna JSON
✅ **DataTables Ajax error** → Recebe JSON válido
✅ **Session expirada (419)** → JSON com mensagem clara
✅ **Validação (422)** → Erros em JSON estruturado
✅ **CSRF Token mismatch** → Enviado automaticamente em todas requisições

---

## 🔧 Arquivos a Modificar

1. `resources/views/layouts/app.blade.php` (ou seu master layout)
2. `public/js/global-ajax.js` (novo arquivo)
3. `app/Http/Controllers/UserController.php`
4. `app/Http/Controllers/OrdemServicoController.php`
5. `app/Exceptions/Handler.php`
6. `public/js/cadastros/usuarios.js` (DataTables)
7. `.env`
8. `config/session.php` (se usar sessão de cookie)

---

# PARTE 1: MUDANÇAS EM ARQUIVOS

## 1️⃣ Blade Template - Adicionar Meta CSRF Token

**Arquivo:** `resources/views/layouts/app.blade.php`

**ANTES:**
```html
<head>
    <!-- ... outros metas ... -->
</head>
```

**DEPOIS:**
```html
<head>
    <!-- ... outros metas ... -->

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Accept JSON Header -->
    <meta name="api-base-url" content="{{ url('/') }}">
</head>

<!-- ... no final do body, ANTES de outros scripts ... -->
<body>
    @yield('content')

    <!-- Scripts globais (PRIMEIRO) -->
    <script src="{{ asset('js/global-ajax.js') }}"></script>

    <!-- Depois seus outros scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/cadastros/usuarios.js') }}"></script>
</body>
```

**Por quê:**
- `<meta name="csrf-token">` permite que JS acesse o token
- Ordem dos scripts importa: global-ajax.js configura $.ajaxSetup antes de qualquer chamada

---

## 2️⃣ Novo Arquivo: Global AJAX Setup

**Arquivo:** `public/js/global-ajax.js` (NOVO - crie este arquivo)

```javascript
/**
 * Global AJAX Configuration
 * Aplica CSRF token, Accept header e tratamento de erros a TODAS requisições
 */

$(document).ready(function() {
    // ========== CONFIGURAÇÃO GLOBAL DE AJAX ==========
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'  // ← Crucial: avisa que esperamos JSON
        },
        xhrFields: {
            withCredentials: true  // ← Envia cookies com requests cross-origin
        },
        timeout: 30000,  // 30 segundos

        // Error handler global para TODAS as requisições AJAX
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                response: xhr.responseText.substring(0, 200)
            });

            // Tratamento específico por status
            if (xhr.status === 401) {
                console.error('❌ Não autenticado - redirecionar para login?');
                // window.location.href = '/login';
            } else if (xhr.status === 419) {
                console.error('❌ Sessão expirada - token CSRF inválido');
                // Recarregar página ou redirecionar para login
                // window.location.reload();
            } else if (xhr.status === 422) {
                console.log('⚠️ Validação falhou - handler específico deve tratar');
            } else if (xhr.status === 500) {
                console.error('❌ Erro no servidor');
            }
        }
    });

    // ========== FUNÇÃO UTILITÁRIA ==========
    /**
     * Parse seguro de JSON
     * Se resposta não for JSON válida, retorna objeto com _raw
     */
    window.safeParseJson = function(responseText) {
        try {
            return JSON.parse(responseText);
        } catch (e) {
            console.error('⚠️ Resposta não-JSON recebida:', responseText.substring(0, 200));
            return {
                _error: true,
                _raw: responseText,
                message: 'Resposta inválida do servidor'
            };
        }
    };

    // ========== FUNÇÃO PARA REQUISIÇÕES JSON ==========
    /**
     * Fazer requisição AJAX com JSON
     * Automaticamente adiciona headers necessários
     */
    window.ajaxJson = function(options) {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            dataType: 'json',
            contentType: 'application/json',
            processData: false
        };

        // Se data é objeto, converter para JSON
        if (options.data && typeof options.data === 'object') {
            options.data = JSON.stringify(options.data);
        }

        return $.ajax($.extend({}, defaults, options));
    };

    // ========== FUNÇÃO PARA REQUISIÇÕES FORM-ENCODED ==========
    /**
     * Fazer requisição AJAX com form-encoded (fallback)
     */
    window.ajaxForm = function(options) {
        const defaults = {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            dataType: 'json',
            processData: true
        };

        return $.ajax($.extend({}, defaults, options));
    };

    console.log('✓ Global AJAX Setup carregado');
});
```

**Por quê:**
- `X-CSRF-TOKEN` header: Enviado automaticamente em TODAS as requisições
- `Accept: application/json`: Sinaliza que esperamos JSON (não HTML)
- `withCredentials: true`: Envia cookies mesmo em requisições cross-origin
- Error handler global: Centraliza tratamento de erros
- Funções utilitárias: Simplificam chamadas AJAX

---

## 3️⃣ UserController - Sempre Retornar JSON

**Arquivo:** `app/Http/Controllers/UserController.php`

**ANTES:**
```php
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator);  // ← PROBLEMA: Redireciona (HTML)
    }

    $user = User::create($request->all());
    return redirect('/usuarios')->with('success', 'Usuário criado');  // ← PROBLEMA: HTML
}
```

**DEPOIS:**
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Salvar novo usuário ou atualizar existente
     * Retorna JSON em todos os casos (sucesso, validação, erro)
     */
    public function store(Request $request)
    {
        // ========== VALIDAÇÃO ==========
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($request->input('id') ?? 'NULL'),
            'celular' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|unique:users,cpf,' . ($request->input('id') ?? 'NULL'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $validator->errors()  // ← JSON com erros estruturados
            ], 422);  // ← 422 Unprocessable Entity
        }

        try {
            $id = $request->input('id');
            $isUpdate = !empty($id);

            DB::beginTransaction();

            if ($isUpdate) {
                // ========== UPDATE ==========
                $user = User::findOrFail($id);
                $user->update($request->only(['name', 'email', 'celular', 'cpf']));

                Log::info("Usuário atualizado: #{$user->id}");

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Usuário atualizado com sucesso',
                    'data' => $user
                ], 200);
            } else {
                // ========== CREATE ==========
                $user = User::create($request->only(['name', 'email', 'celular', 'cpf']));

                Log::info("Novo usuário criado: #{$user->id}");

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Usuário criado com sucesso',
                    'data' => $user
                ], 201);  // ← 201 Created
            }

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error("Erro DB ao salvar usuário: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar usuário (dados duplicados?)',
                'error' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro inesperado ao salvar usuário: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar usuários (para DataTables)
     * Retorna JSON estruturado
     */
    public function list()
    {
        try {
            $users = User::select('id', 'name', 'email', 'celular', 'cpf', 'created_at')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'data' => $users
            ], 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao listar usuários: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar usuários',
                'data' => []
            ], 500);
        }
    }
}
```

**Por quê:**
- `response()->json(...)`: Sempre retorna JSON (não redirect/HTML)
- `422` status: Padrão REST para validação falhada
- `201` status: Padrão REST para recurso criado
- `200` status: Sucesso (GET, PUT, POST bem-sucedido)
- `500` status: Erro no servidor
- `try-catch`: Captura exceções e retorna JSON
- `DB::beginTransaction()/commit()/rollBack()`: Garante consistência

---

## 4️⃣ OrdemServicoController - JSON para Lista

**Arquivo:** `app/Http/Controllers/OrdemServicoController.php`

Se você tem um endpoint `/listar-ordens-faturamento`, garanta que retorna JSON:

```php
public function list_invoice()
{
    try {
        $ordens = DB::table('ordem_servico as os')
            ->join('cliente', 'os.cliente_id', '=', 'cliente.id')
            ->join('users', 'os.consultor_id', '=', 'users.id')
            ->select(
                'os.id',
                'os.numero',
                'os.data_emissao',
                'cliente.id as cliente_id',
                'cliente.nome as cliente_nome',
                'users.name as consultor_nome',
                'os.assunto',
                'os.valor_total',
                'os.status'
            )
            ->orderByDesc('os.created_at')
            ->get();

        return response()->json([
            'data' => $ordens
        ], 200);

    } catch (\Exception $e) {
        Log::error('Erro ao listar ordens: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao listar ordens',
            'data' => []
        ], 500);
    }
}
```

---

## 5️⃣ Exception Handler - Transformar Exceções em JSON

**Arquivo:** `app/Exceptions/Handler.php`

**ANTES:**
```php
public function render($request, Throwable $exception)
{
    return parent::render($request, $exception);
}
```

**DEPOIS:**
```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types that are not reported.
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Renderizar exceção como resposta
     * CRUCIAL: Retornar JSON para AJAX/API, HTML para browser
     */
    public function render($request, Throwable $exception)
    {
        // ========== REQUISIÇÕES QUE ESPERAM JSON ==========
        if ($request->expectsJson()) {
            return $this->renderJsonResponse($request, $exception);
        }

        // ========== REQUISIÇÕES NORMAIS (BROWSER) ==========
        return parent::render($request, $exception);
    }

    /**
     * Renderizar resposta JSON para exceções
     */
    protected function renderJsonResponse($request, Throwable $exception)
    {
        // ========== TOKEN MISMATCH / SESSÃO EXPIRADA ==========
        if ($exception instanceof TokenMismatchException) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão expirada ou CSRF token inválido',
                'code' => 419
            ], 419);
        }

        // ========== NÃO AUTENTICADO ==========
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado - faça login',
                'code' => 401
            ], 401);
        }

        // ========== VALIDAÇÃO FALHOU ==========
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $exception->errors(),
                'code' => 422
            ], 422);
        }

        // ========== MODELO NÃO ENCONTRADO (404) ==========
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso não encontrado',
                'code' => 404
            ], 404);
        }

        // ========== OUTROS ERROS ==========
        $statusCode = $exception->getCode() ?: 500;
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 500;
        }

        return response()->json([
            'success' => false,
            'message' => $exception->getMessage() ?: 'Erro no servidor',
            'code' => $statusCode
        ], $statusCode);
    }
}
```

**Por quê:**
- `$request->expectsJson()`: Detecta automaticamente se é AJAX/API
- `TokenMismatchException` → 419: CSRF token inválido
- `AuthenticationException` → 401: Não autenticado
- `ValidationException` → 422: Validação falhou
- Exceções não capturadas → 500: Erro genérico

---

## 6️⃣ DataTables - Garantir JSON

**Arquivo:** `public/js/cadastros/usuarios.js`

**IMPORTANTE:** Use a função `ajaxJson()` que definimos em global-ajax.js

```javascript
$(function () {
    const $tbl = $('#tblUsuarios');

    const tblUsuarios = $tbl.DataTable({
        ajax: {
            url: '/listar-usuarios',
            type: 'GET',
            dataSrc: 'data',

            // ========== HEADERS EXPLÍCITOS ==========
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },

            // ========== ERROR HANDLER ==========
            error: function(xhr, status, error) {
                console.error('DataTables AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    response: xhr.responseText.substring(0, 200)
                });

                let errorMsg = 'Erro ao carregar dados';
                if (xhr.status === 401) {
                    errorMsg = 'Sessão expirada - faça login novamente';
                } else if (xhr.status === 403) {
                    errorMsg = 'Acesso negado';
                } else if (xhr.status === 404) {
                    errorMsg = 'Rota não encontrada';
                } else if (xhr.status === 500) {
                    errorMsg = 'Erro no servidor';
                }

                Toast.fire({
                    icon: 'error',
                    title: errorMsg
                });
            }
        },

        columns: [
            { title: 'Nome', data: 'name', defaultContent: '' },
            { title: 'Email', data: 'email', defaultContent: '' },
            { title: 'Celular', data: 'celular', defaultContent: '' },
        ],

        order: [[0, 'asc']],
        pageLength: 10
    });

    // ========== SALVAR USUÁRIO VIA AJAX ==========
    $('#btn-salvar-usuario').on('click', function() {
        const jsonData = {
            id: $('#usuario-id').val() || null,
            name: $('#usuario-name').val(),
            email: $('#usuario-email').val(),
            celular: $('#usuario-celular').val(),
        };

        ajaxJson({
            url: '/salvar-usuario',
            type: 'POST',
            data: jsonData,
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                tblUsuarios.ajax.reload();
                $('#modal-usuario').modal('hide');
            },
            error: function(xhr) {
                const response = safeParseJson(xhr.responseText);

                if (xhr.status === 422) {
                    // Validação falhou - mostrar erros
                    let errorMsg = '';
                    $.each(response.errors, function(field, messages) {
                        errorMsg += messages.join(', ') + '\n';
                    });
                    Toast.fire({
                        icon: 'error',
                        title: 'Validação falhou',
                        text: errorMsg
                    });
                } else if (xhr.status === 419) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Sessão expirada'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Erro ao salvar'
                    });
                }
            }
        });
    });
});
```

**Por quê:**
- `ajaxJson()`: Automático CSRF + Accept headers
- Headers explícitos: Redundância para segurança
- Error handler específico: Trata 422, 419, outros erros
- `safeParseJson()`: Fallback se resposta for HTML

---

## 7️⃣ .env - Sessão e Sanctum

**Arquivo:** `.env`

```env
# ========== SESSION & CSRF ==========
SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
SESSION_LIFETIME=120

# ========== SANCTUM (se usar) ==========
SANCTUM_STATEFUL_DOMAINS=localhost:8000,localhost:3000
SANCTUM_TOKEN_EXPIRATION=525600

# ========== CORS (se frontend em porta diferente) ==========
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8000
```

**Explicação:**
- `SESSION_DRIVER=cookie`: Usa cookie para armazenar sessão
- `SESSION_DOMAIN=localhost`: Garante que cookie funciona em localhost
- `SANCTUM_STATEFUL_DOMAINS`: Permite requisições sem token (usa cookie)
- `CORS_ALLOWED_ORIGINS`: Se frontend em porta diferente

---

## 8️⃣ config/session.php - Ajuste (se necessário)

**Arquivo:** `config/session.php`

Garanta que essas linhas estão assim:

```php
return [
    'driver' => env('SESSION_DRIVER', 'cookie'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => true,
    'files' => storage_path('framework/sessions'),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => 'XSRF-TOKEN',  // ← Nome do cookie
    'path' => '/',
    'domain' => env('SESSION_DOMAIN', null),  // ← Usa do .env
    'secure' => env('SESSION_SECURE_COOKIES', false),  // localhost = false
    'http_only' => true,
    'same_site' => 'lax',  // ← Importante para CSRF
];
```

---

# PARTE 2: COMANDOS ARTISAN

Execute na raiz do projeto:

```bash
# Limpar todos os caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:clear

# Ou tudo de uma vez:
php artisan optimize:clear

# Reiniciar o servidor (se necessário)
php artisan serve --host=localhost --port=8000
```

---

# PARTE 3: TESTES COM CURL

## Obter CSRF Token

Primeiro, faça uma requisição GET para obter o token:

```bash
curl -i -c cookies.txt http://localhost:8000/login
# Ou qualquer página que tenha meta csrf-token
```

Extraia o token da resposta HTML ou use:

```bash
curl -s http://localhost:8000 | grep -o 'csrf-token" content="[^"]*' | cut -d'"' -f4
```

Vamos chamar este token de `$TOKEN`.

---

## ✅ Teste 1: POST Válido (201 Created)

```bash
curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $TOKEN" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "celular": "11999999999"
  }'
```

**Resposta Esperada:**
```
HTTP/1.1 201 Created
Content-Type: application/json

{
  "success": true,
  "message": "Usuário criado com sucesso",
  "data": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com",
    "celular": "11999999999",
    "created_at": "2025-11-20T10:30:00Z"
  }
}
```

---

## ⚠️ Teste 2: Validação Falha (422 Unprocessable Entity)

```bash
curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $TOKEN" \
  -d '{
    "name": "",
    "email": "invalido"
  }'
```

**Resposta Esperada:**
```
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/json

{
  "success": false,
  "message": "Validação falhou",
  "errors": {
    "name": ["O campo nome é obrigatório"],
    "email": ["O campo email deve ser um email válido"]
  }
}
```

---

## 🔐 Teste 3: CSRF Token Ausente/Inválido (419 Page Expired)

```bash
curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: token-invalido" \
  -d '{
    "name": "João",
    "email": "joao@example.com"
  }'
```

**Resposta Esperada:**
```
HTTP/1.1 419 Page Expired
Content-Type: application/json

{
  "success": false,
  "message": "Sessão expirada ou CSRF token inválido",
  "code": 419
}
```

---

## 📊 Teste 4: GET DataTables (200 OK)

```bash
curl -i -X GET http://localhost:8000/listar-usuarios \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $TOKEN"
```

**Resposta Esperada:**
```
HTTP/1.1 200 OK
Content-Type: application/json

{
  "data": [
    {
      "id": 1,
      "name": "João Silva",
      "email": "joao@example.com",
      "celular": "11999999999",
      "created_at": "2025-11-20T10:30:00Z"
    }
  ]
}
```

---

## ❌ Teste 5: Accept Header Errado (demonstra o problema)

```bash
curl -i -X GET http://localhost:8000/listar-usuarios \
  -H "Accept: text/html"
```

**Resposta Antes (❌ PROBLEMA):**
```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8

<!DOCTYPE html>
<html>
  <head><title>Usuários</title></head>
  <body>...</body>
</html>
```

**Resposta Depois (✅ CORRIGIDO):**
```
HTTP/1.1 200 OK
Content-Type: application/json

{
  "data": [...]
}
```

---

# PARTE 4: SNIPPET JAVASCRIPT COMPLETO

Para usar em seus arquivos .js (além do global-ajax.js):

```javascript
/**
 * Exemplo completo de requisição AJAX com tratamento de erro
 */

function salvarDados(dados) {
    // Usar a função ajaxJson definida em global-ajax.js
    ajaxJson({
        url: '/salvar-usuario',
        type: 'POST',
        data: dados,
        success: function(response) {
            console.log('✓ Sucesso:', response);

            // Mostrar mensagem
            Toast.fire({
                icon: 'success',
                title: response.message,
                timer: 2000
            });

            // Recarregar tabela se existir
            if (typeof tblUsuarios !== 'undefined') {
                tblUsuarios.ajax.reload();
            }

            // Fechar modal se existir
            $('#modal-usuario').modal('hide');
        },
        error: function(xhr) {
            const response = safeParseJson(xhr.responseText);

            console.error('✗ Erro:', {
                status: xhr.status,
                response: response
            });

            // Tratamento por status
            switch (xhr.status) {
                case 422:  // Validação falhou
                    let erros = '';
                    if (response.errors) {
                        $.each(response.errors, function(field, messages) {
                            erros += `${field}: ${messages.join(', ')}\n`;
                        });
                    }
                    Toast.fire({
                        icon: 'error',
                        title: 'Validação Falhou',
                        text: erros,
                        html: `<pre>${erros}</pre>`
                    });
                    break;

                case 419:  // CSRF token inválido / sessão expirada
                    Toast.fire({
                        icon: 'warning',
                        title: 'Sessão Expirada',
                        text: 'Recarregue a página e tente novamente'
                    });
                    setTimeout(() => location.reload(), 2000);
                    break;

                case 401:  // Não autenticado
                    Toast.fire({
                        icon: 'error',
                        title: 'Não Autenticado',
                        text: 'Faça login novamente'
                    });
                    setTimeout(() => window.location.href = '/login', 2000);
                    break;

                case 500:  // Erro no servidor
                    Toast.fire({
                        icon: 'error',
                        title: 'Erro no Servidor',
                        text: response.message || 'Tente novamente mais tarde'
                    });
                    break;

                default:
                    Toast.fire({
                        icon: 'error',
                        title: `Erro ${xhr.status}`,
                        text: response.message || 'Erro ao processar requisição'
                    });
            }
        }
    });
}

// Chamar assim:
$('#btn-salvar').on('click', function() {
    const dados = {
        id: $('#usuario-id').val() || null,
        name: $('#usuario-name').val(),
        email: $('#usuario-email').val(),
        celular: $('#usuario-celular').val()
    };

    salvarDados(dados);
});
```

---

# PARTE 5: CHECKLIST DE DEPLOY LOCAL

Siga este checklist antes de testar:

- [ ] **Criar** `public/js/global-ajax.js` com código da Parte 1, item 2
- [ ] **Atualizar** `resources/views/layouts/app.blade.php` (meta csrf-token + script)
- [ ] **Atualizar** `app/Http/Controllers/UserController.php`
- [ ] **Atualizar** `app/Http/Controllers/OrdemServicoController.php` (se tem `list_invoice`)
- [ ] **Atualizar** `app/Exceptions/Handler.php`
- [ ] **Atualizar** `public/js/cadastros/usuarios.js` (DataTables + ajaxJson)
- [ ] **Atualizar** `.env` (SESSION_DOMAIN, SANCTUM_STATEFUL_DOMAINS se usar)
- [ ] **Verificar** `config/session.php` (session.same_site = 'lax')
- [ ] **Executar** `php artisan optimize:clear` (ou comandos individuais)
- [ ] **Testar** com curl (tests 1-5 acima)
- [ ] **Testar** no browser:
  - [ ] Abrir http://localhost:8000
  - [ ] F12 → Console (não deve ter erro de CSRF)
  - [ ] F12 → Network → fazer POST
  - [ ] Verificar que response é JSON (não HTML)
  - [ ] Verificar header `Accept: application/json`
- [ ] **Testar** DataTables:
  - [ ] Abrir página com tabela
  - [ ] F12 → Network
  - [ ] Procurar requisição GET `/listar-usuarios`
  - [ ] Response deve ser JSON, não HTML
- [ ] **Testar** validação:
  - [ ] Enviar dados inválidos
  - [ ] Esperar 422 com `errors` em JSON

---

# PARTE 6: POR QUE CADA MUDANÇA RESOLVE

| # | Mudança | Problema Resolvido | Como |
|---|---------|-------------------|------|
| **1** | `<meta name="csrf-token">` no Blade | 419 Token Mismatch | JS consegue ler token e enviar no header |
| **2** | `X-CSRF-TOKEN` header em global-ajax.js | Todas requisições eram rejeitadas | Header adicionado automaticamente |
| **3** | `Accept: application/json` header | Backend retornava HTML ao invés de JSON | Laravel detecta `expectsJson()` corretamente |
| **4** | `response()->json(...)` no Controller | POST 422 e 500 eram HTML/redirect | Sempre JSON em requests AJAX/API |
| **5** | Exception Handler com `expectsJson()` | 419 e 401 retornavam página HTML inteira | Detecta AJAX e retorna JSON estruturado |
| **6** | DataTables headers explícitos + error handler | "Ajax error" genérico do DataTables | Handler específico trata 422/419/401 |

---

# PARTE 7: TROUBLESHOOTING

## Problema: Ainda recebendo HTML em requisição POST

**Verificação:**
```bash
curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}' \
  | head -5
```

**Se mostrar `<!DOCTYPE html>` → Handler.php não foi atualizado**

Solução:
```bash
php artisan view:clear
php artisan config:cache
php artisan optimize:clear
```

---

## Problema: 419 Token Mismatch mesmo com header

**Verificação:**
```bash
# Obter token
TOKEN=$(curl -s http://localhost:8000 | grep -o 'csrf-token" content="[^"]*' | cut -d'"' -f4)

# Testar
curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "X-CSRF-TOKEN: $TOKEN" \
  -H "Accept: application/json" \
  -d '{}'
```

**Causas comuns:**
- Token expirado (refreshar página)
- `.env` não recarregado (rodou `config:cache`?)
- Session driver incorreto (deve ser `cookie`)

Solução:
```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
# Atualizar .env se necessário
```

---

## Problema: DataTables "Ajax error" continua

**F12 → Network → clique na requisição GET `/listar-usuarios`:**

Se Response mostra HTML → Controller não tem `response()->json()`

Se Response é JSON mas ainda erro → Verificar console da F12

```javascript
// No console, testar:
$.ajax({
    url: '/listar-usuarios',
    type: 'GET',
    headers: {
        'Accept': 'application/json'
    },
    success: function(data) {
        console.log('✓ Data:', data);
    },
    error: function(xhr) {
        console.error('✗ Error:', xhr.status, xhr.responseText);
    }
});
```

---

## Problema: CORS error (se frontend em porta diferente)

**Erro típico:**
```
Access to XMLHttpRequest at 'http://localhost:8000/listar-usuarios' from origin 'http://localhost:3000' has been blocked by CORS policy
```

**Solução:**

1. Instalar CORS middleware:
```bash
composer require fruitcake/laravel-cors
```

2. Registrar em `app/Http/Kernel.php`:
```php
protected $middlewareGroups = [
    'api' => [
        \Fruitcake\Cors\HandleCors::class,
        // ...
    ],
];
```

3. Publicar config:
```bash
php artisan vendor:publish --provider="Fruitcake\Cors\CorsServiceProvider"
```

4. Editar `config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000', 'http://localhost:8000'],
'supports_credentials' => true,  // ← Importante para cookies
```

---

# RESUMO: COMO APLICAR TUDO RÁPIDO

```bash
# 1. Criar arquivo JS global
cat > public/js/global-ajax.js << 'EOF'
# (Conteúdo do item 2 acima)
EOF

# 2. Atualizar Controllers
# (Copiar código dos items 3-4)

# 3. Atualizar Handler
# (Copiar código do item 5)

# 4. Atualizar Blade
# (Adicionar meta csrf-token e import global-ajax.js)

# 5. Atualizar .env
# (SESSION_DOMAIN, SANCTUM_STATEFUL_DOMAINS)

# 6. Limpar caches
php artisan optimize:clear

# 7. Testar
curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $(curl -s http://localhost:8000 | grep -o 'csrf-token" content="[^"]*' | cut -d'"' -f4)" \
  -d '{"name":"Test"}'
```

---

**Status:** ✅ Pronto para Deploy
**Tempo de Aplicação:** 15-20 minutos
**Risco:** Baixo (mudanças isoladas)
**Próximo Passo:** Seguir checklist de deploy local e testar com curl
