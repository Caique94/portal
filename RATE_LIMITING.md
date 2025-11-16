# Rate Limiting - Portal Personalitec

## 📋 O Que É Rate Limiting?

Rate Limiting é uma técnica que limita o número de requisições que um usuário/IP pode fazer em um período de tempo. Protege contra:

- **Brute Force Attacks** - Tentativas de força bruta em login
- **DOS Attacks** - Ataques de negação de serviço
- **Abuso de API** - Uso excessivo de recursos
- **Spam** - Requisições maliciosas repetidas

---

## 🔧 Implementação no Portal

### Configuração Atual

```php
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    // APIs internas
});
```

**Significado:** `throttle:60,1`
- **60** = máximo de 60 requisições
- **1** = por minuto

---

## 📊 Limites Recomendados

### Por Tipo de Endpoint

| Tipo | Limite | Período | Motivo |
|------|--------|---------|--------|
| **Listagem** | 60 | 1 min | Busca frequente é normal |
| **Criação** | 30 | 1 min | Menos frequente |
| **Atualização** | 30 | 1 min | Menos frequente |
| **Deleção** | 10 | 1 min | Deve ser raro |
| **Login** | 5 | 1 min | Proteção brute force |

---

## 🚀 Implementação Granular

### Opção 1: Rate Limit por Tipo de Requisição

```php
Route::middleware('auth')->group(function () {
    // Leitura - mais permissivo
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/listar-clientes', [ClienteController::class, 'list']);
        Route::get('/listar-produtos', [ProdutoController::class, 'list']);
    });

    // Escrita - mais restritivo
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/salvar-cliente', [ClienteController::class, 'store']);
        Route::post('/salvar-produto', [ProdutoController::class, 'store']);
    });

    // Deleção - muito restritivo
    Route::middleware('throttle:10,1')->group(function () {
        Route::delete('/excluir-cliente/{id}', [ClienteController::class, 'delete']);
        Route::delete('/excluir-produto/{id}', [ProdutoController::class, 'delete']);
    });
});
```

### Opção 2: Rate Limit Customizado por Rol

```php
// Em app/Http/Middleware/RateLimitByRole.php
public function handle($request, $next)
{
    $user = auth()->user();

    if ($user->papel === 'admin') {
        return $next($request)->header('X-RateLimit-Limit', '300');
    }

    if ($user->papel === 'consultor') {
        return $next($request)->header('X-RateLimit-Limit', '100');
    }

    return $next($request);
}
```

---

## 🔐 Segurança - Login Protegido

### Adicionar rate limit específico para login

```php
Route::post('/login', [LoginController::class, 'authenticate'])
    ->middleware('throttle:5,1');  // Máximo 5 tentativas por minuto
```

---

## 📊 Resposta ao Atingir Rate Limit

### Status HTTP 429 (Too Many Requests)

O `ExceptionHandler` já trata isso:

```json
{
  "success": false,
  "message": "Muitas requisições. Tente novamente mais tarde",
  "code": 429,
  "retry_after": 60
}
```

**Headers Retornados:**
- `X-RateLimit-Limit` - Total de requisições permitidas
- `X-RateLimit-Remaining` - Requisições restantes
- `X-RateLimit-Reset` - Timestamp de reset
- `Retry-After` - Segundos até poder tentar novamente

---

## 🛠️ Configuração no Cache

Rate Limiting usa cache para rastrear requisições. Certifique-se de ter um cache configurado:

### .env
```env
CACHE_DRIVER=redis  # Recomendado para production
# ou
CACHE_DRIVER=database  # Alternativa se Redis não disponível
```

### Se usar database cache

```bash
php artisan cache:table
php artisan migrate
```

---

## 📈 Monitoramento

### Registrar Rate Limit Violations

Adicionar ao `ExceptionHandler`:

```php
if ($exception instanceof ThrottleRequestsException) {
    Log::warning('Rate limit exceeded', [
        'user_id' => auth()->id(),
        'ip' => request()->ip(),
        'path' => request()->path(),
        'timestamp' => now(),
    ]);

    return response()->json([
        'success' => false,
        'message' => 'Muitas requisições',
        'code' => 429,
    ], 429);
}
```

---

## 🧪 Testando Rate Limit

### Com Curl

```bash
#!/bin/bash

# Fazer 70 requisições (limite é 60)
for i in {1..70}; do
    curl -H "Authorization: Bearer TOKEN" \
         http://localhost:8000/listar-clientes

    if [ $? -eq 0 ]; then
        echo "Requisição $i: OK"
    else
        echo "Requisição $i: BLOQUEADA"
    fi

    sleep 0.1  # 100ms entre requisições
done
```

### Com Postman

1. Criar request `GET /listar-clientes`
2. Na aba "Tests", adicionar:
```javascript
pm.test("Rate Limit Headers", function() {
    pm.expect(pm.response.headers.get('X-RateLimit-Limit')).to.exist;
    pm.expect(pm.response.headers.get('X-RateLimit-Remaining')).to.exist;
});
```
3. Run > Runner
4. Set iterations: 70
5. Executar

---

## 🚨 Tratamento do Erro no Frontend

### JavaScript

```javascript
fetch('/listar-clientes', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
})
.then(response => {
    if (response.status === 429) {
        const retryAfter = response.headers.get('Retry-After');
        throw new Error(`Muitas requisições. Tente novamente em ${retryAfter}s`);
    }
    return response.json();
})
.catch(error => {
    console.error('Erro:', error.message);
    // Mostrar aviso visual ao usuário
});
```

### jQuery

```javascript
$.ajax({
    url: '/listar-clientes',
    type: 'GET',
    statusCode: {
        429: function() {
            alert('Muitas requisições. Aguarde um momento.');
            // Desabilitar botão por X segundos
        }
    }
});
```

---

## 📊 Limites Sugeridos para Portal Personalitec

### Desenvolvimento
```php
throttle:1000,1  // Sem limites reais
```

### Staging
```php
// Leitura
throttle:100,1

// Escrita
throttle:50,1

// Deleção
throttle:20,1
```

### Production
```php
// Leitura
throttle:60,1

// Escrita
throttle:30,1

// Deleção
throttle:10,1

// Login
throttle:5,1
```

---

## 🔄 Reset de Limite

Rate limits são **resetados automaticamente** a cada período (1 minuto no exemplo).

Não há comando manual para resetar, mas você pode:

1. **Limpar o cache** (reseta todos os limites):
```bash
php artisan cache:clear
```

2. **Limpar apenas Rate Limits** (se usar Redis):
```bash
redis-cli KEYS "throttle:*" | xargs redis-cli DEL
```

---

## ⚠️ Considerações Importantes

### 1. **Load Balancing**
Se usar múltiplos servidores, certifique-se que o cache é compartilhado (Redis).

### 2. **IP vs User ID**
Por padrão, rate limit é por IP. Para usar User ID:

```php
// Em config/cache.php ou no middleware
// Rate limit por user_id ao invés de IP
```

### 3. **Whitelist de IPs**
Para IPs internos/confiáveis:

```php
if (in_array(request()->ip(), ['127.0.0.1', '192.168.1.1'])) {
    return $next($request);  // Sem rate limit
}
```

---

## 📚 Referências

- [Laravel Throttling](https://laravel.com/docs/rate-limiting)
- [HTTP 429 Status](https://httpwg.org/specs/rfc6585.html#status.429)
- [OWASP API Security](https://owasp.org/www-project-api-security/)

---

## 🎯 Próximas Etapas

- [ ] Configurar Redis para cache em production
- [ ] Adicionar logging de violações
- [ ] Criar alerts para abuso detectado
- [ ] Implementar whitelist de IPs
- [ ] Testar com ferramentas de carga
