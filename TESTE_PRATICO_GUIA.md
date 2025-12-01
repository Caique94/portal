# 🧪 Guia Prático de Testes - Implementações de Hoje

**Data:** 01 de Dezembro de 2025
**Status:** 🟢 PRONTO PARA TESTAR
**Commits:** 319576e, cad0731, 78be524, 4b9f190

---

## ✅ PARTE 1: Testes dos 3 Problemas Resolvidos

### Teste 1.1: CPF Validator (Escopo Corrigido)
**Objetivo:** Verificar que CPF Validator só funciona no modal, não na listagem

**Passos:**
1. Abra a página de **Usuários** (listagem)
2. Abra o DevTools (F12) → Aba **Console**
3. Na tabela de usuários, localize um usuário
4. Clique em **Editar** para abrir o modal
5. No campo **CPF** do modal:
   - Limpe o campo
   - Digite alguns números (ex: "123")
   - **Esperado:** Máscara aplicada automaticamente
6. Feche o modal (Cancel)
7. Feche o DevTools

**Resultado Esperado:** ✅ CPF Validator funcionou apenas dentro do modal

---

### Teste 1.2: Data de Nascimento (Renderização Corrigida)
**Objetivo:** Verificar que data_nasc aparece corretamente ao editar

**Passos:**
1. Abra página de **Usuários**
2. Clique em **Editar** em um usuário que tem data de nascimento
3. Verifique o campo **Data de Nascimento** (ABA 1)
4. **Esperado:** Data deve estar preenchida (ex: "1990-05-15")
5. Modifique a data (escolha outra data)
6. Clique em **Salvar**
7. Clique em **Editar** novamente do mesmo usuário
8. **Esperado:** Data modificada deve estar lá

**Resultado Esperado:** ✅ Data renderiza e persiste corretamente

---

### Teste 1.3: Pessoa Jurídica Salvando Dados Parciais
**Objetivo:** Verificar que dados parciais de Pessoa Jurídica agora salvam

**Passos:**

#### Caso A: Preenchimento Parcial (sem CNPJ)
1. Clique em **Novo Usuário**
2. Preencha **ABA 1** (Dados Pessoais):
   - Nome: "Teste PJ Parcial"
   - Email: "teste.pj.parcial@example.com"
   - Papel: "admin"
3. Vá para **ABA 2** (Pessoa Jurídica)
4. Preencha APENAS:
   - Razão Social: "Empresa Teste Ltda"
   - Telefone: "(11) 99999-8888"
   - **NÃO preencha CNPJ, Endereço, Número, etc**
5. Clique em **Salvar**
6. **Esperado:** Salvar com sucesso (sem erro)
7. Abra novamente o usuário e vá para **ABA 2**
8. **Esperado:** Razão Social e Telefone devem estar lá

**Resultado Esperado:** ✅ Dados parciais salvos com sucesso

---

#### Caso B: CNPJ Preenchido (Exige Todos os 10 Campos)
1. Clique em **Novo Usuário**
2. Preencha **ABA 1** normalmente
3. Vá para **ABA 2**
4. Preencha:
   - CNPJ: "12.345.678/0001-90"
   - Razão Social: "Empresa Com CNPJ Ltda"
   - **NÃO preencha os outros 8 campos obrigatórios**
5. Clique em **Salvar**
6. **Esperado:** Erro 422 com mensagem sobre campos obrigatórios
7. Agora preencha TODOS os 10 campos:
   - CNPJ ✅
   - Razão Social ✅
   - Endereço: "Rua Exemplo, 123"
   - Número: "123"
   - Bairro: "Centro"
   - Cidade: "São Paulo"
   - Estado: "SP"
   - CEP: "01234-567"
   - Telefone: "(11) 99999-8888"
   - Email: "empresa@example.com"
8. Clique em **Salvar**
9. **Esperado:** Salvar com sucesso
10. Abra novamente e verifique ABA 2
11. **Esperado:** Todos os 10 campos devem estar lá

**Resultado Esperado:** ✅ Validação CNPJ funcionando (todos ou nenhum)

---

## 🆕 PARTE 2: Testes do Sistema de Email para Ordem de Serviço

### Pre-requisitos:
- Você precisa ter uma **Ordem de Serviço** existente no banco de dados
- A Ordem deve ter:
  - ✅ `consultor_id` preenchido (consultor existente com email)
  - ✅ `cliente_id` preenchido (cliente existente com email)
  - ✅ `data_emissao` preenchida

### Como encontrar uma Ordem de Serviço ID:

**Option 1 - Direto no Banco de Dados:**
```sql
SELECT id, consultor_id, cliente_id, data_emissao
FROM ordem_servico
LIMIT 5;
```

**Option 2 - Via Postman/cURL:**
```bash
curl http://localhost:8001/api/ordem-servico
# Se houver endpoint de listagem
```

**Option 3 - Criar uma Ordem via UI** (se disponível)

---

### Teste 2.1: Enviar para Consultor
**Objetivo:** Verificar que email é enviado para o consultor

**Via Postman:**
```http
POST http://localhost:8001/api/ordem-servico/enviar-consultor
Content-Type: application/json

{
  "id": 1
}
```

**Via cURL:**
```bash
curl -X POST http://localhost:8001/api/ordem-servico/enviar-consultor \
  -H "Content-Type: application/json" \
  -d '{"id": 1}'
```

**Via JavaScript Console:**
```javascript
fetch('http://localhost:8001/api/ordem-servico/enviar-consultor', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ id: 1 })
})
.then(r => r.json())
.then(d => console.log(d))
```

**Resultado Esperado:**
```json
{
  "success": true,
  "message": "Ordem de Serviço enviada para o Consultor com sucesso"
}
```

**Verificação:**
1. Verifique o email do consultor (deve receber email)
2. Verifique os logs em `storage/logs/laravel.log`:
   ```
   local.INFO: Ordem de Serviço enviada para Consultor {"os_id":1,"consultor_email":"consultor@..."}
   ```

---

### Teste 2.2: Enviar para Cliente
**Objetivo:** Verificar que email é enviado para o cliente

**Via Postman:**
```http
POST http://localhost:8001/api/ordem-servico/enviar-cliente
Content-Type: application/json

{
  "id": 1
}
```

**Resultado Esperado:**
```json
{
  "success": true,
  "message": "Ordem de Serviço enviada para o Cliente com sucesso"
}
```

**Verificação:**
1. Verifique o email do cliente (deve receber email)
2. Verifique os logs

---

### Teste 2.3: Enviar para Ambos
**Objetivo:** Verificar que email é enviado simultaneamente para consultor E cliente

**Via Postman:**
```http
POST http://localhost:8001/api/ordem-servico/enviar-ambos
Content-Type: application/json

{
  "id": 1
}
```

**Resultado Esperado:**
```json
{
  "success": true,
  "message": "Ordem de Serviço enviada com sucesso para Consultor e Cliente",
  "detalhes": {
    "consultor": true,
    "cliente": true
  }
}
```

**Verificação:**
1. Ambos (consultor e cliente) devem receber o email
2. Verifique os logs para duas linhas de sucesso

---

## 📧 O que Você Deve Receber no Email

### Estrutura do Email:
```
De: noreply@personalitec.com.br
Para: consultor@email.com OU cliente@email.com
Assunto: Ordem de Serviço #1 - Personalitec

[Corpo do Email - HTML Formatado]
```

### Conteúdo do Email Deve Incluir:
- ✅ Logo Personalitec no topo
- ✅ Número da Ordem (#1)
- ✅ Nome do Cliente
- ✅ Nome do Consultor
- ✅ Data de Emissão
- ✅ Horas (Início, Fim, Desconto, Traslado)
- ✅ Total de Horas Trabalhadas
- ✅ Detalhamento do Serviço
- ✅ Resumo com valores, KM e status
- ✅ Layout responsivo (funciona em mobile também)

---

## 🔍 Troubleshooting - Se Algo Não Funcionar

### Erro: "404 Not Found"
**Causa:** Route não foi registrada corretamente
**Solução:**
```bash
cd c:\Users\caique\Documents\portal\portal
php artisan route:list | grep "ordem-servico"
# Deve mostrar as 3 rotas
```

### Erro: "500 Internal Server Error"
**Verificar:**
```bash
# Verifique os logs
tail -f storage/logs/laravel.log

# Procure por:
# - "Erro ao enviar Ordem de Serviço"
# - "Class not found"
# - Qualquer erro PHP
```

### Erro: "Email não foi recebido"
**Verificar:**
1. Configuração de email em `.env`:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=seu_host
   MAIL_PORT=sua_porta
   MAIL_USERNAME=seu_usuario
   MAIL_PASSWORD=sua_senha
   MAIL_FROM_ADDRESS=noreply@personalitec.com.br
   ```

2. Verificar logs para confirmar que tentou enviar:
   ```bash
   grep "Ordem de Serviço enviada" storage/logs/laravel.log
   ```

3. Se estiver em desenvolvimento, usar **Mailtrap**:
   - Vá em https://mailtrap.io
   - Crie conta grátis
   - Copie as credenciais SMTP para `.env`
   - Todos os emails serão capturados lá (não vai spam real)

---

## 📋 Checklist de Testes

### Testes dos 3 Problemas:
- [ ] CPF Validator funciona apenas no modal
- [ ] Data nascimento renderiza corretamente
- [ ] Pessoa Jurídica parcial salva (sem CNPJ)
- [ ] Pessoa Jurídica completa salva (com CNPJ)
- [ ] Pessoa Jurídica com CNPJ incompleto dá erro

### Testes de Email:
- [ ] Email para Consultor funciona
- [ ] Email para Cliente funciona
- [ ] Email para Ambos funciona
- [ ] Email recebido tem layout correto
- [ ] Email contém todos os dados esperados
- [ ] Logs mostram sucesso

---

## 🎯 Próximos Passos Após Confirmar Testes

1. **Se tudo passar:**
   - Fazer pull request ou merge para staging
   - Deploy para teste em staging
   - Teste com dados reais

2. **Se algo falhar:**
   - Relatar qual teste falhou
   - Incluir mensagem de erro (se houver)
   - Verificar logs em `storage/logs/laravel.log`

---

**Status:** 🟢 PRONTO PARA TESTAR
**Última Atualização:** 01 de Dezembro de 2025

Boa sorte nos testes! 🚀

