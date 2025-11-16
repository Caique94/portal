# Rodar Portal Localmente - PostgreSQL + Rede Local

## 1. Criar Banco de Dados

Abra pgAdmin ou psql e execute:

```sql
CREATE DATABASE portal;
```

## 2. Configurar .env

Copie `.env.example` para `.env`:

```bash
copy .env.example .env
```

Edite o arquivo `.env` com suas configurações (exemplo):

```env
APP_NAME=Portal
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.0.72:8001

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=portal
DB_USERNAME=postgres
DB_PASSWORD=css1994
DB_SCHEMA=public
DB_TIMEZONE=America/Sao_Paulo

CACHE_DRIVER=array
SESSION_DRIVER=cookie
SESSION_LIFETIME=480
SESSION_SECURE_COOKIE=false
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

BROADCAST_DRIVER=log
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD="sua-senha-app"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@gmail.com
MAIL_FROM_NAME="Sua Empresa"

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_TIMEZONE=America/Sao_Paulo

VITE_APP_NAME="Portal"
VITE_PORT=5173
```

**Ajustes importantes:**
- `APP_URL`: Mude para seu IP local (ex: `http://192.168.0.72:8001`)
- `MAIL_*`: Configure com suas credenciais de email (Gmail, etc)
- `DB_PASSWORD`: Sua senha do PostgreSQL

## 3. Instalar Dependências

```bash
composer install
npm install
```

## 4. Gerar Chave (se não existir)

```bash
php artisan key:generate
```

## 5. Criar Tabelas

```bash
php artisan migrate --seed
```

## 6. Compilar Assets

```bash
npm run build
```

## 7. Iniciar Servidores (3 Terminais)

**Terminal 1 - Servidor Laravel:**

```bash
php artisan serve --host=0.0.0.0 --port=8001
```

(Mude para a porta que configurou em `APP_URL`)

**Terminal 2 - Worker de Filas (para PDFs e Emails):**

```bash
php artisan queue:work
```

**Terminal 3 - Watch Assets (desenvolvimento):**

```bash
npm run dev
```

## 8. Acessar

- **Seu computador:** http://192.168.0.72:8001
- **Outro dispositivo na rede:** http://192.168.0.72:8001

Para descobrir seu IP (Windows):

```bash
ipconfig
```

Procure por "IPv4 Address" em sua conexão de rede.

---

**Pronto! Seu portal está rodando! 🚀**
