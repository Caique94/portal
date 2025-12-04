# 🔒 Política de Segurança - Portal Application

## Índice

1. [SQL Injection](#1-sql-injection)
2. [Autenticação e Tokens](#2-autenticação-e-tokens)
3. [Rate Limiting](#3-rate-limiting)
4. [Headers de Segurança](#4-headers-de-segurança)
5. [Validação de Dados](#5-validação-de-dados)
6. [Logging Seguro](#6-logging-seguro)
7. [Checklist de Code Review](#7-checklist-de-code-review)
8. [Variáveis de Ambiente](#8-variáveis-de-ambiente)

---

## 1. SQL Injection

### ✅ SEMPRE FAÇA

```php
// ✅ Eloquent ORM (preferido)
User::where('email', $email)->first();

// ✅ Query Builder com bindings
DB::select('SELECT * FROM users WHERE email = ?', [$email]);

// ✅ whereRaw com bindings
User::whereRaw('valor_hora BETWEEN ? AND ?', [$min, $max])->get();
```

### 🚫 NUNCA FAÇA

```php
// ❌ Concatenação
$user = DB::select("SELECT * FROM users WHERE email = '$email'");

// ❌ whereRaw sem bindings
User::whereRaw("name LIKE '%{$search}%'")->get();

// ❌ Template string direto
DB::statement("DELETE FROM users WHERE id = $id");
```

### 📋 Regras Obrigatórias

1. **SEMPRE** use Eloquent ou Query Builder
2. **SEMPRE** use bindings (`?` ou `:name`) em queries raw
3. **NUNCA** concatene variáveis diretamente em queries
4. **NUNCA** use `$_GET`, `$_POST`, `request()->input()` direto em SQL
5. Todo `whereRaw`, `selectRaw`, `orderByRaw` **DEVE** ter segundo parâmetro com bindings

---

## 2. Autenticação e Tokens

### 🔐 Laravel Sanctum

**Geração de Token:**
```php
$token = $user->createToken(
    'auth-token',
    ['*'],  // Abilities
    now()->addHours(1)  // Expiração
);

return response()->json([
    'token' => $token->plainTextToken,
    'expires_in' => 3600,
]);
```

**Revogação de Token:**
```php
// Revogar token atual
$request->user()->currentAccessToken()->delete();

// Revogar todos os tokens
$request->user()->tokens()->delete();
```

### ⏱️ Tabela de Expiração

| Token | Duração | Renovável | Onde Armazenar |
|-------|---------|-----------|----------------|
| Access Token | 1 hora | ✅ Sim | Memória JS |
| Refresh Token | 7 dias | ❌ Não | HttpOnly Cookie |
| CSRF Token | Sessão | ✅ Auto | Cookie + Meta |
| API Key | Indefinido | ❌ Não | Banco |
| Password Reset | 1 hora | ❌ Não | Banco |

### 🚫 Proteção contra Vazamento

**NUNCA faça:**
```php
// ❌ NÃO logar tokens
Log::info('Token: ' . $token);

// ❌ NÃO enviar em query string
return redirect('/?token=' . $token);

// ❌ NÃO expor em mensagens de erro
throw new Exception('Invalid: ' . $token);
```

**Sempre use o SanitizeLogger** - ele automaticamente remove tokens, senhas e dados sensíveis dos logs.

---

## 3. Rate Limiting

### 🛡️ Configuração Aplicada

**Login:** 5 requisições por minuto
```php
Route::post('/login', [LoginController::class, 'authenticate'])
    ->middleware('rate.limit:5,1');
```

**API Geral:** 60 requisições por minuto (padrão do CustomRateLimiter)

**Endpoints Críticos:** Aplicar rate limit mais restritivo
```php
Route::post('/reset-password', ...)
    ->middleware('rate.limit:3,10');  // 3 tentativas a cada 10 minutos
```

### 📊 Headers de Rate Limit

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
Retry-After: 30
```

---

## 4. Headers de Segurança

### 🛡️ Headers Aplicados Automaticamente

Middleware `SecurityHeaders` adiciona:

```
X-XSS-Protection: 1; mode=block
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Strict-Transport-Security: max-age=31536000 (produção)
Content-Security-Policy: default-src 'self'; ...
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), ...
```

### 🔧 Ajustar CSP

Edite `app/Http/Middleware/SecurityHeaders.php`:

```php
$csp = implode('; ', [
    "default-src 'self'",
    "script-src 'self' https://trusted-cdn.com",
    // Adicione domínios conforme necessário
]);
```

---

## 5. Validação de Dados

### ✅ Use Form Requests

**Exemplo: StoreUserRequest**
```php
public function rules(): array
{
    return [
        'txtUsuarioEmail' => 'required|email:rfc,dns|unique:users,email',
        'txtUsuarioCPF' => [
            'nullable',
            'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/',
            function ($attribute, $value, $fail) {
                if (!$this->validateCPF($value)) {
                    $fail('CPF inválido');
                }
            },
        ],
    ];
}
```

### 📋 Validações Comuns

| Campo | Regex | Exemplo |
|-------|-------|---------|
| **CPF** | `^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$` | 000.000.000-00 |
| **CNPJ** | `^\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2}$` | 00.000.000/0000-00 |
| **CEP** | `^\d{5}-?\d{3}$` | 00000-000 |
| **Celular** | `^\(?\d{2}\)?\s?9?\d{4}-?\d{4}$` | (00) 90000-0000 |
| **Telefone** | `^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$` | (00) 0000-0000 |
| **Valor Monetário** | `^\d+(\.\d{1,2})?$` | 1500.50 |

### 🧹 Sanitização no Controller

```php
public function store(StoreUserRequest $request)
{
    // Dados já validados e sanitizados pelo Form Request
    $validated = $request->validated();

    // Remover máscara de CPF antes de salvar
    if (!empty($validated['txtUsuarioCPF'])) {
        $validated['txtUsuarioCPF'] = preg_replace('/\D/', '', $validated['txtUsuarioCPF']);
    }

    User::create($validated);
}
```

### 🚫 Proteção XSS

**Blade automaticamente escapa:**
```blade
{{-- ✅ Escapado automaticamente --}}
{{ $user->name }}

{{-- ❌ Raw HTML (use apenas se confiar 100%) --}}
{!! $trustedHtml !!}
```

**JavaScript:**
```javascript
// ✅ Escapar antes de inserir no DOM
const name = escapeHtml(user.name);
document.getElementById('name').textContent = name;

// ❌ NUNCA use innerHTML com dados de usuário
element.innerHTML = user.name;  // PERIGOSO!
```

---

## 6. Logging Seguro

### ✅ O QUE LOGAR

```php
// ✅ Eventos de segurança
Log::warning('Login falhou', [
    'email' => $email,  // OK
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);

// ✅ Ações administrativas
Log::info('Usuário criado', [
    'admin_id' => auth()->id(),
    'new_user_id' => $user->id,
    'papel' => $user->papel,
]);

// ✅ Erros de aplicação
Log::error('Falha ao processar pagamento', [
    'user_id' => $user->id,
    'order_id' => $order->id,
    'error' => $e->getMessage(),
]);
```

### 🚫 O QUE NUNCA LOGAR

```php
// ❌ NUNCA logar:
- Senhas (password, senha, pwd)
- Tokens (token, access_token, api_key)
- Dados de cartão de crédito
- Chaves de API
- Authorization headers completos
- Refresh tokens

// O SanitizeLogger automaticamente remove estes dados
```

### 🔍 SanitizeLogger Automático

Já configurado em `config/logging.php`. Dados sensíveis são automaticamente substituídos por `[REDACTED]`.

**Exemplo:**
```php
// Input
Log::info('Dados', [
    'email' => 'user@example.com',
    'password' => '123456',
    'token' => 'abc123xyz',
]);

// Output no log
Dados {"email":"[EMAIL]","password":"[REDACTED]","token":"[REDACTED]"}
```

---

## 7. Checklist de Code Review

### 🔍 SQL Injection

- [ ] Nenhuma concatenação de strings em queries
- [ ] Todo `whereRaw`, `selectRaw`, `orderByRaw` tem bindings
- [ ] Nenhum `DB::raw()` sem bindings
- [ ] Queries raw usam `?` ou `:name` placeholders
- [ ] Inputs de usuário nunca vão direto para queries

### 🔐 Autenticação

- [ ] Senhas são hasheadas com `Hash::make()`
- [ ] Tokens têm expiração definida
- [ ] Tokens são revogados no logout
- [ ] Refresh tokens são armazenados no banco
- [ ] Verificação de `ativo` antes de autenticar

### 🛡️ Validação

- [ ] Todo endpoint POST/PUT usa Form Request
- [ ] Validação de CPF/CNPJ/CEP implementada
- [ ] Valores monetários são validados com regex
- [ ] Emails validam com `email:rfc,dns`
- [ ] Campos obrigatórios marcados com `required`

### 📝 Logging

- [ ] Nenhum log contém senhas ou tokens
- [ ] Logs de falha de autenticação incluem IP
- [ ] Ações administrativas são logadas
- [ ] Erros incluem contexto suficiente (mas sem dados sensíveis)

### 🚦 Rate Limiting

- [ ] Login tem rate limit restritivo (5/min)
- [ ] Endpoints de API têm rate limit apropriado
- [ ] Endpoints críticos têm rate limit extra

### 🔒 Headers

- [ ] HTTPS forçado em produção (HSTS)
- [ ] CSP configurado adequadamente
- [ ] X-Frame-Options protege contra clickjacking
- [ ] Content-Type validado

---

## 8. Variáveis de Ambiente

### 📄 .env.example (Configurações Seguras)

```env
# ==========================
# APLICAÇÃO
# ==========================
APP_NAME="Portal"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://seudominio.com.br

# ==========================
# LOGGING
# ==========================
LOG_CHANNEL=stack
LOG_LEVEL=warning  # production: warning ou error
LOG_STACK=daily
LOG_DAILY_DAYS=14

# ==========================
# BANCO DE DADOS
# ==========================
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=portal_producao
DB_USERNAME=portal_user
DB_PASSWORD=SENHA_FORTE_AQUI_MIN_16_CARACTERES

# ==========================
# SEGURANÇA
# ==========================
SESSION_DRIVER=database  # Mais seguro que file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true  # Apenas HTTPS
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Token Sanctum
SANCTUM_TOKEN_EXPIRATION=60  # minutos
SANCTUM_STATEFUL_DOMAINS=seudominio.com.br,www.seudominio.com.br

# CORS
FRONTEND_URL=https://app.seudominio.com.br

# ==========================
# EMAIL (Exemplo: Gmail)
# ==========================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=senha-app-gmail  # Use App Password, não senha normal
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seudominio.com.br
MAIL_FROM_NAME="${APP_NAME}"

# ==========================
# CACHE & QUEUE
# ==========================
CACHE_STORE=redis  # ou file
QUEUE_CONNECTION=redis  # ou database/sync

# Redis (se usar)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=SENHA_REDIS_AQUI
REDIS_PORT=6379

# ==========================
# SERVIÇOS EXTERNOS
# ==========================
# APIs públicas (sem auth) podem ficar no .env
VIACEP_URL=https://viacep.com.br/ws

# ==========================
# MONITORAMENTO (Opcional)
# ==========================
# SENTRY_LARAVEL_DSN=
# BUGSNAG_API_KEY=
```

### 🔒 Checklist de .env

**Desenvolvimento:**
- [ ] `APP_DEBUG=true` está OK
- [ ] `APP_ENV=local`
- [ ] Senhas podem ser simples

**Produção:**
- [ ] `APP_DEBUG=false` ⚠️ CRÍTICO
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` foi gerado (`php artisan key:generate`)
- [ ] Senha do banco tem 16+ caracteres, alfanumérica + símbolos
- [ ] `SESSION_SECURE_COOKIE=true` (força HTTPS)
- [ ] `SESSION_HTTP_ONLY=true`
- [ ] `LOG_LEVEL=warning` ou `error`
- [ ] CORS configurado com domínios específicos (não `*`)
- [ ] Todas as credenciais foram alteradas das padrões
- [ ] Arquivo `.env` **NUNCA** vai para o Git

---

## 📚 Recursos Adicionais

### OWASP Top 10 (2021)
1. Broken Access Control
2. Cryptographic Failures
3. **Injection (SQL, XSS)** ← Coberto neste doc
4. Insecure Design
5. Security Misconfiguration
6. Vulnerable and Outdated Components
7. Identification and Authentication Failures
8. Software and Data Integrity Failures
9. Security Logging and Monitoring Failures
10. Server-Side Request Forgery (SSRF)

### Links Úteis
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [PostgreSQL Security](https://www.postgresql.org/docs/current/security.html)

---

## 🚨 Reportar Vulnerabilidade

Se você descobrir uma vulnerabilidade de segurança:

1. **NÃO** abra uma issue pública
2. Envie email para: seguranca@seudominio.com.br
3. Inclua:
   - Descrição detalhada
   - Steps to reproduce
   - Impacto potencial
   - Sugestão de fix (se tiver)

Responderemos em até 48 horas.

---

**Última Atualização:** 2025-12-04
**Versão:** 1.0
**Responsável:** Equipe de Desenvolvimento
