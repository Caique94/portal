# 📦 Sistema de Patches - Portal RPS

Bem-vindo ao sistema de geração de patches automatizado! Este sistema facilita a geração de arquivos ZIP com as alterações antes de fazer deploy.

---

## 🎯 O Que É?

O sistema de patches é uma ferramenta que:

✅ Detecta automaticamente arquivos alterados em sua branch
✅ Gera arquivo ZIP com apenas os arquivos modificados
✅ Cria manifesto detalhado das alterações
✅ Fornece instruções de instalação
✅ Permite versionamento e rollback fácil

---

## 🚀 Como Usar?

### Windows (PowerShell)
```powershell
.\generate-patch.ps1 -BranchName "main"
```

### Linux/macOS (Bash)
```bash
bash generate-patch.sh main
```

### Qualquer SO (PHP)
```bash
php generate-patch.php main
```

---

## 📦 O Que é Gerado?

Cada execução gera um arquivo ZIP contendo:

```
patch_feature_2025-11-21_143000.zip
├── PATCH_MANIFEST.md           # Detalhes das alterações
├── INSTRUCOES_INSTALACAO.md    # Como instalar
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── OrdemServicoController.php
│   └── Models/
│       └── Cliente.php
├── public/
│   └── js/
│       └── faturamento.js
└── resources/
    └── views/
        └── faturamento.blade.php
```

---

## 📖 Documentação

- **WORKFLOW_PATCHES.md** - Guia completo de uso
- **templates/RELEASE_NOTES_TEMPLATE.md** - Template para release notes

---

## 📁 Arquivos Deste Sistema

| Arquivo | Descrição |
|---------|-----------|
| `generate-patch.php` | Gerador principal (PHP) |
| `generate-patch.sh` | Script para Linux/macOS |
| `generate-patch.ps1` | Script para Windows |
| `WORKFLOW_PATCHES.md` | Guia de uso completo |
| `README.md` | Este arquivo |
| `templates/` | Templates para patches |
| `generated/` | Patches gerados |

---

## 🎓 Exemplos

### Exemplo 1: Gerar patch simples
```bash
bash generate-patch.sh main
```

### Exemplo 2: Gerar patch com auto-commit
```bash
bash generate-patch.sh feature/nova-feature --auto-commit
```

### Exemplo 3: Windows PowerShell
```powershell
.\generate-patch.ps1 -BranchName "hotfix/corrigir-erro" -AutoCommit
```

---

## ✨ Recursos

- ✅ Detecção automática de arquivos alterados
- ✅ Compactação em ZIP preservando estrutura
- ✅ Manifesto auto-gerado
- ✅ Instruções de instalação incluídas
- ✅ Suporte a múltiplos SOs
- ✅ Histórico JSON para auditoria
- ✅ Scripts shell e PowerShell
- ✅ Fallback para PHP puro

---

## 🔧 Requisitos

- Git instalado e configurado
- PHP 7.4+ (para gerar patches)
- Bash ou PowerShell (conforme seu SO)

---

## 📊 Workflow Recomendado

```
1. Fazer alterações na branch
   ↓
2. Commits normais
   ↓
3. ANTES de fazer push/deploy:
   Rodar: bash generate-patch.sh main
   ↓
4. Revisar patch em: .patches/generated/
   ↓
5. Fazer code review
   ↓
6. Deploy (push/merge)
   ↓
7. Arquivar patch em releases/
```

---

## 🆘 Problemas?

### "PHP não encontrado"
Certifique-se que PHP está instalado:
```bash
php --version
```

### "Não estou em um repositório Git"
Certifique-se que está dentro do diretório do projeto:
```bash
git status
```

### "Nenhuma mudança detectada"
Faça commit das mudanças primeiro:
```bash
git add .
git commit -m "sua mensagem"
```

---

## 📚 Próximos Passos

1. Leia `WORKFLOW_PATCHES.md` para entender o fluxo completo
2. Execute `generate-patch.sh main` para gerar seu primeiro patch
3. Revise o arquivo `PATCH_MANIFEST.md` gerado
4. Use `templates/RELEASE_NOTES_TEMPLATE.md` para release notes

---

## ✅ Checklist Rápido

- [ ] Entendi o que é um patch
- [ ] Consegui gerar um patch com sucesso
- [ ] Revisei o PATCH_MANIFEST.md
- [ ] Entendi como instalar o patch
- [ ] Adicionei ao meu workflow de deployment

---

## 📞 Suporte

Para dúvidas sobre:
- **Uso**: Veja `WORKFLOW_PATCHES.md`
- **Instalação**: Veja `INSTRUCOES_INSTALACAO.md` dentro do ZIP
- **Problemas**: Abra um issue no repositório

---

## 🎉 Você está Pronto!

Comece gerando seu primeiro patch:

```bash
# Windows
.\generate-patch.ps1 -BranchName "main"

# Linux/macOS
bash generate-patch.sh main

# Ou com PHP
php generate-patch.php main
```

---

**Versão**: 1.0
**Data**: 2025-11-21
**Status**: ✅ Pronto para Uso
