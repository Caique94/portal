# 🔧 Erro PostgreSQL SQLSTATE[22P02] - Análise Completa e Solução

## 📋 Resumo do Erro

```
SQLSTATE[22P02]: Invalid text representation: 7 ERRO: sintaxe de entrada é inválida para tipo bigint: "="
CONTEXT: parâmetro de portal sem nome $2 = '...'
SQL: select count(*) as aggregate from pessoa_juridica_usuario
where cnpj = 65.465.465/4564 and user_id != =)
```

---

## 🔍 Diagnóstico: Problemas Identificados

### **Problema 1: CNPJ com Máscara**
**Linha do erro:** `where cnpj = 65.465.465/4564`

**O que está acontecendo:**
- O frontend envia o CNPJ formatado: `65.465.465/4564` (com pontos e barra)
- O backend não remove a máscara antes de usar na query
- A coluna `cnpj` na tabela é `VARCHAR`, então aceita o valor mascarado
- **MAS:** pode causa problemas de comparação e duplicação (um CNPJ é o mesmo, só que com/sem máscara)

**Solução:**
```php
// ANTES (Errado):
$cnpj = $request->input('cnpj'); // "65.465.465/4564"

// DEPOIS (Correto):
$cnpj = preg_replace('/\D/', '', $request->input('cnpj')); // "654654654564"
```

---

### **Problema 2: user_id com Valor Inválido**
**Linha do erro:** `and user_id != =)`

**O que está acontecendo:**
- O `user_id` está chegando vazio, null ou com caracteres inválidos (`=)`)
- PostgreSQL tenta converter `"=)"` para `BIGINT` (tipo da coluna user_id)
- Falha porque `"=)"` não é um número válido
- **Erro SQLSTATE[22P02]:** "Invalid text representation"

**Causas possíveis:**
1. ❌ Frontend não enviando o `user_id` corretamente
2. ❌ Variável `$userId` não inicializada ou vazia
3. ❌ Parsing incorreto do JSON enviado
4. ❌ A condição `where('user_id', '!=', $userId)` executando com valor inválido

**Solução:**
```php
// ANTES (Errado):
$userId = $request->input('id'); // pode ser vazio ou inválido
$query->where('user_id', '!=', $userId); // tenta usar sem validar

// DEPOIS (Correto):
$userId = (int) $request->input('id');
if (is_numeric($userId) && $userId > 0) {
    $query->where('user_id', '!=', $userId);
}
```

---

## 🔬 Análise Técnica: Por que PostgreSQL dá erro 22P02?

### **O Erro SQLSTATE[22P02]**

| Código | Significado | Causa |
|--------|------------|-------|
| **22P02** | Invalid Text Representation | Tentativa de converter uma string inválida para um tipo numérico |

### **Como Acontece:**

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Frontend envia:                                          │
│    { id: "", cnpj: "65.465.465/4564" }                     │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. Backend recebe e prepara query:                          │
│    $userId = $request->input('id');  // ""                  │
│    $cnpj = $request->input('cnpj');  // "65.465.465/4564"  │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. Query é construída:                                      │
│    SELECT COUNT(*) FROM pessoa_juridica_usuario            │
│    WHERE cnpj = $1          -- "65.465.465/4564"            │
│    AND user_id != $2        -- ""                            │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. PostgreSQL tenta:                                        │
│    - Converter "65.465.465/4564" para comparar com CNPJ     │
│    - Converter "" ou "=)" para BIGINT                        │
│                                                              │
│    ❌ FALHA! "=)" não é BIGINT válido                        │
│    ERRO: Invalid text representation for type bigint        │
└─────────────────────────────────────────────────────────────┘
```

### **Versão de Erro Alternativa**

Se a coluna CNPJ fosse `BIGINT` (o que não é):
```
ERRO: invalid input syntax for integer: "65.465.465/4564"
```

---

## 🛠️ Solução Completa

### **1. Backend - Validação no Controller**

**Arquivo:** `app/Http/Controllers/UserController.php`

#### A) Adicionar método para validar PessoaJuridica

```php
/**
 * Validar e sanitizar dados de Pessoa Jurídica
 */
private function validatePessoaJuridica(array $data): array
{
    // 1. Limpar CNPJ: remover tudo que não é número
    $cnpj = isset($data['txtPJCNPJ'])
        ? preg_replace('/\D/', '', (string)$data['txtPJCNPJ'])
        : null;

    // 2. Validar CNPJ (se fornecido)
    if (!empty($cnpj)) {
        // Deve ter exatamente 14 dígitos
        if (strlen($cnpj) !== 14) {
            throw new \Illuminate\Validation\ValidationException(
                \Illuminate\Validation\Validator::make(
                    ['txtPJCNPJ' => $cnpj],
                    ['txtPJCNPJ' => 'size:14']
                )
            );
        }
    }

    return [
        'user_id'              => $data['user_id'] ?? null,  // NÃO sanitizar, é do DB
        'cnpj'                 => $cnpj,  // ✅ SANITIZADO
        'razao_social'         => $data['txtPJRazaoSocial'] ?? null,
        'nome_fantasia'        => $data['txtPJNomeFantasia'] ?? null,
        'inscricao_estadual'   => $data['txtPJInscricaoEstadual'] ?? null,
        'inscricao_municipal'  => $data['txtPJInscricaoMunicipal'] ?? null,
        'endereco'             => $data['txtPJEndereco'] ?? null,
        'numero'               => $data['txtPJNumero'] ?? null,
        'complemento'          => $data['txtPJComplemento'] ?? null,
        'bairro'               => $data['txtPJBairro'] ?? null,
        'cidade'               => $data['txtPJCidade'] ?? null,
        'estado'               => $data['txtPJEstado'] ?? null,
        'cep'                  => $data['txtPJCEP'] ?? null,
        'telefone'             => $data['txtPJTelefone'] ?? null,
        'email'                => $data['txtPJEmail'] ?? null,
        'site'                 => $data['txtPJSite'] ?? null,
        'ramo_atividade'       => $data['txtPJRamoAtividade'] ?? null,
        'data_constituicao'    => $data['txtPJDataConstituicao'] ?? null,
    ];
}
```

#### B) Verificar CNPJ Duplicado com Segurança

```php
/**
 * Verificar se CNPJ já existe (excluindo o próprio usuário em case de update)
 */
private function checkCNPJDuplicate($cnpj, $userId = null, $pessoaJuridicaId = null)
{
    if (empty($cnpj)) {
        return false; // Se vazio, não é duplicata
    }

    $query = PessoaJuridicaUsuario::where('cnpj', $cnpj);

    // Se é UPDATE, ignorar o próprio registro
    if (!empty($pessoaJuridicaId)) {
        $query->where('id', '!=', $pessoaJuridicaId);
    } elseif (!empty($userId)) {
        // Se é CREATE, verificar se outro usuário já tem este CNPJ
        $query->where('user_id', '!=', intval($userId));
    }

    return $query->exists();
}
```

#### C) Chamar validações no store()

```php
// No método store(), adicionar:

// Validar pessoa jurídica se enviada
if (!empty($validated['txtPJCNPJ']) || !empty($validated['txtPJRazaoSocial'])) {
    $pessoaJuridica = $this->validatePessoaJuridica($validated);

    // Verificar duplicata CNPJ
    if (!empty($pessoaJuridica['cnpj'])) {
        if ($this->checkCNPJDuplicate(
            $pessoaJuridica['cnpj'],
            $user->id ?? $userId
        )) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'txtPJCNPJ' => ['CNPJ já cadastrado para outro usuário']
            ]);
        }
    }

    // Salvar pessoa jurídica
    if ($isUpdate) {
        $user->pessoaJuridica()->updateOrCreate(
            ['user_id' => $user->id],
            $pessoaJuridica
        );
    } else {
        $pessoaJuridica['user_id'] = $user->id;
        PessoaJuridicaUsuario::create($pessoaJuridica);
    }
}
```

---

### **2. Validação no Request (Laravel Form Request)**

**Criar novo arquivo:** `app/Http/Requests/StorePessoaJuridicaRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePessoaJuridicaRequest extends FormRequest
{
    public function authorize()
    {
        return true; // ou adicione lógica de autorização
    }

    public function rules()
    {
        $userId = $this->input('id');

        return [
            'id'                      => 'nullable|integer|min:1',
            'txtPJCNPJ'              => [
                'nullable',
                'string',
                'regex:/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$|^\d{14}$/', // Aceita com ou sem máscara
            ],
            'txtPJRazaoSocial'       => 'nullable|string|max:255',
            'txtPJNomeFantasia'      => 'nullable|string|max:255',
            'txtPJInscricaoEstadual' => 'nullable|string|max:20',
            'txtPJInscricaoMunicipal'=> 'nullable|string|max:20',
            'txtPJEndereco'          => 'nullable|string|max:255',
            'txtPJNumero'            => 'nullable|string|max:20',
            'txtPJComplemento'       => 'nullable|string|max:100',
            'txtPJBairro'            => 'nullable|string|max:100',
            'txtPJCidade'            => 'nullable|string|max:100',
            'txtPJEstado'            => 'nullable|string|max:2',
            'txtPJCEP'               => 'nullable|string|max:10',
            'txtPJTelefone'          => 'nullable|string|max:20',
            'txtPJEmail'             => 'nullable|email|max:255',
            'txtPJSite'              => 'nullable|url|max:255',
            'txtPJRamoAtividade'     => 'nullable|string|max:255',
            'txtPJDataConstituicao'  => 'nullable|date_format:Y-m-d',
        ];
    }

    public function messages()
    {
        return [
            'txtPJCNPJ.regex'    => 'CNPJ deve estar no formato XX.XXX.XXX/XXXX-XX ou 14 dígitos',
            'txtPJEmail.email'   => 'Email da empresa inválido',
            'txtPJSite.url'      => 'URL do site inválida',
            'txtPJDataConstituicao.date_format' => 'Data deve estar em formato YYYY-MM-DD',
        ];
    }

    public function prepareForValidation()
    {
        // Sanitizar CNPJ antes de validar
        $cnpj = $this->input('txtPJCNPJ');
        if ($cnpj) {
            $this->merge([
                'txtPJCNPJ' => preg_replace('/\D/', '', $cnpj)
            ]);
        }
    }
}
```

---

### **3. Frontend - Sanitização de Entrada (JavaScript)**

**Arquivo:** `public/js/cadastros/usuarios.js`

Adicionar antes do `$.ajax`:

```javascript
// ANTES de enviar os dados:
const formData = new FormData($f[0]);
const jsonData = {};

formData.forEach((value, key) => {
    // ✅ Sanitizar CNPJ removendo máscara
    if (key === 'txtPJCNPJ' && value) {
        jsonData[key] = value.replace(/\D/g, ''); // Remove tudo que não é número
    }
    // ✅ Garantir que user_id é número válido
    else if (key === 'id') {
        jsonData[key] = parseInt(value) || null; // Converte para int ou null
    }
    else {
        jsonData[key] = value;
    }
});

console.log('Dados sanitizados:', jsonData);
```

**Ou adicionar classes masking ao form:**

```html
<!-- Adicionar data-attribute para masking -->
<input type="text"
       name="txtPJCNPJ"
       id="txtPJCNPJ"
       class="form-control cnpj"
       placeholder="CNPJ"
       data-sanitize="numeric" />
```

---

## 🧪 Casos de Teste

### **Teste 1: CNPJ com Máscara**

**Input:**
```json
{
  "txtPJCNPJ": "65.465.465/4564"
}
```

**Processo:**
1. ✅ Frontend: `"65.465.465/4564".replace(/\D/g, '')` → `"654654654564"`
2. ✅ Backend: `preg_replace('/\D/', '', '654654654564')` → `"654654654564"`
3. ✅ Query: `WHERE cnpj = '654654654564'` → ✅ Funciona

**Output:**
```php
CNPJ armazenado: "654654654564"
```

---

### **Teste 2: CNPJ sem Máscara**

**Input:**
```json
{
  "txtPJCNPJ": "654654654564"
}
```

**Processo:**
1. ✅ Frontend: nenhuma mudança (já é só números)
2. ✅ Backend: `preg_replace('/\D/', '', '654654654564')` → `"654654654564"`
3. ✅ Query: `WHERE cnpj = '654654654564'` → ✅ Funciona

---

### **Teste 3: user_id Vazio (O ERRO)**

**Input:**
```json
{
  "id": "",
  "txtPJCNPJ": "654654654564"
}
```

**Processo:**
1. ❌ ANTES:
   ```
   $userId = "";
   WHERE user_id != ""  → PostgreSQL tenta converter "" para BIGINT → ERRO 22P02
   ```

2. ✅ DEPOIS:
   ```
   $userId = (int) "" → 0
   if (is_numeric($userId) && $userId > 0) {  // FALSE, não executa
       // ... ignorar WHERE
   }
   Query final: WHERE cnpj = '654654654564'  → ✅ Sucesso
   ```

---

### **Teste 4: user_id com Valor Válido**

**Input:**
```json
{
  "id": "5",
  "txtPJCNPJ": "654654654564"
}
```

**Processo:**
1. ✅ Frontend: `parseInt("5")` → `5`
2. ✅ Backend: `is_numeric(5) && 5 > 0` → TRUE
3. ✅ Query: `WHERE cnpj = '654654654564' AND user_id != 5` → ✅ Funciona

---

### **Teste 5: CNPJ Duplicado**

**Input:**
```json
{
  "id": "3",
  "txtPJCNPJ": "654654654564"
}
```

**Cenário:** CNPJ `654654654564` já existe para `user_id = 2`

**Processo:**
1. ✅ Limpar CNPJ: `"654654654564"`
2. ✅ Verificar duplicata:
   ```php
   PessoaJuridicaUsuario::where('cnpj', '654654654564')
       ->where('user_id', '!=', 3)  // ✅ Válido, é INT
       ->exists()  // true
   ```
3. ✅ Retornar erro: `"CNPJ já cadastrado para outro usuário"`

---

## 📊 Resumo das Correções

| Problema | Solução | Arquivo |
|----------|---------|---------|
| CNPJ com máscara | `preg_replace('/\D/', '', $cnpj)` | Controller + JS |
| user_id vazio | Verificar `is_numeric() && > 0` antes de usar | Controller |
| CNPJ duplicado | Novo método `checkCNPJDuplicate()` | Controller |
| Validação fraca | Criar `StorePessoaJuridicaRequest` | App\Http\Requests |
| Sanitização frontend | Adicionar data-attribute ou regex no JS | usuarios.js |

---

## 🚀 Checklist de Implementação

- [ ] Adicionar método `validatePessoaJuridica()` no Controller
- [ ] Adicionar método `checkCNPJDuplicate()` no Controller
- [ ] Criar `StorePessoaJuridicaRequest.php`
- [ ] Atualizar `store()` para chamar validações
- [ ] Adicionar sanitização de CNPJ no JS
- [ ] Adicionar validação de user_id antes de usar em WHERE
- [ ] Testar todos os 5 casos acima
- [ ] Verificar logs em `storage/logs/laravel.log`
- [ ] Fazer commit com mensagem clara

---

## 📝 Logs Esperados (Debugging)

### **Sucesso:**
```
[2025-11-21 10:30:45] local.INFO: Pessoa jurídica validada
{
    "cnpj": "654654654564",
    "razao_social": "EMPRESA LTDA",
    "user_id": 5
}
```

### **Erro (capturado):**
```
[2025-11-21 10:31:12] local.WARNING: CNPJ duplicado
{
    "cnpj": "654654654564",
    "user_id": 3,
    "motivo": "Já existe para user_id = 2"
}
```

---

## 🔐 Considerações de Segurança

✅ **Sanitização:** Remove caracteres inválidos do CNPJ
✅ **Validação:** Verifica formato (14 dígitos)
✅ **Integridade:** Garante que user_id é sempre um número válido
✅ **Unicidade:** Verifica duplicatas de CNPJ globalmente
✅ **SQL Injection Prevention:** Usa Eloquent (prepared statements)

---

**Status:** ✅ **READY FOR PATCH**
