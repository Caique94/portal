<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('produto_ordem', function(Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produto')->onDelete('cascade');
            $table->foreignId('ordem_servico_id')->constrained('ordem_servico')->onDelete('cascade');
            $table->string('hora_inicio');
            $table->string('hora_final');
            $table->string('hora_desconto');
            $table->string('qtde_total');
            $table->string('detalhamento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_ordem');
    }

};