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
        Schema::create('pagamento_parcelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recibo_provisorio_id'); // FK para recibo_provisorio
            $table->integer('numero_parcela'); // Número da parcela (1, 2, 3...)
            $table->integer('total_parcelas'); // Total de parcelas
            $table->decimal('valor', 10, 2); // Valor da parcela
            $table->date('data_vencimento'); // Data de vencimento
            $table->date('data_pagamento')->nullable(); // Data em que foi paga
            $table->enum('status', ['pendente', 'paga', 'atrasada'])->default('pendente');
            $table->text('observacao')->nullable(); // Observações sobre o pagamento
            $table->timestamps();

            // Foreign key
            $table->foreign('recibo_provisorio_id')->references('id')->on('recibo_provisorio')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamento_parcelas');
    }
};
