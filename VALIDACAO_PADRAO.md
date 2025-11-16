# Padrão de Validação e Tratamento de Erros - Portal Personalitec

## 📋 Visão Geral

Este documento descreve o padrão de validação e tratamento de erros implementado no Portal Personalitec para garantir consistência e robustez em toda a aplicação.

---

## 🔧 Componentes Implementados

### 1. **ExceptionHandler** (`app/Exceptions/Handler.php`)

Centraliza o tratamento de todas as exceções da aplicação.

**Benefícios:**
- Sem exposição de stack trace em produção
- Respostas JSON padronizadas para APIs
- Diferenciação de tipos de erro (validação, autenticação, autorização, etc)

**Erros Tratados:**
- `ValidationException` → 422 com lista de erros
- `AuthenticationException` → 401 não autenticado
- `AuthorizationException` → 403 não autorizado
- `NotFoundHttpException` → 404 não encontrado
- `MethodNotAllowedHttpException` → 405 método não permitido
- `ThrottleRequestsException` → 429 muitas requisições

---

### 2. **ApiResponse Trait** (`app/Traits/ApiResponse.php`)

Padroniza as respostas JSON dos controllers.

**Métodos Disponíveis:**

```php
// Sucesso com dados
$this->respondSuccess($data, 'Mensagem', 200);

// Sucesso com paginação
$this->respondSuccessPaginated($paginator, 'Mensagem', 200);

// Criado
$this->respondCreated($data, 'Recurso criado');

// Erro genérico
$this->respondError('Mensagem de erro', ['campo' => 'erro'], 400);

// Erro de validação
$this->respondValidationError(['campo' => 'erro']);

// Não encontrado
$this->respondNotFound('Recurso não encontrado');

// Não autorizado
$this->respondForbidden('Sem permissão');

// Não autenticado
$this->respondUnauthorized('Não autenticado');

// Sem conteúdo
$this->respondNoContent();
```

**Exemplo de Resposta:**
```json
{
  "success": true,
  "message": "Operação realizada com sucesso",
  "data": {
    "id": 1,
    "nome": "João Silva"
  }
}
```

---

### 3. **FormRequest Classes** (Validação Centralizada)

As FormRequest classes centralizam as regras de validação e mensagens personalizadas.

**Classes Criadas:**
- `StoreClienteRequest` - Validação para criar/editar cliente
- `StoreProdutoRequest` - Validação para criar/editar produto
- `StoreTabelaPrecoRequest` - Validação para criar/editar tabela de preço

**Exemplo de Uso:**

```php
// Antes (sem validação centralizada)
public function store(Request $request)
{
    $validated = $request->validate([
        'txtClienteCodigo' => 'required|string|max:255',
        'txtClienteNome' => 'required|string|max:255',
    ]);
    // ... lógica
}

// Depois (com FormRequest)
public function store(StoreClienteRequest $request)
{
    $validated = $request->validated();
    // Já validado e mapeado!
    // ... lógica
}
```

**Benefícios:**
- Validação centralizada e reutilizável
- Mensagens personalizadas em português
- Mapeamento automático de campos (txtClienteCodigo → codigo)
- Autorização integrada (método `authorize()`)

---

## 🚀 Como Usar

### Passo 1: Usar FormRequest nos Controllers

```php
namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Traits\ApiResponse;

class ClienteController extends Controller
{
    use ApiResponse;

    public function store(StoreClienteRequest $request)
    {
        try {
            $validated = $request->validated(); // Já validado e mapeado

            $cliente = Cliente::create($validated);

            return $this->respondCreated($cliente, 'Cliente criado com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao criar cliente', [], 500);
        }
    }
}
```

### Passo 2: Usar ApiResponse Trait

```php
class ProdutoController extends Controller
{
    use ApiResponse;

    public function list()
    {
        $produtos = Produto::all();
        return $this->respondSuccess($produtos, 'Produtos listados com sucesso');
    }

    public function show($id)
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return $this->respondNotFound('Produto não encontrado');
        }

        return $this->respondSuccess($produto);
    }

    public function destroy($id)
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return $this->respondNotFound();
        }

        $produto->delete();
        return $this->respondNoContent();
    }
}
```

### Passo 3: Melhorar Views com @error e old()

```blade
<form method="POST" action="{{ route('cliente.store') }}">
    @csrf

    <!-- Código do Cliente -->
    <div class="mb-3">
        <label for="codigo" class="form-label">Código *</label>
        <input
            type="text"
            class="form-control @error('txtClienteCodigo') is-invalid @enderror"
            id="codigo"
            name="txtClienteCodigo"
            value="{{ old('txtClienteCodigo') }}"
            required
        >
        @error('txtClienteCodigo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Nome do Cliente -->
    <div class="mb-3">
        <label for="nome" class="form-label">Nome *</label>
        <input
            type="text"
            class="form-control @error('txtClienteNome') is-invalid @enderror"
            id="nome"
            name="txtClienteNome"
            value="{{ old('txtClienteNome') }}"
            required
        >
        @error('txtClienteNome')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
</form>
```

---

## 📊 Resposta Padrão

### Sucesso (200)
```json
{
  "success": true,
  "message": "Operação realizada com sucesso",
  "data": { ... }
}
```

### Erro de Validação (422)
```json
{
  "success": false,
  "message": "Erro na validação dos dados",
  "errors": {
    "txtClienteCodigo": ["Código do cliente é obrigatório"],
    "txtClienteNome": ["Nome do cliente já existe"]
  }
}
```

### Não Encontrado (404)
```json
{
  "success": false,
  "message": "Recurso não encontrado"
}
```

### Erro de Servidor (500)
```json
{
  "success": false,
  "message": "Ocorreu um erro ao processar a requisição"
}
```

---

## 🔒 Segurança

### ExceptionHandler
- ✅ Sem exposição de stack trace em produção
- ✅ Erro genérico para usuários finais
- ✅ Log detalhado no servidor (em desenvolvimento mostra debug)

### FormRequest
- ✅ Validação no servidor (nunca confiar no cliente)
- ✅ Autorização integrada
- ✅ Proteção contra CSRF

### ApiResponse
- ✅ Respostas padronizadas
- ✅ Status HTTP corretos
- ✅ Sem exposição de informações sensíveis

---

## 📝 Criar Nova FormRequest

### Template
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->papel === 'admin';
    }

    public function rules(): array
    {
        return [
            'campo' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'campo.required' => 'Campo é obrigatório',
        ];
    }

    public function attributes(): array
    {
        return [
            'campo' => 'Nome do Campo',
        ];
    }
}
```

---

## 🎯 Próximas Etapas

1. **Audit Logging** - Registrar todas as operações
2. **Rate Limiting** - Limitar requisições por IP
3. **Testes Unitários** - Testar validações
4. **Documentação de API** - Swagger/OpenAPI
5. **Monitoramento** - Sentry ou similar

---

## 📞 Suporte

Para dúvidas sobre validação ou tratamento de erros, consulte:
- Laravel Validation: https://laravel.com/docs/validation
- FormRequests: https://laravel.com/docs/requests#form-request-validation
- Exception Handling: https://laravel.com/docs/error-handling
