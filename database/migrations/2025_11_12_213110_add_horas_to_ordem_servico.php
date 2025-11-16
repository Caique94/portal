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
        Schema::table('ordem_servico', function (Blueprint $table) {
            if (!Schema::hasColumn('ordem_servico', 'horas_trabalhadas')) {
                $table->decimal('horas_trabalhadas', 8, 2)->nullable()->default(0)->after('valor_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordem_servico', function (Blueprint $table) {
            if (Schema::hasColumn('ordem_servico', 'horas_trabalhadas')) {
                $table->dropColumn('horas_trabalhadas');
            }
        });
    }
};
