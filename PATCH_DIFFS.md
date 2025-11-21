# PATCH: Diffs Completos - CSRF + JSON Response

Arquivo de referência com diffs exatos para cada arquivo modificado.

---

## FILE 1: resources/views/layouts/app.blade.php

```diff
--- a/resources/views/layouts/app.blade.php
+++ b/resources/views/layouts/app.blade.php
@@ -5,6 +5,9 @@
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>@yield('title')</title>
+
+    <!-- CSRF Token para AJAX -->
+    <meta name="csrf-token" content="{{ csrf_token() }}">

     <!-- Bootstrap CSS -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@@ -35,6 +38,9 @@
         @endauth
     </div>

+    <!-- Global AJAX Setup (DEVE SER PRIMEIRO) -->
+    <script src="{{ asset('js/global-ajax.js') }}"></script>
+
     <!-- jQuery -->
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@@ -42,10 +48,12 @@
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.org/2.0.0/datatables.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

     <!-- Scripts da aplicação (DEPOIS de global-ajax.js) -->
     <script src="{{ asset('js/app.js') }}"></script>

     @yield('scripts')
</body>
</html>
```

**Mudanças:**
- ✅ Adicionado `<meta name="csrf-token">`
- ✅ Script `global-ajax.js` carregado PRIMEIRO (antes de outros scripts)

---

## FILE 2: app/Http/Controllers/UserController.php

```diff
--- a/app/Http/Controllers/UserController.php
+++ b/app/Http/Controllers/UserController.php
@@ -1,13 +1,15 @@
 <?php

 namespace App\Http\Controllers;

 use App\Models\User;
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Validator;
+use Illuminate\Support\Facades\DB;
+use Illuminate\Support\Facades\Log;

 class UserController extends Controller
 {
     /**
-     * Salvar novo usuário
+     * Salvar novo usuário ou atualizar existente (AJAX endpoint)
      */
     public function store(Request $request)
     {
@@ -15,31 +17,76 @@
         $validator = Validator::make($request->all(), [
             'id' => 'nullable|exists:users,id',
             'name' => 'required|string|max:255',
-            'email' => 'required|email|unique:users,email',
+            'email' => 'required|email|unique:users,email,' . ($request->input('id') ?? 'NULL'),
             'celular' => 'nullable|string|max:20',
+            'cpf' => 'nullable|string|unique:users,cpf,' . ($request->input('id') ?? 'NULL'),
         ]);

         if ($validator->fails()) {
+            // ========== VALIDAÇÃO FALHOU: Retornar 422 com JSON ==========
             return response()->json([
-                'message' => 'Validação falhou',
-                'errors' => $validator->errors()
-            ], 422);
+                'success' => false,
+                'message' => 'Validação falhou',
+                'errors' => $validator->errors()
+            ], 422);  // 422 Unprocessable Entity
         }

-        try {
-            $user = User::create($request->only(['name', 'email', 'celular']));
-            return response()->json([
-                'message' => 'Usuário salvo com sucesso',
-                'data' => $user
-            ], 200);
+        try {
+            $id = $request->input('id');
+            $isUpdate = !empty($id);
+
+            DB::beginTransaction();
+
+            if ($isUpdate) {
+                // ========== UPDATE ==========
+                $user = User::findOrFail($id);
+                $user->update($request->only(['name', 'email', 'celular', 'cpf']));
+
+                Log::info("Usuário atualizado: #{$user->id}");
+                DB::commit();
+
+                return response()->json([
+                    'success' => true,
+                    'message' => 'Usuário atualizado com sucesso',
+                    'data' => $user
+                ], 200);  // 200 OK
+
+            } else {
+                // ========== CREATE ==========
+                $user = User::create($request->only(['name', 'email', 'celular', 'cpf']));
+
+                Log::info("Novo usuário criado: #{$user->id}");
+                DB::commit();
+
+                return response()->json([
+                    'success' => true,
+                    'message' => 'Usuário criado com sucesso',
+                    'data' => $user
+                ], 201);  // 201 Created
+            }

         } catch (\Illuminate\Database\QueryException $e) {
+            DB::rollBack();
             Log::error("Erro ao salvar usuário: " . $e->getMessage());
+
             return response()->json([
+                'success' => false,
                 'message' => 'Erro ao salvar usuário (dados duplicados?)',
                 'error' => $e->getMessage()
             ], 500);

         } catch (\Exception $e) {
+            DB::rollBack();
             Log::error("Erro inesperado: " . $e->getMessage());
+
             return response()->json([
+                'success' => false,
                 'message' => 'Erro ao salvar usuário',
                 'error' => $e->getMessage()
             ], 500);
@@ -48,18 +95,26 @@
     }

     /**
-     * Listar usuários (para DataTables)
+     * Listar usuários (AJAX endpoint para DataTables)
+     * GET /listar-usuarios → JSON
      */
-    public function list()
+    public function list(Request $request)
     {
         try {
-            $usuarios = User::select('id', 'name', 'email', 'celular', 'created_at')
+            $users = User::select('id', 'name', 'email', 'celular', 'cpf', 'created_at')
                 ->orderByDesc('created_at')
                 ->get();

             return response()->json([
-                'data' => $usuarios
+                'data' => $users
             ], 200, [
                 'Content-Type' => 'application/json; charset=utf-8'
             ]);

         } catch (\Exception $e) {
             Log::error("Erro ao listar usuários: " . $e->getMessage());
+
             return response()->json([
+                'success' => false,
                 'message' => 'Erro ao listar usuários',
                 'data' => []
             ], 500);
```

**Mudanças:**
- ✅ Validação retorna 422 com JSON
- ✅ Distingue entre CREATE (201) e UPDATE (200)
- ✅ Usa DB::transaction com rollBack
- ✅ Todas as exceções retornam JSON
- ✅ Log em cada operação
- ✅ Response estruturada com `success` flag

---

## FILE 3: app/Exceptions/Handler.php

```diff
--- a/app/Exceptions/Handler.php
+++ b/app/Exceptions/Handler.php
@@ -1,6 +1,10 @@
 <?php

 namespace App\Exceptions;

 use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
+use Illuminate\Session\TokenMismatchException;
+use Illuminate\Auth\AuthenticationException;
+use Illuminate\Database\Eloquent\ModelNotFoundException;
+use Illuminate\Validation\ValidationException;
 use Throwable;

 class Handler extends ExceptionHandler
@@ -17,15 +21,77 @@
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
+
+    /**
+     * Renderizar exceção como resposta
+     * CRUCIAL: Retornar JSON para AJAX/API, HTML para browser normal
+     */
+    public function render($request, Throwable $exception)
+    {
+        // ========== REQUISIÇÕES QUE ESPERAM JSON ==========
+        if ($request->expectsJson()) {
+            return $this->renderJsonResponse($request, $exception);
+        }
+
+        // ========== REQUISIÇÕES NORMAIS (BROWSER) ==========
+        return parent::render($request, $exception);
+    }
+
+    /**
+     * Renderizar resposta JSON para exceções
+     */
+    protected function renderJsonResponse($request, Throwable $exception)
+    {
+        // ========== TOKEN MISMATCH / SESSÃO EXPIRADA ==========
+        if ($exception instanceof TokenMismatchException) {
+            return response()->json([
+                'success' => false,
+                'message' => 'Sessão expirada ou CSRF token inválido',
+                'code' => 419
+            ], 419);
+        }
+
+        // ========== NÃO AUTENTICADO ==========
+        if ($exception instanceof AuthenticationException) {
+            return response()->json([
+                'success' => false,
+                'message' => 'Não autenticado - faça login',
+                'code' => 401
+            ], 401);
+        }
+
+        // ========== VALIDAÇÃO FALHOU ==========
+        if ($exception instanceof ValidationException) {
+            return response()->json([
+                'success' => false,
+                'message' => 'Validação falhou',
+                'errors' => $exception->errors(),
+                'code' => 422
+            ], 422);
+        }
+
+        // ========== MODELO NÃO ENCONTRADO ==========
+        if ($exception instanceof ModelNotFoundException) {
+            return response()->json([
+                'success' => false,
+                'message' => 'Recurso não encontrado',
+                'code' => 404
+            ], 404);
+        }
+
+        // ========== OUTROS ERROS ==========
+        $statusCode = $exception->getCode() ?: 500;
+        if ($statusCode < 100 || $statusCode > 599) {
+            $statusCode = 500;
+        }
+
+        return response()->json([
+            'success' => false,
+            'message' => $exception->getMessage() ?: 'Erro no servidor',
+            'code' => $statusCode
+        ], $statusCode);
+    }
 }
```

**Mudanças:**
- ✅ Adicionado `render()` que verifica `expectsJson()`
- ✅ `TokenMismatchException` → 419 JSON
- ✅ `AuthenticationException` → 401 JSON
- ✅ `ValidationException` → 422 JSON
- ✅ `ModelNotFoundException` → 404 JSON
- ✅ Erros genéricos → 500 JSON

---

## FILE 4: public/js/cadastros/usuarios.js

```diff
--- a/public/js/cadastros/usuarios.js
+++ b/public/js/cadastros/usuarios.js
@@ -20,21 +20,44 @@
     const tblUsuarios = $tbl.DataTable({
         ajax: {
             url: '/listar-usuarios',
+            type: 'GET',
             dataSrc: 'data',
-             dataType: 'json'
+
+            // ========== HEADERS EXPLÍCITOS ==========
+            headers: {
+                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
+                'Accept': 'application/json',
+                'X-Requested-With': 'XMLHttpRequest'
+            },
+
+            // ========== ERROR HANDLER ==========
+            error: function(xhr, status, error) {
+                console.error('DataTables AJAX Error:', {
+                    status: xhr.status,
+                    statusText: xhr.statusText,
+                    error: error,
+                    response: xhr.responseText.substring(0, 200)
+                });
+
+                let errorMsg = 'Erro ao carregar dados';
+                if (xhr.status === 401) {
+                    errorMsg = 'Sessão expirada - faça login novamente';
+                } else if (xhr.status === 403) {
+                    errorMsg = 'Acesso negado';
+                } else if (xhr.status === 404) {
+                    errorMsg = 'Rota não encontrada';
+                } else if (xhr.status === 500) {
+                    errorMsg = 'Erro no servidor';
+                }
+
+                Toast.fire({
+                    icon: 'error',
+                    title: errorMsg
+                });
+            }
         },
         columns: [
             { title: 'Nome', data: 'name', defaultContent: '' },
             { title: 'Email', data: 'email', defaultContent: '' },
             { title: 'Celular', data: 'celular', defaultContent: '' },
         ],
         order: [[0, 'asc']],
         pageLength: 10
     });

-    // Botão Salvar
+    // ========== SALVAR USUÁRIO VIA AJAX ==========
     $('#btn-salvar-usuario').on('click', function() {
+        const jsonData = {
+            id: $('#usuario-id').val() || null,
+            name: $('#usuario-name').val(),
+            email: $('#usuario-email').val(),
+            celular: $('#usuario-celular').val(),
+        };

-        var formData = $('#form-usuario').serialize();
+        ajaxJson({
+            url: '/salvar-usuario',
+            type: 'POST',
+            data: jsonData,
+            success: function(response) {
+                Toast.fire({
+                    icon: 'success',
+                    title: response.message
+                });
+                tblUsuarios.ajax.reload();
+                $('#modal-usuario').modal('hide');
+            },
+            error: function(xhr) {
+                const response = safeParseJson(xhr.responseText);

-        $.ajax({
-            url: '/salvar-usuario',
-            type: 'POST',
-            data: formData,
-            success: function(data) {
-                Toast.fire({icon: 'success', title: data.message});
-                tblUsuarios.ajax.reload();
-            },
-            error: function(jqXHR, textStatus, errorThrown) {
-                Toast.fire({
+                if (xhr.status === 422) {
+                    // Validação falhou - mostrar erros
+                    let errorMsg = '';
+                    $.each(response.errors, function(field, messages) {
+                        errorMsg += messages.join(', ') + '\n';
+                    });
+                    Toast.fire({
+                        icon: 'error',
+                        title: 'Validação falhou',
+                        text: errorMsg
+                    });
+                } else if (xhr.status === 419) {
+                    Toast.fire({
+                        icon: 'error',
+                        title: 'Sessão expirada'
+                    });
+                } else {
+                    Toast.fire({
+                        icon: 'error',
+                        title: response.message || 'Erro ao salvar'
+                    });
+                }
+            }
-                    icon: 'error',
-                    title: errorThrown
-                });
             }
         });
     });
 });
```

**Mudanças:**
- ✅ Headers explícitos adicionados ao DataTables
- ✅ Error handler específico para 401, 403, 404, 500
- ✅ Mudado de `form.serialize()` para `JSON.stringify()`
- ✅ Usa `ajaxJson()` helper
- ✅ Tratamento de validação (422) com display de erros

---

## FILE 5: .env

```diff
--- a/.env
+++ b/.env
@@ -1,6 +1,9 @@
 APP_NAME="Portal"
 APP_ENV=local
 APP_KEY=base64:...
 APP_DEBUG=true
 APP_URL=http://localhost:8000
+
+SESSION_DOMAIN=localhost
+SANCTUM_STATEFUL_DOMAINS=localhost:8000,localhost:3000

 DB_CONNECTION=pgsql
```

**Mudanças:**
- ✅ `SESSION_DOMAIN=localhost`: Garante cookie em localhost
- ✅ `SANCTUM_STATEFUL_DOMAINS`: Permite Sanctum com cookies

---

## FILE 6: Novo: public/js/global-ajax.js

Arquivo completamente novo (veja arquivo criado separadamente).

---

# Resumo dos Diffs

| Arquivo | Mudanças |
|---------|----------|
| **layouts/app.blade.php** | +3 linhas: meta CSRF + import global-ajax.js |
| **UserController.php** | +56 linhas: try-catch, transaction, 422/201/200, JSON |
| **Handler.php** | +76 linhas: render(), expectsJson(), exception handlers |
| **usuarios.js** | +40 linhas: DataTables headers, error handler, ajaxJson |
| **.env** | +2 linhas: SESSION_DOMAIN, SANCTUM_STATEFUL_DOMAINS |
| **global-ajax.js** | +160 linhas: setup global, helpers, utilitários |

**Total:** ~337 linhas adicionadas

---

# Ordem de Aplicação

1. ✅ Criar `public/js/global-ajax.js` (novo)
2. ✅ Atualizar `resources/views/layouts/app.blade.php`
3. ✅ Atualizar `app/Http/Controllers/UserController.php`
4. ✅ Atualizar `app/Exceptions/Handler.php`
5. ✅ Atualizar `public/js/cadastros/usuarios.js`
6. ✅ Atualizar `.env`
7. ✅ Rodar `php artisan optimize:clear`

---

# Comandos para Aplicar Tudo

```bash
# Se quiser verificar antes de aplicar
git diff --stat

# Aplicar tudo
php artisan optimize:clear

# Iniciar servidor
php artisan serve --host=localhost --port=8000

# Em outro terminal, testar
curl -i -X POST http://localhost:8000/salvar-usuario \
  -H "Accept: application/json" \
  -d '{}'
```

---

**Status:** ✅ Diffs Completos
**Data:** 20 de Novembro de 2025
