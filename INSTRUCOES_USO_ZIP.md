# 📦 Instruções de Uso - RPS_FILTRO_ARQUIVOS_ALTERADOS.zip

## ✅ O que contém o ZIP?

```
RPS_FILTRO_ARQUIVOS_ALTERADOS.zip
├── OrdemServicoController.php
├── faturamento.js
└── README.txt
```

## 🚀 Como Usar

### Passo 1: Extrair o ZIP

```bash
# Windows (Explorer)
Clique com botão direito → Extrair Tudo

# Linux/Mac
unzip RPS_FILTRO_ARQUIVOS_ALTERADOS.zip
```

### Passo 2: Copiar os arquivos para o seu projeto

#### Opção A: Copiar Manualmente

```
OrdemServicoController.php  →  seu-projeto/app/Http/Controllers/
faturamento.js              →  seu-projeto/public/js/
```

#### Opção B: Script de Cópia (Linux/Mac)

```bash
PROJETO_PATH="/caminho/para/seu/projeto"

cp RPS_FILTRO_ARQUIVOS_ALTERADOS/OrdemServicoController.php "$PROJETO_PATH/app/Http/Controllers/"
cp RPS_FILTRO_ARQUIVOS_ALTERADOS/faturamento.js "$PROJETO_PATH/public/js/"

echo "✅ Arquivos copiados com sucesso!"
```

#### Opção C: Script de Cópia (Windows PowerShell)

```powershell
$PROJETO_PATH = "C:\seu\caminho\para\projeto"

Copy-Item "RPS_FILTRO_ARQUIVOS_ALTERADOS\OrdemServicoController.php" -Destination "$PROJETO_PATH\app\Http\Controllers\"
Copy-Item "RPS_FILTRO_ARQUIVOS_ALTERADOS\faturamento.js" -Destination "$PROJETO_PATH\public\js\"

Write-Host "✅ Arquivos copiados com sucesso!"
```

### Passo 3: Limpar Cache Laravel

```bash
cd seu-projeto

php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Passo 4: Testar

1. Abra seu navegador: `http://localhost:8001/faturamento`
2. Clique em "Emitir RPS"
3. Deve aparecer: Modal com lista de clientes que têm ordens aguardando RPS
4. Busque um cliente por nome ou código
5. Selecione um cliente
6. Deve aparecer: Modal melhorado com seleção de ordens
7. Selecione ordens e o total deve recalcular
8. Confirme e siga com o fluxo normal

---

## 📋 O que foi alterado?

### ARQUIVO 1: OrdemServicoController.php

**Adicionado:** Novo método `clientesComOrdensRPS()`

**Localização:** Linhas 658-698 (no final da classe)

**O que faz:**
- Busca clientes que têm ordens com status = 6 (Aguardando RPS)
- Retorna JSON com: id, codigo, nome, numero_ordens
- Cria novo endpoint: `GET /clientes-com-ordens-rps`

---

### ARQUIVO 2: faturamento.js

**Adicionado:**
1. `carregarClientesParaRPS()` - Carrega lista de clientes
2. Event handler para busca de cliente
3. Event handler para seleção de cliente
4. `filtrarTabelaPorClienteRPS()` - Filtra ordens por cliente
5. `abrirModalSelecaoRPS()` - Modal profissional novo
6. `atualizarValorTotalModal()` - Atualiza totais em tempo real

**Modificado:**
- Botão "Emitir RPS" - Agora abre modal de clientes (sem precisar selecionar antes)

**Novo Design:**
- Gradient header azul-roxo
- Cards de ordens com hover effects
- Resumo visual com contadores
- Scrollbar customizada
- Transições suaves

---

## 🔄 Fluxo de Funcionamento

```
1. Usuário clica "Emitir RPS"
   ↓
2. Modal de seleção de clientes abre
   ├─ Busca cliente por nome/código
   └─ Seleciona 1 cliente
   ↓
3. Modal melhorado de seleção de ordens abre
   ├─ Mostra APENAS ordens daquele cliente
   ├─ Pode selecionar múltiplas ordens
   └─ Total recalcula em tempo real
   ↓
4. Clica "Confirmar Seleção"
   ↓
5. Modal de emissão RPS abre (PRÉ-PREENCHIDO)
   ├─ Cliente: já definido
   ├─ Ordens: já definidas
   └─ Total: já calculado
   ↓
6. Preenche número, série, data, condição
   ↓
7. Clica "Salvar"
   ↓
8. ✅ RPS criada com sucesso!
```

---

## ⚠️ Observações Importantes

### Dependências Necessárias

Todos esses já devem estar no seu projeto:
- ✅ Bootstrap 5
- ✅ jQuery
- ✅ SweetAlert2
- ✅ Bootstrap Icons (bi-building)
- ✅ Laravel 11+

Se algum estiver faltando, instale antes de usar.

### Modelo Cliente

O modelo `Cliente` deve ter o relacionamento:
```php
public function ordemServicos()
{
    return $this->hasMany(OrdemServico::class);
}
```

Se não tiver, adicione. Este código já está no projeto.

### Status das Ordens

O código busca ordens com `status = 6` (AGUARDANDO_RPS)

Verifique se suas ordens têm este status no banco de dados:
```sql
SELECT id, cliente_id, status FROM ordem_servico WHERE status = 6;
```

Se não há ordens com status 6, o modal de clientes aparecerá vazio.

---

## 🆘 Troubleshooting

### Erro: "Erro ao carregar clientes"

**Solução:**
1. Verifique se existem ordens com status = 6 no banco
2. Limpe cache: `php artisan cache:clear`
3. Verifique no console do navegador (F12) se há erros AJAX
4. Confira se o endpoint `/clientes-com-ordens-rps` existe

### Modal não abre

**Solução:**
1. Verifique se SweetAlert2 está carregado
2. Abra F12 → Console e veja se há erros
3. Verifique se jQuery está funcionando
4. Teste em navegador diferente

### Total não recalcula

**Solução:**
1. Verifique se elementos com ID `ordensCount` e `totalSelecao` existem
2. Confira se os checkboxes têm classe `rps-checkbox-novo`
3. Abra F12 → Console e veja erros

---

## 📞 Contato & Suporte

Se tiver problemas:
1. Verifique os logs: `storage/logs/laravel.log`
2. Abra F12 → Console para ver erros JavaScript
3. Confira as versões das dependências

---

## 📝 Versão & Data

- **Versão:** 1.0
- **Data:** 2025-11-21
- **Status:** ✅ Pronto para uso
- **Commits:** 2c800eb, 73da932, d777b61, 99e944c

---

## 🎯 Resumo

**Apenas 2 arquivos foram alterados!**

Copie-os para as pastas corretas, limpe cache e teste.

Tudo deve funcionar perfeitamente! 🚀

---

**Bom uso!**
