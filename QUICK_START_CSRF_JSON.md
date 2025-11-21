# ⚡ Quick Start - CSRF + JSON Fix
## Checklist Pronto para Aplicar (5 Passos, 15 min)

---

## 🎯 O Problema

```
POST /salvar-usuario → 422 (HTML)
DataTables → "Ajax error"
Response → "SyntaxError: Unexpected token '<'"
```

**Causa:** Backend retorna HTML em vez de JSON

---

## ✅ A Solução em 5 Passos

### ✓ Passo 1: Criar arquivo global-ajax.js

**Local:** `public/js/global-ajax.js`

**Ação:** Copiar arquivo `public/js/global-ajax.js` que foi criado (já existe no projeto)

**Verificar:**
```bash
ls -la public/js/global-ajax.js
```

---

### ✓ Passo 2: Atualizar Blade Layout

**Arquivo:** `resources/views/layouts/app.blade.php`

**ANTES:**
```html
<head>
    <!-- ... -->
</head>
```

**DEPOIS:**
```html
<head>
    <!-- ... outros metas ... -->

    <!-- ADICIONAR ESTA LINHA -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    @yield('content')

    <!-- ADICIONAR ANTES de outros scripts -->
    <script src="{{ asset('js/global-ajax.js') }}"></script>

    <!-- Depois vêm os outros scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
```

---

### ✓ Passo 3: Atualizar UserController

**Arquivo:** `app/Http/Controllers/UserController.php`

**Mude o método `store()` para:**

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
    public function store(Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($request->input('id') ?? 'NULL'),
            'celular' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $validator->errors()
            ], 422);  // ← 422 é o status para validação
        }

        try {
            $id = $request->input('id');
            $isUpdate = !empty($id);

            DB::beginTransaction();

            if ($isUpdate) {
                // UPDATE
                $user = User::findOrFail($id);
                $user->update($request->only(['name', 'email', 'celular']));
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Usuário atualizado com sucesso',
                    'data' => $user
                ], 200);  // ← 200 para update
            } else {
                // CREATE
                $user = User::create($request->only(['name', 'email', 'celular']));
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Usuário criado com sucesso',
                    'data' => $user
                ], 201);  // ← 201 para create
            }

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error("Erro BD: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar (dados duplicados?)',
                'error' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function list()
    {
        try {
            $users = User::select('id', 'name', 'email', 'celular', 'created_at')
                ->orderByDesc('created_at')
                ->get();

            return response()->json(['data' => $users], 200);

        } catch (\Exception $e) {
            Log::error("Erro ao listar: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar usuários',
                'data' => []
            ], 500);
        }
    }
}
```

---

### ✓ Passo 4: Atualizar Exception Handler

**Arquivo:** `app/Exceptions/Handler.php`

**TODA a classe deve ser:**

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
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Renderizar: JSON para AJAX, HTML para browser
     */
    public function render($request, Throwable $exception)
    {
        // Se request espera JSON (AJAX/API), retornar JSON
        if ($request->expectsJson()) {
            return $this->renderJsonResponse($request, $exception);
        }

        // Senão, usar comportamento padrão (HTML)
        return parent::render($request, $exception);
    }

    /**
     * Retornar JSON para exceções
     */
    protected function renderJsonResponse($request, Throwable $exception)
    {
        // Token expirado
        if ($exception instanceof TokenMismatchException) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão expirada ou CSRF token inválido',
                'code' => 419
            ], 419);
        }

        // Não autenticado
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
                'code' => 401
            ], 401);
        }

        // Validação falhou (throw ValidationException em rules)
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $exception->errors(),
                'code' => 422
            ], 422);
        }

        // Recurso não encontrado
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso não encontrado',
                'code' => 404
            ], 404);
        }

        // Erro genérico
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

---

### ✓ Passo 5: Atualizar DataTables

**Arquivo:** `public/js/cadastros/usuarios.js`

**Método `store()` deve usar `ajaxJson()`:**

```javascript
$(function () {
    const $tbl = $('#tblUsuarios');

    const tblUsuarios = $tbl.DataTable({
        ajax: {
            url: '/listar-usuarios',
            type: 'GET',
            dataSrc: 'data',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error: function(xhr, status, error) {
                console.error('DataTables error:', xhr.status);
                let msg = 'Erro ao carregar dados';
                if (xhr.status === 401) msg = 'Sessão expirada';
                if (xhr.status === 500) msg = 'Erro no servidor';
                Toast.fire({ icon: 'error', title: msg });
            }
        },
        columns: [
            { title: 'Nome', data: 'name', defaultContent: '' },
            { title: 'Email', data: 'email', defaultContent: '' },
        ],
        order: [[0, 'asc']],
        pageLength: 10
    });

    // SALVAR
    $('#btn-salvar-usuario').on('click', function() {
        const dados = {
            id: $('#usuario-id').val() || null,
            name: $('#usuario-name').val(),
            email: $('#usuario-email').val(),
            celular: $('#usuario-celular').val(),
        };

        ajaxJson({
            url: '/salvar-usuario',
            type: 'POST',
            data: dados,
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
                    // Validação
                    let erros = '';
                    $.each(response.errors, function(field, msgs) {
                        erros += field + ': ' + msgs.join(', ') + '\n';
                    });
                    Toast.fire({
                        icon: 'error',
                        title: 'Validação Falhou',
                        text: erros
                    });
                } else if (xhr.status === 419) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Sessão expirada'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Erro'
                    });
                }
            }
        });
    });
});
```

---

### ✓ Passo 6 (Opcional): Atualizar .env

**Arquivo:** `.env`

```diff
+SESSION_DOMAIN=localhost
+SANCTUM_STATEFUL_DOMAINS=localhost:8000,localhost:3000
```

---

## 🚀 Limpar Cache e Testar

```bash
# Limpar tudo
php artisan optimize:clear

# Iniciar servidor
php artisan serve --host=localhost --port=8000
```

---

## ✔️ Teste Rápido (no browser)

1. **Abrir** http://localhost:8000/cadastros/usuarios
2. **F12** → Console
3. **Procurar erro** (não deve ter)
4. **Preencher formulário** e clicar Salvar
5. **F12 → Network → procurar POST /salvar-usuario**
6. **Verificar Response:**
   - ✅ Status: 201 ou 422
   - ✅ Content-Type: `application/json`
   - ✅ Body é JSON válido (não HTML)

---

## 📝 Checklist Final

- [ ] Arquivo `public/js/global-ajax.js` existe
- [ ] `layouts/app.blade.php` tem `<meta name="csrf-token">`
- [ ] `layouts/app.blade.php` carrega `global-ajax.js` PRIMEIRO
- [ ] `UserController.php` tem try-catch e retorna JSON em TUDO
- [ ] `Handler.php` tem `expectsJson()` e `renderJsonResponse()`
- [ ] `usuarios.js` usa `ajaxJson()` e tem error handler
- [ ] `.env` tem `SESSION_DOMAIN` (se não usa, comentar)
- [ ] Rodou `php artisan optimize:clear`
- [ ] Testou no browser (F12 → Network)
- [ ] POST retorna JSON (não HTML)
- [ ] DataTables carrega sem erro

---

## 🧪 5 Testes Curl Essenciais

### Teste 1: Sucesso (201)

```bash
TOKEN=$(curl -s http://localhost:8000 | grep -o 'csrf-token" content="[^"]*' | cut -d'"' -f4)

curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $TOKEN" \
  -d '{"name":"João","email":"joao@test.com"}'
```

**Esperado:** `201 Created` + JSON

---

### Teste 2: Validação (422)

```bash
curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $TOKEN" \
  -d '{"name":""}'
```

**Esperado:** `422 Unprocessable Entity` + JSON com `errors`

---

### Teste 3: Token Inválido (419)

```bash
curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: token-falso" \
  -d '{"name":"João"}'
```

**Esperado:** `419 Page Expired` + JSON

---

### Teste 4: DataTables GET (200)

```bash
curl -i http://localhost:8000/listar-usuarios \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $TOKEN"
```

**Esperado:** `200 OK` + JSON com `data: [...]`

---

### Teste 5: Accept Header Errado

```bash
curl -i http://localhost:8000/listar-usuarios \
  -H "Accept: text/html"
```

**ANTES (❌ bug):** HTML
**DEPOIS (✅ fix):** JSON (porque Handler verifica `expectsJson()` e usa Accept header)

---

## 🎯 Por Que Funciona

| Mudança | Resolve |
|---------|---------|
| `<meta csrf-token>` | JS consegue ler token |
| `X-CSRF-TOKEN` header | Backend valida CSRF |
| `Accept: application/json` | Laravel entende que é AJAX (`expectsJson()`) |
| `response()->json()` | Sempre retorna JSON (não HTML/redirect) |
| `Handler::expectsJson()` | Exceções viram JSON (não página inteira) |
| `DB::transaction()` | Rollback em erro (evita dados parciais) |

---

## 🆘 Rápido Troubleshooting

### Recebendo HTML em POST?
```bash
# Verificar se Handler foi salvo
grep -n "expectsJson" app/Exceptions/Handler.php
# Deve aparecer pelo menos uma vez

# Limpar cache
php artisan config:cache
php artisan cache:clear
```

### DataTables ainda mostra "Ajax error"?
```bash
# F12 → Network → clique em /listar-usuarios
# Verificar Response tab
# Se for HTML → Controller não retorna JSON
# Se for JSON → Console pode ter erro de parsing
```

### 419 mesmo com token?
```bash
# Verificar se token no HTML é válido
curl -s http://localhost:8000 | grep csrf-token | head -1

# Usar esse token no curl
curl -i -X POST ... -H "X-CSRF-TOKEN: $TOKEN" ...
```

---

## 📞 Próximo Passo

Assim que terminar os 6 passos:

1. Rodar `php artisan optimize:clear`
2. Testar com curl (todos 5 testes)
3. Testar no browser (F12 Network)
4. Se tiver erro, consultar Troubleshooting acima

**Tempo total:** 15-20 minutos

---

**Versão:** 1.0
**Status:** ✅ Pronto para Usar
**Data:** 20 de Novembro de 2025
