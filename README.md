# 📱 Portal - Sistema de Gestão de Ordens de Serviço e RPS

Um sistema completo e robusto para gerenciar **Ordens de Serviço (OS)**, **aprovações**, **faturamento** e **emissão de RPS** (Recibos de Prestação de Serviços).

![Laravel](https://img.shields.io/badge/Laravel-10-FF2D20?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

## 🎯 Funcionalidades Principais

### 📋 Gestão de Ordens de Serviço
- ✅ Criação e edição de ordens de serviço
- ✅ Sistema de aprovações com fluxo de estados (State Machine)
- ✅ Cálculo automático de valores (horas, despesas, deslocamento, KM)
- ✅ Auditoria completa com rastreamento de mudanças
- ✅ Contestação de ordens com motivos documentados

### 💰 Faturamento e RPS
- ✅ Gestão de faturamento de ordens aprovadas
- ✅ Emissão de RPS com parcelamento configurável
- ✅ Histórico completo de transações
- ✅ Integração com condições de pagamento
- ✅ Geração automática de PDFs

### 👥 Controle de Acesso por Papéis (RBAC)
- 👨‍💼 **Administrador**: Acesso total, aprova ordens
- 👤 **Consultor**: Cria e gerencia suas próprias ordens
- 💰 **Financeiro**: Gerencia faturamento e RPS

### 📊 Relatórios e Exportação
- 📈 Relatórios de fechamento por consultor
- 📊 Análises por cliente e período
- 📥 Exportação para Excel e PDF
- 📉 Dashboard com gráficos

## 🛠️ Tecnologias Utilizadas

### Backend
- **Laravel 10** - Framework PHP moderno e robusto
- **MySQL/PostgreSQL** - Banco de dados relacional
- **Laravel Queue** - Processamento assíncronos de jobs
- **DomPDF** - Geração de PDFs
- **Laravel Events** - Sistema baseado em eventos

### Frontend
- **Bootstrap 5** - Framework CSS responsivo
- **jQuery** - Manipulação do DOM
- **DataTables** - Tabelas avançadas com filtros e paginação
- **SweetAlert2** - Alertas elegantes e intuitivos
- **Select2** - Selects customizados com busca
- **Moment.js** - Manipulação de datas

### Arquitetura
- **State Machine Pattern** - Fluxo de estados imutável
- **Service Layer** - Lógica de negócio isolada
- **Event-Driven Architecture** - Sistema baseado em eventos
- **Repository Pattern** - Abstração de dados
- **Role-Based Access Control** - Controle de acesso granular

## 📋 Pré-requisitos

- **PHP 8.1+**
- **Composer** (Gerenciador de dependências PHP)
- **MySQL 5.7+** ou **PostgreSQL 10+**
- **Node.js 14+** (para compilar assets)
- **Git**

## 🚀 Instalação Rápida

### 1️⃣ Clonar o repositório

```bash
git clone https://github.com/seu-usuario/portal.git
cd portal
```

### 2️⃣ Instalar dependências

```bash
# Dependências PHP
composer install

# Dependências Node (assets)
npm install
```

### 3️⃣ Configurar variáveis de ambiente

```bash
# Copiar arquivo de configuração
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate
```

### 4️⃣ Configurar banco de dados

Edite o arquivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=portal
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

### 5️⃣ Executar migrações

```bash
php artisan migrate --seed
```

### 6️⃣ Compilar assets

```bash
npm run build
```

### 7️⃣ Iniciar servidores

```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Compilação de assets (opcional)
npm run dev

# Terminal 3: Worker de filas (para PDFs)
php artisan queue:work
```

Acesse em: `http://localhost:8000`

## 📁 Estrutura do Projeto

```
portal/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controladores (lógica de requisições)
│   │   └── Middleware/         # Middlewares de autenticação
│   ├── Models/                 # Modelos Eloquent (banco de dados)
│   ├── Services/               # Serviços (lógica de negócio)
│   ├── Events/                 # Eventos da aplicação
│   ├── Listeners/              # Listeners que reagem a eventos
│   ├── Jobs/                   # Jobs assíncronos (filas)
│   └── Enums/                  # Enumerações (status, papéis)
├── routes/
│   └── web.php                 # Rotas da aplicação
├── resources/
│   ├── views/                  # Templates Blade
│   └── css/                    # Estilos CSS
├── public/
│   ├── js/                     # JavaScript do frontend
│   └── assets/                 # Imagens e recursos
├── database/
│   ├── migrations/             # Migrações do banco
│   └── seeders/                # Dados de teste
├── storage/                    # Arquivos (logs, cache, uploads)
├── config/                     # Configurações da aplicação
└── tests/                      # Testes automatizados
```

## 🔐 Segurança

### ⚠️ Variáveis Sensíveis - NUNCA commitar!

```
❌ Arquivo .env com senhas reais
❌ Chaves de API e tokens
❌ Credenciais de banco de dados
❌ Chaves de criptografia privadas
❌ Dados pessoais ou sensíveis
```

Use o arquivo `.env.example` como referência e configure localmente.

### ✅ Boas Práticas Implementadas

- ✅ **CSRF Protection** em todos os formulários
- ✅ **SQL Injection Prevention** com Eloquent ORM
- ✅ **XSS Protection** com sanitização de dados
- ✅ **Password Hashing** com bcrypt
- ✅ **Role-Based Authorization** granular
- ✅ **Audit Trail** de todas as alterações
- ✅ **Structured Logging** de erros
- ✅ **Input Validation** em todos os endpoints

## 👥 Papéis e Permissões

| Recurso | Admin | Consultor | Financeiro |
|---------|-------|-----------|-----------|
| Criar OS | ✅ | ✅ | ❌ |
| Editar própria OS | ✅ | ✅ | ❌ |
| Editar qualquer OS | ✅ | ❌ | ❌ |
| Aprovar OS | ✅ | ❌ | ❌ |
| Contestar OS | ✅ | ❌ | ❌ |
| Deletar OS | ✅ | ✅* | ❌ |
| Ver valores | ✅ | ❌ | ✅ |
| Faturar OS | ✅ | ❌ | ✅ |
| Emitir RPS | ✅ | ❌ | ✅ |

*Apenas suas próprias ordens

## 📊 Fluxo de Estados da OS

```
╔═══════════════════════════════════════════════════════════════╗
║                    FLUXO DE ORDEM DE SERVIÇO                  ║
╚═══════════════════════════════════════════════════════════════╝

   ┌─────────────────┐
   │  EM_ABERTO (1)  │  <- Criação
   └────────┬────────┘
            │
            │ Enviar para aprovação
            ↓
   ┌──────────────────────────────┐
   │ AGUARDANDO_APROVACAO (2)     │  <- Admin revisa
   └──────────┬─────────┬──────────┘
              │         │
        Aprovar│         │Contestar
              │         │
              ↓         ↓
   ┌──────────────────┐ ┌─────────────────┐
   │  APROVADO (4)    │ │ CONTESTADA (3)  │
   └────────┬─────────┘ └────────┬────────┘
            │                    │
            │                    │ Reenviar
            │                    │
            └────────────────────┘
                     │
                     ↓
   ┌────────────────────────────┐
   │ AGUARD. FATURAMENTO (4)    │  <- Pronto p/ faturar
   └────────┬──────────────────┘
            │
            │ Faturar
            ↓
   ┌────────────────┐
   │  FATURADA (5)  │
   └────────┬───────┘
            │
            │ Aguardar RPS
            ↓
   ┌─────────────────────┐
   │ AGUARD. RPS (6)     │
   └────────┬────────────┘
            │
            │ Emitir RPS
            ↓
   ┌─────────────────────┐
   │  RPS_EMITIDA (7)    │  <- Finalizado
   └─────────────────────┘
```

## 🔄 Processos Automáticos

### Geração de PDF e Envio de Email

Quando uma OS é **aprovada**:

1. ✅ Sistema cria evento `OSApproved`
2. 📄 Listener enfileira job `GenerateReportJob`
3. 📧 Listener enfileira job `SendReportEmailJob`
4. 🔄 Worker processa jobs: gera PDF e envia email
5. 💾 Email é registrado em `report_email_logs`

**Iniciar worker de filas:**

```bash
php artisan queue:work

# Ou em produção (daemon)
php artisan queue:work --daemon
```

## 🧪 Testes

```bash
# Executar todos os testes
php artisan test

# Com cobertura
php artisan test --coverage

# Um teste específico
php artisan test tests/Feature/OrdemServicoTest.php
```

## 📝 Logging e Auditoria

### Visualizar Logs

```bash
# Logs da aplicação
tail -f storage/logs/laravel.log

# Consultar auditoria no banco
SELECT * FROM ordem_servico_audits ORDER BY created_at DESC;
```

### Estrutura de Auditoria

Cada alteração em uma OS registra:
- **user_id** - Quem fez a alteração
- **action** - Criação, atualização, exclusão
- **old_values** - Dados anteriores
- **new_values** - Dados novos
- **created_at** - Quando foi alterado

## 🐛 Troubleshooting

### Erro: "SQLSTATE[HY000]: General error"

```bash
# Verificar credenciais do banco em .env
# Executar migrações
php artisan migrate
```

### Erro: "Call to undefined function"

```bash
composer install
php artisan cache:clear
```

### PDFs não sendo gerados

```bash
# Instalar DomPDF
composer require barryvdh/laravel-dompdf

# Iniciar worker
php artisan queue:work
```

### DataTable vazio

1. Abra Console do navegador (F12)
2. Verifique erros AJAX
3. Certifique que endpoint retorna `{ data: [...] }`
4. Verifique permissões do usuário

## 🚦 Variáveis de Ambiente Importantes

```env
# Aplicação
APP_NAME=Portal
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com
APP_LOCALE=pt_BR

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=seu-host
DB_DATABASE=portal
DB_USERNAME=seu-usuario
DB_PASSWORD=SENHA_SEGURA_AQUI

# Email (Envio de PDFs)
MAIL_MAILER=smtp
MAIL_HOST=smtp.seu-email.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@example.com
MAIL_PASSWORD=sua-senha-email
MAIL_FROM_ADDRESS=noreply@seu-dominio.com
MAIL_FROM_NAME="Portal OS"

# Fila (Queue)
QUEUE_CONNECTION=database
QUEUE_DRIVER=database

# Cache
CACHE_STORE=database
```

## 📞 Suporte

Encontrou um bug? Abra uma [issue no GitHub](https://github.com/seu-usuario/portal/issues).

## 🤝 Contribuindo

Contribuições são bem-vindas!

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a **MIT License** - veja [LICENSE](LICENSE) para detalhes.

## 🙏 Agradecimentos

Desenvolvido com ❤️ usando **Laravel** e tecnologias modernas.

---

<div align="center">

**Criado com Laravel 10 | PHP 8.1+ | MySQL**

Made with ❤️ for better order management

</div>
