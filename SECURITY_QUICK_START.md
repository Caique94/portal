# 🚀 Guia Rápido - Segurança Aplicada

## ✅ O que JÁ está funcionando

### 1. Headers de Segurança ✅
**Já aplicado automaticamente em TODAS as respostas**

Não precisa fazer nada. Todo response já tem:
- Proteção XSS
- Proteção Clickjacking
- Content Security Policy
- E mais...

### 2. Rate Limiting ✅
**Já aplicado no login (5 tentativas/minuto)**

```php
// Login já está protegido ✅
Route::post('/login', ...)->middleware('rate.limit:5,1');
```

### 3. Sanitização de Logs ✅
**Já aplicado em TODOS os logs**

Senhas, tokens, CPFs são **automaticamente** removidos dos logs.

```php
// Pode usar normalmente, dados sensíveis são automaticamente removidos
Log::info('User data', [
    'email' => 'user@example.com',
    'password' => '123',  // Será [REDACTED]
]);
```

### 4. Validação de Content-Type ✅
**Já aplicado automaticamente**

Requisições POST/PUT/PATCH são validadas automaticamente.

---

## 📋 O que VOCÊ precisa fazer

### 1. Aplicar Form Request nos Controllers

**ANTES:**
```php
public function store(Request $request)
{
    $request->validate([
        'txtUsuarioNome' => 'required|string|max:255',
        'txtUsuarioEmail' => 'required|email',
        // ... muitas regras
    ]);

    User::create($request->all());
}
```

**DEPOIS:**
```php
use App\Http\Requests\StoreUserRequest;

public function store(StoreUserRequest $request)
{
    // Dados já validados e sanitizados!
    $validated = $request->validated();

    User::create($validated);
}
```

**Aplicar em:**
- [ ] UserController::store()
- [ ] ClienteController::store()
- [ ] ProdutoController::store()
- [ ] Outros controllers com POST/PUT

### 2. Aplicar Rate Limiting em Rotas Críticas

**Adicionar em `routes/web.php`:**

```php
// Password reset (3 tentativas a cada 10 minutos)
Route::post('/reset-password', ...)->middleware('rate.limit:3,10');

// Criar/editar recursos (30 por minuto)
Route::post('/salvar-usuario', ...)->middleware('rate.limit:30,1');
Route::post('/salvar-cliente', ...)->middleware('rate.limit:30,1');
Route::post('/salvar-produto', ...)->middleware('rate.limit:30,1');

// Ordem de serviço (20 por minuto - mais crítico)
Route::post('/salvar-ordem-servico', ...)->middleware('rate.limit:20,1');
```

### 3. Revisar Queries por SQL Injection

**Buscar padrões perigosos:**

```bash
# No terminal, procurar concatenações
grep -r "DB::select.*\$" app/Http/Controllers/
grep -r "whereRaw.*{" app/Http/Controllers/
```

**Substituir:**

```php
// ❌ PERIGOSO
DB::select("SELECT * FROM users WHERE id = $id");
whereRaw("name LIKE '%{$search}%'")

// ✅ SEGURO
DB::select('SELECT * FROM users WHERE id = ?', [$id]);
whereRaw('name LIKE ?', ["%{$search}%"])
```

---

## 🧪 Como Testar

### Teste 1: Verificar Headers de Segurança

```bash
curl -I http://localhost:8000/

# Deve mostrar:
# X-XSS-Protection: 1; mode=block
# X-Frame-Options: SAMEORIGIN
# X-Content-Type-Options: nosniff
```

### Teste 2: Testar Rate Limiting

```bash
# Fazer 6 tentativas de login rápido
for i in {1..6}; do
  curl -X POST http://localhost:8000/login \
    -d "email=test@test.com&password=123"
done

# 6ª requisição deve retornar: HTTP 429 Too Many Requests
```

### Teste 3: Verificar Sanitização de Logs

**Em qualquer controller, adicione temporariamente:**

```php
Log::info('Teste de segurança', [
    'password' => '123456',
    'token' => 'abc123xyz',
    'email' => 'user@example.com',
    'cpf' => '000.000.000-00',
]);
```

**Verificar log:**

```bash
tail -f storage/logs/laravel.log
```

**Deve mostrar:**
```
Teste de segurança {"password":"[REDACTED]","token":"[REDACTED]","email":"[EMAIL]","cpf":"[CPF]"}
```

---

## 📚 Documentos Importantes

### Para Desenvolvedores

1. **[SECURITY.md](SECURITY.md)** - Política completa de segurança
   - SQL Injection (como evitar)
   - Autenticação e tokens
   - Validações comuns (CPF, CNPJ, CEP)
   - O que logar e o que não logar

2. **[CODE_REVIEW_CHECKLIST.md](CODE_REVIEW_CHECKLIST.md)** - Use em TODA revisão
   - 16 pontos de verificação
   - Itens críticos que bloqueiam merge

3. **[SECURITY_IMPLEMENTATION_SUMMARY.md](SECURITY_IMPLEMENTATION_SUMMARY.md)** - Resumo executivo
   - O que foi implementado
   - Status de cada item
   - Próximos passos

---

## 🚨 Antes de Ir para Produção

### Checklist Obrigatório

- [ ] `APP_DEBUG=false` no `.env`
- [ ] `APP_ENV=production` no `.env`
- [ ] `LOG_LEVEL=warning` ou `error` no `.env`
- [ ] HTTPS configurado (SSL/TLS)
- [ ] Senha do banco forte (16+ caracteres)
- [ ] Executar migrations em produção:
  ```bash
  php artisan migrate --force
  ```
- [ ] Executar SQL de estados/cidades:
  ```bash
  psql -U user -d database -f estados_cidades_brasil.sql
  ```
- [ ] Limpar e cachear configurações:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```
- [ ] Testar login em produção
- [ ] Testar rate limiting em produção
- [ ] Verificar logs em produção (sem dados sensíveis)

---

## 🔧 Configuração do .env

### Desenvolvimento (já configurado)

```env
APP_DEBUG=true
APP_ENV=local
LOG_LEVEL=debug
```

### Produção (configurar antes do deploy)

```env
APP_DEBUG=false  # ⚠️ CRÍTICO - Nunca true em produção
APP_ENV=production
LOG_LEVEL=warning

# Sessão (HTTPS)
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# CORS (domínio real)
FRONTEND_URL=https://app.seudominio.com.br
SANCTUM_STATEFUL_DOMAINS=seudominio.com.br,www.seudominio.com.br
```

---

## 💡 Dicas Rápidas

### ✅ SEMPRE faça

```php
// ✅ Use Eloquent ou Query Builder
User::where('email', $email)->first();

// ✅ Use bindings em queries raw
DB::select('SELECT * FROM users WHERE id = ?', [$id]);

// ✅ Use Form Requests para validação
public function store(StoreUserRequest $request)

// ✅ Remova máscaras antes de salvar
$cpf = preg_replace('/\D/', '', $request->input('cpf'));

// ✅ Log eventos importantes
Log::info('User created', ['user_id' => $user->id]);
```

### ❌ NUNCA faça

```php
// ❌ Concatenação em SQL
DB::select("SELECT * FROM users WHERE id = $id");

// ❌ Logar senhas ou tokens
Log::info('Login', ['password' => $pwd, 'token' => $token]);

// ❌ Expor dados sensíveis em erros
throw new Exception('Token: ' . $token);

// ❌ Salvar com máscara
User::create(['cpf' => '000.000.000-00']);  // Salvar sem pontos!
```

---

## 🆘 Problemas Comuns

### 1. "Too Many Requests" ao testar

**Problema:** Rate limiting bloqueando seus testes

**Solução:** Esperar 1 minuto ou limpar cache:
```bash
php artisan cache:clear
```

### 2. CORS bloqueando requisições

**Problema:** Frontend não consegue fazer requisições

**Solução:** Adicionar domínio em `config/cors.php`:
```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
    'http://localhost:3001',  // Adicione aqui
],
```

### 3. Logs não mostram dados sensíveis

**Isso é CORRETO!** O SanitizeLogger está funcionando.

Se precisar ver dados sensíveis em desenvolvimento, **temporariamente** remova de `config/logging.php`:
```php
// Remover temporariamente:
// 'processors' => [App\Logging\SanitizeLogger::class],
```

⚠️ **NUNCA** faça isso em produção!

---

## 📞 Suporte

**Dúvidas sobre segurança?**
1. Leia [SECURITY.md](SECURITY.md)
2. Verifique [CODE_REVIEW_CHECKLIST.md](CODE_REVIEW_CHECKLIST.md)
3. Consulte a equipe de segurança

**Encontrou uma vulnerabilidade?**
📧 seguranca@seudominio.com.br

---

**Última atualização:** 2025-12-04
**Versão:** 1.0
