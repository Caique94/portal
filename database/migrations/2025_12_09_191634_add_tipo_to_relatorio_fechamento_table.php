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
        Schema::table('relatorio_fechamento', function (Blueprint $table) {
            $table->string('tipo', 20)->default('consultor')->after('id');
            // tipo: 'cliente' ou 'consultor'
            // cliente: usa valores do totalizador administrativo (preco_produto)
            // consultor: usa valores do totalizador consultor (valor_hora_consultor)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relatorio_fechamento', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
