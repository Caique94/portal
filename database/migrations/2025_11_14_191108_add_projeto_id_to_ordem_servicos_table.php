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
            $table->foreignId('projeto_id')->nullable()->constrained('projetos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordem_servico', function (Blueprint $table) {
            $table->dropForeignKey(['projeto_id']);
            $table->dropColumn('projeto_id');
        });
    }
};
