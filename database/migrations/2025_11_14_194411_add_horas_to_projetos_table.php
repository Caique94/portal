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
        Schema::table('projetos', function (Blueprint $table) {
            $table->decimal('horas_alocadas', 10, 2)->default(0)->comment('Total de horas alocadas para o projeto');
            $table->decimal('horas_consumidas', 10, 2)->default(0)->comment('Total de horas consumidas (através das OS)');
            $table->decimal('horas_restantes', 10, 2)->nullable()->comment('Horas restantes (alocadas - consumidas)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projetos', function (Blueprint $table) {
            $table->dropColumn(['horas_alocadas', 'horas_consumidas', 'horas_restantes']);
        });
    }
};
