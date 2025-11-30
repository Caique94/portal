# Guia de Teste - Implementação de CPF em Dados Pessoais

**Data:** 30 de Novembro de 2025
**Status:** ✅ Pronto para Teste

---

## 🚀 Como Testar a Implementação

### Teste 1: Criar Novo Usuário com CPF Válido

**Passos:**
1. Abrir http://localhost:8000/cadastros/usuarios
2. Clicar em "Adicionar"
3. Preencher formulário:
   - Nome: João Silva
   - Data Nasc: 1990-01-15
   - Email: joao@example.com
   - Celular: (11) 98765-4321
   - Papel: Consultor
   - CPF: **12345678909** (ou qualquer CPF válido com 11 dígitos)
   - Valor Hora: 150,00

4. Observar:
   - ✅ CPF deve ser formatado: `123.456.789-09`
   - ✅ Campo deve ter borda VERDE (is-valid)
   - ✅ Deve salvar com sucesso (200 ou 201)

**CPFs Válidos para Teste:**
```
Sem formatação:
- 12345678909
- 98765432109
- 11144477735

Já formatado:
- 123.456.789-09
- 987.654.321-09
- 111.444.777-35
```

---

### Teste 2: Testar Validação - CPF Inválido

**Passos:**
1. Abrir modal "Adicionar"
2. Preencher CPF: **11111111111** (dígitos repetidos)
3. Sair do campo (clicar em outro campo)

**Esperado:**
- ❌ Campo com borda VERMELHA (is-invalid)
- ❌ Tooltip de erro: "O CPF deve estar no formato..."
- ❌ Botão "Salvar" não salva

---

### Teste 3: Testar Formatação Automática

**Passos:**
1. Abrir modal "Adicionar"
2. No campo CPF, digitar: **12345678909** (sem máscara)

**Esperado (durante digitação):**
```
Digita: 1      → Campo mostra: 1
Digita: 12     → Campo mostra: 12
Digita: 123    → Campo mostra: 123
Digita: 1234   → Campo mostra: 123.4
Digita: 12345  → Campo mostra: 123.45
Digita: 123456 → Campo mostra: 123.456
Digita: 1234567 → Campo mostra: 123.456.7
Digita: 12345678 → Campo mostra: 123.456.78
Digita: 123456789 → Campo mostra: 123.456.789
Digita: 1234567890 → Campo mostra: 123.456.789-0
Digita: 12345678909 → Campo mostra: 123.456.789-09
```

---

### Teste 4: Testar com CPF Vazio (Permitido)

**Passos:**
1. Abrir modal "Adicionar"
2. Deixar CPF **VAZIO**
3. Preencher:
   - Nome: Maria Santos
   - Email: maria@example.com
   - Papel: Consultor
4. Clicar "Salvar"

**Esperado:**
- ✅ Usuário criado com sucesso
- ✅ CPF pode ser vazio (nullable)

---

### Teste 5: Testar Edição de Usuário

**Passos:**
1. Abrir /cadastros/usuarios
2. Clicar em "Editar" em um usuário existente
3. Modificar CPF para: **98765432109**
4. Clicar "Salvar"

**Esperado:**
- ✅ Usuário atualizado com sucesso
- ✅ CPF salvo formatado internamente como: `98765432109`

---

### Teste 6: Validação no Backend

**Via cURL:**

```bash
# Teste 1: CPF válido (formatado)
curl -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $(curl -s http://localhost:8000/cadastros/usuarios | grep -oP 'csrf-token" content="\K[^"]*')" \
  -d '{
    "txtUsuarioNome": "Teste API",
    "txtUsuarioEmail": "teste-api@example.com",
    "slcUsuarioPapel": "consultor",
    "txtUsuarioCPF": "123.456.789-09"
  }'

# Esperado: 201 Created
# Resposta JSON com success: true
```

```bash
# Teste 2: CPF válido (sem formatação)
curl -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: TOKEN_AQUI" \
  -d '{
    "txtUsuarioNome": "Teste API 2",
    "txtUsuarioEmail": "teste-api2@example.com",
    "slcUsuarioPapel": "consultor",
    "txtUsuarioCPF": "12345678909"
  }'

# Esperado: 201 Created
# Resposta JSON com success: true
```

```bash
# Teste 3: CPF inválido (menos de 11 dígitos)
curl -X POST http://localhost:8000/salvar-usuario \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: TOKEN_AQUI" \
  -d '{
    "txtUsuarioNome": "Teste Inválido",
    "txtUsuarioEmail": "teste-invalido@example.com",
    "slcUsuarioPapel": "consultor",
    "txtUsuarioCPF": "123456789"
  }'

# Esperado: 422 Unprocessable Entity
# Resposta JSON:
# {
#   "success": false,
#   "message": "Erro de validação dos dados",
#   "errors": {
#     "txtUsuarioCPF": ["O CPF deve estar no formato XXX.XXX.XXX-XX ou conter 11 dígitos"]
#   }
# }
```

---

## 📊 Checklist de Testes

### Frontend
- [ ] Formatação automática funciona (123.456.789-09)
- [ ] Validação de dígitos verificadores funciona
- [ ] Campo fica VERDE quando CPF é válido
- [ ] Campo fica VERMELHO quando CPF é inválido
- [ ] CPF vazio é permitido
- [ ] Pode salvar quando CPF é válido
- [ ] Não salva quando CPF é inválido (com erro)

### Backend
- [ ] CPF formatado é aceito (123.456.789-09)
- [ ] CPF sem máscara é aceito (12345678909)
- [ ] CPF inválido retorna erro 422
- [ ] Mensagem de erro é em português
- [ ] CPF é salvo SEM máscara no banco de dados (11 dígitos)

### Integration
- [ ] Carregar usuário mostra CPF formatado
- [ ] Editar usuário funciona com CPF novo
- [ ] DataTable mostra CPF na coluna (sem máscara)
- [ ] Exportar para Excel funciona

---

## 🐛 Possíveis Problemas e Soluções

### Problema 1: Campo CPF não formata enquanto digita
**Solução:** Verificar se `public/js/validators/cpf-validator.js` foi carregado
```javascript
// No console do browser (F12):
console.log('Validador carregado?', typeof window.validateCPF);
// Deve retornar: "function"
```

### Problema 2: Erro 500 ao salvar
**Solução:** Verificar logs
```bash
tail -f storage/logs/laravel.log
```

### Problema 3: CPF não é formatado ao carregar usuário
**Solução:** Verificar se o JavaScript está sendo executado após o modal abrir
```javascript
// No console, após abrir modal de edição:
console.log($('#txtUsuarioCPF').val());
// Deve mostrar CPF SEM máscara (11 dígitos)
// JavaScript vai formatar em tempo real
```

### Problema 4: Validação rejeita CPF válido
**Solução:** Verificar dígitos verificadores com calculadora online:
https://www.calcul.com.br/calcula/cpf

---

## 📝 Relatório de Testes

Após completar todos os testes, preencha:

| Teste | Status | Observações |
|-------|--------|-------------|
| Criar usuário com CPF válido | ⬜ | |
| CPF inválido é rejeitado | ⬜ | |
| Formatação automática funciona | ⬜ | |
| CPF vazio é permitido | ⬜ | |
| Edição funciona | ⬜ | |
| Validação backend funciona | ⬜ | |
| Dados salvos no banco corretamente | ⬜ | |

---

## 🔍 Verificações Finais

### 1. Verificar banco de dados
```bash
# Conectar ao database
php artisan tinker

# Verificar como CPF foi salvo
>>> $user = \App\Models\User::latest()->first();
>>> echo $user->cgc;
# Deve mostrar: 12345678909 (SEM máscara)

# Verificar se tem 11 caracteres
>>> strlen($user->cgc);
# Deve retornar: 11
```

### 2. Verificar arquivo de logs
```bash
tail -50 storage/logs/laravel.log
```

Deve mostrar mensagens como:
```
[2025-11-30 20:41:13] local.INFO: UserController::store iniciado {"isUpdate":false,...}
[2025-11-30 20:41:13] local.INFO: Novo usuário criado {"userId":XX,"email":"...","cpf":"12345678909",...}
```

### 3. Verificar JavaScript no console
```javascript
// F12 → Console
validateCPF("12345678909")      // Deve retornar: true
validateCPF("11111111111")      // Deve retornar: false
formatCPF("12345678909")        // Deve retornar: "123.456.789-09"
```

---

## ✅ Teste Completo (Fluxo End-to-End)

1. Abrir http://localhost:8000/cadastros/usuarios
2. Clicar "Adicionar"
3. Digitar CPF: `12345678909`
4. Observar formatação: `123.456.789-09`
5. Preencher outros campos obrigatórios
6. Clicar "Salvar"
7. Observar sucesso: "Usuário criado com sucesso"
8. Recarregar página: http://localhost:8000/cadastros/usuarios
9. Clicar "Editar" no usuário criado
10. Verificar que CPF aparece como: `123.456.789-09`
11. Fechar modal
12. No console do navegador, executar:
    ```javascript
    // Deve retornar dados do usuário
    $('table').DataTable().rows().data()[0]
    // Verificar que cgc = "12345678909" (sem máscara)
    ```

---

## 🎯 Conclusão

Se todos os testes passarem, a implementação está **100% funcional** e pronta para produção! ✅
