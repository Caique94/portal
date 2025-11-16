# Exemplo de Refactoring - ClienteController

## 🔄 Antes e Depois

### ANTES (Código Atual)
```php
<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function list(Request $request)
    {
        $data = Cliente::join('tabela_preco', ...)
            ->select('cliente.*', ...)
            ->orderBy('cliente.nome', 'asc')
            ->get();

        return response()->json($data);  // ❌ Sem padronização
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([...]);

        $mappedData = [...];

        if ($request->filled('id')) {
            $cli = Cliente::find($request->input('id'));
            if (!$cli) {
                return response()->json(['ok'=>false, 'msg'=>'...'], 404);  // ❌ Inconsistente
            }
            $cli->update($mappedData);
            return response()->json(['ok'=>true, ...], 200);  // ❌ Formato diferente
        }

        $cliente = Cliente::create($mappedData);

        return response()->json(['ok'=>true, 'msg'=>'...', 'data'=>$cliente], 201);  // ❌ Diferente
    }

    public function delete($id)
    {
        $row = Cliente::find($id);
        if (!$row) {
            return response()->json(['ok'=>false, 'msg'=>'...'], 404);  // ❌ Sem padrão
        }
        $row->delete();
        return response()->json(['ok'=>true, 'msg'=>'...']);  // ❌ Sem status code
    }
}
```

**Problemas:**
- ❌ 4 formatos diferentes de resposta
- ❌ Sem tratamento de exceção centralizado
- ❌ Validação duplicada em vários controllers
- ❌ Mensagens em português espalhadas no código
- ❌ Status HTTP inconsistentes

---

### DEPOIS (Refatorado com Novo Padrão)

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Models\Cliente;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    use ApiResponse;  // ✅ Padrão de resposta centralizado

    /**
     * Listar todos os clientes
     */
    public function list()
    {
        $clientes = Cliente::with('tabelaPreco')
            ->orderBy('nome', 'asc')
            ->get();

        return $this->respondSuccess($clientes, 'Clientes listados com sucesso');
    }

    /**
     * Obter um cliente específico
     */
    public function show($id)
    {
        $cliente = Cliente::with('tabelaPreco')->find($id);

        if (!$cliente) {
            return $this->respondNotFound('Cliente não encontrado');
        }

        return $this->respondSuccess($cliente);
    }

    /**
     * Criar ou atualizar cliente
     * @param StoreClienteRequest $request  ✅ Validação centralizada
     */
    public function store(StoreClienteRequest $request)
    {
        try {
            $validated = $request->validated();  // ✅ Já mapeado!

            // Se vier ID na request, atualiza; senão, cria
            if ($request->has('id')) {
                $cliente = Cliente::find($request->input('id'));

                if (!$cliente) {
                    return $this->respondNotFound('Cliente não encontrado');
                }

                $cliente->update($validated);
                return $this->respondSuccess($cliente, 'Cliente atualizado com sucesso');
            }

            $cliente = Cliente::create($validated);

            return $this->respondCreated($cliente, 'Cliente criado com sucesso');

        } catch (\Illuminate\Database\QueryException $e) {
            // Erro de banco de dados
            return $this->respondError('Erro ao salvar cliente: ' . $e->getMessage(), [], 500);
        } catch (\Exception $e) {
            // Erro genérico
            return $this->respondError('Erro ao processar cliente', [], 500);
        }
    }

    /**
     * Deletar cliente
     */
    public function destroy($id)
    {
        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return $this->respondNotFound('Cliente não encontrado');
            }

            $cliente->delete();

            return $this->respondNoContent();

        } catch (\Exception $e) {
            return $this->respondError('Erro ao deletar cliente', [], 500);
        }
    }

    /**
     * Listar clientes com paginação
     */
    public function paginated(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $search = $request->query('search');

        $query = Cliente::with('tabelaPreco');

        if ($search) {
            $query->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
        }

        $clientes = $query->orderBy('nome', 'asc')->paginate($perPage);

        return $this->respondSuccessPaginated($clientes, 'Clientes listados com sucesso');
    }
}
```

---

## 📊 Comparação

| Aspecto | Antes | Depois |
|---------|-------|--------|
| **Formatos de Resposta** | 4 diferentes | 1 padrão |
| **Tratamento de Erro** | Manual em cada método | Centralizado no ExceptionHandler |
| **Validação** | Duplicada em cada controller | Centralizada em FormRequest |
| **Mensagens** | Espalhadas no código | Em um arquivo de tradução |
| **Status HTTP** | Inconsistente | Consistente |
| **Código Duplicado** | Alto | Baixo |
| **Manutenibilidade** | Difícil | Fácil |
| **Testabilidade** | Difícil | Fácil |

---

## 🎯 Benefícios da Refatoração

### 1. **Consistência**
```json
// Sempre o mesmo formato
{
  "success": true,
  "message": "...",
  "data": {...}
}
```

### 2. **Segurança**
- Stack trace nunca é exposto
- Validação no servidor
- Autorização integrada

### 3. **Manutenibilidade**
- Mudança de resposta → 1 arquivo (ApiResponse Trait)
- Mudança de validação → 1 arquivo (FormRequest)
- Novo controller → copia padrão

### 4. **Produtividade**
- Menos código boilerplate
- Menos bugs
- Mais foco na lógica de negócio

### 5. **Developer Experience**
- Documentação automática do formato de resposta
- IDE autocomplete dos métodos
- Erros mais claros

---

## 🚀 Como Aplicar em Outros Controllers

### Template Rápido
```php
use App\Traits\ApiResponse;
use App\Http\Requests\Store{Entidade}Request;

class {Entidade}Controller extends Controller
{
    use ApiResponse;

    public function store(Store{Entidade}Request $request)
    {
        $validated = $request->validated();
        $item = {Model}::create($validated);
        return $this->respondCreated($item);
    }

    public function destroy($id)
    {
        $item = {Model}::find($id);
        if (!$item) return $this->respondNotFound();
        $item->delete();
        return $this->respondNoContent();
    }
}
```

### Passos
1. Adicionar `use ApiResponse;` no controller
2. Criar `StoreXyzRequest` se necessário
3. Trocar `response()->json()` por `$this->respondXyz()`
4. Testar

---

## ✅ Checklist de Refactoring

- [ ] Importar `ApiResponse` Trait
- [ ] Criar FormRequest se necessário
- [ ] Substituir `response()->json()` por métodos do Trait
- [ ] Remover validações inline (usar FormRequest)
- [ ] Adicionar try/catch se necessário
- [ ] Testar com Postman/Insomnia
- [ ] Atualizar documentação da API
- [ ] Code review

---

## 📝 Próximas Entidades a Refatorar

1. ProdutoController
2. TabelaPrecoController
3. ContatoController
4. OrdemServicoController
5. FaturamentoController

---

## 📚 Referências

- [Laravel Form Requests](https://laravel.com/docs/requests#form-request-validation)
- [Laravel Traits](https://laravel.com/docs/8.x#traits)
- [REST API Best Practices](https://restfulapi.net/)
