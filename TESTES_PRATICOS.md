# 🧪 Testes Práticos - SQLSTATE[22P02] Correction

## 🎯 Como Testar a Correção

Este documento contém testes que você pode executar para validar a solução.

---

## 📋 Pré-requisitos

- ✅ Backend Laravel rodando: `http://localhost:8001`
- ✅ PostgreSQL acessível
- ✅ Arquivos corrigidos aplicados (ver CORRECOES_COMPLETAS_USUARIO_JURIDICA.md)
- ✅ DevTools aberto: F12 > Console

---

## 🧪 TESTE 1: CNPJ com Máscara

### Objetivo
Verificar que CNPJ mascarado é sanitizado corretamente.

### Pré-requisitos
- [ ] Estar na página: USUÁRIOS > Adicionar Usuário
- [ ] Modal "Adicionar Usuário" aberto

### Passos

1. **Preencher Formulário:**
   ```
   Aba: Dados Pessoais
   ├─ Nome: "Test User 1"
   ├─ Email: "test1@example.com"
   ├─ Data Nasc: "1990-01-01"
   ├─ Papel: "consultor"
   └─ (Salvar primeiro para criar o usuário base)

   Aba: Pessoa Jurídica
   ├─ CNPJ: "65.465.465/4564"  ← COM MÁSCARA
   ├─ Razão Social: "TEST LTDA"
   └─ Nome Fantasia: "Test"
   ```

2. **Abrir DevTools:**
   - Pressione: **F12**
   - Vá para aba: **Console**

3. **Clicar em "Salvar"**
   - Observar console para mensagens

4. **Verificar Console:**
   ```
   Você deve ver:
   ✅ "Dados sanitizados prontos para envio: {...}"
   ✅ "txtPJCNPJ: "654654654564""  (SEM máscara)
   ```

5. **Verificar Sucesso:**
   ```
   ✅ Toast verde: "Usuário salvo com sucesso!"
   ✅ Modal fecha
   ✅ Tabela atualiza
   ```

6. **Verificar Banco de Dados:**
   ```bash
   $ psql -U postgres -d portal
   portal=# SELECT cnpj FROM pessoa_juridica_usuario WHERE cnpj LIKE '%654%';

   Resultado esperado:
            cnpj
   ─────────────────
    654654654564    ← SEM MÁSCARA ✅
   ```

### Critério de Sucesso
- ✅ Console mostra `txtPJCNPJ: "654654654564"` (sem máscara)
- ✅ Banco armazena sem máscara
- ✅ Nenhum erro SQLSTATE[22P02]

---

## 🧪 TESTE 2: user_id Vazio (O ERRO ORIGINAL)

### Objetivo
Verificar que user_id vazio não gera erro PostgreSQL.

### Pré-requisitos
- [ ] Estar na página: USUÁRIOS > Adicionar Usuário
- [ ] Modal aberto
- [ ] DevTools aberto (F12 > Console)

### Passos

1. **Preencher Formulário Mínimo:**
   ```
   Aba: Dados Pessoais
   ├─ Nome: "Test User 2"
   ├─ Email: "test2@example.com"
   ├─ Papel: "financeiro"
   └─ (DEIXAR CAMPOS VAZIOS propositalmente)

   Aba: Pessoa Jurídica
   ├─ CNPJ: "11.222.333/0001-81"
   ├─ Razão Social: "COMPANY LTDA"
   └─ (DEIXAR id vazio)
   ```

2. **Clicar em "Salvar"**

3. **Verificar Console (F12 > Console):**
   ```
   Você deve ver:
   ✅ "Dados sanitizados prontos para envio: {...}"
   ✅ "user_id convertido: "" → null"
   ✅ Nenhum erro de tipo de dado
   ```

4. **Verificar Resposta:**
   ```
   ✅ Status: 201 (Created)
   ✅ Message: "Usuário criado com sucesso"
   ✅ Nenhum erro 500 ou SQLSTATE[22P02]
   ```

5. **Verificar Logs:**
   ```bash
   $ tail -f storage/logs/laravel.log

   Buscar por:
   ✅ "Pessoa Jurídica validada com sucesso"
   ✅ "Pessoa Jurídica salva com sucesso"
   ❌ Nenhum erro SQLSTATE[22P02]
   ```

### Critério de Sucesso
- ✅ Console mostra `user_id convertido: "" → null`
- ✅ Usuário criado com sucesso (status 201)
- ✅ Nenhum erro PostgreSQL
- ✅ Logs mostram sucesso

---

## 🧪 TESTE 3: CNPJ Duplicado

### Objetivo
Verificar que sistema detecta e rejeita CNPJ duplicado.

### Pré-requisitos
- [ ] Teste 1 já executado (CNPJ `654654654564` já existe)
- [ ] Estar na página: USUÁRIOS > Adicionar Usuário
- [ ] Modal aberto

### Passos

1. **Preencher Formulário com CNPJ DUPLICADO:**
   ```
   Aba: Dados Pessoais
   ├─ Nome: "Test User 3"
   ├─ Email: "test3@example.com"
   ├─ Papel: "admin"

   Aba: Pessoa Jurídica
   ├─ CNPJ: "65.465.465/4564"  ← MESMO DO TESTE 1!
   ├─ Razão Social: "ANOTHER COMPANY"
   ```

2. **Clicar em "Salvar"**

3. **Verificar Erro Esperado:**
   ```
   Toast vermelho:
   ✅ Icon: error
   ✅ Title: "Erro de validação dos dados"
   ✅ Text: "CNPJ já cadastrado para outro usuário"
   ```

4. **Verificar Console:**
   ```
   ✅ Status: 422 (Unprocessable Entity)
   ✅ errors: { txtPJCNPJ: ["CNPJ já cadastrado..."] }
   ```

5. **Verificar Logs:**
   ```bash
   $ tail -f storage/logs/laravel.log | grep "duplicado"

   ✅ "CNPJ duplicado detectado"
   ✅ "Tentativa de salvar CNPJ duplicado"
   ```

6. **Verificar Banco:**
   ```sql
   SELECT COUNT(*) FROM pessoa_juridica_usuario WHERE cnpj = '654654654564';

   Resultado: 1  ← Apenas um registro ✅
   ```

### Critério de Sucesso
- ✅ Toast de erro exibido
- ✅ CNPJ não foi salvo duplicado
- ✅ Logs mostram detecção de duplicata
- ✅ Status 422 retornado

---

## 🧪 TESTE 4: Dados Válidos Completos

### Objetivo
Verificar que sistema salva dados válidos com sucesso.

### Pré-requisitos
- [ ] Estar na página: USUÁRIOS > Adicionar Usuário
- [ ] Modal aberto
- [ ] DevTools aberto

### Passos

1. **Preencher Formulário Completo:**
   ```
   Aba: Dados Pessoais
   ├─ Nome: "João Silva"
   ├─ Email: "joao.silva@test.com"
   ├─ Data Nasc: "1985-06-15"
   ├─ Celular: "(11) 99999-8888"
   ├─ Papel: "consultor"
   ├─ CPF/CNPJ: "123.456.789-09"
   ├─ Valor Hora: "150,00"
   ├─ Valor Desl.: "50,00"
   ├─ Valor KM: "1,50"
   └─ Salário Base: "5000,00"

   Aba: Pessoa Jurídica
   ├─ CNPJ: "12.345.678/0001-99"
   ├─ Razão Social: "JOÃO SILVA CONSULTORIA LTDA"
   ├─ Nome Fantasia: "JS Consultoria"
   ├─ Inscrição Estadual: "123.456.789.012"
   ├─ Inscrição Municipal: "987654"
   ├─ Endereço: "Rua das Flores, 100"
   ├─ Número: "100"
   ├─ Bairro: "Centro"
   ├─ Cidade: "São Paulo"
   ├─ Estado: "SP"
   ├─ CEP: "01234-567"
   ├─ Telefone: "(11) 3333-4444"
   ├─ Email: "empresa@test.com"
   ├─ Site: "https://www.test.com"
   ├─ Ramo Atividade: "Consultoria de TI"
   └─ Data Constituição: "2015-03-20"

   Aba: Dados de Pagamento
   ├─ Titular da Conta: "João Silva"
   ├─ CPF/CNPJ Titular: "123.456.789-09"
   ├─ Banco: "0001"
   ├─ Agência: "0001"
   ├─ Conta: "123456-7"
   ├─ Tipo Conta: "corrente"
   └─ Chave PIX: "joao@test.com"
   ```

2. **Clicar em "Salvar"**

3. **Verificar Console:**
   ```
   ✅ Dados sanitizados aparecem
   ✅ CNPJ: "123456780001 99" (sem máscara)
   ✅ CPF: "12345678909" (sem máscara)
   ✅ CEP: "01234567" (sem máscara)
   ✅ user_id: inteiro válido
   ```

4. **Verificar Toast:**
   ```
   ✅ Verde
   ✅ "Usuário criado com sucesso!"
   ✅ Modal fecha
   ✅ Tabela atualiza
   ```

5. **Verificar Banco:**
   ```sql
   SELECT id, name, email, papel FROM users WHERE email = 'joao.silva@test.com';

   SELECT cnpj, razao_social FROM pessoa_juridica_usuario
   WHERE cnpj = '123456780001999';

   SELECT titular_conta, cpf_cnpj_titular FROM pagamento_usuario
   WHERE user_id = (SELECT id FROM users WHERE email = 'joao.silva@test.com');
   ```

6. **Verificar Logs:**
   ```bash
   $ grep "João Silva\|joao.silva@test.com" storage/logs/laravel.log

   ✅ "Novo usuário criado"
   ✅ "Pessoa Jurídica validada com sucesso"
   ✅ "Pessoa Jurídica salva com sucesso"
   ```

### Critério de Sucesso
- ✅ Todos os dados foram sanitizados corretamente
- ✅ Usuário criado (novo ID)
- ✅ Pessoa Jurídica salva
- ✅ Pagamento salvo
- ✅ Nenhum erro
- ✅ Logs mostram sucesso

---

## 🧪 TESTE 5: CNPJ Sem Máscara

### Objetivo
Verificar que CNPJ sem máscara também funciona.

### Pré-requisitos
- [ ] Estar na página: USUÁRIOS > Adicionar Usuário
- [ ] Modal aberto

### Passos

1. **Preencher com CNPJ SEM MÁSCARA:**
   ```
   Aba: Dados Pessoais
   ├─ Nome: "Test User 5"
   ├─ Email: "test5@example.com"
   ├─ Papel: "consultor"

   Aba: Pessoa Jurídica
   ├─ CNPJ: "99888777000100"  ← SEM MÁSCARA (14 dígitos)
   ├─ Razão Social: "DIRECT INPUT LTDA"
   ```

2. **Clicar em "Salvar"**

3. **Verificar Console:**
   ```
   ✅ CNPJ: "99888777000100" (continua igual)
   ✅ Validação passa
   ```

4. **Verificar Sucesso:**
   ```
   ✅ Toast verde
   ✅ Usuário salvo
   ```

5. **Verificar Banco:**
   ```sql
   SELECT cnpj FROM pessoa_juridica_usuario WHERE cnpj = '99888777000100';

   Resultado: 99888777000100  ✅
   ```

### Critério de Sucesso
- ✅ CNPJ sem máscara aceito
- ✅ Salvo corretamente
- ✅ Nenhum erro

---

## 📊 Tabela de Testes

| # | Teste | Input | Esperado | Resultado |
|---|-------|-------|----------|-----------|
| 1 | CNPJ Mascarado | `65.465.465/4564` | `654654654564` | ✅ |
| 2 | user_id Vazio | `id: ""` | `null` (sem erro) | ✅ |
| 3 | CNPJ Duplicado | Mesmo CNPJ 2x | Erro 422 | ✅ |
| 4 | Dados Completos | Todos os campos | Status 201 | ✅ |
| 5 | CNPJ Sem Máscara | `99888777000100` | Aceito | ✅ |

---

## 🐛 Se Um Teste Falhar

### Teste 1 Falha: CNPJ não é sanitizado
```
❌ Console: txtPJCNPJ: "65.465.465/4564" (com máscara)

SOLUÇÃO:
1. Verifique usuarios.js linha ~240
2. Verificar: value.replace(/\D/g, '')
3. Recarga: Ctrl+Shift+R (hard refresh)
```

### Teste 2 Falha: Erro SQLSTATE[22P02]
```
❌ Erro: "sintaxe de entrada é inválida para tipo bigint"

SOLUÇÃO:
1. Verifique UserController.php - método validatePessoaJuridica()
2. Verifique: is_numeric($userId) && $userId > 0
3. Verifique logs: storage/logs/laravel.log
4. Verifique: FormRequest de validação
```

### Teste 3 Falha: Duplicata não é detectada
```
❌ Mesmo CNPJ salvo 2 vezes

SOLUÇÃO:
1. Verifique UserController.php - método checkCNPJDuplicate()
2. Query de duplicata está correta?
3. Índice existe na tabela? ALTER TABLE pessoa_juridica_usuario ADD INDEX cnpj;
```

### Teste 4 Falha: Salvar não funciona
```
❌ Status 500 ou outro erro

SOLUÇÃO:
1. Verifique logs: storage/logs/laravel.log
2. Verifique StorePessoaJuridicaRequest.php existe?
3. Verifique namespace correto?
4. Rodou: php artisan cache:clear
```

### Teste 5 Falha: CNPJ sem máscara é rejeitado
```
❌ Erro de validação: "CNPJ deve estar em formato..."

SOLUÇÃO:
1. Verifique regex em StorePessoaJuridicaRequest.php
2. Regex deve aceitar com E sem máscara
3. Padrão: /^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/
```

---

## 📝 Registro de Testes

Use esta tabela para registrar seus testes:

| Data | Teste | Status | Nota |
|------|-------|--------|------|
| | TESTE 1 | ❌/✅ | |
| | TESTE 2 | ❌/✅ | |
| | TESTE 3 | ❌/✅ | |
| | TESTE 4 | ❌/✅ | |
| | TESTE 5 | ❌/✅ | |

---

## 🎯 Conclusão

Se todos os 5 testes passarem ✅, a correção está **PRONTA PARA PRODUÇÃO**.

---

## 🔗 Próximos Passos

- [ ] Todos os testes passaram? ✅
- [ ] Fazer commit: `Fix: PostgreSQL SQLSTATE[22P02] - CNPJ e user_id`
- [ ] Push para repositório
- [ ] Fazer PR (se aplicável)
- [ ] Deploy em produção
- [ ] Monitorar logs por 24h

---

**Status:** ✅ **PRONTO PARA TESTAR**
