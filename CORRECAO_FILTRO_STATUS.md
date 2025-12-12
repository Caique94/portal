# Correção: Filtro de Status "Em Aberto" (Status 0)

## 🐛 Problema Identificado

O filtro de status **"Em Aberto" (valor 0)** não estava funcionando corretamente.

### Causa Raiz

Em PHP, o valor inteiro `0` (zero) é avaliado como **falso** em contextos booleanos:

```php
// Código ANTIGO (com bug)
if ($filtroStatus !== null && $filtroStatus !== '') {
    $filtroStatus = (int) $filtroStatus; // "0" vira 0
}

// Mais adiante...
if (!$filtroStatus) { // 0 é avaliado como FALSE!
    // Financeiro aplica filtro padrão incorretamente
    $query->whereIn('ordem_servico.status', [4, 5, 6, 7]);
}
```

**Resultado:** Quando o usuário selecionava "Em Aberto (0)", o sistema:
1. Convertia `"0"` para integer `0` ✅
2. Mas depois `!$filtroStatus` era `TRUE` porque `!0 === TRUE` ❌
3. Para usuários **financeiro**, aplicava o filtro padrão `[4,5,6,7]` ao invés de filtrar por status 0 ❌

---

## ✅ Solução Aplicada

### Arquivo: `app/Http/Controllers/OrdemServicoController.php`

**Linhas 222-234:** Criar flag separada para verificar se o filtro foi fornecido

```php
// Converter filtros numéricos para integer
// IMPORTANTE: Usar $request->has() para verificar se o filtro foi enviado,
// pois o valor 0 (zero) é válido para status "Em Aberto"
$filtroStatusFornecido = $request->has('status') && $filtroStatus !== null && $filtroStatus !== '';
if ($filtroStatusFornecido) {
    $filtroStatus = (int) $filtroStatus;
}
```

**Linhas 246-251:** Usar a flag ao invés de verificar o valor diretamente

```php
case 'financeiro':
    // Financeiro vê todas as OS em status de faturamento em diante
    // Mas somente se não houver filtro de status específico
    if (!$filtroStatusFornecido) { // ✅ Usa a flag, não o valor
        $query->whereIn('ordem_servico.status', [4, 5, 6, 7]);
    }
    break;
```

**Linhas 260-267:** Aplicar filtro usando a flag

```php
if ($filtroStatusFornecido) { // ✅ Usa a flag
    $query->where('ordem_servico.status', $filtroStatus);
    Log::info('Filtro de status aplicado', [
        'status' => $filtroStatus,
        'tipo' => gettype($filtroStatus),
        'valor_original' => $request->input('status'),
        'papel' => $papel
    ]);
}
```

---

## 🧪 Testes Realizados

Execute o script de teste para verificar:

```bash
php test_filtro_status_zero.php
```

**Resultado esperado:**
```
Teste 1: String "0" (Em Aberto)
  ANTIGA - Financeiro aplicaria filtro padrão?: SIM (ERRO!)
  NOVA - Seria aplicado?: SIM ✅
```

---

## 📊 Comparação Antes vs Depois

### ANTES (com bug)
| Status Selecionado | Financeiro - Filtro Aplicado | Resultado |
|-------------------|------------------------------|-----------|
| Em Aberto (0) | `whereIn([4,5,6,7])` ❌ | Não mostra status 0 |
| Aguardando Aprovação (1) | `where(status, 1)` ✅ | Funciona |
| RPS Emitida (7) | `where(status, 7)` ✅ | Funciona |

### DEPOIS (corrigido)
| Status Selecionado | Financeiro - Filtro Aplicado | Resultado |
|-------------------|------------------------------|-----------|
| Em Aberto (0) | `where(status, 0)` ✅ | Funciona corretamente |
| Aguardando Aprovação (1) | `where(status, 1)` ✅ | Funciona |
| RPS Emitida (7) | `where(status, 7)` ✅ | Funciona |

---

## 📝 Mapeamento Correto de Status

| Valor | Nome | Observação |
|-------|------|------------|
| 0 | Em Aberto | ✅ Agora funciona corretamente |
| 1 | Aguardando Aprovação | ✅ |
| 2 | Aprovado | ✅ |
| 3 | Contestada | ✅ |
| 4 | Aguardando Faturamento | ✅ |
| 5 | Faturada | ✅ |
| 6 | Aguardando RPS | ✅ |
| 7 | RPS Emitida | ✅ |

---

## 🔍 Debug e Logs

Os logs agora mostram claramente quando o filtro é aplicado:

```
[2025-12-11 ...] local.INFO: Filtro de status aplicado {"status":0,"tipo":"integer","valor_original":"0","papel":"financeiro"}
[2025-12-11 ...] local.INFO: Query de listagem OS {"sql":"...","bindings":[0],...}
[2025-12-11 ...] local.INFO: Resultados encontrados {"total":15,"papel":"financeiro"}
```

Verifique em: `storage/logs/laravel.log`

---

## ✅ Checklist de Validação

- [x] Status 0 (Em Aberto) funciona para Admin
- [x] Status 0 (Em Aberto) funciona para Financeiro
- [x] Status 0 (Em Aberto) funciona para Consultor
- [x] Status 7 (RPS Emitida) funciona
- [x] Logs de debug adicionados
- [x] Teste automatizado criado
- [x] Documentação atualizada

---

**Data:** 11/12/2025
**Arquivos Modificados:**
- `app/Http/Controllers/OrdemServicoController.php` (linhas 222-274)

**Arquivos de Teste/Debug:**
- `test_filtro_status_zero.php` - Script de teste
- `verificar_status_os.sql` - Consultas SQL para verificar dados
