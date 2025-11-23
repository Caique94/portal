<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns don't exist before adding them
        if (Schema::hasTable('tabela_preco')) {
            Schema::table('tabela_preco', function (Blueprint $table) {
                if (!Schema::hasColumn('tabela_preco', 'data_inicio')) {
                    $table->date('data_inicio')->nullable()->after('descricao');
                }
                if (!Schema::hasColumn('tabela_preco', 'data_vencimento')) {
                    $table->date('data_vencimento')->nullable()->after('data_inicio');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tabela_preco', function (Blueprint $table) {
            if (Schema::hasColumn('tabela_preco', 'data_inicio')) {
                $table->dropColumn('data_inicio');
            }
            if (Schema::hasColumn('tabela_preco', 'data_vencimento')) {
                $table->dropColumn('data_vencimento');
            }
        });
    }
};
