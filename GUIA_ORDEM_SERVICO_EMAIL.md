# 📧 Guia - Sistema de Email para Ordem de Serviço

**Data:** 01 de Dezembro de 2025
**Status:** ✅ IMPLEMENTADO
**Commit:** `cad0731`

---

## 📋 O Que Foi Criado

### 1. Mailable Class - `OrdemServicoMail`
**Arquivo:** `app/Mail/OrdemServicoMail.php`

Classe responsável por estruturar o email. Suporta:
- Envio para Consultor
- Envio para Cliente
- Tipo de destinatário dinâmico

```php
// Usar assim:
Mail::to($email)->send(new OrdemServicoMail($ordemServico, 'consultor'));
// ou
Mail::to($email)->send(new OrdemServicoMail($ordemServico, 'cliente'));
```

### 2. Email Template - `ordem-servico.blade.php`
**Arquivo:** `resources/views/emails/ordem-servico.blade.php`

Template HTML responsivo com:
- ✅ Logo Personalitec
- ✅ Informações do Cliente
- ✅ Informações do Consultor
- ✅ Horas trabalhadas (Início, Fim, Desconto, Traslado)
- ✅ Detalhamento do serviço
- ✅ Resumo com valores e KM
- ✅ Responsivo (Desktop, Tablet, Mobile)

**Dados que carrega dinamicamente:**
- `$ordemServico->id` - Número da OS
- `$ordemServico->cliente->name` - Nome do cliente
- `$ordemServico->consultor->name` - Nome do consultor
- `$ordemServico->data_emissao` - Data de emissão
- `$ordemServico->hora_inicio` - Hora início
- `$ordemServico->hora_final` - Hora fim
- `$ordemServico->hora_desconto` - Horas de desconto
- `$ordemServico->deslocamento` - Custo de deslocamento
- `$ordemServico->horas_trabalhadas` - Total de horas
- `$ordemServico->detalhamento` - Descrição do serviço
- `$ordemServico->valor_total` - Valor total
- `$ordemServico->km` - Quilometragem
- `$ordemServico->status` - Status da OS

### 3. Service Layer - `OrdemServicoEmailService`
**Arquivo:** `app/Services/OrdemServicoEmailService.php`

Service responsável pela lógica de envio. Métodos disponíveis:

#### `enviarParaConsultor(OrdemServico $os): bool`
Envia para o email do consultor
```php
$service = new OrdemServicoEmailService();
$sucesso = $service->enviarParaConsultor($ordemServico);
```

#### `enviarParaCliente(OrdemServico $os): bool`
Envia para o email do cliente (PJ ou usuário)
```php
$sucesso = $service->enviarParaCliente($ordemServico);
```

#### `enviarParaAmbos(OrdemServico $os): array`
Envia para ambos e retorna array com status
```php
$resultados = $service->enviarParaAmbos($ordemServico);
// Retorna: ['consultor' => true/false, 'cliente' => true/false]
```

### 4. Controller Methods - 3 Novos Endpoints
**Arquivo:** `app/Http/Controllers/OrdemServicoController.php`

#### `POST /enviar-os-consultor`
Envia Ordem de Serviço para o Consultor
```bash
POST /api/ordem-servico/enviar-consultor
Body: { "id": 123 }

Response:
{
  "success": true,
  "message": "Ordem de Serviço enviada para o Consultor com sucesso"
}
```

#### `POST /enviar-os-cliente`
Envia Ordem de Serviço para o Cliente
```bash
POST /api/ordem-servico/enviar-cliente
Body: { "id": 123 }

Response:
{
  "success": true,
  "message": "Ordem de Serviço enviada para o Cliente com sucesso"
}
```

#### `POST /enviar-os-ambos`
Envia para Consultor E Cliente
```bash
POST /api/ordem-servico/enviar-ambos
Body: { "id": 123 }

Response:
{
  "success": true,
  "message": "Ordem de Serviço enviada com sucesso para Consultor e Cliente",
  "detalhes": {
    "consultor": true,
    "cliente": true
  }
}
```

---

## 🔧 Como Usar na Prática

### Cenário 1: Enviar manualmente via API
```bash
# JavaScript/Fetch
const resposta = await fetch('/api/ordem-servico/enviar-ambos', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ id: 123 })
});
const dados = await resposta.json();
console.log(dados.message);
```

### Cenário 2: Enviar automaticamente ao criar/atualizar OS
No `OrdemServicoController.php` ou em um Event Listener:

```php
use App\Services\OrdemServicoEmailService;

// Após salvar a OS
$ordemServico = OrdemServico::create([...]);

$emailService = new OrdemServicoEmailService();
$emailService->enviarParaConsultor($ordemServico);
$emailService->enviarParaCliente($ordemServico);
```

### Cenário 3: Usar em um Listener de Eventos
```php
// app/Listeners/EnviarOSAoConsultor.php
class EnviarOSAoConsultor
{
    public function handle(OSCreated $event)
    {
        $emailService = new OrdemServicoEmailService();
        $emailService->enviarParaConsultor($event->ordemServico);
    }
}
```

---

## ✉️ Estrutura do Email Enviado

### Para o Consultor:
```
De: noreply@personalitec.com.br
Para: consultor@email.com
Assunto: Ordem de Serviço #123 - Personalitec

[HTML com:]
- Logo Personalitec
- Cliente que contratou
- Contato do cliente
- Data de emissão
- Horas trabalhadas
- Detalhamento do serviço
- Resumo com valores
```

### Para o Cliente:
```
De: noreply@personalitec.com.br
Para: cliente@email.com (ou email PJ)
Assunto: Ordem de Serviço #123 - Personalitec

[Mesmo HTML acima, mas pode ser adaptado conforme necessidade]
```

---

## 🔍 Logging e Debugging

Todos os envios são logados em `storage/logs/laravel.log`:

```
[2025-12-01 15:30:45] local.INFO: Ordem de Serviço enviada para Consultor {"os_id":123,"consultor_email":"consultor@email.com"}
[2025-12-01 15:30:46] local.INFO: Ordem de Serviço enviada para Cliente {"os_id":123,"cliente_email":"cliente@email.com"}
```

Se houver erro:
```
[2025-12-01 15:30:47] local.ERROR: Erro ao enviar Ordem de Serviço para Consultor {"os_id":123,"error":"SMTP connection failed","trace":"..."}
```

---

## 📧 Configuração do Email

A configuração padrão usa as variáveis de ambiente do Laravel:
```php
// .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_FROM_ADDRESS=noreply@personalitec.com.br
MAIL_FROM_NAME=Personalitec
```

Para alterar o remetente, modifique em `config/mail.php` ou nas variáveis de ambiente acima.

---

## 🎨 Customizações Possíveis

### Alterar o Logo
Em `resources/views/emails/ordem-servico.blade.php`, linha com:
```blade
<img src="https://static.wixstatic.com/media/c4d4c1_6fa078f57383404faf7ceb1d9533f4fb~mv2.png/..." alt="Personalitec logo">
```

### Adicionar Mais Campos
Basta adicionar campos na template:
```blade
<div class="row">
  <span class="label">Novo Campo:</span> {{ $ordemServico->novo_campo }}
</div>
```

### Customizar Mensagem do Assunto
Em `app/Mail/OrdemServicoMail.php`:
```php
public function envelope(): Envelope
{
    return new Envelope(
        subject: "Custom: OS #" . $this->ordemServico->id . " para " . $this->ordemServico->cliente->name,
    );
}
```

---

## ✅ Checklist de Implementação

- [x] Mailable class criada
- [x] Email template (Blade) criada
- [x] Service layer criada
- [x] Controller methods adicionados
- [x] Logging implementado
- [x] Erro handling implementado
- [x] Email responsivo
- [x] Documentação completa

---

## 📞 Próximos Passos (Opcional)

1. **PDF Export**: Converter email HTML para PDF usando `barryvdh/laravel-dompdf`
2. **Templates Múltiplos**: Criar templates diferentes para Consultor vs Cliente
3. **Fila de Emails**: Usar `Mail::queue()` para não bloquear a requisição
4. **Anexos**: Adicionar anexos (PDF, relatórios, etc)
5. **Rastreamento**: Integrar com sistema de rastreamento de emails

---

**Status:** 🟢 PRONTO PARA USAR
**Última Atualização:** 01 de Dezembro de 2025

