# 📦 WORKFLOW DE PATCHES

## 🎯 Objetivo

Gerar arquivos ZIP de patch antes de fazer deploy, facilitando distribuição, versionamento e rollback de alterações.

---

## 🚀 Quick Start

### Windows (PowerShell)
```powershell
# Gerar patch da branch atual
.\patches\generate-patch.ps1 -BranchName "main"

# Com auto-commit
.\patches\generate-patch.ps1 -BranchName "main" -AutoCommit
```

### Linux / macOS (Bash)
```bash
# Gerar patch da branch atual
bash .patches/generate-patch.sh main

# Com auto-commit
bash .patches/generate-patch.sh main --auto-commit
```

### PHP (Cross-platform)
```bash
# Gerar patch direto com PHP
php .patches/generate-patch.php main
```

---

## 📋 Fluxo Padrão de Desenvolvimento

### 1️⃣ Desenvolvimento
```bash
git checkout -b feature/nova-funcionalidade
# ... fazer alterações ...
git add .
git commit -m "feat: Descrição da alteração"
```

### 2️⃣ Gerar Patch (ANTES do Deploy)
```bash
# Windows PowerShell
.\patches\generate-patch.ps1 -BranchName "feature/nova-funcionalidade"

# Linux/macOS
bash .patches/generate-patch.sh feature/nova-funcionalidade
```

### 3️⃣ Revisar o Patch
```
Arquivo gerado: .patches/generated/patch_nova_funcionalidade_YYYY-MM-DD_HHMMSS.zip

Contém:
  ✅ PATCH_MANIFEST.md     - Detalhes das alterações
  ✅ INSTRUCOES_INSTALACAO.md - Como instalar
  ✅ Arquivos alterados    - Estrutura do projeto
```

### 4️⃣ Code Review
- Revisar `PATCH_MANIFEST.md` para lista completa
- Verificar estatísticas (linhas adicionadas/removidas)
- Validar que apenas arquivos necessários foram inclusos

### 5️⃣ Deploy
```bash
# Fazer push da branch
git push origin feature/nova-funcionalidade

# Criar Pull Request
gh pr create --title "Adicionar nova funcionalidade"

# Ou mergeá-la depois de aprovado
git checkout main
git merge feature/nova-funcionalidade
```

### 6️⃣ Arquivar o Patch
```bash
# Copiar patch para diretório de releases/arquivos
cp .patches/generated/patch_*.zip releases/v1.0/
```

---

## 📁 Estrutura de Diretórios

```
.patches/
├── generate-patch.php      # Gerador PHP
├── generate-patch.sh       # Script Bash
├── generate-patch.ps1      # Script PowerShell
├── WORKFLOW_PATCHES.md     # Este arquivo
├── templates/              # Templates para patches
│   ├── PATCH_MANIFEST.md
│   └── INSTRUCOES.md
└── generated/              # Patches gerados
    ├── patch_*.zip
    ├── patch_history.json
    └── ...
```

---

## 📊 Informações do Patch

Cada patch gerado contém:

### PATCH_MANIFEST.md
```markdown
# 📦 PATCH MANIFEST

**Data**: 2025-11-21 14:30:00
**Branch**: feature/nova-funcionalidade
**Commit**: a1b2c3d

## 📊 Estatísticas
| Métrica | Quantidade |
|---------|------------|
| Arquivos Adicionados | 2 |
| Arquivos Modificados | 3 |
| Linhas Adicionadas | 247 |
| Linhas Removidas | 35 |

## 📝 Arquivos Alterados
- ✨ Adicionado: `app/Models/NovoModelo.php`
- 🔧 Modificado: `app/Http/Controllers/Controller.php`
- ...
```

### INSTRUCOES_INSTALACAO.md
```markdown
# 📋 Instruções de Instalação

## Passo a Passo
1. Extrair: unzip patch_*.zip -d patch_temp/
2. Copiar: cp -r patch_temp/* /seu/projeto/
3. Limpar cache: php artisan cache:clear
4. Testar as alterações
```

---

## ✨ Exemplos de Uso

### Exemplo 1: Feature RPS com 2 arquivos alterados

```bash
# Develop na branch
git checkout -b feature/rps-melhorias
# ... editar arquivos ...
git add .
git commit -m "feat: Melhorias na emissão de RPS"

# Gerar patch
bash .patches/generate-patch.sh feature/rps-melhorias

# Output:
# ✅ 2 arquivo(s) encontrado(s)
# ✅ Patch gerado com sucesso!
# 📦 Arquivo: patch_rps_melhorias_2025-11-21_143000.zip
```

### Exemplo 2: Fix crítico em produção

```bash
# Hotfix branch
git checkout -b hotfix/corrigir-erro-critical
# ... editar arquivo com bug ...
git add .
git commit -m "fix: Corrigir erro crítico no pagamento"

# Gerar patch
bash .patches/generate-patch.sh hotfix/corrigir-erro-critical

# Fazer review do patch
cat .patches/generated/PATCH_MANIFEST.md

# Deploy rápido (sem PR se urgente)
git push origin hotfix/corrigir-erro-critical
```

### Exemplo 3: Deploy com múltiplos patches

```bash
# Feature 1
git checkout -b feature/feature1
# ... commit ...
bash .patches/generate-patch.sh feature/feature1
# Resultado: patch_feature1_*.zip

# Feature 2
git checkout -b feature/feature2
# ... commit ...
bash .patches/generate-patch.sh feature/feature2
# Resultado: patch_feature2_*.zip

# Arquivar todos os patches
mkdir -p releases/v1.5
cp .patches/generated/patch_*.zip releases/v1.5/
```

---

## 🔍 Consultar Histórico de Patches

O arquivo `.patches/generated/patch_history.json` mantém registro de todos os patches:

```json
{
  "timestamp": "2025-11-21 14:30:00",
  "branch": "feature/nova-funcionalidade",
  "version": "2025-11-21_143000",
  "commit": "a1b2c3d4e5f6",
  "stats": {
    "files_added": 2,
    "files_modified": 3,
    "files_deleted": 0,
    "lines_added": 247,
    "lines_removed": 35
  },
  "files": [
    {
      "status": "A",
      "path": "app/Models/NovoModelo.php"
    },
    {
      "status": "M",
      "path": "app/Http/Controllers/Controller.php"
    }
  ]
}
```

---

## ⚙️ Configuração Avançada

### Ignorar arquivos no patch

Editar `.patches/generate-patch.php` e adicionar filtro:

```php
// Ignorar arquivos específicos
if (strpos($file, '.env') !== false) {
    continue;
}

if (strpos($file, 'node_modules') !== false) {
    continue;
}
```

### Customizar estrutura do patch

Editar `generateManifest()` em `.patches/generate-patch.php` para adicionar mais informações.

---

## 🆘 Troubleshooting

### Problema: "PHP não encontrado"
**Solução**: Adicionar PHP ao PATH ou usar caminho completo:
```bash
C:\php\php.exe .patches/generate-patch.php main
```

### Problema: "Nenhuma mudança detectada"
**Solução**: Certificar que está na branch correta e há commits não mergeados:
```bash
git log main..HEAD
```

### Problema: "Erro ao criar ZIP"
**Solução**: Verificar permissões:
```bash
chmod -R 755 .patches/generated/
```

---

## 📋 Checklist Antes do Deploy

- [ ] Gerar patch com `generate-patch.sh`
- [ ] Revisar `PATCH_MANIFEST.md`
- [ ] Verificar que apenas arquivos necessários foram inclusos
- [ ] Code review do patch
- [ ] Testes da feature
- [ ] Fazer commit/push da branch
- [ ] Criar Pull Request
- [ ] Merge para main após aprovação
- [ ] Arquivar patch em releases/

---

## 🎯 Benefícios

✅ **Rastreabilidade**: Saber exatamente o que foi alterado
✅ **Distribuição**: Fácil compartilhar alterações entre projetos
✅ **Rollback**: Versão anterior sempre disponível
✅ **Documentação**: Cada patch auto-documentado
✅ **Auditoria**: Histórico completo de deployments
✅ **Segurança**: Review antes de deploy

---

## 📚 Próximas Etapas

1. Usar `generate-patch.sh` em TODOS os deployments
2. Arquivar patches em `releases/`
3. Manter histórico de deployments
4. Documentar release notes com base no patch

---

**Versão**: 1.0
**Data**: 2025-11-21
**Status**: ✅ Pronto para Usar
