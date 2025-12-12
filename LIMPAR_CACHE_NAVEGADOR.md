# Como Limpar o Cache do Navegador

O erro 403 ao contestar uma OS está acontecendo porque o navegador está usando a versão antiga do arquivo JavaScript (sem o token CSRF).

## ✅ Solução Rápida: Hard Refresh

### Windows/Linux
Pressione **Ctrl + Shift + R** ou **Ctrl + F5**

### Mac
Pressione **Cmd + Shift + R**

---

## 🔧 Solução Alternativa: Limpar Cache Manualmente

### Google Chrome / Microsoft Edge
1. Pressione **F12** para abrir DevTools
2. Clique com botão direito no ícone de **Recarregar** (ao lado da barra de endereço)
3. Selecione **"Esvaziar cache e atualização forçada"** ou **"Hard Reload"**

### Firefox
1. Pressione **Ctrl + Shift + Delete**
2. Selecione **"Cache"**
3. Clique em **"Limpar agora"**
4. Recarregue a página com **F5**

---

## 🧪 Como Verificar se Funcionou

1. Após limpar o cache, pressione **F12** para abrir o Console
2. Vá para a aba **Network** (Rede)
3. Marque **"Disable cache"** (Desabilitar cache)
4. Recarregue a página (**F5**)
5. Tente contestar uma OS novamente

**No console, você NÃO deve mais ver:**
```
POST http://localhost:8001/contestar-ordem-servico 403 (Forbidden)
```

**Se funcionar, você verá:**
```
POST http://localhost:8001/contestar-ordem-servico 200 (OK)
```

---

## 🔍 Verificar se o Token CSRF Está Sendo Enviado

Na aba **Network** (Rede) do DevTools:

1. Clique em contestar uma OS
2. Procure pela requisição `/contestar-ordem-servico`
3. Clique nela
4. Vá para a aba **Headers** (Cabeçalhos)
5. Em **Request Headers**, procure por:
   ```
   X-CSRF-TOKEN: [um token longo aqui]
   ```

Se o `X-CSRF-TOKEN` aparecer, o código está correto e funcionando! ✅

Se NÃO aparecer, o navegador ainda está usando o cache antigo. Tente:
1. Fechar completamente o navegador
2. Reabrir
3. Acessar novamente

---

## 💡 Dica: Desabilitar Cache Durante Desenvolvimento

Para evitar esse problema no futuro durante o desenvolvimento:

1. Abra DevTools (**F12**)
2. Vá para **Settings** (⚙️ no canto superior direito)
3. Em **Network**, marque:
   - ✅ **"Disable cache (while DevTools is open)"**

Agora, enquanto o DevTools estiver aberto, o cache sempre será desabilitado automaticamente.

---

## 📋 Checklist de Resolução

- [ ] Hard Refresh com Ctrl+Shift+R
- [ ] Verificar token CSRF no Network tab
- [ ] Contestar uma OS para testar
- [ ] Confirmar que não há mais erro 403
- [ ] Marcar "Disable cache" no DevTools para desenvolvimento

---

**Depois de limpar o cache, a contestação de OS deve funcionar perfeitamente!** 🎉
