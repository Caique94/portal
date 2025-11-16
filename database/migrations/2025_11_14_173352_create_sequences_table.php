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
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type')->unique(); // Ex: 'user', 'cliente', 'fornecedor', 'produto', 'ordem_servico'
            $table->unsignedBigInteger('current_number')->default(0); // Número atual
            $table->string('prefix')->default(''); // Prefixo (ex: 'CLI', 'FOR', 'PRD')
            $table->integer('min_digits')->default(4); // Número mínimo de dígitos (ex: 0001, 00001)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
