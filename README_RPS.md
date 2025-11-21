# Sistema RPS - Documentação

Bem-vindo à documentação completa do sistema de RPS (Recibo de Serviços Prestados) do portal.

## 📚 Documentação Disponível

### 1. **RPS_SISTEMA_FATURAMENTO.md** - Documentação Principal
Comece aqui! Contém:
- Visão geral do sistema RPS
- Arquitetura e componentes
- Fluxo de faturamento completo
- Modelos de dados (tabelas)
- Todos os endpoints de API (GET, POST, etc)
- Exemplos de uso
- Tratamento de erros

**Ideal para:** Entender como o sistema funciona, usar a API, troubleshooting

**Tempo de leitura:** 30-40 minutos

---

### 2. **RPS_GUIA_CUSTOMIZACOES.md** - Guia de Customizações
Guia prático de como estender o sistema. Contém:
- Guia rápido para customizações comuns
- Estrutura de pastas
- Locais exatos de modificação
- 5 exemplos práticos e testados:
  - Adicionar campo de data de pagamento
  - Gerar número RPS automaticamente
  - Notificar cliente por email
  - Criar relatório de RPS
  - Integração com webhook
- Teste de customizações
- Troubleshooting

**Ideal para:** Fazer mudanças, estender funcionalidades

**Tempo de leitura:** 20-30 minutos + implementação

---

## 🚀 Quick Start (3 minutos)

### Para Entender Rápido

1. RPS é um **documento fiscal** que prova prestação de serviço
2. No portal: **Emite RPS** a partir de Ordens de Serviço
3. Status possíveis: `emitida` → `cancelada` → `revertida`
4. Permite agrupar múltiplas OS de um cliente em 1 RPS

### Fluxo Básico

```
Ordem de Serviço aprovada
    ↓
Status: "Aguardando RPS"
    ↓
Usuário clica "Emitir RPS"
    ↓
✅ RPS criada
✉️ Notificação enviada ao consultor
📊 Auditoria registrada
```

---

## 🎯 O Que Você Pode Fazer Agora

### ✅ Usar o Sistema Atual
Consulte [RPS_SISTEMA_FATURAMENTO.md](./RPS_SISTEMA_FATURAMENTO.md) seção **API Endpoints**

Exemplos:
- Criar RPS: `POST /rps`
- Listar RPS: `GET /rps`
- Cancelar RPS: `POST /rps/{id}/cancelar`
- Ver auditoria: `GET /rps/{id}/auditoria`

### ✅ Fazer Customizações
Consulte [RPS_GUIA_CUSTOMIZACOES.md](./RPS_GUIA_CUSTOMIZACOES.md)

Exemplos:
- Adicionar campo `data_pagamento`
- Gerar número RPS automático
- Enviar email ao cliente
- Criar relatório mensal
- Integrar com sistema NFS-e

---

## 📖 Estrutura das Documentações

```
RPS_SISTEMA_FATURAMENTO.md
├── Visão Geral do Sistema
├── Arquitetura
├── Fluxo de Faturamento
├── Modelos de Dados
│   ├── Tabela rps
│   ├── Tabela ordem_servico_rps
│   ├── Tabela rps_audit
│   └── Relacionamentos Eloquent
├── API Endpoints (10 endpoints completos)
├── Permissões e Autorização
├── Implementação (4 cenários com código)
├── Tratamento de Erros
├── Customizações Futuras
├── Exemplos de Uso
└── Suporte

RPS_GUIA_CUSTOMIZACOES.md
├── Guia Rápido (9 cenários comuns)
├── Estrutura de Pastas
├── Locais de Modificação
├── 5 Customizações Detalhadas
│   ├── Campo de data de pagamento
│   ├── Número RPS automático
│   ├── Notificação por email
│   ├── Relatório de RPS
│   └── Webhook para NFS-e
├── Teste de Customizações
└── Troubleshooting
```

---

## 🔍 Encontre Respostas Rapidamente

### "Como criar uma RPS?"
→ [RPS_SISTEMA_FATURAMENTO.md - Seção API Endpoints](./RPS_SISTEMA_FATURAMENTO.md#3-criar-rps)

### "Quais campos tem uma RPS?"
→ [RPS_SISTEMA_FATURAMENTO.md - Seção Modelos de Dados](./RPS_SISTEMA_FATURAMENTO.md#-modelos-de-dados)

### "Como adicionar novo campo?"
→ [RPS_GUIA_CUSTOMIZACOES.md - Seção 1](./RPS_GUIA_CUSTOMIZACOES.md#1-adicionar-campo-de-data-de-pagamento)

### "Como gerar número automático?"
→ [RPS_GUIA_CUSTOMIZACOES.md - Seção 2](./RPS_GUIA_CUSTOMIZACOES.md#2-gerar-número-rps-automático)

### "Como enviar email?"
→ [RPS_GUIA_CUSTOMIZACOES.md - Seção 3](./RPS_GUIA_CUSTOMIZACOES.md#3-notificar-cliente-automaticamente)

### "Como integrar com NFS-e?"
→ [RPS_SISTEMA_FATURAMENTO.md - Cenário 4](./RPS_SISTEMA_FATURAMENTO.md#cenário-4-integração-com-nfs-e-nota-fiscal-de-serviço)

### "Erro ao criar RPS?"
→ [RPS_SISTEMA_FATURAMENTO.md - Seção Tratamento de Erros](./RPS_SISTEMA_FATURAMENTO.md#-tratamento-de-erros)

### "Como fazer testes?"
→ [RPS_GUIA_CUSTOMIZACOES.md - Seção Teste de Customizações](./RPS_GUIA_CUSTOMIZACOES.md#-teste-de-customizações)

---

## 💾 Arquivos do Sistema

### Models
- `app/Models/RPS.php` - Model principal
- `app/Models/OrdemServico.php` - Relacionamento

### Controllers
- `app/Http/Controllers/RPSController.php` - 10 endpoints
- `app/Http/Controllers/FaturamentoController.php` - View

### Services
- `app/Services/PermissionService.php` - Permissões
- `app/Services/AuditService.php` - Auditoria
- `app/Services/NotificationService.php` - Notificações

### Events & Listeners
- `app/Events/RPSEmitted.php` - Evento
- `app/Listeners/HandleRPSEmitted.php` - Tratador

### Frontend
- `public/js/faturamento.js` - JavaScript
- `resources/views/faturamento.blade.php` - Template

### Database
- `database/migrations/2025_11_15_033008_create_rps_table.php`
- `database/migrations/2025_11_15_033058_create_ordem_servico_rps_table.php`

---

## 🔐 Permissões

Quem pode fazer o quê?

| Operação | Admin | Financeiro | Consultor |
|----------|-------|-----------|-----------|
| Ver RPS | ✅ | ✅ | ✅ (próprias) |
| Criar RPS | ✅ | ✅ | ❌ |
| Cancelar RPS | ✅ | ✅ | ❌ |
| Ver Auditoria | ✅ | ✅ | ❌ |

---

## 📊 Status de uma RPS

```
EMITIDA (padrão)
   ├─→ CANCELADA (com motivo)
   │      └─→ REVERTIDA (revert cancelamento)
   │
   └─→ (Permanece emitida até ação)
```

---

## 🛠️ Tecnologias Usadas

- **Backend:** Laravel 11
- **Database:** PostgreSQL
- **Frontend:** jQuery + DataTables.js + Bootstrap 5
- **Auth:** Laravel Fortify / Sanctum
- **Events:** Laravel Events System
- **Notifications:** Laravel Mail + Custom Services

---

## ✨ Destaques do Sistema

✅ **Auditoria Completa**
- Registra: criação, modificação, cancelamento, reversão
- Quem fez, quando fez, o quê fez

✅ **Agrupamento Inteligente**
- Agrupar múltiplas OS de um cliente
- Oferece seleção de quais OS incluir
- Calcula valor total automaticamente

✅ **Notificações**
- Email ao consultor quando RPS é emitida
- Extensível para SMS, webhooks, etc

✅ **Segurança**
- Verificação de permissões em cada operação
- Validação de dados
- Transações com rollback em caso de erro

✅ **Flexível**
- Fácil adicionar novos campos
- Fácil adicionar novos eventos
- Fácil integrar com sistemas externos

---

## 📝 Checklist de Implementação

Se você está implementando RPS pela primeira vez:

- [ ] Ler [RPS_SISTEMA_FATURAMENTO.md](./RPS_SISTEMA_FATURAMENTO.md)
- [ ] Entender o fluxo completo
- [ ] Verificar rotas em `routes/web.php`
- [ ] Testar endpoints com Postman ou CURL
- [ ] Testar frontend em `http://localhost:8001/faturamento`
- [ ] Criar primeiras RPS
- [ ] Verificar auditoria
- [ ] Testar cancelamento

Se você está customizando:

- [ ] Ler [RPS_GUIA_CUSTOMIZACOES.md](./RPS_GUIA_CUSTOMIZACOES.md)
- [ ] Escolher tipo de customização
- [ ] Seguir passo a passo
- [ ] Testes localmente
- [ ] Verificar em staging
- [ ] Deploy em produção

---

## 🚀 Próximos Passos

### Curto Prazo
1. Implementar PDF export (stub pronto)
2. Adicionar filtros avançados
3. Dashboard com resumo

### Médio Prazo
1. Integração NFS-e
2. Aprovação workflow
3. Cálculo automático de impostos

### Longo Prazo
1. Portal cliente (visualizar RPS)
2. Pagamento online integrado
3. Mobile app

Veja detalhes em [RPS_SISTEMA_FATURAMENTO.md - Customizações Futuras](./RPS_SISTEMA_FATURAMENTO.md#-customizações-futuras)

---

## 📞 Dúvidas Frequentes

**P: Posso alterar o número da RPS após criar?**
A: A migração usa `unique`, então número não pode ser duplicado. Cancele e crie nova se necessário.

**P: Uma OS pode estar em múltiplas RPS?**
A: Não, há `UNIQUE` constraint na tabela `ordem_servico_rps`.

**P: Como reverter um cancelamento?**
A: Use endpoint `POST /rps/{id}/reverter` com motivo.

**P: Posso deletar uma RPS?**
A: Não está implementado. Use cancelamento + reversão conforme necessário.

**P: Como integrar com sistema externo?**
A: Crie webhook em `WebhookController.php`, veja exemplo em Guia de Customizações.

---

## 📚 Leitura Recomendada

1. **Iniciante:** Comece por este arquivo (README_RPS.md) - 5 min
2. **Entendimento:** Leia [RPS_SISTEMA_FATURAMENTO.md](./RPS_SISTEMA_FATURAMENTO.md) - 30 min
3. **Prática:** Teste os exemplos em "Exemplos de Uso" - 15 min
4. **Customização:** Consulte [RPS_GUIA_CUSTOMIZACOES.md](./RPS_GUIA_CUSTOMIZACOES.md) conforme necessário

---

## 📞 Suporte

Para dúvidas ou problemas:

1. **Consultar documentação** - Comece por um dos 2 arquivos principais
2. **Verificar logs** - `storage/logs/laravel.log`
3. **Usar Tinker** - `php artisan tinker` para testar queries
4. **Testar API** - Use Postman/CURL com exemplos fornecidos

---

**Versão:** 1.0
**Data:** 19 de Novembro de 2025
**Status:** Documentação Completa ✅

Próximo passo: Selecione uma das documentações e comece!
