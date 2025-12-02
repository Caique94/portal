# 🚀 Quick Reference - Ordem de Serviço Email & PDF

**For:** Developers deploying or maintaining the system
**Status:** ✅ Production Ready

---

## 📝 Quick Start

### Send Email to Consultant
```php
$os = OrdemServico::with('consultor', 'cliente')->find($id);
Mail::to($os->consultor->email)->send(new OrdemServicoMail($os, 'consultor'));
```
**Result:** Email + PDF showing "RESUMO - SEU GANHO"

### Send Email to Client
```php
$os = OrdemServico::with('consultor', 'cliente')->find($id);
Mail::to($os->cliente->email)->send(new OrdemServicoMail($os, 'cliente'));
```
**Result:** Email + PDF showing "RESUMO FINANCEIRO"

### Send Using Queue (Recommended)
```php
Mail::to($email)->queue(new OrdemServicoMail($os, 'consultor'));
```

---

## 📁 File Structure

```
app/
├── Mail/
│   └── OrdemServicoMail.php                  (3.2 KB)
└── Services/
    └── OrdemServicoPdfService.php            (3.5 KB)

resources/views/emails/
├── ordem-servico-consultor.blade.php        (15 KB) - Consultant email
├── ordem-servico-cliente.blade.php          (15 KB) - Client email
└── ordem-servico-pdf.blade.php              (13 KB) - PDF template ⭐

storage/app/temp/                            (PDFs saved here)
```

---

## 🎯 What Each File Does

### `OrdemServicoMail.php`
- Routes to correct template (consultant/client)
- Generates PDF automatically
- Attaches PDF to email
- Handles errors gracefully

### `OrdemServicoPdfService.php`
- `gerarPdfConsultor()` - Generate consultant PDF
- `gerarPdfCliente()` - Generate client PDF
- `salvarPdfTemporario()` - Save PDF to storage
- `getNomeArquivoPdf()` - Get filename with timestamp

### Email Templates
- `ordem-servico-consultor.blade.php` - Consultant view (shows earnings)
- `ordem-servico-cliente.blade.php` - Client view (shows invoice)
- `ordem-servico-pdf.blade.php` - PDF view (DomPDF-optimized)

---

## ✅ Deployment Checklist

- [ ] `storage/app/temp/` directory exists
- [ ] `public/images/logo-personalitec.png` exists
- [ ] Mail driver configured (`.env` file)
- [ ] DomPDF installed (`composer show | grep dompdf`)
- [ ] Relationships loaded: `with('consultor', 'cliente')`

---

## 🧹 Maintenance

### Clean Temporary Files
```bash
# Manual
rm -rf storage/app/temp/*.pdf

# Or using PHP
php artisan tinker
> File::delete(glob(storage_path('app/temp/*.pdf')));
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Verify PDF Generation
```php
php artisan tinker
$os = OrdemServico::with('consultor', 'cliente')->first();
$pdf = App\Services\OrdemServicoPdfService::gerarPdfConsultor($os);
file_put_contents('test.pdf', $pdf);
# Open test.pdf to verify
```

---

## 🐛 Common Issues

| Problem | Solution |
|---------|----------|
| PDF not attached | Check `storage/app/temp/` permissions: `chmod -R 755 storage/app/temp/` |
| Logo missing | Verify file: `ls public/images/logo-personalitec.png` |
| Email not sending | Check mail config in `.env`: `MAIL_DRIVER`, credentials |
| Wrong values shown | Verify `tipoDestinatario` parameter: 'consultor' or 'cliente' |
| Memory error | Use queue instead: `.queue()` instead of `.send()` |

---

## 📊 Performance Tips

| Scenario | Recommendation |
|----------|-----------------|
| 1-5 emails | Use `.send()` |
| 6-50 emails | Use `.queue()` |
| 50+ emails | Use batched queue jobs |
| High volume | Schedule cleanup job |

---

## 🔍 Key Configuration

**DomPDF Settings** (in `OrdemServicoPdfService.php`):
```php
->setOption('enable-local-file-access', true)      // Enable local files
->setOption('isHtml5ParserEnabled', true)          // HTML5 support
->setOption('dpi', 96)                             // Screen resolution
->setOption('defaultFont', 'Arial')                // Safe font
->setOption('allow_url_fopen', true)               // File opening
```

**Image Handling**:
```blade
@if(isset($logoPath) && file_exists($logoPath))
    {{-- Base64 encoded logo --}}
    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}">
@else
    {{-- Fallback --}}
    <img src="{{ asset('images/logo-personalitec.png') }}">
@endif
```

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| `IMPLEMENTATION_COMPLETE.md` | Full implementation guide |
| `PDF_RENDERING_IMPROVEMENTS.md` | PDF optimization details |
| `PDF_ANEXO_FEATURE.md` | Feature implementation |
| `QUICK_REFERENCE.md` | This file |

---

## 🎯 Variables in Templates

### Available in Email & PDF Templates
```php
$ordemServico       // OrdemServico model instance
$tipoDestinatario   // 'consultor' or 'cliente'
$logoPath           // (PDF only) Path to logo file
```

### Accessed via Model
```blade
{{ $ordemServico->id }}
{{ $ordemServico->cliente->nome }}
{{ $ordemServico->consultor->name }}
{{ $ordemServico->valor_total }}
{{ $ordemServico->qtde_total }}
{{ $ordemServico->deslocamento }}
```

---

## 🚀 Production Deployment

1. Commit all changes: `git add . && git commit -m "..."`
2. Pull latest code
3. Run tests
4. Verify in staging
5. Deploy to production
6. Monitor logs: `tail -f storage/logs/laravel.log`

---

## ✨ What's Automatic

✅ PDF generated in email constructor
✅ PDF saved to `storage/app/temp/`
✅ PDF attached with proper name
✅ Errors logged but don't block email
✅ Correct template routed based on recipient type

---

**Quick Reference Version:** 1.0
**Status:** ✅ Production Ready
**Last Updated:** December 1, 2025

For full details, see `IMPLEMENTATION_COMPLETE.md`
