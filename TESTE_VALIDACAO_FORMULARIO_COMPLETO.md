# 🧪 Teste Completo - Validação de Formulário

**Data:** 30 de Novembro de 2025
**Status:** Testes de Validação de Constraint Violations
**Objetivo:** Verificar se o formulário de usuários salva corretamente com diferentes combinações de campos

---

## 📋 Resumo das Alterações

### ✅ Problema Resolvido
- **Erro 500:** `SQLSTATE[23502]: Not null violation: o valor nulo na coluna "estado"...`
- **Causa:** Código tentava salvar Pessoa Jurídica com campos obrigatórios vazios
- **Solução:** Adicionada validação para AMBOS os relacionamentos:
  1. **Pessoa Jurídica:** Valida 10 campos obrigatórios antes de salvar
  2. **Pagamento:** Valida 4 campos obrigatórios antes de salvar

### 🔧 Campos Obrigatórios Identificados

#### Pessoa Jurídica (10 campos)
- ✅ CNPJ
- ✅ Razão Social
- ✅ Endereço
- ✅ Número
- ✅ Bairro
- ✅ Cidade
- ✅ Estado
- ✅ CEP
- ✅ Telefone
- ✅ Email

#### Pagamento (4 campos)
- ✅ Titular da Conta
- ✅ Banco
- ✅ Agência
- ✅ Conta

---

## 🎯 Casos de Teste

### TESTE 1: Criar usuário com APENAS ABA 1 (Dados Pessoais)
**Objetivo:** Verificar se é possível criar usuário sem preencher ABAs 2 e 3

**Passos:**
1. Abrir formulário de novo usuário
2. Preencher ABA 1 (Dados Pessoais):
   - Nome: "João Silva Teste 1"
   - Email: "joao.teste1@example.com"
   - Papel: "admin"
   - Data Nasc.: "1990-01-15"
   - Celular: "(11) 98765-4321"
   - CPF: "123.456.789-10"
   - Valor Hora: "50,00"
   - Valor Deslocamento: "10,00"
   - Valor KM: "0,50"
   - Salário Base: "3.000,00"
3. **NÃO preencher** ABA 2 (Pessoa Jurídica)
4. **NÃO preencher** ABA 3 (Pagamento)
5. Clicar em "Salvar"

**Resultado Esperado:**
- ✅ Usuário criado com sucesso
- ✅ Sem erro 500
- ✅ Sem erro de constraint violation
- ✅ Log mostra: "Pessoa Jurídica não salva (faltam campos obrigatórios)"
- ✅ Log mostra: "Dados de Pagamento não salvos (faltam campos obrigatórios)"

**Verificação:**
```
[OK] Usuário aparece na lista de usuários
[OK] Dados de ABA 1 estão corretos
[OK] ABA 2 está vazia (sem registros)
[OK] ABA 3 está vazia (sem registros)
```

---

### TESTE 2: Criar usuário com ABA 1 + Pessoa Jurídica INCOMPLETA

**Objetivo:** Verificar se sistema detecta campos faltantes em Pessoa Jurídica

**Passos:**
1. Abrir formulário de novo usuário
2. Preencher ABA 1 normalmente
3. Preencher ABA 2 (Pessoa Jurídica) com APENAS alguns campos:
   - CNPJ: "12.345.678/0001-90"
   - Razão Social: "Empresa Teste Ltda"
   - **NÃO preencher:** Endereço, Número, Bairro, Cidade, Estado, CEP, Telefone, Email
4. **NÃO preencher** ABA 3
5. Clicar em "Salvar"

**Resultado Esperado:**
- ✅ Usuário criado com sucesso
- ✅ Sem erro 500
- ✅ Sem erro de constraint violation
- ✅ Log mostra: "Pessoa Jurídica não salva (faltam campos obrigatórios)"
- ✅ Mensagem no console indica campos faltantes

**Verificação:**
```
[OK] Usuário aparece na lista
[OK] ABA 2 está vazia (parciais não foram salvos)
[OK] Nenhum erro de validação
```

---

### TESTE 3: Criar usuário com ABA 1 + Pessoa Jurídica COMPLETA

**Objetivo:** Verificar se Pessoa Jurídica completa é salva com sucesso

**Passos:**
1. Abrir formulário de novo usuário
2. Preencher ABA 1 normalmente
3. Preencher ABA 2 (Pessoa Jurídica) com TODOS os campos obrigatórios:
   - CNPJ: "12.345.678/0001-90"
   - Razão Social: "Empresa Teste Ltda"
   - Endereço: "Rua das Flores"
   - Número: "123"
   - Bairro: "Centro"
   - Cidade: "São Paulo"
   - Estado: "SP"
   - CEP: "01310-100"
   - Telefone: "(11) 3333-4444"
   - Email: "empresa@example.com"
   - Nome Fantasia: "Teste Fantasia" (OPCIONAL)
   - Inscrição Estadual: "123.456.789.012" (OPCIONAL)
4. **NÃO preencher** ABA 3
5. Clicar em "Salvar"

**Resultado Esperado:**
- ✅ Usuário criado com sucesso
- ✅ Log mostra: "Pessoa Jurídica salva com sucesso"
- ✅ Sem erro 500

**Verificação:**
```
[OK] Usuário aparece na lista
[OK] Ao abrir edição, ABA 2 mostra todos os dados salvos
[OK] Valores persistem após reload
```

---

### TESTE 4: Criar usuário com ABA 1 + Pagamento INCOMPLETO

**Objetivo:** Verificar se Pagamento incompleto não é salvo

**Passos:**
1. Abrir formulário de novo usuário
2. Preencher ABA 1 normalmente
3. **NÃO preencher** ABA 2
4. Preencher ABA 3 (Pagamento) com APENAS alguns campos:
   - Titular da Conta: "João Silva"
   - **NÃO preencher:** Banco, Agência, Conta
5. Clicar em "Salvar"

**Resultado Esperado:**
- ✅ Usuário criado com sucesso
- ✅ Log mostra: "Dados de Pagamento não salvos (faltam campos obrigatórios)"
- ✅ Sem erro 500

**Verificação:**
```
[OK] Usuário aparece na lista
[OK] ABA 3 está vazia (parciais não foram salvos)
[OK] Nenhum erro
```

---

### TESTE 5: Criar usuário com ABA 1 + Pagamento COMPLETO

**Objetivo:** Verificar se Pagamento completo é salvo com sucesso

**Passos:**
1. Abrir formulário de novo usuário
2. Preencher ABA 1 normalmente
3. **NÃO preencher** ABA 2
4. Preencher ABA 3 (Pagamento) com TODOS os campos obrigatórios:
   - Titular da Conta: "João Silva"
   - Banco: "Banco do Brasil"
   - Agência: "1234"
   - Conta: "567890"
   - Tipo de Conta: "corrente"
   - CPF/CNPJ do Titular: "123.456.789-10" (OPCIONAL)
   - Chave PIX: "joao@example.com" (OPCIONAL)
5. Clicar em "Salvar"

**Resultado Esperado:**
- ✅ Usuário criado com sucesso
- ✅ Log mostra: "Dados de Pagamento salvos com sucesso"
- ✅ Sem erro 500

**Verificação:**
```
[OK] Usuário aparece na lista
[OK] Ao abrir edição, ABA 3 mostra todos os dados salvos
[OK] Valores persistem após reload
```

---

### TESTE 6: Criar usuário com TUDO PREENCHIDO (ABA 1 + 2 + 3)

**Objetivo:** Verificar se formulário completo funciona sem erros

**Passos:**
1. Abrir formulário de novo usuário
2. Preencher ABA 1 completamente
3. Preencher ABA 2 completamente
4. Preencher ABA 3 completamente
5. Clicar em "Salvar"

**Resultado Esperado:**
- ✅ Usuário criado com sucesso
- ✅ Log mostra: "Pessoa Jurídica salva com sucesso"
- ✅ Log mostra: "Dados de Pagamento salvos com sucesso"
- ✅ Todos os 3 registros criados

**Verificação:**
```
[OK] Usuário aparece na lista
[OK] Ao abrir edição, TODAS as ABAs mostram dados
[OK] Nenhum campo vazio que deveria estar preenchido
[OK] Valores persistem após reload
```

---

### TESTE 7: Editar usuário existente - Modificar dados

**Objetivo:** Verificar se UPDATE também respeita as validações

**Passos:**
1. Abrir usuário criado no TESTE 6 (com tudo preenchido)
2. Na ABA 2, limpar o campo "Estado"
3. Clicar em "Salvar"

**Resultado Esperado:**
- ✅ Usuário atualizado com sucesso
- ✅ Log mostra: "Pessoa Jurídica não salva (faltam campos obrigatórios)"
- ✅ Dados antigos de Pessoa Jurídica são mantidos (não são atualizados com valores vazios)

**Verificação:**
```
[OK] Ao reabrir edição, Estado ainda tem o valor anterior
[OK] Sem erro 500
```

---

## 🔍 Verificação de Logs

### Como verificar logs da aplicação:

```bash
# Terminal/PowerShell
tail -f "c:\Users\caique\Documents\portal\portal\storage\logs\laravel.log"
```

### O que procurar:

#### ✅ Sucesso - Pessoa Jurídica Completa
```
[INFO] Pessoa Jurídica salva com sucesso: {"user_id": 123, "cnpj": "12.345.678/0001-90"}
```

#### ✅ Sucesso - Pagamento Completo
```
[INFO] Dados de Pagamento salvos com sucesso: {"user_id": 123, "banco": "Banco do Brasil"}
```

#### ✅ Esperado - Faltam Campos
```
[INFO] Pessoa Jurídica não salva (faltam campos obrigatórios): {...}
[INFO] Dados de Pagamento não salvos (faltam campos obrigatórios): {...}
```

#### ❌ Erro - Nunca deve aparecer
```
SQLSTATE[23502]: Not null violation
SQLSTATE[23503]: Foreign key violation
Error 500
```

---

## 📊 Matriz de Testes

| Teste | ABA 1 | ABA 2 | ABA 3 | Status | Esperado | Resultado |
|-------|-------|-------|-------|--------|----------|-----------|
| 1 | ✅ Completo | ❌ Vazio | ❌ Vazio | Salvo | ✅ Sucesso | ⬜ |
| 2 | ✅ Completo | ⚠️ Incompleto | ❌ Vazio | Salvo | ✅ Sucesso (AB2 skip) | ⬜ |
| 3 | ✅ Completo | ✅ Completo | ❌ Vazio | Salvo | ✅ Sucesso (AB2 save) | ⬜ |
| 4 | ✅ Completo | ❌ Vazio | ⚠️ Incompleto | Salvo | ✅ Sucesso (AB3 skip) | ⬜ |
| 5 | ✅ Completo | ❌ Vazio | ✅ Completo | Salvo | ✅ Sucesso (AB3 save) | ⬜ |
| 6 | ✅ Completo | ✅ Completo | ✅ Completo | Salvo | ✅ Sucesso (Tudo) | ⬜ |
| 7 | ✅ (Editar) | ⚠️ (Incompleto) | ✅ | Update | ✅ Sucesso (skip) | ⬜ |

**Legenda:**
- ⬜ Não testado
- ✅ Passou (resultado correto)
- ❌ Falhou (resultado incorreto)

---

## 🐛 Troubleshooting

### Se Teste 1 falhar com erro 500:
- Verificar logs em `storage/logs/laravel.log`
- Procurar por "SQLSTATE[23502]"
- Confirmar que validação de Pessoa Jurídica está em UserController.php linhas 74-84

### Se Teste 3 falhar (Pessoa Jurídica não salva):
- Verificar se todos os 10 campos obrigatórios estão preenchidos
- Procurar nos logs por "Pessoa Jurídica não salva"
- Confirmar campo "estado" está preenchido (foi o erro original)

### Se Teste 5 falhar (Pagamento não salva):
- Verificar se todos os 4 campos obrigatórios estão preenchidos
- Procurar nos logs por "Dados de Pagamento não salvos"
- Confirmar que "Banco", "Agência" e "Conta" estão preenchidos

---

## ✅ Checklist Final

### Implementação
- [ ] UserController.php tem validação para Pessoa Jurídica (linhas 74-84)
- [ ] UserController.php tem validação para Pagamento (linhas 117-121)
- [ ] Código foi commitado
- [ ] Não há arquivos não salvos

### Testes
- [ ] Teste 1 passou (ABA 1 only)
- [ ] Teste 2 passou (ABA 1 + ABA 2 incompleto)
- [ ] Teste 3 passou (ABA 1 + ABA 2 completo)
- [ ] Teste 4 passou (ABA 1 + ABA 3 incompleto)
- [ ] Teste 5 passou (ABA 1 + ABA 3 completo)
- [ ] Teste 6 passou (ABA 1 + 2 + 3)
- [ ] Teste 7 passou (editar)

### Verificações
- [ ] Nenhum erro 500 em nenhum teste
- [ ] Nenhum erro SQLSTATE[23502] em logs
- [ ] Logs mostram comportamento esperado
- [ ] Dados persistem após reload
- [ ] Formatação monetária está correta (150,00)

### Se Tudo Passou
- [ ] Sistema está pronto para staging
- [ ] Documentação foi atualizada
- [ ] Próximo passo: Code review + Merge

---

## 📞 Referência Rápida

**Arquivo modificado:**
- `app/Http/Controllers/UserController.php`
  - Linhas 74-84: Validação Pessoa Jurídica
  - Linhas 117-121: Validação Pagamento

**Git Commit:**
- `1991096` - fix: Add validation for all required Pagamento fields

**Campos Críticos:**
- Pessoa Jurídica: estado, cidade (fáceis de deixar em branco)
- Pagamento: banco, agência (fáceis de deixar em branco)

---

**Data:** 30 de Novembro de 2025
**Status:** 🟡 Aguardando execução dos testes
**Próxima Ação:** Executar TESTE 1 a 7 e preencher a matriz de resultados

