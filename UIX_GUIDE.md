# Portal Personalitec - Guia de Estilo UIX

**Versão:** 2.0 (Bootstrap 5 Consolidado)
**Data:** 14 de Novembro de 2025
**Framework:** Bootstrap 5 (Primário)
**Status:** ✅ Design System Implementado

---

## 📋 Índice

1. [Princípios de Design](#princípios)
2. [Paleta de Cores](#cores)
3. [Tipografia](#tipografia)
4. [Spacing & Layout](#spacing)
5. [Componentes](#componentes)
6. [Padrões Responsive](#responsive)
7. [Acessibilidade](#acessibilidade)
8. [Exemplos de Uso](#exemplos)

---

## 🎨 Princípios de Design {#princípios}

### Filosofia
- **Simplicidade:** Interface limpa e intuitiva
- **Consistência:** Padrões visuais uniformes
- **Acessibilidade:** WCAG 2.1 AA compliant
- **Responsividade:** Mobile-first approach
- **Performance:** CSS otimizado, sem frameworks desnecessários

### Decisões de Design
- ✅ **Framework:** Bootstrap 5 (mantém-se como primário)
- ❌ **Tailwind CSS:** Desativado (mantém coexistência)
- ✅ **CSS Variables:** Implementados para theming
- ✅ **Componentes:** Sistema reutilizável

---

## 🎨 Paleta de Cores {#cores}

### Cores de Marca
```css
--color-brand-primary: #50B5DF      /* Azul claro */
--color-brand-primary-dark: #252ED9 /* Azul escuro */
--color-brand-secondary: #009EFB    /* Azul secundário */
--color-brand-light: #F2F7F8        /* Fundo claro */
```

### Cores de Status
| Status | Cor | Hex | Uso |
|--------|-----|-----|-----|
| Success | Verde | #81B043 | Ações bem-sucedidas, aprovado |
| Error | Vermelho | #F5352F | Erros, rejeição |
| Warning | Laranja | #FF771C | Atenção, alerta |
| Info | Azul | #1C8DD9 | Informações |
| Pending | Amarelo | #FAB700 | Pendente, aguardando |
| Review | Roxo | #9572D6 | Em revisão |

### Cores Neutras
```
--color-gray-50:  #F9FAFB
--color-gray-100: #F3F4F6
--color-gray-200: #E5E7EB  (Borders)
--color-gray-300: #D1D5DB
--color-gray-400: #9CA3AF  (Placeholders)
--color-gray-500: #6B7280
--color-gray-600: #4B5563
--color-gray-700: #374151
--color-gray-800: #1F2937
--color-gray-900: #111827
```

### Uso de Cores
```html
<!-- Componentes de Marca -->
<div class="bg-primary">Azul Primário</div>

<!-- Status Badges -->
<span class="badge-success">Aprovado</span>
<span class="badge-error">Rejeitado</span>

<!-- Textos -->
<p class="text-primary">Texto principal</p>
<p class="text-secondary">Texto secundário</p>
```

---

## 🔤 Tipografia {#tipografia}

### Font Family
```css
--font-family-base: -apple-system, BlinkMacSystemFont, 'Segoe UI',
                    Roboto, 'Helvetica Neue', Arial, sans-serif;
```

### Escalas de Fonte
| Tamanho | Valor | Uso |
|---------|-------|-----|
| xs | 12px | Labels, badges |
| sm | 14px | Texto pequeno, labels |
| base | 16px | Texto padrão |
| lg | 18px | Subheadings |
| xl | 20px | Headings |
| 2xl | 24px | Page titles |
| 3xl | 30px | Large titles |
| 4xl | 36px | Hero titles |

### Peso da Fonte
- **Light (300):** Textos secundários (raramente usado)
- **Normal (400):** Corpo do texto
- **Medium (500):** Labels, badges
- **Semibold (600):** Headings, ênfase
- **Bold (700):** Títulos principais

### Linha
```css
--line-height-tight: 1.2     /* Headings */
--line-height-normal: 1.5    /* Texto padrão */
--line-height-relaxed: 1.75  /* Textos longos */
```

### Exemplos
```html
<h1 class="fs-1">Título Grande (36px)</h1>
<h2 class="fs-2">Título Médio (30px)</h2>
<h3 class="fs-3">Título Pequeno (24px)</h3>
<p class="fs-6">Texto padrão (16px)</p>
<small>Texto pequeno (12px)</small>
```

---

## 📐 Spacing & Layout {#spacing}

### Sistema de Espaçamento
Base: **4px**

| Classe | Valor | Uso |
|--------|-------|-----|
| `spacing-1` | 4px | Espaço mínimo |
| `spacing-2` | 8px | Espaço pequeno |
| `spacing-3` | 12px | Espaço padrão |
| `spacing-4` | 16px | Espaço base |
| `spacing-6` | 24px | Espaço grande |
| `spacing-8` | 32px | Espaço extra |

### Padding (Elementos)
```css
--padding-xs: 8px
--padding-sm: 12px
--padding-base: 16px      /* Padrão */
--padding-lg: 24px
--padding-xl: 32px
```

### Margin (Espaço Entre Elementos)
```css
--margin-xs: 8px
--margin-sm: 12px
--margin-base: 16px       /* Padrão */
--margin-lg: 24px
--margin-xl: 32px
```

### Gap (Flex/Grid)
```css
--gap-xs: 8px
--gap-sm: 12px
--gap-base: 16px          /* Padrão */
--gap-lg: 24px
```

### Exemplo
```html
<!-- Usando Bootstrap classes -->
<div class="p-3">Padding 12px</div>
<div class="mb-4">Margin bottom 16px</div>
<div class="gap-3">Gap 12px (flex/grid)</div>

<!-- Ou usar variáveis CSS diretamente -->
<div style="padding: var(--padding-lg);">Padding 24px</div>
```

---

## 🧩 Componentes {#componentes}

### Cards

**KPI Card** (Dashboard)
```html
<div class="kpi-card">
    <div class="kpi-card-icon">
        <i class="bi bi-graph-up"></i>
    </div>
    <div class="kpi-card-content">
        <div class="kpi-card-label">Total de Vendas</div>
        <div class="kpi-card-value">R$ 15.234,50</div>
    </div>
</div>
```

**Custom Card**
```html
<div class="card-custom">
    <h5>Card Title</h5>
    <p>Card content goes here</p>
</div>
```

### Botões

**Primary Button**
```html
<button class="btn btn-primary-custom">Salvar</button>
<button class="btn btn-primary-custom btn-sm-custom">Pequeno</button>
<button class="btn btn-primary-custom btn-lg-custom">Grande</button>
```

**Other Variants**
```html
<button class="btn btn-success-custom">Sucesso</button>
<button class="btn btn-danger-custom">Deletar</button>
<button class="btn btn-secondary-custom">Cancelar</button>
<button class="btn btn-outline-custom">Outline</button>
```

### Badges

**Status Badge**
```html
<span class="badge-custom badge-success">Aprovado</span>
<span class="badge-custom badge-error">Rejeitado</span>
<span class="badge-custom badge-warning">Alerta</span>
<span class="badge-custom badge-pending">Pendente</span>
```

### Alertas

**Alert Variants**
```html
<div class="alert-custom alert-success-custom">
    <i class="bi bi-check-circle"></i>
    Operação realizada com sucesso!
</div>

<div class="alert-custom alert-error-custom">
    <i class="bi bi-exclamation-triangle"></i>
    Erro ao processar solicitação
</div>
```

### Formulários

**Form Floating Label**
```html
<div class="form-floating mb-3">
    <input type="text" class="form-control" id="nome" placeholder="Nome">
    <label for="nome">Nome *</label>
</div>

<div class="form-floating mb-3">
    <select class="form-select" id="status">
        <option>Selecione...</option>
        <option>Ativo</option>
        <option>Inativo</option>
    </select>
    <label for="status">Status *</label>
</div>
```

### Tabelas

**Table com Responsive**
```html
<div class="table-responsive">
    <table class="table-custom table table-striped">
        <thead>
            <tr>
                <th>Coluna 1</th>
                <th>Coluna 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dado 1</td>
                <td>Dado 2</td>
            </tr>
        </tbody>
    </table>
</div>
```

### Modais

**Modal Customizado**
```html
<div class="modal fade" id="meuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
        <div class="modal-content modal-custom">
            <div class="modal-header">
                <h5 class="modal-title">Título</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Conteúdo aqui
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>
```

---

## 📱 Padrões Responsive {#responsive}

### Breakpoints
```css
xs:  0px     (Mobile phones)
sm:  576px   (Small mobile)
md:  768px   (Tablets)
lg:  992px   (Tablets large)
xl:  1200px  (Desktop)
2xl: 1400px  (Large desktop)
```

### Sidebar Responsivo
- **Desktop (≥1200px):** Sidebar sempre visível (260px)
- **Tablet (768-1199px):** Sidebar escondido, menu hamburguês ativo
- **Mobile (<768px):** Sidebar escondido, drawer com overlay

### Grid Responsivo
```html
<!-- Desktop: 4 colunas, Tablet: 2 colunas, Mobile: 1 coluna -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-12">Item 1</div>
    <div class="col-lg-3 col-md-6 col-12">Item 2</div>
    <div class="col-lg-3 col-md-6 col-12">Item 3</div>
    <div class="col-lg-3 col-md-6 col-12">Item 4</div>
</div>
```

### Touch Targets
- Mínimo: **44x44px** em mobile
- Espaçamento mínimo entre elementos: **8px**
- Botões: aplicam `min-height: 44px` automaticamente em mobile

### Exemplo Mobile-First
```css
/* Mobile primeiro */
.card { padding: 12px; }

/* Tablet */
@media (min-width: 768px) {
    .card { padding: 16px; }
}

/* Desktop */
@media (min-width: 1200px) {
    .card { padding: 24px; }
}
```

---

## ♿ Acessibilidade {#acessibilidade}

### Cores
- ✅ Contraste mínimo 4.5:1 para texto normal
- ✅ Contraste mínimo 3:1 para textos grandes
- ✅ Não depender apenas de cor para transmitir informação

### Teclado
- ✅ Todos os elementos interativos acessíveis por teclado
- ✅ Ordem de tabulação lógica
- ✅ Indicador de foco visível (outline)

### ARIA
```html
<!-- Botão hamburguês -->
<button aria-label="Menu" id="sidebarToggle">
    <i class="bi bi-list"></i>
</button>

<!-- Modal -->
<div role="dialog" aria-labelledby="modalTitle" aria-modal="true">
    <h2 id="modalTitle">Título do Modal</h2>
</div>

<!-- Badges de status -->
<span aria-label="Aprovado" class="badge-success">✓</span>
```

### Textos Alt
```html
<img src="logo.png" alt="Logo Personalitec" />
<i class="bi bi-check" aria-label="Aprovar"></i>
```

### Semântica
```html
<!-- Bom -->
<button class="btn">Salvar</button>
<nav><a href="#home">Home</a></nav>
<main><h1>Página</h1></main>
<footer>&copy; 2025</footer>

<!-- Evitar -->
<div onclick="salvar()">Salvar</div>
<div class="nav"><span>Home</span></div>
```

---

## 💻 Exemplos de Uso {#exemplos}

### Exemplo 1: Page com KPIs
```html
@extends('layout.master')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Dashboard</h1>

    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12">
            <div class="kpi-card">
                <div class="kpi-card-icon">
                    <i class="bi bi-box"></i>
                </div>
                <div class="kpi-card-content">
                    <div class="kpi-card-label">Total de Produtos</div>
                    <div class="kpi-card-value">1.234</div>
                </div>
            </div>
        </div>
        <!-- mais KPI cards... -->
    </div>
</div>
@endsection
```

### Exemplo 2: Tabela Responsiva
```html
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>João Silva</td>
                <td>joao@example.com</td>
                <td>
                    <span class="badge badge-success">Ativo</span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary">Editar</button>
                    <button class="btn btn-sm btn-danger">Deletar</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### Exemplo 3: Formulário Modal
```html
<div class="modal fade" id="formModal">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="nome" required>
                                <label for="nome">Nome *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" required>
                                <label for="email">Email *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="role" required>
                                    <option>Selecione...</option>
                                    <option>Admin</option>
                                    <option>Usuário</option>
                                </select>
                                <label for="role">Papel *</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

## 📚 Recursos Adicionais

### Arquivos CSS
- `design-tokens.css` - Variáveis CSS e design tokens
- `components.css` - Componentes reutilizáveis
- `app.css` - CSS customizado do portal

### Documentação Bootstrap
- https://getbootstrap.com/docs/5.3/

### Ícones
- Bootstrap Icons: https://icons.getbootstrap.com/

---

## ✅ Checklist para Novas Pages

Ao criar uma página nova, verifique:

- [ ] Responsiva em mobile (<768px)
- [ ] Responsiva em tablet (768-1199px)
- [ ] Responsiva em desktop (1200px+)
- [ ] Usa design tokens (CSS variables)
- [ ] Componentes reutilizáveis
- [ ] Acessibilidade WCAG 2.1 AA
- [ ] Touch targets mínimo 44x44px
- [ ] Contraste adequado (4.5:1)
- [ ] Sem overflow horizontal em mobile
- [ ] Testado em navegadores modernos

---

**Versão:** 2.0
**Última atualização:** 14 de Novembro de 2025
**Desenvolvido com:** Claude Code + Personalitec Team
