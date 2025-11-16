# Redis Cache Setup Guide

## 📋 Visão Geral

Este guia configura Redis como cache driver para melhorar performance do Portal Personalitec.

**Benefícios:**
- Cache in-memory (muito mais rápido que file-based)
- Expiração automática de chaves
- Suporte para cache invalidation
- Performance: 100-1000x mais rápido que banco de dados

---

## 🚀 Quick Setup

### 1. Instalar Redis

**Windows (WSL ou Docker):**
```bash
# Docker (recomendado)
docker run -d -p 6379:6379 redis:latest

# Ou WSL
wsl
sudo apt update && sudo apt install redis-server
redis-server
```

**macOS:**
```bash
brew install redis
brew services start redis
```

**Linux:**
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl start redis-server
```

### 2. Verificar Instalação

```bash
redis-cli ping
# Esperado: PONG
```

### 3. Configurar Laravel

**`.env`:**
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**`config/cache.php`:**
```php
'default' => env('CACHE_DRIVER', 'redis'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
    ],
],
```

### 4. Testar Conexão

```bash
php artisan tinker
>>> Cache::put('test', 'working', 3600)
>>> Cache::get('test')
=> "working"
```

---

## 💾 Cache Implementation

### Static Data Caching

**Clients (24 horas):**
```php
// app/Http/Controllers/ClienteController.php
public function list() {
    $clientes = Cache::remember('clientes.all', 24 * 60, function() {
        return Cliente::with(['tabelaPreco', 'contatos'])
            ->orderBy('nome', 'asc')
            ->get();
    });

    return $this->respondSuccess($clientes);
}
```

**Products (24 horas):**
```php
// app/Http/Controllers/ProdutoController.php
public function list() {
    $produtos = Cache::remember('produtos.all', 24 * 60, function() {
        return Produto::where('ativo', true)
            ->orderBy('nome', 'asc')
            ->get();
    });

    return $this->respondSuccess($produtos);
}
```

**Price Tables (24 horas):**
```php
// app/Http/Controllers/TabelaPrecoController.php
public function list() {
    $tabelas = Cache::remember('tabelas_preco.all', 24 * 60, function() {
        return TabelaPreco::where('ativo', true)
            ->orderBy('nome', 'asc')
            ->get();
    });

    return $this->respondSuccess($tabelas);
}
```

### Dashboard Caching

**Payment Dashboard (15 minutos):**
```php
// app/Http/Controllers/PagamentoParcelaController.php
public function dashboard() {
    $stats = Cache::remember('pagamento.dashboard', 15, function() {
        $hoje = now();

        return [
            'total_pendentes' => PagamentoParcela::where('status', 'pendente')->count(),
            'total_atrasadas' => PagamentoParcela::where('status', 'atrasada')->count(),
            'total_pagas' => PagamentoParcela::where('status', 'paga')->count(),
            'valor_pendente' => PagamentoParcela::where('status', 'pendente')->sum('valor'),
            'valor_atrasado' => PagamentoParcela::where('status', 'atrasada')->sum('valor'),
            'valor_pago' => PagamentoParcela::where('status', 'paga')->sum('valor'),
            'vencendo_mes' => PagamentoParcela::where('status', 'pendente')
                ->whereBetween('data_vencimento', [$hoje, $hoje->copy()->addDays(30)])
                ->count()
        ];
    });

    return $this->respondSuccess($stats);
}
```

---

## 🔄 Cache Invalidation

### Events for Cache Invalidation

**When client is created/updated/deleted:**
```php
// app/Http/Controllers/ClienteController.php
public function store(StoreClienteRequest $request) {
    // ... create/update logic ...

    // Invalidate cache
    Cache::forget('clientes.all');
    Cache::forget('clientes.list');

    return $this->respondSuccess($cliente);
}

public function delete($id) {
    // ... delete logic ...

    // Invalidate cache
    Cache::forget('clientes.all');

    return $this->respondNoContent();
}
```

**When product is created/updated:**
```php
// app/Http/Controllers/ProdutoController.php
public function store(StoreProdutoRequest $request) {
    // ... create/update logic ...

    Cache::forget('produtos.all');
    Cache::forget('produtos.active');

    return $this->respondSuccess($produto);
}
```

**When payment status changes:**
```php
// app/Http/Controllers/PagamentoParcelaController.php
public function list(Request $request) {
    // ... update status logic ...

    // Invalidate dashboard cache
    Cache::forget('pagamento.dashboard');

    return $this->respondSuccess($parcelas);
}

public function marcarPaga(Request $request, $id) {
    // ... mark as paid logic ...

    // Invalidate cache
    Cache::forget('pagamento.dashboard');

    return $this->respondSuccess($parcela);
}
```

---

## 🎯 Cache Keys Naming Convention

```
Padrão: domain.type.scope

Exemplos:
- clientes.all              → All clients
- clientes.list             → Client list (paginado)
- clientes.{id}             → Single client
- produtos.all              → All products
- produtos.active           → Active products only
- tabelas_preco.all         → All price tables
- pagamento.dashboard       → Payment dashboard stats
- pagamento.parcelas.{rps}  → Payment details for RPS
- relatorio.{periodo}       → Report for period
```

---

## ⏱️ Cache Durations

```php
// Define padrões de TTL (Time To Live)

// Dados estáticos (mudam raramente)
const STATIC_CACHE = 24 * 60;  // 24 horas

// Dashboard e relatórios (mudam com frequência)
const DASHBOARD_CACHE = 15;     // 15 minutos

// Listas mutáveis
const LIST_CACHE = 60;          // 1 hora

// User-specific (nunca cache)
const USER_CACHE = 0;           // sem cache
```

---

## 🔍 Monitoring

### Check Redis Memory Usage

```bash
redis-cli info memory
```

### View Cached Keys

```bash
redis-cli KEYS "*"
redis-cli KEYS "clientes*"
redis-cli KEYS "pagamento*"
```

### Check Key TTL

```bash
redis-cli TTL clientes.all
# -1 = no expiration
# -2 = key doesn't exist
# > 0 = seconds remaining
```

### Clear Specific Cache

```bash
redis-cli DEL clientes.all
redis-cli FLUSHDB        # Clear all cache
redis-cli FLUSHALL       # Clear everything
```

---

## 🚨 Common Issues

### Issue: Connection Refused
```
Error: Redis::get(): Connection refused

Solution:
1. Verify Redis is running: redis-cli ping
2. Check HOST/PORT in .env
3. Check firewall settings
```

### Issue: Cache Not Working
```
Problem: Cache always null

Solution:
1. Check CACHE_DRIVER=redis in .env
2. Run: php artisan cache:clear
3. Check Redis memory: redis-cli info memory
```

### Issue: Out of Memory
```
Error: OOM command not allowed when used memory > 'maxmemory'

Solution:
redis-cli CONFIG SET maxmemory 256mb
redis-cli CONFIG SET maxmemory-policy allkeys-lru
```

---

## 🧪 Cache Testing

### PHP Testing

```php
// Test cache write
Cache::put('test_key', ['data' => 'value'], 60);

// Test cache read
$data = Cache::get('test_key');
assert($data['data'] === 'value');

// Test cache forget
Cache::forget('test_key');
assert(Cache::get('test_key') === null);
```

### CLI Testing

```bash
# Open Redis CLI
redis-cli

# Set value
> SET mykey "Hello"
OK

# Get value
> GET mykey
"Hello"

# Delete value
> DEL mykey
(integer) 1

# Exit
> EXIT
```

---

## 📊 Performance Gains

### Expected Improvements

```
Operation            Before Cache   After Cache   Improvement
List 1000 clientes   500ms          50ms          10x
Dashboard load       2000ms         200ms         10x
Search query         3000ms         100ms         30x
Product list         1000ms         50ms          20x
```

### Memory vs Speed Trade-off

```
Redis Configuration:
- maxmemory: 256MB (adjust per server)
- maxmemory-policy: allkeys-lru (remove old keys)
- Typical usage: ~100-200MB for full cache
```

---

## 🔒 Production Configuration

### Security

```php
// .env (production)
REDIS_PASSWORD=strong_password_here
REDIS_PORT=6380  # Use non-default port
```

### Redis Configuration

```bash
# /etc/redis/redis.conf
bind 127.0.0.1          # Only localhost
requirepass secret      # Set password
maxmemory 512mb         # Limit memory
maxmemory-policy allkeys-lru
appendonly yes          # Persistence
```

### Docker Production

```dockerfile
FROM redis:7-alpine

EXPOSE 6379

CMD ["redis-server", "--requirepass", "$REDIS_PASSWORD"]
```

---

## ✅ Validation Checklist

- [ ] Redis server running and accessible
- [ ] .env configured with CACHE_DRIVER=redis
- [ ] redis-cli ping returns PONG
- [ ] php artisan tinker Cache::get('test') works
- [ ] Client list uses Cache::remember()
- [ ] Product list uses Cache::remember()
- [ ] Dashboard uses Cache::remember()
- [ ] Cache invalidation works on create/update/delete
- [ ] Memory usage monitored
- [ ] TTL set appropriately for each cache key

---

## 📚 Resources

- [Laravel Cache Documentation](https://laravel.com/docs/cache)
- [Redis Documentation](https://redis.io/documentation)
- [Redis Commands](https://redis.io/commands)

---

**Status:** Ready for implementation
**Estimated Time:** 2-3 hours to fully implement all caching
