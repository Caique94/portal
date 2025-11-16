<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('produto_tabela', function(Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produto')->onDelete('cascade');
            $table->foreignId('tabela_preco_id')->constrained('tabela_preco')->onDelete('cascade');
            $table->string('preco');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_tabela');
    }

};