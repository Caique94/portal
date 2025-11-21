<?php
/**
 * PATCH GENERATOR
 *
 * Ferramenta para gerar arquivos ZIP de patch automaticamente
 * Uso: php .patches/generate-patch.php "feature/nome-da-feature"
 */

date_default_timezone_set('America/Sao_Paulo');

class PatchGenerator {
    private $basePath;
    private $patchDir;
    public $generatedDir;
    private $branchName;
    private $patchVersion;
    public $files = [];
    private $stats = [
        'files_added' => 0,
        'files_modified' => 0,
        'files_deleted' => 0,
        'lines_added' => 0,
        'lines_removed' => 0
    ];

    public function __construct($branchName) {
        // Use current working directory instead of absolute path
        $this->basePath = getcwd();
        $this->patchDir = $this->basePath . '/.patches';
        $this->generatedDir = $this->patchDir . '/generated';
        $this->branchName = $branchName;
        $this->patchVersion = date('Y-m-d_His');

        if (!is_dir($this->generatedDir)) {
            mkdir($this->generatedDir, 0755, true);
        }
    }

    /**
     * Obter lista de arquivos alterados comparando com main
     */
    public function getChangedFiles() {
        // Obter diff entre main e branch especificada
        $branch = str_replace("'", "", $this->branchName);
        $output = shell_exec('git diff --name-status main..' . $branch . ' 2>&1');

        if (!$output) {
            echo "⚠️  Nenhuma mudança detectada ou branch não comparável com main\n";
            return [];
        }

        $lines = array_filter(explode("\n", trim($output)));

        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) >= 2) {
                $status = $parts[0];
                $file = $parts[1];

                $this->files[] = [
                    'status' => $status,
                    'path' => $file,
                    'full_path' => $this->basePath . '/' . $file
                ];

                // Contar estatísticas
                $this->countStats($status, $file);
            }
        }

        return $this->files;
    }

    /**
     * Contar linhas adicionadas/removidas
     */
    private function countStats($status, $file) {
        if ($status === 'A') {
            $this->stats['files_added']++;
        } elseif ($status === 'M') {
            $this->stats['files_modified']++;
        } elseif ($status === 'D') {
            $this->stats['files_deleted']++;
        }

        // Contar linhas alteradas
        $branch = str_replace("'", "", $this->branchName);
        $diff = shell_exec('git diff main..' . $branch . ' -- ' . escapeshellarg($file) . ' 2>&1');

        if ($diff) {
            preg_match_all('/^\+/', $diff, $matches);
            $this->stats['lines_added'] += count($matches[0]);

            preg_match_all('/^\-/', $diff, $matches);
            $this->stats['lines_removed'] += count($matches[0]);
        }
    }

    /**
     * Gerar arquivo ZIP com os arquivos alterados
     */
    public function generateZip() {
        if (empty($this->files)) {
            echo "❌ Nenhum arquivo para incluir no patch\n";
            return false;
        }

        $zipName = $this->generatePatchName();
        $zipPath = $this->generatedDir . '/' . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            echo "❌ Erro ao criar arquivo ZIP\n";
            return false;
        }

        // Adicionar arquivos ao ZIP mantendo estrutura
        foreach ($this->files as $file) {
            if ($file['status'] !== 'D' && file_exists($file['full_path'])) {
                $zip->addFile($file['full_path'], $file['path']);
            }
        }

        // Adicionar arquivo de manifesto
        $manifest = $this->generateManifest();
        $zip->addFromString('PATCH_MANIFEST.md', $manifest);

        // Adicionar instruções de instalação
        $instructions = $this->generateInstructions();
        $zip->addFromString('INSTRUCOES_INSTALACAO.md', $instructions);

        $zip->close();

        echo "✅ Patch gerado com sucesso!\n";
        echo "📦 Arquivo: " . basename($zipPath) . "\n";
        echo "📁 Caminho: " . $zipPath . "\n";

        return $zipPath;
    }

    /**
     * Gerar nome do arquivo patch
     */
    private function generatePatchName() {
        $branchClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->branchName);
        return "patch_{$branchClean}_{$this->patchVersion}.zip";
    }

    /**
     * Gerar arquivo de manifesto
     */
    private function generateManifest() {
        $manifest = "# 📦 PATCH MANIFEST\n\n";
        $manifest .= "**Data**: " . date('Y-m-d H:i:s') . "\n";
        $manifest .= "**Branch**: " . $this->branchName . "\n";
        $manifest .= "**Commit**: " . trim(shell_exec('cd ' . escapeshellarg($this->basePath) . ' && git rev-parse --short HEAD')) . "\n\n";

        $manifest .= "## 📊 Estatísticas\n\n";
        $manifest .= "| Métrica | Quantidade |\n";
        $manifest .= "|---------|------------|\n";
        $manifest .= "| Arquivos Adicionados | " . $this->stats['files_added'] . " |\n";
        $manifest .= "| Arquivos Modificados | " . $this->stats['files_modified'] . " |\n";
        $manifest .= "| Arquivos Deletados | " . $this->stats['files_deleted'] . " |\n";
        $manifest .= "| Linhas Adicionadas | " . $this->stats['lines_added'] . " |\n";
        $manifest .= "| Linhas Removidas | " . $this->stats['lines_removed'] . " |\n";
        $manifest .= "| **Total de Arquivos** | **" . count($this->files) . "** |\n\n";

        $manifest .= "## 📝 Arquivos Alterados\n\n";
        foreach ($this->files as $file) {
            $statusEmoji = [
                'A' => '✨ Adicionado',
                'M' => '🔧 Modificado',
                'D' => '🗑️  Deletado'
            ];

            $manifest .= "- **" . ($statusEmoji[$file['status']] ?? $file['status']) . "**: `" . $file['path'] . "`\n";
        }

        return $manifest;
    }

    /**
     * Gerar instruções de instalação
     */
    private function generateInstructions() {
        $instructions = "# 📋 Instruções de Instalação do Patch\n\n";
        $instructions .= "## Passo a Passo\n\n";
        $instructions .= "### 1️⃣ Extrair o arquivo\n";
        $instructions .= "```bash\n";
        $instructions .= "unzip patch_*.zip -d patch_temp/\n";
        $instructions .= "```\n\n";

        $instructions .= "### 2️⃣ Copiar arquivos para o projeto\n";
        $instructions .= "```bash\n";
        $instructions .= "cp -r patch_temp/* /seu/projeto/\n";
        $instructions .= "```\n\n";

        $instructions .= "### 3️⃣ Limpar cache (se Laravel)\n";
        $instructions .= "```bash\n";
        $instructions .= "cd /seu/projeto\n";
        $instructions .= "php artisan cache:clear\n";
        $instructions .= "php artisan config:clear\n";
        $instructions .= "```\n\n";

        $instructions .= "### 4️⃣ Testar as alterações\n";
        $instructions .= "- Verifique o PATCH_MANIFEST.md para lista completa de arquivos\n";
        $instructions .= "- Teste cada funcionalidade alterada\n";
        $instructions .= "- Limpe temporários: `rm -rf patch_temp/`\n\n";

        $instructions .= "## ⚠️ Observações Importantes\n\n";
        $instructions .= "- Faça backup dos arquivos originais antes de aplicar o patch\n";
        $instructions .= "- Teste em ambiente de desenvolvimento ANTES de produção\n";
        $instructions .= "- Verifique se há conflitos com suas customizações\n";
        $instructions .= "- Execute testes após aplicar o patch\n";

        return $instructions;
    }

    /**
     * Exibir resumo
     */
    public function displaySummary() {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "📊 RESUMO DO PATCH\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

        echo "Estatísticas:\n";
        echo "  ✨ Adicionados:  " . $this->stats['files_added'] . " arquivos\n";
        echo "  🔧 Modificados:  " . $this->stats['files_modified'] . " arquivos\n";
        echo "  🗑️  Deletados:    " . $this->stats['files_deleted'] . " arquivos\n";
        echo "  📝 Linhas add:   " . $this->stats['lines_added'] . " linhas\n";
        echo "  📝 Linhas rem:   " . $this->stats['lines_removed'] . " linhas\n";
        echo "  ℹ️  Total:        " . count($this->files) . " arquivos\n\n";

        echo "═══════════════════════════════════════════════════════════════\n\n";
    }

    /**
     * Obter relatório JSON para histórico
     */
    public function getJsonReport() {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'branch' => $this->branchName,
            'version' => $this->patchVersion,
            'commit' => trim(shell_exec('cd ' . escapeshellarg($this->basePath) . ' && git rev-parse HEAD')),
            'stats' => $this->stats,
            'files' => $this->files
        ];
    }
}

// Main
if ($argc < 2) {
    echo "Uso: php .patches/generate-patch.php <branch-name> [--zip-only]\n\n";
    echo "Exemplo:\n";
    echo "  php .patches/generate-patch.php main\n";
    echo "  php .patches/generate-patch.php feature/rps-filtro-clientes\n\n";
    exit(1);
}

$branchName = $argv[1];
$zipOnly = isset($argv[2]) && $argv[2] === '--zip-only';

$generator = new PatchGenerator($branchName);

echo "🔍 Analisando alterações...\n";
$generator->getChangedFiles();

if (empty($generator->files)) {
    echo "❌ Nenhuma alteração encontrada\n";
    exit(1);
}

echo "✅ " . count($generator->files) . " arquivo(s) encontrado(s)\n\n";

$zipPath = $generator->generateZip();

if ($zipPath) {
    $generator->displaySummary();

    // Salvar relatório JSON
    $report = $generator->getJsonReport();
    file_put_contents(
        $generator->generatedDir . '/patch_history.json',
        json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    echo "✨ Pronto para deploy!\n";
} else {
    echo "❌ Erro ao gerar patch\n";
    exit(1);
}
