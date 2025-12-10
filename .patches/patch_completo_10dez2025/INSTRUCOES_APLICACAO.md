# Patch Completo - 10 de Dezembro de 2025

**Data:** 10/12/2025
**Commits incluídos:**
- 4045354 - fix: Remove duplicate migration and add column existence check
- a3439b3 - feat: Transform Cadastros section into collapsible menu
- 363add1 - fix: Change sidebar-link flex-direction from column to row
- f8c4c59 - feat: Remove consultor filter from Fechamento Cliente
- be8cd7f - fix: Update route references from old relatorio-fechamento.index

## Descrição Geral

Este patch contém todas as melhorias e correções implementadas no dia 10/12/2025, incluindo:

1. **Separação completa de Fechamento Cliente e Consultor**
2. **Menu lateral com collapse para Cadastros**
3. **Correção de CSS do menu**
4. **Remoção de filtro de consultor no Fechamento Cliente**
5. **Correção de rotas antigas**

---

## Mudanças Detalhadas

### 1. Migration - Verificação de Coluna Existente
**Arquivo:** `database/migrations/2025_12_05_125023_add_tipo_to_relatorio_fechamento_table.php`

**Mudança:**
- Adicionada verificação `Schema::hasColumn()` antes de criar coluna `tipo`
- Evita erro "Duplicate column" em produção
- Migration duplicada (2025_12_09) foi removida

### 2. Menu Cadastros com Collapse
**Arquivo:** `resources/views/layout/master.blade.php`

**Mudanças:**
- Transformado seção "Cadastros" de `sidebar-cap` para menu collapse
- Estrutura igual ao menu "Fechamento"
- Expandível/recolhível com ícone chevron
- Auto-expande quando em qualquer rota de cadastros

**Subitens:**
- Usuários
- Clientes
- Produtos
- Tabela de Preços
- Condições de Pagamento

### 3. CSS - Alinhamento de Ícones
**Arquivo:** `public/css/app.css`

**Mudança:**
- Linha 107: `flex-direction: column` → `flex-direction: row`
- Ícone chevron agora aparece ao lado do texto, não embaixo
- Afeta menus "Fechamento" e "Cadastros"

### 4. Fechamento Cliente - Remoção de Filtro
**Arquivo:** `resources/views/relatorio-fechamento/index-cliente.blade.php`

**Mudanças:**
- Removido filtro "Consultor" da view
- Layout ajustado para 3 colunas (Status, Data Início, Data Fim)
- Foco nos dados do cliente, independente do consultor

**Arquivo:** `app/Http/Controllers/RelatorioFechamentoController.php`

**Mudanças:**
- Método `indexCliente()`: removido filtro por `consultor_id`
- Removida variável `$consultores` do compact
- Adicionado comentário explicativo

### 5. Views Separadas
**Arquivos:**
- `resources/views/relatorio-fechamento/index-cliente.blade.php` (NOVO)
- `resources/views/relatorio-fechamento/index-consultor.blade.php` (NOVO)

**Mudanças:**
- Duas views completamente separadas
- Sem condicionais ou ternários
- Todas as rotas hardcoded para seu tipo específico
- JavaScript com `baseRoute` fixo

### 6. Correção de Rotas Antigas
**Arquivo:** `resources/views/relatorio-fechamento/show.blade.php`

**Mudança:**
- Linha 9: Botão "Voltar" agora usa rota condicional baseada em `tipo`
- `relatorio-fechamento-cliente.index` OU `relatorio-fechamento-consultor.index`

**Arquivo:** `app/Http/Controllers/RelatorioFechamentoController.php`

**Mudança:**
- Método `destroy()`: redireciona para rota correta baseada no tipo
- Salva tipo antes de deletar o registro

### 7. Rotas
**Arquivo:** `routes/web.php`

**Mudanças:**
- Removidas rotas API antigas conflitantes (linhas 284-285)
- Mantidas rotas separadas para cliente e consultor
- Rotas API restantes movidas para `/api/` prefix

---

## Arquivos do Patch

```
patch_completo_10dez2025/
├── INSTRUCOES_APLICACAO.md (este arquivo)
├── RelatorioFechamentoController.php
├── 2025_12_05_125023_add_tipo_to_relatorio_fechamento_table.php
├── app.css
├── master.blade.php
├── create.blade.php
├── index-cliente.blade.php (NOVO)
├── index-consultor.blade.php (NOVO)
├── index.blade.php
├── show.blade.php
└── web.php
```

---

## Instruções de Aplicação

### Passo 1: Backup
```bash
cd /var/www/sistemasemteste
mkdir -p .backups/before_patch_10dez2025

# Backup dos arquivos que serão alterados
cp app/Http/Controllers/RelatorioFechamentoController.php .backups/before_patch_10dez2025/
cp database/migrations/2025_12_05_125023_add_tipo_to_relatorio_fechamento_table.php .backups/before_patch_10dez2025/
cp public/css/app.css .backups/before_patch_10dez2025/
cp resources/views/layout/master.blade.php .backups/before_patch_10dez2025/
cp resources/views/relatorio-fechamento/*.blade.php .backups/before_patch_10dez2025/
cp routes/web.php .backups/before_patch_10dez2025/
```

### Passo 2: Aplicar Arquivos
```bash
# Copiar controller
cp patch_completo_10dez2025/RelatorioFechamentoController.php app/Http/Controllers/

# Copiar migration
cp patch_completo_10dez2025/2025_12_05_125023_add_tipo_to_relatorio_fechamento_table.php database/migrations/

# Copiar CSS
cp patch_completo_10dez2025/app.css public/css/

# Copiar layout
cp patch_completo_10dez2025/master.blade.php resources/views/layout/

# Copiar views de fechamento
cp patch_completo_10dez2025/create.blade.php resources/views/relatorio-fechamento/
cp patch_completo_10dez2025/index-cliente.blade.php resources/views/relatorio-fechamento/
cp patch_completo_10dez2025/index-consultor.blade.php resources/views/relatorio-fechamento/
cp patch_completo_10dez2025/index.blade.php resources/views/relatorio-fechamento/
cp patch_completo_10dez2025/show.blade.php resources/views/relatorio-fechamento/

# Copiar rotas
cp patch_completo_10dez2025/web.php routes/
```

### Passo 3: Limpar Migration Duplicada (IMPORTANTE!)
```bash
# Se a migration duplicada ainda existir, remover
rm -f database/migrations/2025_12_09_191634_add_tipo_to_relatorio_fechamento_table.php

# Remover do banco de dados também
php artisan tinker
```

Dentro do tinker:
```php
DB::table('migrations')->where('migration', '2025_12_09_191634_add_tipo_to_relatorio_fechamento_table')->delete();
exit
```

### Passo 4: Rodar Migrations (caso necessário)
```bash
php artisan migrate
```

### Passo 5: Limpar Caches
```bash
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize
```

### Passo 6: Ajustar Permissões
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## Verificação

Após aplicar o patch, verifique:

### 1. Menu Lateral
- ✅ "Fechamento" aparece com seta ao lado (mesma linha)
- ✅ Ao clicar, expande mostrando "Fechamento Cliente" e "Fechamento Consultor"
- ✅ "Cadastros" aparece com seta ao lado (mesma linha)
- ✅ Ao clicar, expande mostrando os 5 subitens

### 2. Fechamento Cliente
- ✅ URL: `/relatorio-fechamento-cliente`
- ✅ Filtros: Status, Data Início, Data Fim (SEM filtro de consultor)
- ✅ Mostra todos os relatórios tipo "cliente"
- ✅ Botão: "Novo Fechamento Cliente"

### 3. Fechamento Consultor
- ✅ URL: `/relatorio-fechamento-consultor`
- ✅ Filtros: Consultor, Status, Data Início, Data Fim
- ✅ Mostra todos os relatórios tipo "consultor"
- ✅ Botão: "Novo Fechamento Consultor"

### 4. Funcionalidades
- ✅ Criar novo relatório (cliente e consultor)
- ✅ Visualizar relatório
- ✅ Gerar PDF
- ✅ Enviar para aprovação
- ✅ Aprovar/Rejeitar
- ✅ Enviar email
- ✅ Deletar (redireciona para lista correta)
- ✅ Botão "Voltar" leva para lista correta

---

## Diferenças Entre Fechamento Cliente e Consultor

| Aspecto | Fechamento Cliente | Fechamento Consultor |
|---------|-------------------|---------------------|
| **Valores** | Tabela de preços do cliente (`preco_produto`) | Valor do consultor (`valor_hora_consultor`) |
| **Filtro Consultor** | ❌ Não tem | ✅ Tem |
| **Foco** | Dados do cliente | Dados do consultor específico |
| **Número de Filtros** | 3 (Status, Datas) | 4 (Consultor, Status, Datas) |

---

## Troubleshooting

### Erro: "Route [relatorio-fechamento.index] not defined"
**Solução:** Limpe o cache de rotas
```bash
php artisan route:clear
php artisan view:clear
```

### Erro: "Duplicate column: tipo"
**Solução:** Remova a migration duplicada do banco
```bash
php artisan tinker
DB::table('migrations')->where('migration', 'LIKE', '%add_tipo_to_relatorio_fechamento%')->get();
# Identifique a duplicada e delete
DB::table('migrations')->where('migration', '2025_12_09_191634_add_tipo_to_relatorio_fechamento_table')->delete();
```

### Menu não expande/recolhe
**Solução:** Verifique se o Bootstrap JS está carregado
```bash
# Limpe cache do navegador
# Verifique console do navegador por erros JS
```

### Seta do menu aparece embaixo do texto
**Solução:** Certifique-se que o arquivo CSS foi copiado corretamente
```bash
# Verifique linha 107 do app.css
grep "flex-direction" public/css/app.css
# Deve mostrar: flex-direction: row;
```

---

## Rollback

Caso precise reverter as mudanças:

```bash
cd /var/www/sistemasemteste

# Restaurar arquivos do backup
cp .backups/before_patch_10dez2025/RelatorioFechamentoController.php app/Http/Controllers/
cp .backups/before_patch_10dez2025/2025_12_05_125023_add_tipo_to_relatorio_fechamento_table.php database/migrations/
cp .backups/before_patch_10dez2025/app.css public/css/
cp .backups/before_patch_10dez2025/master.blade.php resources/views/layout/
cp .backups/before_patch_10dez2025/*.blade.php resources/views/relatorio-fechamento/
cp .backups/before_patch_10dez2025/web.php routes/

# Limpar caches
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## Notas Importantes

1. **Migration Duplicada**: Certifique-se de remover a migration `2025_12_09_191634_add_tipo_to_relatorio_fechamento_table.php` tanto do sistema de arquivos quanto do banco de dados.

2. **Cache de Navegador**: Após aplicar o patch, peça aos usuários para limpar o cache do navegador ou dar Ctrl+F5 para garantir que o novo CSS seja carregado.

3. **Permissões**: Após copiar arquivos, sempre verifique as permissões dos diretórios `storage` e `bootstrap/cache`.

4. **Dados Existentes**: Relatórios existentes continuarão funcionando. Os que não têm campo `tipo` receberão o valor padrão 'consultor'.

---

## Suporte

Em caso de dúvidas ou problemas:
1. Verifique os logs: `tail -f storage/logs/laravel.log`
2. Consulte este documento de instruções
3. Entre em contato com o desenvolvedor

---

## Resumo de Benefícios

✅ **Interface mais limpa** - Menus organizados com collapse
✅ **Separação clara** - Cliente vs Consultor bem definidos
✅ **Sem conflitos** - Rotas e migrations corrigidas
✅ **Melhor UX** - Filtros apropriados para cada tipo
✅ **Manutenibilidade** - Código sem condicionais complexos
✅ **Performance** - Queries otimizadas por tipo
