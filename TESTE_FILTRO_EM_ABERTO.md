# Como Testar o Filtro "Em Aberto" (Status 0)

## 📋 Preparação

1. **Limpe os logs antigos:**
```bash
# Windows
del storage\logs\laravel.log

# Ou se preferir apenas truncar
echo. > storage\logs\laravel.log
```

2. **Limpe o cache do Laravel:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 🧪 Teste Passo a Passo

### Passo 1: Acesse como Admin
1. Faça login como **admin**
2. Acesse a página de Ordem de Serviço
3. Abra o Console do navegador (F12)

### Passo 2: Teste o Filtro "Em Aberto"
1. No filtro de Status, selecione **"Em Aberto"**
2. Clique em **"Aplicar Filtros"**
3. Observe o console do navegador

**O que você deve ver no console:**
```
Filtros aplicados: ?status=0
```

### Passo 3: Verifique os Logs do Laravel

Abra o arquivo `storage/logs/laravel.log` e procure pelas últimas entradas. Você deve ver algo assim:

```
[2025-12-11 ...] local.INFO: === INÍCIO list() ===
{
  "papel": "admin",
  "user_id": 1,
  "query_string": "status=0",
  "all_input": {"status": "0"}
}

[2025-12-11 ...] local.INFO: Filtros capturados (antes de converter)
{
  "filtroStatus": "0",
  "filtroStatus_tipo": "string",
  "request_has_status": true
}

[2025-12-11 ...] local.INFO: Após conversão
{
  "filtroStatusFornecido": true,
  "filtroStatus": 0,
  "filtroStatus_tipo": "integer"
}

[2025-12-11 ...] local.INFO: Filtro de status aplicado
{
  "status": 0,
  "tipo": "integer",
  "valor_original": "0",
  "papel": "admin"
}

[2025-12-11 ...] local.INFO: Query de listagem OS
{
  "sql": "select ordem_servico.*, cliente.codigo as cliente_codigo, ... where ordem_servico.status = ? ...",
  "bindings": [0],
  "filtros": {
    "status": 0,
    "consultor": null,
    "cliente": null,
    "mes": null,
    "ano": null
  }
}

[2025-12-11 ...] local.INFO: Resultados encontrados
{
  "total": X,
  "papel": "admin"
}
```

## ✅ O Que Verificar

### No Console do Navegador:
- ✅ Query string deve ser `?status=0`
- ✅ Não deve haver erros JavaScript

### Nos Logs do Laravel:
- ✅ `filtroStatus` deve ser `"0"` (string)
- ✅ `request_has_status` deve ser `true`
- ✅ `filtroStatusFornecido` deve ser `true`
- ✅ Após conversão, `filtroStatus` deve ser `0` (integer)
- ✅ Deve aparecer "Filtro de status aplicado"
- ✅ Na query SQL, deve ter `where ordem_servico.status = ?` com binding `[0]`

### Na Tabela:
- ✅ Deve mostrar apenas OS com status 0 (Em Aberto)
- ✅ Se mostrar "Nenhum registro encontrado", verifique se existem OS com status 0 no banco

## 🔍 Se Não Funcionar

### Problema: Logs não aparecem
**Solução:** Verifique se o arquivo `.env` tem:
```
LOG_CHANNEL=single
LOG_LEVEL=info
```

### Problema: `filtroStatusFornecido` é `false`
**Possível causa:** O JavaScript não está enviando o parâmetro `status`

**Verifique:**
1. Abra o DevTools do navegador
2. Vá em Network (Rede)
3. Filtre por XHR
4. Clique em "Aplicar Filtros"
5. Procure pela requisição para `/listar-ordens-servico`
6. Verifique se a URL tem `?status=0`

### Problema: Não existem registros com status 0
**Execute esta query no banco:**
```sql
SELECT COUNT(*) FROM ordem_servico WHERE status = 0;
```

Se retornar 0, significa que não há OS com status "Em Aberto" no banco.

**Para criar uma OS de teste:**
1. Clique em "Nova OS" na interface
2. Preencha os campos
3. Salve (status padrão deve ser 0)

## 📊 Query SQL para Diagnóstico

Execute no PostgreSQL:

```sql
-- Ver distribuição de status
SELECT
    status,
    COUNT(*) as total,
    CASE
        WHEN status = 0 THEN 'Em Aberto'
        WHEN status = 1 THEN 'Aguardando Aprovação'
        WHEN status = 3 THEN 'Contestada'
        WHEN status = 4 THEN 'Aguardando Faturamento'
        WHEN status = 5 THEN 'Faturada'
        WHEN status = 6 THEN 'Aguardando RPS'
        WHEN status = 7 THEN 'RPS Emitida'
        ELSE 'Desconhecido: ' || status
    END as nome_status
FROM ordem_servico
GROUP BY status
ORDER BY status;

-- Ver últimas OS criadas
SELECT id, status, created_at
FROM ordem_servico
ORDER BY created_at DESC
LIMIT 10;
```

## 📞 Próximos Passos

Após fazer o teste:

1. **Se funcionar:**
   - Pode remover os logs extras se quiser
   - Teste os outros status também

2. **Se não funcionar:**
   - Copie os logs do Laravel e me envie
   - Me diga o que apareceu no console do navegador
   - Me diga quantos registros tem com status 0 no banco
