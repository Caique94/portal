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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'valor_desloc')) {
                $table->string('valor_desloc')->nullable();
            }
            if (!Schema::hasColumn('users', 'valor_km')) {
                $table->string('valor_km')->nullable();
            }
            if (!Schema::hasColumn('users', 'salario_base')) {
                $table->string('salario_base')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'valor_desloc')) {
                $table->dropColumn('valor_desloc');
            }
            if (Schema::hasColumn('users', 'valor_km')) {
                $table->dropColumn('valor_km');
            }
            if (Schema::hasColumn('users', 'salario_base')) {
                $table->dropColumn('salario_base');
            }
        });
    }
};
