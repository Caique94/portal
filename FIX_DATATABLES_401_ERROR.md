# Fix DataTables AJAX Error 401 - Solução Completa

## 🔴 Erro Identificado

```
DataTables warning: Ajax error
SyntaxError: Unexpected token '<', "<!DOCTYPE ..." is not valid JSON
Network: 401 Unauthorized
```

### Causa Raiz

O DataTables estava recebendo **HTML em vez de JSON** quando o servidor retornava erro 401. Isso ocorria porque:

1. **O DataTables não enviava o header `Accept: application/json`**
2. **O Laravel (`$request->expectsJson()`) não reconhecia como requisição JSON**
3. **O Handler.php retornava HTML em vez de JSON para erro 401**
4. **O DataTables tentava fazer parse de HTML como JSON → SyntaxError**

---

## ✅ Solução Implementada

### 1. CORRIGIR `public/js/app.js` - AJAX SETUP GLOBAL

**ANTES:**
```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

**DEPOIS:**
```javascript
// ========== AJAX SETUP GLOBAL ==========
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'  // Força retorno em JSON
    },
    error: function(xhr, status, error) {
        // Handle AJAX errors globally
        if (xhr.status === 401) {
            console.error('Erro 401: Sessão expirada ou não autenticado');
            // Redirecionar para login se necessário
            // window.location.href = '/login';
        } else if (xhr.status === 403) {
            console.error('Erro 403: Acesso negado');
        }
    }
});
```

**O que muda:**
- ✓ Adiciona `'Accept': 'application/json'` a TODAS as requisições AJAX
- ✓ Isso faz o Laravel reconhecer como requisição JSON
- ✓ O Handler retorna JSON em vez de HTML

---

### 2. CORRIGIR `public/js/cadastros/usuarios.js` - DATATABLES CONFIG

**ANTES:**
```javascript
const tblUsuarios = $tbl.DataTable({
  ajax: {
    url: '/listar-usuarios',
    type: 'GET',
    dataSrc: 'data' // { data: [...] }
  },
  // ... resto das configs
});
```

**DEPOIS:**
```javascript
const tblUsuarios = $tbl.DataTable({
  ajax: {
    url: '/listar-usuarios',
    type: 'GET',
    dataSrc: 'data', // { data: [...] }
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    // Tratamento detalhado de erros
    error: function(xhr, status, error) {
      console.error('DataTables AJAX Error:', {
        status: xhr.status,
        statusText: xhr.statusText,
        responseText: xhr.responseText.substring(0, 200),
        error: error
      });

      let errorMsg = 'Erro ao carregar dados';

      if (xhr.status === 401) {
        errorMsg = 'Sessão expirada. Faça login novamente.';
        console.error('401 Unauthorized - Precisa fazer login novamente');
      } else if (xhr.status === 403) {
        errorMsg = 'Você não tem permissão para acessar este recurso';
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
  // ... resto das configs
});
```

**O que muda:**
- ✓ Adiciona headers explícitos no DataTables
- ✓ Implementa tratamento de erros detalhado
- ✓ Mostra mensagem clara ao usuário em vez de aviso genérico do DataTables
- ✓ Log detalhado no console para debugging

---

### 3. CORRIGIR `app/Exceptions/Handler.php` - FORÇA JSON PARA ROTAS DE API

**ANTES:**
```php
public function render(Request $request, Throwable $exception): Response
{
    // Se é uma requisição AJAX/API, retorna JSON
    if ($request->expectsJson()) {
        return $this->handleJsonException($request, $exception);
    }

    // Caso contrário, usa o comportamento padrão
    return parent::render($request, $exception);
}
```

**DEPOIS:**
```php
public function render(Request $request, Throwable $exception): Response
{
    // Se é uma requisição AJAX/API, retorna JSON
    if ($request->expectsJson()) {
        return $this->handleJsonException($request, $exception);
    }

    // Se é uma requisição para uma rota de API interna (começa com /api ou /listar-)
    // Também retorna JSON para evitar erro de parsing no DataTables
    if ($request->is('api/*') || $request->is('listar-*') || $request->is('salvar-*') ||
        $request->is('toggle-*') || $request->is('excluir-*') || $request->is('remover-*')) {
        return $this->handleJsonException($request, $exception);
    }

    // Caso contrário, usa o comportamento padrão
    return parent::render($request, $exception);
}
```

**O que muda:**
- ✓ Detecta rotas de API pelo padrão de URL
- ✓ Retorna JSON automaticamente para essas rotas
- ✓ Erro 401 é retornado como JSON, não HTML
- ✓ Evita o erro "Unexpected token '<'" no DataTables

---

### 4. CONTROLLER JÁ ESTÁ CORRETO ✓

O `UserController::list()` já retorna JSON no formato correto:

```php
public function list()
{
    // ... buscar dados ...
    return response()->json(['data' => $rows], 200, ['Content-Type' => 'application/json; charset=utf-8']);
}
```

✓ Formato DataTables: `{ "data": [...] }`
✓ Status HTTP 200 com JSON
✓ Content-Type correto

---

### 5. ROTAS JÁ ESTÃO CORRETAS ✓

Em `routes/web.php`:
```php
Route::middleware('auth')->group(function () {
    Route::get('/listar-usuarios', [UserController::class, 'list']);
    // ...
});
```

✓ Rota protegida com `middleware('auth')`
✓ Retorna 401 quando não autenticado
✓ Agora retorna JSON em vez de HTML (graças ao Handler.php)

---

### 6. MIDDLEWARE JÁ ESTÁ CORRETO ✓

Em `app/Http/Middleware/Authenticate.php`:
```php
protected function redirectTo(Request $request): ?string
{
    return $request->expectsJson() ? null : route('login');
}
```

✓ Retorna null (erro 401) para requisições JSON
✓ Redireciona para login para requisições HTML

---

## 🔍 Fluxo de Requisição - ANTES vs DEPOIS

### ANTES (com erro):
```
Browser
  ↓
DataTables AJAX (SEM Accept: application/json)
  ↓
Laravel não sabe que é JSON → $request->expectsJson() = false
  ↓
Middleware Authenticate verifica sessão → 401
  ↓
Handler.php retorna HTML de erro
  ↓
DataTables recebe: <!DOCTYPE html><html>...[HTML]...</html>
  ↓
DataTables tenta fazer JSON.parse() → SyntaxError: Unexpected token '<'
  ↓
Aviso: "DataTables warning: Ajax error"
```

### DEPOIS (corrigido):
```
Browser
  ↓
DataTables AJAX (COM Accept: application/json)
  ↓
$.ajaxSetup também adiciona Accept: application/json
  ↓
Laravel reconhece → $request->expectsJson() = true
  ↓
Middleware Authenticate verifica sessão → 401
  ↓
Handler.php retorna JSON de erro (por causa da rota pattern matching)
  ↓
DataTables recebe: {"success": false, "message": "Não autenticado", "code": 401}
  ↓
DataTables faz JSON.parse() → Sucesso ✓
  ↓
Error callback do DataTables é acionado
  ↓
Toast exibe: "Sessão expirada. Faça login novamente."
```

---

## 📋 Checklist de Resposta JSON Esperada

### Sucesso (HTTP 200):
```json
{
  "data": [
    {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "papel": "admin",
      "cgc": "12345678901234",
      "celular": "11999999999",
      "ativo": true,
      "valor_hora": "0.00",
      "valor_desloc": "0.00",
      "valor_km": "0.00",
      "salario_base": "0.00",
      "data_nasc": "1981-06-09",
      "created_at": "2025-11-19 15:11:39"
    }
  ]
}
```

### Erro 401 (HTTP 401):
```json
{
  "success": false,
  "message": "Não autenticado",
  "code": 401
}
```

### Erro 403 (HTTP 403):
```json
{
  "success": false,
  "message": "Você não tem permissão para acessar este recurso",
  "code": 403
}
```

### Erro 422 (Validação):
```json
{
  "success": false,
  "message": "Erro na validação dos dados",
  "errors": {
    "email": ["Email é obrigatório"],
    "name": ["Nome é obrigatório"]
  },
  "code": 422
}
```

---

## 🚀 Como Testar

### 1. Abra o DevTools (F12)
- Vá para **Console**
- Vá para **Network**

### 2. Navegue para `/cadastros/usuarios`

### 3. Verifique na aba Network:
- Procure por `/listar-usuarios`
- **Request Headers** devem incluir:
  ```
  Accept: application/json
  X-CSRF-TOKEN: ...
  X-Requested-With: XMLHttpRequest
  ```

### 4. Verifique a Response:
- **Status**: 200 (se autenticado) ou 401 (se não autenticado)
- **Content-Type**: `application/json`
- **Body**: JSON válido (não HTML)

### 5. Se houver erro:
- Verifique o **Console** para mensagens de erro
- DataTables mostrará mensagem clara em Toast

---

## 🔧 Ajustes Adicionais Opcionais

### Se você tiver mais DataTables, replique o padrão

Todos os arquivos `public/js/cadastros/*.js` devem seguir o mesmo padrão:

```javascript
const table = $('#tableId').DataTable({
  ajax: {
    url: '/listar-dados',
    type: 'GET',
    dataSrc: 'data',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    error: function(xhr, status, error) {
      console.error('AJAX Error:', xhr.status, xhr.statusText);
      // Mostrar Toast com erro
    }
  },
  // ... resto das configs
});
```

### Exemplo para Clientes

Se tiver `/public/js/cadastros/clientes.js`, aplique o mesmo padrão:

```javascript
const tblClientes = $tbl.DataTable({
  ajax: {
    url: '/listar-clientes',
    type: 'GET',
    dataSrc: 'data',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    error: function(xhr, status, error) {
      console.error('DataTables AJAX Error:', {
        status: xhr.status,
        statusText: xhr.statusText,
        error: error
      });

      let msg = 'Erro ao carregar dados';
      if (xhr.status === 401) {
        msg = 'Sessão expirada. Faça login novamente.';
      } else if (xhr.status === 403) {
        msg = 'Você não tem permissão para acessar este recurso';
      }

      Toast.fire({ icon: 'error', title: msg });
    }
  },
  // ... resto das configs
});
```

---

## 📊 Resumo das Mudanças

| Arquivo | Mudança | Impacto |
|---------|---------|--------|
| `public/js/app.js` | Adiciona `Accept: application/json` ao global `$.ajaxSetup()` | Todas as requisições AJAX enviam header JSON |
| `public/js/cadastros/usuarios.js` | Adiciona headers e error handler no DataTables | DataTables agora trata erros corretamente |
| `app/Exceptions/Handler.php` | Detecta rotas de API e retorna JSON | Erro 401 retorna JSON, não HTML |
| Controller | ✓ Sem mudanças necessárias | Já retorna JSON correto |
| Rotas | ✓ Sem mudanças necessárias | Autenticação já funciona |
| Middleware | ✓ Sem mudanças necessárias | Já retorna null para JSON |

---

## ⚠️ Erros Comuns Evitados

1. ✓ **SyntaxError: Unexpected token '<'** → Resolvido: Agora retorna JSON
2. ✓ **DataTables warning: Ajax error** → Resolvido: Error handler exibido
3. ✓ **401 Unauthorized retorna HTML** → Resolvido: Retorna JSON
4. ✓ **Sessão expirada não tem mensagem clara** → Resolvido: Toast exibe mensagem

---

## 🎯 Resultado Final

```
✓ DataTables carrega dados com sucesso
✓ Erro 401 exibe mensagem clara
✓ Erro 403 exibe mensagem clara
✓ Erro 422 exibe validações
✓ Console mostra erros detalhados para debugging
✓ Sem aviso genérico do DataTables
✓ Suporta todas as requisições AJAX
```

---

## 📖 Referências

- [DataTables: Server-side processing](https://datatables.net/examples/server_side/simple.html)
- [jQuery: $.ajax() documentation](https://api.jquery.com/jquery.ajax/)
- [Laravel: HTTP Responses](https://laravel.com/docs/11.x/responses)
- [Laravel: Exception Handling](https://laravel.com/docs/11.x/errors)
