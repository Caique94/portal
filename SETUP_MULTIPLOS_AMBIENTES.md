# Portal - Configuração de Múltiplos Ambientes

Este documento descreve como configurar e gerenciar três ambientes diferentes para o Portal:
- **Desenvolvimento** (Development)
- **Validação/Testes** (Staging)
- **Produção** (Production)

---

## 📋 Resumo dos Ambientes

| Aspecto | Desenvolvimento | Staging | Produção |
|--------|-----------------|---------|----------|
| **Porta** | 8000 | 8080 | 8001 |
| **URL Local** | http://localhost:8000 | http://localhost:8080 | http://localhost:8001 |
| **URL Rede** | http://192.168.0.166:8000 | http://192.168.0.166:8080 | http://192.168.0.166:8001 |
| **Banco de Dados** | portal_dev | portal_staging | portal_prod |
| **APP_DEBUG** | true | true | false |
| **LOG_LEVEL** | debug | warning | error |
| **Uso** | Desenvolvimento local | Validação por usuários | Produção final |

---

## 🗂️ Arquivos Criados

```
projeto/
├── .env                          ← Arquivo principal (gerenciado automaticamente)
├── .env.development             ← Configuração de desenvolvimento
├── .env.staging                 ← Configuração de staging
├── .env.production              ← Configuração de produção
├── START_DEVELOPMENT.bat        ← Script para iniciar ambiente DEV
├── START_STAGING.bat            ← Script para iniciar ambiente STAGING
├── START_PRODUCTION.bat         ← Script para iniciar ambiente PROD
├── SETUP_DATABASES.sql          ← Script para criar bancos de dados
└── SETUP_MULTIPLOS_AMBIENTES.md ← Este documento
```

---

## 📊 Configuração de Banco de Dados

### 1️⃣ Criar os Bancos de Dados

Execute o script SQL `SETUP_DATABASES.sql` no pgAdmin ou psql:

**Opção A: Via pgAdmin**
1. Abra pgAdmin em seu navegador
2. Clique com botão direito em "Databases"
3. Selecione "Create" → "Database"
4. Crie os seguintes bancos:
   - **portal_dev** (Desenvolvimento)
   - **portal_staging** (Staging)
   - **portal_prod** (Produção)

**Opção B: Via psql (Command Line)**
```bash
psql -U postgres -f SETUP_DATABASES.sql
```

**Opção C: Executando comandos SQL diretamente**
```sql
CREATE DATABASE portal_dev WITH OWNER postgres ENCODING 'UTF8';
CREATE DATABASE portal_staging WITH OWNER postgres ENCODING 'UTF8';
CREATE DATABASE portal_prod WITH OWNER postgres ENCODING 'UTF8';
```

### 2️⃣ Verificar Bancos de Dados Criados

No pgAdmin, você deve ver 4 bancos de dados:
- portal (original)
- portal_dev
- portal_staging
- portal_prod

---

## 🚀 Como Usar Cada Ambiente

### Ambiente de Desenvolvimento

**Para iniciar o ambiente de desenvolvimento:**
1. Execute `START_DEVELOPMENT.bat`
2. Acessar via:
   - Seu computador: `http://localhost:8000`
   - Outro dispositivo: `http://192.168.0.166:8000`

**Características:**
- APP_DEBUG = true (mostra erros detalhados)
- LOG_LEVEL = debug (mais informações nos logs)
- Use para desenvolvimentos locais e testes iniciais

**Exemplo de uso:**
```
Você está desenvolvendo uma nova feature:
→ Trabalhe em Development
→ Teste localmente em http://localhost:8000
```

### Ambiente de Staging (Validação/Testes)

**Para iniciar o ambiente de staging:**
1. Execute `START_STAGING.bat`
2. Acessar via:
   - Seu computador: `http://localhost:8080`
   - Outro dispositivo: `http://192.168.0.166:8080`

**Características:**
- APP_DEBUG = true (ainda mostra erros para diagnóstico)
- LOG_LEVEL = warning (menos verbose que dev)
- Use para testes de usuários antes de ir para produção

**Exemplo de uso:**
```
Você quer validar uma feature com usuários:
→ Faça deploy em Staging
→ Compartilhe: http://192.168.0.166:8080
→ Usuários testam e validam
→ Se OK, move para Produção
```

### Ambiente de Produção

**Para iniciar o ambiente de produção:**
1. Execute `START_PRODUCTION.bat`
2. Acessar via:
   - Seu computador: `http://localhost:8001`
   - Outro dispositivo: `http://192.168.0.166:8001`

**Características:**
- APP_DEBUG = false (não mostra detalhes de erros)
- LOG_LEVEL = error (apenas erros críticos)
- Use para usuários finais

**Exemplo de uso:**
```
Após validação em Staging:
→ Feature vai para Production
→ Usuários finais acessam: http://192.168.0.166:8001
```

---

## 🔄 Fluxo de Desenvolvimento Recomendado

```
┌─────────────────────────────────────────────────────────────┐
│                    DESENVOLVIMENTO                          │
│         (Development - http://localhost:8000)               │
│  - Você faz mudanças no código                              │
│  - Testa localmente                                         │
│  - Usa banco de dados portal_dev                            │
└────────────┬────────────────────────────────────────────────┘
             │
             ↓ Mudanças testadas e funcionando
┌─────────────────────────────────────────────────────────────┐
│                   VALIDAÇÃO/TESTES                          │
│      (Staging - http://192.168.0.166:8080)                 │
│  - Outros usuários testam as novas features                │
│  - Dados mais realistas (portal_staging)                   │
│  - Validação com dados reais de clientes                   │
└────────────┬────────────────────────────────────────────────┘
             │
             ↓ Validado por usuários
┌─────────────────────────────────────────────────────────────┐
│                      PRODUÇÃO                               │
│      (Production - http://192.168.0.166:8001)              │
│  - Ambiente de usuários finais                             │
│  - Banco de dados portal_prod (real)                       │
│  - Debug desativado, apenas erros críticos                 │
└─────────────────────────────────────────────────────────────┘
```

---

## 📝 Migrações e Seeds em Cada Ambiente

### Executar Migrações

**Quando você está em um ambiente específico (.env ativo):**

```bash
# As migrações serão executadas no banco configurado no .env
php artisan migrate

# Ou com seed de dados
php artisan migrate:seed
```

**Exemplo para cada ambiente:**

1. **Development** (.env.development)
```bash
copy .env.development .env
php artisan migrate:seed  # Popula portal_dev com dados de teste
```

2. **Staging** (.env.staging)
```bash
copy .env.staging .env
php artisan migrate:seed  # Popula portal_staging com dados realistas
```

3. **Production** (.env.production)
```bash
copy .env.production .env
php artisan migrate  # Somente migração, sem seed automático
```

---

## ⚠️ Importante: Alternando Entre Ambientes

Os arquivos `.bat` fazem isso automaticamente, mas você precisa saber:

1. **Cada script copia automaticamente o arquivo `.env` correspondente**
   - `START_DEVELOPMENT.bat` → copia `.env.development` para `.env`
   - `START_STAGING.bat` → copia `.env.staging` para `.env`
   - `START_PRODUCTION.bat` → copia `.env.production` para `.env`

2. **Não edite o arquivo `.env` diretamente** (será sobrescrito)
   - Se precisar fazer mudanças, edite o `.env.{ambiente}` específico

3. **Antes de alternar ambientes, feche todos os servidores**
   - Pressione Ctrl+C em cada terminal
   - Ou feche as janelas

---

## 🔍 Verificando Qual Ambiente Está Ativo

**Opção 1: Olhar o título da janela Laravel**
```
"Portal DEV - Servidor Laravel"       ← Desenvolvimento
"Portal STAGING - Servidor Laravel"   ← Staging
"Portal PRODUCAO - Servidor Laravel"  ← Produção
```

**Opção 2: Checkar o arquivo .env**
```bash
# Ver qual APP_ENV está ativo
findstr APP_ENV .env
```

**Opção 3: Acessar a página e ver o título**
- Desenvolvimento: "Portal Desenvolvimento"
- Staging: "Portal Validação/Testes"
- Produção: "Portal Produção"

---

## 💾 Backup de Dados por Ambiente

Cada banco de dados é independente, então:

- Mudanças em **portal_dev** NÃO afetam portal_staging ou portal_prod
- Mudanças em **portal_staging** NÃO afetam portal_prod
- Mudanças em **portal_prod** são as reais e afetam usuários

**Para fazer backup:**

```bash
# Backup de Development
pg_dump -U postgres portal_dev > backup_dev.sql

# Backup de Staging
pg_dump -U postgres portal_staging > backup_staging.sql

# Backup de Produção
pg_dump -U postgres portal_prod > backup_prod.sql
```

---

## 🐛 Troubleshooting

### Erro: "Banco de dados portal_dev não existe"

**Solução:** Execute o script SETUP_DATABASES.sql para criar os bancos

### Erro: "Porta 8000 já está em uso"

**Solução:**
- Feche o processo que está usando a porta
- Ou use uma porta diferente no START_DEVELOPMENT.bat

```bash
# Verificar qual processo está usando a porta
netstat -ano | findstr :8000

# Matar processo (se necessário)
taskkill /PID <PID> /F
```

### Mudança de ambiente não reflete

**Solução:**
1. Feche todos os 3 terminais do servidor anterior
2. Execute o novo script `.bat`
3. Limpe o cache do navegador (Ctrl+Shift+Delete)

---

## 📚 Resumo de Configurações

### Arquivo .env.development
- **Porta:** 8000
- **Banco:** portal_dev
- **Debug:** true
- **Log Level:** debug

### Arquivo .env.staging
- **Porta:** 8080
- **Banco:** portal_staging
- **Debug:** true
- **Log Level:** warning

### Arquivo .env.production
- **Porta:** 8001
- **Banco:** portal_prod
- **Debug:** false
- **Log Level:** error

---

## ✅ Checklist de Setup

- [ ] Criou os 3 bancos de dados (portal_dev, portal_staging, portal_prod)
- [ ] Confirmou que os arquivos `.env.*` existem
- [ ] Confirmou que os scripts `.bat` existem
- [ ] Testou START_DEVELOPMENT.bat
- [ ] Testou START_STAGING.bat
- [ ] Testou START_PRODUCTION.bat
- [ ] Executou migrações em cada ambiente
- [ ] Confirmou que cada ambiente usa o banco correto

---

**Dúvidas?** Consulte este documento ou execute os scripts batch que tudo funciona automaticamente! 🚀
