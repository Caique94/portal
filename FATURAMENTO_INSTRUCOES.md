# 📋 Instruções de Instalação - Patch de Faturamento

## Passo a Passo

### 1️⃣ Extrair o arquivo
```bash
unzip patch_faturamento_filtro_2025-11-21.zip -d patch_temp/
```

### 2️⃣ Copiar arquivos para o projeto
Preserve a estrutura de diretórios:

```bash
cp patch_temp/app/Http/Controllers/OrdemServicoController.php seu-projeto/app/Http/Controllers/
cp patch_temp/public/js/faturamento.js seu-projeto/public/js/
cp patch_temp/resources/views/faturamento.blade.php seu-projeto/resources/views/
cp patch_temp/routes/web.php seu-projeto/routes/
```

### 3️⃣ Limpar cache (se Laravel)
```bash
cd seu-projeto
php artisan cache:clear
php artisan config:clear
```

### 4️⃣ Testar as alterações

#### A. Abrir página de faturamento
```
http://seu-dominio/faturamento
```

#### B. Clique no botão "Faturar"
- Deve abrir um modal com lista de clientes
- Modal deve ser centered com background estático

#### C. Testar busca de cliente
- Digite parte do nome do cliente
- Lista deve filtrar em tempo real
- Deve funcionar com código e nome

#### D. Selecionar um cliente
- Clique em um cliente da lista
- Modal deve fechar
- Modal com ordens do cliente deve abrir
- Modal mostra apenas ordens com status = 4 (APROVADO)

#### E. Selecionar múltiplas ordens
- Checkboxes devem estar todos selecionados por padrão
- Clique em uma ordem para desselecionar
- Total deve recalcular em tempo real
- Número de ordens selecionadas deve aparecer
- Valor total deve aparecer em R$ 0,00

#### F. Confirmar faturamento
- Clique no botão "Confirmar Seleção"
- Ordens devem ser faturadas
- Tabela deve recarregar
- Mensagem de sucesso deve aparecer
- As ordens faturadas não devem mais aparecer com status 4

### 5️⃣ Verificar console do navegador
```
F12 → Console
```

- Não deve haver erros vermelhos
- Verifique as requisições AJAX
- `/clientes-com-ordens-faturar` deve retornar 200 OK
- `/faturar-ordens-servico` deve retornar 200 OK

### 6️⃣ Verificar logs do Laravel
```bash
tail -f seu-projeto/storage/logs/laravel.log
```

- Nenhum erro CRITICAL ou ERROR
- Apenas warnings normais

### 7️⃣ Limpar temporários
```bash
rm -rf patch_temp/
```

## ⚠️ Observações Importantes

### Backup
- **SEMPRE** faça backup dos arquivos originais antes de aplicar o patch:
  ```bash
  cp app/Http/Controllers/OrdemServicoController.php app/Http/Controllers/OrdemServicoController.php.bak
  cp public/js/faturamento.js public/js/faturamento.js.bak
  cp resources/views/faturamento.blade.php resources/views/faturamento.blade.php.bak
  cp routes/web.php routes/web.php.bak
  ```

### Ambiente
- Teste em **ambiente de desenvolvimento PRIMEIRO**
- Certifique-se que você tem ordens com status = 4
- Verificar que jQuery, Bootstrap 5, SweetAlert2 estão carregados

### Conflitos
- Verifique se há conflitos com suas customizações
- Se houver, mescle manualmente
- Teste novamente após mesclar

### Rollback
Se algo der errado:
```bash
cp app/Http/Controllers/OrdemServicoController.php.bak app/Http/Controllers/OrdemServicoController.php
cp public/js/faturamento.js.bak public/js/faturamento.js
cp resources/views/faturamento.blade.php.bak resources/views/faturamento.blade.php
cp routes/web.php.bak routes/web.php
php artisan cache:clear
```

## ✅ Checklist de Testes

- [ ] Página de faturamento carrega sem erros
- [ ] Botão "Faturar" abre modal de clientes
- [ ] Busca de clientes funciona em tempo real
- [ ] Seleção de cliente abre modal de ordens
- [ ] Modal mostra apenas ordens status = 4
- [ ] Seleção múltipla de ordens funciona
- [ ] Total é recalculado dinamicamente
- [ ] Deselecionar ordem recalcula total
- [ ] Confirmar faturamento funciona
- [ ] Ordens desaparecem da tabela após faturamento
- [ ] Mensagem de sucesso aparece
- [ ] Nenhum erro no console do navegador
- [ ] Nenhum erro nos logs do Laravel
- [ ] Modal de clientes abre novamente sem erros

## 🆘 Troubleshooting

### P: Modal de clientes não aparece
**A**:
1. Abra F12 > Console para verificar erros JavaScript
2. Verifique se `#modalSelecionarClienteFaturamento` existe no HTML
3. Certifique-se que Bootstrap.js está carregado
4. Teste em browser moderno

### P: Lista de clientes não carrega
**A**:
1. Verifique se endpoint `/clientes-com-ordens-faturar` retorna dados
2. Acesse `http://seu-dominio/clientes-com-ordens-faturar` no browser
3. Verifique se existem ordens com status = 4 no banco
4. Confira se modelo Cliente tem relacionamento `ordemServicos`
5. Verifique logs do Laravel

### P: AJAX error 404
**A**:
1. Verifique se rota foi adicionada em `routes/web.php`
2. Execute: `php artisan cache:clear`
3. Verifique URL em `carregarClientesParaFaturamento()` está correta
4. Confirme que você está acessando dentro de uma rota protegida

### P: Seleção de ordens não funciona
**A**:
1. Verifique se SweetAlert2 está carregado
2. Abra Console > Verificar se há erros
3. Checkboxes devem ter classe `.rps-checkbox-faturamento`
4. Teste em browser moderno (Chrome 90+, Firefox 88+, Safari 14+)

### P: Total não recalcula
**A**:
1. Verifique se `atualizarValorTotalFaturamento()` é chamada
2. Confirme elementos IDs:
   - `#ordensCountFaturamento`
   - `#totalFaturamento`
   - `#totalHeaderFaturamento`
3. Verifique console para erros de JavaScript

### P: Faturamento não funciona
**A**:
1. Verifique se `/faturar-ordens-servico` endpoint existe
2. Confirme que POST está sendo enviado
3. Verifique resposta da requisição
4. Confira logs do Laravel
5. Valide dados sendo enviados

## 📞 Suporte

### Se tudo der certo
- Congratulações! 🎉
- Use em produção
- Archive o patch para futuro rollback

### Se algo der errado
1. Use o checklist acima
2. Verifique o backup está ok
3. Fazer rollback se necessário
4. Entrar em contato com suporte técnico

## 📚 Documentação Relacionada

- PATCH_MANIFEST.md - Detalhes técnicos do patch
- RPS_FILTRO_CLIENTES_IMPLEMENTACAO.md - Similar para RPS
- NOVO_WORKFLOW_DEPLOYMENT.md - Workflow de patches em geral

## ✨ Próximos Passos

Após implementação com sucesso:

1. **Archive o Patch**
   ```bash
   mkdir -p releases/2025-11-21
   cp patch_faturamento_filtro_2025-11-21.zip releases/2025-11-21/
   ```

2. **Atualizar Release Notes**
   - Documentar que novo filtro foi adicionado
   - Mencionar benefícios
   - Link para o patch

3. **Treinar Time**
   - Mostrar novo workflow
   - Praticar seleção múltipla
   - Demonstrar cálculo de total

4. **Monitorar**
   - Acompanhar logs por alguns dias
   - Coletar feedback de usuários
   - Corrigir qualquer problema descoberto

---

**Versão**: 1.0
**Data**: 2025-11-21
**Status**: ✅ Pronto para Uso
**Tempo Estimado**: 30-45 minutos
