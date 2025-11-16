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
        Schema::create('condicoes_pagamento', function (Blueprint $table) {
            $table->id();
            $table->string('descricao'); // Ex: "À Vista", "Parcelado x3"
            $table->integer('numero_parcelas')->default(1); // 1 = à vista
            $table->integer('intervalo_dias')->default(0); // 0 = à vista
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condicoes_pagamento');
    }
};
