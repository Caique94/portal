# ✅ Checklist de Code Review - Portal Application

Use este checklist para revisar **TODA** nova feature ou bug fix antes do merge.

---

## 🔒 1. SQL Injection

- [ ] ❌ Nenhuma concatenação de strings em queries (`"SELECT * FROM users WHERE id = $id"`)
- [ ] ✅ Todo `whereRaw()`, `selectRaw()`, `orderByRaw()` tem segundo parâmetro com bindings
- [ ] ✅ Queries raw usam `?` ou `:name` placeholders
- [ ] ✅ Inputs de usuário (`request()->input()`, `$_GET`, `$_POST`) **nunca** vão direto para SQL
- [ ] ✅ Arrays de IDs usam `whereIn()` ou `ANY($1::int[])` (PostgreSQL)
- [ ] ✅ LIKE queries usam binding: `where('name', 'LIKE', $var)`

**❌ Exemplo PROIBIDO:**
```php
DB::select("SELECT * FROM users WHERE email = '{$email}'");
```

**✅ Exemplo CORRETO:**
```php
DB::select('SELECT * FROM users WHERE email = ?', [$email]);
```

---

## 🔐 2. Autenticação e Autorização

- [ ] ✅ Senhas são hasheadas com `Hash::make()` (nunca plaintext)
- [ ] ✅ Tokens têm expiração definida
- [ ] ✅ Tokens são revogados no logout
- [ ] ✅ Verificação de `$user->ativo` antes de autenticar
- [ ] ✅ Middleware `auth` aplicado em rotas protegidas
- [ ] ✅ Autorização por papel (`RoleMiddleware`) onde necessário
- [ ] ❌ Nenhum token é logado ou exposto em mensagens de erro
- [ ] ❌ Nenhum token em query strings (`?token=...`)

---

## 🛡️ 3. Validação de Dados

- [ ] ✅ Todo endpoint POST/PUT/PATCH usa **Form Request**
- [ ] ✅ Validação de CPF/CNPJ/CEP implementada corretamente
- [ ] ✅ Valores monetários validados com regex `^\d+(\.\d{1,2})?$`
- [ ] ✅ Emails validam com `email:rfc,dns`
- [ ] ✅ Campos obrigatórios marcados com `required`
- [ ] ✅ Campos numéricos têm `min` e `max` definidos
- [ ] ✅ Strings têm `max:255` ou tamanho apropriado
- [ ] ✅ Datas validam com `date`, `before`, `after`
- [ ] ✅ Enums/selects validam com `Rule::in()`

**Exemplo de validação forte:**
```php
'txtUsuarioCPF' => [
    'nullable',
    'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/',
    function ($attribute, $value, $fail) {
        if (!$this->validateCPF($value)) {
            $fail('CPF inválido');
        }
    },
],
```

---

## 🧹 4. Sanitização de Dados

- [ ] ✅ Máscaras são removidas antes de salvar no banco (CPF, CNPJ, CEP, telefone)
- [ ] ✅ Valores monetários são convertidos corretamente (vírgula → ponto)
- [ ] ✅ Strings são trimmed antes de salvar
- [ ] ✅ Output no Blade usa `{{ }}` (escapado) ao invés de `{!! !!}`
- [ ] ❌ Nenhum `innerHTML` com dados de usuário em JavaScript

**Sanitização de CPF:**
```php
$cpf = preg_replace('/\D/', '', $request->input('txtUsuarioCPF'));
```

---

## 📝 5. Logging

- [ ] ❌ **NUNCA** logar: senhas, tokens, API keys, dados de cartão
- [ ] ✅ Logs de falha de autenticação incluem: IP, user_agent, tentativa de email
- [ ] ✅ Ações administrativas são logadas (criar usuário, alterar permissões, etc.)
- [ ] ✅ Erros incluem contexto suficiente (`user_id`, `action`, `error_message`)
- [ ] ✅ SanitizeLogger está ativo (configurado em `config/logging.php`)
- [ ] ✅ Level de log apropriado: `debug`, `info`, `warning`, `error`, `critical`

**❌ NUNCA:**
```php
Log::info('User login', ['password' => $password, 'token' => $token]);
```

**✅ SEMPRE:**
```php
Log::info('Login realizado', [
    'user_id' => $user->id,
    'ip' => $request->ip(),
]);
```

---

## 🚦 6. Rate Limiting

- [ ] ✅ Rota de login tem rate limit restritivo (`rate.limit:5,1`)
- [ ] ✅ Endpoints de API têm rate limit apropriado
- [ ] ✅ Endpoints críticos (reset password, create user) têm rate limit extra
- [ ] ✅ Rate limit apropriado por tipo de endpoint:
  - Login: 5 req/min
  - Password reset: 3 req/10min
  - API geral: 60 req/min
  - Endpoints de leitura: 120 req/min

**Aplicar rate limiting:**
```php
Route::post('/login', ...)->middleware('rate.limit:5,1');
```

---

## 🔒 7. Headers de Segurança

- [ ] ✅ Middleware `SecurityHeaders` está aplicado globalmente
- [ ] ✅ CSP (Content-Security-Policy) permite apenas domínios confiáveis
- [ ] ✅ `X-Frame-Options` está configurado
- [ ] ✅ HTTPS forçado em produção (HSTS)
- [ ] ✅ CORS configurado com domínios específicos (não `*`)

---

## 🌐 8. CORS

- [ ] ✅ `allowed_origins` lista domínios específicos (não `['*']`)
- [ ] ✅ `supports_credentials` é `true` para cookies/auth
- [ ] ✅ `allowed_headers` inclui apenas headers necessários
- [ ] ❌ `allowed_methods` não inclui métodos não usados

**Configuração segura:**
```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
],
```

---

## 💾 9. Banco de Dados

- [ ] ✅ Migrations têm `down()` implementado
- [ ] ✅ Foreign keys usam `onDelete('cascade')` ou `onDelete('set null')`
- [ ] ✅ Índices criados para colunas usadas em WHERE, JOIN, ORDER BY
- [ ] ✅ Colunas sensíveis (CPF, email) têm índices
- [ ] ✅ Campos de dinheiro usam `DECIMAL(10,2)` (não VARCHAR)
- [ ] ✅ Timestamps (`created_at`, `updated_at`) adicionados onde apropriado

---

## 🧪 10. Testes (se aplicável)

- [ ] ✅ Feature test para endpoint criado
- [ ] ✅ Validação de inputs é testada
- [ ] ✅ Casos de erro são testados (401, 403, 404, 422, 500)
- [ ] ✅ Autenticação/autorização testada

---

## 📁 11. Arquivos e Estrutura

- [ ] ✅ Controller tem apenas lógica de requisição/resposta
- [ ] ✅ Lógica de negócio está em Services/Actions
- [ ] ✅ Queries complexas estão em Repositories ou Scopes
- [ ] ✅ Nomes de variáveis são descritivos (`$user` não `$u`)
- [ ] ✅ Funções têm no máximo 20-30 linhas
- [ ] ✅ Comentários PHPDoc onde necessário

---

## 🔧 12. Performance

- [ ] ✅ N+1 queries evitados (use `with()` para relacionamentos)
- [ ] ✅ Paginação implementada para listas grandes
- [ ] ✅ Cache usado onde apropriado
- [ ] ✅ Índices de banco criados para queries frequentes
- [ ] ❌ Nenhum `select *` desnecessário (especifique colunas)

**Evitar N+1:**
```php
// ❌ N+1
$users = User::all();
foreach ($users as $user) {
    echo $user->cliente->nome;  // Query por iteração
}

// ✅ Eager Loading
$users = User::with('cliente')->get();
```

---

## 🐛 13. Tratamento de Erros

- [ ] ✅ Try-catch em operações que podem falhar (API externa, DB, file I/O)
- [ ] ✅ Erros retornam JSON estruturado: `{ success: false, message: '...' }`
- [ ] ✅ HTTP status codes corretos:
  - 200: OK
  - 201: Created
  - 400: Bad Request
  - 401: Unauthorized
  - 403: Forbidden
  - 404: Not Found
  - 422: Validation Error
  - 500: Internal Server Error
- [ ] ✅ Mensagens de erro são amigáveis para o usuário
- [ ] ❌ Stack traces **nunca** expostos ao frontend em produção

---

## 🎨 14. Frontend (JavaScript)

- [ ] ✅ AJAX usa CSRF token (`X-CSRF-TOKEN` header)
- [ ] ✅ Inputs são validados no frontend também (UX)
- [ ] ✅ Erros de API são tratados e mostrados ao usuário
- [ ] ✅ Loading states implementados
- [ ] ❌ Nenhum `eval()` ou código dinâmico perigoso
- [ ] ❌ Nenhum `innerHTML` com dados de usuário

**CSRF Token:**
```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

---

## 📝 15. Documentação

- [ ] ✅ README atualizado se necessário
- [ ] ✅ Comentários em código complexo
- [ ] ✅ Variáveis de ambiente documentadas em `.env.example`
- [ ] ✅ API endpoints documentados (se API pública)

---

## 🚀 16. Deploy e Produção

- [ ] ✅ `.env.example` atualizado com novas variáveis
- [ ] ✅ Migrations testadas (up e down)
- [ ] ✅ Seeds testados (se houver)
- [ ] ✅ Assets compilados (`npm run build`)
- [ ] ✅ Cache limpo em produção após deploy:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

---

## ✍️ Assinatura do Revisor

**Feature/Bug:** #___
**Desenvolvedor:** ___________
**Revisor:** ___________
**Data:** ___/___/______

**Todos os itens foram verificados?** [ ] Sim [ ] Não

**Comentários adicionais:**
___________________________________________
___________________________________________
___________________________________________

---

## 🔴 Itens Críticos (Bloqueiam Merge)

Se **QUALQUER** item abaixo falhar, o código **NÃO** pode ir para produção:

1. ❌ SQL Injection presente
2. ❌ Senhas ou tokens em plaintext
3. ❌ APP_DEBUG=true em produção
4. ❌ Autenticação não implementada em rota sensível
5. ❌ Validação de entrada faltando
6. ❌ Dados sensíveis logados
7. ❌ CORS aberto para `*` em produção

---

**Última atualização:** 2025-12-04
