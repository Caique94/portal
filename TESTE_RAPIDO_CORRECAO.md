# 🚀 TESTE RÁPIDO - Correção PostgreSQL SQLSTATE[22P02]

## ✅ O que foi FINALMENTE CORRIGIDO

O método `store()` agora **chama corretamente** os métodos de validação e salva:
1. ✅ **Pessoa Jurídica** (se fornecida)
2. ✅ **Dados de Pagamento** (se fornecidos)

---

## 🧪 Como Testar Agora

### 1. Limpar Cache (OBRIGATÓRIO)
```bash
php artisan cache:clear
php artisan config:clear
```

### 2. Acessar o Sistema
```
URL: http://localhost:8001/login
Email: admin@example.com
Senha: 123 (ou a senha configurada)
```

### 3. Testar Cadastro de Usuário COM Pessoa Jurídica

**Passo 1:** Ir para USUÁRIOS > Adicionar Usuário

**Passo 2:** Preencher a aba "Dados Pessoais"
```
Nome: "Test User"
Email: "testuser@example.com"
Data Nasc: "1990-01-01"
Papel: "consultor"
```

**Passo 3:** Preencher a aba "Pessoa Jurídica" (IMPORTANTE!)
```
CNPJ: "65.465.465/4564"  ← COM MÁSCARA!
Razão Social: "TEST LTDA"
Nome Fantasia: "Test"
```

**Passo 4:** Preencher a aba "Dados de Pagamento" (OPCIONAL)
```
Titular Conta: "Test User"
CPF/CNPJ Titular: "123.456.789-09"
Banco: "0001"
Agência: "0001"
Conta: "123456-7"
Tipo Conta: "corrente"
```

**Passo 5:** Clicar em "Salvar"

---

## ✅ Esperado (Se Funcionar Corretamente)

### ✅ No Navegador (Console F12)
```javascript
Dados sanitizados prontos para envio: {
  txtUsuarioNome: "Test User",
  txtUsuarioEmail: "testuser@example.com",
  txtPJCNPJ: "654654654564",        // ✅ SEM MÁSCARA!
  txtPJRazaoSocial: "TEST LTDA",
  txtPagTitularConta: "Test User",
  txtPagCpfCnpjTitular: "12345678909",  // ✅ SEM MÁSCARA!
  id: null,                              // ✅ NEW USER
  ...
}
```

### ✅ Toast (Notificação)
```
✅ Verde
"Usuário criado com sucesso!"
Modal fecha
Tabela atualiza
```

### ✅ No Banco de Dados

**Tabela: users**
```sql
SELECT * FROM users WHERE email = 'testuser@example.com';

id  | name      | email                 | papel
----|-----------|------------------------|----------
1   | Test User | testuser@example.com   | consultor
```

**Tabela: pessoa_juridica_usuario**
```sql
SELECT * FROM pessoa_juridica_usuario WHERE user_id = 1;

id | user_id | cnpj          | razao_social
---|---------|---------------|---------------
1  | 1       | 654654654564  | TEST LTDA
```

**Tabela: pagamento_usuario**
```sql
SELECT * FROM pagamento_usuario WHERE user_id = 1;

id | user_id | titular_conta | cpf_cnpj_titular
---|---------|---------------|------------------
1  | 1       | Test User     | 12345678909
```

### ✅ Logs (storage/logs/laravel.log)
```
[2025-11-21 15:30:00] local.INFO: UserController::store iniciado
[2025-11-21 15:30:01] local.INFO: Pessoa Jurídica validada
[2025-11-21 15:30:02] local.INFO: Pessoa Jurídica salva
[2025-11-21 15:30:03] local.INFO: Dados de Pagamento salvos
[2025-11-21 15:30:04] local.INFO: Usuário salvo com sucesso
```

---

## ❌ Se der erro...

### Erro: "CNPJ não está sendo sanitizado"
```
❌ Console mostra: txtPJCNPJ: "65.465.465/4564" (com máscara)

SOLUÇÃO:
1. Abra: public/js/cadastros/usuarios.js
2. Procure por: .replace(/\D/g, '')
3. Verifique que está lá (linha 241)
4. Recarga: Ctrl+Shift+R (hard refresh)
```

### Erro: "SQLSTATE[22P02]"
```
❌ user_id está vazio causando erro

SOLUÇÃO:
1. Verifique que usuarios.js tem:
   const id = parseInt(value);
   jsonData[key] = !isNaN(id) && id > 0 ? id : null;

2. user_id vazio deve virar null (não enviar vazio)
```

### Erro: "Pessoa Jurídica não está sendo salva"
```
❌ Dados não aparecem no banco

SOLUÇÃO:
1. Verifique UserController.php linhas 68-77
2. Deve ter:
   if (!empty($validated['txtPJRazaoSocial']) || !empty($validated['txtPJCNPJ'])) {
       $pessoaJuridica = $this->validatePessoaJuridica($validated);
       $this->savePessoaJuridica($user, $pessoaJuridica);
   }

3. Se não tem, re-aplique a correção
```

### Erro: "Pagamento não está sendo salvo"
```
❌ Dados não aparecem na tabela pagamento_usuario

SOLUÇÃO:
1. Verifique UserController.php linhas 79-92
2. Deve ter:
   if (!empty($validated['txtPagTitularConta']) || !empty($validated['txtPagBanco'])) {
       $pagamento = $this->validatePagamento($validated);
       $user->pagamento()->updateOrCreate(...);
   }

3. Se não tem, re-aplique a correção
```

---

## 🔍 Debug - Verificar Logs

```bash
# Terminal 1: Acompanhar logs em tempo real
tail -f storage/logs/laravel.log

# Terminal 2: No navegador, salve um usuário
# Você verá os logs aparecendo em tempo real no Terminal 1
```

---

## 📊 Checklist de Validação

- [ ] `php artisan cache:clear` executado
- [ ] Navegador aberto: `http://localhost:8001`
- [ ] Preenchido: Dados Pessoais ✅
- [ ] Preenchido: Pessoa Jurídica (CNPJ com máscara) ✅
- [ ] Preenchido: Dados de Pagamento ✅
- [ ] Clicou em "Salvar" ✅
- [ ] Toast verde apareceu ✅
- [ ] Verificou: usuarios.js sanitização ✅
- [ ] Verificou: Banco de dados (3 tabelas) ✅
- [ ] Verificou: Logs ✅

---

## 🎯 Se TUDO PASSOU ✅

Parabéns! A correção está funcionando!

Próximos passos:
1. Fazer commit
2. Fazer push
3. Deploy em produção

---

## 📞 Dúvidas?

1. Verifique `CORRECAO_APLICADA.md`
2. Verifique `TESTES_PRATICOS.md`
3. Verifique logs: `storage/logs/laravel.log`

---

**Status:** ✅ **PRONTO PARA TESTAR AGORA**
