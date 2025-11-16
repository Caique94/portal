# API Documentation - Portal Personalitec

## 🌐 Base URL
```
http://localhost:8000/api
```

---

## 👥 Cliente Endpoints

### List Clientes
**GET** `/clientes`

Retrieves all clientes with relationships (tabela_preco, contatos).

**Query Parameters:**
- None required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Clientes listados com sucesso",
  "data": [
    {
      "id": 1,
      "nome": "Empresa A",
      "email": "contato@empresaa.com",
      "codigo": "CLI001",
      "tabela_preco_id": 1,
      "ativo": true,
      "tabelaPreco": {
        "id": 1,
        "nome": "Tabela Padrão",
        "codigo": "TAB001"
      },
      "contatos": [
        {
          "id": 1,
          "nome": "João Silva",
          "email": "joao@empresaa.com",
          "telefone": "11999999999"
        }
      ]
    }
  ]
}
```

**Performance:**
- Query count: 2-3 (with eager loading)
- Response time: 100-150ms (fresh), 10ms (cached)
- Cached for 24 hours

---

### Create Cliente
**POST** `/clientes`

Creates a new cliente.

**Request Body:**
```json
{
  "nome": "Nova Empresa",
  "email": "contato@nova.com",
  "telefone": "11988888888",
  "codigo": "CLI002",
  "tabela_preco_id": 1,
  "ativo": true
}
```

**Validation Rules:**
- `nome`: required|string|max:255
- `email`: required|email|unique:clientes
- `codigo`: required|string|max:20|unique:clientes
- `tabela_preco_id`: required|exists:tabelas_preco,id
- `ativo`: boolean

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Cliente criado com sucesso",
  "data": {
    "id": 2,
    "nome": "Nova Empresa",
    "email": "contato@nova.com",
    "codigo": "CLI002",
    "tabela_preco_id": 1,
    "ativo": true
  }
}
```

**Error (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Erro na validação",
  "errors": {
    "email": ["The email field is required."],
    "tabela_preco_id": ["The tabela_preco_id field is required."]
  }
}
```

**Side Effects:**
- Invalidates `clientes.all` cache

---

### Show Cliente
**GET** `/clientes/{id}`

Retrieves a specific cliente with all relationships.

**Path Parameters:**
- `id`: integer (required)

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Cliente recuperado com sucesso",
  "data": {
    "id": 1,
    "nome": "Empresa A",
    "email": "contato@empresaa.com",
    "codigo": "CLI001",
    "tabela_preco_id": 1,
    "ativo": true,
    "tabelaPreco": { /* ... */ },
    "contatos": [ /* ... */ ],
    "ordensServico": [ /* ... */ ]
  }
}
```

**Performance:**
- Query count: 3 (eager loading)
- Response time: 50ms

---

### Update Cliente
**PATCH** `/clientes/{id}`

Updates a cliente's information.

**Request Body (all fields optional):**
```json
{
  "nome": "Empresa Atualizada",
  "email": "novo@email.com",
  "tabela_preco_id": 2,
  "ativo": false
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Cliente atualizado com sucesso",
  "data": { /* updated cliente */ }
}
```

**Side Effects:**
- Invalidates `clientes.all` cache

---

### Delete Cliente
**DELETE** `/clientes/{id}`

Deletes a cliente.

**Response (204 No Content):**
```
(empty body)
```

**Side Effects:**
- Invalidates `clientes.all` cache
- Cascades delete related records (if configured)

---

### Paginated Clientes
**GET** `/clientes/paginated`

Retrieves paginated list of clientes.

**Query Parameters:**
- `page`: integer (default: 1)
- `per_page`: integer (default: 15)

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Clientes paginados recuperados com sucesso",
  "data": [
    { /* cliente 1 */ },
    { /* cliente 2 */ }
  ],
  "links": {
    "first": "http://localhost:8000/api/clientes/paginated?page=1",
    "last": "http://localhost:8000/api/clientes/paginated?page=2",
    "next": "http://localhost:8000/api/clientes/paginated?page=2",
    "prev": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 2,
    "per_page": 15,
    "to": 15,
    "total": 25
  }
}
```

---

## 📦 Produto Endpoints

### List Produtos
**GET** `/produtos`

Retrieves all produtos.

**Query Parameters:**
- `q`: string (optional) - Search by código or nome

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Produtos listados com sucesso",
  "data": [
    {
      "id": 1,
      "codigo": "PROD001",
      "nome": "Produto A",
      "descricao": "Descrição do produto",
      "ativo": true
    }
  ]
}
```

**Performance:**
- Without search: Cached for 24 hours
- With search: No cache (real-time results)
- Query count: 1-2
- Response time: 50-100ms (fresh), 5ms (cached)

---

### Create Produto
**POST** `/produtos`

Creates a new produto.

**Request Body:**
```json
{
  "codigo": "PROD002",
  "nome": "Novo Produto",
  "descricao": "Descrição",
  "ativo": true
}
```

**Validation Rules:**
- `codigo`: required|string|max:20|unique:produtos
- `nome`: required|string|max:255
- `descricao`: nullable|string
- `ativo`: boolean

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Produto criado com sucesso",
  "data": { /* novo produto */ }
}
```

**Side Effects:**
- Invalidates `produtos.all` and `produtos.active` caches

---

### Active Produtos List
**GET** `/produtos/active`

Retrieves only active produtos.

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Produtos ativos listados com sucesso",
  "data": [ /* apenas produtos ativo=true */ ]
}
```

**Performance:**
- Cached for 24 hours
- Response time: 5-10ms (cached)

---

### Toggle Produto Status
**PATCH** `/produtos/{id}/toggle`

Toggles produto's ativo status.

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Status do produto alterado com sucesso",
  "data": {
    "id": 1,
    "codigo": "PROD001",
    "nome": "Produto A",
    "ativo": false
  }
}
```

**Side Effects:**
- Invalidates `produtos.all` and `produtos.active` caches

---

## 💳 Parcela de Pagamento Endpoints

### List Parcelas
**GET** `/parcelas`

Retrieves all payment installments.

**Query Parameters:**
- `recibo_provisorio_id`: integer (optional) - Filter by RPS
- `status`: string (optional) - Filter: 'pendente|paga|atrasada'

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Parcelas listadas com sucesso",
  "data": [
    {
      "id": 1,
      "recibo_provisorio_id": 1,
      "numero_parcela": 1,
      "total_parcelas": 3,
      "valor": 100.50,
      "data_vencimento": "2024-11-20",
      "data_pagamento": null,
      "status": "pendente",
      "observacao": null
    }
  ]
}
```

**Performance Notes:**
- Batch update applied for overdue parcelas
- Query count: 2-3 (with eager loading)
- Response time: 100-150ms

---

### Create Parcelas
**POST** `/parcelas`

Creates multiple payment installments for an RPS.

**Request Body:**
```json
{
  "recibo_provisorio_id": 1,
  "total_parcelas": 3,
  "valor_total": 300.00,
  "data_primeira_parcela": "2024-11-20",
  "intervalo_dias": 30
}
```

**Validation Rules:**
- `recibo_provisorio_id`: required|exists:recibo_provisorio,id
- `total_parcelas`: required|integer|min:1|max:12
- `valor_total`: required|numeric|min:0
- `data_primeira_parcela`: required|date
- `intervalo_dias`: required|integer|min:1

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Parcelas criadas com sucesso",
  "data": [
    { "numero_parcela": 1, "valor": 100.00, "data_vencimento": "2024-11-20", "status": "pendente" },
    { "numero_parcela": 2, "valor": 100.00, "data_vencimento": "2024-12-20", "status": "pendente" },
    { "numero_parcela": 3, "valor": 100.00, "data_vencimento": "2025-01-20", "status": "pendente" }
  ]
}
```

**Calculation Logic:**
- Each installment value = `valor_total / total_parcelas`
- Due date increment: `intervalo_dias` days from previous

---

### Mark Parcela as Paid
**PATCH** `/parcelas/{id}/marcar-paga`

Marks a payment installment as paid.

**Request Body:**
```json
{
  "data_pagamento": "2024-11-15",
  "observacao": "Pagamento realizado via transferência"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Parcela marcada como paga",
  "data": {
    "id": 1,
    "status": "paga",
    "data_pagamento": "2024-11-15",
    "observacao": "Pagamento realizado via transferência"
  }
}
```

**Side Effects:**
- Invalidates `pagamento.dashboard` cache

---

### Payment Dashboard
**GET** `/parcelas/dashboard`

Retrieves payment statistics and metrics.

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Dashboard de parcelas carregado",
  "data": {
    "total_pendentes": 45,
    "total_atrasadas": 12,
    "total_pagas": 234,
    "valor_pendente": 4500.50,
    "valor_atrasado": 1200.00,
    "valor_pago": 23400.00,
    "vencendo_mes": 8
  }
}
```

**Performance:**
- Cached for 15 minutes
- Query count: 1 (highly optimized aggregation)
- Response time: 200ms (fresh), 10ms (cached)

---

## 📊 Common Response Formats

### Success Response (2xx)
```json
{
  "success": true,
  "message": "Operação realizada com sucesso",
  "data": { /* response data */ }
}
```

### Error Response (4xx/5xx)
```json
{
  "success": false,
  "message": "Descrição do erro",
  "errors": { /* validation errors if applicable */ }
}
```

### Validation Error (422)
```json
{
  "success": false,
  "message": "Erro de validação",
  "errors": {
    "campo": ["Mensagem de erro"],
    "outro_campo": ["Mensagem 1", "Mensagem 2"]
  }
}
```

---

## 🔍 Error Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 204 | No Content | Request successful, no content returned |
| 400 | Bad Request | Invalid request format |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Internal Server Error | Server error |

---

## ⚡ Performance Metrics

### Query Counts
| Endpoint | Queries | Time (Fresh) | Time (Cached) |
|----------|---------|--------------|---------------|
| GET /clientes | 2-3 | 150ms | 10ms |
| POST /clientes | 1 | 50ms | - |
| GET /produtos | 1 | 100ms | 5ms |
| GET /parcelas | 2-3 | 120ms | - |
| GET /parcelas/dashboard | 1 | 200ms | 10ms |

### Cache Configuration
| Endpoint | TTL | Cache Key |
|----------|-----|-----------|
| GET /clientes | 24h | `clientes.all` |
| GET /produtos | 24h | `produtos.all` |
| GET /produtos/active | 24h | `produtos.active` |
| GET /parcelas/dashboard | 15min | `pagamento.dashboard` |

---

## 🔐 Authentication

Currently, the API does not require authentication for demonstration purposes.

**Note:** In production, implement:
- API token authentication
- Rate limiting
- CORS configuration
- Request logging

---

## 📝 Example Requests

### cURL

**Get all clientes:**
```bash
curl -X GET http://localhost:8000/api/clientes \
  -H "Content-Type: application/json"
```

**Create a cliente:**
```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Nova Empresa",
    "email": "contato@nova.com",
    "codigo": "CLI002",
    "tabela_preco_id": 1,
    "ativo": true
  }'
```

### JavaScript/Fetch

**Get clientes:**
```javascript
fetch('http://localhost:8000/api/clientes')
  .then(response => response.json())
  .then(data => console.log(data.data))
  .catch(error => console.error('Error:', error));
```

**Create cliente:**
```javascript
fetch('http://localhost:8000/api/clientes', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    nome: 'Nova Empresa',
    email: 'contato@nova.com',
    codigo: 'CLI002',
    tabela_preco_id: 1,
    ativo: true
  })
})
  .then(response => response.json())
  .then(data => console.log(data.data))
  .catch(error => console.error('Error:', error));
```

---

## 📚 Related Documentation

- [FASE3_QUICK_REFERENCE.md](FASE3_QUICK_REFERENCE.md) - Quick facts
- [QUERY_LOGGING_GUIDE.md](QUERY_LOGGING_GUIDE.md) - Performance monitoring
- [EAGER_LOADING_GUIDE.md](EAGER_LOADING_GUIDE.md) - Query optimization

---

**API Version:** 1.0
**Last Updated:** 2024-11-13
**Status:** ✅ Complete

