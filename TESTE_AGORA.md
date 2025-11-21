# ✅ TESTE AGORA - Salvamento Pessoa Jurídica e Pagamento

**Status:** ✅ Código corrigido e deployado
**Commit:** `723ea20`
**Data:** 2025-11-21

---

## 🎯 O Que Foi Corrigido Agora

**Problema:** Os dados de Pessoa Jurídica e Pagamento não estavam sendo salvos
**Solução:** Refatorei o código para salvar DIRETAMENTE no método `store()` em uma única transação

### Antes:
```php
if (!empty($validated['txtPJRazaoSocial']) || !empty($validated['txtPJCNPJ'])) {
    // chamava método separado
}
```

### Depois:
```php
// Sempre valida e tenta salvar se houver dados
$pessoaJuridica = $this->validatePessoaJuridica($validated);
if (!empty($pessoaJuridica['cnpj']) || !empty($pessoaJuridica['razao_social'])) {
    $pessoaJuridica['user_id'] = $user->id;
    $user->pessoaJuridica()->updateOrCreate(
        ['user_id' => $user->id],
        $pessoaJuridica
    );
}
```

---

## 🚀 Como Testar Agora

### 1. Limpar Cache (CRÍTICO)
```bash
php artisan cache:clear
php artisan config:clear
```

### 2. Acessar Sistema
```
URL: http://localhost:8001
Login: seu usuario
```

### 3. Ir para USUÁRIOS > Adicionar Usuário

### 4. Preencher APENAS a Aba "Dados Pessoais"
```
Nome: "João Silva"
Email: "joao@test.com"
Data Nasc: "1990-01-01"
Papel: "consultor"
```
**Clique em "Salvar"** - Deve funcionar sem Pessoa Jurídica

### 5. Preencher Dados Pessoais + Pessoa Jurídica

**Aba: Dados Pessoais**
```
Nome: "Maria Santos"
Email: "maria@test.com"
Data Nasc: "1985-05-15"
Papel: "financeiro"
```

**Aba: Pessoa Jurídica**
```
CNPJ: "12.345.678/0001-99"           ← COM MÁSCARA!
Razão Social: "MARIA SANTOS LTDA"
Nome Fantasia: "MS Serviços"
Endereço: "Rua ABC, 123"
Número: "123"
Bairro: "Centro"
Cidade: "São Paulo"
Estado: "SP"
CEP: "01234-567"
```

**Clique em "Salvar"** - Deve salvar usuário + Pessoa Jurídica

### 6. Preencher TUDO (Dados Pessoais + Pessoa Jurídica + Pagamento)

**Aba: Dados Pessoais**
```
Nome: "Carlos Oliveira"
Email: "carlos@test.com"
Data Nasc: "1988-03-20"
Papel: "admin"
```

**Aba: Pessoa Jurídica**
```
CNPJ: "98.765.432/0001-11"
Razão Social: "CARLOS OLIVEIRA CONSULTORIA"
Nome Fantasia: "CO Consultoria"
...
```

**Aba: Dados de Pagamento**
```
Titular Conta: "Carlos Oliveira"
CPF/CNPJ Titular: "123.456.789-09"     ← COM MÁSCARA!
Banco: "0001" (Banco do Brasil)
Agência: "0001"
Conta: "123456-7"
Tipo Conta: "corrente"
PIX Key: "carlos@test.com"
```

**Clique em "Salvar"** - Deve salvar TUDO em uma transação

---

## ✅ Verificar Resultado

### No Navegador
```
✅ Toast Verde: "Usuário criado com sucesso!"
✅ Modal fecha
✅ Tabela atualiza com novo usuário
```

### No Console (F12 > Console)
```
✅ "Dados sanitizados prontos para envio: {
    txtPJCNPJ: "123456780001999",     // SEM máscara
    txtPagCpfCnpjTitular: "12345678909" // SEM máscara
}"
```

### No Banco de Dados

```bash
psql -U postgres -d portal

-- Verifique users
SELECT id, name, email, papel FROM users WHERE email = 'carlos@test.com';
# Esperado: Carlos Oliveira | carlos@test.com | admin

-- Verifique pessoa_juridica_usuario (CNPJ SEM máscara!)
SELECT id, user_id, cnpj, razao_social FROM pessoa_juridica_usuario
WHERE user_id = X;
# Esperado: ... | X | 98765432000111 | CARLOS OLIVEIRA CONSULTORIA

-- Verifique pagamento_usuario (CPF/CNPJ SEM máscara!)
SELECT id, user_id, titular_conta, cpf_cnpj_titular, banco FROM pagamento_usuario
WHERE user_id = X;
# Esperado: ... | X | Carlos Oliveira | 12345678909 | 0001
```

### Nos Logs
```bash
tail -f storage/logs/laravel.log

# Procurar por:
[2025-11-21 XX:XX:XX] local.INFO: UserController::store iniciado
[2025-11-21 XX:XX:XX] local.INFO: Pessoa Jurídica validada
[2025-11-21 XX:XX:XX] local.INFO: Pessoa Jurídica salva com sucesso
[2025-11-21 XX:XX:XX] local.INFO: Dados de Pagamento salvos com sucesso
[2025-11-21 XX:XX:XX] local.INFO: Usuário salvo com sucesso
```

---

## ❌ Se Não Funcionar

### Erro: "Página em branco" ou "500"
```
1. Verifique logs: storage/logs/laravel.log
2. Procure por: "ERRO" ou "Exception"
3. Copie a mensagem de erro completa
```

### Erro: "SQLSTATE[22P02]"
```
1. Verifique se CNPJ/CPF estão sendo sanitizados
2. Console (F12) deve mostrar: "654654654564" (SEM máscara)
3. Se não, verifique usuarios.js linhas 238-260
4. Recarga com Ctrl+Shift+R (hard refresh)
```

### Erro: "Pessoa Jurídica não salva"
```
1. Verifique tabela: pessoa_juridica_usuario
2. SELECT * FROM pessoa_juridica_usuario;
3. Se vazia, verifique UserController.php linhas 68-82
4. Verifique logs procurando por erros
```

### Erro: "Pagamento não salvo"
```
1. Verifique tabela: pagamento_usuario
2. SELECT * FROM pagamento_usuario;
3. Se vazia, verifique UserController.php linhas 91-112
4. Verifique logs procurando por erros
```

---

## 📊 Checklist de Teste

- [ ] Cache limpo com `php artisan cache:clear`
- [ ] Acessado http://localhost:8001
- [ ] Teste 1: Apenas Dados Pessoais (✅ ou ❌)
- [ ] Teste 2: Dados Pessoais + Pessoa Jurídica (✅ ou ❌)
- [ ] Teste 3: TUDO (Dados Pessoais + Pessoa Jurídica + Pagamento) (✅ ou ❌)
- [ ] Verificado banco de dados (3 tabelas) (✅ ou ❌)
- [ ] Verificado logs (✅ ou ❌)
- [ ] Sem erro SQLSTATE[22P02] (✅ ou ❌)

---

## 🎯 Resultado Esperado

Se TUDO passar ✅:
- ✅ Usuário criado com sucesso
- ✅ Pessoa Jurídica salva (CNPJ SEM máscara)
- ✅ Pagamento salvo (CPF/CNPJ SEM máscara)
- ✅ Sem erro PostgreSQL
- ✅ Logs mostram sucesso

---

## 📝 Próximos Passos

Se tudo funcionar ✅:
1. Fazer commit (já feito: `723ea20`)
2. Fazer push (já feito)
3. Fazer merge para main
4. Deploy em produção

---

**Status:** ✅ **PRONTO PARA TESTAR AGORA**

Execute os testes acima e me avise se algo não funciona!
