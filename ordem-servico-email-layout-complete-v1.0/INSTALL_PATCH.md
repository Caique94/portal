# Guia de Instalação - Patch Ordem de Serviço Email Layout

## 📦 Arquivos do Patch

```
ordem-servico-email-layout-optimization.patch  (33 KB)
PATCH_README.md                                (6 KB)
PATCH_COMMITS_SUMMARY.txt                      (Referência)
INSTALL_PATCH.md                               (Este arquivo)
```

## 🚀 Instalação Rápida (Recomendado)

### Passo 1: Verificar o Patch
```bash
cd /caminho/do/projeto
git apply --check ordem-servico-email-layout-optimization.patch
```

Se passar sem erros, continue para o Passo 2.

### Passo 2: Aplicar o Patch
```bash
# Opção A - Como commits individuais (Preserva histórico)
git am ordem-servico-email-layout-optimization.patch

# Opção B - Como mudança única (Mais rápido)
git apply ordem-servico-email-layout-optimization.patch
```

### Passo 3: Verificar Resultado
```bash
git log --oneline -6
git status
```

Deve mostrar 6 novos commits e sem modificações não comitadas.

---

## 📋 Pré-requisitos

- Git instalado e configurado
- Projeto Laravel com tabela `ordem_servico` atualizada
- Nenhuma modificação não comitada em `resources/views/emails/ordem-servico.blade.php`

## ⚠️ Se Houver Conflitos

### Método 1: Aplicar com Rejeições
```bash
git apply --reject ordem-servico-email-layout-optimization.patch
# Resolva os arquivos .rej manualmente
git add .
git commit -m "fix: Resolve patch conflicts"
```

### Método 2: Reverter e Tentar Novamente
```bash
git reset --hard HEAD
git apply ordem-servico-email-layout-optimization.patch
```

### Método 3: Reverter Commits Anteriores
Se o patch não funcionar na primeira tentativa:
```bash
# Encontre o último commit estável
git log --oneline | head -20

# Revert até um commit anterior
git reset --hard <commit-hash>
git am ordem-servico-email-layout-optimization.patch
```

---

## ✅ Checklist de Validação

- [ ] Patch aplicado sem erros
- [ ] Git log mostra 6 novos commits
- [ ] Arquivo `resources/views/emails/ordem-servico.blade.php` modificado
- [ ] Nenhuma mudança pendente (`git status` limpo)
- [ ] Testes passando (se houver)

---

## 🧪 Testes de Funcionalidade

### 1. No Admin
```
1. Ir para: Ordens de Serviço
2. Criar ou selecionar uma OS existente
3. Verificar que os campos aparecem corretamente no formulário
4. Aprovar a OS
```

### 2. No Email Recebido
Verificar se cada campo está correto:

**Cliente:**
- [ ] Mostra nome real (ex: HOMEPLAST (0001))
- [ ] Não mostra N/A

**Tabela de Horas:**
- [ ] HORA INICIO: 08:00
- [ ] HORA FIM: 17:00
- [ ] HORA DESCONTO: 01:30 (ou 00:00 se vazio)
- [ ] DESPESA: R$ 30,00 (ou -- se vazio)
- [ ] TRANSLADO: R$ 50,25 (horas × valor_hora)
- [ ] TOTAL HORAS: 7.50 (qtde_total ou cálculo)

**RESUMO:**
- [ ] Chamado: 150
- [ ] Data de Emissão: 01/12/2025 (não Previsão Retorno)
- [ ] KM: -- ou valor
- [ ] TOTAL OS: R$ 435,00

**Visual:**
- [ ] Header com gradiente azul vibrante (#1E88E5-#42A5F5)
- [ ] Tabelas e seções com cores atualizada
- [ ] Logo visível no footer

---

## 🔄 Rollback (Se Necessário)

### Reverter Último Commit
```bash
git revert HEAD
```

### Reverter Vários Commits
```bash
# Voltar aos 6 últimos commits
git reset --hard HEAD~6

# Ou reverter específico
git revert e2033aa
```

### Restaurar de Backup
```bash
# Se tiver um backup da branch anterior
git checkout backup-branch -- resources/views/emails/ordem-servico.blade.php
```

---

## 📊 Informações do Patch

**Versão:** 1.0
**Data:** 02 de Dezembro de 2025
**Commits:** 6
**Linhas Modificadas:** +86, -109
**Arquivos:** 1 principal (ordem-servico.blade.php)

**Commits Incluídos:**
1. `879ceaf` - Remove total hours from RESUMO
2. `33da9d3` - Update documentation
3. `1a4b552` - Calculate traslado correctly
4. `e2514a7` - Use qtde_total for TOTAL HORAS
5. `a914b16` - Correct TRASLADO spelling
6. `e2033aa` - Update gradient colors

---

## 🆘 Troubleshooting

### Erro: "Patch does not apply"
```bash
# Verifique a versão do git
git --version

# Tente com --force
git apply --force ordem-servico-email-layout-optimization.patch

# Ou use --ignore-space-change
git apply --ignore-space-change ordem-servico-email-layout-optimization.patch
```

### Erro: "Already up to date"
```bash
# Significa que os commits já foram aplicados
git log --oneline | grep "traslado\|RESUMO\|gradiente"
```

### Email não atualiza após deploy
```bash
# Limpar cache se estiver usando
php artisan cache:clear
php artisan view:clear

# Resetar queue se usar jobs
php artisan queue:restart
```

---

## 📞 Contato

Se encontrar problemas:

1. Verifique o arquivo `PATCH_README.md`
2. Consulte `PATCH_COMMITS_SUMMARY.txt` para detalhes técnicos
3. Execute `git log --oneline -10` para verificar histórico
4. Revise os arquivos `.rej` se houver conflitos

---

## 📝 Notas Importantes

- **Backup:** Sempre faça backup antes de aplicar patches
- **Testes:** Teste em ambiente de desenvolvimento primeiro
- **Git History:** O patch preserva o histórico de commits
- **Nenhuma Dependência:** Não requer mudanças no banco de dados
- **Compatibilidade:** Compatível com Laravel 8+

---

**Status:** ✅ **PRONTO PARA PRODUÇÃO**

Siga os passos acima e o patch será aplicado com sucesso!

```bash
# Resumo dos comandos principais:
git apply --check ordem-servico-email-layout-optimization.patch
git am ordem-servico-email-layout-optimization.patch
git log --oneline -6
```
