# Criar Bancos de Dados - Passo a Passo

Este guia mostra como criar os três bancos de dados necessários (portal_dev, portal_staging, portal_prod).

---

## Opção 1: Via pgAdmin (Recomendado - Interface Gráfica)

### Passo 1: Abrir pgAdmin
1. Abra seu navegador
2. Acesse `http://localhost:5050` (ou a URL onde pgAdmin está rodando)
3. Faça login com suas credenciais

### Passo 2: Conectar ao Servidor PostgreSQL
1. Na árvore à esquerda, clique em **Servers**
2. Você deve ver seu servidor PostgreSQL (ex: "PostgreSQL 12")
3. Clique para conectar

### Passo 3: Criar Banco de DESENVOLVIMENTO (portal_dev)
1. Clique com botão direito em **Databases**
2. Selecione **Create** → **Database**
3. Na aba **General**, preencha:
   - **Database name:** `portal_dev`
   - **Owner:** `postgres`
4. Clique em **Save**

### Passo 4: Criar Banco de STAGING (portal_staging)
1. Clique com botão direito em **Databases** novamente
2. Selecione **Create** → **Database**
3. Na aba **General**, preencha:
   - **Database name:** `portal_staging`
   - **Owner:** `postgres`
4. Clique em **Save**

### Passo 5: Criar Banco de PRODUÇÃO (portal_prod)
1. Clique com botão direito em **Databases** novamente
2. Selecione **Create** → **Database**
3. Na aba **General**, preencha:
   - **Database name:** `portal_prod`
   - **Owner:** `postgres`
4. Clique em **Save**

### Passo 6: Verificar
Você deve ver na lista de Databases:
- portal (original)
- portal_dev ✓
- portal_staging ✓
- portal_prod ✓

---

## Opção 2: Via SQL Script (Arquivo SETUP_DATABASES.sql)

### Passo 1: Abrir Query Tool no pgAdmin
1. Clique com botão direito no seu servidor PostgreSQL
2. Selecione **Query Tool**
3. Uma aba com editor SQL abrirá

### Passo 2: Copiar SQL
Copie todo o conteúdo do arquivo `SETUP_DATABASES.sql`:

```sql
CREATE DATABASE portal_dev
  WITH OWNER postgres
  ENCODING 'UTF8'
  LC_COLLATE='pt_BR.UTF-8'
  LC_CTYPE='pt_BR.UTF-8'
  TEMPLATE=template0;

CREATE DATABASE portal_staging
  WITH OWNER postgres
  ENCODING 'UTF8'
  LC_COLLATE='pt_BR.UTF-8'
  LC_CTYPE='pt_BR.UTF-8'
  TEMPLATE=template0;

CREATE DATABASE portal_prod
  WITH OWNER postgres
  ENCODING 'UTF8'
  LC_COLLATE='pt_BR.UTF-8'
  LC_CTYPE='pt_BR.UTF-8'
  TEMPLATE=template0;
```

### Passo 3: Colar no Query Tool
1. Cole o código SQL no editor
2. Clique no botão **Execute** (Play)
3. Você verá: "Query returned successfully with no result in xxx ms"

### Passo 4: Verificar
Atualize a lista de Databases (F5) e confirme que os 3 novos bancos existem.

---

## Opção 3: Via Command Line (PowerShell/CMD)

### Passo 1: Abrir PowerShell
1. Pressione `Win + X`
2. Selecione **Windows PowerShell** ou **Command Prompt**

### Passo 2: Navegar até a pasta do projeto
```powershell
cd "C:\Users\caique\Documents\portal\portal"
```

### Passo 3: Executar o script SQL
Se você tem `psql` instalado:

```powershell
psql -U postgres -f SETUP_DATABASES.sql
```

Ou execute cada comando individualmente:

```powershell
psql -U postgres -c "CREATE DATABASE portal_dev WITH OWNER postgres ENCODING 'UTF8';"
psql -U postgres -c "CREATE DATABASE portal_staging WITH OWNER postgres ENCODING 'UTF8';"
psql -U postgres -c "CREATE DATABASE portal_prod WITH OWNER postgres ENCODING 'UTF8';"
```

### Passo 4: Verificar
Execute:
```powershell
psql -U postgres -l
```

Você verá uma lista com todos os bancos, incluindo:
- portal_dev
- portal_staging
- portal_prod

---

## ✅ Verificação Final

Depois de criar os bancos, você pode verificar no pgAdmin:

1. Expanda **Databases** na árvore
2. Você deve ver:
   ```
   Databases
   ├── postgres
   ├── template0
   ├── template1
   ├── portal          (original)
   ├── portal_dev      ✓ (novo)
   ├── portal_staging  ✓ (novo)
   └── portal_prod     ✓ (novo)
   ```

---

## ⚠️ Se algo deu errado

### Erro: "Database already exists"
Significa que o banco já foi criado. Tudo bem, você pode ignorar o erro ou usar:

```sql
DROP DATABASE IF EXISTS portal_dev;
CREATE DATABASE portal_dev WITH OWNER postgres ENCODING 'UTF8';
```

### Erro: "Permission denied"
Certifique-se de que está usando um usuário com permissão (ex: `postgres`).

### Erro: "Connection refused"
Verifique se PostgreSQL está rodando:
- Windows: Procure por "PostgreSQL" nos serviços (Services)
- Linux/Mac: `sudo systemctl start postgresql`

---

## 🎯 Próximos Passos

Depois de criar os bancos, você pode:

1. **Executar migrações em cada banco:**
   ```bash
   # Para DEV
   copy .env.development .env
   php artisan migrate:seed

   # Para STAGING
   copy .env.staging .env
   php artisan migrate:seed

   # Para PRODUCTION
   copy .env.production .env
   php artisan migrate
   ```

2. **Iniciar os servidores:**
   - `START_DEVELOPMENT.bat` para DEV
   - `START_STAGING.bat` para STAGING
   - `START_PRODUCTION.bat` para PRODUÇÃO

---

**Dúvidas?** Consulte o arquivo `SETUP_MULTIPLOS_AMBIENTES.md` para mais detalhes!
