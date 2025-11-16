<?php
/**
 * Script para copiar dados entre bancos de dados
 * Copia os dados do banco 'portal' para portal_dev, portal_staging e portal_prod
 */

// Configuração de conexão
$source_db = 'portal';
$target_dbs = ['portal_dev', 'portal_staging', 'portal_prod'];
$host = '127.0.0.1';
$port = 5432;
$user = 'postgres';
$password = 'css1994';

// Tabelas a copiar (em ordem para respeitar chaves estrangeiras)
$tables = [
    'users',
    'condicoes_pagamento',
    'cliente',
    'fornecedor',
    'tabela_preco',
    'produto',
    'produto_tabela',
    'contato',
    'ordem_servico',
    'produto_ordem',
    'recibo_provisorio',
    'pagamento_parcelas',
    'rps',
    'ordem_servico_rps',
    'ordem_servico_audits',
    'relatorio_fechamento',
    'projetos',
    'reports',
    'report_email_logs',
    'sequences'
];

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║        COPIANDO DADOS ENTRE BANCOS DE DADOS                  ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

try {
    // Conectar ao banco source
    $source_conn = pg_connect("host=$host port=$port dbname=$source_db user=$user password=$password");
    if (!$source_conn) {
        throw new Exception("Erro ao conectar ao banco source ($source_db)");
    }
    echo "✓ Conectado ao banco source: $source_db\n\n";

    // Para cada banco target
    foreach ($target_dbs as $target_db) {
        echo "▶ Copiando para: $target_db\n";
        echo str_repeat("─", 60) . "\n";

        // Conectar ao banco target
        $target_conn = pg_connect("host=$host port=$port dbname=$target_db user=$user password=$password");
        if (!$target_conn) {
            echo "✗ Erro ao conectar ao banco target ($target_db)\n\n";
            continue;
        }

        // Para cada tabela
        foreach ($tables as $table) {
            try {
                // Limpar tabela target
                pg_query($target_conn, "TRUNCATE TABLE \"$table\" CASCADE");

                // Buscar dados da source
                $result = pg_query($source_conn, "SELECT * FROM \"$table\"");
                if (!$result) {
                    echo "  ⚠ Tabela '$table' não encontrada na source\n";
                    continue;
                }

                $row_count = pg_num_rows($result);

                if ($row_count > 0) {
                    // Copiar dados
                    while ($row = pg_fetch_assoc($result)) {
                        $columns = array_keys($row);
                        $values = array_map(function($v) use ($target_conn) {
                            return is_null($v) ? 'NULL' : "'" . pg_escape_string($target_conn, $v) . "'";
                        }, array_values($row));

                        $sql = "INSERT INTO \"$table\" (" . implode(', ', array_map(function($c) {
                            return "\"$c\"";
                        }, $columns)) . ") VALUES (" . implode(', ', $values) . ")";

                        pg_query($target_conn, $sql);
                    }
                    echo "  ✓ $table ($row_count registros)\n";
                } else {
                    echo "  ✓ $table (0 registros)\n";
                }
            } catch (Exception $e) {
                echo "  ✗ Erro ao copiar '$table': " . $e->getMessage() . "\n";
            }
        }

        pg_close($target_conn);
        echo "\n✓ Concluído para $target_db\n\n";
    }

    pg_close($source_conn);

} catch (Exception $e) {
    echo "\n✗ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║        CÓPIA CONCLUÍDA COM SUCESSO!                         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "Resumo:\n";
echo "  • Dados copiados do banco: $source_db\n";
echo "  • Para os bancos: " . implode(', ', $target_dbs) . "\n";
echo "  • Tabelas copiadas: " . count($tables) . "\n\n";

echo "Próximos passos:\n";
echo "  1. Execute START_DEVELOPMENT.bat para iniciar em porta 8000\n";
echo "  2. Execute START_STAGING.bat para iniciar em porta 8080\n";
echo "  3. Execute START_PRODUCTION.bat para iniciar em porta 8001\n\n";

?>
