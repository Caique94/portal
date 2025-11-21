# Guia Rápido - Aplicando a Solução

## ⚡ 3 Mudanças Simples para Resolver o Erro

Se você quer apenas aplicar a solução rapidinho sem ler toda a documentação, siga estes 3 passos:

---

## 1️⃣ Editar `public/js/app.js`

**Localize esta linha:**
```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

**Substitua por:**
```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'  // ← ADICIONE ESTA LINHA
    },
    error: function(xhr, status, error) {
        if (xhr.status === 401) {
            console.error('Erro 401: Sessão expirada ou não autenticado');
        } else if (xhr.status === 403) {
            console.error('Erro 403: Acesso negado');
        }
    }
});
```

✅ **Feito!** Agora todas as requisições AJAX enviarão o header correto.

---

## 2️⃣ Editar Cada DataTable (ex: `public/js/cadastros/usuarios.js`)

**Localize este código:**
```javascript
const tblUsuarios = $tbl.DataTable({
  ajax: {
    url: '/listar-usuarios',
    type: 'GET',
    dataSrc: 'data' // { data: [...] }
  },
```

**Substitua por:**
```javascript
const tblUsuarios = $tbl.DataTable({
  ajax: {
    url: '/listar-usuarios',
    type: 'GET',
    dataSrc: 'data',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    error: function(xhr, status, error) {
      console.error('DataTables AJAX Error:', {
        status: xhr.status,
        statusText: xhr.statusText,
        responseText: xhr.responseText.substring(0, 200),
        error: error
      });

      let errorMsg = 'Erro ao carregar dados';
      if (xhr.status === 401) {
        errorMsg = 'Sessão expirada. Faça login novamente.';
      } else if (xhr.status === 403) {
        errorMsg = 'Você não tem permissão para acessar este recurso';
      } else if (xhr.status === 404) {
        errorMsg = 'Rota não encontrada';
      } else if (xhr.status === 500) {
        errorMsg = 'Erro no servidor';
      }

      Toast.fire({
        icon: 'error',
        title: errorMsg
      });
    }
  },
```

✅ **Feito!** Agora o DataTables mostrará mensagens claras de erro.

**Repita este passo para TODOS os arquivos DataTables:**
- `public/js/cadastros/clientes.js`
- `public/js/cadastros/produtos.js`
- `public/js/cadastros/fornecedores.js`
- `public/js/cadastros/tabela-precos.js`
- `public/js/cadastros/condicoes-pagamento.js`
- Etc.

---

## 3️⃣ Editar `app/Exceptions/Handler.php`

**Localize esta função:**
```php
public function render(Request $request, Throwable $exception): Response
{
    // Se é uma requisição AJAX/API, retorna JSON
    if ($request->expectsJson()) {
        return $this->handleJsonException($request, $exception);
    }

    // Caso contrário, usa o comportamento padrão
    return parent::render($request, $exception);
}
```

**Substitua por:**
```php
public function render(Request $request, Throwable $exception): Response
{
    // Se é uma requisição AJAX/API, retorna JSON
    if ($request->expectsJson()) {
        return $this->handleJsonException($request, $exception);
    }

    // Se é uma requisição para uma rota de API interna (começa com /api ou /listar-)
    // Também retorna JSON para evitar erro de parsing no DataTables
    if ($request->is('api/*') || $request->is('listar-*') || $request->is('salvar-*') ||
        $request->is('toggle-*') || $request->is('excluir-*') || $request->is('remover-*')) {
        return $this->handleJsonException($request, $exception);
    }

    // Caso contrário, usa o comportamento padrão
    return parent::render($request, $exception);
}
```

✅ **Feito!** Agora erros 401 retornarão JSON em vez de HTML.

---

## ✅ Pronto!

Você acabou de implementar a solução completa em 3 passos.

### Para Testar:

1. Abra o navegador em `http://localhost:8001/cadastros/usuarios`
2. Abra F12 (DevTools)
3. Vá para **Network**
4. Procure pela requisição `/listar-usuarios`
5. Verifique que:
   - Status é 200 (ou 401 se não autenticado)
   - Response é JSON (não HTML)
   - Headers incluem `Accept: application/json`

---

## 🔍 Se Ainda Tiver Problemas

### Erro ainda aparece?

1. **Limpe o cache do navegador**
   - F12 → Settings ⚙️
   - Marque "Disable cache (while DevTools is open)"
   - Recarregue a página (F5)

2. **Verifique o Console (F12)**
   - Procure por mensagens vermelhas
   - Copie o erro completo

3. **Verifique a aba Network (F12)**
   - Clique na requisição `/listar-usuarios`
   - Vá para **Response**
   - Se for HTML: Handler.php não está sendo aplicado
   - Se for JSON: Tudo está certo

### Se a resposta for HTML:

Verifique se:
- [ ] Você editou `app/Exceptions/Handler.php` corretamente
- [ ] A rota segue o padrão `/listar-*`, `/salvar-*`, etc
- [ ] Você salvou o arquivo

---

## 📊 Antes vs Depois - Visual

### ANTES (Erro):
```
Browser envia requisição SEM Accept: application/json
    ↓
Laravel retorna erro 401 em HTML
    ↓
DataTables recebe: <!DOCTYPE html>...[página de login]...</html>
    ↓
JavaScript tenta parse: JSON.parse() → ERRO!
    ↓
Console mostra: "SyntaxError: Unexpected token '<'"
    ↓
Usuário vê: "DataTables warning: Ajax error"
```

### DEPOIS (Corrigido):
```
Browser envia requisição COM Accept: application/json
    ↓
Laravel retorna erro 401 em JSON
    ↓
DataTables recebe: {"success": false, "message": "Não autenticado", "code": 401}
    ↓
JavaScript faz parse: JSON.parse() → OK!
    ↓
Error callback é acionado
    ↓
Usuário vê: Toast com "Sessão expirada. Faça login novamente."
```

---

## 📚 Para Entender Mais

Se quiser entender melhor o que está acontecendo, leia:

1. **RESUMO_SOLUCAO_DATATABLES.md** - Visão geral completa
2. **FIX_DATATABLES_401_ERROR.md** - Análise detalhada
3. **EXEMPLO_DATATABLES_CORRETO.md** - Exemplos de código completo

---

## 🎯 Checklist de Aplicação

- [ ] Editei `public/js/app.js`
- [ ] Adicionei headers ao DataTables em `usuarios.js`
- [ ] Adicionei headers aos outros DataTables (clientes, produtos, etc)
- [ ] Editei `app/Exceptions/Handler.php`
- [ ] Testei com F12 Network tab
- [ ] Não vejo mais "SyntaxError: Unexpected token '<'"
- [ ] DataTables mostra mensagem clara em caso de erro

---

## 💡 Dica Final

Copie este padrão para TODOS os seus DataTables:

```javascript
const tblNome = $('#tableId').DataTable({
  ajax: {
    url: '/listar-dados',
    type: 'GET',
    dataSrc: 'data',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    error: function(xhr) {
      console.error('Erro:', xhr.status);
      let msg = xhr.status === 401 ? 'Sessão expirada' : 'Erro ao carregar dados';
      Toast.fire({ icon: 'error', title: msg });
    }
  },
  // ... resto das configurações
});
```

---

## ✨ Resultado Esperado

Depois de aplicar estas 3 mudanças:

✓ DataTables carrega dados sem erros
✓ Erros 401 mostram mensagem clara
✓ Sem aviso "DataTables warning: Ajax error"
✓ Sem "SyntaxError: Unexpected token '<'"
✓ Console mostra informações úteis para debugging

**Tempo estimado: 10-15 minutos**
