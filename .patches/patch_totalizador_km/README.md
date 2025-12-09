# Patch: Ocultar Linhas KM em Totalizadores (OS Não Presencial)
**Data:** 09 de Dezembro de 2025
**Versão:** 1.0.0
**Commit:** `a9a8592`

## Resumo

Este patch corrige a exibição dos totalizadores de Ordem de Serviço para ocultar informações relacionadas a KM e Deslocamento quando a OS **não é presencial**.

## Problema

Quando uma OS não era presencial, os totalizadores ainda mostravam as linhas:
- ❌ "Valor KM Cliente: R$ 0,00"
- ❌ "Valor KM Consultor: R$ 0,00"

Mesmo que os valores fossem zero, essas linhas confundiam os usuários, dando a impressão de que havia alguma cobrança de KM.

## Solução

### Comportamento Atual (Após Patch)

**OS Presencial:**
```
Totalizador - Administração
├─ Valor Hora Cliente: R$ 48,00
├─ Valor KM Cliente: R$ 1,50        ← APARECE
├─ Valor do Serviço: R$ 384,00
├─ Despesas: R$ 50,00
├─ KM: R$ 66,00                     ← APARECE
└─ Deslocamento: R$ 64,00           ← APARECE
```

**OS NÃO Presencial:**
```
Totalizador - Administração
├─ Valor Hora Cliente: R$ 48,00
├─ Valor do Serviço: R$ 384,00
└─ Despesas: R$ 50,00
                                    ← Nada de KM aparece
```

## Arquivos Modificados (2)

1. `resources/views/ordem-servico.blade.php`
2. `public/js/ordem-servico.js`

## Alterações Detalhadas

### 1. ordem-servico.blade.php

#### Totalizador Admin (linha 191)
**ANTES:**
```html
<tr>
    <td><strong>Valor KM Cliente:</strong></td>
    <td class="text-end" id="valorKMConsultor">R$ 0,00</td>
</tr>
```

**DEPOIS:**
```html
<tr id="linhaValorKMCliente" style="display: none;">
    <td><strong>Valor KM Cliente:</strong></td>
    <td class="text-end" id="valorKMConsultor">R$ 0,00</td>
</tr>
```

#### Totalizador Consultor (linha 243)
**ANTES:**
```html
<tr>
    <td><strong>Valor KM Consultor:</strong></td>
    <td class="text-end" id="valorKMConsultorConsultor">R$ 0,00</td>
</tr>
```

**DEPOIS:**
```html
<tr id="linhaValorKMConsultor" style="display: none;">
    <td><strong>Valor KM Consultor:</strong></td>
    <td class="text-end" id="valorKMConsultorConsultor">R$ 0,00</td>
</tr>
```

**Mudanças:**
- Adicionado `id="linhaValorKMCliente"` para o totalizador Admin
- Adicionado `id="linhaValorKMConsultor"` para o totalizador Consultor
- Ambas linhas iniciam ocultas com `style="display: none;"`

### 2. ordem-servico.js

#### Totalizador Admin (linhas 846-870)

**ANTES:**
```javascript
// Mostrar/ocultar linhas de KM e Deslocamento
if ($('#chkOrdemPresencial').is(':checked') && (km > 0 || horasDeslocamento > 0)) {
    if (km > 0) {
        $('#linhaKM').show();
        $('#totalKM').text(formatarMoeda(valorKM));
    } else {
        $('#linhaKM').hide();
    }

    if (horasDeslocamento > 0) {
        $('#linhaDeslocamento').show();
        $('#totalDeslocamento').text(formatarMoeda(valorDeslocamento));
    } else {
        $('#linhaDeslocamento').hide();
    }
} else {
    $('#linhaKM').hide();
    $('#linhaDeslocamento').hide();
}
```

**DEPOIS:**
```javascript
// Mostrar/ocultar linhas relacionadas a presencial
if ($('#chkOrdemPresencial').is(':checked')) {
    // Mostrar linha de Valor KM Cliente
    $('#linhaValorKMCliente').show();

    // Mostrar linhas de KM e Deslocamento se tiver valores
    if (km > 0) {
        $('#linhaKM').show();
        $('#totalKM').text(formatarMoeda(valorKM));
    } else {
        $('#linhaKM').hide();
    }

    if (horasDeslocamento > 0) {
        $('#linhaDeslocamento').show();
        $('#totalDeslocamento').text(formatarMoeda(valorDeslocamento));
    } else {
        $('#linhaDeslocamento').hide();
    }
} else {
    // Ocultar todas as linhas relacionadas a presencial
    $('#linhaValorKMCliente').hide();
    $('#linhaKM').hide();
    $('#linhaDeslocamento').hide();
}
```

#### Totalizador Consultor (linhas 896-920)

**Mesma lógica aplicada**, substituindo:
- `linhaValorKMCliente` → `linhaValorKMConsultor`
- `linhaKM` → `linhaKMConsultor`
- `linhaDeslocamento` → `linhaDeslocamentoConsultor`

**Mudanças:**
- Verifica se checkbox presencial está marcado
- Se **SIM**: Mostra linha "Valor KM" + linhas de totais (se > 0)
- Se **NÃO**: Oculta todas as linhas relacionadas a KM/Deslocamento

## Instruções de Deploy

### Deploy via Git (Recomendado)

```bash
# 1. Conectar ao servidor
ssh root@sistemasemteste.com.br

# 2. Navegar para o diretório
cd /var/www/sistemasemteste.com.br

# 3. Pull do GitHub (se já commitado)
git pull origin main

# 4. Limpar cache de views
php artisan view:clear

# 5. Reiniciar serviços (opcional, mas recomendado)
systemctl restart nginx php8.3-fpm

# 6. Limpar cache do navegador
# Pressionar Ctrl+Shift+R ou abrir em aba anônima
```

### Deploy Manual

```bash
# 1. Extrair patch
unzip patch_totalizador_km.zip -d /tmp/

# 2. Fazer backup
cd /var/www/sistemasemteste.com.br
cp public/js/ordem-servico.js public/js/ordem-servico.js.backup
cp resources/views/ordem-servico.blade.php resources/views/ordem-servico.blade.php.backup

# 3. Copiar arquivos
cp /tmp/patch_totalizador_km/public/js/ordem-servico.js public/js/
cp /tmp/patch_totalizador_km/resources/views/ordem-servico.blade.php resources/views/

# 4. Ajustar permissões
chown www-data:www-data public/js/ordem-servico.js
chown www-data:www-data resources/views/ordem-servico.blade.php
chmod 644 public/js/ordem-servico.js
chmod 644 resources/views/ordem-servico.blade.php

# 5. Limpar cache de views
php artisan view:clear

# 6. Reiniciar PHP-FPM (opcional)
systemctl restart php8.3-fpm
```

## Verificações Pós-Deploy

### ✅ Teste 1: OS Presencial
1. Criar/Editar OS com produto presencial
2. Verificar totalizadores
3. **Esperado:**
   - ✅ Mostra "Valor KM Cliente/Consultor"
   - ✅ Mostra "KM: R$ XX,XX" (se km > 0)
   - ✅ Mostra "Deslocamento: R$ XX,XX" (se deslocamento > 0)

### ✅ Teste 2: OS NÃO Presencial
1. Criar/Editar OS com produto NÃO presencial
2. Verificar totalizadores
3. **Esperado:**
   - ✅ NÃO mostra "Valor KM Cliente/Consultor"
   - ✅ NÃO mostra linhas de KM ou Deslocamento
   - ✅ Mostra apenas: Valor Hora, Serviço, Despesas

### ✅ Teste 3: Troca de Produto
1. Selecionar produto presencial
2. **Esperado:** Linhas KM aparecem
3. Trocar para produto não presencial
4. **Esperado:** Linhas KM desaparecem imediatamente

### ✅ Teste 4: Cache do Navegador
1. Limpar cache: Ctrl+Shift+R
2. Ou abrir em aba anônima
3. Testar funcionalidade

## Rollback

### Via Git
```bash
cd /var/www/sistemasemteste.com.br
git checkout HEAD~1 -- public/js/ordem-servico.js
git checkout HEAD~1 -- resources/views/ordem-servico.blade.php
php artisan view:clear
systemctl restart php8.3-fpm
```

### Via Backup Manual
```bash
cd /var/www/sistemasemteste.com.br
cp public/js/ordem-servico.js.backup public/js/ordem-servico.js
cp resources/views/ordem-servico.blade.php.backup resources/views/ordem-servico.blade.php
php artisan view:clear
systemctl restart php8.3-fpm
```

## Linhas Controladas

Este patch controla a visibilidade de **4 linhas** nos totalizadores:

### Totalizador Admin
1. `#linhaValorKMCliente` - Valor da tarifa KM do cliente
2. `#linhaKM` - Total KM calculado
3. `#linhaDeslocamento` - Total Deslocamento calculado

### Totalizador Consultor
4. `#linhaValorKMConsultor` - Valor da tarifa KM do consultor
5. `#linhaKMConsultor` - Total KM calculado (visão consultor)
6. `#linhaDeslocamentoConsultor` - Total Deslocamento calculado (visão consultor)

## Impacto

**Positivo:**
- ✅ Interface mais limpa
- ✅ Menos confusão para usuários
- ✅ Informação relevante apenas quando aplicável

**Sem Impacto:**
- ✅ Cálculos continuam funcionando normalmente
- ✅ Valores salvos corretamente no banco
- ✅ Outras funcionalidades não afetadas

## Compatibilidade

| Componente | Compatível |
|------------|-----------|
| Laravel 12.25.0 | ✅ |
| PHP 8.3.27+ | ✅ |
| PHP 8.4+ | ✅ |
| Navegadores modernos | ✅ |
| Mobile | ✅ |

## Observações Importantes

1. **Cache do Navegador:** Usuários devem limpar cache (Ctrl+Shift+R)
2. **Cache de Views:** Execute `php artisan view:clear` no servidor
3. **Sem Migration:** Este patch não requer alterações no banco de dados
4. **Retrocompatível:** Funciona com OS existentes no banco

## Troubleshooting

### Problema: Linhas ainda aparecem
**Solução:**
```bash
# Limpar cache do Laravel
php artisan view:clear
php artisan config:clear

# Limpar cache do navegador
# Ctrl+Shift+R ou abrir em aba anônima
```

### Problema: JavaScript não funciona
**Solução:**
```bash
# Verificar se arquivo foi copiado
ls -la public/js/ordem-servico.js

# Ver logs do navegador (F12 > Console)
# Procurar por erros JavaScript

# Hard refresh
Ctrl+F5 ou Cmd+Shift+R
```

### Problema: Totalizadores não atualizam
**Solução:**
```javascript
// Abrir console do navegador (F12)
// Executar:
$('#chkOrdemPresencial').trigger('change');

// Se funcionar, problema é de cache
// Limpar cache e testar novamente
```

## Logs e Debug

Este patch não gera logs específicos. Para debug:

**Console do Navegador (F12):**
```javascript
// Ver estado do checkbox
console.log($('#chkOrdemPresencial').is(':checked'));

// Ver linhas
console.log($('#linhaValorKMCliente').is(':visible'));
console.log($('#linhaKM').is(':visible'));
```

## Contato e Suporte

Em caso de problemas:
1. Verificar cache (Laravel + Navegador)
2. Testar em aba anônima
3. Verificar console do navegador (F12)
4. Confirmar que arquivos foram copiados corretamente

---

**Patch testado e aprovado! 🚀**

**Nota:** Este é um patch **ISOLADO** contendo apenas a correção dos totalizadores. Se você precisar de outras correções (produto presencial, contestação, etc.), use o patch completo.
