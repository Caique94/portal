# Quick Reference - Padrões de Validação

## 🚀 TL;DR (Too Long; Didn't Read)

### Usar em Controllers
```php
use App\Traits\ApiResponse;
use App\Http\Requests\StoreXyzRequest;

class XyzController extends Controller {
    use ApiResponse;

    public function store(StoreXyzRequest $request) {
        $data = $request->validated();
        $xyz = Xyz::create($data);
        return $this->respondCreated($xyz);
    }
}
```

### Criar FormRequest
```php
class StoreXyzRequest extends FormRequest {
    public function authorize(): bool {
        return auth()->user()?->papel === 'admin';
    }

    public function rules(): array {
        return [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:xyz,email',
        ];
    }

    public function messages(): array {
        return [
            'nome.required' => 'Nome é obrigatório',
            'email.unique' => 'Este email já existe',
        ];
    }
}
```

---

## 📋 ApiResponse Methods

| Método | HTTP | Uso |
|--------|------|-----|
| `respondSuccess($data)` | 200 | ✅ Sucesso com dados |
| `respondCreated($data)` | 201 | ✅ Recurso criado |
| `respondNoContent()` | 204 | ✅ Sem conteúdo |
| `respondError($msg)` | 400 | ❌ Erro genérico |
| `respondValidationError($errors)` | 422 | ❌ Validação falhou |
| `respondUnauthorized()` | 401 | ❌ Não autenticado |
| `respondForbidden()` | 403 | ❌ Sem permissão |
| `respondNotFound()` | 404 | ❌ Não encontrado |
| `respondSuccessPaginated($paginator)` | 200 | ✅ Com paginação |

---

## 🎯 Resposta Padrão

**Sucesso:**
```json
{
  "success": true,
  "message": "Operação realizada",
  "data": {...}
}
```

**Erro:**
```json
{
  "success": false,
  "message": "Erro",
  "errors": {"campo": ["erro"]}
}
```

---

## ✅ Validações Comuns

```php
'nome' => 'required|string|max:255'
'email' => 'required|email|unique:users,email'
'idade' => 'required|integer|min:18|max:120'
'ativo' => 'required|boolean'
'data' => 'required|date_format:Y-m-d'
'cpf' => 'required|regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/'
'telefone' => 'required|regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/'
```

---

## 🔗 Caminhos de Arquivos

```
app/Exceptions/Handler.php                           ← Tratamento central
app/Traits/ApiResponse.php                          ← Respostas padrão
app/Http/Requests/Store{Entidade}Request.php        ← Validações
routes/web.php                                       ← Rate limiting
```

---

## 🧪 Testando

### Postman
```
POST /salvar-cliente
Headers:
  Authorization: Bearer TOKEN
  Content-Type: application/json

Body:
{
  "txtClienteCodigo": "C001",
  "txtClienteNome": "João"
}
```

### Curl
```bash
curl -X POST http://localhost:8000/salvar-cliente \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"txtClienteCodigo":"C001","txtClienteNome":"João"}'
```

---

## 🚨 Erros Comuns

### ❌ Sem FormRequest (antes)
```php
// ❌ NÃO FAZER
$validated = $request->validate([...]);
return response()->json(['ok'=>true, 'msg'=>'...']);
```

### ✅ Com FormRequest (depois)
```php
// ✅ FAZER ASSIM
public function store(StoreXyzRequest $request) {
    $validated = $request->validated();
    return $this->respondCreated($xyz);
}
```

---

## 🔑 Cheatsheet

```php
// ✅ Sucesso
return $this->respondSuccess($data);
return $this->respondCreated($data);

// ❌ Erro
return $this->respondNotFound();
return $this->respondForbidden();
return $this->respondUnauthorized();
return $this->respondValidationError($errors);
return $this->respondError('Mensagem', [], 500);

// 📄 Paginação
return $this->respondSuccessPaginated($paginator);

// 🔲 Vazio
return $this->respondNoContent();
```

---

## 📚 Ler Mais

- `VALIDACAO_PADRAO.md` - Documentação completa
- `EXEMPLO_REFACTORING_CLIENTE.md` - Tutorial passo a passo
- `RATE_LIMITING.md` - Detalhes de rate limiting
- `FASE1_RESUMO.md` - Resumo da implementação

---

## 🆘 Precisa de Ajuda?

1. Verificar se FormRequest está criado
2. Verificar se Controller usa `ApiResponse` Trait
3. Verificar se FormRequest tem `validated()` method
4. Ler documentação correspondente
5. Testar com Postman

---

## ⚡ Checklist Rápido

- [ ] Importar `ApiResponse` Trait
- [ ] Criar `StoreXyzRequest`
- [ ] Adicionar `rules()` method
- [ ] Adicionar `messages()` method (opcional)
- [ ] Trocar `response()->json()` por `respondXyz()`
- [ ] Testar com Postman
- [ ] Pronto! 🎉

---

**Dúvidas?** Leia `VALIDACAO_PADRAO.md`
