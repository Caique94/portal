# 🚀 Deploy no EasyPanel - Portal Personalitec

## 📋 O que é EasyPanel?

EasyPanel é uma plataforma de hospedagem simplificada que gerencia containers Docker automaticamente. Você conecta seu repositório Git e o EasyPanel cuida do resto.

---

## 🔧 Pré-requisitos

1. ✅ Conta no EasyPanel (https://easypanel.io)
2. ✅ Repositório Git do projeto (GitHub, GitLab, Bitbucket)
3. ✅ Código commitado e enviado

---

## 📦 Passo 1: Preparar Repositório

```bash
# No seu computador
cd C:\Users\caique\Documents\portal\portal

# Inicializar Git (se ainda não tiver)
git init
git add .
git commit -m "Deploy inicial - Portal Personalitec"

# Conectar com repositório remoto
git remote add origin https://github.com/seu-usuario/portal-personalitec.git
git push -u origin main
```

---

## 🌐 Passo 2: Criar Projeto no EasyPanel

### **2.1 - Acessar Dashboard**
1. Acesse https://easypanel.io
2. Faça login
3. Clique em **"Create Project"** ou **"New Project"**

### **2.2 - Conectar Repositório**
1. Nome do projeto: `portal-personalitec`
2. Selecione **"Deploy from Git"**
3. Conecte sua conta GitHub/GitLab
4. Selecione o repositório `portal-personalitec`
5. Branch: `main`

### **2.3 - Configurar Build**
1. **Build Method:** Docker
2. **Dockerfile Path:** `Dockerfile` (raiz do projeto)
3. **Port:** `80` (porta interna do container)
4. **External Port:** `80` ou `443` (EasyPanel gerencia automaticamente)

---

## 🗄️ Passo 3: Adicionar Banco de Dados PostgreSQL

### **Opção A: PostgreSQL Integrado do EasyPanel**
1. No projeto, clique em **"Add Database"**
2. Selecione **PostgreSQL**
3. Nome: `portal_db`
4. Versão: `15`
5. Clique em **"Create"**

**O EasyPanel criará automaticamente e fornecerá:**
- `DB_HOST` - hostname do banco
- `DB_PORT` - porta (geralmente 5432)
- `DB_DATABASE` - nome do banco
- `DB_USERNAME` - usuário
- `DB_PASSWORD` - senha

### **Opção B: PostgreSQL Externo (Supabase, etc.)**
Use as credenciais do seu banco externo nas variáveis de ambiente.

---

## 🔴 Passo 4: Adicionar Redis (Cache)

1. No projeto, clique em **"Add Service"**
2. Selecione **Redis**
3. Versão: `7-alpine`
4. Nome: `portal_redis`
5. Clique em **"Create"**

**O EasyPanel fornecerá:**
- `REDIS_HOST` - hostname do Redis
- `REDIS_PORT` - 6379

---

## ⚙️ Passo 5: Configurar Variáveis de Ambiente

No EasyPanel, vá em **"Environment Variables"** e adicione:

```env
# App
APP_NAME="Portal Personalitec"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:SUA_CHAVE_AQUI
APP_URL=https://seu-dominio.easypanel.host

# Database (Use as credenciais fornecidas pelo EasyPanel)
DB_CONNECTION=pgsql
DB_HOST=postgres  # hostname fornecido pelo EasyPanel
DB_PORT=5432
DB_DATABASE=portal_db
DB_USERNAME=postgres
DB_PASSWORD=senha_gerada_automaticamente

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis (Use o hostname fornecido pelo EasyPanel)
REDIS_HOST=redis  # hostname fornecido pelo EasyPanel
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (se usar)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@gmail.com
MAIL_FROM_NAME="Portal Personalitec"
```

**⚠️ IMPORTANTE:** Gere a `APP_KEY` rodando localmente:
```bash
php artisan key:generate --show
```
Copie o valor e cole em `APP_KEY`.

---

## 🚀 Passo 6: Deploy

1. Clique em **"Deploy"** no EasyPanel
2. Aguarde o build da imagem (2-5 minutos)
3. O EasyPanel mostrará os logs em tempo real

**Status do Deploy:**
- ✅ Building... - Criando imagem Docker
- ✅ Pushing... - Enviando para registry
- ✅ Deploying... - Iniciando container
- ✅ Running - Aplicação no ar!

---

## 🔧 Passo 7: Executar Migrations

### **Via Terminal do EasyPanel:**
1. Vá em **"Console"** ou **"Terminal"**
2. Execute:
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan storage:link
```

### **Via One-time Job (Recomendado):**
1. Vá em **"Jobs"**
2. Clique **"Create Job"**
3. Command: `php artisan migrate --force`
4. Execute

---

## 🌍 Passo 8: Configurar Domínio

### **Domínio Fornecido pelo EasyPanel:**
```
https://portal-personalitec.easypanel.host
```

### **Domínio Customizado:**
1. Vá em **"Domains"**
2. Clique **"Add Domain"**
3. Digite: `seu-dominio.com`
4. Configure DNS:
   - **Tipo:** A
   - **Nome:** @ (ou subdominio)
   - **Valor:** IP fornecido pelo EasyPanel

5. ✅ **SSL Automático:** EasyPanel configura Let's Encrypt automaticamente

---

## 📊 Passo 9: Monitoramento

### **Logs em Tempo Real:**
```
Dashboard → Logs → View Real-time Logs
```

### **Métricas:**
- CPU Usage
- Memory Usage
- Network I/O
- Restart Count

### **Health Check:**
EasyPanel verifica automaticamente se a aplicação está respondendo.

---

## 🔄 Atualizações Automáticas

### **Deploy Automático via Git:**
1. Faça alterações no código
2. Commit e push:
```bash
git add .
git commit -m "Atualização X"
git push origin main
```
3. **EasyPanel detecta automaticamente e faz redeploy!**

### **Deploy Manual:**
1. Dashboard → **"Redeploy"**
2. Aguarde o processo

---

## 🛠️ Comandos Úteis no Terminal do EasyPanel

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rodar migrations
php artisan migrate --force

# Ver status da aplicação
php artisan about

# Criar usuário admin (se tiver seeder)
php artisan db:seed --class=AdminSeeder

# Verificar configuração do banco
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

---

## 🔒 Segurança

✅ **SSL/HTTPS** - Automático com Let's Encrypt  
✅ **Firewall** - Gerenciado pelo EasyPanel  
✅ **Backups** - Configure backups automáticos do PostgreSQL  
✅ **Variáveis de Ambiente** - Nunca commitar `.env` no Git  

### **Ativar Backups:**
1. PostgreSQL Service → **"Backups"**
2. **"Enable Automatic Backups"**
3. Frequência: Diário
4. Retenção: 7 dias

---

## 📦 Estrutura de Serviços no EasyPanel

```
portal-personalitec/
├── App (Laravel)         - Container principal
├── PostgreSQL 15         - Banco de dados
└── Redis 7               - Cache/Sessions/Queue
```

**Networking:** Todos os serviços se comunicam internamente via rede privada.

---

## 🆘 Troubleshooting

### **App não inicia:**
1. Verifique logs: Dashboard → Logs
2. Verifique variáveis de ambiente (DB_HOST, REDIS_HOST)
3. Force rebuild: **"Redeploy"**

### **Erro 500:**
```bash
# No terminal do EasyPanel
tail -f storage/logs/laravel.log
```

### **Banco não conecta:**
1. Verifique `DB_HOST` nas variáveis
2. Confirme que PostgreSQL está rodando
3. Teste conexão:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### **Redis não conecta:**
1. Verifique `REDIS_HOST` nas variáveis
2. Confirme que Redis está rodando

---

## 💰 Custos Estimados (EasyPanel)

- **Plano Free:** até 1 GB RAM, 1 vCPU
- **Plano Pro:** a partir de $5-10/mês (recomendado para produção)

---

## 📝 Checklist Final

- [ ] Código commitado no Git
- [ ] Repositório conectado no EasyPanel
- [ ] PostgreSQL criado e configurado
- [ ] Redis adicionado
- [ ] Variáveis de ambiente configuradas
- [ ] APP_KEY gerada
- [ ] Deploy realizado com sucesso
- [ ] Migrations executadas
- [ ] Domínio configurado (opcional)
- [ ] SSL ativo (automático)
- [ ] Backups automáticos ativados
- [ ] Aplicação acessível e funcionando

---

## 🎯 Próximos Passos

1. ✅ Testar todas as funcionalidades
2. ✅ Configurar monitoramento de erros (Sentry, Bugsnag)
3. ✅ Configurar email (SMTP)
4. ✅ Documentar credenciais em local seguro
5. ✅ Treinar equipe no uso da plataforma

---

## 🔗 Links Úteis

- **EasyPanel Docs:** https://easypanel.io/docs
- **Suporte:** https://easypanel.io/support
- **Status Page:** Verificar status dos serviços

---

✅ **Deploy no EasyPanel concluído com sucesso!** 🎉

**URL da aplicação:** `https://seu-dominio.easypanel.host`
