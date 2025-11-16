# 🔍 Guia de Debug - Filtros & Relatórios

## O Problema: "Carregando dados..." Infinito

Se você está vendo a aba "Filtros & Relatórios" mostrando "Carregando dados..." indefinidamente, siga este guia passo a passo.

---

## ✅ Passo 1: Abra o Console do Navegador

### Windows/Linux/Mac:
- Pressione **F12** ou **Ctrl+Shift+I** (Windows/Linux)
- Pressione **Cmd+Option+I** (Mac)

### No Chrome/Firefox/Edge:
1. Clique com botão direito na página
2. Selecione "Inspecionar" (Inspect)
3. Vá para a aba **Console**

---

## ✅ Passo 2: Procure por Mensagens de Erro

### Você verá logs como:

**✅ SE TUDO ESTÁ OK:**
```
Iniciando loadFilterOptions...
Response status: 200
Filter options loaded: {clientes: Array(5), consultores: Array(4), status: Array(8)}
Populado 5 clientes
Populado 4 consultores
Populado 8 status
Filter options populated successfully
```

**❌ SE HÁ ERRO:**
```
Iniciando loadFilterOptions...
Response status: 401
HTTP Error: 401
ERRO CRÍTICO ao carregar filtros: Não autorizado. Faça login novamente.
⚠️ Erro ao carregar filtros: Não autorizado. Faça login novamente.
```

---

## 🔧 Soluções por Tipo de Erro

### Erro 1: "Não autorizado. Faça login novamente." (401)
**Causa:** Sua sessão expirou ou cookies não foram enviados
**Solução:**
1. Faça logout clicando no seu usuário no menu
2. Clique em "Sair" (Logout)
3. Faça login novamente com:
   - **Email:** admin@example.com
   - **Senha:** 123
4. Volte para Dashboard Gerencial → Filtros & Relatórios

---

### Erro 2: "Acesso negado. Apenas administradores..." (403)
**Causa:** Seu usuário não é admin
**Solução:**
1. Verifique se está logado como **admin**
2. Se não for admin, peça ao administrador para dar acesso

---

### Erro 3: "API não encontrada" (404)
**Causa:** A rota da API não existe
**Solução:**
1. Verifique se as rotas foram adicionadas corretamente
2. Reinicie o servidor Laravel:
   ```bash
   # Parar servidor anterior (Ctrl+C)
   # Depois:
   php artisan serve --host=0.0.0.0 --port=8001
   ```

---

### Erro 4: "Erro no servidor. Verifique os logs." (500)
**Causa:** Erro no PHP/Laravel
**Solução:**
1. Abra outro terminal
2. Veja os logs do Laravel:
   ```bash
   tail -f storage/logs/laravel.log
   ```
3. Procure pela mensagem de erro vermelha
4. Copie o erro e me mostre

---

## 📊 Teste Manual da API (via Browser)

### Teste 1: Abra esta URL no navegador
```
http://localhost:8001/api/reports/filter-options
```

**Resultado esperado:** Uma página branca com JSON mostrando:
```json
{
  "clientes": [
    {"id": 1, "nome": "Cliente A"},
    ...
  ],
  "consultores": [...],
  "status": [...]
}
```

**Se receber erro 401:** Você não está autenticado
**Se receber JSON:** ✅ A API está funcionando

---

## 🔎 Teste de Filtros Passo a Passo

### 1. Página carrega?
```
Menu → Dashboard Gerencial → Aba "Filtros & Relatórios"
```
✅ Se a página carrega com a form de filtros → OK

---

### 2. Dropdowns preenchidos?
1. Clique em "Cliente"
2. Deve aparecer uma lista com clientes
3. Se aparecer vazio → Erro na API

---

### 3. Clique em "Aplicar Filtros"
1. Deixe todos os campos vazios
2. Clique no botão azul "Aplicar Filtros"
3. Deve aparecer:
   - ✅ Resumo com 4 números (Total, Faturado, Pendente, Ordens)
   - ✅ Tabela com todas as ordens
   - ✅ Botões de "Exportar em Excel" e "Exportar em PDF"

---

### 4. Clique em "Exportar em Excel"
1. Um arquivo deve ser baixado automaticamente
2. Se não baixar:
   - Verifique se bloqueador de pop-ups está ativo
   - Desative temporariamente e tente novamente

---

## 📋 Checklist de Testes

- [ ] Console do navegador não mostra erros em vermelho
- [ ] Dropdowns estão preenchidos (não vazios)
- [ ] Clique em "Aplicar Filtros" (vazio) mostra 47 ordens
- [ ] Resumo exibe números corretos
- [ ] Tabela mostra dados
- [ ] Botões de exportação aparecem
- [ ] Excel baixa corretamente
- [ ] PDF baixa corretamente
- [ ] Selecione um cliente → tabela atualiza
- [ ] Clique "Limpar Filtros" → form reseta

---

## 🚨 Se Nada Funcionar

### Passo 1: Verifique se o servidor está rodando
```bash
ps aux | grep "php artisan serve"
```
Se não aparecer, inicie com:
```bash
php artisan serve --host=0.0.0.0 --port=8001
```

---

### Passo 2: Verifique os logs do Laravel
```bash
tail -50 storage/logs/laravel.log
```

Procure por linhas vermelhas com `ERROR` ou `ERRO`.

---

### Passo 3: Teste a API via cURL
```bash
curl -s http://localhost:8001/api/reports/filter-options
```

Se retornar JSON → API está OK
Se retornar HTML com "Redirecting to /login" → Não autenticado

---

### Passo 4: Limpe o cache do Laravel
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

Depois reinicie o servidor.

---

## 💡 Dicas Úteis

1. **F5 força recarregar** - Se tiver problema, faça F5 para recarregar página
2. **Ctrl+Shift+Delete** - Limpa cache do navegador (se tiver problema persistente)
3. **Abra DevTools antes de clicar** - Assim você vê os logs enquanto acontecem
4. **Network tab** - Vá para aba Network e veja requisições HTTP reais

---

## 📞 Se Precisar de Ajuda

Copie estas informações e envie para o suporte:

1. **Mensagem do console (F12 → Console)**
2. **URL onde a erro acontece**
3. **Output do comando:**
   ```bash
   tail -20 storage/logs/laravel.log
   ```
4. **Seu papel no sistema** (admin? consultor?)

---

## ✨ Status

**Data:** 16 de Novembro de 2025
**Versão:** 2.0 (Com melhor tratamento de erros)

