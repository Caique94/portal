# 🐳 Deploy Docker - Portal Personalitec

## 📋 Arquivos Criados

- `Dockerfile` - Imagem Laravel com PHP 8.3-FPM + Nginx
- `docker-compose.yml` - Orquestração de containers (App, PostgreSQL, Redis)
- `docker/nginx/default.conf` - Configuração Nginx
- `docker/supervisor/supervisord.conf` - Gerenciamento de processos (PHP-FPM, Nginx, Queue, Schedule)
- `.dockerignore` - Arquivos excluídos do build

---

## 🚀 Deploy na VPS

### **1. Preparar Ambiente VPS**

```bash
# Conectar na VPS
ssh usuario@ip-da-vps

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verificar instalação
docker --version
docker-compose --version
```

---

### **2. Enviar Projeto para VPS**

**Opção A: Via Git (Recomendado)**
```bash
# Na VPS
cd /var/www
sudo git clone https://seu-repositorio.git portal
cd portal
```

**Opção B: Via SCP/SFTP**
```bash
# No seu computador local
cd C:\Users\caique\Documents\portal\portal
scp -r . usuario@ip-da-vps:/var/www/portal
```

---

### **3. Configurar Variáveis de Ambiente**

```bash
# Na VPS
cd /var/www/portal

# Copiar .env de exemplo
cp .env.example .env

# Editar .env
nano .env
```

**Configurações importantes no `.env`:**
```env
APP_NAME="Portal Personalitec"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://seu-dominio.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=portal_personalitec
DB_USERNAME=portal_user
DB_PASSWORD=TROQUE_POR_SENHA_FORTE

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

### **4. Build e Subir Containers**

```bash
# Build da imagem (primeira vez)
sudo docker-compose build --no-cache

# Subir containers
sudo docker-compose up -d

# Verificar status
sudo docker-compose ps

# Ver logs
sudo docker-compose logs -f app
```

---

### **5. Configurar Laravel dentro do Container**

```bash
# Entrar no container
sudo docker exec -it portal_personalitec_app bash

# Gerar chave da aplicação
php artisan key:generate

# Rodar migrations
php artisan migrate --force

# Otimizar Laravel para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Criar link simbólico do storage
php artisan storage:link

# Sair do container
exit
```

---

### **6. Configurar Firewall e Portas**

```bash
# Permitir porta 8001 (ou a que você configurou)
sudo ufw allow 8001/tcp
sudo ufw reload

# Verificar firewall
sudo ufw status
```

---

### **7. Acessar Aplicação**

```
http://ip-da-vps:8001
```

---

## 🔧 Comandos Úteis

### **Gerenciar Containers**
```bash
# Parar containers
sudo docker-compose down

# Reiniciar containers
sudo docker-compose restart

# Ver logs em tempo real
sudo docker-compose logs -f app

# Ver logs do PostgreSQL
sudo docker-compose logs -f postgres
```

### **Executar Comandos Artisan**
```bash
# Rodar migrations
sudo docker exec -it portal_personalitec_app php artisan migrate

# Limpar cache
sudo docker exec -it portal_personalitec_app php artisan cache:clear

# Listar rotas
sudo docker exec -it portal_personalitec_app php artisan route:list
```

### **Backup do Banco de Dados**
```bash
# Exportar banco
sudo docker exec -it portal_personalitec_db pg_dump -U portal_user portal_personalitec > backup_$(date +%Y%m%d).sql

# Importar banco
cat backup.sql | sudo docker exec -i portal_personalitec_db psql -U portal_user -d portal_personalitec
```

### **Atualizar Código**
```bash
# Puxar última versão do Git
cd /var/www/portal
sudo git pull origin main

# Rebuild da imagem
sudo docker-compose build --no-cache

# Reiniciar containers
sudo docker-compose down
sudo docker-compose up -d

# Rodar migrations
sudo docker exec -it portal_personalitec_app php artisan migrate --force

# Limpar e otimizar cache
sudo docker exec -it portal_personalitec_app php artisan config:cache
sudo docker exec -it portal_personalitec_app php artisan route:cache
sudo docker exec -it portal_personalitec_app php artisan view:cache
```

---

## 🌐 Configurar Domínio (Opcional)

### **Nginx Reverso Proxy (Host VPS)**

```bash
# Instalar Nginx na VPS (fora dos containers)
sudo apt update
sudo apt install nginx

# Criar configuração
sudo nano /etc/nginx/sites-available/portal
```

**Conteúdo:**
```nginx
server {
    listen 80;
    server_name seu-dominio.com www.seu-dominio.com;

    location / {
        proxy_pass http://localhost:8001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
# Ativar site
sudo ln -s /etc/nginx/sites-available/portal /etc/nginx/sites-enabled/

# Testar configuração
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
```

### **SSL com Certbot (HTTPS)**
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# Obter certificado SSL
sudo certbot --nginx -d seu-dominio.com -d www.seu-dominio.com

# Renovação automática já configurada
sudo certbot renew --dry-run
```

---

## 📊 Monitoramento

### **Ver uso de recursos**
```bash
# Uso de CPU/Memória
sudo docker stats

# Espaço em disco
sudo docker system df
```

### **Ver logs específicos**
```bash
# Logs do Laravel
sudo docker exec -it portal_personalitec_app tail -f storage/logs/laravel.log

# Logs do Nginx
sudo docker exec -it portal_personalitec_app tail -f /var/log/nginx/error.log

# Logs do Worker Queue
sudo docker exec -it portal_personalitec_app tail -f storage/logs/worker.log
```

---

## 🔒 Segurança

1. **Trocar senhas do banco no `.env` e `docker-compose.yml`**
2. **Configurar firewall (UFW):**
   ```bash
   sudo ufw enable
   sudo ufw allow 22/tcp   # SSH
   sudo ufw allow 80/tcp   # HTTP
   sudo ufw allow 443/tcp  # HTTPS
   sudo ufw allow 8001/tcp # App (ou fechar se usar proxy)
   ```
3. **Desabilitar debug em produção:** `APP_DEBUG=false`
4. **Configurar SSL/HTTPS**

---

## 🆘 Troubleshooting

### **Container não inicia**
```bash
# Ver logs detalhados
sudo docker-compose logs app

# Rebuild forçado
sudo docker-compose down
sudo docker-compose build --no-cache
sudo docker-compose up -d
```

### **Erro de permissão**
```bash
# Dentro do container
sudo docker exec -it portal_personalitec_app bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### **Banco não conecta**
```bash
# Verificar se PostgreSQL está rodando
sudo docker-compose ps postgres

# Testar conexão
sudo docker exec -it portal_personalitec_app php artisan tinker
>>> DB::connection()->getPdo();
```

---

## 📦 Estrutura de Containers

- **portal_personalitec_app** - Laravel + PHP-FPM + Nginx (porta 8001)
- **portal_personalitec_db** - PostgreSQL 15 (porta 5432)
- **portal_personalitec_redis** - Redis 7 (porta 6379)

**Volumes persistentes:**
- `postgres_data` - Dados do banco PostgreSQL

---

✅ **Projeto dockerizado com sucesso!** 🎉
