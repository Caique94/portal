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
            $table->enum('tipo', ['consultor', 'cliente'])->default('consultor')->after('consultor_id');
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
