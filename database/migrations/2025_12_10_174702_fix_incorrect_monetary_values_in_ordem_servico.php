<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Esta migration corrige valores monetários que foram salvos incorretamente
     * devido à conversão inadequada de formato brasileiro (730,00) para float.
     *
     * Critérios para correção:
     * - Valor >= 10000 (valores muito altos são suspeitos)
     * - Valor é um número inteiro (sem decimais) ou termina em .00
     * - Quando dividido por 100, resulta em valor mais razoável
     *
     * IMPORTANTE: Execute primeiro um SELECT para ver quais registros serão afetados:
     *
     * SELECT id, valor_despesa, preco_produto, valor_total, km, created_at
     * FROM ordem_servico
     * WHERE (valor_despesa >= 10000 OR preco_produto >= 10000 OR valor_total >= 10000 OR km >= 10000)
     * ORDER BY created_at DESC;
     */
    public function up(): void
    {
        echo "\n=== Corrigindo valores monetários incorretos ===\n";

        $columns = [
            'valor_despesa' => 'Valor Despesa',
            'preco_produto' => 'Preço Produto',
            'valor_total' => 'Valor Total',
            'km' => 'KM'
        ];

        $totalFixed = 0;

        foreach ($columns as $column => $label) {
            // Buscar registros com valores suspeitos (>= 10000)
            $records = DB::table('ordem_servico')
                ->whereNotNull($column)
                ->where($column, '>=', 10000)
                ->select('id', $column)
                ->get();

            if ($records->count() > 0) {
                echo "\n{$label}: Encontrados {$records->count()} registros com valores >= 10000\n";

                foreach ($records as $record) {
                    $oldValue = $record->$column;
                    $newValue = $oldValue / 100;

                    // Atualizar
                    DB::table('ordem_servico')
                        ->where('id', $record->id)
                        ->update([$column => $newValue]);

                    echo "  OS #{$record->id}: {$oldValue} → {$newValue}\n";
                    $totalFixed++;
                }
            }
        }

        if ($totalFixed > 0) {
            echo "\n✓ Total de valores corrigidos: {$totalFixed}\n";
            \Log::info("Fixed {$totalFixed} monetary values in ordem_servico (divided by 100)");
        } else {
            echo "\n✓ Nenhum valor incorreto encontrado!\n";
        }

        echo "===========================================\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há como reverter com segurança
        echo "\n⚠️  AVISO: Não é possível reverter esta migration com segurança.\n";
        echo "Os valores originais foram sobrescritos.\n\n";

        \Log::warning('Cannot safely reverse monetary value corrections in ordem_servico');
    }
};
