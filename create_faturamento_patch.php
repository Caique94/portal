<?php
$zipPath = '.patches/generated/patch_faturamento_filtro_2025-11-21.zip';
$zip = new ZipArchive();

if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $files = [
        'app/Http/Controllers/OrdemServicoController.php',
        'public/js/faturamento.js',
        'resources/views/faturamento.blade.php',
        'routes/web.php'
    ];

    foreach ($files as $file) {
        if (file_exists($file)) {
            $zip->addFile($file, $file);
        }
    }

    // Add manifest
    $manifest = file_get_contents(__DIR__ . '/FATURAMENTO_PATCH_MANIFEST.md');
    $zip->addFromString('PATCH_MANIFEST.md', $manifest);

    // Add instructions
    $instructions = file_get_contents(__DIR__ . '/FATURAMENTO_INSTRUCOES.md');
    $zip->addFromString('INSTRUCOES_INSTALACAO.md', $instructions);

    $zip->close();

    echo "✅ Patch criado com sucesso!\n";
    echo "📦 Arquivo: patch_faturamento_filtro_2025-11-21.zip\n";
    echo "📁 Tamanho: " . number_format(filesize($zipPath), 0, ',', '.') . " bytes\n";
} else {
    echo "❌ Erro ao criar ZIP\n";
}
?>
